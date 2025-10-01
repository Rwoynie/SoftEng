<?php

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2)); // Adjust based on your directory structure
}

require_once __DIR__ . '/Controller.php';

class AuthController extends Controller {
    
    public function handleRequest() {
        // Start session at the beginning
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        // Role may not always be posted (e.g., direct modal open after redirect) – handle gracefully
        $role = $_POST['role'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->redirectWithError('All fields are required.');
        }
        
        // Authenticate user
        $user = $this->authenticateUser($username, $password, $role);
        
        if ($user) {
            // Create session and redirect
            $this->createUserSession($user);
            $this->redirect('../../app/Views/User/userViewPage.php');
        } else {
            // Only redirect with error if no specific error message was already set
            if (!isset($_SESSION['error_message']) || empty($_SESSION['error_message'])) {
                $this->redirectWithError('Invalid credentials. Please try again.');
            } else {
                // Redirect with the existing error message
                
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }
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
            
            // First, check if user exists and get their status (regardless of approval)
            $userStatus = $userModel->getUserStatus($username);
            error_log("User status for $username: " . ($userStatus ?? 'null'));
            
            if ($userStatus === 'pending') {
                $_SESSION['error_message'] = "Your account is pending for approval. <br>Please wait for administrator approval before logging in.";
                error_log("Setting pending message for user: $username");
                return false;
            }
            
            if ($userStatus === 'rejected') {
                $_SESSION['error_message'] = "Your account has been rejected. Please contact the administrator for more information.";
                error_log("Setting rejected message for user: $username");
                return false;
            }
            
            // If account status is approved or we don't know the status, try to login
            // Use loginWithStatus to get user regardless of approval status
            $user = $userModel->loginWithStatus($username, $password);
            
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
                    // Double-check that the account is approved before allowing login
                    if ($user->Acc_Status === 'approved') {
                        return [
                            'id' => $user->ID,
                            'username' => $user->Email, // Using email as username
                            'email' => $user->Email,
                            'name' => $user->First_Name . ' ' . $user->Last_Name,
                            'role' => $user->User_Role
                        ];
                    } else {
                        // Account exists but not approved
                        $_SESSION['error_message'] = "Your account is pending approval. Please wait for approval before logging in.";
                        return false;
                    }
                } else {
                    error_log("Role mismatch: User role is $userRole, but selected role is $selectedRole");
                }
            }
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Authentication error: ' . $e->getMessage();
        }
        
        // Only set default error message if no specific error was already set
        if (!isset($_SESSION['error_message']) || empty($_SESSION['error_message'])) {
            $_SESSION['error_message'] = 'Invalid credentials. Please try again.';
        } else {
            // Debug: Log what error message is being set
            error_log("Setting error message: " . $_SESSION['error_message']);
        }
        return false;
    }

    
    public function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['logged_in'] = true;
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