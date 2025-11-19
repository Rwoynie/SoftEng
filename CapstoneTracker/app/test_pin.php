<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone explicitly
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/../Database/config.php';
require_once __DIR__ . '/Models/Profile.php';

$testUserId = 1; 
$db = new Database();
$profile = new Profile($db);

echo "Testing PIN functionality with timezone fix...<br>";

// Test PIN storage
$pin = sprintf("%06d", mt_rand(1, 999999));

echo "Generated PIN: " . $pin . "<br>";
echo "PHP Time: " . date('Y-m-d H:i:s') . "<br>";

// Get database time
$db->query("SELECT NOW() as db_time, DATE_ADD(NOW(), INTERVAL 10 MINUTE) as db_expiry");
$dbTime = $db->singleAssoc();
echo "DB Time: " . $dbTime['db_time'] . "<br>";
echo "DB Expiry: " . $dbTime['db_expiry'] . "<br>";

// Store PIN (will use database time internally)
$storeResult = $profile->storePasswordChangePin($testUserId, $pin, $dbTime['db_expiry']);
echo "PIN Storage: " . ($storeResult ? "SUCCESS" : "FAILED") . "<br>";

// Immediately check what's in database
echo "<br>--- Immediate Database Check ---<br>";
$db->query("SELECT *, TIMESTAMPDIFF(SECOND, NOW(), expiry_date) as seconds_remaining FROM password_change_pins WHERE user_id = :user_id ORDER BY created_at DESC");
$db->bind(':user_id', $testUserId);
$pins = $db->resultSetAssoc();
foreach ($pins as $pinRecord) {
    echo "PIN: '" . $pinRecord['pin_code'] . "' | Expiry: " . $pinRecord['expiry_date'] . " | Used: " . $pinRecord['used'] . "<br>";
    echo "Seconds remaining: " . $pinRecord['seconds_remaining'] . "<br>";
}

// Verify PIN
echo "<br>--- First Verification ---<br>";
$verifyResult = $profile->verifyPasswordChangePin($testUserId, $pin);
echo "PIN Verification: " . ($verifyResult ? "SUCCESS" : "FAILED") . "<br>";

// Check database after verification
echo "<br>--- After Verification Check ---<br>";
$db->query("SELECT * FROM password_change_pins WHERE user_id = :user_id ORDER BY created_at DESC");
$db->bind(':user_id', $testUserId);
$pinsAfter = $db->resultSetAssoc();
foreach ($pinsAfter as $pinRecord) {
    echo "PIN: '" . $pinRecord['pin_code'] . "' | Expiry: " . $pinRecord['expiry_date'] . " | Used: " . $pinRecord['used'] . "<br>";
}
?>