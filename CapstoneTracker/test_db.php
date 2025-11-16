<?php
require_once __DIR__ . '/Database/config.php'; 
$db = new Database();
echo "Connected: " . ($db->isConnected() ? 'YES' : 'NO') . "<br>";
if (!$db->isConnected()) {
    echo "Error: " . $db->getError();
}
?>