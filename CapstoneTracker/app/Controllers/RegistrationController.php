<?php
/**
 * Registration Controller
 * Handles user registration for students and faculty
 */

 /*
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2)); 
}
 */
require_once '../Models/Database.php';
require_once '../Models/Model.php';
require_once '../Models/User.php';

class RegistrationController {

    private $userModel;
    private $error = null;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Handle registration request - determines if it's student or faculty
     */
    public function handleRegistration($data, $files = []) {
        $action = $data['action'] ?? '';
        
        if ($action === 'student_register') {
            return $this->registerStudent($data, $files);
        } elseif ($action === 'faculty_register') {
            return $this->registerFaculty($data);
        } else {
            $this->error = "Invalid registration action";
            return false;
        }
    }
    
    /**
     * Handle student registration
     */
    public function registerStudent($data, $files = []) {
        try {

            $designation = 'Student';
            // Validate required fields
            $required = ['firstName', 'lastName', 'studentId', 'course', 'email', 'password', 'confirmPassword'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("All required fields must be filled. Missing: " . $field);
                }
            }
            
            // Validate email format and domain
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            if (!preg_match('/@usep\.edu\.ph$/i', $data['email'])) {
                throw new Exception("Only USeP email addresses (@usep.edu.ph) are allowed");
            }
            
            // Validate password match
            if ($data['password'] !== $data['confirmPassword']) {
                throw new Exception("Passwords do not match");
            }
            
            // Validate password strength
            if (strlen($data['password']) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            // Handle profile picture upload
            $profilePicPath = null;
            if (!empty($files['profilePic']) && $files['profilePic']['error'] === UPLOAD_ERR_OK) {
                $profilePicPath = $this->handleProfilePictureUpload($files['profilePic']);
            }
            
            // Prepare user data for registration
            $userData = [
                'password' => $data['password'],
                'first_name' => trim($data['firstName']),
                'middle_name' => trim($data['middleName'] ?? ''),
                'last_name' => trim($data['lastName']),
                'extension' => trim($data['extension'] ?? ''),
                'email' => trim($data['email']),
                'student_id' => trim($data['studentId']),
                'user_role' => 'student',
                'acc_status' => 'pending',
                'course' => $data['course'],
                'designation' => $data['designation'],
                'profile_pic' => $profilePicPath
            ];
            
            // Use the register function from User model
            $result = $this->userModel->register($userData);
            
            if ($result) {
                return true;
            } else {
                throw new Exception("Registration failed. Please try again.");
            }
            
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            error_log("Student registration error: " . $this->error);
            return false;
        }
    }
    
    /**
     * Handle faculty registration
     */
    public function registerFaculty($data, $files = []) {
        try {
            // Validate required fields
            $required = ['firstName', 'lastName', 'employeeId', 'department', 'designation', 'email', 'password', 'confirmPassword'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("All required fields must be filled. Missing: " . $field);
                }
            }
            
            // Validate email format and domain
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            if (!preg_match('/@usep\.edu\.ph$/i', $data['email'])) {
                throw new Exception("Only USeP email addresses (@usep.edu.ph) are allowed");
            }
            
            // Validate password match
            if ($data['password'] !== $data['confirmPassword']) {
                throw new Exception("Passwords do not match");
            }
            
            // Validate password strength
            if (strlen($data['password']) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            // Handle profile picture upload (optional for faculty)
            $profilePicPath = null;
            if (!empty($files['profilePic']) && $files['profilePic']['error'] === UPLOAD_ERR_OK) {
                $profilePicPath = $this->handleProfilePictureUpload($files['profilePic']);
            }
            
            // Prepare user data for registration
            $userData = [
                'password' => $data['password'],
                'first_name' => trim($data['firstName']),
                'middle_name' => trim($data['middleName'] ?? ''),
                'last_name' => trim($data['lastName']),
                'extension' => trim($data['extension'] ?? ''),
                'email' => trim($data['email']),
                'employee_id' => trim($data['employeeId']),
                'user_role' => 'faculty',
                'acc_status' => 'pending',
                'department' => $data['department'],
                'designation' => $data['designation'],
                'profile_pic' => $profilePicPath  // Added profile picture for faculty
            ];
            
            // Use the register function from User model
            $result = $this->userModel->register($userData);
            
            if ($result) {
                return true;
            } else {
                // Get the specific error from the model if available
                $modelError = $this->userModel->getError();
                throw new Exception($modelError ?: "Registration failed. Please try again.");
            }
            
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            error_log("Faculty registration error: " . $this->error);
            return false;
        }
    }
    
    /**
     * Process registration form submission
     */
    public function processRegistration() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug: Log the POST data
        error_log("Registration POST data: " . print_r($_POST, true));
        error_log("Registration FILES data: " . print_r($_FILES, true));
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';
            error_log("Registration action: " . $action);
            
            try {
                if ($action === 'student_register') {
                    $result = $this->registerStudent($_POST, $_FILES);
                    
                    if ($result) {
                        $_SESSION['success_message'] = "Student registration successful! Your account is now pending for approval.";
                        header('Location: ../../app/Views/User/indexLogin.php');
                        exit();
                    } else {
                        throw new Exception($this->getError() ?: "Registration failed. Please try again.");
                    }
                } elseif ($action === 'faculty_register') {
                    error_log("Processing faculty registration...");
                    $result = $this->registerFaculty($_POST, $_FILES);
                    
                    if ($result) {
                        $_SESSION['success_message'] = "Faculty registration successful! Your account is now pending for approval.";
                        header('Location: ../../app/Views/User/indexLogin.php');
                        exit();
                    } else {
                        $error = $this->getError();
                        error_log("Faculty registration error: " . $error);
                        throw new Exception($error ?: "Faculty registration failed. Please try again.");
                    }
                } else {
                    throw new Exception("Invalid registration action: " . $action);
                }
            } catch (Exception $e) {
                // Store the specific error message
                $_SESSION['error_message'] = $e->getMessage();
                // Also store which modal to open
                $_SESSION['error_modal'] = ($action === 'faculty_register') ? 'faculty' : 'student';
                error_log("Registration exception: " . $e->getMessage());
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid request method";
            header('Location: ../../app/Views/User/indexLogin.php');
            exit();
        }
    }
    
    /**
     * Handle profile picture upload
     */
    private function handleProfilePictureUpload($file) {
        $uploadDir = __DIR__ . '/../../uploads/profile_pictures/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Only JPG, PNG, and GIF images are allowed');
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size must be less than 5MB');
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload profile picture');
        }
        
        return 'uploads/profile_pictures/' . $filename;
    }
    
    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Set error message
     */
    public function setError($message) {
        $this->error = $message;
    }
    
    /**
     * Get success response
     */
    public function getSuccessResponse($message = "Registration successful!") {
        return [
            'success' => true,
            'message' => $message
        ];
    }
    
    /**
     * Get error response
     */
    public function getErrorResponse($message = null) {
        return [
            'success' => false,
            'message' => $message ?: $this->error
        ];
    }
}

// Handle direct access to this file for registration processing
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $registrationController = new RegistrationController();
    $registrationController->processRegistration();
}

?>