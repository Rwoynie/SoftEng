<?php
// debug_phpmailer_loading.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHPMailer Loading Debug</h2>";

$emailSenderPath = __DIR__ . '/EmailSender.php';
echo "Loading EmailSender from: " . $emailSenderPath . "<br>";

// Check if we can manually load PHPMailer
$vendorPath = __DIR__ . '/../../vendor/';
echo "Vendor path: " . $vendorPath . "<br>";

// Try to load PHPMailer manually
$phpmailerLoaded = false;
$manualPaths = [
    $vendorPath . 'phpmailer/phpmailer/src/PHPMailer.php',
    $vendorPath . 'PHPMailer/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php'
];

foreach ($manualPaths as $path) {
    if (file_exists($path)) {
        echo "✅ Found PHPMailer at: " . $path . "<br>";
        require_once $path;
        require_once dirname($path) . '/SMTP.php';
        require_once dirname($path) . '/Exception.php';
        $phpmailerLoaded = true;
        break;
    }
}

if ($phpmailerLoaded) {
    echo "✅ PHPMailer manually loaded!<br>";
    
    // Test if classes exist
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ PHPMailer class exists!<br>";
    } else {
        echo "❌ PHPMailer class NOT found even after manual load!<br>";
    }
} else {
    echo "❌ Could not find PHPMailer manually<br>";
}

// Now test your EmailSender
echo "<h3>Testing EmailSender...</h3>";
require_once $emailSenderPath;

try {
    $emailSender = new EmailSender();
    echo "EmailSender PHPMailer Available: " . ($emailSender->mailerAvailable ? 'Yes ✅' : 'No ❌') . "<br>";
} catch (Exception $e) {
    echo "Error creating EmailSender: " . $e->getMessage() . "<br>";
}
?>