<?php
/**
 * Registration Controller
 * Handles user registration for students and faculty
 */

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2)); 
}

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/Model.php';
require_once __DIR__ . '/../Models/User.php';

class RegistrationController {
    private $db;
    private $userModel;
    private $error = null;
    
    public function __construct() {
        $this->db = new Database();
        $this->userModel = new User();
    }
    
    /**
     * Handle student registration
     */
    public function registerStudent($data, $files = []) {
        // Validate required fields
        $required = ['firstName', 'lastName', 'studentId', 'yearLevel', 'course', 'email', 'password', 'confirmPassword'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->error = "All required fields are missing: " . $field;
                return false;
            }
        }
        
        // Validate email format and domain
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error = "Invalid email format";
            return false;
        }
        
        if (!preg_match('/@usep\.edu\.ph$/i', $data['email'])) {
            $this->error = "Only USeP email addresses (@usep.edu.ph) are allowed";
            return false;
        }
        
        // Validate password match
        if ($data['password'] !== $data['confirmPassword']) {
            $this->error = "Passwords do not match";
            return false;
        }
        
        // Validate password strength
        if (strlen($data['password']) < 8) {
            $this->error = "Password must be at least 8 characters long";
            return false;
        }
        
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            $this->error = "Email address is already registered";
            return false;
        }
        
        // Check if student ID already exists
        if ($this->userIdExists($data['studentId'])) {
            $this->error = "Student ID is already registered";
            return false;
        }
        
        try {
            // Handle profile picture upload
            $profilePicPath = null;
            if (!empty($files['profilePic']) && $files['profilePic']['error'] === UPLOAD_ERR_OK) {
                $profilePicPath = $this->handleProfilePictureUpload($files['profilePic']);
            }
            
            // Prepare user data for your existing register function
            $userData = [
                'password' => $data['password'],
                'first_name' => $data['firstName'],
                'middle_name' => $data['middleName'] ?? '',
                'last_name' => $data['lastName'],
                'extension' => $data['extension'] ?? '',
                'email' => $data['email'],
                'student_id' => $data['studentId'],
                'user_role' => 'student',
                'acc_status' => 'pending', // Default status for new registrations
                'year_level' => $data['yearLevel'],
                'course' => $data['course'],
                'profile_pic' => $profilePicPath
            ];
            
            // Use your existing register function from User model
            $result = $this->userModel->register($userData);
            
            if ($result) {
                return true;
            } else {
                $this->error = "Registration failed. Please try again.";
                return false;
            }
            
        } catch (Exception $e) {
            $this->error = "Registration error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Handle faculty registration
     */
    public function registerFaculty($data) {
        // Validate required fields
        $required = ['firstName', 'lastName', 'employeeId', 'department', 'designation', 'email', 'password', 'confirmPassword'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->error = "All required fields are missing: " . $field;
                return false;
            }
        }
        
        // Validate email format and domain
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error = "Invalid email format";
            return false;
        }
        
        if (!preg_match('/@usep\.edu\.ph$/i', $data['email'])) {
            $this->error = "Only USeP email addresses (@usep.edu.ph) are allowed";
            return false;
        }
        
        // Validate password match
        if ($data['password'] !== $data['confirmPassword']) {
            $this->error = "Passwords do not match";
            return false;
        }
        
        // Validate password strength
        if (strlen($data['password']) < 8) {
            $this->error = "Password must be at least 8 characters long";
            return false;
        }
        
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            $this->error = "Email address is already registered";
            return false;
        }
        
        // Check if employee ID already exists
        if ($this->userIdExists($data['employeeId'])) {
            $this->error = "Employee ID is already registered";
            return false;
        }
        
        try {
            // Prepare user data for your existing register function
            $userData = [
                'password' => $data['password'],
                'first_name' => $data['firstName'],
                'middle_name' => $data['middleName'] ?? '',
                'last_name' => $data['lastName'],
                'extension' => $data['extension'] ?? '',
                'email' => $data['email'],
                'student_id' => $data['employeeId'], // Using student_id field for employee ID
                'user_role' => 'faculty',
                'acc_status' => 'pending', // Default status for new registrations
                'department' => $data['department'],
                'designation' => $data['designation']
            ];
            
            // Use your existing register function from User model
            $result = $this->userModel->register($userData);
            
            if ($result) {
                return true;
            } else {
                $this->error = "Registration failed. Please try again.";
                return false;
            }
            
        } catch (Exception $e) {
            $this->error = "Registration error: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Check if email already exists (compatible with your User model)
     */
    private function emailExists($email) {
        try {
            $this->userModel->db->query('SELECT ID FROM USER_INFORMATION WHERE Email = :email');
            $this->userModel->db->bind(':email', $email);
            $this->userModel->db->execute();
            return $this->userModel->db->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if user ID already exists (compatible with your User model)
     */
    private function userIdExists($userId) {
        try {
            $this->userModel->db->query('SELECT ID FROM USER_INFORMATION WHERE Student_ID = :user_id OR User_ID = :user_id');
            $this->userModel->db->bind(':user_id', $userId);
            $this->userModel->db->execute();
            return $this->userModel->db->rowCount() > 0;
        } catch (Exception $e) {
            return false;
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
     * Extract first name from full name
     */
    private function extractFirstName($fullName) {
        $names = explode(' ', trim($fullName));
        return $names[0] ?? '';
    }
    
    /**
     * Extract last name from full name
     */
    private function extractLastName($fullName) {
        $names = explode(' ', trim($fullName));
        return end($names) ?? '';
    }
    
    /**
     * Extract middle name from full name
     */
    private function extractMiddleName($fullName) {
        $names = explode(' ', trim($fullName));
        if (count($names) > 2) {
            return implode(' ', array_slice($names, 1, -1));
        }
        return '';
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
?>