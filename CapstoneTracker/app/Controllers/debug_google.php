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
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        .test-form { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
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
    
    // Test data similar to what Google would send
    $testGoogleData = [
        'email' => 'teststudent@usep.edu.ph',
        'name' => 'Test Student',
        'user_role' => 'student',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'password' => 'google_auto_generated_password_123',
        'course' => 'Computer Science',
        'department' => 'College of Engineering'
    ];
    
    echo "<h2>1. Testing Google Registration Method</h2>";
    echo "<pre>Test data: " . print_r($testGoogleData, true) . "</pre>";
    
    // Test the registerGoogleUser method
    $result = $userModel->registerGoogleUser($testGoogleData);
    
    if ($result) {
        echo "<div class='success'>✓ Google registration test SUCCESS</div>";
        echo "<p>New user ID: " . $userModel->getDb()->lastInsertId() . "</p>";
    } else {
        echo "<div class='error'>✗ Google registration test FAILED</div>";
        echo "<p>Error: " . $userModel->getError() . "</p>";
    }
    
    // Test if email already exists check works
    echo "<h2>2. Testing Email Exists Check</h2>";
    $emailExists = $userModel->emailExists($testGoogleData['email']);
    echo "<p>Email '{$testGoogleData['email']}' exists: " . ($emailExists ? "YES" : "NO") . "</p>";
    
    // Check what Google-related methods exist in User model
    echo "<h2>3. Available User Model Methods</h2>";
    $methods = get_class_methods($userModel);
    $googleMethods = array_filter($methods, function($method) {
        return stripos($method, 'google') !== false;
    });
    echo "<pre>Google-related methods: " . print_r($googleMethods, true) . "</pre>";
    
    // Test the regular registration method for comparison
    echo "<h2>4. Testing Regular Registration Method</h2>";
    $testGoogleData = [
        'email' => 'teststudent@usep.edu.ph',
        'name' => 'Test Student',
        'user_role' => 'student',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'password' => 'google_auto_generated_password_123',
        'course' => 'Computer Science',
        'department' => 'College of Engineering',
        'student_id' => '2024-0001' // Add this required field
    ];
    
    $regularResult = $userModel->register($regularData);
    if ($regularResult) {
        echo "<div class='success'>✓ Regular registration SUCCESS</div>";
    } else {
        echo "<div class='error'>✗ Regular registration FAILED</div>";
        echo "<p>Error: " . $userModel->getError() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>✗ ERROR: " . $e->getMessage() . "</div>";
    echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>