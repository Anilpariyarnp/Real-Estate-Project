<?php  
session_start(); // Start the session
include 'components/connect.php';
include 'functions.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

include 'components/save_send.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- home section starts  -->
<div class="home">
   <section class="center">
      <form action="search.php" method="post">
         <h3>find your perfect home</h3>
         <div class="box">
            <p>enter location <span>*</span></p>
            <input type="text" name="h_location" required maxlength="100" placeholder="enter city name" class="input">
         </div>
         <div class="flex">
            <input type="submit" value="search property" name="h_search" class="btn">
         </div>
      </form>
   </section>
</div>
<!-- home section ends -->

<!-- recommended properties section starts  -->
<?php
if($user_id != ''){
   if(isset($_SESSION['user_activity']) && !empty($_SESSION['user_activity'])){
      $property_ids = array_column($_SESSION['user_activity'], 'property_id');
      $placeholders = implode(',', array_fill(0, count($property_ids), '?'));
      $recommendation_sql = "
         SELECT * FROM `property` 
         WHERE id NOT IN ($placeholders)
         ORDER BY date DESC
         LIMIT 6
      ";
      $stmt = $conn->prepare($recommendation_sql);
      $stmt->execute($property_ids);
      $recommended_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if(count($recommended_properties) > 0){
?>
<section class="listings">
   <h1 class="heading">recommended for you</h1>
   <div class="box-container">
      <?php
         foreach($recommended_properties as $recommended){
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_user->execute([$recommended['user_id']]);
            $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

            $image_count_02 = !empty($recommended['image_02']) ? 1 : 0;
            $image_count_03 = !empty($recommended['image_03']) ? 1 : 0;
            $image_count_04 = !empty($recommended['image_04']) ? 1 : 0;
            $image_count_05 = !empty($recommended['image_05']) ? 1 : 0;
            $total_images = (1 + $image_count_02 + $image_count_03 + $image_count_04 + $image_count_05);

            $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? and user_id = ?");
            $select_saved->execute([$recommended['id'], $user_id]);
      ?>
      <form action="" method="POST">
         <div class="box">
            <input type="hidden" name="property_id" value="<?= htmlspecialchars($recommended['id']); ?>">
            <?php if($select_saved->rowCount() > 0): ?>
               <button type="submit" name="save" class="save"><i class="fas fa-heart"></i><span>saved</span></button>
            <?php else: ?>
               <button type="submit" name="save" class="save"><i class="far fa-heart"></i><span>save</span></button>
            <?php endif; ?>
            <div class="thumb">
               <p class="total-images"><i class="far fa-image"></i><span><?= $total_images; ?></span></p> 
               <img src="uploaded_files/<?= htmlspecialchars($recommended['image_01']); ?>" alt="">
            </div>
            <div class="admin">
               <h3><?= substr(htmlspecialchars($fetch_user['name']), 0, 1); ?></h3>
               <div>
                  <p><?= htmlspecialchars($fetch_user['name']); ?></p>
                  <span><?= htmlspecialchars($recommended['date']); ?></span>
               </div>
            </div>
         </div>
         <div class="box">
            <div class="price"><i class="fas fa-rupee-sign"></i><span><?= htmlspecialchars($recommended['price']); ?></span></div>
            <h3 class="name"><?= htmlspecialchars($recommended['property_name']); ?></h3>
            <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($recommended['address']); ?></span></p>
            <div class="flex">
               <p><i class="fas fa-house"></i><span><?= htmlspecialchars($recommended['type']); ?></span></p>
               <p><i class="fas fa-tag"></i><span><?= htmlspecialchars($recommended['offer']); ?></span></p>
               <p><i class="fas fa-bed"></i><span><?= htmlspecialchars($recommended['bhk']); ?> BHK</span></p>
               <p><i class="fas fa-trowel"></i><span><?= htmlspecialchars($recommended['status']); ?></span></p>
               <p><i class="fas fa-couch"></i><span><?= htmlspecialchars($recommended['furnished']); ?></span></p>
               <p><i class="fas fa-maximize"></i><span><?= htmlspecialchars($recommended['carpet']); ?>sqft</span></p>
            </div>
            <div class="flex-btn">
               <a href="view_property.php?get_id=<?= htmlspecialchars($recommended['id']); ?>" class="btn">view property</a>
               <?php if($user_id != ''): ?>
                  <a href="contact.php?property=<?= htmlspecialchars($recommended['id']); ?>" class="btn">send Inquiry</a>
               <?php else: ?>
                  <a href="login.php" class="btn" onclick="event.preventDefault(); swal('Login Required', 'Please login first to send inquiry', 'warning').then((value) => { window.location.href = 'login.php'; });">send Inquiry</a>
               <?php endif; ?>
            </div>
         </div>
      </form>
      <?php
         }
      ?>
   </div>
</section>
<?php
      }
   }
}
?>

<!-- latest listings section starts  -->
<section class="listings">
   <h1 class="heading">latest listings</h1>
   <div class="box-container">
      <?php
         $select_properties = $conn->prepare("SELECT * FROM `property` ORDER BY date DESC LIMIT 6");
         $select_properties->execute();

         if($select_properties->rowCount() > 0){
            while($fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC)){
               $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
               $select_user->execute([$fetch_property['user_id']]);
               $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Unknown User'];

               $image_count_02 = !empty($fetch_property['image_02']) ? 1 : 0;
               $image_count_03 = !empty($fetch_property['image_03']) ? 1 : 0;
               $image_count_04 = !empty($fetch_property['image_04']) ? 1 : 0;
               $image_count_05 = !empty($fetch_property['image_05']) ? 1 : 0;
               $total_images = (1 + $image_count_02 + $image_count_03 + $image_count_04 + $image_count_05);

               $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? and user_id = ?");
               $select_saved->execute([$fetch_property['id'], $user_id]);
      ?>
      <form action="" method="POST">
         <div class="box">
            <input type="hidden" name="property_id" value="<?= htmlspecialchars($fetch_property['id']); ?>">
            <?php if($select_saved->rowCount() > 0): ?>
               <button type="submit" name="save" class="save"><i class="fas fa-heart"></i><span>saved</span></button>
            <?php else: ?>
               <button type="submit" name="save" class="save"><i class="far fa-heart"></i><span>save</span></button>
            <?php endif; ?>
            <div class="thumb">
               <p class="total-images"><i class="far fa-image"></i><span><?= $total_images; ?></span></p> 
               <img src="uploaded_files/<?= htmlspecialchars($fetch_property['image_01']); ?>" alt="">
            </div>
            <div class="admin">
               <h3><?= substr(htmlspecialchars($fetch_user['name']), 0, 1); ?></h3>
               <div>
                  <p><?= htmlspecialchars($fetch_user['name']); ?></p>
                  <span><?= htmlspecialchars($fetch_property['date']); ?></span>
               </div>
            </div>
         </div>
         <div class="box">
            <div class="price"><i class="fas fa-rupee-sign"></i><span><?= htmlspecialchars($fetch_property['price']); ?></span></div>
            <h3 class="name"><?= htmlspecialchars($fetch_property['property_name']); ?></h3>
            <p class="location"><i class="fas fa-map-marker-alt"></i><span><?= htmlspecialchars($fetch_property['address']); ?></span></p>
            <div class="flex">
               <p><i class="fas fa-house"></i><span><?= htmlspecialchars($fetch_property['type']); ?></span></p>
               <p><i class="fas fa-tag"></i><span><?= htmlspecialchars($fetch_property['offer']); ?></span></p>
               <p><i class="fas fa-bed"></i><span><?= htmlspecialchars($fetch_property['bhk']); ?> BHK</span></p>
               <p><i class="fas fa-trowel"></i><span><?= htmlspecialchars($fetch_property['status']); ?></span></p>
               <p><i class="fas fa-couch"></i><span><?= htmlspecialchars($fetch_property['furnished']); ?></span></p>
               <p><i class="fas fa-maximize"></i><span><?= htmlspecialchars($fetch_property['carpet']); ?>sqft</span></p>
            </div>
            <div class="flex-btn">
               <a href="view_property.php?get_id=<?= htmlspecialchars($fetch_property['id']); ?>" class="btn">view property</a>
               <?php if($user_id != ''): ?>
                  <a href="contact.php?property=<?= htmlspecialchars($fetch_property['id']); ?>" class="btn">send inquiry</a>
               <?php else: ?>
                  <a href="login.php" class="btn" onclick="event.preventDefault(); swal('Login Required', 'Please login first to send inquiry', 'warning').then((value) => { window.location.href = 'login.php'; });">send Inquiry</a>
               <?php endif; ?>
            </div>
         </div>
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">No properties added yet! <a href="post_property.php" style="margin-top:1.5rem;" class="btn">Add New</a></p>';
         }
      ?>
   </div>
   <div style="margin-top: 2rem; text-align:center;">
      <a href="listings.php" class="inline-btn">view all</a>
   </div>
</section>
<!-- latest listings section ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>

<script>
   let range = document.querySelector("#range");
   range.oninput = () =>{
      document.querySelector('#output').innerHTML = range.value;
   }
</script>

</body>
</html> 

