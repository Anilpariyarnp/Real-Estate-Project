<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
   exit();
}

$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$errors = [];

if(isset($_POST['submit'])){
   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $old_pass = trim($_POST['old_pass']);
   $new_pass = trim($_POST['new_pass']);
   $c_pass = trim($_POST['c_pass']);

   // Validate name (letters only, 3-20 chars)
   if(!empty($name)){
      if(!preg_match("/^[a-zA-Z]+$/", $name)){
         $errors['name'] = 'Username can only contain letters';
      } elseif(strlen($name) < 3 || strlen($name) > 20){
         $errors['name'] = 'Username must be 3-20 characters';
      }
   }

   // Validate email
   if(!empty($email)){
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
         $errors['email'] = 'Invalid email format';
      }
   }

   // Password validation
   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709'; // SHA1 of empty string
   $prev_pass = $fetch_profile['password'];
   
   if(!empty($old_pass) || !empty($new_pass) || !empty($c_pass)){
      $old_pass_sha1 = sha1($old_pass);
      
      if(empty($old_pass)){
         $errors['old_pass'] = 'Old password is required';
      } elseif($old_pass_sha1 != $prev_pass){
         $errors['old_pass'] = 'Old password is incorrect';
      }
      
      if(!empty($new_pass) && strlen($new_pass) < 8){
         $errors['new_pass'] = 'New password must be at least 8 characters';
      } elseif(!empty($new_pass) && (!preg_match("/[A-Z]/", $new_pass) || !preg_match("/[0-9]/", $new_pass) || !preg_match("/[^A-Za-z0-9]/", $new_pass))){
         $errors['new_pass'] = 'Password must contain uppercase, number, and special character';
      }
      
      if($new_pass != $c_pass){
         $errors['c_pass'] = 'New passwords do not match';
      }
   }

   // If no errors, proceed with updates
   if(empty($errors)){
      // Update name if changed
      if(!empty($name) && $name != $fetch_profile['name']){
         $verify_name = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND id != ?");
         $verify_name->execute([$name, $admin_id]);
         
         if($verify_name->rowCount() > 0){
            $warning_msg[] = 'Username already taken!';
         }else{
            $update_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
            $update_name->execute([$name, $admin_id]);
            $success_msg[] = 'Username updated successfully!';
         }
      }

      // Update email if changed
      if(!empty($email) && $email != $fetch_profile['email']){
         $verify_email = $conn->prepare("SELECT * FROM `admins` WHERE email = ? AND id != ?");
         $verify_email->execute([$email, $admin_id]);
         
         if($verify_email->rowCount() > 0){
            $warning_msg[] = 'Email already taken!';
         }else{
            $update_email = $conn->prepare("UPDATE `admins` SET email = ? WHERE id = ?");
            $update_email->execute([$email, $admin_id]);
            $success_msg[] = 'Email updated successfully!';
         }
      }

      // Update password if changed
      if(!empty($new_pass) && !empty($c_pass) && $new_pass == $c_pass){
         $new_pass_sha1 = sha1($new_pass);
         if($new_pass_sha1 != $prev_pass){
            $update_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
            $update_pass->execute([$new_pass_sha1, $admin_id]);
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
   <link rel="stylesheet" href="../css/admin_style.css">
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
      .hint {
         color: #666;
         font-size: 12px;
         margin-top: 5px;
         display: block;
      }
   </style>
</head>
<body>
   
<?php include '../components/admin_header.php'; ?>

<section class="form-container">
   <form action="" method="POST" id="updateForm">
      <h3>Update Profile</h3>
      
      <div class="input-group">
         <input type="text" name="name" id="name" placeholder="<?= htmlspecialchars($fetch_profile['name']); ?>" 
                maxlength="20" class="box" oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '')"
                value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
         <?php if(isset($errors['name'])): ?>
            <span class="error"><?= $errors['name'] ?></span>
         <?php endif; ?>
         <small class="hint">Leave empty to keep current</small>
      </div>
      
      <div class="input-group">
         <input type="email" name="email" id="email" placeholder="<?= htmlspecialchars($fetch_profile['email']); ?>" 
                maxlength="255" class="box" oninput="this.value = this.value.replace(/\s/g, '')"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
         <?php if(isset($errors['email'])): ?>
            <span class="error"><?= $errors['email'] ?></span>
         <?php endif; ?>
         <small class="hint">Leave empty to keep current</small>
      </div>
      
      <div class="input-group">
         <input type="password" name="old_pass" id="old_pass" placeholder="Enter old password" maxlength="20" class="box">
         <?php if(isset($errors['old_pass'])): ?>
            <span class="error"><?= $errors['old_pass'] ?></span>
         <?php endif; ?>
         <small class="hint">Required if changing password</small>
      </div>
      
      <div class="input-group">
         <input type="password" name="new_pass" id="new_pass" placeholder="Enter new password" maxlength="20" class="box">
         <?php if(isset($errors['new_pass'])): ?>
            <span class="error"><?= $errors['new_pass'] ?></span>
         <?php endif; ?>
         <small class="hint">Must contain uppercase, number, and special character</small>
      </div>
      
      <div class="input-group">
         <input type="password" name="c_pass" id="c_pass" placeholder="Confirm new password" maxlength="20" class="box">
         <?php if(isset($errors['c_pass'])): ?>
            <span class="error"><?= $errors['c_pass'] ?></span>
         <?php endif; ?>
      </div>
      
      <input type="submit" value="Update Now" name="submit" class="btn">
   </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updateForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const oldPassInput = document.getElementById('old_pass');
    const newPassInput = document.getElementById('new_pass');
    const cPassInput = document.getElementById('c_pass');
    
    // Validate name on input
    nameInput.addEventListener('input', function() {
        const value = this.value.trim();
        if(value.length > 0 && !/^[a-zA-Z]+$/.test(value)) {
            showError(this, 'Username can only contain letters');
        } else if(value.length > 0 && (value.length < 3 || value.length > 20)) {
            showError(this, 'Username must be 3-20 characters');
        } else {
            clearError(this);
        }
    });
    
    // Validate email on input
    emailInput.addEventListener('input', function() {
        const value = this.value.trim();
        if(value.length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showError(this, 'Invalid email format');
        } else {
            clearError(this);
        }
    });
    
    // Password validation when changing password
    function validatePasswordFields() {
        const oldPassValue = oldPassInput.value.trim();
        const newPassValue = newPassInput.value.trim();
        const cPassValue = cPassInput.value.trim();
        
        // Only validate if any password field has content
        if(oldPassValue.length > 0 || newPassValue.length > 0 || cPassValue.length > 0) {
            if(oldPassValue.length === 0) {
                showError(oldPassInput, 'Old password is required');
            } else {
                clearError(oldPassInput);
            }
            
            if(newPassValue.length > 0 && newPassValue.length < 8) {
                showError(newPassInput, 'Password must be at least 8 characters');
            } else if(newPassValue.length > 0 && (!/[A-Z]/.test(newPassValue) || !/[0-9]/.test(newPassValue) || !/[^A-Za-z0-9]/.test(newPassValue))) {
                showError(newPassInput, 'Password must contain uppercase, number, and special character');
            } else {
                clearError(newPassInput);
            }
            
            if(newPassValue !== cPassValue) {
                showError(cPassInput, 'New passwords do not match');
            } else {
                clearError(cPassInput);
            }
        }
    }
    
    // Validate password fields on input
    oldPassInput.addEventListener('input', validatePasswordFields);
    newPassInput.addEventListener('input', validatePasswordFields);
    cPassInput.addEventListener('input', validatePasswordFields);
    
    function showError(input, message) {
        const errorElement = input.nextElementSibling;
        if(errorElement && errorElement.classList.contains('error')) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            input.style.borderColor = '#ff3860';
        }
    }
    
    function clearError(input) {
        const errorElement = input.nextElementSibling;
        if(errorElement && errorElement.classList.contains('error')) {
            errorElement.style.display = 'none';
            input.style.borderColor = '';
        }
    }
    
    // Form submission handler
    form.addEventListener('submit', function(e) {
        validatePasswordFields();
        
        // Check if any errors are visible
        const errors = form.querySelectorAll('.error[style="display: block;"]');
        if(errors.length > 0) {
            e.preventDefault();
            // Scroll to first error
            errors[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>

<?php include '../components/message.php'; ?>
</body>
</html>