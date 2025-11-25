<?php

// Migration script to add Login_Method column to USER_INFORMATION table

require_once __DIR__ . '/Database/config.php';

try {
    $db = new Database();

    // Check if Login_Method column already exists
    $db->query("SHOW COLUMNS FROM USER_INFORMATION LIKE 'Login_Method'");
    $exists = $db->rowCount() > 0;

    echo "Row count: " . $db->rowCount() . "\n";
    echo "Exists: " . ($exists ? 'true' : 'false') . "\n";

    if (!$exists) {
        // Add the Login_Method column
        echo "Attempting to add column...\n";
        $db->query("ALTER TABLE USER_INFORMATION
                   ADD COLUMN Login_Method ENUM('manual', 'google') DEFAULT 'manual'
                   AFTER Acc_Status");

        $result = $db->execute();
        echo "Execute result: " . ($result ? 'true' : 'false') . "\n";

        if ($result) {
            echo "✅ Login_Method column added successfully!\n";

            // Set existing Google users to 'google' based on some criteria
            // For now, we'll set all to 'manual' and let registration handle new users
            echo "✅ All existing users defaulted to 'manual' login method\n";

        } else {
            echo "❌ Failed to add Login_Method column\n";
            echo "Error: " . print_r($db->errorInfo(), true) . "\n";
        }
    } else {
        echo "✅ Login_Method column already exists\n";
    }

} catch (Exception $e) {
    echo "❌ Migration error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
