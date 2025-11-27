<?php

include '../components/connect.php';

if(isset($_POST['submit'])){

   $name = trim($_POST['name']);
   $name = filter_var($name, FILTER_SANITIZE_STRING); 
   $email = trim($_POST['email']);
   $email = filter_var($email, FILTER_SANITIZE_EMAIL); 
   $pass = sha1(trim($_POST['pass']));
   $pass = filter_var($pass, FILTER_SANITIZE_STRING); 

   // Check if the admin exists with the provided name, email, and password
   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND email = ? AND password = ? LIMIT 1");
   $select_admins->execute([$name, $email, $pass]);
   $row = $select_admins->fetch(PDO::FETCH_ASSOC);

   if($select_admins->rowCount() > 0){
      // Set a cookie for admin ID and redirect to the dashboard
      setcookie('admin_id', $row['id'], time() + 60*60*24*30, '/');
      header('location:dashboard.php');
   }else{
      $warning_msg[] = 'Incorrect username, email, or password!';
   }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body style="padding-left: 0;">

<!-- login section starts  -->

<section class="form-container" style="min-height: 100vh;">

   <form action="" method="POST">
      <h3>Welcome Back!</h3>
      <!-- <p>Default name = <span>admin</span>, email = <span>admin@example.com</span> & password = <span>111</span></p> -->
      
      <!-- Username Field -->
      <input type="text" name="name" placeholder="Enter username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Email Field -->
      <input type="email" name="email" placeholder="Enter email" maxlength="255" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Password Field -->
      <input type="password" name="pass" placeholder="Enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Submit Button -->
      <input type="submit" value="Login Now" name="submit" class="btn">
   </form>

</section>

<!-- login section ends -->

<!-- SweetAlert for displaying messages -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- Include message component for displaying warnings or success messages -->
<?php include '../components/message.php'; ?>

</body>
</html>