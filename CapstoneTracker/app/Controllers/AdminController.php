<?php

error_log("=== ADMIN CONTROLLER ACCESSED ===");
error_log("Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));
error_log("Action: " . ($_POST['action'] ?? $_GET['action'] ?? 'NONE'));

require_once __DIR__ . '/Controller.php';

class AdminController extends Controller {

    public function __construct() {
       
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
    private function validateCsrfToken($token, $maxRetries = 3) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $retryCount = 0;
        while ($retryCount < $maxRetries) {
            if (isset($_SESSION['csrf_token']) && 
                hash_equals($_SESSION['csrf_token'], $token)) {
                return true;
            }
            
            // Wait a bit and retry (session might not be ready)
            usleep(50000); // 50ms
            session_write_close();
            session_start();
            $retryCount++;
        }
        
        return false;
    }
    
    public function login() {
        // Start session if not already started - use consistent approach
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Ensure CSRF token exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Check if user is already logged in as admin
        if ($this->isLoggedIn() && $this->isAdmin()) {
            $this->redirectToAdminDashboard();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processAdminLogin();
        } else {
            // Show admin login form
            $this->showAdminLoginForm();
        }
    }
    
    private function processAdminLogin() {

        error_log("=== PROCESS ADMIN LOGIN STARTED ===");

        usleep(100000); // 100ms delay

        $csrfToken = $_POST['csrf_token'] ?? '';

        error_log("CSRF Token from form: " . $csrfToken);
        error_log("CSRF Token from session: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));

        if (!$this->validateCsrfToken($csrfToken)) {
        error_log("CSRF TOKEN VALIDATION FAILED");
        $_SESSION['admin_error_message'] = 'Invalid security token. Please try again.';
        header('Location: ../../app/Views/User/indexLogin.php?admin=1');
        exit();
    }
        // Get form data
        $identifier = $_POST['admin_username'] ?? '';
        $password = $_POST['admin_password'] ?? '';

        error_log("Username: $identifier");
        error_log("Password: " . (!empty($password) ? "SET" : "EMPTY"));
        
        // Validate input
        if (empty($identifier) || empty($password)) {
            $this->redirectWithError('Admin ID and password are required.');
        }
        
        // Authenticate using AuthController logic
        $user = $this->authenticateAdmin($identifier, $password);
        
        if ($user) {
            // Create admin session and redirect to dashboard
            $this->createAdminSession($user);
            $this->redirectToAdminDashboard();
        } else {
            $this->redirectWithError('Invalid admin credentials or insufficient privileges.');
        }
    }
    
    private function authenticateAdmin($identifier, $password) {
        try {
            require_once '../Models/User.php';
            
            $userModel = new User();
    
            // MODIFIED: For admin login, only authenticate by User_ID
            $user = $userModel->loginAdmin($identifier, $password); // We'll create this method
            
            if ($user) {
                // Check if user has admin role
                $userRole = strtolower($user->User_Role ?? '');
                
                if ($userRole === 'subadmin' || $userRole === 'superadmin' || $userRole === 'admin') {
                    error_log("Admin user authenticated: " . $identifier . " (Role: " . $userRole . ")");
                    
                    // Return ALL user data from database
                    return [
                        'id' => $user->ID,
                        'user_id' => $user->User_ID,
                        'email' => $user->Email,
                        'first_name' => $user->First_Name,
                        'last_name' => $user->Last_Name,
                        'middle_name' => $user->Middle_Name ?? '',
                        'extension' => $user->Extension ?? '',
                        'role' => $user->User_Role,
                        'course' => $user->Course ?? null,
                        'department' => $user->Department ?? null,
                        'designation' => $user->Designation ?? null,
                        'employee_id' => $user->Employee_ID ?? null,
                        'student_id' => $user->Student_ID ?? null,
                        'profile_pic' => $user->Profile_Pic ?? null,
                        'date_created' => $user->Date_Created ?? null,
                        'last_login' => $user->Last_Login ?? null
                    ];
                } else {
                    error_log("Admin login attempt by non-admin user: $identifier (Role: $userRole)");
                }
            } else {
                // If login failed, check if user exists but has different status
                $userExists = $userModel->checkUserExists($identifier);
                
                if ($userExists) {
                    $userStatus = $userModel->getUserStatus($identifier);
                    error_log("Admin login failed - user exists but status: " . $userStatus);
                    
                    if ($userStatus === 'pending') {
                        $this->redirectWithError('Your account is pending approval. Please wait for administrator approval.');
                    } elseif ($userStatus === 'rejected') {
                        $this->redirectWithError('Your account has been rejected. Please contact administrator.');
                    }
                }
                
                error_log("No approved admin user found with identifier: " . $identifier);
            }
        } catch (Exception $e) {
            error_log("Admin authentication error: " . $e->getMessage());
        }
        
        return false;
    }
    
    private function createAdminSession($user) {
        // Store only what's needed for functionality and display
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_db_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
        
        // Don't store sensitive or unnecessary data in session
        // Remove these if they were previously stored:
        unset($_SESSION['middle_name']);
        unset($_SESSION['extension']);
        unset($_SESSION['password_hash']);
        // etc.
    }

    public function dashboard() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in and is admin
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirectWithError('Access denied. Admin privileges required.');
            return;
        }
        

        // Prepare COMPLETE data array with ALL session values
        $data = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_db_id' => $_SESSION['user_db_id'] ?? null, 
            'user_email' => $_SESSION['user_email'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
            'first_name' => $_SESSION['first_name'] ?? null,
            'last_name' => $_SESSION['last_name'] ?? null,
            'middle_name' => $_SESSION['middle_name'] ?? null,
            'extension' => $_SESSION['extension'] ?? null,
            'course' => $_SESSION['course'] ?? null,
            'department' => $_SESSION['department'] ?? null,
            'designation' => $_SESSION['designation'] ?? null,
            'employee_id' => $_SESSION['employee_id'] ?? null,
            'student_id' => $_SESSION['student_id'] ?? null,
            'profile_pic' => $_SESSION['profile_pic'] ?? null,
            'date_created' => $_SESSION['date_created'] ?? null,
            'last_login' => $_SESSION['last_login'] ?? null
        ];
        
        // Debug: Log session data
        error_log("=== ADMIN DASHBOARD SESSION DATA ===");
        error_log("Session ID: " . session_id());
        error_log("User DB ID: " . ($data['user_db_id'] ?? 'NOT SET'));
        error_log("All session data: " . print_r($_SESSION, true));
        error_log("Passing to view: " . print_r($data, true));
        
        // Include the AdminDashboard view with data
        $this->loadView('Admin/AdminDashboard', $data);
    }

    private function loadView($viewPath, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            error_log("View file not found: " . $viewFile);
            die("View file not found.");
        }
        exit();
    }
    
    // Also fix the isAdmin() method to check for both admin and superAdmin
    private function isAdmin() {
        $role = $_SESSION['user_role'] ?? '';
        return in_array(strtolower($role), ['superadmin', 'subadmin']);
    }
    
    
    private function redirectToAdminDashboard() {
        header('Location: ../../app/Views/Admin/AdminDashboard.php');
        exit();
    }
    
    private function showAdminLoginForm() {
        // You can create a separate admin login view or use the existing one
        // For now, we'll redirect to the modal in indexLogin.php
        header('Location: ../../app/Views/User/indexLogin.php?admin=1');
        exit();
    }
    
    private function redirectWithError($message) {
        $_SESSION['admin_error_message'] = $message;
        header('Location: ../../app/Views/User/indexLogin.php?admin=1');
        exit();
    }
    
    protected function redirect($location) {
        header("Location: $location");
        exit();
    }
    
    // Logout method for admin
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        $this->redirect('../../app/Views/User/indexLogin.php');
    }
}


// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminController = new AdminController();

// Handle the request based on action
$action = $_POST['action'] ?? $_GET['action'] ?? 'login';

if ($action === 'login') {
    $adminController->login();
} elseif ($action === 'dashboard') {
    $adminController->dashboard();
} elseif ($action === 'logout') {
    $adminController->logout();
}
?>