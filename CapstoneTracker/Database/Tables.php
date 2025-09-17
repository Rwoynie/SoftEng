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

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    header('Content-Type: application/json');
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}
// Create Tables

// Create Tables

// STUDENT_INFORMATION TABLE (Improved)
$createStudentTable = "CREATE TABLE IF NOT EXISTS STUDENT_INFORMATION (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pswrd VARCHAR(255) NOT NULL,
    Salt VARCHAR(255) NOT NULL,
    First_Name VARCHAR(50) NOT NULL,
    Middle_Name VARCHAR(50),
    Last_Name VARCHAR(50) NOT NULL,
    Extension VARCHAR(20),
    Email VARCHAR(255) UNIQUE NOT NULL,
    Student_ID VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (Email),
    INDEX (Student_ID)
) ENGINE=InnoDB;";

// THESIS TABLE (Improved - store file path instead of BLOB)
$createThesisTable = "CREATE TABLE IF NOT EXISTS THESIS (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11) UNSIGNED NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Author VARCHAR(255) NOT NULL,
    File_Path VARCHAR(500) NOT NULL,  -- Store file path instead of BLOB
    File_Size INT(11) NOT NULL,
    File_Type VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES STUDENT_INFORMATION(ID) ON DELETE CASCADE,
    INDEX (student_id),
    INDEX (Title)
) ENGINE=InnoDB;";

// THESIS_REVIEWS TABLE (Additional table for reviews)
$createReviewTable = "CREATE TABLE IF NOT EXISTS THESIS_REVIEWS (
    ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT(11) UNSIGNED NOT NULL,
    reviewer_id INT(11) UNSIGNED NOT NULL,
    rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thesis_id) REFERENCES THESIS(ID) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES STUDENT_INFORMATION(ID) ON DELETE CASCADE,
    UNIQUE KEY unique_review (thesis_id, reviewer_id)
) ENGINE=InnoDB;";

$queries = [
    $createStudentTable,
    $createThesisTable,
    $createReviewTable
];

// Execute each query and handle errors
$success = true;
$errors = [];

foreach ($queries as $index => $query) {
    if ($conn->query($query) !== TRUE) {
        $errorMsg = "Table creation failed for query " . ($index + 1) . ": " . $conn->error;
        error_log($errorMsg);
        $errors[] = $errorMsg;
        $success = false;
    }
}

// Close connection
$conn->close();

// Output result
header('Content-Type: application/json');
if ($success) {
    echo json_encode([
        "success" => true, 
        "message" => "All tables created successfully!",
        "tables_created" => ["STUDENT_INFORMATION", "THESIS", "THESIS_REVIEWS"]
    ]);
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Some table creations failed",
        "errors" => $errors
    ]);
}
?>