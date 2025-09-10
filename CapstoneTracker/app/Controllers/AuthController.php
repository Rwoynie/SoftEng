<?php
class AuthController extends Controller {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process login form
            $userModel = $this->model('User');
            $user = $userModel->login($_POST['email'], $_POST['password']);
            
            if ($user) {
                // Create session
                $this->createUserSession($user);
                $this->redirect('user/dashboard');
            } else {
                // Login failed
                $data = [
                    'email' => $_POST['email'],
                    'password' => '',
                    'email_err' => 'Invalid credentials',
                    'password_err' => 'Invalid credentials'
                ];
                $this->view('auth/login', $data);
            }
        } else {
            // Load form
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => ''
            ];
            $this->view('auth/login', $data);
        }
    }
    
    public function createUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
    }
    
    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        session_destroy();
        $this->redirect('public/index');
    }
}