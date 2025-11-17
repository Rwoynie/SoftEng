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

        // Detect AJAX login from indexLogin.php (FormData appends 'ajax' => '1')
        $isAjax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

        // Validate CSRF token first
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($csrfToken)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid security token. Please try again.'
                ]);
                exit();
            }

            $this->redirectWithError('Invalid security token. Please try again.');
            return;
        }

        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'All fields are required.'
                ]);
                exit();
            }

            $this->redirectWithError('All fields are required.');
            return;
        }
        
        // Clear any existing error message
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        // Authenticate user
        $result = $this->authenticateUser($username, $password, $role, $isAjax);
        
        if ($result['success']) {
            $user = $result['user'];
            // Create session
            $this->createUserSession($user);

            if ($isAjax) {
                // Return JSON success for frontend fetch handler.
                // This URL is resolved relative to indexLogin.php, so use an absolute path.
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'redirect' => '/CapstoneTracker/app/Views/User/userViewPage.php'
                ]);
                exit();
            }

            // Non-AJAX: redirect as before (path is relative to app/Controllers)
            $this->redirect('../Views/User/userViewPage.php');
        } else {
            $error = $result['error'];
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => $error
                ]);
                exit();
            } else {
                $_SESSION['error_message'] = $error;
                header('Location: ../Views/User/indexLogin.php');
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

    private function authenticateUser($username, $password, $role, $isAjax = false) {
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
                        return ['success' => false, 'error' => "Your account is pending approval. Please wait for administrator approval before logging in."];
                    } else if ($user->Acc_Status === 'rejected') {
                        return ['success' => false, 'error' => "Your account registration was rejected. Please contact the administrator for more information."];
                    } else if ($user->Acc_Status === 'approved') {
                        // Account is approved - allow login
                        return ['success' => true, 'user' => [
                            'id' => $user->ID,
                            'username' => $user->Email,
                            'email' => $user->Email,
                            'name' => $user->First_Name . ' ' . $user->Last_Name,
                            'role' => $user->User_Role
                        ]];
                    } else {
                        // Unknown status
                        return ['success' => false, 'error' => 'Your account status is invalid. Please contact administrator.'];
                    }
                } else {
                    error_log("Role mismatch: User role is $userRole, but selected role is $selectedRole");
                    return ['success' => false, 'error' => 'Invalid credentials for the selected role.'];
                }
            } else {
                // No user found or password incorrect
                return ['success' => false, 'error' => 'Invalid credentials. Please try again.'];
            }
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Authentication error: ' . $e->getMessage()];
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
        $this->redirect('../Views/User/indexLogin.php');
    }

    private function redirectWithError($message) {
        $_SESSION['error_message'] = $message;
        header('Location: ../Views/User/indexLogin.php');
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