<?php

// Disable error display to prevent output before JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Clean any existing output
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffering
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
        ob_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not authenticated'], JSON_UNESCAPED_UNICODE);
        ob_end_flush();
        exit;
    }

    // Include required files with proper paths
    $base_dir = __DIR__ . '/../';
    
    // Include Database config (project-level Database/config.php)
    $database_config_path = __DIR__ . '/../../Database/config.php';
    if (!file_exists($database_config_path)) {
        throw new Exception('Database config file not found at: ' . $database_config_path);
    }
    require_once $database_config_path;
    
    // Re-disable error display after config.php might have enabled it
    ini_set('display_errors', 0);
    
    // Clean buffer after includes in case they output anything
    ob_clean();

    // Ensure Database class is available
    $database_class_path = $base_dir . 'Models/Database.php';
    if (file_exists($database_class_path)) {
        require_once $database_class_path;
        ob_clean(); // Clean after each include
    }

    // Include Profile model
    $profile_model_path = $base_dir . 'Models/Profile.php';
    if (!file_exists($profile_model_path)) {
        throw new Exception('Profile model file not found at: ' . $profile_model_path);
    }
    require_once $profile_model_path;
    ob_clean(); // Clean after include

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
            // Clean output buffer before sending JSON
            ob_clean();
            
            // Get user profile
            $profileData = $profileModel->getUserProfile($userId);
            
            if ($profileData) {
                echo json_encode([
                    'success' => true,
                    'profile' => $profileData
                ], JSON_UNESCAPED_UNICODE);
                debug_log("Profile data retrieved successfully");
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Profile not found'
                ], JSON_UNESCAPED_UNICODE);
                debug_log("Profile not found for user: " . $userId);
            }
            break;

        case 'update':
            // Clean output buffer before sending JSON
            ob_clean();
            
            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
                break;
            }

            // Verify CSRF token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token'], JSON_UNESCAPED_UNICODE);
                break;
            }

            $updateData = [];
            $allowedFields = ['first_name', 'last_name', 'middle_name', 'email', 'course'];
            
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                    $updateData[$field] = trim($_POST[$field]);
                }
            }

            // Handle optional profile picture upload
            if (isset($_FILES['profile_pic']) && is_uploaded_file($_FILES['profile_pic']['tmp_name'])) {
                $tmp = $_FILES['profile_pic']['tmp_name'];
                $size = $_FILES['profile_pic']['size'] ?? 0;
                $mime = @mime_content_type($tmp) ?: '';
                $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
                if ($size > 0 && $size <= 5 * 1024 * 1024 && in_array($mime, $allowedTypes, true)) {
                    $binary = file_get_contents($tmp);
                    if ($binary !== false) {
                        $updateData['profile_pic'] = $binary;
                    }
                }
            }

            // Handle optional password update
            if (!empty($_POST['password']) || !empty($_POST['confirm_password'])) {
                $password = trim($_POST['password'] ?? '');
                $confirm = trim($_POST['confirm_password'] ?? '');
                if ($password === '' || $confirm === '') {
                    echo json_encode(['success' => false, 'message' => 'Please fill both password fields'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                if ($password !== $confirm) {
                    echo json_encode(['success' => false, 'message' => 'Passwords do not match'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                if (strlen($password) < 8) {
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters'], JSON_UNESCAPED_UNICODE);
                    break;
                }
                // Generate new salt and hash
                $salt = bin2hex(random_bytes(16));
                $hash = password_hash($password . $salt, PASSWORD_DEFAULT);
                $updateData['pswrd'] = $hash;
                $updateData['salt'] = $salt;
            }

            if (empty($updateData)) {
                echo json_encode(['success' => false, 'message' => 'No valid data provided for update'], JSON_UNESCAPED_UNICODE);
                break;
            }

            $result = $profileModel->updateUserProfile($userId, $updateData);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update profile'
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        default:
            ob_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
            break;
    }

} catch (Exception $e) {
    // Clean any output
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Start fresh buffer for error response
    ob_start();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);

    debug_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}

// Flush output buffer and exit
ob_end_flush();
exit;