<?php

// Enable error reporting for debugging - remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Simple debug function
function debug_log($message) {
    file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

try {
    debug_log("ProfileController accessed: " . ($_GET['action'] ?? 'no action'));

    // Check if it's an AJAX request
    if (!isset($_GET['action'])) {
        throw new Exception('No action specified');
    }

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    debug_log("Session started, user_id: " . ($_SESSION['user_id'] ?? 'not set'));

    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        exit;
    }

    // Include required files with proper paths
    $base_dir = __DIR__ . '/../';
    
    // Include Database config
    $database_config_path = $base_dir . 'Database/config.php';
    if (!file_exists($database_config_path)) {
        throw new Exception('Database config file not found at: ' . $database_config_path);
    }
    require_once $database_config_path;

    // Include Profile model
    $profile_model_path = $base_dir . 'Models/Profile.php';
    if (!file_exists($profile_model_path)) {
        throw new Exception('Profile model file not found at: ' . $profile_model_path);
    }
    require_once $profile_model_path;

    // Check if Database class exists
    if (!class_exists('Database')) {
        throw new Exception('Database class not found');
    }

    // Create database connection
    $database = new Database();
    debug_log("Database connection created");

    // Create Profile model
    $profileModel = new Profile($database);
    debug_log("Profile model created");

    $action = $_GET['action'];
    $userId = $_SESSION['user_id'];

    debug_log("Processing action: " . $action . " for user: " . $userId);

    switch ($action) {
        case 'get':
            // Get user profile
            $profileData = $profileModel->getUserProfile($userId);
            
            if ($profileData) {
                echo json_encode([
                    'success' => true,
                    'profile' => $profileData
                ]);
                debug_log("Profile data retrieved successfully");
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Profile not found'
                ]);
                debug_log("Profile not found for user: " . $userId);
            }
            break;

        case 'update':
            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }

            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                break;
            }

            $updateData = [];
            $allowedFields = ['first_name', 'last_name', 'middle_name', 'email', 'course'];
            
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                    $updateData[$field] = trim($_POST[$field]);
                }
            }

            if (empty($updateData)) {
                echo json_encode(['success' => false, 'message' => 'No valid data provided for update']);
                break;
            }

            $result = $profileModel->updateUserProfile($userId, $updateData);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (Exception $e) {
    // Clean any output
    while (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    debug_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}

// Ensure no extra output
if (ob_get_length()) {
    ob_end_clean();
}

exit;
?>