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
$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];

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


// STUDENT_INFORMATION TABLE
$createStudentTable = "CREATE TABLE IF NOT EXISTS STUDENT_INFORMATION (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pswrd VARCHAR(255) NOT NULL,
    Salt VARCHAR(255) NOT NULL,
    First_Name VARCHAR(50) NOT NULL,
    Middle_Name VARCHAR(50),
    Last_Name VARCHAR(50) NOT NULL,
    Extension VARCHAR(20),
    Email VARCHAR(255) NOT NULL,
    Student_ID VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (Email)
) ENGINE=InnoDB;";

$createThesisTable = "CREATE TABLE IF NOT EXISTS THESES (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Author VARCHAR(255) NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
) ENGINE=InnoDB;";

$queries = [
    $createStudentTable,
    $createThesisTable
];

// Execute each query and handle errors
foreach ($queries as $query) {
    if ($conn->query($query) !== TRUE) {
        error_log("Table creation failed: " . $conn->error);
        header('Content-Type: application/json');
        die(json_encode(["success" => false, "message" => "Table creation failed: " . $conn->error]));
    }
}

?>