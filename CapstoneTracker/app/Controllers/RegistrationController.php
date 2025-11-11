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
     * Generate and store CSRF token
     */
    private function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && 
            hash_equals($_SESSION['csrf_token'], $token);
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

            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($csrfToken)) {
                $_SESSION['error_message'] = 'Invalid security token. Please try again.';
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }

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

            $profilePicData = null;
            if (!empty($files['profilePic']) && $files['profilePic']['error'] === UPLOAD_ERR_OK) {
                $profilePicData = file_get_contents($files['profilePic']['tmp_name']);
            } else {
                // Create a default profile picture (small transparent pixel)
                $profilePicData = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
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
                'designation' => $designation,
                'profile_pic' => $profilePicData
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
            // Validate CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($csrfToken)) {
                $_SESSION['error_message'] = 'Invalid security token. Please try again.';
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }

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
            
            $profilePicData = null;
            if (!empty($files['profilePic']) && $files['profilePic']['error'] === UPLOAD_ERR_OK) {
                $profilePicData = file_get_contents($files['profilePic']['tmp_name']);
            } else {
                // Create a default profile picture (small transparent pixel)
                $profilePicData = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
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
                'profile_pic' => $profilePicData
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


function validateEmail($email) {
    return preg_match("/^[A-Za-z0-9._%+-]+@usep\.edu\.ph$/", $email);
}

function fieldError(&$errors, $field, $message) {
    $errors[$field] = $message;
}

if ($action === 'student_register' || $action === 'faculty_register') {
    $errors = [];
    $old = $_POST;
    $role = $action === 'student_register' ? 'student' : 'faculty';

    // Extract common fields
    $firstName = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $lastName = trim($_POST['lastName']);
    $extension = trim($_POST['extension']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate name
    if (empty($firstName)) fieldError($errors, 'firstName', 'First name is required.');
    if (empty($lastName)) fieldError($errors, 'lastName', 'Last name is required.');

    // Validate email
    if (!validateEmail($email)) {
        fieldError($errors, 'email', 'Please use your university email (@usep.edu.ph).');
    } else {
        // check duplicates
        $table = $role === 'student' ? 'students' : 'faculty';
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            fieldError($errors, 'email', 'This email is already registered.');
        }
    }

    // Validate password
    if (strlen($password) < 8) fieldError($errors, 'password', 'Password must be at least 8 characters.');
    if ($password !== $confirmPassword) fieldError($errors, 'confirmPassword', 'Passwords do not match.');

    // Role-specific validation
    if ($role === 'student') {
        $studentId = trim($_POST['studentId']);
        $course = trim($_POST['course']);
        if (empty($studentId)) fieldError($errors, 'studentId', 'Student ID is required.');
        if (empty($course)) fieldError($errors, 'course', 'Please select your course.');

        // Duplicate student ID
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        if ($stmt->rowCount() > 0) fieldError($errors, 'studentId', 'Student ID already exists.');
    } else {
        $employeeId = trim($_POST['employeeId']);
        $department = trim($_POST['department']);
        $designation = trim($_POST['designation']);
        if (empty($employeeId)) fieldError($errors, 'employeeId', 'Employee ID is required.');
        if (empty($department)) fieldError($errors, 'department', 'Please select a department.');
        if (empty($designation)) fieldError($errors, 'designation', 'Designation is required.');

        // Duplicate employee ID
        $stmt = $pdo->prepare("SELECT * FROM faculty WHERE employee_id = ?");
        $stmt->execute([$employeeId]);
        if ($stmt->rowCount() > 0) fieldError($errors, 'employeeId', 'Employee ID already exists.');
    }

    // Validate and upload profile picture
    $profilePicPath = null;
    if (!empty($_FILES['profilePic']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['profilePic']['type'];
        $fileSize = $_FILES['profilePic']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            fieldError($errors, 'profilePic', 'Only JPG and PNG images are allowed.');
        } elseif ($fileSize > 5 * 1024 * 1024) {
            fieldError($errors, 'profilePic', 'Image must be less than 5MB.');
        } else {
            $uploadDir = "../../resources/Uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = uniqid() . "_" . basename($_FILES['profilePic']['name']);
            $targetPath = $uploadDir . $fileName;
            move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetPath);
            $profilePicPath = $fileName;
        }
    }

    // If errors → return back with session
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $old;
        header("Location: ../../Views/Auth/" . ($role === 'student' ? 'student_register.php' : 'faculty_register.php'));
        exit;
    }

    // If valid → insert into database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'student') {
        $stmt = $pdo->prepare("INSERT INTO students 
            (first_name, middle_name, last_name, extension, student_id, course, email, password, profile_pic, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $middleName, $lastName, $extension, $studentId, $course, $email, $hashedPassword, $profilePicPath, $role]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO faculty 
            (first_name, middle_name, last_name, extension, employee_id, department, designation, email, password, profile_pic, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $middleName, $lastName, $extension, $employeeId, $department, $designation, $email, $hashedPassword, $profilePicPath, $role]);
    }

    unset($_SESSION['old']);
    $_SESSION['success'] = ucfirst($role) . " account created successfully!";
    header("Location: ../../Views/Auth/indexLogin.php");
    exit;
}
?>