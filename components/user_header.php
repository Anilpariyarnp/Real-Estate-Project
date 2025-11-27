<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch user info
$user_type = isset($_COOKIE['user_type']) ? $_COOKIE['user_type'] : '';
$user_id = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : '';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<header class="header">

   <nav class="navbar nav-1">
      <section class="flex">
         <a href="home.php" class="logo"><i class="fas fa-house"></i>MyHome</a>

         <ul>
            <?php if($user_type == 'seller'): ?>
               <li><a href="post_property.php">Post Property <i class="fas fa-paper-plane"></i></a></li>
            <?php elseif($user_type == 'buyer'): ?>
               <li class="welcome-message">Real Home Nepal</li>
            <?php else: ?>
               <li class="welcome-message">Welcome to Real Home Nepal</li>
            <?php endif; ?>
         </ul>
      </section>
   </nav>

   <nav class="navbar nav-2">
      <section class="flex">
         <div id="menu-btn" class="fas fa-bars"></div>

         <div class="menu">
            <ul>
               <?php if($user_type == 'seller'): ?>
               <li><a href="#">My Listings <i class="fas fa-angle-down"></i></a>
                  <ul>
                     <li><a href="dashboard.php">Dashboard</a></li>
                     <li><a href="my_listings.php">My Listings</a></li>
                  </ul>
               </li>
               <?php endif; ?>
               <li><a href="#">Options <i class="fas fa-angle-down"></i></a>
                  <ul>
                     <li><a href="listings.php">All Listings</a></li>
                  </ul>
               </li>
               <li><a href="#">Help <i class="fas fa-angle-down"></i></a>
                  <ul>
                     <li><a href="about.php">About Us</a></li>
                     <li><a href="contact.php">Contact Us</a></li>
                     <li><a href="contact.php#faq">FAQ</a></li>
                  </ul>
               </li>
            </ul>
         </div>

         <ul>
            <li><a href="saved.php">Saved <i class="far fa-heart"></i></a></li>
            <li><a href="#">Account <i class="fas fa-angle-down"></i></a>
               <ul>
                  <?php if($user_id): ?>
                     <li><a href="update.php">Update Profile</a></li>
                     <li><a href="components/user_logout.php" onclick="return confirm('Logout from this website?');">Logout</a></li>
                  <?php else: ?>
                     <li><a href="login.php">Login Now</a></li>
                     <li><a href="register.php">Register New</a></li>
                  <?php endif; ?>
               </ul>
            </li>
         </ul>
      </section>
   </nav>

</header>

<style>
.welcome-message {
    color: var(--black);
    font-weight: 600;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    padding: 0 0.8rem;
    white-space: nowrap;
    color : green:
}

@media (max-width: 768px) {
    .welcome-message {
        font-size: 1.4rem;
        padding: 0 1rem;
    }
}
</style>