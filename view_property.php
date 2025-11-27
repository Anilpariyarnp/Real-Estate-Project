<?php  
session_start(); // Start the session
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:home.php');
}

include 'components/save_send.php';

// Track user activity (if logged in)
if($user_id != ''){
   if(!isset($_SESSION['user_activity'])){
      $_SESSION['user_activity'] = []; // Initialize the activity array
   }
   $_SESSION['user_activity'][] = [
      'property_id' => $get_id,
      'activity_type' => 'view',
      'timestamp' => time()
   ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>View Property</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- view property section starts  -->

<section class="view-property">

   <h1 class="heading">property details</h1>

   <?php
      $select_properties = $conn->prepare("SELECT * FROM `property` WHERE id = ? ORDER BY date DESC LIMIT 1");
      $select_properties->execute([$get_id]);
      if($select_properties->rowCount() > 0){
         $fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC);

         if($fetch_property){
            $property_id = $fetch_property['id'];

            // Fetch user details
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_user->execute([$fetch_property['user_id']]);
            $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

            // Check if the property is saved by the user
            $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? and user_id = ?");
            $select_saved->execute([$fetch_property['id'], $user_id]);
   ?>
   <div class="details">
      <div class="swiper images-container">
         <div class="swiper-wrapper">
            <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_01']); ?>" alt="" class="swiper-slide">
            <?php if(!empty($fetch_property['image_02'])){ ?>
            <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_02']); ?>" alt="" class="swiper-slide">
            <?php } ?>
            <?php if(!empty($fetch_property['image_03'])){ ?>
            <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_03']); ?>" alt="" class="swiper-slide">
            <?php } ?>
            <?php if(!empty($fetch_property['image_04'])){ ?>
            <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_04']); ?>" alt="" class="swiper-slide">
            <?php } ?>
            <?php if(!empty($fetch_property['image_05'])){ ?>
            <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_05']); ?>" alt="" class="swiper-slide">
            <?php } ?>
         </div>
         <div class="swiper-pagination"></div>
      </div>
      <h3 class="name"><?= htmlspecialchars($fetch_property['property_name']); ?></h3>
      <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($fetch_property['address']); ?></span></p>
      <div class="info">
         <p><i class="fas fa-rupee-sign"></i><span><?= htmlspecialchars($fetch_property['price']); ?></span></p>
         <p><i class="fas fa-user"></i><span><?= htmlspecialchars($fetch_user['name']); ?></span></p>
         <p><i class="fas fa-phone"></i><a href="tel:<?= htmlspecialchars($fetch_user['number']); ?>"><?= htmlspecialchars($fetch_user['number']); ?></a></p>
         <p><i class="fas fa-building"></i><span><?= htmlspecialchars($fetch_property['type']); ?></span></p>
         <p><i class="fas fa-house"></i><span><?= htmlspecialchars($fetch_property['offer']); ?></span></p>
         <p><i class="fas fa-calendar"></i><span><?= htmlspecialchars($fetch_property['date']); ?></span></p>
      </div>
      <h3 class="title">details</h3>
      <div class="flex">
         <div class="box">
            <p><i>rooms :</i><span><?= htmlspecialchars($fetch_property['bhk']); ?> BHK</span></p>
            <p><i>deposit amount : </i><span><span class="fas fa-rupee-sign" style="margin-right: .5rem;"></span><?= htmlspecialchars($fetch_property['deposite']); ?></span></p>
            <p><i>status :</i><span><?= htmlspecialchars($fetch_property['status']); ?></span></p>
            <p><i>bedroom :</i><span><?= htmlspecialchars($fetch_property['bedroom']); ?></span></p>
            <p><i>bathroom :</i><span><?= htmlspecialchars($fetch_property['bathroom']); ?></span></p>
            <p><i>balcony :</i><span><?= htmlspecialchars($fetch_property['balcony']); ?></span></p>
         </div>
         <div class="box">
            <p><i>carpet area :</i><span><?= htmlspecialchars($fetch_property['carpet']); ?>sqft</span></p>
            <p><i>age :</i><span><?= htmlspecialchars($fetch_property['age']); ?> years</span></p>
            <p><i>total floors :</i><span><?= htmlspecialchars($fetch_property['total_floors']); ?></span></p>
            <p><i>room floor :</i><span><?= htmlspecialchars($fetch_property['room_floor']); ?></span></p>
            <p><i>furnished :</i><span><?= htmlspecialchars($fetch_property['furnished']); ?></span></p>
            <p><i>loan :</i><span><?= htmlspecialchars($fetch_property['loan']); ?></span></p>
         </div>
      </div>
      <h3 class="title">amenities</h3>
      <div class="flex">
         <div class="box">
            <p><i class="fas fa-<?= ($fetch_property['lift'] == 'yes') ? 'check' : 'times'; ?>"></i><span>lifts</span></p>
            <p><i class="fas fa-<?= ($fetch_property['security_guard'] == 'yes') ? 'check' : 'times'; ?>"></i><span>security guards</span></p>
            <p><i class="fas fa-<?= ($fetch_property['play_ground'] == 'yes') ? 'check' : 'times'; ?>"></i><span>play ground</span></p>
            <p><i class="fas fa-<?= ($fetch_property['garden'] == 'yes') ? 'check' : 'times'; ?>"></i><span>gardens</span></p>
            <p><i class="fas fa-<?= ($fetch_property['water_supply'] == 'yes') ? 'check' : 'times'; ?>"></i><span>water supply</span></p>
            <p><i class="fas fa-<?= ($fetch_property['power_backup'] == 'yes') ? 'check' : 'times'; ?>"></i><span>power backup</span></p>
         </div>
         <div class="box">
            <p><i class="fas fa-<?= ($fetch_property['parking_area'] == 'yes') ? 'check' : 'times'; ?>"></i><span>parking area</span></p>
            <p><i class="fas fa-<?= ($fetch_property['gym'] == 'yes') ? 'check' : 'times'; ?>"></i><span>gym</span></p>
            <p><i class="fas fa-<?= ($fetch_property['shopping_mall'] == 'yes') ? 'check' : 'times'; ?>"></i><span>shopping mall</span></p>
            <p><i class="fas fa-<?= ($fetch_property['hospital'] == 'yes') ? 'check' : 'times'; ?>"></i><span>hospital</span></p>
            <p><i class="fas fa-<?= ($fetch_property['school'] == 'yes') ? 'check' : 'times'; ?>"></i><span>schools</span></p>
            <p><i class="fas fa-<?= ($fetch_property['market_area'] == 'yes') ? 'check' : 'times'; ?>"></i><span>market area</span></p>
         </div>
      </div>
      <h3 class="title">description</h3>
      <p class="description"><?= htmlspecialchars($fetch_property['description']); ?></p>
      <form action="" method="post" class="flex-btn">
         <input type="hidden" name="property_id" value="<?= htmlspecialchars($property_id); ?>">
         <?php if($select_saved->rowCount() > 0): ?>
            <button type="submit" name="save" class="save"><i class="fas fa-heart"></i><span>saved</span></button>
         <?php else: ?>
            <button type="submit" name="save" class="save"><i class="far fa-heart"></i><span>save</span></button>
         <?php endif; ?>
         
         <?php if($user_id != ''): ?>
            <a href="contact.php?property=<?= htmlspecialchars($fetch_property['id']); ?>" class="btn">send Inquiry</a>
         <?php else: ?>
            <a href="login.php" class="btn" onclick="event.preventDefault(); swal('Login Required', 'Please login first to send Inquiry', 'warning').then((value) => { window.location.href = 'login.php'; });">send Inquiry</a>
         <?php endif; ?>
      </form>
   </div>

   <!-- Recommended Properties Section -->
   <?php
      if($user_id != ''){
         if(isset($_SESSION['user_activity']) && !empty($_SESSION['user_activity'])){
            $property_ids = array_column($_SESSION['user_activity'], 'property_id');
            $placeholders = implode(',', array_fill(0, count($property_ids), '?'));
            $recommendation_sql = "
               SELECT * FROM `property` 
               WHERE address LIKE ? 
               AND type = ? 
               AND price BETWEEN ? AND ? 
               AND id NOT IN ($placeholders)
               LIMIT 5
            ";
            $stmt = $conn->prepare($recommendation_sql);
            $location = $fetch_property['address'];
            $type = $fetch_property['type'];
            $min_price = $fetch_property['price'] * 0.8;
            $max_price = $fetch_property['price'] * 1.2;
            $stmt->execute(array_merge([$location, $type, $min_price, $max_price], $property_ids));
            $recommended_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($recommended_properties) > 0){
   ?>
   <h2 class="heading">Recommended Properties</h2>
   <div class="recommendations">
      <?php foreach($recommended_properties as $recommended): ?>
      <div class="box">
         <h3><?= htmlspecialchars($recommended['property_name']); ?></h3>
         <p><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($recommended['address']); ?></p>
         <p><i class="fas fa-rupee-sign"></i><?= htmlspecialchars($recommended['price']); ?></p>
         <p><i class="fas fa-building"></i><?= htmlspecialchars($recommended['type']); ?></p>
         <a href="view_property.php?get_id=<?= htmlspecialchars($recommended['id']); ?>" class="btn">View Property</a>
      </div>
      <?php endforeach; ?>
   </div>
   <?php
            }else{
               echo '<p class="empty">No recommendations found!</p>';
            }
         }else{
            echo '<p class="empty">No activity found to generate recommendations!</p>';
         }
      }
   ?>
   <?php
         }else{
            echo '<p class="empty">property not found! <a href="post_property.php" style="margin-top:1.5rem;" class="btn">add new</a></p>';
         }
      }else{
         echo '<p class="empty">property not found! <a href="post_property.php" style="margin-top:1.5rem;" class="btn">add new</a></p>';
      }
   ?>

</section>

<!-- view property section ends -->

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>

<script>
var swiper = new Swiper(".images-container", {
   effect: "coverflow",
   grabCursor: true,
   centeredSlides: true,
   slidesPerView: "auto",
   loop:true,
   coverflowEffect: {
      rotate: 0,
      stretch: 0,
      depth: 200,
      modifier: 3,
      slideShadows: true,
   },
   pagination: {
      el: ".swiper-pagination",
   },
});
</script>

</body>
</html>