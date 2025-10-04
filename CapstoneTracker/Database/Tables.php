<?php
/**
 * Database Schema Manager
 * Handles table definitions, database creation, and setup operations
 */

 class DatabaseSchema {
    private $db;
    private $error = null;
    
    public function __construct($database = null) {
        if ($database) {
            require_once __DIR__ . '/../app/Models/Database.php';
            $this->db = $database;
        }
    }
    
    /**
     * Get all table creation queries
     */
    public static function getTableQueries() {
        return [
            // USER_INFORMATION TABLE (UPDATED WITH ADDITIONAL FIELDS)
            "CREATE TABLE IF NOT EXISTS USER_INFORMATION (
                ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                pswrd VARCHAR(255) NOT NULL,
                Salt VARCHAR(255) NOT NULL,
                First_Name VARCHAR(50) NOT NULL,
                Middle_Name VARCHAR(50),
                Last_Name VARCHAR(50) NOT NULL,
                Extension VARCHAR(20),
                Email VARCHAR(255) UNIQUE NOT NULL,
                User_ID VARCHAR(255) UNIQUE NOT NULL,
                Student_ID VARCHAR(255) UNIQUE,
                Employee_ID VARCHAR(255) UNIQUE,
                User_Role ENUM('student', 'faculty', 'admin', 'superAdmin') NOT NULL,
                Acc_Status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                Department VARCHAR(255) NOT NULL,
                Course VARCHAR(255) NOT NULL,   
                Designation VARCHAR(255),
                Profile_Pic BLOB,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (Email),
                INDEX (User_ID),
                INDEX (Student_ID),
                INDEX (Employee_ID),
                INDEX (User_Role),
                INDEX (Acc_Status)
            ) ENGINE=InnoDB;",
            
            // THESIS TABLE
            "CREATE TABLE IF NOT EXISTS THESIS (
                ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                User_ID INT(11) UNSIGNED NOT NULL,
                Thesis_Department VARCHAR(255) NOT NULL,
                Thesis_Course VARCHAR(255) NOT NULL,
                Thesis_Email VARCHAR(255) NOT NULL,
                Title VARCHAR(255) NOT NULL,
                Author VARCHAR(255) NOT NULL,
                Thesis_AbstractFile LONGBLOB NOT NULL,
                Thesis_File LONGBLOB NOT NULL,    
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (User_ID) REFERENCES USER_INFORMATION(ID) ON DELETE CASCADE,
                INDEX (User_ID),
                INDEX (Title)
            ) ENGINE=InnoDB;",

            

            // THESIS_REVIEWS TABLE
            "CREATE TABLE IF NOT EXISTS THESIS_REVIEWS (
                ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                thesis_id INT(11) UNSIGNED NOT NULL,
                reviewer_id INT(11) UNSIGNED NOT NULL,
                rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
                comments TEXT,
                reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (thesis_id) REFERENCES THESIS(ID) ON DELETE CASCADE,
                FOREIGN KEY (reviewer_id) REFERENCES USER_INFORMATION(ID) ON DELETE CASCADE,
                UNIQUE KEY unique_review (thesis_id, reviewer_id)
            ) ENGINE=InnoDB;"
        ];
    }
    
    /**
     * Get ordered table queries (ensures proper foreign key relationships)
     */
    public static function getOrderedTableQueries() {
        $queries = self::getTableQueries();
        
        // Simple ordering to ensure tables with foreign keys are created after referenced tables
        usort($queries, function($a, $b) {
            $priority = [
                'USER_INFORMATION' => 0,
                'THESIS' => 1,
                'THESIS_REVIEWS' => 2
            ];
            
            $tableA = self::extractTableName($a);
            $tableB = self::extractTableName($b);
            
            return ($priority[$tableA] ?? 999) <=> ($priority[$tableB] ?? 999);
        });
        
        return $queries;
    }
    
    /**
     * Extract table name from CREATE TABLE query
     */
    private static function extractTableName($query) {
        if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $query, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    /**
     * Get default admin account data
     */
    public static function getDefaultAdminData() {
        $password = 'compendiumSystemAdmin';
        $salt = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);
        
        return [
            'pswrd' => $hashedPassword,
            'Salt' => $salt,
            'First_Name' => 'Super',
            'Middle_Name' => 'Admin',
            'Last_Name' => 'Admin',
            'Extension' => null,
            'Email' => 'admin@usep.edu.ph',
            'User_ID' => 'ADMIN001',
            'Student_ID' => null,
            'Employee_ID' => null,
            'User_Role' => 'superAdmin',
            'Acc_Status' => 'approved',
            'Department' => 'Administration',
            'Course' => 'Administration',
            'Designation' => 'System Administrator',
            'Profile_Pic' => null
        ];
    }
    
    /**
     * Create database if it doesn't exist
     */
    public function createDatabase($host, $username, $password, $databaseName) {
        try {
            $conn = new PDO("mysql:host=$host", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "CREATE DATABASE IF NOT EXISTS $databaseName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $conn->exec($sql);
            
            // Test if we can connect to the new database
            $conn2 = new PDO("mysql:host=$host;dbname=$databaseName", $username, $password);
            $conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn2 = null;
            
            $conn = null;
            
            return true;
        } catch (PDOException $e) {
            $this->error = "Database creation failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Create all tables
     */
    public function createTables() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $queries = self::getOrderedTableQueries();
        
        foreach ($queries as $query) {
            try {
                $this->db->query($query);
                $this->db->execute();
            } catch (PDOException $e) {
                $this->error = "Table creation failed for '" . self::extractTableName($query) . "': " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Create default admin account
     */
    public function createDefaultAdmin() {
        try {
            $adminData = self::getDefaultAdminData();
            
            $this->db->query("SELECT ID FROM USER_INFORMATION WHERE Email = :email");
            $this->db->bind(':email', $adminData['Email']);
            $this->db->execute();
            
            if ($this->db->rowCount() == 0) {
                // Build the query with all fields
                $this->db->query("INSERT INTO USER_INFORMATION 
                    (pswrd, Salt, First_Name, Middle_Name, Last_Name, Extension, Email, User_ID, Student_ID, Employee_ID, User_Role, Acc_Status, Department, Course , Designation, Profile_Pic) 
                    VALUES 
                    (:password, :salt, :first_name, :middle_name, :last_name, :extension, :email, :user_id, :student_id, :employee_id, :user_role, :acc_status, :department, :course, :designation, :profile_pic)");
                
                // Bind all parameters
                $this->db->bind(':password', $adminData['pswrd']);
                $this->db->bind(':salt', $adminData['Salt']);
                $this->db->bind(':first_name', $adminData['First_Name']);
                $this->db->bind(':middle_name', $adminData['Middle_Name']);
                $this->db->bind(':last_name', $adminData['Last_Name']);
                $this->db->bind(':extension', $adminData['Extension']);
                $this->db->bind(':email', $adminData['Email']);
                $this->db->bind(':user_id', $adminData['User_ID']);
                $this->db->bind(':student_id', $adminData['Student_ID']);
                $this->db->bind(':employee_id', $adminData['Employee_ID']);
                $this->db->bind(':user_role', $adminData['User_Role']);
                $this->db->bind(':acc_status', $adminData['Acc_Status']);
                $this->db->bind(':department', $adminData['Department']);
                $this->db->bind(':course', $adminData['Course']);
                $this->db->bind(':designation', $adminData['Designation']);
                $this->db->bind(':profile_pic', $adminData['Profile_Pic']);
                
                $this->db->execute();
                return true;
            }
            return false; // Admin already exists
        } catch (Exception $e) {
            $this->error = "Admin creation failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Check if database is initialized
     */
    public function isDatabaseInitialized() {
        if (!$this->db || !$this->db->isConnected()) {
            return false;
        }
        
        try {
            $this->db->query("SELECT 1 FROM USER_INFORMATION LIMIT 1");
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Complete setup process (database + tables + admin)
     */
    public function fullSetup($host, $username, $password, $databaseName) {
        // Create database first
        if (!$this->createDatabase($host, $username, $password, $databaseName)) {
            return false;
        }
        
        // Now connect to the specific database
        try {
            require_once '../../Models/Database.php';
            $this->db = new Database();
            
            if (!$this->db->isConnected()) {
                $this->error = "Failed to connect to database: " . $this->db->getError();
                return false;
            }
        } catch (Exception $e) {
            $this->error = "Database connection failed: " . $e->getMessage();
            return false;
        }
        
        // Create tables
        if (!$this->createTables()) {
            return false;
        }
        
        // Create admin
        if (!$this->createDefaultAdmin()) {
            // This is not a critical error - admin might already exist
            error_log("Admin creation note: " . $this->getError());
        }
        
        return true;
    }

    /**
     * Static method for manual setup (replaces old Tables.php)
     */
    public static function manualSetup() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Load environment
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $dotenvPath = dirname(__DIR__) . '/.env';
        if (!file_exists($dotenvPath)) {
            die("Error: .env file not found at: " . $dotenvPath);
        }
        
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
        
        // Get credentials
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $database = $_ENV['DB_NAME'];
        
        // Create schema manager
        $schema = new DatabaseSchema();
        
        // Create database
        if (!$schema->createDatabase($host, $username, $password, $database)) {
            die("Database creation failed: " . $schema->getError());
        }
        
        echo "Database created successfully.<br>";
        
        // Connect to specific database
        require_once __DIR__ . '/Database.php';
        require_once __DIR__ . '/config.php';
        
        try {
            $db = new Database();
            $schema = new DatabaseSchema($db);
            
            // Create tables
            if (!$schema->createTables()) {
                die("Table creation failed: " . $schema->getError());
            }
            
            echo "All tables created successfully.<br>";
            
            // Create admin
            if ($schema->createDefaultAdmin()) {
                echo "Default admin account created.<br>";
                echo "Email: admin@thesis.system<br>Password: admin123<br>";
                echo "<strong>Please change this password immediately after login!</strong><br>";
            }
            
            echo "<h3>Setup completed successfully!</h3>";
            
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
?>