<?php
class UserController extends Controller {
    public function dashboard() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        $thesisModel = $this->model('Thesis');
        $theses = $thesisModel->getUserTheses($_SESSION['user_id']);
        
        $data = [
            'title' => 'Dashboard',
            'theses' => $theses
        ];
        $this->view('User/dashboard', $data);
    }
    
    public function profile() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
        
        $userModel = $this->model('User');
        $user = $userModel->getUserById($_SESSION['user_id']);
        
        $data = [
            'title' => 'Profile',
            'user' => $user
        ];
        $this->view('User/profile', $data);
    }
    
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process file upload
            $thesisModel = $this->model('Thesis');
            
            if ($thesisModel->uploadThesis($_POST, $_FILES)) {
                $this->redirect('user/dashboard');
            } else {
                // Upload failed
                $data = [
                    'title' => 'Upload Thesis',
                    'error' => 'Failed to upload thesis'
                ];
                $this->view('User/upload', $data);
            }
        } else {
            $data = [
                'title' => 'Upload Thesis'
            ];
            $this->view('User/upload', $data);
        }
    }
}