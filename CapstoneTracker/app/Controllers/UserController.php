<?php

require_once __DIR__ . '/Controller.php';
class UserController extends Controller {
    public function dashboard() {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        $thesisModel = $this->model('Thesis');
        $theses = $thesisModel->getUserTheses($_SESSION['user_id']);
        
        $data = [
            'title' => 'Dashboard',
            'theses' => $theses
        ];
        $this->view('user/dashboard', $data);
    }
    
    public function profile() {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        $userModel = $this->model('User');
        $user = $userModel->getUserById($_SESSION['user_id']);
        
        $data = [
            'title' => 'Profile',
            'user' => $user
        ];
        $this->view('user/profile', $data);
    }

    public function handleRequest() {
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if (method_exists($this, $action)) {
                $this->$action();
                return;
            }
        }
        // Default action or error
        $this->redirect('auth/login');
    }
    
    public function upload() {
        if (!isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process file upload
            $thesisModel = $this->model('Thesis');
            
            if ($thesisModel->uploadThesis($_POST, $_FILES, $_SESSION['User_ID'])) {
                $_SESSION['success'] = 'Thesis uploaded successfully!';
                $this->redirect('user/dashboard');
            } else {
                // Upload failed - show error
                $data = [
                    'title' => 'Upload Thesis',
                    'error' => $thesisModel->getError() ?: 'Failed to upload thesis. Please try again.'
                ];
                $this->view('user/upload', $data);
            }
        } else {
            $data = [
                'title' => 'Upload Thesis'
            ];
            $this->view('user/upload', $data);
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $controller = new UserController();
    $controller->handleRequest();
}