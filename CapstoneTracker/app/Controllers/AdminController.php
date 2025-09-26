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
    
    public function login() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
    
    // Get form data
    $identifier = $_POST['admin_username'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    
    error_log("Username: $identifier");
    error_log("Password: " . (!empty($password) ? "SET" : "EMPTY"));
    
        // Get form data
        $identifier = $_POST['admin_username'] ?? '';
        $password = $_POST['admin_password'] ?? '';
        
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
            // Reuse the User model for authentication
            require_once '..\..\Models\User.php';
            
            $userModel = new User();
            $user = $userModel->login($identifier, $password);
            
            if ($user) {
                // Check if user has admin role
                $userRole = strtolower($user->User_Role ?? '');
                
                if ($userRole === 'admin') {
                    return [
                        'id' => $user->ID,
                        'username' => $user->User_ID, // Using User_ID as username
                        'email' => $user->Email,
                        'name' => $user->First_Name . ' ' . $user->Last_Name,
                        'role' => $user->User_Role
                    ];
                } else {
                    error_log("Admin login attempt by non-admin user: $identifier (Role: $userRole)");
                }
            }
        } catch (Exception $e) {
            error_log("Admin authentication error: " . $e->getMessage());
        }
        
        return false;
    }
    
    private function createAdminSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true; // Additional flag for admin
    }
    
    public function dashboard() {
        // Check if user is logged in and is admin
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        if (!$this->isAdmin()) {
            // Redirect to user dashboard or show error
            $this->redirect('user/dashboard');
        }
        
        $thesisModel = $this->model('Thesis');
        $theses = $thesisModel->getAllTheses();
        
        $data = [
            'title' => 'Admin Dashboard',
            'theses' => $theses
        ];
        
        // Load the AdminDashboard view
        $this->view('admin/dashboard', $data);
    }
    
    
    
    private function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
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
}
?>