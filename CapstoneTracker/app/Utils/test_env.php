<?php
// test_env.php - Comprehensive test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email System Test</h2>";

// Use relative path from current directory
$emailSenderPath = __DIR__ . '/EmailSender.php';
echo "Looking for EmailSender at: " . $emailSenderPath . "<br>";

if (!file_exists($emailSenderPath)) {
    die("❌ EmailSender.php not found at: " . $emailSenderPath);
}

echo "✅ EmailSender.php found!<br>";

// Test if we can require the file
try {
    require_once $emailSenderPath;
    echo "✅ EmailSender.php required successfully!<br>";
} catch (Exception $e) {
    die("❌ Failed to require EmailSender.php: " . $e->getMessage());
}

// Test if class exists
if (class_exists('EmailSender')) {
    echo "✅ EmailSender class exists!<br>";
} else {
    die("❌ EmailSender class not found after requiring file!");
}

// Test if we can instantiate the class
try {
    $emailSender = new EmailSender();
    echo "✅ EmailSender instantiated successfully!<br>";
} catch (Exception $e) {
    die("❌ Failed to instantiate EmailSender: " . $e->getMessage());
}

// Test email configuration
echo "<h3>Testing Email Configuration</h3>";
try {
    echo "Host: " . EmailConfig::SMTP_HOST . "<br>";
    echo "Port: " . EmailConfig::SMTP_PORT . "<br>";
    echo "Username: " . EmailConfig::SMTP_USERNAME . "<br>";
    echo "From Email: " . EmailConfig::SMTP_FROM_EMAIL . "<br>";
    echo "From Name: " . EmailConfig::SMTP_FROM_NAME . "<br>";
    echo "✅ Email configuration loaded successfully!<br>";
} catch (Exception $e) {
    die("❌ Failed to load email configuration: " . $e->getMessage());
}

// Test PHPMailer availability
echo "<h3>Testing PHPMailer</h3>";
if ($emailSender->mailerAvailable) {
    echo "✅ PHPMailer is available!<br>";
} else {
    echo "⚠️ PHPMailer is not available (using stubs)<br>";
}

// Test email sending (commented out for safety - uncomment to actually send)
echo "<h3>Testing Email Sending</h3>";
echo "<p><em>Email sending test is commented out for safety.</em></p>";
echo "<p><em>Uncomment the code below to test actual email sending.</em></p>";

/*
try {
    $result = $emailSender->sendWelcomeEmail('test@example.com', 'Test User', 'test123', 'student');
    
    if ($result) {
        echo "✅ Test email sent successfully!";
    } else {
        echo "❌ Failed to send test email. Check error logs.";
    }
} catch (Exception $e) {
    echo "❌ Error sending test email: " . $e->getMessage();
}
*/

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<p>If all checks above show ✅, your email system is configured correctly.</p>";
echo "<p>To test actual email sending, uncomment the email sending code in this test file.</p>";
?>