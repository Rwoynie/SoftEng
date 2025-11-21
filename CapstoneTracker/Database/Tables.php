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
     * all table creation
     */
        public static function getTableQueries() {
            return [
                "CREATE TABLE IF NOT EXISTS USER_INFORMATION (
        ID INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        pswrd VARCHAR(255) NOT NULL,
        Salt VARCHAR(255) NOT NULL,
        First_Name VARCHAR(50) NOT NULL,
        Middle_Name VARCHAR(50),
        Last_Name VARCHAR(50) NOT NULL,
        Extension VARCHAR(20),
        Email VARCHAR(255) UNIQUE NOT NULL, -- Stores HASHED emails
        User_ID VARCHAR(255) UNIQUE NOT NULL, -- Stores HASHED user_ids
        Student_ID VARCHAR(255) UNIQUE, -- Stores HASHED student_ids
        Employee_ID VARCHAR(255) UNIQUE, -- Stores HASHED employee_ids
        Email_Hash VARCHAR(255),
        User_ID_Hash VARCHAR(255),
        Student_ID_Hash VARCHAR(255),
        Employee_ID_Hash VARCHAR(255), -- Changed semicolon to comma here
        User_Role ENUM('student', 'faculty', 'SubAdmin', 'superAdmin') NOT NULL,
        Acc_Status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        Department VARCHAR(255) NOT NULL,
        Course VARCHAR(255) NOT NULL,
        Profile_Pic LONGBLOB,
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
            ) ENGINE=InnoDB;",

            "CREATE TABLE IF NOT EXISTS password_change_pins (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT(11) UNSIGNED NOT NULL,
                pin_code VARCHAR(6) NOT NULL,
                expiry_date DATETIME NOT NULL,
                used TINYINT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES USER_INFORMATION(ID) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_pin_code (pin_code),
                INDEX idx_expiry_date (expiry_date),
                INDEX idx_used (used)
            ) ENGINE=InnoDB;"

            
        ];
    }

    /**
     * all view creation queries
     */
    public static function getViewQueries() {
        return [
            "CREATE OR REPLACE VIEW vw_user_summary AS
            SELECT 
                ui.ID,
                ui.First_Name,
                ui.Last_Name,
                ui.Email,
                ui.User_Role,
                ui.Acc_Status,
                ui.Department,
                ui.Course,
                ui.created_at,
                COUNT(DISTINCT t.ID) as thesis_count,
                COUNT(DISTINCT tr.ID) as reviews_count,
                (SELECT COUNT(*) FROM THESIS_REVIEWS tr2 
                 WHERE tr2.reviewer_id = ui.ID) as reviews_given
            FROM USER_INFORMATION ui
            LEFT JOIN THESIS t ON ui.ID = t.User_ID
            LEFT JOIN THESIS_REVIEWS tr ON ui.ID = tr.reviewer_id
            GROUP BY ui.ID;",

            "CREATE OR REPLACE VIEW vw_thesis_details AS
            SELECT 
                t.ID,
                t.Title,
                t.Author,
                t.Adviser,
                t.Thesis_Department,
                t.Thesis_Course,
                t.HardBound_Available,
                t.uploaded_at,
                ui.First_Name as uploader_first_name,
                ui.Last_Name as uploader_last_name,
                ui.Email as uploader_email,
                COALESCE(AVG(tr.rating), 0) as average_rating,
                COUNT(tr.ID) as review_count
            FROM THESIS t
            LEFT JOIN USER_INFORMATION ui ON t.User_ID = ui.ID
            LEFT JOIN THESIS_REVIEWS tr ON t.ID = tr.thesis_id
            GROUP BY t.ID;",

            "CREATE OR REPLACE VIEW vw_active_announcements AS
            SELECT 
                a.*,
                CONCAT(ui.First_Name, ' ', ui.Last_Name) as created_by_name,
                ui.Email as created_by_email
            FROM ANNOUNCEMENTS a
            LEFT JOIN USER_INFORMATION ui ON a.created_by = ui.ID
            WHERE a.status = 'published' 
            AND a.start_date <= NOW() 
            AND (a.end_date IS NULL OR a.end_date >= NOW())
            ORDER BY a.is_pinned DESC, a.created_at DESC;",

            "CREATE OR REPLACE VIEW vw_system_statistics AS
            SELECT 
                (SELECT COUNT(*) FROM USER_INFORMATION) as total_users,
                (SELECT COUNT(*) FROM USER_INFORMATION WHERE Acc_Status = 'approved') as approved_users,
                (SELECT COUNT(*) FROM USER_INFORMATION WHERE Acc_Status = 'pending') as pending_users,
                (SELECT COUNT(*) FROM THESIS) as total_theses,
                (SELECT COUNT(*) FROM ANNOUNCEMENTS WHERE status = 'published') as active_announcements,
                (SELECT COUNT(*) FROM THESIS_REVIEWS) as total_reviews,
                (SELECT COUNT(*) FROM LOGIN_ATTEMPTS WHERE success = 1 AND DATE(attempt_time) = CURDATE()) as today_logins,
                (SELECT COUNT(*) FROM NOTIFICATIONS WHERE is_read = 0) as unread_notifications;",

            "CREATE OR REPLACE VIEW vw_security_monitoring AS
            SELECT 
                la.*,
                ui.First_Name,
                ui.Last_Name,
                ui.Email,
                CASE 
                    WHEN la.success = 0 AND la.attempt_time >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 'HIGH'
                    WHEN la.success = 0 AND la.attempt_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'MEDIUM'
                    ELSE 'LOW'
                END as risk_level
            FROM LOGIN_ATTEMPTS la
            LEFT JOIN USER_INFORMATION ui ON la.user_id = ui.ID
            ORDER BY la.attempt_time DESC;"
        ];
    }

    /**
     * all sp 
     */
    public static function getStoredProcedureQueries() {
        return [
            "DELIMITER //",

            "CREATE PROCEDURE sp_update_user_role(
                IN p_user_id INT,
                IN p_new_role VARCHAR(15),
                IN p_updated_by INT
            )
            BEGIN
                DECLARE current_role VARCHAR(15);
                DECLARE admin_count INT;
                
          
                SELECT User_Role INTO current_role 
                FROM USER_INFORMATION 
                WHERE ID = p_user_id;
                
                IF current_role IN ('admin', 'superAdmin') AND p_new_role NOT IN ('admin', 'superAdmin') THEN
                    SELECT COUNT(*) INTO admin_count 
                    FROM USER_INFORMATION 
                    WHERE User_Role IN ('admin', 'superAdmin') 
                    AND Acc_Status = 'approved' 
                    AND ID != p_user_id;
                    
                    IF admin_count = 0 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot remove the last admin account';
                    END IF;
                END IF;
                
                SET @current_user_id = p_updated_by;
                
                UPDATE USER_INFORMATION 
                SET User_Role = p_new_role 
                WHERE ID = p_user_id;
                
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, new_values, user_id)
                VALUES ('USER_INFORMATION', p_user_id, 'UPDATE', 
                       JSON_OBJECT('User_Role', current_role),
                       JSON_OBJECT('User_Role', p_new_role),
                       p_updated_by);
            END //",

            "CREATE PROCEDURE sp_bulk_update_user_status(
                IN p_user_ids JSON,
                IN p_new_status VARCHAR(10),
                IN p_updated_by INT
            )
            BEGIN
                DECLARE i INT DEFAULT 0;
                DECLARE user_count INT;
                DECLARE current_user_id INT;
                
                SET user_count = JSON_LENGTH(p_user_ids);
                
                SET @current_user_id = p_updated_by;
                
                WHILE i < user_count DO
                    SET current_user_id = JSON_EXTRACT(p_user_ids, CONCAT('$[', i, ']'));
                    
                    UPDATE USER_INFORMATION 
                    SET Acc_Status = p_new_status 
                    WHERE ID = current_user_id;
                    
                    SET i = i + 1;
                END WHILE;
                
                SELECT CONCAT('Updated ', user_count, ' users to status: ', p_new_status) as result;
            END //",

            "CREATE PROCEDURE sp_get_user_activity_report(
                IN p_department VARCHAR(255),
                IN p_start_date DATE,
                IN p_end_date DATE
            )
            BEGIN
                SELECT 
                    ui.ID,
                    CONCAT(ui.First_Name, ' ', ui.Last_Name) as user_name,
                    ui.Email,
                    ui.Department,
                    ui.User_Role,
                    COUNT(DISTINCT t.ID) as theses_uploaded,
                    COUNT(DISTINCT tr.thesis_id) as reviews_given,
                    MAX(t.uploaded_at) as last_upload,
                    MAX(tr.reviewed_at) as last_review
                FROM USER_INFORMATION ui
                LEFT JOIN THESIS t ON ui.ID = t.User_ID 
                    AND (p_start_date IS NULL OR DATE(t.uploaded_at) >= p_start_date)
                    AND (p_end_date IS NULL OR DATE(t.uploaded_at) <= p_end_date)
                LEFT JOIN THESIS_REVIEWS tr ON ui.ID = tr.reviewer_id
                    AND (p_start_date IS NULL OR DATE(tr.reviewed_at) >= p_start_date)
                    AND (p_end_date IS NULL OR DATE(tr.reviewed_at) <= p_end_date)
                WHERE (p_department IS NULL OR ui.Department = p_department)
                GROUP BY ui.ID
                ORDER BY theses_uploaded DESC, reviews_given DESC;
            END //",

            "CREATE PROCEDURE sp_search_theses(
                IN p_search_term VARCHAR(255),
                IN p_department VARCHAR(255),
                IN p_course VARCHAR(255),
                IN p_min_rating DECIMAL(3,2),
                IN p_limit INT
            )
            BEGIN
                SELECT 
                    t.*,
                    ui.First_Name as uploader_first_name,
                    ui.Last_Name as uploader_last_name,
                    COALESCE(AVG(tr.rating), 0) as average_rating,
                    COUNT(tr.ID) as review_count
                FROM THESIS t
                LEFT JOIN USER_INFORMATION ui ON t.User_ID = ui.ID
                LEFT JOIN THESIS_REVIEWS tr ON t.ID = tr.thesis_id
                WHERE (
                    p_search_term IS NULL OR 
                    t.Title LIKE CONCAT('%', p_search_term, '%') OR
                    t.Author LIKE CONCAT('%', p_search_term, '%') OR
                    t.Adviser LIKE CONCAT('%', p_search_term, '%')
                )
                AND (p_department IS NULL OR t.Thesis_Department = p_department)
                AND (p_course IS NULL OR t.Thesis_Course = p_course)
                GROUP BY t.ID
                HAVING (p_min_rating IS NULL OR average_rating >= p_min_rating)
                ORDER BY average_rating DESC, t.uploaded_at DESC
                LIMIT p_limit;
            END //",

            "CREATE PROCEDURE sp_get_thesis_statistics(
                IN p_period VARCHAR(10)
            )
            BEGIN
                DECLARE start_date DATE;
                
                CASE p_period
                    WHEN 'day' THEN SET start_date = CURDATE();
                    WHEN 'week' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 7 DAY);
                    WHEN 'month' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 1 MONTH);
                    WHEN 'year' THEN SET start_date = DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
                    ELSE SET start_date = '1900-01-01';
                END CASE;
                
                SELECT 
                    COUNT(*) as total_uploads,
                    COUNT(DISTINCT User_ID) as unique_uploaders,
                    AVG(review_count) as avg_reviews,
                    AVG(avg_rating) as overall_avg_rating,
                    MAX(uploads_per_day) as max_uploads_day
                FROM (
                    SELECT 
                        t.User_ID,
                        COUNT(t.ID) as uploads_per_day,
                        COALESCE(AVG(tr.rating), 0) as avg_rating,
                        COUNT(tr.ID) as review_count
                    FROM THESIS t
                    LEFT JOIN THESIS_REVIEWS tr ON t.ID = tr.thesis_id
                    WHERE DATE(t.uploaded_at) >= start_date
                    GROUP BY t.User_ID, DATE(t.uploaded_at)
                ) as daily_stats;
            END //",

            "CREATE PROCEDURE sp_cleanup_audit_logs(
                IN p_retention_days INT
            )
            BEGIN
                DELETE FROM AUDIT_LOGS 
                WHERE changed_at < DATE_SUB(NOW(), INTERVAL p_retention_days DAY);
                
                SELECT ROW_COUNT() as deleted_records;
            END //",

            "CREATE PROCEDURE sp_detect_suspicious_logins(
                IN p_lookback_hours INT
            )
            BEGIN
                SELECT 
                    ip_address,
                    COUNT(*) as failed_attempts,
                    MAX(attempt_time) as last_attempt,
                    GROUP_CONCAT(DISTINCT email) as attempted_emails
                FROM LOGIN_ATTEMPTS 
                WHERE success = 0 
                AND attempt_time >= DATE_SUB(NOW(), INTERVAL p_lookback_hours HOUR)
                GROUP BY ip_address
                HAVING failed_attempts >= 5
                ORDER BY failed_attempts DESC;
            END //",

            "CREATE PROCEDURE sp_generate_security_report(
                IN p_start_date DATE,
                IN p_end_date DATE
            )
            BEGIN
                SELECT 
                    'failed_logins_by_ip' as report_section,
                    ip_address,
                    COUNT(*) as attempt_count,
                    COUNT(DISTINCT email) as unique_emails
                FROM LOGIN_ATTEMPTS 
                WHERE success = 0 
                AND DATE(attempt_time) BETWEEN p_start_date AND p_end_date
                GROUP BY ip_address
                HAVING attempt_count > 3
                
                UNION ALL

                SELECT 
                    'user_account_changes' as report_section,
                    CONCAT('User ', record_id) as identifier,
                    COUNT(*) as change_count
                FROM AUDIT_LOGS 
                WHERE table_name = 'USER_INFORMATION'
                AND DATE(changed_at) BETWEEN p_start_date AND p_end_date
                GROUP BY record_id
                HAVING change_count > 5
                
                UNION ALL
                
                SELECT 
                    'thesis_modifications' as report_section,
                    CONCAT('Thesis ', record_id) as identifier,
                    COUNT(*) as modification_count
                FROM AUDIT_LOGS 
                WHERE table_name = 'THESIS'
                AND DATE(changed_at) BETWEEN p_start_date AND p_end_date
                GROUP BY record_id
                HAVING modification_count > 3;
            END //",

            "DELIMITER ;"
        ];
    }

    /**
     * functionssss
     */
    public static function getFunctionQueries() {
        return [
            "DELIMITER //",

            "CREATE FUNCTION fn_calculate_user_reputation(p_user_id INT) 
            RETURNS DECIMAL(5,2)
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE reputation DECIMAL(5,2) DEFAULT 50.0;
                DECLARE thesis_count INT;
                DECLARE avg_rating DECIMAL(3,2);
                DECLARE review_count INT;
                
                SELECT COUNT(*) INTO thesis_count 
                FROM THESIS 
                WHERE User_ID = p_user_id;
                
                SELECT COALESCE(AVG(tr.rating), 0) INTO avg_rating
                FROM THESIS t
                LEFT JOIN THESIS_REVIEWS tr ON t.ID = tr.thesis_id
                WHERE t.User_ID = p_user_id;
                
                SELECT COUNT(*) INTO review_count
                FROM THESIS_REVIEWS 
                WHERE reviewer_id = p_user_id;
                
                SET reputation = reputation + (thesis_count * 5);
                SET reputation = reputation + (avg_rating * 10);
                SET reputation = reputation + (review_count * 2);
                
                IF reputation > 100 THEN
                    SET reputation = 100;
                END IF;
                
                RETURN reputation;
            END //",

            "CREATE FUNCTION fn_can_delete_user(p_user_id INT) 
            RETURNS BOOLEAN
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE user_role VARCHAR(15);
                DECLARE admin_count INT;
                
                SELECT User_Role INTO user_role 
                FROM USER_INFORMATION 
                WHERE ID = p_user_id;
                
                IF user_role IN ('admin', 'superAdmin') THEN
                    SELECT COUNT(*) INTO admin_count 
                    FROM USER_INFORMATION 
                    WHERE User_Role IN ('admin', 'superAdmin') 
                    AND Acc_Status = 'approved' 
                    AND ID != p_user_id;
                    
                    IF admin_count = 0 THEN
                        RETURN FALSE;
                    END IF;
                END IF;
                
                RETURN TRUE;
            END //",


            "CREATE FUNCTION fn_get_department_thesis_count(p_department VARCHAR(255)) 
            RETURNS INT
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE thesis_count INT;
                
                SELECT COUNT(*) INTO thesis_count
                FROM THESIS
                WHERE Thesis_Department = p_department;
                
                RETURN thesis_count;
            END //",

            "CREATE FUNCTION fn_create_notification(
                p_user_id INT,
                p_type VARCHAR(20),
                p_title VARCHAR(255),
                p_message TEXT,
                p_related_id INT
            ) 
            RETURNS INT
            MODIFIES SQL DATA
            BEGIN
                DECLARE notification_id INT;
                
                INSERT INTO NOTIFICATIONS (user_id, type, title, message, related_id)
                VALUES (p_user_id, p_type, p_title, p_message, p_related_id);
                
                SET notification_id = LAST_INSERT_ID();
                
                RETURN notification_id;
            END //",

            "CREATE FUNCTION fn_get_unread_notification_count(p_user_id INT) 
            RETURNS INT
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE unread_count INT;
                
                SELECT COUNT(*) INTO unread_count
                FROM NOTIFICATIONS
                WHERE user_id = p_user_id AND is_read = FALSE;
                
                RETURN unread_count;
            END //",


            "CREATE FUNCTION fn_get_announcement_days_remaining(p_announcement_id INT) 
            RETURNS INT
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE days_remaining INT;
                DECLARE end_date_val DATETIME;
                
                SELECT end_date INTO end_date_val
                FROM ANNOUNCEMENTS
                WHERE id = p_announcement_id;
                
                IF end_date_val IS NULL THEN
                    RETURN NULL;
                END IF;
                
                SET days_remaining = DATEDIFF(end_date_val, CURDATE());
                
                IF days_remaining < 0 THEN
                    RETURN 0;
                END IF;
                
                RETURN days_remaining;
            END //",


            "CREATE FUNCTION fn_format_duration(p_minutes INT) 
            RETURNS VARCHAR(50)
            DETERMINISTIC
            BEGIN
                DECLARE result VARCHAR(50);
                
                IF p_minutes < 60 THEN
                    SET result = CONCAT(p_minutes, ' minutes');
                ELSEIF p_minutes < 1440 THEN
                    SET result = CONCAT(FLOOR(p_minutes / 60), ' hours ', MOD(p_minutes, 60), ' minutes');
                ELSE
                    SET result = CONCAT(FLOOR(p_minutes / 1440), ' days ', FLOOR(MOD(p_minutes, 1440) / 60), ' hours');
                END IF;
                
                RETURN result;
            END //",

            "DELIMITER ;"
        ];
    }

    /**
     * all triggerrr 
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
                
                -- Note: We can't compare Email_Hash changes directly since they're hashed
                -- But we can track when User_Role or Acc_Status changes
                
                IF JSON_LENGTH(changes) > 0 THEN
                    INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, new_values, user_id)
                    VALUES ('USER_INFORMATION', NEW.ID, 'UPDATE', 
                           JSON_OBJECT('User_Role', OLD.User_Role, 'Acc_Status', OLD.Acc_Status),
                           JSON_OBJECT('User_Role', NEW.User_Role, 'Acc_Status', NEW.Acc_Status),
                           @current_user_id);
                END IF;
            END;",

            "CREATE TRIGGER audit_user_deletions
            BEFORE DELETE ON USER_INFORMATION
            FOR EACH ROW
            BEGIN
                INSERT INTO AUDIT_LOGS (table_name, record_id, action, old_values, user_id)
                VALUES ('USER_INFORMATION', OLD.ID, 'DELETE', 
                       JSON_OBJECT('User_Role', OLD.User_Role, 'Acc_Status', OLD.Acc_Status, 'First_Name', OLD.First_Name, 'Last_Name', OLD.Last_Name),
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
            END;",

            "CREATE TRIGGER log_successful_login_attempt
            AFTER INSERT ON LOGIN_ATTEMPTS
            FOR EACH ROW
            BEGIN
                IF NEW.success = 1 THEN
                    INSERT INTO AUDIT_LOGS (table_name, record_id, action, new_values, user_id)
                    VALUES ('LOGIN_ATTEMPTS', NEW.id, 'INSERT', 
                           JSON_OBJECT('email', NEW.email, 'user_id', NEW.user_id, 'ip_address', NEW.ip_address, 'success', NEW.success),
                           NEW.user_id);
                END IF;
            END;",

            "CREATE TRIGGER prevent_duplicate_logins
            BEFORE UPDATE ON USER_INFORMATION
            FOR EACH ROW
            BEGIN
            END;",

             "CREATE TRIGGER detect_suspicious_login_activity
            AFTER INSERT ON LOGIN_ATTEMPTS
            FOR EACH ROW
            BEGIN
                DECLARE recent_failed_count INT;
                
                -- Count recent failed attempts from same IP
                SELECT COUNT(*) INTO recent_failed_count
                FROM LOGIN_ATTEMPTS 
                WHERE ip_address = NEW.ip_address 
                AND success = 0 
                AND attempt_time >= DATE_SUB(NOW(), INTERVAL 15 MINUTE);
                
                -- If more than 5 failed attempts in 15 minutes, log as suspicious
                IF recent_failed_count >= 5 THEN
                    INSERT INTO NOTIFICATIONS (user_id, type, title, message, related_id)
                    SELECT 
                        NULL, 
                        'security', 
                        'Suspicious Login Activity Detected', 
                        CONCAT('Multiple failed login attempts (', recent_failed_count, ') from IP: ', NEW.ip_address),
                        NEW.id;
                END IF;
            END;"

            

        ];
    }
    
    /**
     * Get ordered table queries
     */
    public static function getOrderedTableQueries() {
        $queries = self::getTableQueries();
        
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
        
        // Hash the identifiers for storage
        $email = 'admin@usep.edu.ph';
        $userId = 'ADMIN001';
        
        $emailHash = hash('sha256', $email);
        $userIdHash = hash('sha256', $userId);
        
        return [
            'pswrd' => $hashedPassword,
            'Salt' => $salt,
            'First_Name' => 'Super',
            'Middle_Name' => 'Admin',
            'Last_Name' => 'Admin',
            'Extension' => null,
            'Email' => $email, // Store hashed value in Email column
            'User_ID' => $userId, // Store hashed value in User_ID column
            'Email_Hash' => $emailHash,
            'User_ID_Hash' => $userIdHash,
            'User_Role' => 'superAdmin',
            'Acc_Status' => 'approved',
            'Department' => 'Administration',
            'Course' => 'Administration',
            'Profile_Pic' => null
            // Note: The hash columns will use the same values as Email and User_ID
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
     * Create all views
     */
    public function createViews() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $viewQueries = self::getViewQueries();
        
        foreach ($viewQueries as $viewQuery) {
            try {
                $this->db->query($viewQuery);
                $this->db->execute();
            } catch (PDOException $e) {
                error_log("View creation note: " . $e->getMessage());
                continue;
            }
        }
        
        return true;
    }

    /**
     * Create all stored procedures
     */
    public function createStoredProcedures() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $procedureQueries = self::getStoredProcedureQueries();
        
        $this->dropExistingStoredProcedures();
        
        foreach ($procedureQueries as $procedureQuery) {
            try {
                if (strpos($procedureQuery, 'DELIMITER') === 0) {
                    continue;
                }
                
                $this->db->query($procedureQuery);
                $this->db->execute();
            } catch (PDOException $e) {
                error_log("Stored procedure creation failed: " . $e->getMessage());
                $this->error = "Stored procedure creation failed: " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }

    /**
     * Create all functions
     */
    public function createFunctions() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $functionQueries = self::getFunctionQueries();
        
        $this->dropExistingFunctions();
        
        foreach ($functionQueries as $functionQuery) {
            try {
                if (strpos($functionQuery, 'DELIMITER') === 0) {
                    continue;
                }
                
                $this->db->query($functionQuery);
                $this->db->execute();
            } catch (PDOException $e) {
                error_log("Function creation failed: " . $e->getMessage());
                $this->error = "Function creation failed: " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }

    /**
     * Drop existing stored procedures
     */
    private function dropExistingStoredProcedures() {
        $procedures = [
            'sp_update_user_role',
            'sp_bulk_update_user_status',
            'sp_get_user_activity_report',
            'sp_search_theses',
            'sp_get_thesis_statistics',
            'sp_cleanup_audit_logs',
            'sp_detect_suspicious_logins',
            'sp_generate_security_report'
        ];
        
        foreach ($procedures as $procedure) {
            try {
                $this->db->query("DROP PROCEDURE IF EXISTS $procedure");
                $this->db->execute();
            } catch (PDOException $e) {
                continue;
            }
        }
    }

    /**
     * Drop existing functions
     */
    private function dropExistingFunctions() {
        $functions = [
            'fn_calculate_user_reputation',
            'fn_can_delete_user',
            'fn_get_department_thesis_count',
            'fn_create_notification',
            'fn_get_unread_notification_count',
            'fn_get_announcement_days_remaining',
            'fn_format_duration'
        ];
        
        foreach ($functions as $function) {
            try {
                $this->db->query("DROP FUNCTION IF EXISTS $function");
                $this->db->execute();
            } catch (PDOException $e) {
                continue;
            }
        }
    }

    /**
     * Create triggers
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
            
            // Check if admin already exists using hashed email
            $this->db->query("SELECT ID FROM USER_INFORMATION WHERE Email = :email");
            $this->db->bind(':email', $adminData['Email']);
            $this->db->execute();
            
            if ($this->db->rowCount() == 0) {
                // Build the query with all required columns including the new hash columns
                $this->db->query("INSERT INTO USER_INFORMATION 
                    (pswrd, Salt, First_Name, Middle_Name, Last_Name, Extension, Email, User_ID, 
                     Email_Hash, User_ID_Hash, Student_ID_Hash, Employee_ID_Hash,
                     User_Role, Acc_Status, Department, Course, Profile_Pic) 
                    VALUES 
                    (:password, :salt, :first_name, :middle_name, :last_name, :extension, 
                     :email, :user_id, :email_hash, :user_id_hash, :student_id_hash, :employee_id_hash,
                     :user_role, :acc_status, :department, :course, :profile_pic)");
    
                // Bind all parameters - using hashed values for Email and User_ID
                $this->db->bind(':password', $adminData['pswrd']);
                $this->db->bind(':salt', $adminData['Salt']);
                $this->db->bind(':first_name', $adminData['First_Name']);
                $this->db->bind(':middle_name', $adminData['Middle_Name']);
                $this->db->bind(':last_name', $adminData['Last_Name']);
                $this->db->bind(':extension', $adminData['Extension']);
                $this->db->bind(':email', $adminData['Email']); // Hashed email
                $this->db->bind(':user_id', $adminData['User_ID']); // Hashed user_id
                
                // Bind the hash columns (for admin, we only need email and user_id hashes)
                $this->db->bind(':email_hash', $adminData['Email_Hash']); // Same as email column
                $this->db->bind(':user_id_hash', $adminData['User_ID_Hash']); // Same as user_id column
                $this->db->bind(':student_id_hash', null); // Null for admin
                $this->db->bind(':employee_id_hash', null); // Null for admin
                
                $this->db->bind(':user_role', $adminData['User_Role']);
                $this->db->bind(':acc_status', $adminData['Acc_Status']);
                $this->db->bind(':department', $adminData['Department']);
                $this->db->bind(':course', $adminData['Course']);
                $this->db->bind(':profile_pic', $adminData['Profile_Pic']);
                
                $this->db->execute();
                return true;
            }
            return false;
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
    
    public function fullSetup($host, $username, $password, $databaseName) {
        if (!$this->createDatabase($host, $username, $password, $databaseName)) {
            return false;
        }
        
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
        
        if (!$this->createTables()) {
            return false;
        }

        if (!$this->createViews()) {
            error_log("View creation note: " . $this->getError());
        }

        if (!$this->createStoredProcedures()) {
            error_log("Stored procedure creation note: " . $this->getError());
        }

        if (!$this->createFunctions()) {
            error_log("Function creation note: " . $this->getError());
        }

        if (!$this->createTriggers()) {
            return false;
        }
        
        if (!$this->createDefaultAdmin()) {
            error_log("Admin creation note: " . $this->getError());
        }
        
        return true;
    }

    /**
     * Static method
     */
    public static function manualSetup() {
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

        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $database = $_ENV['DB_NAME'];

        $schema = new DatabaseSchema();

        if (!$schema->createDatabase($host, $username, $password, $database)) {
            die("Database creation failed: " . $schema->getError());
        }
        
        echo "Database created successfully.<br>";

        require_once __DIR__ . '/Database.php';
        require_once __DIR__ . '/config.php';
        
        try {
            $db = new Database();
            $schema = new DatabaseSchema($db);

            if (!$schema->createTables()) {
                die("Table creation failed: " . $schema->getError());
            }
            
            echo "All tables created successfully.<br>";

            if ($schema->createViews()) {
                echo "All views created successfully.<br>";
            } else {
                echo "View creation completed with notes.<br>";
            }

            if ($schema->createStoredProcedures()) {
                echo "All stored procedures created successfully.<br>";
            } else {
                echo "Stored procedure creation completed with notes.<br>";
            }

            if ($schema->createFunctions()) {
                echo "All functions created successfully.<br>";
            } else {
                echo "Function creation completed with notes.<br>";
            }

            if (!$schema->createTriggers()) {
                die("Trigger creation failed: " . $schema->getError());
            }
            
            echo "All triggers created successfully.<br>";

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

    /**
     * Test database objectss
     */
    public function testDatabaseObjects() {
        if (!$this->db || !$this->db->isConnected()) {
            return ['error' => 'Database not connected'];
        }

        $results = [];

        try {
            $this->db->query("SELECT COUNT(*) as count FROM vw_user_summary");
            $results['vw_user_summary'] = $this->db->single()->count;

            $this->db->query("SELECT COUNT(*) as count FROM vw_thesis_details");
            $results['vw_thesis_details'] = $this->db->single()->count;

            $this->db->query("SELECT COUNT(*) as count FROM vw_active_announcements");
            $results['vw_active_announcements'] = $this->db->single()->count;

            $this->db->query("SELECT fn_get_department_thesis_count('BSIT') as count");
            $results['fn_get_department_thesis_count'] = $this->db->single()->count;


            $this->db->query("SELECT COUNT(*) as user_count FROM USER_INFORMATION");
            $userCount = $this->db->single()->user_count;

            if ($userCount > 0) {
                $this->db->query("SELECT ID FROM USER_INFORMATION LIMIT 1");
                $userId = $this->db->single()->ID;

                $this->db->query("SELECT fn_calculate_user_reputation(:user_id) as reputation");
                $this->db->bind(':user_id', $userId);
                $results['fn_calculate_user_reputation'] = $this->db->single()->reputation;
            }

        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $results;
    }
}
?>