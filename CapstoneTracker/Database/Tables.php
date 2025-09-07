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

$createThesisTable = "CREATE TABLE IF NOT EXISTS THESIS (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Author VARCHAR(255) NOT NULL,
    
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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