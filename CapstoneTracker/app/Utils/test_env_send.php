<?php
// test_env_send.php - Test with actual email sending
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email System Test - With Sending</h2>";

// Use relative path from current directory
$emailSenderPath = __DIR__ . '/EmailSender.php';
echo "Looking for EmailSender at: " . $emailSenderPath . "<br>";

if (!file_exists($emailSenderPath)) {
    die("❌ EmailSender.php not found at: " . $emailSenderPath);
}

echo "✅ EmailSender.php found!<br>";

require_once $emailSenderPath;

// Test email sending to a real email address
$testEmail = 'your-email@gmail.com'; // CHANGE THIS TO YOUR ACTUAL EMAIL
$testName = 'Test User';
$testPassword = 'TempPass123';
$testRole = 'student';

try {
    $emailSender = new EmailSender();
    
    echo "<h3>Sending Test Email to: " . $testEmail . "</h3>";
    
    $result = $emailSender->sendWelcomeEmail($testEmail, $testName, $testPassword, $testRole);
    
    if ($result) {
        echo "<div style='color: green; font-weight: bold;'>✅ Test email sent successfully!</div>";
        echo "<p>Check your email inbox (and spam folder) for the test message.</p>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Failed to send test email.</div>";
        echo "<p>Check the error logs for more information.</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</div>";
}

// Display debug information
echo "<hr><h3>Debug Information:</h3>";
echo "PHPMailer Available: " . ($emailSender->mailerAvailable ? 'Yes' : 'No') . "<br>";
echo "SMTP Host: " . EmailConfig::SMTP_HOST . "<br>";
echo "SMTP Port: " . EmailConfig::SMTP_PORT . "<br>";
echo "SMTP Username: " . EmailConfig::SMTP_USERNAME . "<br>";
?>