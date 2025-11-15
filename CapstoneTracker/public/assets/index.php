<?php
// Load Config
require_once '../config/config.php';

// Load Helpers
require_once '../app/helpers/session_helper.php';
require_once '../app/helpers/url_helper.php';

// public/index.php or your entry point
require_once __DIR__ . '/../Database/config.php';
require_once __DIR__ . '/../app/Controllers/PublicHomeController.php';

$controller = new PublicHomeController();
$controller->index();


// Autoload Core Classes
spl_autoload_register(function($className) {
    require_once '../core/' . $className . '.php';
});

// Start Session
session_start();

// Initialize App
$app = new App();