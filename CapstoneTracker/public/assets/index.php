<?php
// Load Config
require_once '../config/config.php';

// Load Helpers
require_once '../app/helpers/session_helper.php';
require_once '../app/helpers/url_helper.php';

// Autoload Core Classes
spl_autoload_register(function($className) {
    require_once '../core/' . $className . '.php';
});

// Start Session
session_start();

// Initialize App
$app = new App();