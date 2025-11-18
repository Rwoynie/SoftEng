<?php
// Correct path for Database.php
require_once __DIR__ . '/../../Database/config.php';

class Profile {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get user profile data by user ID
     */
    public function getUserProfile($userId) {
        try {
            $this->db->query("
                SELECT 
                    ui.ID,
                    ui.First_Name,
                    ui.Middle_Name,
                    ui.Last_Name,
                    ui.Extension,
                    ui.Email,
                    ui.User_ID,
                    ui.Student_ID,
                    ui.Employee_ID,
                    ui.User_Role,
                    ui.Acc_Status,
                    ui.Department,
                    ui.Course,
                    ui.created_at,
                    ui.updated_at,
                    COALESCE(la.last_login, ui.created_at) as last_login,
                    CONCAT(ui.First_Name, ' ', 
                           IFNULL(CONCAT(ui.Middle_Name, ' '), ''), 
                           ui.Last_Name,
                           IFNULL(CONCAT(' ', ui.Extension), '')) as Full_Name
                FROM USER_INFORMATION ui
                LEFT JOIN (
                    SELECT user_id, MAX(attempt_time) as last_login
                    FROM LOGIN_ATTEMPTS 
                    WHERE success = 1 
                    GROUP BY user_id
                ) la ON ui.ID = la.user_id
                WHERE ui.ID = :user_id
            ");
            
            $this->db->bind(':user_id', $userId);
            $result = $this->db->singleAssoc();
            
            if ($result) {
                // Format dates for display
                $result['member_since'] = date('F j, Y', strtotime($result['created_at']));
                $result['last_login_formatted'] = date('F j, Y g:i A', strtotime($result['last_login']));
                
                // Format role for display
                $result['role_display'] = $this->formatRole($result['User_Role']);
                
                // Format status for display
                $result['status_display'] = ucfirst($result['Acc_Status']);
                
                return $result;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Profile Model Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user profile information
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['First_Name', 'Middle_Name', 'Last_Name', 'Extension', 'Email'];
            $updates = [];
            $bindings = [':user_id' => $userId];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = :$field";
                    $bindings[":$field"] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $query = "UPDATE USER_INFORMATION SET " . implode(', ', $updates) . " WHERE ID = :user_id";
            $this->db->query($query);
            
            foreach ($bindings as $key => $value) {
                $this->db->bind($key, $value);
            }
            
            return $this->db->execute();
            
        } catch (Exception $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // First, verify current password
            $this->db->query("SELECT pswrd, Salt FROM USER_INFORMATION WHERE ID = :user_id");
            $this->db->bind(':user_id', $userId);
            $user = $this->db->singleAssoc();
            
            if (!$user) {
                return false;
            }
            
            // Verify current password
            if (!password_verify($currentPassword . $user['Salt'], $user['pswrd'])) {
                return false;
            }
            
            // Generate new salt and hash new password
            $newSalt = bin2hex(random_bytes(16));
            $newHashedPassword = password_hash($newPassword . $newSalt, PASSWORD_DEFAULT);
            
            // Update password and salt
            $this->db->query("UPDATE USER_INFORMATION SET pswrd = :password, Salt = :salt WHERE ID = :user_id");
            $this->db->bind(':password', $newHashedPassword);
            $this->db->bind(':salt', $newSalt);
            $this->db->bind(':user_id', $userId);
            
            return $this->db->execute();
            
        } catch (Exception $e) {
            error_log("Password Change Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Format role for display
     */
    private function formatRole($role) {
        $roleMap = [
            'student' => 'Student',
            'faculty' => 'Faculty',
            'SubAdmin' => 'Sub Administrator',
            'superAdmin' => 'Super Administrator'
        ];
        
        return $roleMap[$role] ?? ucfirst($role);
    }

    /**
 * Get user by email
 */
    public function getUserByEmail($email) {
        $this->db->query("
            SELECT ID, Email, CONCAT(First_Name, ' ', Last_Name) as Full_Name 
            FROM USER_INFORMATION 
            WHERE Email = :email AND Acc_Status = 'approved'
        ");
        $this->db->bind(':email', $email);
        return $this->db->singleAssoc();
    }

    /**
     * Store password reset token
     */
    public function storePasswordResetToken($userId, $token, $expiry) {
        $this->db->query("
            INSERT INTO password_reset_tokens (user_id, token, expiry_date) 
            VALUES (:user_id, :token, :expiry)
            ON DUPLICATE KEY UPDATE 
            token = VALUES(token), 
            expiry_date = VALUES(expiry_date),
            created_at = NOW()
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':token', $token);
        $this->db->bind(':expiry', $expiry);
        return $this->db->execute();
    }

    /**
     * Verify current password
     */
    public function verifyCurrentPassword($userId, $currentPassword) {
        try {
            $this->db->query("SELECT pswrd, Salt FROM USER_INFORMATION WHERE ID = :user_id");
            $this->db->bind(':user_id', $userId);
            $user = $this->db->singleAssoc();
            
            if ($user && isset($user['pswrd']) && isset($user['Salt'])) {
                $hashedInput = password_hash($currentPassword . $user['Salt'], PASSWORD_DEFAULT);
                return password_verify($currentPassword . $user['Salt'], $user['pswrd']);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error verifying current password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store password change PIN
     */
    public function storePasswordChangePin($userId, $pin, $expiry) {
        try {
            // First, clear any existing PIN for this user
            $this->clearPasswordChangePin($userId);
            
            $this->db->query("
                INSERT INTO password_change_pins (user_id, pin_code, expiry_date) 
                VALUES (:user_id, :pin, :expiry)
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':pin', $pin);
            $this->db->bind(':expiry', $expiry);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error storing password change PIN: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password change PIN
     */
    public function verifyPasswordChangePin($userId, $pin) {
        try {
            $this->db->query("
                SELECT id FROM password_change_pins 
                WHERE user_id = :user_id 
                AND pin_code = :pin 
                AND expiry_date > NOW()
                AND used = 0
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':pin', $pin);
            $result = $this->db->singleAssoc();
            
            if ($result) {
                // Mark PIN as used
                $this->db->query("UPDATE password_change_pins SET used = 1 WHERE id = :id");
                $this->db->bind(':id', $result['id']);
                $this->db->execute();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error verifying password change PIN: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear password change PIN
     */
    public function clearPasswordChangePin($userId) {
        try {
            $this->db->query("DELETE FROM password_change_pins WHERE user_id = :user_id");
            $this->db->bind(':user_id', $userId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error clearing password change PIN: " . $e->getMessage());
            return false;
        }
    }
}
?>