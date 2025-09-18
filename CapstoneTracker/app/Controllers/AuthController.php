<?php
class AuthController extends Controller {
    
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('../View/User/userViewPage.php');
        }
        
        // Display your login page directly
        require_once __DIR__ . '/../indexLogin.php';
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
    
    public function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
    }
    
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        session_destroy();
        $this->redirect('auth/login');
    }
}
?>