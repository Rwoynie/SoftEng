<?php

// config.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/Models/Database.php';
require_once __DIR__ . '/../vendor/autoload.php'; 
// Load helper functions
require_once __DIR__ . '/../app/helpers/functions.php';


$dotenvPath = dirname(__DIR__) . '/.env';
if (!file_exists($dotenvPath)) {
    die("Error: .env file not found at: " . $dotenvPath);
}

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); 
$dotenv->load();

// Define BASE_URL
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration (for PDO)
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);
define('URLROOT', 'http://localhost/CapstoneTracker');

// Remove the MySQLi connection code since we're using PDO through Database class
// The Database class will handle the connection using these constants
?>