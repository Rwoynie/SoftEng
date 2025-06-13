<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php'; 

$dotenvPath = dirname(__DIR__) . '/.env';
if (!file_exists($dotenvPath)) {
    die("Error: .env file not found at: " . $dotenvPath);
}

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__)); 
$dotenv->load();

// Database Connection Configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$database = $_ENV['DB_NAME'] ?? 'COMPENDIUM';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// Create Database if it doesn't exist
if (!$conn->query("CREATE DATABASE IF NOT EXISTS $database")) {
    error_log("Database creation failed: " . $conn->error);
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "message" => "Database creation failed: " . $conn->error]));
}

// Select Database
if (!$conn->select_db($database)) {
    error_log("Failed to select database '$database': " . $conn->error);
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "message" => "Failed to select database: " . $conn->error]));
}

// Create Tables




?>