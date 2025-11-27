<?php  
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

// Initialize error array
$errors = [];

if(isset($_POST['send'])){

   $msg_id = create_unique_id();
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

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
   $nepal_pattern3 = '/^\+97798\d{8}$/'; // +97798xxxxxxx (13 digits)
   $nepal_pattern4 = '/^\+97797\d{8}$/'; // +97797xxxxxxx (13 digits)
   
   if(empty($number)) {
       $errors['number'] = 'Phone number is required';
   } elseif(!preg_match($nepal_pattern1, $number) && 
            !preg_match($nepal_pattern2, $number) && 
            !preg_match($nepal_pattern3, $number) && 
            !preg_match($nepal_pattern4, $number)) {
       $errors['number'] = 'Invalid Nepal phone number (must start with 98/97 or +97798/+97797)';
   }

   // Validate message
   if(empty($message)) {
       $errors['message'] = 'Message is required';
   } elseif(strlen($message) < 10) {
       $errors['message'] = 'Message should be at least 10 characters';
   } elseif(preg_match('/<[^>]*>/', $message)) {
       $errors['message'] = 'HTML tags are not allowed';
   }

   // If no errors, proceed with database operations
   if(empty($errors)) {
       $verify_contact = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
       $verify_contact->execute([$name, $email, $number, $message]);

       if($verify_contact->rowCount() > 0){
           $warning_msg[] = 'Message already sent!';
       } else {
           $send_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
           $send_message->execute([$msg_id, $name, $email, $number, $message]);
           
           // Send automatic email to user
           sendMail($email, "Thank You for Your Inquiry", "Hello $name, <br>Thank you for reaching out! We have received your message and will get back to you soon.<br><br>Best Regards,<br>Real Estate Support Team");

           $success_msg[] = 'Message sent successfully!';
       }
   }
}

function sendMail($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'anil22333pariyar@gmail.com';  
        $mail->Password = 'duurlrwwcznxcoyx';   
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('pariyarrupa986@gmail.com', 'Real Estate Support');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact Us</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="contact">
   <div class="row">
      <div class="image">
         <img src="images/contact-img.svg" alt="">
      </div>
      <form action="" method="post">
         <h3>Get in Touch</h3>
         
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
            <textarea name="message" placeholder="Enter your message (min 10 characters)" required maxlength="1000" cols="30" rows="10" class="box"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
            <?php if(isset($errors['message'])): ?>
               <span class="error"><?= $errors['message'] ?></span>
            <?php endif; ?>
         </div>
         
         <input type="submit" value="Send Message" name="send" class="btn">
      </form>
   </div>
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
</body>
</html>