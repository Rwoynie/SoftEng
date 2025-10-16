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
                    Designation,
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
                    Designation,
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
                    Designation,
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
            // Total users
            $this->db->query("SELECT COUNT(*) as total FROM USER_INFORMATION");
            $stats['total_users'] = $this->db->single()->total;

            // Total approved users
            $this->db->query("SELECT COUNT(*) as total FROM USER_INFORMATION WHERE Acc_Status = 'approved'");
            $stats['approved_users'] = $this->db->single()->total;

            // Total pending users
            $this->db->query("SELECT COUNT(*) as total FROM USER_INFORMATION WHERE Acc_Status = 'pending'");
            $stats['pending_users'] = $this->db->single()->total;

            // Total theses
            $this->db->query("SELECT COUNT(*) as total FROM THESIS");
            $stats['total_theses'] = $this->db->single()->total;

            // Total reviews
            $this->db->query("SELECT COUNT(*) as total FROM THESIS_REVIEWS");
            $stats['total_reviews'] = $this->db->single()->total;

            // Recent theses count (last 7 days)
            $this->db->query("SELECT COUNT(*) as total FROM THESIS WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['recent_theses'] = $this->db->single()->total;

            return $stats;

        } catch (Exception $e) {
            error_log("Error getting system statistics: " . $e->getMessage());
            return $stats;
        }
    }

    /**
     * Get activity logs (simplified - you might want to create a separate logs table)
     */
    public function getRecentActivity($limit = 20) {
        try {
            // This is a simplified version - in production, you'd have a dedicated activity log table
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
            // If your database class has error info, log it:
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
        // Debug: Log the data being received
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
}
?>