<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

// Initialize error array
$errors = [];

if(isset($_POST['submit'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $number = $_POST['number'];
   $email = $_POST['email'];
   $user_type = $_POST['user_type'];
   $pass = $_POST['pass'];
   $c_pass = $_POST['c_pass'];

   // Validate name (only letters and spaces)
   if(empty($name)) {
       $errors['name'] = 'Name is required';
   } elseif(!preg_match("/^[a-zA-Z ]*$/", $name)) {
       $errors['name'] = 'Only letters and spaces allowed';
   }

   // Validate email
   if(empty($email)) {
       $errors['email'] = 'Email is required';
   } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $errors['email'] = 'Invalid email format';
   }

   // Validate phone number (Nepal format)
   $nepal_pattern1 = '/^98\d{8}$/'; // 98xxxxxxxx (10 digits)
   $nepal_pattern2 = '/^97\d{8}$/'; // 97xxxxxxxx (10 digits)
   
   if(empty($number)) {
       $errors['number'] = 'Phone number is required';
   } elseif(!preg_match($nepal_pattern1, $number) && !preg_match($nepal_pattern2, $number)) {
       $errors['number'] = 'Invalid Nepal phone number (must start with 98 or 97)';
   }

   // Validate user type
   if(empty($user_type) || !in_array($user_type, ['buyer', 'seller'])) {
       $errors['user_type'] = 'Please select a valid account type';
   }

   // Validate password
   if(empty($pass)) {
       $errors['pass'] = 'Password is required';
   } elseif(strlen($pass) < 8) {
       $errors['pass'] = 'Password must be at least 8 characters';
   }

   // Validate confirm password
   if(empty($c_pass)) {
       $errors['c_pass'] = 'Please confirm your password';
   } elseif($pass != $c_pass) {
       $errors['c_pass'] = 'Passwords do not match';
   }

   // If no errors, proceed with registration
   if(empty($errors)) {
       // Sanitize inputs
       $name = filter_var($name, FILTER_SANITIZE_STRING); 
       $number = filter_var($number, FILTER_SANITIZE_STRING);
       $email = filter_var($email, FILTER_SANITIZE_STRING);
       $user_type = filter_var($user_type, FILTER_SANITIZE_STRING);
       $pass = sha1($pass);
       $c_pass = sha1($c_pass);

       $select_users = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
       $select_users->execute([$email]);

       if($select_users->rowCount() > 0){
          $warning_msg[] = 'Email already taken!';
       } else {
          $insert_user = $conn->prepare("INSERT INTO `users`(id, name, number, email, password, user_type) VALUES(?,?,?,?,?,?)");
          $insert_user->execute([$id, $name, $number, $email, $c_pass, $user_type]);
          
          if($insert_user){
             $verify_users = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
             $verify_users->execute([$email, $pass]);
             $row = $verify_users->fetch(PDO::FETCH_ASSOC);
          
             if($verify_users->rowCount() > 0){
                setcookie('user_id', $row['id'], time() + 60*60*24*30, '/');
                setcookie('user_type', $row['user_type'], time() + 60*60*24*30, '/');
                header('location:home.php');
                exit();
             }else{
                $error_msg[] = 'Something went wrong!';
             }
          }
       }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .error {
         color: #ff3860;
         font-size: 12px;
         margin-top: 5px;
         display: block;
      }
      .input-group {
         margin-bottom: 15px;
      }
   </style>
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- register section starts  -->
<section class="form-container">
   <form action="" method="post">
      <h3>Create an account!</h3>
      
      <div class="input-group">
    <input type="text" name="name" id="name" required maxlength="50" placeholder="Enter your name" class="box" 
           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
           oninput="validateName(this)">
    <?php if(isset($errors['name'])): ?>
       <span class="error"><?= $errors['name'] ?></span>
    <?php endif; ?>
    <span id="nameError" class="error" style="display:none;"></span>
</div>

<script>
function validateName(input) {
    const nameError = document.getElementById('nameError');
    const nameRegex = /^[A-Za-z\s]+$/;
    
    if (input.value === '') {
        nameError.style.display = 'none';
        return true;
    }
    
    if (!nameRegex.test(input.value)) {
        nameError.textContent = 'Only letters and spaces are allowed';
        nameError.style.display = 'block';
        return false;
    } else {
        nameError.style.display = 'none';
        return true;
    }
}

// Optional: Add validation on form submit
document.querySelector('form').addEventListener('submit', function(e) {
    const nameInput = document.getElementById('name');
    if (!validateName(nameInput)) {
        e.preventDefault();
    }
});
</script>
      
      <div class="input-group">
    <input type="email" name="email" required maxlength="50" placeholder="Enter your email" class="box" 
           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
           pattern="^[a-zA-Z0-9._%+-]+@(gmail\.com|yahoo\.com|hotmail\.com|outlook\.com|[a-zA-Z0-9-]+\.[a-zA-Z]{2,})$"
           oninput="validateStrictEmail(this)">
    <span id="email-feedback" class="feedback"></span>
    <?php if(isset($errors['email'])): ?>
        <span class="error"><?= $errors['email'] ?></span>
    <?php endif; ?>
</div>
      
<div class="input-group">
    <input type="text" name="number" id="number" required maxlength="10" placeholder="Enter your number (98/97...)" class="box" 
           value="<?= isset($_POST['number']) ? htmlspecialchars($_POST['number']) : '' ?>"
           oninput="validateNumber(this)">
    <?php if(isset($errors['number'])): ?>
       <span class="error"><?= $errors['number'] ?></span>
    <?php endif; ?>
    <span id="numberError" class="error" style="display:none;"></span>
</div>

<script>
function validateNumber(input) {
    const numberError = document.getElementById('numberError');
    const value = input.value;
    
    // Remove any non-digit characters
    const cleanedValue = value.replace(/\D/g, '');
    if (cleanedValue !== value) {
        input.value = cleanedValue; // Update the input with cleaned value
    }
    
    // Validate length and format
    if (value === '') {
        numberError.style.display = 'none';
        return true;
    }
    
    if (value.length !== 10) {
        numberError.textContent = 'Phone number must be 10 digits';
        numberError.style.display = 'block';
        return false;
    }
    
    if (!value.startsWith('98') && !value.startsWith('97')) {
        numberError.textContent = 'Phone number must start with 98 or 97';
        numberError.style.display = 'block';
        return false;
    }
    
    numberError.style.display = 'none';
    return true;
}

// Optional: Add validation on form submit
document.querySelector('form').addEventListener('submit', function(e) {
    const numberInput = document.getElementById('number');
    if (!validateNumber(numberInput)) {
        e.preventDefault();
    }
});
</script>
      
      <div class="input-group">
         <select name="user_type" class="box" required>
            <option value="" disabled selected>Select Account Type</option>
            <option value="buyer" <?= (isset($_POST['user_type']) && $_POST['user_type'] == 'buyer' ? 'selected' : '') ?>>Buyer</option>
            <option value="seller" <?= (isset($_POST['user_type']) && $_POST['user_type'] == 'seller' ? 'selected' : '') ?>>Seller</option>
         </select>
         <?php if(isset($errors['user_type'])): ?>
            <span class="error"><?= $errors['user_type'] ?></span>
         <?php endif; ?>
      </div>
      
      <div class="input-group">
         <input type="password" name="pass" required maxlength="20" placeholder="Enter your password (min 8 chars)" class="box">
         <?php if(isset($errors['pass'])): ?>
            <span class="error"><?= $errors['pass'] ?></span>
         <?php endif; ?>
      </div>
      
      <div class="input-group">
         <input type="password" name="c_pass" required maxlength="20" placeholder="Confirm your password" class="box">
         <?php if(isset($errors['c_pass'])): ?>
            <span class="error"><?= $errors['c_pass'] ?></span>
         <?php endif; ?>
      </div>
      
      <p>Already have an account? <a href="login.php">Login now</a></p>
      <input type="submit" value="Register Now" name="submit" class="btn">
   </form>
</section>
<!-- register section ends -->
<script>
// List of allowed email domains
const ALLOWED_DOMAINS = [
    'gmail.com',
    'yahoo.com',
    'hotmail.com',
    'outlook.com',
    'icloud.com',
    'protonmail.com'
    // Add other domains you want to allow
];

function validateStrictEmail(input) {
    const feedback = document.getElementById('email-feedback');
    const email = input.value.trim();
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    // Clear feedback if empty
    if (!email) {
        feedback.textContent = '';
        feedback.className = 'feedback';
        return;
    }
    
    // Basic format validation
    if (!emailRegex.test(email)) {
        feedback.textContent = 'Please enter a valid email format (e.g., user@example.com)';
        feedback.className = 'feedback error';
        return;
    }
    
    // Extract domain
    const domain = email.split('@')[1].toLowerCase();
    
    // Check against allowed domains
    if (!ALLOWED_DOMAINS.some(allowed => domain === allowed || domain.endsWith('.' + allowed))) {
        feedback.textContent = 'Please use a valid email provider (Gmail, Yahoo, etc.)';
        feedback.className = 'feedback error';
        return;
    }
    
    // Check for common typos in allowed domains
    const COMMON_TYPOS = {
        'gmial.com': 'gmail.com',
        'gmal.com': 'gmail.com',
        'gmail.cm': 'gmail.com',
        'yaho.com': 'yahoo.com',
        'hotmal.com': 'hotmail.com',
        'outook.com': 'outlook.com'
    };
    
    if (COMMON_TYPOS[domain]) {
        const corrected = email.replace(domain, COMMON_TYPOS[domain]);
        feedback.textContent = `Did you mean ${corrected}?`;
        feedback.className = 'feedback warning';
        return;
    }
    
    // If everything checks out
    feedback.textContent = 'Valid email address';
    feedback.className = 'feedback success';
}

// Add form submission validation
document.querySelector('form').addEventListener('submit', function(e) {
    const emailInput = this.querySelector('input[name="email"]');
    validateStrictEmail(emailInput);
    
    const feedback = document.getElementById('email-feedback');
    if (feedback.classList.contains('error')) {
        e.preventDefault();
    }
});
</script>

<style>
.feedback {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
}
.feedback.error { color: #dc3545; }
.feedback.warning { color: #ffc107; }
.feedback.success { color: #28a745; }
.error { color: #dc3545; font-size: 0.8rem; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
</body>
</html>