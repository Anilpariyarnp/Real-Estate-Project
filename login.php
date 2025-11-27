<?php

include 'components/connect.php';


if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_POST['submit'])){

  $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
  $pass = sha1($_POST['pass']);
  $login_type = $_POST['login_type']; // Get the selected login type

  $select_users = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? AND user_type = ? LIMIT 1");
  $select_users->execute([$email, $pass, $login_type]);
  $row = $select_users->fetch(PDO::FETCH_ASSOC);

  if($select_users->rowCount() > 0){
     setcookie('user_id', $row['id'], time() + 60*60*24*30, '/');
     setcookie('user_type', $row['user_type'], time() + 60*60*24*30, '/'); // Store user type in cookie

     header('location:home.php'); // Redirect to homepage
  }else{
     $warning_msg[] = 'Incorrect username, password, or user type!';
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
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- login section starts  -->

<section class="form-container">

   <form action="" method="post">
      <h3>welcome back!</h3>
      <select name="login_type" class="box" required>
    <option value="" disabled selected>Login as</option>
    <option value="buyer">Buyer</option>
    <option value="seller">Seller</option>
</select>

      <input type="email" name="email" required maxlength="50" placeholder="enter your email" class="box">
      <input type="password" name="pass" required maxlength="20" placeholder="enter your password" class="box">
      <p>don't have an account? <a href="register.php">register new</a></p>
      <input type="submit" value="login now" name="submit" class="btn">
   </form>

</section>

<!-- login section ends -->










<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>