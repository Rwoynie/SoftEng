<?php
// final_test.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Final Email Test</h2>";

require_once __DIR__ . '/EmailSender.php';

try {
    $emailSender = new EmailSender();
    
    echo "EmailSender created successfully!<br>";
    echo "SMTP: " . $emailSender->host . ":" . $emailSender->port . "<br>";
    echo "Username: " . $emailSender->username . "<br><br>";
    
    // Test email
    $testEmail = 'rltiempo25@gmail.com'; // Send to yourself
    echo "Sending test email to: " . $testEmail . "<br>";
    
    $result = $emailSender->sendWelcomeEmail($testEmail, 'Test User', 'TestPass123', 'student');
    
    if ($result) {
        echo "<div style='color: green; font-weight: bold; padding: 20px; border: 3px solid green; font-size: 18px;'>";
        echo "✅ SUCCESS! Email sent successfully!";
        echo "</div>";
        echo "<p>Check your Gmail inbox and spam folder.</p>";
    } else {
        echo "<div style='color: red; font-weight: bold; padding: 20px; border: 3px solid red; font-size: 18px;'>";
        echo "❌ FAILED! Email not sent.";
        echo "</div>";
        echo "<p>Check the PHP error logs for details.</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> The SMTP debug output will show above. Look for authentication success messages.</p>";
?>