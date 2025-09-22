<?php
class AdminController extends Controller {
    public function dashboard() {
        // Check if user is logged in and is admin
        if (!isLoggedIn()) {
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
        $this->view('admin/dashboard', $data);
    }
    
    private function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    // Add other admin methods here
}