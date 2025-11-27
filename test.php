<?php
require 'sendmail.php';

$to = "buyer1122buy@gmail.com"; // Replace with your email for testing
$subject = "Test Email from Canteen";
$body = "<h3>Hello, this is a test email from real estate</h3>";

if (sendMail($to, $subject, $body)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>