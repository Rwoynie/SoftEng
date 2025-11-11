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
            } elseif ($action === 'forgot_password') {
                $this->processForgotPassword();
            } elseif ($action === 'reset_password') {
                $this->processResetPassword();
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
        
        // Check if this is an AJAX request - check multiple indicators
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (!empty($_SERVER['HTTP_ACCEPT']) && 
                   strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');
        
        // Debug logging
        error_log("AJAX detection - X-Requested-With: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set'));
        error_log("AJAX detection - Accept: " . ($_SERVER['HTTP_ACCEPT'] ?? 'not set'));
        error_log("AJAX detection - POST ajax: " . ($_POST['ajax'] ?? 'not set'));
        error_log("AJAX detection result: " . ($isAjax ? 'true' : 'false'));
        
        // Validate input
        if (empty($username) || empty($password)) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'All fields are required.');
                return;
            }
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
            if ($isAjax) {
                // Return path relative to current page location (indexLogin.php is in same dir as userViewPage.php)
                $this->sendJsonResponse(true, 'Login successful', 'userViewPage.php');
                return;
            }
            $this->redirect('../Views/User/userViewPage.php');
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

    /**
     * Process forgot password request
     */
    public function processForgotPassword() {
        // Check if this is an AJAX request
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (!empty($_SERVER['HTTP_ACCEPT']) && 
                   strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($csrfToken)) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Invalid security token. Please try again.');
                return;
            }
            $this->redirectWithError('Invalid security token. Please try again.');
            return;
        }

        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'student';

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Please enter a valid email address.');
                return;
            }
            $this->redirectWithError('Please enter a valid email address.');
            return;
        }

        // Check if user exists
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        
        try {
            // Check if email exists in database
            $sql = "SELECT ID, Email, User_Role, First_Name, Last_Name FROM USER_INFORMATION WHERE Email = :email";
            $db = $userModel->getDb();
            $db->query($sql);
            $db->bind(':email', $email);
            $user = $db->single();

            if (!$user) {
                // Don't reveal if email exists for security
                if ($isAjax) {
                    $this->sendJsonResponse(true, 'If the email exists, a reset code will be sent.');
                    return;
                }
                $this->redirectWithError('If the email exists, a reset code will be sent.');
                return;
            }

            // Check if role matches
            $userRole = strtolower($user->User_Role ?? '');
            $selectedRole = strtolower($role);
            $roleMapping = [
                'researcher' => 'student',
                'faculty' => 'faculty'
            ];
            $mappedRole = $roleMapping[$selectedRole] ?? $selectedRole;

            if ($userRole !== $mappedRole) {
                if ($isAjax) {
                    $this->sendJsonResponse(false, 'Invalid email for the selected role.');
                    return;
                }
                $this->redirectWithError('Invalid email for the selected role.');
                return;
            }

            // Generate 6-digit reset code
            $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store reset code in session with expiration (15 minutes)
            $_SESSION['password_reset'] = [
                'email' => $email,
                'code' => $resetCode,
                'expires' => time() + (15 * 60), // 15 minutes
                'role' => $role
            ];

            // Send email with reset code
            try {
                require_once ROOT_DIR . '\app\Models\EmailService.php';
                $emailService = new EmailService();
                
                // Get user's full name for email
                $userFullName = ($user->First_Name ?? '') . ' ' . ($user->Last_Name ?? 'User');
                if (trim($userFullName) === '') {
                    $userFullName = 'User';
                }
                
                // Send password reset email
                $emailService->sendPasswordResetEmail($email, $userFullName, $resetCode);
                
                error_log("Password reset email sent to $email");
                
                if ($isAjax) {
                    $this->sendJsonResponse(true, 'Reset code has been sent to your email address. Please check your inbox.');
                    return;
                }
                $_SESSION['success_message'] = 'Reset code has been sent to your email address.';
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
                
            } catch (Exception $emailException) {
                // Log email error but don't reveal it to user for security
                error_log("Failed to send password reset email to $email: " . $emailException->getMessage());
                
                // Still return success to user (security best practice - don't reveal if email exists)
                // But log the code for manual recovery if needed
                error_log("Password reset code for $email (email failed): $resetCode");
                
                if ($isAjax) {
                    // In development, you might want to return the code if email fails
                    // In production, just return a generic message
                    $devMode = ($_ENV['APP_ENV'] ?? 'production') === 'development';
                    if ($devMode) {
                        $this->sendJsonResponse(true, 'Email sending failed. Reset code: ' . $resetCode . ' (Check logs)');
                    } else {
                        $this->sendJsonResponse(true, 'If the email exists, a reset code has been sent. Please check your inbox.');
                    }
                    return;
                }
                $_SESSION['success_message'] = 'If the email exists, a reset code has been sent.';
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }
            
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            if ($isAjax) {
                $this->sendJsonResponse(false, 'An error occurred. Please try again.');
                return;
            }
            $this->redirectWithError('An error occurred. Please try again.');
        }
    }

    /**
     * Process password reset
     */
    public function processResetPassword() {
        // Check if this is an AJAX request
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                  (!empty($_SERVER['HTTP_ACCEPT']) && 
                   strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                  (isset($_POST['ajax']) && $_POST['ajax'] == '1');

        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($csrfToken)) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Invalid security token. Please try again.');
                return;
            }
            $this->redirectWithError('Invalid security token. Please try again.');
            return;
        }

        $email = $_POST['email'] ?? '';
        $resetCode = $_POST['reset_code'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'student';

        // Validate inputs
        if (empty($email) || empty($resetCode) || empty($newPassword) || empty($confirmPassword)) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'All fields are required.');
                return;
            }
            $this->redirectWithError('All fields are required.');
            return;
        }

        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Passwords do not match.');
                return;
            }
            $this->redirectWithError('Passwords do not match.');
            return;
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Password must be at least 8 characters long.');
                return;
            }
            $this->redirectWithError('Password must be at least 8 characters long.');
            return;
        }

        // Check if reset code exists and is valid
        if (!isset($_SESSION['password_reset'])) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Invalid or expired reset code. Please request a new one.');
                return;
            }
            $this->redirectWithError('Invalid or expired reset code. Please request a new one.');
            return;
        }

        $resetData = $_SESSION['password_reset'];

        // Check if expired
        if (time() > $resetData['expires']) {
            unset($_SESSION['password_reset']);
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Reset code has expired. Please request a new one.');
                return;
            }
            $this->redirectWithError('Reset code has expired. Please request a new one.');
            return;
        }

        // Check if email matches
        if ($resetData['email'] !== $email) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Invalid reset code for this email.');
                return;
            }
            $this->redirectWithError('Invalid reset code for this email.');
            return;
        }

        // Check if code matches
        if ($resetData['code'] !== $resetCode) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Invalid reset code.');
                return;
            }
            $this->redirectWithError('Invalid reset code.');
            return;
        }

        // Update password in database
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();

        try {
            // Generate new salt and hash password
            $salt = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($newPassword . $salt, PASSWORD_DEFAULT);

            // Update password and salt
            $sql = "UPDATE USER_INFORMATION SET pswrd = :pswrd, Salt = :salt WHERE Email = :email";
            $db->query($sql);
            $db->bind(':pswrd', $hashedPassword);
            $db->bind(':salt', $salt);
            $db->bind(':email', $email);
            $db->execute();

            // Clear reset code from session
            unset($_SESSION['password_reset']);

            if ($isAjax) {
                $this->sendJsonResponse(true, 'Password reset successfully. You can now login with your new password.');
                return;
            }
            $_SESSION['success_message'] = 'Password reset successfully. You can now login with your new password.';
            header('Location: ../../app/Views/User/indexLogin.php');
            exit();

        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            if ($isAjax) {
                $this->sendJsonResponse(false, 'An error occurred while resetting your password. Please try again.');
                return;
            }
            $this->redirectWithError('An error occurred while resetting your password. Please try again.');
        }
    }

    protected function redirect($location) {
        header("Location: $location");
        exit();
    }
    
    private function sendJsonResponse($success, $message, $redirect = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'error' => $success ? null : $message,
            'message' => $success ? $message : null,
            'redirect' => $redirect
        ]);
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