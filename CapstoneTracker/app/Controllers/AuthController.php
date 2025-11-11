<?php



if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2)); // Adjust based on your directory structure
}

require_once __DIR__ . '/Controller.php';

class AuthController extends Controller {
    
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

    public function handleRequest() {
        // Start session at the beginning
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Generate CSRF token if it doesn't exist
            $this->generateCsrfToken();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? ($_GET['action'] ?? ''); // Check both POST and GET
            
            if ($action === 'login') {
                $this->processLogin();
            } elseif ($action === 'googleLogin') {
                $this->googleLogin();
            } else {
                // Handle unknown action - registration actions are now handled by RegistrationController
                $_SESSION['error_message'] = "Invalid action: " . $action;
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $action = $_GET['action'] ?? '';
            
            if ($action === 'logout') {
                $this->logout();
            }
        }
    }
    
    public function processLogin() {
        // Get form data
        error_log("Login attempt - Username: " . ($_POST['email'] ?? 'empty'));
        error_log("Login attempt - Role: " . ($_POST['role'] ?? 'empty'));
    
        // Validate CSRF token first
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($csrfToken)) {
            $this->redirectWithError('Invalid security token. Please try again.');
            return;
        }

        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->redirectWithError('All fields are required.');
            return;
        }
        
        // Clear any existing error message
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        // Authenticate user
        $user = $this->authenticateUser($username, $password, $role);
        
        if ($user) {
            // Create session and redirect
            $this->createUserSession($user);
            $this->redirect('../../app/Views/User/userViewPage.php');
        } else {
            // Debug: Log the error message that was set
            $errorMsg = $_SESSION['error_message'] ?? 'No error message set';
            error_log("Authentication failed with message: " . $errorMsg);
            
            // Ensure we have an error message
            if (!isset($_SESSION['error_message']) || empty($_SESSION['error_message'])) {
                $_SESSION['error_message'] = 'Invalid credentials. Please try again.';
            }
            
            header('Location: ../../app/Views/User/indexLogin.php');
            exit();
        }
    }

    public function googleLogin() {
        // Start output buffering to catch any errors
        ob_start();
        
        try {
            error_log("=== GOOGLE LOGIN DEBUG START ===");
            error_log("POST data: " . print_r($_POST, true));
            
            // Clear any previous output
            while (ob_get_level() > 1) {
                ob_end_clean();
            }
            
            // Set header first to ensure clean JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect_url' => '../../Views/User/userViewPage.php'
            ]);
            exit();
            
            // TEMPORARY: Skip CSRF validation for Google login during development
            error_log("CSRF validation skipped for development");
    
            $credential = $_POST['credential'] ?? '';
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
            $role = $_POST['role'] ?? 'student';
    
            // Basic validation
            if (empty($credential) || empty($email)) {
                throw new Exception('Missing required Google authentication data.');
            }
    
            error_log("Email: " . $email);
            error_log("Name: " . $name);
            error_log("Role: " . $role);
    
            // TEMPORARY: Skip Google token validation during development
            $isValidToken = true;
            error_log("Google token validation SKIPPED for development");
    
            // Check if user exists in your database
            error_log("Checking if user exists in database...");
            $user = $this->findByEmail($email);
            
            if ($user) {
                // Convert user to array if it's an object
                if (is_object($user)) {
                    $user = (array)$user;
                }
                
                // Safely get user properties
                $accStatus = $user['Acc_Status'] ?? $user->Acc_Status ?? 'unknown';
                $userId = $user['ID'] ?? $user->ID ?? null;
                $userEmail = $user['Email'] ?? $user->Email ?? '';
                $firstName = $user['First_Name'] ?? $user->First_Name ?? '';
                $lastName = $user['Last_Name'] ?? $user->Last_Name ?? '';
                $userRole = $user['User_Role'] ?? $user->User_Role ?? '';
                
                error_log("User found in database. Account status: " . $accStatus);
                error_log("User ID: " . $userId);
                error_log("User Role: " . $userRole);
                
                // ✅ EXISTING USER: Check if account is approved
                if ($accStatus === 'pending') {
                    throw new Exception('Your account is pending approval. Please wait for administrator approval.');
                } else if ($accStatus === 'rejected') {
                    throw new Exception('Your account registration was rejected. Please contact the administrator.');
                } else if ($accStatus === 'approved') {
                    // ✅ EXISTING APPROVED USER: Log them in
                    error_log("User account approved, creating session...");
                    $this->createUserSession([
                        'id' => $userId,
                        'email' => $userEmail,
                        'name' => $firstName . ' ' . $lastName,
                        'role' => $userRole
                    ]);
                    
                    error_log("Session created successfully");
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'redirect_url' => '../../Views/User/userViewPage.php'
                    ]);
                    exit();
                } else {
                    throw new Exception('Your account status is invalid. Please contact administrator.');
                }
            } else {
                error_log("User not found in database, starting auto-registration...");
                // ❌ NEW USER: Auto-register them
                $registrationResult = $this->autoRegisterGoogleUser($email, $name, $role);
                error_log("Auto-registration result: " . print_r($registrationResult, true));
                
                if ($registrationResult['success']) {
                    error_log("Auto-registration successful, creating session...");
                    // Log the user in after registration
                    $this->createUserSession([
                        'id' => $registrationResult['user_id'],
                        'email' => $email,
                        'name' => $name,
                        'role' => $role
                    ]);
                    
                    error_log("Session created for new user");
                    echo json_encode([
                        'success' => true,
                        'message' => 'Account created and login successful',
                        'redirect_url' => '../../Views/User/userViewPage.php'
                    ]);
                    exit();
                } else {
                    throw new Exception($registrationResult['message']);
                }
            }
    
        } catch (Exception $e) {
        
            // Clear any output that might have been generated
            ob_clean();

            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
            
            error_log("Google login exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
        } finally {
            // Ensure no extra output
            if (ob_get_length()) {
                ob_end_clean();
            }
        }
    }

    /**
     * Auto-register a user from Google Sign-In
     */
    private function autoRegisterGoogleUser($email, $name, $role) {
        error_log("=== AUTO REGISTRATION DEBUG START ===");
        require_once ROOT_DIR . '\app\Models\User.php';
        
        try {
            $userModel = new User();
            
            // Generate auto password
            $autoPassword = $this->generateAutoPassword();
            error_log("Generated auto password: " . $autoPassword);
            
            // Parse name into first and last name
            $nameParts = $this->parseName($name);
            $firstName = $nameParts['first_name'];
            $lastName = $nameParts['last_name'];
            error_log("Parsed name - First: $firstName, Last: $lastName");
            
            // Determine user role
            $userRole = ($role === 'faculty') ? 'faculty' : 'student';
            error_log("User role: $userRole");
            
            // Generate student/employee ID based on role
            $userIdNumber = $this->generateUserIdNumber($userRole);
            error_log("Generated user ID: $userIdNumber");
            
            // Prepare user data
            $userData = [
                'password' => $autoPassword,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'user_role' => $userRole,
                'acc_status' => 'approved',
                'profile_pic' => base64_decode('R0lGODlhAQABAIAAAAAA/P///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7')
            ];
            
            // Add role-specific fields
            if ($userRole === 'student') {
                $userData['student_id'] = $userIdNumber;
                $userData['course'] = 'Not Specified';
                $userData['designation'] = 'Student';
            } else {
                $userData['employee_id'] = $userIdNumber;
                $userData['department'] = 'Not Specified';
                $userData['designation'] = 'Faculty';
            }
            
            // Register the user
            error_log("Calling userModel->register()...");
            $result = $userModel->register($userData);
            error_log("Register result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                // Get the newly created user ID
                error_log("Getting newly created user...");
                $newUser = $this->findByEmail($email);
                
                if ($newUser) {
                    // Convert to array if it's an object
                    if (is_object($newUser)) {
                        $newUser = (array)$newUser;
                    }
                    
                    $userId = $newUser['ID'] ?? $newUser->ID ?? null;
                    error_log("New user found with ID: " . $userId);
                    
                    // ✅ SEND WELCOME EMAIL WITH PASSWORD
                    $emailSent = $this->sendWelcomeEmail($email, $name, $autoPassword, $userRole);
                    
                    if ($emailSent) {
                        error_log("Welcome email sent successfully to: " . $email);
                    } else {
                        error_log("Failed to send welcome email to: " . $email);
                        // Don't fail the registration if email fails, just log it
                    }
                    
                    error_log("=== AUTO REGISTRATION DEBUG END - SUCCESS ===");
                    return [
                        'success' => true,
                        'user_id' => $userId,
                        'message' => 'Account created successfully' . ($emailSent ? ' and welcome email sent' : '')
                    ];
                } else {
                    error_log("New user not found after registration");
                    return [
                        'success' => false,
                        'message' => 'Account created but could not retrieve user ID'
                    ];
                }
            } else {
                $modelError = $userModel->getError();
                error_log("Registration failed. Model error: " . $modelError);
                return [
                    'success' => false,
                    'message' => $modelError ?: 'Failed to create account. Please try again.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Auto-registration exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Registration error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate auto password for Google users
     */
    private function generateAutoPassword() {
        // Generate a random 12-character password
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Send welcome email to new Google Sign-In users
     */
    private function sendWelcomeEmail($email, $name, $password, $role) {
        try {
            // Use __DIR__ to get the current directory path
            $emailSenderPath = __DIR__ . '/EmailSender.php';
            
            error_log("Looking for EmailSender at: " . $emailSenderPath);
            
            if (!file_exists($emailSenderPath)) {
                error_log("EmailSender.php not found at: " . $emailSenderPath);
                
                // Try alternative relative path
                $emailSenderPath = ROOT_DIR . '/app/Utils/EmailSender.php';
                error_log("Trying alternative path: " . $emailSenderPath);
                
                if (!file_exists($emailSenderPath)) {
                    error_log("EmailSender.php not found at alternative path: " . $emailSenderPath);
                    return false;
                }
            }
            
            error_log("Loading EmailSender from: " . $emailSenderPath);
            require_once $emailSenderPath;
            
            $emailSender = new EmailSender();
            return $emailSender->sendWelcomeEmail($email, $name, $password, $role);
            
        } catch (Exception $e) {
            error_log("Welcome email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse full name into first and last name
     */
    private function parseName($fullName) {
        $nameParts = explode(' ', trim($fullName));
        
        $firstName = $nameParts[0] ?? '';
        $lastName = end($nameParts) ?? '';
        
        // If there's a middle name, include it in first name or handle as needed
        if (count($nameParts) > 2) {
            $firstName = $nameParts[0] . ' ' . $nameParts[1];
            $lastName = $nameParts[2] ?? $lastName;
        }
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }

    /**
     * Generate user ID number based on role
     */
    private function generateUserIdNumber($role) {
        $prefix = ($role === 'faculty') ? 'EMP-' : 'STU-';
        $randomNumber = random_int(10000, 99999);
        return $prefix . $randomNumber;
    }

    private function validateGoogleToken($credential) {
        try {
            error_log("Validating Google token...");
            
            if (empty($credential)) {
                error_log("Google token is empty");
                return false;
            }
    
            // Use Google's tokeninfo endpoint
            $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($credential);
            error_log("Calling Google tokeninfo endpoint...");
            
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if ($response === FALSE) {
                error_log("Failed to call Google tokeninfo endpoint");
                return false;
            }
            
            $data = json_decode($response, true);
            error_log("Google tokeninfo response: " . print_r($data, true));
            
            if (isset($data['error'])) {
                error_log("Google token validation error: " . $data['error']);
                return false;
            }
            
            if (isset($data['email']) && ($data['email_verified'] === 'true' || $data['email_verified'] === true)) {
                error_log("Google token validated successfully for email: " . $data['email']);
                return true;
            }
            
            error_log("Google token validation failed - email not verified or not present");
            return false;
            
        } catch (Exception $e) {
            error_log("Google token validation exception: " . $e->getMessage());
            return false;
        }
    }

	/**
     * Find user by email for Google login flow
     */
    private function findByEmail($email) {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        $db->query('SELECT * FROM USER_INFORMATION WHERE Email = :email LIMIT 1');
        $db->bind(':email', $email);
        $result = $db->single();
        
        // Convert object to array if needed
        if (is_object($result)) {
            $result = (array)$result;
        }
        
        error_log("findByEmail result: " . print_r($result, true));
        return $result;
    }

    private function authenticateUser($username, $password, $role) {
        // Use your User model for authentication
        require_once ROOT_DIR . '\app\Models\User.php';
        
        try {
            $userModel = new User();
            
            // MODIFIED: Use loginByEmail which now returns user regardless of status
            $user = $userModel->loginByEmail($username, $password);
            
            if ($user) {
                // Check if user role matches the selected role
                $userRole = strtolower($user->User_Role ?? '');
                $selectedRole = strtolower($role);
                
                // Map role names for compatibility
                $roleMapping = [
                    'researcher' => 'student',
                    'faculty' => 'faculty'
                ];
                
                $mappedRole = $roleMapping[$selectedRole] ?? $selectedRole;
                
                if ($userRole === $mappedRole) {
                    // Check account status before allowing login
                    if ($user->Acc_Status === 'pending') {
                        $_SESSION['error_message'] = "Your account is pending approval. Please wait for administrator approval before logging in.";
                        return false;
                    } else if ($user->Acc_Status === 'rejected') {
                        $_SESSION['error_message'] = "Your account registration was rejected. Please contact the administrator for more information.";
                        return false;
                    } else if ($user->Acc_Status === 'approved') {
                        // Account is approved - allow login
                        return [
                            'id' => $user->ID,
                            'username' => $user->Email,
                            'email' => $user->Email,
                            'name' => $user->First_Name . ' ' . $user->Last_Name,
                            'role' => $user->User_Role
                        ];
                    } else {
                        // Unknown status
                        $_SESSION['error_message'] = 'Your account status is invalid. Please contact administrator.';
                        return false;
                    }
                } else {
                    error_log("Role mismatch: User role is $userRole, but selected role is $selectedRole");
                    $_SESSION['error_message'] = 'Invalid credentials for the selected role.';
                    return false;
                }
            } else {
                // No user found or password incorrect
                $_SESSION['error_message'] = 'Invalid credentials. Please try again.';
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Authentication error: ' . $e->getMessage();
            return false;
        }
    }

    
    public function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['logged_in'] = true;

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        $this->redirect('../../app/Views/User/indexLogin.php');
    }

    private function redirectWithError($message) {
        $_SESSION['error_message'] = $message;
        header('Location: ../../app/Views/User/indexLogin.php');
        exit();
    }

    protected function redirect($location) {
        header("Location: $location");
        exit();
    }
}

// Instantiate and handle the request if this file is accessed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Check if this is a Google login request
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') === 'google_login') {
        $authController = new AuthController();
        $authController->googleLogin();
        exit();
    } else {
        // Handle other requests normally
        $authController = new AuthController();
        $authController->handleRequest();
    }
}
?>