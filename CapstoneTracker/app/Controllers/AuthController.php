<?php

require_once __DIR__ . '/Controller.php';

class AuthController extends Controller {
    
    public function handleRequest() {
        // Start session at the beginning
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'login') {
                $this->processLogin();
            } elseif ($action === 'googleLogin') {
                $this->googleLogin();
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
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password) || empty($role)) {
            $this->redirectWithError('All fields are required.');
        }
        
        // Authenticate user
        $user = $this->authenticateUser($username, $password, $role);
        
        if ($user) {
            // Create session and redirect
            $this->createUserSession($user);
            $this->redirect('../View/User/userViewPage.php');
        } else {
            $this->redirectWithError('Invalid credentials. Please try again.');
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
        // Database connection
        require_once ROOT_DIR . '\Database\config.php';
        
        // Determine table based on role
        $table = ($role === 'Faculty') ? 'faculty' : 'researchers';
        
        // Query database
        $query = "SELECT * FROM $table WHERE username = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            error_log("Database error: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (assuming passwords are hashed)
            if (password_verify($password, $user['password'])) {
                return [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $role
                ];
            } else {
                error_log("Password verification failed for user: $username");
            }
        } else {
            error_log("User not found: $username in table: $table");
        }
        
        return false;
    }



    
    public function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        $this->redirect('../../indexLogin.php');
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