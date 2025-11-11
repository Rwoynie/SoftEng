<?php
// test_email_config.php - Put this in your Database/ directory or app/Utils/
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email Configuration Test</h2>";

$emailConfigPath = __DIR__ . '/../../Database/email_config.php';
echo "Looking for email_config.php at: " . $emailConfigPath . "<br>";

if (!file_exists($emailConfigPath)) {
    die("❌ email_config.php not found!");
}

echo "✅ email_config.php found!<br>";

require_once $emailConfigPath;

// Test if EmailConfig class exists and constants are defined
if (class_exists('EmailConfig')) {
    echo "✅ EmailConfig class exists!<br>";
    
    $constants = [
        'SMTP_HOST',
        'SMTP_PORT', 
        'SMTP_USERNAME',
        'SMTP_PASSWORD',
        'SMTP_FROM_EMAIL',
        'SMTP_FROM_NAME',
        'WELCOME_SUBJECT'
    ];
    
    foreach ($constants as $constant) {
        if (defined("EmailConfig::$constant")) {
            $value = constant("EmailConfig::$constant");
            // Mask password for security
            if ($constant === 'SMTP_PASSWORD') {
                $value = '********' . substr($value, -4);
            }
            echo "✅ EmailConfig::$constant: " . $value . "<br>";
        } else {
            echo "❌ EmailConfig::$constant not defined!<br>";
        }
    }
} else {
    echo "❌ EmailConfig class not found!<br>";
}
?>