<?php

require_once 'Model.php';
require_once 'RoleModel.php'; // Include the RoleModel

class User extends Model {
    protected $tableName = 'USER_INFORMATION'; // Set table name
    
    /**
     * Get database instance for external use
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * User registration method - IMPROVED VERSION with automatic role creation
     */
    public function register($data) {
        try {
            // Generate salt and hash password
            $salt = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($data['password'] . $salt, PASSWORD_DEFAULT);
            
            // Generate unique User_ID based on role
            $userRole = $data['user_role'] ?? 'student';
            $userId = $this->generateUserId($userRole, $data);
            
            // Check if User_ID already exists
            if ($this->userIdExists($userId)) {
                throw new Exception("User ID '$userId' is already registered");
            }
            
            // Check if email already exists
            if ($this->emailExists($data['email'])) {
                throw new Exception("Email address '{$data['email']}' is already registered");
            }
            
            // Build the SQL query based on available data
            $fields = ['pswrd', 'Salt', 'First_Name', 'Last_Name', 'Email', 'User_ID', 'User_Role', 'Acc_Status', 'Profile_Pic'];
            $values = [':password', ':salt', ':first_name', ':last_name', ':email', ':user_id', ':user_role', ':acc_status', ':profile_pic'];
            $bindings = [
                ':password' => $hashedPassword,
                ':salt' => $salt,
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':email' => $data['email'] ?? '',
                ':user_id' => $userId,
                ':user_role' => $userRole,
                ':acc_status' => $data['acc_status'] ?? 'pending',
                ':profile_pic' => $data['profile_pic']
            ];
            
            // Optional fields
            $optionalFields = [
                'middle_name' => 'Middle_Name',
                'extension' => 'Extension',
                'course' => 'Course',
                'department' => 'Department',
                'designation' => 'Designation',
            ];
            
            foreach ($optionalFields as $dataKey => $dbField) {
                if (!empty($data[$dataKey])) {
                    $fields[] = $dbField;
                    $values[] = ":$dataKey";
                    $bindings[":$dataKey"] = $data[$dataKey];
                }
            }
            
            // Add Student_ID or Employee_ID based on role
            if ($userRole === 'student' && !empty($data['student_id'])) {
                $fields[] = 'Student_ID';
                $values[] = ':student_id';
                $bindings[':student_id'] = $data['student_id'];
                
                // Check if Student_ID already exists
                if ($this->studentIdExists($data['student_id'])) {
                    throw new Exception("Student ID '{$data['student_id']}' is already registered");
                }
            } elseif ($userRole === 'faculty' && !empty($data['employee_id'])) {
                $fields[] = 'Employee_ID';
                $values[] = ':employee_id';
                $bindings[':employee_id'] = $data['employee_id'];
                
                // Check if Employee_ID already exists
                if ($this->employeeIdExists($data['employee_id'])) {
                    throw new Exception("Employee ID '{$data['employee_id']}' is already registered");
                }
            }
            
            // Build the final query
            $sql = 'INSERT INTO ' . $this->tableName . ' (' . implode(', ', $fields) . ') 
                    VALUES (' . implode(', ', $values) . ')';
            
            $this->db->query($sql);
            
            // Bind all parameters
            foreach ($bindings as $key => $value) {
                $this->db->bind($key, $value);
            }
            
            // Execute the query
            $result = $this->db->execute();
            
            // If registration successful, create default role entry
            if ($result) {
                $newUserId = $this->db->lastInsertId();
                $this->createDefaultRole($newUserId, $userRole);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            throw $e; // Re-throw to let controller handle it
        }
    }
    
    /**
     * Create default role entry for new user
     */
    private function createDefaultRole($userId, $userRole) {
        try {
            $roleModel = new RoleModel($this->db);
            
            // Set default permissions based on user role
            switch ($userRole) {
                case 'superAdmin':
                case 'admin':
                    // Admins get all permissions by default
                    $subAdmin = 'Yes';
                    $canEdit = 'Yes';
                    $manageAccess = 'Yes';
                    break;
                case 'faculty':
                    // Faculty can edit but not manage access
                    $subAdmin = 'No';
                    $canEdit = 'Yes';
                    $manageAccess = 'No';
                    break;
                case 'student':
                default:
                    // Students get no special permissions
                    $subAdmin = 'No';
                    $canEdit = 'No';
                    $manageAccess = 'No';
                    break;
            }
            
            // Store the original user role so we can restore it later if needed
            $originalUserRole = $userRole;
            
            // Create the role entry with the original user role
            return $roleModel->createRole($userId, $subAdmin, $canEdit, $manageAccess, $originalUserRole);
            
        } catch (Exception $e) {
            error_log("Error creating default role for user $userId: " . $e->getMessage());
            // Don't throw exception here - registration should still succeed even if role creation fails
            return false;
        }
    }
    
    /**
     * Generate unique User_ID based on role
     */
    private function generateUserId($role, $data) {
        if ($role === 'student') {
            // For students: S + Student_ID (e.g., S2025-12345)
            $studentId = $data['student_id'] ?? '';
            if (empty($studentId)) {
                throw new Exception("Student ID is required for student registration");
            }
            return 'S' . $studentId;
        } elseif ($role === 'faculty') {
            // For faculty: F + Employee_ID (e.g., FEMP-12345)
            $employeeId = $data['employee_id'] ?? '';
            if (empty($employeeId)) {
                throw new Exception("Employee ID is required for faculty registration");
            }
            return 'F' . $employeeId;
        } else {
            // For other roles (like admin), use as is
            return $data['user_id'] ?? uniqid();
        }
    }
    
    /**
     * User login method
     */
    public function login($identifier, $password) {
        try {
            // Allow login by email, user_id, student_id, or employee_id
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE (Email = :identifier OR User_ID = :identifier OR Student_ID = :identifier OR Employee_ID = :identifier) AND Acc_Status = "approved"');
            $this->db->bind(':identifier', $identifier);
            $result = $this->db->single();
            
            if ($result) {
                // Verify password
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                
                if (password_verify($password . $salt, $hashedPassword)) {
                    return $result;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("User login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email already exists
     */
    public function emailExists($email) {
        try {
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE Email = :email');
            $this->db->bind(':email', $email);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if User_ID already exists
     */
    public function userIdExists($userId) {
        try {
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE User_ID = :user_id');
            $this->db->bind(':user_id', $userId);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("User ID exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if Student_ID already exists
     */
    public function studentIdExists($studentId) {
        try {
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE Student_ID = :student_id');
            $this->db->bind(':student_id', $studentId);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Student ID exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if Employee_ID already exists
     */
    public function employeeIdExists($employeeId) {
        try {
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE Employee_ID = :employee_id');
            $this->db->bind(':employee_id', $employeeId);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Employee ID exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get error message
     */
    public function getError() {
        return $this->error ?? null;
    }

    /**
     * Check if a user exists (regardless of status)
     */
    public function checkUserExists($identifier) {
        try {
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE Email = :identifier OR User_ID = :identifier OR Student_ID = :identifier OR Employee_ID = :identifier');
            $this->db->bind(':identifier', $identifier);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("User exists check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user account status
     */
    public function getUserStatus($identifier) {
        try {
            $this->db->query('SELECT Acc_Status FROM USER_INFORMATION WHERE Email = :identifier OR User_ID = :identifier OR Student_ID = :identifier OR Employee_ID = :identifier');
            $this->db->bind(':identifier', $identifier);
            $result = $this->db->single();
            
            if ($result) {
                return $result->Acc_Status;
            }
            return null;
        } catch (Exception $e) {
            error_log("Get user status error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Enhanced login method that returns more detailed information
     */
    public function loginWithStatus($identifier, $password) {
        try {
            // Get user regardless of status
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE Email = :identifier OR User_ID = :identifier OR Student_ID = :identifier OR Employee_ID = :identifier');
            $this->db->bind(':identifier', $identifier);
            $result = $this->db->single();
            
            if ($result) {
                // Verify password first
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                
                if (password_verify($password . $salt, $hashedPassword)) {
                    return $result;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("User login with status error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user with role information
     */
    public function getUserWithRole($userId) {
        try {
            $this->db->query('
                SELECT ui.*, r.Sub_Admin, r.Can_Edit, r.Manage_Access 
                FROM USER_INFORMATION ui 
                LEFT JOIN ROLES r ON ui.ID = r.User_ID 
                WHERE ui.ID = :user_id
            ');
            $this->db->bind(':user_id', $userId);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Get user with role error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user account status and optionally update roles
     */
    public function updateAccountStatus($userId, $status, $roleData = null) {
        try {
            $this->db->beginTransaction();
            
            // Update user status
            $this->db->query('UPDATE USER_INFORMATION SET Acc_Status = :status WHERE ID = :user_id');
            $this->db->bind(':status', $status);
            $this->db->bind(':user_id', $userId);
            $userUpdated = $this->db->execute();
            
            // Update roles if provided
            $roleUpdated = true;
            if ($roleData !== null && $userUpdated) {
                $roleModel = new RoleModel($this->db);
                $roleUpdated = $roleModel->saveRole(
                    $userId, 
                    $roleData['sub_admin'] ?? 'No', 
                    $roleData['can_edit'] ?? 'No', 
                    $roleData['manage_access'] ?? 'No'
                );
            }
            
            if ($userUpdated && $roleUpdated) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update account status error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin login method - ONLY allows login by User_ID
     */
    public function loginAdmin($user_id, $password) {
        try {
            // MODIFIED: Only allow login by User_ID for admin users
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE User_ID = :user_id AND Acc_Status = "approved"');
            $this->db->bind(':user_id', $user_id);
            $result = $this->db->single();
            
            if ($result) {
                // Verify password
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                
                if (password_verify($password . $salt, $hashedPassword)) {
                    return $result;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Student/Faculty login method - ONLY allows login by Email
     */
    public function loginByEmail($email, $password) {
        try {
            // MODIFIED: Only allow login by Email for student/faculty users
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE Email = :email AND Acc_Status = "approved"');
            $this->db->bind(':email', $email);
            $result = $this->db->single();
            
            if ($result) {
                // Verify password
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                
                if (password_verify($password . $salt, $hashedPassword)) {
                    return $result;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Email login error: " . $e->getMessage());
            return false;
        }
    }
}
?>