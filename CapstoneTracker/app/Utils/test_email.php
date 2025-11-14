<?php
// test_email.php
require_once 'EmailSender.php';

$emailSender = new EmailSender();

// Test with a known email
$testEmail = "rltiempo25@gmail.com";
$testName = "Test User";
$testSubject = "Test Email from Compendium System";
$testBody = "<h1>Test Email</h1><p>This is a test email from the Compendium System.</p>";

$result = $emailSender->sendHtmlEmail($testEmail, $testName, $testSubject, $testBody);

if ($result) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send test email. Check error logs.";
}
?>