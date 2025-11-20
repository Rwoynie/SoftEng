<?php
/**
 * Debug Google Registration
 * Use this file to check Google registration issues
 */

// Enable all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Google Registration Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        .test-form { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .step { margin: 15px 0; padding: 10px; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
    <h1>Google Registration Debug</h1>";

try {
    // Include required files
    require_once '../Models/Database.php';
    require_once '../Models/User.php';
    
    echo "<div class='info'>✓ Required files loaded successfully</div>";
    
    // Create instances
    $userModel = new User();
    
    echo "<div class='info'>✓ User model initialized</div>";
    
    // Generate unique test email to avoid conflicts
    $timestamp = time();
    $testEmail = "teststudent{$timestamp}@usep.edu.ph";
    
    // Test data similar to what Google would send
    $testGoogleData = [
        'email' => $testEmail,
        'name' => 'Test Student',
        'user_role' => 'student',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'password' => 'google_auto_generated_password_123',
        'acc_status' => 'approved',
        'profile_pic' => base64_decode('R0lGODlhAQABAIAAAAAA/P///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7')
    ];
    
    echo "<h2>1. Testing Google Registration Method</h2>";
    echo "<pre>Test data: " . print_r($testGoogleData, true) . "</pre>";
    
    // Test the registerGoogleUser method
    echo "<div class='step'>Calling registerGoogleUser()...</div>";
    $result = $userModel->registerGoogleUser($testGoogleData);
    
    if ($result) {
        echo "<div class='success'>✓ Google registration test SUCCESS</div>";
        $lastInsertId = $userModel->getDb()->lastInsertId();
        echo "<p>Last Insert ID: " . ($lastInsertId ?: 'NULL') . "</p>";
        
        // Try to find the newly created user
        echo "<div class='step'>Searching for newly created user...</div>";
        $newUser = $userModel->findByEmail($testEmail);
        
        if ($newUser) {
            echo "<div class='success'>✓ User found after registration</div>";
            echo "<pre>User data: " . print_r($newUser, true) . "</pre>";
            
            $userId = $newUser['ID'] ?? $newUser->ID ?? null;
            $userIdentifier = $newUser['User_ID'] ?? $newUser->User_ID ?? null;
            
            if ($userId) {
                echo "<div class='success'>✓ User ID retrieved: " . $userId . "</div>";
                echo "<div class='info'>User Identifier: " . ($userIdentifier ?: 'NULL') . "</div>";
            } else {
                echo "<div class='error'>✗ User ID is NULL in retrieved user data</div>";
            }
        } else {
            echo "<div class='error'>✗ User not found after registration</div>";
            
            // Try alternative search methods
            echo "<div class='step'>Trying alternative search methods...</div>";
            
            // Check if user exists with different method
            $userExists = $userModel->checkUserExists($testEmail);
            echo "<p>checkUserExists result: " . ($userExists ? 'YES' : 'NO') . "</p>";
            
            // Get user status
            $userStatus = $userModel->getUserStatus($testEmail);
            echo "<p>getUserStatus result: " . ($userStatus ?: 'NULL') . "</p>";
        }
    } else {
        echo "<div class='error'>✗ Google registration test FAILED</div>";
        echo "<p>Error: " . $userModel->getError() . "</p>";
    }
    
    // Test if email already exists check works
    echo "<h2>2. Testing Email Exists Check</h2>";
    $emailExists = $userModel->emailExists($testEmail);
    echo "<p>Email '{$testEmail}' exists: " . ($emailExists ? "YES" : "NO") . "</p>";
    
    // Check what Google-related methods exist in User model
    echo "<h2>3. Available User Model Methods</h2>";
    $methods = get_class_methods($userModel);
    $googleMethods = array_filter($methods, function($method) {
        return stripos($method, 'google') !== false;
    });
    echo "<pre>Google-related methods: " . print_r($googleMethods, true) . "</pre>";
    
    // Test email sending functionality
    echo "<h2>4. Testing Email Sending Functionality</h2>";
    
    // Include and test EmailSender
    $emailSenderPath = __DIR__ . '/../Utils/EmailSender.php';
    if (file_exists($emailSenderPath)) {
        require_once $emailSenderPath;
        echo "<div class='info'>✓ EmailSender loaded successfully</div>";
        
        $emailSender = new EmailSender();
        echo "<div class='info'>EmailSender initialized. Mailer available: " . ($emailSender->mailerAvailable ? 'YES' : 'NO') . "</div>";
        
        // Test email configuration
        echo "<div class='step'>Testing email configuration...</div>";
        echo "<pre>SMTP Host: " . EmailConfig::SMTP_HOST . "</pre>";
        echo "<pre>SMTP Port: " . EmailConfig::SMTP_PORT . "</pre>";
        echo "<pre>SMTP Username: " . EmailConfig::SMTP_USERNAME . "</pre>";
        
        // Test connection
        $connectionTest = $emailSender->testConnection();
        echo "<p>SMTP Connection Test: " . ($connectionTest ? "SUCCESS" : "FAILED") . "</p>";
        
        // Test sending a simple email
        echo "<div class='step'>Testing email sending...</div>";
        $testEmailTo = "test@example.com"; // Change this to a real email for testing
        $testName = "Test User";
        $testSubject = "Google Registration Debug Test";
        $testBody = "<h1>Test Email</h1><p>This is a test email from the Google registration debug script.</p>";
        
        echo "<p>Sending test email to: {$testEmailTo}</p>";
        
        $emailResult = $emailSender->sendHtmlEmail($testEmailTo, $testName, $testSubject, $testBody);
        
        if ($emailResult) {
            echo "<div class='success'>✓ Test email sent SUCCESSFULLY</div>";
        } else {
            echo "<div class='error'>✗ Test email sending FAILED</div>";
            echo "<p class='warning'>Note: This might be due to invalid recipient email or SMTP configuration</p>";
        }
        
        // Test welcome email function specifically
        if ($newUser && $userId) {
            echo "<div class='step'>Testing welcome email function...</div>";
            $welcomeEmailResult = $emailSender->sendWelcomeEmail(
                $testEmail, 
                'Test Student', 
                'test_password_123', 
                'student', 
                $userIdentifier ?: 'TEST123'
            );
            
            if ($welcomeEmailResult) {
                echo "<div class='success'>✓ Welcome email sent SUCCESSFULLY</div>";
            } else {
                echo "<div class='error'>✗ Welcome email sending FAILED</div>";
            }
        }
        
    } else {
        echo "<div class='error'>✗ EmailSender.php not found at: " . $emailSenderPath . "</div>";
    }
    
    // Test database queries directly
    echo "<h2>5. Testing Database Queries</h2>";
    
    try {
        $db = $userModel->getDb();
        
        // Count total users
        $db->query('SELECT COUNT(*) as total FROM USER_INFORMATION');
        $totalUsers = $db->single();
        echo "<p>Total users in database: " . ($totalUsers->total ?? 'N/A') . "</p>";
        
        // Check if our test user exists in database
        $db->query('SELECT * FROM USER_INFORMATION WHERE Email LIKE :email');
        $db->bind(':email', '%' . $timestamp . '%');
        $matchingUsers = $db->resultSet();
        
        echo "<p>Users matching our test pattern: " . count($matchingUsers) . "</p>";
        if (count($matchingUsers) > 0) {
            echo "<pre>Matching users: " . print_r($matchingUsers, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>✗ Database query error: " . $e->getMessage() . "</div>";
    }
    
    // Test the regular registration method for comparison
    echo "<h2>6. Testing Regular Registration Method</h2>";
    $regularEmail = "regular{$timestamp}@usep.edu.ph";
    $regularData = [
        'email' => $regularEmail,
        'first_name' => 'Regular',
        'last_name' => 'User',
        'password' => 'regular_password_123',
        'user_role' => 'student',
        'student_id' => 'REG' . $timestamp,
        'course' => 'Computer Science',
        'acc_status' => 'approved'
    ];
    
    echo "<pre>Regular registration data: " . print_r($regularData, true) . "</pre>";
    
    $regularResult = $userModel->register($regularData);
    if ($regularResult) {
        echo "<div class='success'>✓ Regular registration SUCCESS</div>";
        
        // Find the regular user
        $regularUser = $userModel->findByEmail($regularEmail);
        if ($regularUser) {
            echo "<div class='success'>✓ Regular user found after registration</div>";
            $regularUserId = $regularUser['ID'] ?? $regularUser->ID ?? null;
            echo "<p>Regular User ID: " . ($regularUserId ?: 'NULL') . "</p>";
        } else {
            echo "<div class='error'>✗ Regular user not found after registration</div>";
        }
    } else {
        echo "<div class='error'>✗ Regular registration FAILED</div>";
        echo "<p>Error: " . $userModel->getError() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ ERROR: " . $e->getMessage() . "</div>";
    echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Summary</h2>";
echo "<p>This debug script tests:</p>";
echo "<ul>
    <li>Google registration method</li>
    <li>User retrieval after registration</li>
    <li>Email sending functionality</li>
    <li>Database connectivity</li>
    <li>Regular registration for comparison</li>
</ul>";

echo "</body></html>";
?>