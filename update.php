<?php  
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
   exit();
}

$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

// Initialize error array
$errors = [];

if(isset($_POST['submit'])){
   $name = trim($_POST['name']);
   $number = trim($_POST['number']);
   $email = trim($_POST['email']);
   $old_pass = trim($_POST['old_pass']);
   $new_pass = trim($_POST['new_pass']);
   $c_pass = trim($_POST['c_pass']);

   // Validate name (only letters and spaces)
   if(!empty($name)){
      if(!preg_match("/^[a-zA-Z ]*$/", $name)) {
         $errors['name'] = 'Only letters and spaces allowed';
      } else {
         $name = filter_var($name, FILTER_SANITIZE_STRING);
      }
   }

   // Validate email
   if(!empty($email)){
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $errors['email'] = 'Invalid email format';
      } else {
         $email = filter_var($email, FILTER_SANITIZE_STRING);
      }
   }

   // Validate phone number (Nepal format)
   if(!empty($number)){
      $nepal_pattern1 = '/^98\d{8}$/'; // 98xxxxxxxx (10 digits)
      $nepal_pattern2 = '/^97\d{8}$/'; // 97xxxxxxxx (10 digits)
      
      if(!preg_match($nepal_pattern1, $number) && !preg_match($nepal_pattern2, $number)) {
         $errors['number'] = 'Invalid Nepal phone number (must start with 98 or 97)';
      } else {
         $number = filter_var($number, FILTER_SANITIZE_STRING);
      }
   }

   // Password validation
   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709'; // SHA1 of empty string
   $prev_pass = $fetch_user['password'];
   
   if(!empty($old_pass) || !empty($new_pass) || !empty($c_pass)){
      $old_pass_sha1 = sha1($old_pass);
      
      if($old_pass_sha1 != $prev_pass){
         $errors['old_pass'] = 'Old password is incorrect';
      }
      
      if(strlen($new_pass) < 8){
         $errors['new_pass'] = 'New password must be at least 8 characters';
      }
      
      if($new_pass != $c_pass){
         $errors['c_pass'] = 'New passwords do not match';
      }
   }

   // If no errors, proceed with updates
   if(empty($errors)){
      // Update name if changed
      if(!empty($name) && $name != $fetch_user['name']){
         $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
         $update_name->execute([$name, $user_id]);
         $success_msg[] = 'Name updated successfully!';
      }

      // Update email if changed
      if(!empty($email) && $email != $fetch_user['email']){
         $verify_email = $conn->prepare("SELECT email FROM `users` WHERE email = ? AND id != ?");
         $verify_email->execute([$email, $user_id]);
         
         if($verify_email->rowCount() > 0){
            $warning_msg[] = 'Email already taken!';
         }else{
            $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id = ?");
            $update_email->execute([$email, $user_id]);
            $success_msg[] = 'Email updated successfully!';
         }
      }

      // Update number if changed
      if(!empty($number) && $number != $fetch_user['number']){
         $verify_number = $conn->prepare("SELECT number FROM `users` WHERE number = ? AND id != ?");
         $verify_number->execute([$number, $user_id]);
         
         if($verify_number->rowCount() > 0){
            $warning_msg[] = 'Number already taken!';
         }else{
            $update_number = $conn->prepare("UPDATE `users` SET number = ? WHERE id = ?");
            $update_number->execute([$number, $user_id]);
            $success_msg[] = 'Number updated successfully!';
         }
      }

      // Update password if changed
      if(!empty($new_pass) && !empty($c_pass) && $new_pass == $c_pass){
         $new_pass_sha1 = sha1($new_pass);
         if($new_pass_sha1 != $prev_pass){
            $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $update_pass->execute([$new_pass_sha1, $user_id]);
            $success_msg[] = 'Password updated successfully!';
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
   <title>Update Profile</title>
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

<section class="form-container">
   <form action="" method="post">
      <h3>Update your account!</h3>
      
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
      <input type="email" name="email" required maxlength="50" placeholder="<?= htmlspecialchars($fetch_user['email']); ?>" class="box" 
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
         <input type="password" name="old_pass" maxlength="20" placeholder="Enter your old password" class="box">
         <?php if(isset($errors['old_pass'])): ?>
            <span class="error"><?= $errors['old_pass'] ?></span>
         <?php endif; ?>
      </div>
      
      <div class="input-group">
         <input type="password" name="new_pass" maxlength="20" placeholder="Enter new password (min 8 chars)" class="box">
         <?php if(isset($errors['new_pass'])): ?>
            <span class="error"><?= $errors['new_pass'] ?></span>
         <?php endif; ?>
      </div>
      
      <div class="input-group">
         <input type="password" name="c_pass" maxlength="20" placeholder="Confirm new password" class="box">
         <?php if(isset($errors['c_pass'])): ?>
            <span class="error"><?= $errors['c_pass'] ?></span>
         <?php endif; ?>
      </div>
      
      <input type="submit" value="Update Now" name="submit" class="btn">
   </form>
</section>
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