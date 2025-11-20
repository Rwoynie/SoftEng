// test_hashing.php - Place this in your web root temporarily
<?php
$email = 'admin@usep.edu.ph';
$user_id = 'ADMIN001';

$emailHash = hash('sha256', $email);
$userIdHash = hash('sha256', $user_id);

echo "Email: $email\n";
echo "Email Hash: $emailHash\n\n";

echo "User ID: $user_id\n";
echo "User ID Hash: $userIdHash\n";

// Connect to database and check what's actually stored
try {
    require_once 'app/Models/Database.php';
    $db = new Database();
    
    // Check by email hash
    $db->query('SELECT * FROM USER_INFORMATION WHERE Email_Hash = :email_hash');
    $db->bind(':email_hash', $emailHash);
    $userByEmail = $db->single();
    
    echo "\n=== DATABASE CHECK ===\n";
    echo "User by email hash: " . ($userByEmail ? "FOUND" : "NOT FOUND") . "\n";
    
    // Check by user_id hash
    $db->query('SELECT * FROM USER_INFORMATION WHERE User_ID_Hash = :user_id_hash');
    $db->bind(':user_id_hash', $userIdHash);
    $userById = $db->single();
    
    echo "User by user_id hash: " . ($userById ? "FOUND" : "NOT FOUND") . "\n";
    
    if ($userByEmail) {
        echo "User details:\n";
        echo "  ID: " . ($userByEmail->ID ?? 'N/A') . "\n";
        echo "  Role: " . ($userByEmail->User_Role ?? 'N/A') . "\n";
        echo "  Status: " . ($userByEmail->Acc_Status ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>