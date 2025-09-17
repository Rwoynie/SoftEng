<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
class Controller {
    /**
     * Load a model
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }
    
    /**
     * Load a view
     * @param string $view View path
     * @param array $data Data to pass to view
     */
    protected function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
    
    /**
     * Redirect to a different page
     * @param string $path Redirect path
     */
    protected function redirect($path) {
        header('Location: ' . BASE_URL . $path);
        exit();
    }
    
    /**
     * Check if user is logged in (for controllers that extend this)
     * @return bool True if logged in
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Require login for protected pages
     */
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }
}
?>