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
        // Handle Google login logic here
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process Google authentication
            // This would verify the Google token and create user session
            
            // After successful authentication:
            $user = [/* user data from Google */];
            $this->createUserSession($user);
            $this->redirect('user/dashboard');
        }
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
    
    $authController = new AuthController();
    $authController->handleRequest();
}
?>