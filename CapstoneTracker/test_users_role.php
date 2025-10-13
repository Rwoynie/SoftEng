<?php
// test_users_roles.php
require_once __DIR__ . '/app/Models/Database.php';
require_once __DIR__ . '/app/Models/RoleModel.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing getAllUsersWithCompleteRoles()</h2>";

try {
    // Create database connection
    $database = new Database();
    
    // Test direct database query first
    echo "<h3>1. Testing Direct Database Query</h3>";
    
    // Check if USER_INFORMATION table has data
    $database->query("SELECT COUNT(*) as user_count FROM USER_INFORMATION");
    $userCount = $database->singleAssoc();
    echo "Total users in USER_INFORMATION: " . $userCount['user_count'] . "<br>";
    
    // Check if ROLES table has data
    $database->query("SELECT COUNT(*) as role_count FROM ROLES");
    $roleCount = $database->singleAssoc();
    echo "Total roles in ROLES table: " . $roleCount['role_count'] . "<br>";
    
    // Test the exact query used in getAllUsersWithCompleteRoles
    echo "<h3>2. Testing Exact Query</h3>";
    $query = "
        SELECT 
            ui.ID as User_ID,
            ui.First_Name, 
            ui.Middle_Name, 
            ui.Last_Name, 
            ui.Extension,
            ui.Email,
            ui.User_Role,
            ui.Acc_Status,
            ui.Department,
            ui.Course,
            ui.created_at,
            r.Role_ID,
            r.Sub_Admin,
            r.Can_Edit,
            r.Manage_Access,
            r.created_at as role_created_at,
            r.updated_at as role_updated_at
        FROM USER_INFORMATION ui
        LEFT JOIN ROLES r ON ui.ID = r.User_ID
        ORDER BY ui.created_at DESC
    ";
    
    $database->query($query);
    $results = $database->resultSetAssoc();
    
    echo "Query returned " . count($results) . " rows<br>";
    
    if (count($results) > 0) {
        echo "<h3>3. Sample Data (First 5 rows)</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>User_ID</th><th>Name</th><th>Email</th><th>User_Role</th><th>Sub_Admin</th><th>Can_Edit</th><th>Manage_Access</th></tr>";
        
        $count = 0;
        foreach ($results as $row) {
            if ($count >= 5) break;
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['User_Role']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Sub_Admin'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Can_Edit'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Manage_Access'] ?? 'NULL') . "</td>";
            echo "</tr>";
            $count++;
        }
        echo "</table>";
        
        // Show all data for debugging
        echo "<h3>4. Complete Raw Data</h3>";
        echo "<pre>" . print_r($results, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>No data returned from query!</p>";
        
        // Let's check what's actually in the tables
        echo "<h3>5. Checking Individual Tables</h3>";
        
        // Check USER_INFORMATION
        $database->query("SELECT ID, First_Name, Last_Name, Email, User_Role FROM USER_INFORMATION LIMIT 10");
        $users = $database->resultSetAssoc();
        echo "<strong>USER_INFORMATION data (first 10):</strong><br>";
        echo "<pre>" . print_r($users, true) . "</pre>";
        
        // Check ROLES
        $database->query("SELECT * FROM ROLES LIMIT 10");
        $roles = $database->resultSetAssoc();
        echo "<strong>ROLES data (first 10):</strong><br>";
        echo "<pre>" . print_r($roles, true) . "</pre>";
    }
    
    // Test the RoleModel method directly
    echo "<h3>6. Testing RoleModel Method</h3>";
    $roleModel = new RoleModel($database);
    $modelResults = $roleModel->getAllUsersWithCompleteRoles();
    
    echo "RoleModel returned: " . count($modelResults) . " users<br>";
    if (count($modelResults) > 0) {
        echo "<pre>" . print_r(array_slice($modelResults, 0, 3), true) . "</pre>";
    }
    
    // Check for any errors
    $error = $roleModel->getError();
    if ($error) {
        echo "<p style='color: red;'>RoleModel Error: " . $error . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>Stack trace: " . $e->getTraceAsString() . "</pre>";
}
?>