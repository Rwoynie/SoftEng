<?php
class Controller {
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }
    
    public function view($view, $data = []) {
        if (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/layouts/main.php';
        } else {
            die('View does not exist');
        }
    }
    
    public function redirect($url) {
        header('Location: ' . URLROOT . '/' . $url);
        exit();
    }
}