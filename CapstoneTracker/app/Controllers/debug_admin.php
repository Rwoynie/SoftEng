<?php
/**
 * Debug Admin Credentials
 * Use this file to check if admin user exists and verify credentials
 */

// Enable all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Credentials Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        .test-form { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Admin Credentials Debug</h1>";

try {
    // Include required files
    require_once '../Models/Database.php';
    require_once '../Models/User.php';
    
    echo "<div class='info'>✓ Required files loaded successfully</div>";
    
    // Create instances
    $userModel = new User();
    $db = $userModel->getDb();
    
    echo "<div class='info'>✓ Database connection established</div>";
    
    // Test admin ID
    $adminId = 'ADMIN001';
    $adminIdHash = hash('sha256', $adminId);
    
    echo "<h2>1. Checking Admin User in Database</h2>";
    echo "<p><strong>Admin ID:</strong> $adminId</p>";
    echo "<p><strong>Hashed Admin ID:</strong> $adminIdHash</p>";
    
    // Query for the admin user
    $db->query('SELECT * FROM USER_INFORMATION WHERE User_ID_Hash = :hash');
    $db->bind(':hash', $adminIdHash);
    $admin = $db->single();
    
    if ($admin) {
        echo "<div class='success'>✓ ADMIN USER FOUND IN DATABASE</div>";
        echo "<pre>";
        echo "ID: " . ($admin->ID ?? 'NULL') . "\n";
        echo "User Role: " . ($admin->User_Role ?? 'NULL') . "\n";
        echo "Account Status: " . ($admin->Acc_Status ?? 'NULL') . "\n";
        echo "First Name: " . ($admin->First_Name ?? 'NULL') . "\n";
        echo "Last Name: " . ($admin->Last_Name ?? 'NULL') . "\n";
        echo "Department: " . ($admin->Department ?? 'NULL') . "\n";
        echo "Course: " . ($admin->Course ?? 'NULL') . "\n";
        echo "Has Password: " . (!empty($admin->pswrd) ? "YES (" . strlen($admin->pswrd) . " chars)" : "NO") . "\n";
        echo "Has Salt: " . (!empty($admin->Salt) ? "YES (" . strlen($admin->Salt) . " chars)" : "NO") . "\n";
        echo "Password Hash (first 30 chars): " . substr($admin->pswrd ?? '', 0, 30) . "...\n";
        echo "Salt: " . ($admin->Salt ?? 'NULL') . "\n";
        echo "</pre>";
        
        // Test password verification
        echo "<h2>2. Testing Password Verification</h2>";
        
        $testPassword = 'compendiumSystemAdmin';
        $salt = $admin->Salt;
        $hashedPassword = $admin->pswrd;
        
        echo "<p><strong>Test Password:</strong> $testPassword</p>";
        echo "<p><strong>Stored Salt:</strong> $salt</p>";
        
        $passwordWithSalt = $testPassword . $salt;
        $verificationResult = password_verify($passwordWithSalt, $hashedPassword);
        
        if ($verificationResult) {
            echo "<div class='success'>✓ PASSWORD VERIFICATION SUCCESSFUL</div>";
            echo "<p>The default password 'compendiumSystemAdmin' works correctly.</p>";
        } else {
            echo "<div class='error'>✗ PASSWORD VERIFICATION FAILED</div>";
            echo "<p>The default password 'compendiumSystemAdmin' does not match.</p>";
            
            // Debug why it might be failing
            echo "<h3>Password Debug Info:</h3>";
            echo "<pre>";
            echo "Password + Salt: '$testPassword$salt'\n";
            echo "Password + Salt Length: " . strlen($passwordWithSalt) . "\n";
            echo "Stored Hash Length: " . strlen($hashedPassword) . "\n";
            
            // Check if it's the default admin password
            $defaultPassword = 'compendiumSystemAdmin';
            $defaultWithSalt = $defaultPassword . $salt;
            $defaultCheck = password_verify($defaultWithSalt, $hashedPassword);
            echo "Default password check: " . ($defaultCheck ? "WORKS" : "FAILS") . "\n";
            
            // Test without salt
            $noSaltCheck = password_verify($testPassword, $hashedPassword);
            echo "Password without salt: " . ($noSaltCheck ? "WORKS" : "FAILS") . "\n";
            
            // Test with salt first
            $saltFirstCheck = password_verify($salt . $testPassword, $hashedPassword);
            echo "Salt + Password: " . ($saltFirstCheck ? "WORKS" : "FAILS") . "\n";
            echo "</pre>";
        }
        
    } else {
        echo "<div class='error'>✗ ADMIN USER NOT FOUND IN DATABASE</div>";
        
        // Show all admin users that exist
        echo "<h2>Existing Admin Users in Database:</h2>";
        $db->query('SELECT ID, User_Role, Acc_Status, First_Name, Last_Name FROM USER_INFORMATION WHERE User_Role IN ("superAdmin", "SubAdmin", "admin")');
        $admins = $db->resultSet();
        
        if (count($admins) > 0) {
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Role</th><th>Status</th><th>First Name</th><th>Last Name</th></tr>";
            foreach ($admins as $adminUser) {
                echo "<tr>";
                echo "<td>" . ($adminUser->ID ?? 'NULL') . "</td>";
                echo "<td>" . ($adminUser->User_Role ?? 'NULL') . "</td>";
                echo "<td>" . ($adminUser->Acc_Status ?? 'NULL') . "</td>";
                echo "<td>" . ($adminUser->First_Name ?? 'NULL') . "</td>";
                echo "<td>" . ($adminUser->Last_Name ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No admin users found in the database.</p>";
        }
        
        // Show all users for reference
        echo "<h2>All Users in Database (first 10):</h2>";
        $db->query('SELECT ID, User_Role, Acc_Status, First_Name, Last_Name FROM USER_INFORMATION LIMIT 10');
        $allUsers = $db->resultSet();
        
        if (count($allUsers) > 0) {
            echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Role</th><th>Status</th><th>First Name</th><th>Last Name</th></tr>";
            foreach ($allUsers as $user) {
                echo "<tr>";
                echo "<td>" . ($user->ID ?? 'NULL') . "</td>";
                echo "<td>" . ($user->User_Role ?? 'NULL') . "</td>";
                echo "<td>" . ($user->Acc_Status ?? 'NULL') . "</td>";
                echo "<td>" . ($user->First_Name ?? 'NULL') . "</td>";
                echo "<td>" . ($user->Last_Name ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Test form to try different credentials
    echo "
    <div class='test-form'>
        <h2>3. Test Admin Login</h2>
        <form method='post' action=''>
            <p>
                <label>Admin ID:</label><br>
                <input type='text' name='test_admin_id' value='$adminId' style='width: 300px;'>
            </p>
            <p>
                <label>Password:</label><br>
                <input type='password' name='test_password' value='compendiumSystemAdmin' style='width: 300px;'>
            </p>
            <p>
                <input type='submit' name='test_login' value='Test Login'>
            </p>
        </form>
    </div>";
    
    // Handle test login
    if (isset($_POST['test_login'])) {
        $testAdminId = $_POST['test_admin_id'] ?? '';
        $testPassword = $_POST['test_password'] ?? '';
        
        echo "<h2>Test Login Results</h2>";
        echo "<p><strong>Testing:</strong> Admin ID: '$testAdminId', Password: '$testPassword'</p>";
        
        $result = $userModel->loginAdmin($testAdminId, $testPassword);
        
        if ($result && is_object($result)) {
            echo "<div class='success'>✓ LOGIN SUCCESSFUL</div>";
            echo "<pre>";
            echo "User ID: " . ($result->ID ?? 'NULL') . "\n";
            echo "Role: " . ($result->User_Role ?? 'NULL') . "\n";
            echo "Status: " . ($result->Acc_Status ?? 'NULL') . "\n";
            echo "Name: " . ($result->First_Name ?? 'NULL') . " " . ($result->Last_Name ?? 'NULL') . "\n";
            echo "</pre>";
        } else {
            echo "<div class='error'>✗ LOGIN FAILED</div>";
            echo "<p>The login failed. Check the credentials above.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ ERROR: " . $e->getMessage() . "</div>";
    echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";

echo "<h2>4. Direct User Model Test</h2>";

$testResult = $userModel->loginAdmin('ADMIN001', 'compendiumSystemAdmin');
if ($testResult) {
    echo "<div class='success'>✓ Direct User Model Test: SUCCESS</div>";
    echo "<pre>User object returned with role: " . ($testResult->User_Role ?? 'NULL') . "</pre>";
} else {
    echo "<div class='error'>✗ Direct User Model Test: FAILED</div>";
    echo "<p>The User model's loginAdmin method returned false.</p>";
}

// Add this after the existing code in debug_admin.php
echo "<h2>5. Hash Method Debug</h2>";

// Test the hashData method directly
$testUserId = 'ADMIN001';
$testHash = $userModel->hashData($testUserId);
echo "<p><strong>Test User ID:</strong> $testUserId</p>";
echo "<p><strong>Computed Hash:</strong> $testHash</p>";
echo "<p><strong>Expected Hash:</strong> 89b933c62993dd19e05ae115f18c12491e28b67834079aff0a79c94e4472be1b</p>";

if ($testHash === '89b933c62993dd19e05ae115f18c12491e28b67834079aff0a79c94e4472be1b') {
    echo "<div class='success'>✓ Hash method works correctly</div>";
} else {
    echo "<div class='error'>✗ Hash method is producing different results!</div>";
}

// Let's also check what's actually in the database
echo "<h2>6. Database Content Check</h2>";
$db->query('SELECT ID, User_Role, Acc_Status, User_ID_Hash FROM USER_INFORMATION WHERE User_Role IN ("superAdmin", "SubAdmin", "admin")');
$admins = $db->resultSet();

foreach ($admins as $admin) {
    echo "<h3>Admin User ID: " . $admin->ID . "</h3>";
    echo "<pre>";
    echo "Role: " . $admin->User_Role . "\n";
    echo "Status: " . $admin->Acc_Status . "\n";
    echo "Stored Hash: " . $admin->User_ID_Hash . "\n";
    echo "Expected Hash: 89b933c62993dd19e05ae115f18c12491e28b67834079aff0a79c94e4472be1b\n";
    echo "Match: " . ($admin->User_ID_Hash === '89b933c62993dd19e05ae115f18c12491e28b67834079aff0a79c94e4472be1b' ? "YES" : "NO");
    echo "</pre>";
}

// Test the fixed hash method
echo "<h2>7. Testing Fixed Hash Method</h2>";

// Test different variations to find the right one
$testUserId = 'ADMIN001';
$variations = [
    'raw' => $testUserId,
    'trimmed' => trim($testUserId),
    'lowercase' => strtolower($testUserId),
    'trimmed_lowercase' => trim(strtolower($testUserId))
];

foreach ($variations as $name => $variant) {
    $hash = hash('sha256', $variant);
    $matches = $hash === '89b933c62993dd19e05ae115f18c12491e28b67834079aff0a79c94e4472be1b';
    echo "<p><strong>$name:</strong> '$variant' → $hash → " . ($matches ? "✅ MATCH" : "❌ NO MATCH") . "</p>";
}
?>