
<?php

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
                User_Role ENUM('student', 'faculty', 'SubAdmin', 'superAdmin') NOT NULL,
                Acc_Status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                Department VARCHAR(255) NOT NULL,
                Course VARCHAR(255) NOT NULL,   
                
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
                Adviser VARCHAR(255) NOT NULL,
                HardBound_Available ENUM('Yes', 'No') NOT NULL,
                Thesis_AbstractFile LONGBLOB NOT NULL,
                Thesis_File LONGBLOB NOT NULL,    
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (User_ID) REFERENCES USER_INFORMATION(ID) ON DELETE CASCADE,
                INDEX (User_ID),
                INDEX (Title)
            ) ENGINE=InnoDB;",

            "CREATE TABLE IF NOT EXISTS ROLES (
                Role_ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                User_ID INT(11) UNSIGNED NOT NULL,
                Sub_Admin ENUM('Yes', 'No') NOT NULL,
                Can_Edit ENUM('Yes', 'No') NOT NULL,
                Manage_Access ENUM('Yes', 'No') NOT NULL,
                Original_User_Role VARCHAR(15) NULL,
                FOREIGN KEY (User_ID) REFERENCES USER_INFORMATION(ID) ON DELETE CASCADE,
                INDEX (User_ID)
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
            ) ENGINE=InnoDB;",

        
            "CREATE TABLE IF NOT EXISTS ANNOUNCEMENTS (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(200) NOT NULL,
                content TEXT NOT NULL,
                type ENUM('deadline', 'event', 'important', 'maintenance', 'information') NOT NULL DEFAULT 'information',
                start_date DATETIME NOT NULL,
                end_date DATETIME NULL,
                is_pinned TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
                created_by INT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                INDEX idx_status (status),
                INDEX idx_type (type),
                INDEX idx_pinned (is_pinned),
                INDEX idx_dates (start_date, end_date)
            ) ENGINE=InnoDB;",

            // AUDIT LOGS TABLE
            "CREATE TABLE IF NOT EXISTS AUDIT_LOGS (
                id INT PRIMARY KEY AUTO_INCREMENT,
                table_name VARCHAR(50) NOT NULL,
                record_id INT NOT NULL,
                action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
                old_values JSON,
                new_values JSON,
                user_id INT,
                ip_address VARCHAR(45),
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_table_record (table_name, record_id),
                INDEX idx_action (action),
                INDEX idx_changed_at (changed_at),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB;",

            // LOGIN ATTEMPTS TABLE
            "CREATE TABLE IF NOT EXISTS LOGIN_ATTEMPTS (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                email VARCHAR(255),
                attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                success BOOLEAN,
                user_agent TEXT,
                INDEX idx_email (email),
                INDEX idx_ip (ip_address),
                INDEX idx_time (attempt_time),
                INDEX idx_success (success)
            ) ENGINE=InnoDB;",

            // NOTIFICATIONS TABLE
            "CREATE TABLE IF NOT EXISTS NOTIFICATIONS (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                type ENUM('user_approval', 'thesis_upload', 'announcement', 'system', 'security') NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                related_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_type (type),
                INDEX idx_read (is_read),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB;",

            // PASSWORD RESET TOKENS
            "CREATE TABLE IF NOT EXISTS PASSWORD_RESET_TOKENS (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT(11) UNSIGNED NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_used BOOLEAN DEFAULT FALSE,
                INDEX idx_user (user_id),
                INDEX idx_token (token),
                INDEX idx_expires (expires_at),
                INDEX idx_used (is_used),
                INDEX idx_created (created_at),
                FOREIGN KEY (user_id) REFERENCES USER_INFORMATION(ID) ON DELETE CASCADE
            ) ENGINE=InnoDB;"

            
        ];
    }

    /**
     * all trigger 
     */
    public static function getTriggerQueries() {
        return [
            "CREATE TRIGGER audit_user_changes
            AFTER UPDATE ON USER_INFORMATION
            FOR EACH ROW
            BEGIN
                DECLARE changes JSON DEFAULT JSON_OBJECT();
                
                IF OLD.User_Role != NEW.User_Role THEN
                    SET changes = JSON_SET(changes, '$.role_changed', JSON_OBJECT('old', OLD.User_Role, 'new', NEW.User_Role));
                END IF;
                
                IF OLD.Acc_Status != NEW.Acc_Status THEN
                    SET changes = JSON_SET(changes, '$.status_changed', JSON_OBJECT('old', OLD.Acc_Status, 'new', NEW.Acc_Status));
                END IF;
                
                IF OLD.Email != NEW.Email THEN
                    SET changes = JSON_SET(changes, '$.email_changed', JSON_OBJECT('old', OLD.Email, 'new', NEW.Email));
                END IF;
                
                IF JSON_LENGTH(changes) > 0 THEN
                    INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, new_values, user_id)
                    VALUES ('USER_INFORMATION', NEW.ID, 'UPDATE', 
                           JSON_OBJECT('User_Role', OLD.User_Role, 'Acc_Status', OLD.Acc_Status, 'Email', OLD.Email),
                           JSON_OBJECT('User_Role', NEW.User_Role, 'Acc_Status', NEW.Acc_Status, 'Email', NEW.Email),
                           @current_user_id);
                END IF;
            END;",

            "CREATE TRIGGER audit_user_deletions
            BEFORE DELETE ON USER_INFORMATION
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, user_id)
                VALUES ('USER_INFORMATION', OLD.ID, 'DELETE', 
                       JSON_OBJECT('User_Role', OLD.User_Role, 'Acc_Status', OLD.Acc_Status, 'Email', OLD.Email, 'First_Name', OLD.First_Name, 'Last_Name', OLD.Last_Name),
                       @current_user_id);
            END;",

           
            "CREATE TRIGGER audit_thesis_uploads
            AFTER INSERT ON THESIS
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, new_values, user_id)
                VALUES ('THESIS', NEW.ID, 'INSERT', 
                    JSON_OBJECT('Title', NEW.Title, 'Author', NEW.Author, 'Thesis_Course', NEW.Thesis_Course),
                    NEW.User_ID);
                
                INSERT INTO NOTIFICATIONS (user_id, type, title, message, related_id)
                SELECT ID, 'thesis_upload', 'New Thesis Uploaded', 
                    CONCAT('A new thesis \"', NEW.Title, '\" has been uploaded by ', NEW.Author),
                    NEW.ID
                FROM USER_INFORMATION 
                WHERE User_Role IN ('admin', 'superAdmin') AND Acc_Status = 'approved';
            END;",

            "CREATE TRIGGER audit_thesis_updates
            AFTER UPDATE ON THESIS
            FOR EACH ROW
            BEGIN
                IF OLD.Title != NEW.Title OR OLD.Author != NEW.Author THEN
                    INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, new_values, user_id)
                    VALUES ('THESIS', NEW.ID, 'UPDATE', 
                           JSON_OBJECT('Title', OLD.Title, 'Author', OLD.Author),
                           JSON_OBJECT('Title', NEW.Title, 'Author', NEW.Author),
                           @current_user_id);
                END IF;
            END;",

            "CREATE TRIGGER audit_thesis_deletions
            BEFORE DELETE ON THESIS
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, user_id)
                VALUES ('THESIS', OLD.ID, 'DELETE', 
                       JSON_OBJECT('Title', OLD.Title, 'Author', OLD.Author, 'User_ID', OLD.User_ID),
                       @current_user_id);
            END;",

            "CREATE TRIGGER audit_announcement_creation
            AFTER INSERT ON ANNOUNCEMENTS
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, new_values, user_id, ip_address)
                VALUES ('ANNOUNCEMENTS', NEW.id, 'INSERT', 
                    JSON_OBJECT('title', NEW.title, 'type', NEW.type, 'status', NEW.status, 'is_pinned', NEW.is_pinned),
                    NEW.created_by, @current_user_ip);
            END;",


            "CREATE TRIGGER audit_announcement_changes
            AFTER INSERT ON ANNOUNCEMENTS
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, new_values, user_id)
                VALUES ('ANNOUNCEMENTS', NEW.id, 'INSERT', 
                       JSON_OBJECT('title', NEW.title, 'type', NEW.type, 'status', NEW.status),
                       NEW.created_by);
            END;",

            "CREATE TRIGGER audit_announcement_updates
            AFTER UPDATE ON ANNOUNCEMENTS
            FOR EACH ROW
            BEGIN
                IF OLD.title != NEW.title OR OLD.status != NEW.status OR OLD.is_pinned != NEW.is_pinned OR OLD.type != NEW.type THEN
                    INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, new_values, user_id, ip_address)
                    VALUES ('ANNOUNCEMENTS', NEW.id, 'UPDATE', 
                        JSON_OBJECT('title', OLD.title, 'status', OLD.status, 'is_pinned', OLD.is_pinned, 'type', OLD.type),
                        JSON_OBJECT('title', NEW.title, 'status', NEW.status, 'is_pinned', NEW.is_pinned, 'type', NEW.type),
                        @current_user_id, @current_user_ip);
                END IF;
            END;",

            "CREATE TRIGGER audit_announcement_deletions
            BEFORE DELETE ON ANNOUNCEMENTS
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, user_id, ip_address)
                VALUES ('ANNOUNCEMENTS', OLD.id, 'DELETE', 
                    JSON_OBJECT('title', OLD.title, 'type', OLD.type, 'created_by', OLD.created_by),
                    @current_user_id, @current_user_ip);
            END;",

            // TRIGGER: Prevent last admin deletion
            "CREATE TRIGGER prevent_last_admin_deletion
            BEFORE DELETE ON USER_INFORMATION
            FOR EACH ROW
            BEGIN
                DECLARE admin_count INT;
                IF OLD.User_Role IN ('admin', 'superAdmin') AND OLD.Acc_Status = 'approved' THEN
                    SELECT COUNT(*) INTO admin_count 
                    FROM USER_INFORMATION 
                    WHERE User_Role IN ('admin', 'superAdmin') AND Acc_Status = 'approved' AND ID != OLD.ID;
                    
                    IF admin_count = 0 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete the last admin account';
                    END IF;
                END IF;
            END;",


            "CREATE TRIGGER auto_archive_announcements
            BEFORE UPDATE ON ANNOUNCEMENTS
            FOR EACH ROW
            BEGIN
                IF NEW.end_date IS NOT NULL AND NEW.end_date < NOW() AND NEW.status = 'published' THEN
                    SET NEW.status = 'archived';
                END IF;
            END;",

            "CREATE TRIGGER notify_user_approval
            AFTER UPDATE ON USER_INFORMATION
            FOR EACH ROW
            BEGIN
                IF OLD.Acc_Status = 'pending' AND NEW.Acc_Status = 'approved' THEN
                    INSERT INTO NOTIFICATIONS (user_id, type, title, message, related_id)
                    VALUES (NEW.ID, 'user_approval', 'Account Approved', 
                           'Your account has been approved. You can now access all features.', NEW.ID);
                END IF;
            END;"

        ];
    }
    
    /**
     * Get ordered table queries
     */
    public static function getOrderedTableQueries() {
        $queries = self::getTableQueries();
        
        // Simple ordering to ensure tables with foreign keys are created after referenced tables
        usort($queries, function($a, $b) {
            $priority = [
                'USER_INFORMATION' => 0,
                'THESIS' => 1,
                'THESIS_REVIEWS' => 2,
                'ANNOUNCEMENTS' => 3,
                'AUDIT_LOGS' => 4,
                'LOGIN_ATTEMPTS' => 5,
                'NOTIFICATIONS' => 6,
                'PASSWORD_RESET_TOKENS' => 7,
                'ROLES' => 8
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
            $tableName = self::extractTableName($query);
            try {
                error_log("Creating table: " . $tableName);
                $this->db->query($query);
                $this->db->execute();
                error_log("Successfully created table: " . $tableName);
            } catch (PDOException $e) {
                $errorMsg = "Table creation failed for '" . $tableName . "': " . $e->getMessage();
                error_log($errorMsg);
                $this->error = $errorMsg;
                return false;
            } catch (Exception $e) {
                $errorMsg = "Table creation failed for '" . $tableName . "': " . $e->getMessage();
                error_log($errorMsg);
                $this->error = $errorMsg;
                return false;
            }
        }
        
        error_log("All tables created successfully");
        return true;
    }

    /**
     *  triggerssss so muchh??? eme
     */
    public function createTriggers() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $this->dropExistingTriggers();
        
        $triggerQueries = self::getTriggerQueries();
        
        foreach ($triggerQueries as $triggerQuery) {
            try {
                $this->db->query($triggerQuery);
                $this->db->execute();
            } catch (PDOException $e) {
                $this->error = "Trigger creation failed: " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }

    /**
     * Drop triggers
     */
    private function dropExistingTriggers() {
        $triggers = [
            'audit_user_changes',
            'audit_user_deletions',
            'audit_thesis_uploads',
            'audit_thesis_updates',
            'audit_thesis_deletions',
            'audit_announcement_changes',
            'audit_announcement_updates',
            'audit_announcement_deletions',
            'prevent_last_admin_deletion',
            'auto_archive_announcements',
            'notify_user_approval'
        ];
        
        foreach ($triggers as $trigger) {
            try {
                $this->db->query("DROP TRIGGER IF EXISTS $trigger");
                $this->db->execute();
            } catch (PDOException $e) {
                // Continue even if trigger doesn't exist
                continue;
            }
        }
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
                    (pswrd, Salt, First_Name, Middle_Name, Last_Name, Extension, Email, User_ID, Student_ID, Employee_ID, User_Role, Acc_Status, Department, Course ,  Profile_Pic) 
                    VALUES 
                    (:password, :salt, :first_name, :middle_name, :last_name, :extension, :email, :user_id, :student_id, :employee_id, :user_role, :acc_status, :department, :course, :profile_pic)");
                
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
     * Complete setup process (database + tables + triggers + admin)
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

        // Create triggers
        if (!$this->createTriggers()) {
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

            // Create triggers
            if (!$schema->createTriggers()) {
                die("Trigger creation failed: " . $schema->getError());
            }
            
            echo "All triggers created successfully.<br>";
            
            // Create admin
            if ($schema->createDefaultAdmin()) {
                echo "Default admin account created.<br>";
                echo "Email: admin@usep.edu.ph<br>Password: compendiumSystemAdmin<br>";
                echo "<strong>Please change this password immediately after login!</strong><br>";
            }
            
            echo "<h3>Setup completed successfully!</h3>";
            
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
?>
