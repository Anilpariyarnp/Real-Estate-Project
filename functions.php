<?php
function getRecommendations($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM property");
    $stmt->execute();
    $allProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($allProperties)) {
        return fallbackRecommendations($conn);
    }

    // Step 2: Define features
    $features = ['bhk', 'bedroom', 'bathroom', 'floor', 'balcony', 'age'];
    $featureRanges = [];

    foreach ($features as $f) {
        // Extract non-null numeric values only
        $column = array_filter(array_column($allProperties, $f), function($val) {
            return is_numeric($val);
        });

        // Only calculate if values exist
        if (!empty($column)) {
            $featureRanges[$f] = [
                'min' => min($column),
                'max' => max($column)
            ];
        }
    }

    // Remove features with no valid data
    $features = array_keys($featureRanges);

    if (empty($features)) {
        return fallbackRecommendations($conn);
    }

    // Step 3: Get user activity
    if (isset($_SESSION['user_activity']) && !empty($_SESSION['user_activity'])) {
        $userActivity = $_SESSION['user_activity'];

        $userVectors = [];
        foreach ($userActivity as $activity) {
            foreach ($allProperties as $prop) {
                if ($prop['id'] == $activity['property_id']) {
                    $userVectors[] = buildFeatureVector($prop, $features, $featureRanges);
                    break;
                }
            }
        }

        if (empty($userVectors)) {
            return fallbackRecommendations($conn);
        }

        // Step 4: Average user vector
        $userInterest = array_fill(0, count($features), 0);
        foreach ($userVectors as $vec) {
            foreach ($vec as $i => $val) {
                $userInterest[$i] += $val;
            }
        }
        foreach ($userInterest as $i => $val) {
            $userInterest[$i] = $val / count($userVectors);
        }

        // Step 5: Score all properties
        $recommendations = [];
        foreach ($allProperties as $prop) {
            if (in_array($prop['id'], array_column($userActivity, 'property_id'))) continue;

            $vector = buildFeatureVector($prop, $features, $featureRanges);
            $score = cosineSimilarity($userInterest, $vector);
            $recommendations[] = ['property' => $prop, 'score' => $score];
        }

        // Sort by similarity
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice(array_column($recommendations, 'property'), 0, 5);
    } else {
        return fallbackRecommendations($conn);
    }
}

function buildFeatureVector($prop, $features, $ranges) {
    $vector = [];
    foreach ($features as $f) {
        $val = isset($prop[$f]) ? $prop[$f] : null;
        $min = $ranges[$f]['min'];
        $max = $ranges[$f]['max'];
        $vector[] = normalize($val, $min, $max);
    }
    return $vector;
}

function normalize($val, $min, $max) {
    if (!is_numeric($val)) return 0;
    return ($max - $min == 0) ? 0 : ($val - $min) / ($max - $min);
}

function cosineSimilarity($vecA, $vecB) {
    $dot = 0; $magA = 0; $magB = 0;
    for ($i = 0; $i < count($vecA); $i++) {
        $dot += $vecA[$i] * $vecB[$i];
        $magA += $vecA[$i] * $vecA[$i];
        $magB += $vecB[$i] * $vecB[$i];
    }
    if ($magA == 0 || $magB == 0) return 0;
    return $dot / (sqrt($magA) * sqrt($magB));
}

function fallbackRecommendations($conn) {
    $stmt = $conn->prepare("SELECT * FROM property ORDER BY RAND() LIMIT 5");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
