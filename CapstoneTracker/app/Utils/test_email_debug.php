<?php
// test_email_debug.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email Sending Debug Test</h2>";

require_once __DIR__ . '/EmailSender.php';

try {
    $emailSender = new EmailSender();
    
    // Test multiple emails
    $testEmails = [
        'test1@example.com',
        'test2@example.com', 
        'test3@example.com'
    ];
    
    foreach ($testEmails as $index => $email) {
        echo "<h3>Sending test email " . ($index + 1) . " to: " . $email . "</h3>";
        
        $result = $emailSender->sendWelcomeEmail($email, 'Test User ' . ($index + 1), 'TestPass123', 'student');
        
        if ($result) {
            echo "<div style='color: green;'>✅ Email " . ($index + 1) . " sent successfully!</div>";
        } else {
            echo "<div style='color: red;'>❌ Email " . ($index + 1) . " failed to send.</div>";
        }
        
        echo "<br>";
        
        // Small delay between emails
        sleep(1);
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
?>