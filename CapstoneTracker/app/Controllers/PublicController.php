<?php
class PublicController extends Controller {
    public function index() {
        $data = [
            'title' => 'Home',
            'description' => 'A digital library for USeP student research.'
        ];
        $this->view('public/home', $data);
    }
}