<?php
// config.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/Models/Database.php';

// Resolve project root reliably (one level up from Database directory)
$projectRoot = realpath(__DIR__ . '/..');
if ($projectRoot === false) {
    http_response_code(500);
    die("Failed to resolve project root from: " . __DIR__);
}

// Composer autoload - check in project root/vendor/autoload.php

$vendorAutoload = $projectRoot . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    http_response_code(500);
    die("Composer autoload not found. Run 'composer install' in project root: " . $projectRoot);
}

// Now use phpdotenv (class exists only after autoload)
use Dotenv\Dotenv;

// .env path check
$dotenvPath = $projectRoot . '/.env';
if (!file_exists($dotenvPath)) {
    // Not fatal if you don't use .env — change to a warning if desired
    http_response_code(500);
    die("Error: .env file not found at: " . $dotenvPath . ". Create one or copy .env.example from the project root.");
}

try {
    if (!class_exists(\Dotenv\Dotenv::class)) {
        die("vlucas/phpdotenv is not installed. Run 'composer require vlucas/phpdotenv' or 'composer install'.");
    }
    $dotenv = Dotenv::createImmutable($projectRoot);
    $dotenv->load();
} catch (Exception $e) {
    error_log('Dotenv load error: ' . $e->getMessage());
    die("Failed to load environment variables: " . $e->getMessage());
}

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