<?php
// detailed_smtp_debug.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Detailed SMTP Debug Test</h2>";

$emailSenderPath = __DIR__ . '/EmailSender.php';
require_once $emailSenderPath;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $emailSender = new EmailSender();
    
    echo "<h3>Configuration Check:</h3>";
    echo "PHPMailer Available: " . ($emailSender->isMailerAvailable() ? 'Yes ✅' : 'No ❌') . "<br>";
    echo "SMTP Host: " . htmlspecialchars($emailSender->getHost()) . "<br>";
    echo "SMTP Port: " . htmlspecialchars((string)$emailSender->getPort()) . "<br>";
    echo "SMTP Username: " . htmlspecialchars($emailSender->getUsername()) . "<br>";
    echo "SMTP Password: " . (strlen((string)$emailSender->getPassword()) > 0 ? 'Set ✅' : 'Not Set ❌') . "<br>";
    
    // Test 1: Basic connection
    echo "<h3>Test 1: Basic SMTP Connection</h3>";
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $emailSender->getHost();
    $mail->Port = (int)$emailSender->getPort();
    $mail->SMTPAuth = true;
    $mail->Username = $emailSender->getUsername();
    $mail->Password = $emailSender->getPassword();
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    
    // Capture debug output
    $debugOutput = [];
    $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
        $debugOutput[] = $str;
        echo "SMTP: " . htmlspecialchars($str) . "<br>";
    };
    
    echo "Attempting to connect...<br>";
    
    try {
        $mail->smtpConnect();
        echo "<span style='color: green;'>✅ SMTP Connection Successful!</span><br>";
        $mail->smtpClose();
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ SMTP Connection Failed: " . $e->getMessage() . "</span><br>";
    }
    
    // Test 2: Try to send a simple email
    echo "<h3>Test 2: Sending Test Email</h3>";
    
    $testEmail = 'rltiempo25@gmail.com'; // Send to yourself first
    echo "Sending test email to: " . $testEmail . "<br>";
    
    $result = $emailSender->sendWelcomeEmail($testEmail, 'Test User', 'TestPass123', 'student');
    
    if ($result) {
        echo "<div style='color: green; font-weight: bold; padding: 10px; border: 2px solid green;'>✅ Email sent successfully!</div>";
    } else {
        echo "<div style='color: red; font-weight: bold; padding: 10px; border: 2px solid red;'>❌ Failed to send email.</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h3>Common Gmail SMTP Issues to Check:</h3>";
echo "<ol>";
echo "<li><strong>App Password:</strong> Make sure you're using an App Password, not your regular Gmail password</li>";
echo "<li><strong>2-Factor Authentication:</strong> Must be enabled to generate App Passwords</li>";
echo "<li><strong>Less Secure Apps:</strong> This setting is deprecated but check if it's enabled</li>";
echo "<li><strong>Firewall/Antivirus:</strong> May block outgoing SMTP connections</li>";
echo "<li><strong>XAMPP Restrictions:</strong> Some XAMPP configurations block external connections</li>";
echo "</ol>";

// Check PHP error log location
echo "<h3>Debug Information:</h3>";
echo "PHP Error Log: " . ini_get('error_log') . "<br>";
echo "Check this file for detailed SMTP conversation logs.<br>";
?>