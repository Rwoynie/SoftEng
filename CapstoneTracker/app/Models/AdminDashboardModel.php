<?php
// AdminDashboardModel.php
require_once __DIR__ . '/Database.php';


class AdminDashboardModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get database connection for use in controller
     */
    public function getDatabase() {
        
        return $this->db;
    }

    /**
     * Get all users with their information
     */
    public function getAllUsers() {
        try {
            $this->db->query("
                SELECT 
                    ID,
                    First_Name,
                    Middle_Name,
                    Last_Name,
                    Extension,
                    Email,
                    User_ID,
                    Student_ID,
                    Employee_ID,
                    User_Role,
                    Acc_Status,
                    Department,
                    Course,
                    
                    created_at,
                    updated_at
                FROM USER_INFORMATION 
                ORDER BY created_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        try {
            $this->db->query("
                SELECT 
                    ID,
                    First_Name,
                    Middle_Name,
                    Last_Name,
                    Extension,
                    Email,
                    User_ID,
                    Student_ID,
                    Employee_ID,
                    User_Role,
                    Acc_Status,
                    Department,
                    Course,
                    
                    created_at,
                    updated_at
                FROM USER_INFORMATION 
                WHERE User_Role = :role
                ORDER BY created_at DESC
            ");
            $this->db->bind(':role', $role);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting users by role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by account status
     */
    public function getUsersByStatus($status) {
        try {
            $this->db->query("
                SELECT 
                    ID,
                    First_Name,
                    Middle_Name,
                    Last_Name,
                    Extension,
                    Email,
                    User_ID,
                    Student_ID,
                    Employee_ID,
                    User_Role,
                    Acc_Status,
                    Department,
                    Course,
                    
                    created_at,
                    updated_at
                FROM USER_INFORMATION 
                WHERE Acc_Status = :status
                ORDER BY created_at DESC
            ");
            $this->db->bind(':status', $status);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting users by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user count by role
     */
    public function getUserCountByRole() {
        try {
            $this->db->query("
                SELECT 
                    User_Role,
                    COUNT(*) as count
                FROM USER_INFORMATION 
                WHERE Acc_Status = 'approved'
                GROUP BY User_Role
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting user count by role: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total user count by status
     */
    public function getUserCountByStatus() {
        try {
            $this->db->query("
                SELECT 
                    Acc_Status,
                    COUNT(*) as count
                FROM USER_INFORMATION 
                GROUP BY Acc_Status
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting user count by status: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole($userId, $newRole) {
        try {
            $this->db->query("
                UPDATE USER_INFORMATION 
                SET User_Role = :role, updated_at = CURRENT_TIMESTAMP
                WHERE ID = :user_id
            ");
            $this->db->bind(':role', $newRole);
            $this->db->bind(':user_id', $userId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating user role: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user account status
     */
    public function updateUserStatus($userId, $newStatus) {
        try {
            $this->db->query("
                UPDATE USER_INFORMATION 
                SET Acc_Status = :status, updated_at = CURRENT_TIMESTAMP
                WHERE ID = :user_id
            ");
            $this->db->bind(':status', $newStatus);
            $this->db->bind(':user_id', $userId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating user status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user account
     */
    public function deleteUser($userId) {
        try {
            $this->db->query("DELETE FROM USER_INFORMATION WHERE ID = :user_id");
            $this->db->bind(':user_id', $userId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all theses with user information
     */
    public function getAllTheses() {
        try {
            $this->db->query("
                SELECT 
                    t.ID,
                    t.Title,
                    t.Author,
                    t.File_Path,
                    t.File_Size,
                    t.File_Type,
                    t.uploaded_at,
                    t.updated_at,
                    u.First_Name,
                    u.Middle_Name,
                    u.Last_Name,
                    u.Department,
                    u.Course
                FROM THESIS t
                JOIN USER_INFORMATION u ON t.User_ID = u.ID
                ORDER BY t.uploaded_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting all theses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get theses by department
     */
    public function getThesesByDepartment($department) {
        try {
            $this->db->query("
                SELECT 
                    t.ID,
                    t.Title,
                    t.Author,
                    t.File_Path,
                    t.File_Size,
                    t.File_Type,
                    t.uploaded_at,
                    t.updated_at,
                    u.First_Name,
                    u.Middle_Name,
                    u.Last_Name,
                    u.Department,
                    u.Course
                FROM THESIS t
                JOIN USER_INFORMATION u ON t.User_ID = u.ID
                WHERE u.Department = :department
                ORDER BY t.uploaded_at DESC
            ");
            $this->db->bind(':department', $department);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting theses by department: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent theses (last 30 days)
     */
    public function getRecentTheses($limit = 10) {
        try {
            $this->db->query("
                SELECT 
                    t.ID,
                    t.Title,
                    t.Author,
                    t.File_Path,
                    t.File_Size,
                    t.File_Type,
                    t.uploaded_at,
                    t.updated_at,
                    u.First_Name,
                    u.Middle_Name,
                    u.Last_Name,
                    u.Department,
                    u.Course
                FROM THESIS t
                JOIN USER_INFORMATION u ON t.User_ID = u.ID
                WHERE t.uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY t.uploaded_at DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting recent theses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search theses by title or author
     */
    public function searchTheses($searchTerm) {
        try {
            $this->db->query("
                SELECT 
                    t.ID,
                    t.Title,
                    t.Author,
                    t.File_Path,
                    t.File_Size,
                    t.File_Type,
                    t.uploaded_at,
                    t.updated_at,
                    u.First_Name,
                    u.Middle_Name,
                    u.Last_Name,
                    u.Department,
                    u.Course
                FROM THESIS t
                JOIN USER_INFORMATION u ON t.User_ID = u.ID
                WHERE t.Title LIKE :search OR t.Author LIKE :search
                ORDER BY t.uploaded_at DESC
            ");
            $this->db->bind(':search', '%' . $searchTerm . '%');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error searching theses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get thesis reviews with user information
     */
    public function getThesisReviews() {
        try {
            $this->db->query("
                SELECT 
                    tr.ID,
                    tr.rating,
                    tr.comments,
                    tr.reviewed_at,
                    t.Title as thesis_title,
                    t.Author as thesis_author,
                    ur.First_Name as reviewer_first_name,
                    ur.Last_Name as reviewer_last_name,
                    ur.User_Role as reviewer_role
                FROM THESIS_REVIEWS tr
                JOIN THESIS t ON tr.thesis_id = t.ID
                JOIN USER_INFORMATION ur ON tr.reviewer_id = ur.ID
                ORDER BY tr.reviewed_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting thesis reviews: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system statistics 
     */
    public function getSystemStatistics() {
        $stats = [];

        try {
            $this->db->query("SELECT COUNT(*) as total FROM USER_INFORMATION");
            $stats['total_users'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM USER_INFORMATION WHERE Acc_Status = 'approved'");
            $stats['approved_users'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM USER_INFORMATION WHERE Acc_Status = 'pending'");
            $stats['pending_users'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM THESIS");
            $stats['total_theses'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM THESIS_REVIEWS");
            $stats['total_reviews'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM THESIS WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['recent_theses'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM LOGIN_ATTEMPTS WHERE success = FALSE AND attempt_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['failed_logins_24h'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM AUDIT_LOGS WHERE changed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['audit_logs_7d'] = $this->db->single()->total;

            $this->db->query("SELECT COUNT(*) as total FROM NOTIFICATIONS WHERE is_read = FALSE");
            $stats['unread_notifications'] = $this->db->single()->total;

            return $stats;

        } catch (Exception $e) {
            error_log("Error getting system statistics: " . $e->getMessage());
            return $stats;
        }
    }

    /**
     * Get activity logs 
     */
    public function getRecentActivity($limit = 20) {
        try {
            
            $this->db->query("
                (SELECT 
                    'thesis_upload' as activity_type,
                    CONCAT('Uploaded thesis: ', Title) as description,
                    uploaded_at as activity_date,
                    u.First_Name,
                    u.Last_Name,
                    u.User_Role
                FROM THESIS t
                JOIN USER_INFORMATION u ON t.User_ID = u.ID
                ORDER BY uploaded_at DESC
                LIMIT :limit)
                
                UNION ALL
                
                (SELECT 
                    'user_registration' as activity_type,
                    CONCAT('Registered: ', First_Name, ' ', Last_Name) as description,
                    created_at as activity_date,
                    First_Name,
                    Last_Name,
                    User_Role
                FROM USER_INFORMATION
                ORDER BY created_at DESC
                LIMIT :limit)
                
                ORDER BY activity_date DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting recent activity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active announcements
     */
    public function getActiveAnnouncements() {
    try {
        $sql = "SELECT * FROM announcements 
                WHERE status = 'published' 
                AND (start_date IS NULL OR start_date <= NOW())
                AND (end_date IS NULL OR end_date = '0000-00-00 00:00:00' OR end_date >= NOW())
                ORDER BY is_pinned DESC, created_at DESC";
        
        $this->db->query($sql);
        return $this->db->resultSet();
        
    } catch (Exception $e) {
        error_log("Error fetching active announcements: " . $e->getMessage());
        return [];
    }
}

    /**
     * Get archived announcements
     */
    public function getArchivedAnnouncements() {
        try {
            $this->db->query("
                SELECT * FROM announcements 
                WHERE status = 'archived' 
                ORDER BY created_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting archived announcements: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get specific announcement by ID
     */
    public function getAnnouncementById($id) {
        try {
            $this->db->query("SELECT * FROM announcements WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error getting announcement: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new announcement
     */
    public function createAnnouncement($data) {
    try {
        $sql = "INSERT INTO announcements (title, content, type, start_date, end_date, is_pinned, status, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql);
        $this->db->bind(1, $data['title']);
        $this->db->bind(2, $data['content']);
        $this->db->bind(3, $data['type']);
        $this->db->bind(4, $data['start_date']);
        $this->db->bind(5, $data['end_date']);
        $this->db->bind(6, $data['is_pinned']);
        $this->db->bind(7, $data['status']);
        $this->db->bind(8, $data['created_by']);
        $this->db->bind(9, $data['created_at']);
        
        $result = $this->db->execute();
        
        if (!$result) {
            error_log("Database execute failed for announcement creation");
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));
            if (method_exists($this->db, 'getError')) {
                error_log("DB Error: " . $this->db->getError());
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Exception in createAnnouncement: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

    /**
 * Update announcement
 */
public function updateAnnouncement($id, $data) {
    try {
        error_log("Updating announcement ID: " . $id);
        error_log("Update data: " . print_r($data, true));
        
        $this->db->query("
            UPDATE announcements 
            SET title = :title, content = :content, type = :type, 
                start_date = :start_date, end_date = :end_date, is_pinned = :is_pinned, 
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':content', $data['content']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);
        $this->db->bind(':is_pinned', $data['is_pinned']);
        
        $result = $this->db->execute();
        error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating announcement: " . $e->getMessage());
        return false;
    }
}

    /**
     * Delete announcement
     */
    public function deleteAnnouncement($id) {
        try {
            $this->db->query("DELETE FROM announcements WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting announcement: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle announcement pin status
     */
    public function togglePinAnnouncement($id, $isPinned) {
        try {
            $this->db->query("UPDATE announcements SET is_pinned = :is_pinned WHERE id = :id");
            $this->db->bind(':is_pinned', $isPinned);
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error toggling announcement pin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update announcement status
     */
    public function updateAnnouncementStatus($id, $status) {
        try {
            $this->db->query("UPDATE announcements SET status = :status WHERE id = :id");
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating announcement status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt($userId, $email, $ipAddress, $success, $userAgent = null) {
        try {
            $this->db->query("
                INSERT INTO LOGIN_ATTEMPTS (user_id, email, ip_address, success, user_agent)
                VALUES (:user_id, :email, :ip_address, :success, :user_agent)
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':email', $email);
            $this->db->bind(':ip_address', $ipAddress);
            $this->db->bind(':success', $success);
            $this->db->bind(':user_agent', $userAgent);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error logging login attempt: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent login attempts
     */
    public function getRecentLoginAttempts($limit = 50) {
        try {
            error_log("Model: Getting login attempts - limit: $limit");
            
            $this->db->query("
                SELECT 
                    la.*,
                    ui.First_Name,
                    ui.Last_Name,
                    ui.User_Role
                FROM LOGIN_ATTEMPTS la
                LEFT JOIN USER_INFORMATION ui ON la.user_id = ui.ID
                ORDER BY la.attempt_time DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit);
            
            $result = $this->db->resultSet();
            error_log("Model: Found " . count($result) . " login attempts");
            
            return $result;
        } catch (Exception $e) {
            error_log("Error getting login attempts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audit logs
     */

public function getAuditLogs($limit, $table = null) {
    try {
        $sql = "SELECT 
                    a.*, 
                    u.First_Name, 
                    u.Last_Name, 
                    u.User_Role
                FROM AUDIT_LOGS a
                LEFT JOIN USER_INFORMATION u ON a.user_id = u.ID"; 
        
        $params = [];

        if (!empty($table)) {
            $sql .= " WHERE a.changed_table = :table";
            $params[':table'] = $table;
        }

        $sql .= " ORDER BY a.changed_at DESC LIMIT :limit";
        
        $this->db->query($sql);
        
        
        $this->db->bind(':limit', (int)$limit); 
        if (!empty($table)) {
            $this->db->bind(':table', $table);
        }
        
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting audit logs: " . $e->getMessage());
        return [];
    }
}

public function getLoginAttempts($limit) {
    try {
       
        $this->db->query("
            SELECT * FROM LOGIN_ATTEMPTS 
            ORDER BY attempt_time DESC 
            LIMIT :limit
        ");
        $this->db->bind(':limit', (int)$limit);
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting login attempts: " . $e->getMessage());
        return [];
    }
}



    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 20, $unreadOnly = false) {
        try {
            $sql = "
                SELECT *
                FROM NOTIFICATIONS
                WHERE user_id = :user_id
            ";
            
            if ($unreadOnly) {
                $sql .= " AND is_read = FALSE";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit";
            
            $this->db->query($sql);
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':limit', $limit);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId) {
        try {
            $this->db->query("UPDATE NOTIFICATIONS SET is_read = TRUE WHERE id = :id");
            $this->db->bind(':id', $notificationId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }

    

    /**
     * Get security alerts 
     */
    public function getSecurityAlerts($limit = 10) {
        try {
            $this->db->query("
                SELECT 
                    la.*,
                    ui.First_Name,
                    ui.Last_Name,
                    ui.User_Role
                FROM LOGIN_ATTEMPTS la
                LEFT JOIN USER_INFORMATION ui ON la.user_id = ui.ID
                WHERE la.success = FALSE
                ORDER BY la.attempt_time DESC
                LIMIT :limit
            ");
            $this->db->bind(':limit', $limit);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting security alerts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get department statistics
     */
    public function getDepartmentStatistics() {
        try {
            $this->db->query("
                SELECT 
                    u.Department,
                    COUNT(DISTINCT u.ID) as user_count,
                    COUNT(DISTINCT t.ID) as thesis_count,
                    AVG(tr.rating) as avg_rating
                FROM USER_INFORMATION u
                LEFT JOIN THESIS t ON u.ID = t.User_ID
                LEFT JOIN THESIS_REVIEWS tr ON t.ID = tr.thesis_id
                WHERE u.Acc_Status = 'approved'
                GROUP BY u.Department
                ORDER BY thesis_count DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting department statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user distribution by department
     */
    public function getUserDistributionByDepartment() {
        try {
            $this->db->query("
                SELECT 
                    Course,
                    User_Role,
                    COUNT(*) as user_count
                FROM USER_INFORMATION
                WHERE Acc_Status = 'approved'
                GROUP BY Course, User_Role
                ORDER BY Course, User_Role
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting user distribution: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get thesis statistics
     */
    public function getThesisStatistics($department = 'all') {
    try {
        $sql = "
            SELECT 
                COUNT(*) as total_theses,
                AVG(LENGTH(File_Size)) as avg_file_size,
                COUNT(DISTINCT Author) as unique_authors,
                MAX(uploaded_at) as latest_upload,
                MIN(uploaded_at) as earliest_upload
            FROM THESIS t
        ";
        
        if ($department !== 'all') {
            $sql .= " JOIN USER_INFORMATION u ON t.User_ID = u.ID WHERE u.Department = :department";
        }
        
        $this->db->query($sql);
        
        if ($department !== 'all') {
            $this->db->bind(':department', $department);
        }
        
        return $this->db->single();
    } catch (Exception $e) {
        error_log("Error getting thesis statistics: " . $e->getMessage());
        return null;
    }
}

    /**
     * Get thesis upload trends
     */
    public function getThesisUploadTrends($department = 'all') {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(uploaded_at, '%Y-%m') as month,
                    COUNT(*) as upload_count
                FROM THESIS t
            ";
            
            if ($department !== 'all') {
                $sql .= " JOIN USER_INFORMATION u ON t.User_ID = u.ID WHERE u.Course = :course";
            }
            
            $sql .= " GROUP BY DATE_FORMAT(uploaded_at, '%Y-%m') ORDER BY month DESC LIMIT 12";
            
            $this->db->query($sql);
            
            if ($department !== 'all') {
                $this->db->bind(':course', $department);
            }
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting thesis upload trends: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics($department = 'all') {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN Acc_Status = 'approved' THEN 1 ELSE 0 END) as approved_users,
                    SUM(CASE WHEN Acc_Status = 'pending' THEN 1 ELSE 0 END) as pending_users,
                    SUM(CASE WHEN User_Role = 'student' THEN 1 ELSE 0 END) as student_users,
                    SUM(CASE WHEN User_Role = 'faculty' THEN 1 ELSE 0 END) as faculty_users,
                    SUM(CASE WHEN User_Role = 'admin' THEN 1 ELSE 0 END) as admin_users
                FROM USER_INFORMATION
            ";
            
            if ($department !== 'all') {
                $sql .= " WHERE Course = :course";
            }
            
            $this->db->query($sql);
            
            if ($department !== 'all') {
                $this->db->bind(':course', $department);
            }
            
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error getting user statistics: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user registration trends
     */
    public function getUserRegistrationTrends($department = 'all') {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as registration_count
                FROM USER_INFORMATION
            ";
            
            if ($department !== 'all') {
                $sql .= " WHERE Course = :course";
            }
            
            $sql .= " GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 12";
            
            $this->db->query($sql);
            
            if ($department !== 'all') {
                $this->db->bind(':course', $department);
            }
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting user registration trends: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by department
     */
    public function getUsersByDepartment($department) {
    try {
        $this->db->query("
            SELECT 
                ID,
                First_Name,
                Middle_Name,
                Last_Name,
                Extension,
                Email,
                User_Role,
                Acc_Status,
                Department,
                Course,
                created_at
            FROM USER_INFORMATION 
            WHERE Department = :department
            ORDER BY created_at DESC
        ");
        $this->db->bind(':department', $department);
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting users by department: " . $e->getMessage());
        return [];
    }
}

    /**
     * Get all department reports
     */
    public function getAllDepartmentReports() {
        try {
            $departments = $this->getDepartmentStatistics();
            $reports = [];
            
            foreach ($departments as $dept) {
                $reports[$dept->Department] = [
                    'course' => $dept,
                    'theses' => $this->getThesesByDepartment($dept->Department),
                    'users' => $this->getUsersByDepartment($dept->Department)
                ];
            }
            
            return $reports;
        } catch (Exception $e) {
            error_log("Error getting all department reports: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get specific department report
     */
    public function getDepartmentReport($department) {
        try {
            return [
                'department' => $department,
                'stats' => $this->getDepartmentStatisticsForDept($department),
                'theses' => $this->getThesesByDepartment($department),
                'users' => $this->getUsersByDepartment($department),
                'upload_trends' => $this->getThesisUploadTrends($department),
                'user_trends' => $this->getUserRegistrationTrends($department)
            ];
        } catch (Exception $e) {
            error_log("Error getting department report: " . $e->getMessage());
            return null;
        }
    }


/**
 * Get thesis counts by program with course filtering
 */
public function getThesisCountsByProgram($department = 'all', $course = 'all') {
    try {
        $sql = "
            SELECT 
                Thesis_Course as program,
                COUNT(*) as thesis_count
            FROM THESIS 
            WHERE Thesis_Course IS NOT NULL 
              AND Thesis_Course != ''
        ";

        $params = [];
        
       
        if ($department !== 'all') {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                $sql .= " AND Thesis_Course IN ($placeholders)";
                $params = array_merge($params, $courseCodes);
            }
        }

       
        if ($course !== 'all' && !empty($course)) {
            $sql .= " AND Thesis_Course = ?";
            $params[] = $course;
        }

        $sql .= " GROUP BY Thesis_Course ORDER BY thesis_count DESC";

        $this->db->query($sql);
        foreach ($params as $i => $val) {
            $this->db->bind($i + 1, $val);
        }

        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error in getThesisCountsByProgram: " . $e->getMessage());
        return [];
    }
}


    /**
     * Get department statistics for specific department
     */
    public function getDepartmentStatisticsForDept($department) {
        try {
            $this->db->query("
                SELECT 
                    u.Department,
                    COUNT(DISTINCT u.ID) as user_count,
                    COUNT(DISTINCT t.ID) as thesis_count,
                    AVG(tr.rating) as avg_rating,
                    COUNT(DISTINCT CASE WHEN u.User_Role = 'student' THEN u.ID END) as student_count,
                    COUNT(DISTINCT CASE WHEN u.User_Role = 'faculty' THEN u.ID END) as faculty_count
                FROM USER_INFORMATION u
                LEFT JOIN THESIS t ON u.ID = t.User_ID
                LEFT JOIN THESIS_REVIEWS tr ON t.ID = tr.thesis_id
                WHERE u.Department = :department AND u.Acc_Status = 'approved'
                GROUP BY u.Department
            ");
            $this->db->bind(':department', $department);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error getting department statistics: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get course distribution for reports 
     */
    public function getCourseDistribution($department = 'all') {
    try {
        $sql = "
            SELECT 
                u.Course as course,
                COUNT(DISTINCT u.ID) as student_count,
                COUNT(DISTINCT t.ID) as thesis_count
            FROM USER_INFORMATION u
            LEFT JOIN THESIS t ON u.ID = t.User_ID
            WHERE u.Acc_Status = 'approved' 
            AND u.User_Role = 'student'
            AND u.Course IS NOT NULL
            AND u.Course != ''
        ";
        
        $params = [];
        
        if ($department !== 'all') {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                $sql .= " AND u.Course IN ($placeholders)";
                $params = array_merge($params, $courseCodes);
            }
        }
        
        $sql .= " GROUP BY u.Course ORDER BY student_count DESC";
        
        $this->db->query($sql);
        foreach ($params as $index => $value) {
            $this->db->bind($index + 1, $value);
        }
        
        return $this->db->resultSet();
        
    } catch (Exception $e) {
        error_log("Error getting course distribution: " . $e->getMessage());
        return [];
    }
}

    /**
     * Get monthly thesis uploads for reports
     */
    public function getMonthlyThesisUploads($department = 'all') {
    try {
        $sql = "
            SELECT 
                DATE_FORMAT(uploaded_at, '%b') as month,
                DATE_FORMAT(uploaded_at, '%M') as month_name,
                COUNT(*) as upload_count
            FROM THESIS t
            WHERE YEAR(uploaded_at) = YEAR(CURDATE())
        ";
        
        $params = [];
        
        if ($department !== 'all') {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                $sql .= " AND t.Thesis_Course IN ($placeholders)";
                $params = array_merge($params, $courseCodes);
            }
        }
        
        $sql .= " GROUP BY DATE_FORMAT(uploaded_at, '%Y-%m'), month, month_name
                  ORDER BY DATE_FORMAT(uploaded_at, '%Y-%m')";
        
        $this->db->query($sql);
        foreach ($params as $index => $value) {
            $this->db->bind($index + 1, $value);
        }
        
        $results = $this->db->resultSet();
        
       
        $months = [
            'Jan' => 'January', 'Feb' => 'February', 'Mar' => 'March', 
            'Apr' => 'April', 'May' => 'May', 'Jun' => 'June',
            'Jul' => 'July', 'Aug' => 'August', 'Sep' => 'September',
            'Oct' => 'October', 'Nov' => 'November', 'Dec' => 'December'
        ];
        
        $monthly = [];
        foreach ($months as $short => $full) {
            $found = false;
            foreach ($results as $row) {
                if ($row->month === $short) {
                    $monthly[] = [
                        'month' => $short,
                        'month_name' => $full,
                        'upload_count' => (int)$row->upload_count
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $monthly[] = [
                    'month' => $short,
                    'month_name' => $full,
                    'upload_count' => 0
                ];
            }
        }
        return $monthly;
        
    } catch (Exception $e) {
        error_log("Error getting monthly uploads: " . $e->getMessage());
        return $this->getEmptyMonthlyData();
    }
}


/**
 * Get empty monthly data structure
 */
private function getEmptyMonthlyData() {
    $months = [
        ['month' => 'Jan', 'month_name' => 'January', 'upload_count' => 0],
        ['month' => 'Feb', 'month_name' => 'February', 'upload_count' => 0],
        ['month' => 'Mar', 'month_name' => 'March', 'upload_count' => 0],
        ['month' => 'Apr', 'month_name' => 'April', 'upload_count' => 0],
        ['month' => 'May', 'month_name' => 'May', 'upload_count' => 0],
        ['month' => 'Jun', 'month_name' => 'June', 'upload_count' => 0],
        ['month' => 'Jul', 'month_name' => 'July', 'upload_count' => 0],
        ['month' => 'Aug', 'month_name' => 'August', 'upload_count' => 0],
        ['month' => 'Sep', 'month_name' => 'September', 'upload_count' => 0],
        ['month' => 'Oct', 'month_name' => 'October', 'upload_count' => 0],
        ['month' => 'Nov', 'month_name' => 'November', 'upload_count' => 0],
        ['month' => 'Dec', 'month_name' => 'December', 'upload_count' => 0]
    ];
    
    return $months;
}

/**
 * Get user distribution by role with course filtering
 */
public function getUserDistributionByRole($department = 'all', $course = 'all') {
    try {
        $sql = "
            SELECT 
                User_Role,
                COUNT(*) as user_count
            FROM USER_INFORMATION
            WHERE Acc_Status = 'approved'
            AND User_Role IS NOT NULL
        ";
        
        $params = [];
        
        
        if ($department !== 'all') {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                $sql .= " AND Course IN ($placeholders)";
                $params = array_merge($params, $courseCodes);
            }
        }

       
        if ($course !== 'all' && !empty($course)) {
            $sql .= " AND Course = ?";
            $params[] = $course;
        }
        
        $sql .= " GROUP BY User_Role ORDER BY user_count DESC";
        
        $this->db->query($sql);
        foreach ($params as $index => $value) {
            $this->db->bind($index + 1, $value);
        }
        
        return $this->db->resultSet();
        
    } catch (Exception $e) {
        error_log("Error getting user distribution by role: " . $e->getMessage());
        return [];
    }
}


/**
 * Get thesis per program with course filtering
 */
public function getThesisPerProgram($department = 'all', $course = 'all') {
    try {
        $sql = "
            SELECT 
                Thesis_Course as program,
                COUNT(*) as thesis_count
            FROM THESIS 
            WHERE Thesis_Course IS NOT NULL 
            AND Thesis_Course != ''
        ";
        
        $params = [];
        
        
        if ($department !== 'all') {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                $sql .= " AND Thesis_Course IN ($placeholders)";
                $params = array_merge($params, $courseCodes);
            }
        }

        if ($course !== 'all' && !empty($course)) {
            $sql .= " AND Thesis_Course = ?";
            $params[] = $course;
        }
        
        $sql .= " GROUP BY Thesis_Course ORDER BY thesis_count DESC";
        
        $this->db->query($sql);
        foreach ($params as $index => $value) {
            $this->db->bind($index + 1, $value);
        }
        
        return $this->db->resultSet();
        
    } catch (Exception $e) {
        error_log("Error getting thesis per program: " . $e->getMessage());
        return [];
    }
}


/**
 * Get program counts for department cards 
 */
public function getProgramThesisCounts() {
    try {
        $sql = "
            SELECT 
                Thesis_Course as program,
                COUNT(*) as thesis_count
            FROM THESIS 
            WHERE Thesis_Course IS NOT NULL 
            AND Thesis_Course != ''
            GROUP BY Thesis_Course
            ORDER BY thesis_count DESC
        ";
        
        $this->db->query($sql);
        return $this->db->resultSet();
        
    } catch (Exception $e) {
        error_log("Error getting program thesis counts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get reports statistics with course filtering
 */
public function getReportsStats($department = 'all', $course = 'all') {
    try {
    
        $userSql = "SELECT COUNT(*) as total FROM USER_INFORMATION WHERE Acc_Status = 'approved' AND User_Role = 'student' OR User_Role = 'faculty' OR User_Role = 'SubAdmin'";
        $thesisSql = "SELECT COUNT(*) as total FROM THESIS WHERE 1=1";
        
        $userParams = [];
        $thesisParams = [];

    
        if ($department !== 'all' && !empty($department)) {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                
               
                $userSql .= " AND Course IN ($placeholders)";
                $userParams = array_merge($userParams, $courseCodes);
                
                
                $thesisSql .= " AND Thesis_Course IN ($placeholders)";
                $thesisParams = array_merge($thesisParams, $courseCodes);
            }
        }

   
        if ($course !== 'all' && !empty($course)) {
            $userSql .= " AND Course = ?";
            $userParams[] = $course;
            
            $thesisSql .= " AND Thesis_Course = ?";
            $thesisParams[] = $course;
        }

     
        $this->db->query($userSql);
        foreach ($userParams as $index => $value) {
            $this->db->bind($index + 1, $value);
        }
        $totalApprovedUsers = (int)($this->db->single()->total ?? 0);

      
        $this->db->query($thesisSql);
        foreach ($thesisParams as $index => $value) {
            $this->db->bind($index + 1, $value);
        }
        $totalTheses = (int)($this->db->single()->total ?? 0);

        return [
            'total_theses' => $totalTheses,
            'total_students' => $totalApprovedUsers,
            'total_users' => $totalApprovedUsers
        ];

    } catch (Exception $e) {
        error_log("getReportsStats error: " . $e->getMessage());
        return [
            'total_theses' => 0,
            'total_students' => 0,
            'total_users' => 0
        ];
    }
}


    /**
     * Get course statistics for reports
     */
    public function getCourseStatisticsForReports($department = 'all') {
        try {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            $useCourses = !empty($courseCodes) && $department !== 'all';
            $placeholders = $useCourses ? str_repeat('?,', count($courseCodes) - 1) . '?' : '';
            
            $sql = "
                SELECT 
                    u.Course,
                    COUNT(DISTINCT u.ID) as total_users,
                    COUNT(DISTINCT CASE WHEN u.User_Role = 'student' THEN u.ID END) as student_count,
                    COUNT(DISTINCT CASE WHEN u.User_Role = 'faculty' THEN u.ID END) as faculty_count,
                    COUNT(DISTINCT CASE WHEN u.User_Role IN ('admin', 'superAdmin') THEN u.ID END) as admin_count,
                    COUNT(DISTINCT t.ID) as thesis_count
                FROM USER_INFORMATION u
                LEFT JOIN THESIS t ON u.ID = t.User_ID
                WHERE u.Acc_Status = 'approved'
                AND u.Course IS NOT NULL
                AND u.Course != ''
            ";
            
            if ($useCourses) {
                $sql .= " AND u.Course IN ($placeholders)";
            }
            
            $sql .= " GROUP BY u.Course ORDER BY thesis_count DESC";
            
            $this->db->query($sql);
            
            if ($useCourses) {
                foreach ($courseCodes as $index => $code) {
                    $this->db->bind($index + 1, $code);
                }
            }
            
            return $this->db->resultSet();
            
        } catch (Exception $e) {
            error_log("Error getting course stats for reports: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get department course mapping for reports
     */
    public function getDepartmentCourseMapping() {
        try {
            $this->db->query("
                SELECT DISTINCT 
                    Department,
                    Course
                FROM USER_INFORMATION 
                WHERE Department IS NOT NULL AND Course IS NOT NULL
                ORDER BY Department, Course
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting department course mapping: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get theses by course (for course-based filtering)
     */
    public function getThesesByCourse($course) {
        try {
            $this->db->query("
                SELECT 
                    t.ID,
                    t.Title,
                    t.Author,
                    t.File_Path,
                    t.File_Size,
                    t.File_Type,
                    t.uploaded_at,
                    t.updated_at,
                    u.First_Name,
                    u.Middle_Name,
                    u.Last_Name,
                    u.Department,
                    u.Course
                FROM THESIS t
                JOIN USER_INFORMATION u ON t.User_ID = u.ID
                WHERE u.Course = :course
                ORDER BY t.uploaded_at DESC
            ");
            $this->db->bind(':course', $course);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting theses by course: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get course-based user distribution for pie chart
     */
    public function getUserDistributionByCourse($department = 'all') {
    try {
        $sql = "
            SELECT 
                Course,
                COUNT(*) as user_count
            FROM USER_INFORMATION
            WHERE Acc_Status = 'approved'
            AND Course IS NOT NULL
            AND Course != ''
        ";
        
        if ($department !== 'all') {
            $sql .= " AND Department = :department";
        }
        
        $sql .= " GROUP BY Course ORDER BY user_count DESC";
        
        $this->db->query($sql);
        
        if ($department !== 'all') {
            $this->db->bind(':department', $department);
        }
        
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting user distribution by course: " . $e->getMessage());
        return [];
    }
}

    /**
 * Get course codes by department with proper mapping
 */
    private function getCourseCodesByDepartment($department) {
        $department = strtolower(trim($department));
        
        $map = [
            'all'    => [],
            'bsit'   => ['Bachelor of Science in Information Technology', 'BSIT'],
            'beced'  => ['Bachelor of Early Childhood Education', 'BECED'],
            'bsed'   => ['Bachelor of Secondary Education', 'BSED'],
            'btvted' => ['Bachelor of Technical-Vocational Teacher Education', 'BTVTED'],
            'beed'   => ['Bachelor of Elementary Education', 'BEED'],
            'bsned'  => ['Bachelor of Special Needs Education', 'BSNED'],
            'bsabe'  => [
                'Bachelor of Science in Agricultural and Biosystems Engineering',
                'Bachelor of Science in Agriculture and Biosystems Engineering',
                'BSABE'
            ],
        ];

        return $map[$department] ?? [];
    }


/**
 * Get all available courses from database
 */
public function getAllCourses() {
    try {
        $this->db->query("
            SELECT DISTINCT Course 
            FROM USER_INFORMATION 
            WHERE Course IS NOT NULL AND Course != ''
            ORDER BY Course
        ");
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting all courses: " . $e->getMessage());
        return [];
    }
}

/**
 * Get courses by department
 */
public function getCoursesByDepartment($department) {
    try {
        if ($department === 'all') {
            return $this->getAllCourses();
        }
        
        $courseCodes = $this->getCourseCodesByDepartment($department);
        if (empty($courseCodes)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
        $this->db->query("
            SELECT DISTINCT Course 
            FROM USER_INFORMATION 
            WHERE Course IN ($placeholders)
            ORDER BY Course
        ");
        
        foreach ($courseCodes as $index => $course) {
            $this->db->bind($index + 1, $course);
        }
        
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting courses by department: " . $e->getMessage());
        return [];
    }
}


    public function getDepartmentThesisCounts() {
        try {
            $counts = [];
            

            $this->db->query("SELECT COUNT(*) as count FROM thesis");
            $counts['all'] = $this->db->single()->count ?? 0;
            
        
            $departmentMapping = [
                'bsit' => ['Bachelor of Science in Information Technology', 'BSIT'],
                'beced' => ['Bachelor of Early Childhood Education', 'BECED'],
                'bsed' => ['Bachelor of Secondary Education', 'BSED'],
                'btvted' => ['Bachelor of Technical-Vocational Teacher Education', 'BTVTED'],
                'beed' => ['Bachelor of Elementary Education', 'BEED'],
                'bsned' => ['Bachelor of Special Needs Education', 'BSNED'],
                'bsabe' => [
                    'Bachelor of Science in Agricultural and Biosystems Engineering',
                    'Bachelor of Science in Agriculture and Biosystems Engineering',
                    'BSABE'
                ],
            ];
            
            foreach ($departmentMapping as $deptCode => $courseNames) {
                $placeholders = str_repeat('?,', count($courseNames) - 1) . '?';
                $sql = "SELECT COUNT(*) as count FROM thesis WHERE Thesis_Course IN ($placeholders)";
                
                $this->db->query($sql);
                foreach ($courseNames as $i => $courseName) {
                    $this->db->bind($i + 1, $courseName);
                }
                $result = $this->db->single();
                $counts[$deptCode] = $result->count ?? 0;
            }
            
            return $counts;
            
        } catch (Exception $e) {
            error_log("Error in getDepartmentThesisCounts: " . $e->getMessage());
            // Return default counts
            return array_fill_keys(['all', 'bsit', 'beced', 'bsed', 'btvted', 'beed', 'bsned', 'bsabe'], 0);
        }
    }


/**
 * Get login attempts with enhanced trigger data
 */
public function getLoginAttemptsWithTriggers($limit = 100) {
    try {
        $this->db->query("
            SELECT 
                la.*,
                ui.First_Name,
                ui.Last_Name,
                ui.User_Role,
                ui.Email,
                la.notes,
                la.attempt_time,
                la.ip_address,
                la.success,
                la.user_agent
            FROM LOGIN_ATTEMPTS la
            LEFT JOIN USER_INFORMATION ui ON la.user_id = ui.ID
            ORDER BY la.attempt_time DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting login attempts with triggers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get audit logs with trigger data
 */
public function getAuditLogsWithTriggers($limit = 100, $table = null) {
    try {
        $sql = "SELECT 
                    al.*,
                    ui.First_Name,
                    ui.Last_Name,
                    ui.User_Role,
                    ui.Email,
                    al.ip_address,
                    al.changed_at,
                    al.action,
                    al.table_name,
                    al.old_values,
                    al.new_values
                FROM AUDIT_LOGS al
                LEFT JOIN USER_INFORMATION ui ON al.user_id = ui.ID";
        
        $params = [];

        if (!empty($table)) {
            $sql .= " WHERE al.table_name = :table";
            $params[':table'] = $table;
        }

        $sql .= " ORDER BY al.changed_at DESC LIMIT :limit";
        
        $this->db->query($sql);
        $this->db->bind(':limit', (int)$limit);
        
        if (!empty($table)) {
            $this->db->bind(':table', $table);
        }
        
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting audit logs with triggers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get suspicious login activities detected by triggers
 */
public function getSuspiciousActivities($limit = 50) {
    try {
        $this->db->query("
            SELECT 
                la.*,
                ui.First_Name,
                ui.Last_Name,
                ui.User_Role,
                COUNT(*) as attempt_count
            FROM LOGIN_ATTEMPTS la
            LEFT JOIN USER_INFORMATION ui ON la.user_id = ui.ID
            WHERE la.success = FALSE
            AND la.attempt_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            GROUP BY la.ip_address
            HAVING attempt_count >= 3
            ORDER BY attempt_count DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting suspicious activities: " . $e->getMessage());
        return [];
    }
}

/**
 * Get login statistics for dashboard
 */
public function getLoginStatistics($hours = 24) {
    try {
        $this->db->query("
            SELECT 
                COUNT(*) as total_attempts,
                SUM(CASE WHEN success = TRUE THEN 1 ELSE 0 END) as successful_logins,
                SUM(CASE WHEN success = FALSE THEN 1 ELSE 0 END) as failed_logins,
                COUNT(DISTINCT ip_address) as unique_ips,
                COUNT(DISTINCT user_id) as unique_users
            FROM LOGIN_ATTEMPTS 
            WHERE attempt_time >= DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");
        $this->db->bind(1, $hours);
        
        return $this->db->single();
    } catch (Exception $e) {
        error_log("Error getting login statistics: " . $e->getMessage());
        return null;
    }
}






}





?>