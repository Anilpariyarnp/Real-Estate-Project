<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['submit'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); 
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_EMAIL); 
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING); 
   $c_pass = sha1($_POST['c_pass']);
   $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING);   

   // Check if the username or email already exists
   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name = ? OR email = ?");
   $select_admins->execute([$name, $email]);

   if($select_admins->rowCount() > 0){
      $warning_msg[] = 'Username or email already taken!';
   }else{
      if($pass != $c_pass){
         $warning_msg[] = 'Password not matched!';
      }else{
         // Insert the new admin into the database
         $insert_admin = $conn->prepare("INSERT INTO `admins`(id, name, email, password) VALUES(?,?,?,?)");
         $insert_admin->execute([$id, $name, $email, $c_pass]);
         $success_msg[] = 'Registered successfully!';
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

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php include '../components/admin_header.php'; ?>
<!-- header section ends -->

<!-- register section starts  -->

<section class="form-container">

   <form action="" method="POST">
      <h3>Register New Admin</h3>
      
      <!-- Username Field -->
      <input type="text" name="name" placeholder="Enter username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Email Field -->
      <input type="email" name="email" placeholder="Enter email" maxlength="255" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Password Field -->
      <input type="password" name="pass" placeholder="Enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Confirm Password Field -->
      <input type="password" name="c_pass" placeholder="Confirm password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Submit Button -->
      <input type="submit" value="Register Now" name="submit" class="btn">
   </form>

</section>

<!-- register section ends -->

<!-- SweetAlert for displaying messages -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

<!-- Include message component for displaying warnings or success messages -->
<?php include '../components/message.php'; ?>

</body>
</html>