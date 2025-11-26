<?php

require_once 'Model.php';
require_once 'RoleModel.php'; // Include the RoleModel

class User extends Model {
    protected $tableName = 'USER_INFORMATION'; // Set table name
	protected $error = null;
    
    /**
     * Get database instance for external use
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Hash sensitive data using SHA-256
     */
    public function hashData($data) {
        // Remove any trimming and case conversion that might be changing the input
        return hash('sha256', $data);
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
            
            // Hash sensitive identifiers for security
            $emailHash = $this->hashData($data['email']);
            $userIdHash = $this->hashData($userId);
            
            // Build the SQL query - store both plain and hashed values
            $fields = [
                'pswrd', 'Salt', 'First_Name', 'Last_Name', 
                'Email', 'Email_Hash',           // Plain AND hashed email
                'User_ID', 'User_ID_Hash',       // Plain AND hashed User_ID
                'User_Role', 'Acc_Status', 'Profile_Pic'
            ];
            
            $values = [
                ':password', ':salt', ':first_name', ':last_name',
                ':email_plain', ':email_hash',
                ':user_id_plain', ':user_id_hash',
                ':user_role', ':acc_status', ':profile_pic'
            ];
            
            $bindings = [
                ':password' => $hashedPassword,
                ':salt' => $salt,
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':email_plain' => $data['email'],      // Store plain email
                ':email_hash' => $emailHash,           // Store hashed email
                ':user_id_plain' => $userId,           // Store plain User_ID
                ':user_id_hash' => $userIdHash,        // Store hashed User_ID
                ':user_role' => $userRole,
                ':acc_status' => $data['acc_status'] ?? 'pending',
                ':profile_pic' => $data['profile_pic'] ?? null
            ];
            
            // Optional fields
            $optionalFields = [
                'middle_name' => 'Middle_Name',
                'extension' => 'Extension',
                'course' => 'Course',
                'department' => 'Department',
            ];
            
            foreach ($optionalFields as $dataKey => $dbField) {
                if (!empty($data[$dataKey])) {
                    $fields[] = $dbField;
                    $values[] = ":$dataKey";
                    $bindings[":$dataKey"] = $data[$dataKey];
                }
            }
            
            // Add both plain and hashed Student_ID or Employee_ID
            if (($userRole === 'student' || $userRole === 'researcher') && !empty($data['student_id'])) {
                $studentIdHash = $this->hashData($data['student_id']);
                
                // Check if hashed value already exists
                if ($this->valueExists('Student_ID_Hash', $studentIdHash)) {
                    throw new Exception("Student ID is already registered");
                }
                
                $fields[] = 'Student_ID';
                $fields[] = 'Student_ID_Hash';
                $values[] = ':student_id_plain';
                $values[] = ':student_id_hash';
                $bindings[':student_id_plain'] = $data['student_id'];  // Plain
                $bindings[':student_id_hash'] = $studentIdHash;        // Hashed
            } elseif ($userRole === 'faculty' && !empty($data['employee_id'])) {
                $employeeIdHash = $this->hashData($data['employee_id']);
                
                // Check if hashed value already exists
                if ($this->valueExists('Employee_ID_Hash', $employeeIdHash)) {
                    throw new Exception("Employee ID is already registered");
                }
                
                $fields[] = 'Employee_ID';
                $fields[] = 'Employee_ID_Hash';
                $values[] = ':employee_id_plain';
                $values[] = ':employee_id_hash';
                $bindings[':employee_id_plain'] = $data['employee_id'];  // Plain
                $bindings[':employee_id_hash'] = $employeeIdHash;        // Hashed
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
                return $userId;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
 * Find user by email - CASE-INSENSITIVE plain text lookup
 */
public function findByEmail($email) {
    $query = "SELECT * FROM USER_INFORMATION WHERE LOWER(Email) = LOWER(:email) LIMIT 1";
    $this->db->query($query);
    $this->db->bind(':email', trim($email));
    
    $result = $this->db->single();
    
    error_log("findByEmail('$email') -> " . ($result ? 'FOUND (ID: ' . $result->ID . ')' : 'NOT FOUND'));
    
    return $result;
}

   

  
    /**
     * Register a user from Google Sign-In - SEPARATE METHOD with different requirements
     */
    public function registerGoogleUser($data) {
        try {
            error_log("=== USER MODEL GOOGLE REGISTRATION (DUAL STORAGE) ===");
            error_log("Received data: " . print_r($data, true));
            
            // Validate required fields
            if (empty($data['email']) || empty($data['user_role'])) {
                throw new Exception("Email and user role are required for Google registration");
            }
            
            // NEW: Restrict Google Sign-In to @usep.edu.ph domain only
            if (!preg_match('/@usep\.edu\.ph$/i', $data['email'])) {
                error_log("Google registration blocked: Non-USeP email attempted - " . $data['email']);
                throw new Exception("Only USeP email addresses (@usep.edu.ph) are allowed for Google registration");
            }
            
            $userRole = $data['user_role'];
            
            // Generate salt and hash password
            $salt = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($data['password'] . $salt, PASSWORD_DEFAULT);
            
            // Generate unique User_ID based on role
            $originalUserId = $this->generateUserId($userRole, $data);
            $userIdHash = $this->hashData($originalUserId);
            
            // Hash sensitive identifiers for storage
            $emailHash = $this->hashData($data['email']);
            
            // Check if hashed values already exist in hashed columns
            if ($this->valueExists('Email_Hash', $emailHash)) {
                throw new Exception("Email address is already registered");
            }
            
            if ($this->valueExists('User_ID_Hash', $userIdHash)) {
                throw new Exception("User ID is already registered");
            }
            
            // Build the SQL query with dual storage
            $fields = [
                'pswrd', 'Salt', 'First_Name', 'Last_Name', 
                'Email', 'Email_Hash',           // Plain AND hashed email
                'User_ID', 'User_ID_Hash',       // Plain AND hashed User_ID
                'User_Role', 'Acc_Status', 'Login_Method'
            ];
            
            $values = [
                ':password', ':salt', ':first_name', ':last_name',
                ':email_plain', ':email_hash',
                ':user_id_plain', ':user_id_hash',
                ':user_role', ':acc_status', ':login_method'
            ];
            
            $bindings = [
                ':password' => $hashedPassword,
                ':salt' => $salt,
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':email_plain' => $data['email'],      // Store plain email
                ':email_hash' => $emailHash,           // Store hashed email
                ':user_id_plain' => $originalUserId,   // Store plain User_ID
                ':user_id_hash' => $userIdHash,        // Store hashed User_ID
                ':user_role' => $userRole,
                ':acc_status' => 'approved', // Google users are auto-approved
                ':login_method' => 'google'
            ];
            
            // Optional fields
            $optionalFields = [
                'middle_name' => 'Middle_Name',
                'extension' => 'Extension',
                'course' => 'Course',
                'department' => 'Department',
            ];
            
            foreach ($optionalFields as $dataKey => $dbField) {
                if (!empty($data[$dataKey])) {
                    $fields[] = $dbField;
                    $values[] = ":$dataKey";
                    $bindings[":$dataKey"] = $data[$dataKey];
                }
            }
            
            // Add both plain and hashed Student_ID or Employee_ID
            if ($userRole === 'student' && !empty($data['student_id'])) {
                $studentIdHash = $this->hashData($data['student_id']);
                
                if ($this->valueExists('Student_ID_Hash', $studentIdHash)) {
                    throw new Exception("Student ID is already registered");
                }
                
                $fields[] = 'Student_ID';
                $fields[] = 'Student_ID_Hash';
                $values[] = ':student_id_plain';
                $values[] = ':student_id_hash';
                $bindings[':student_id_plain'] = $data['student_id'];  // Plain
                $bindings[':student_id_hash'] = $studentIdHash;        // Hashed
            } elseif ($userRole === 'faculty' && !empty($data['employee_id'])) {
                $employeeIdHash = $this->hashData($data['employee_id']);
                
                if ($this->valueExists('Employee_ID_Hash', $employeeIdHash)) {
                    throw new Exception("Employee ID is already registered");
                }
                
                $fields[] = 'Employee_ID';
                $fields[] = 'Employee_ID_Hash';
                $values[] = ':employee_id_plain';
                $values[] = ':employee_id_hash';
                $bindings[':employee_id_plain'] = $data['employee_id'];  // Plain
                $bindings[':employee_id_hash'] = $employeeIdHash;        // Hashed
            }
            
            // Build the final query
            $sql = 'INSERT INTO ' . $this->tableName . ' (' . implode(', ', $fields) . ') 
                    VALUES (' . implode(', ', $values) . ')';
            
            error_log("Google registration SQL: " . $sql);
            error_log("Google registration bindings: " . print_r($bindings, true));
            
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
                
                error_log("Google registration SUCCESS - DB ID: " . $newUserId . ", Original User_ID: " . $originalUserId);
                
                // Return both the database ID and original User_ID
                return [
                    'db_id' => $newUserId,
                    'user_identifier' => $originalUserId
                ];
            }
            
            error_log("Google registration result: " . ($result ? 'SUCCESS' : 'FAILED'));
            return false;
            
        } catch (Exception $e) {
            error_log("Google user registration error: " . $e->getMessage());
            $this->error = $e->getMessage();
            return false;
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
                    $canEdit = 'No';
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
 * Generate unique User_ID based on role - FIXED VERSION
 */
public function generateUserId($role, $data) {
    error_log("=== GENERATE USER ID ===");
    error_log("Role: " . $role);
    
    $role = strtolower($role);
    
    if ($role === 'student' || $role === 'researcher') {
        $studentId = $data['student_id'] ?? '';
        error_log("Student ID from data: " . $studentId);
        
        if (empty($studentId)) {
            // Generate student ID: STU + Year + 5 random digits
            $studentId = 'STU' . date('Y') . '-' . sprintf('%05d', random_int(0, 99999));
            error_log("Generated Student ID: " . $studentId);
        }
        return $studentId;
        
    } elseif ($role === 'faculty') {
        $employeeId = $data['employee_id'] ?? '';
        error_log("Employee ID from data: " . $employeeId);
        
        if (empty($employeeId)) {
            // Generate faculty ID: FAC + Year + 5 random digits
            $employeeId = 'FAC' . date('Y') . '-' . sprintf('%05d', random_int(0, 99999));
            error_log("Generated Faculty ID: " . $employeeId);
        }
        return $employeeId;
        
    } else {
        // For other roles: USR + Year + 5 random digits
        $userId = $data['user_id'] ?? 'USR' . date('Y') . '-' . sprintf('%05d', random_int(0, 99999));
        error_log("Other role User ID: " . $userId);
        return $userId;
    }
}
    
    /**
     * User login method
     */
    public function login($identifier, $password) {
        try {
            error_log("=== LOGIN ATTEMPT ===");
            
            // Hash the identifier for lookup
            $identifierHash = $this->hashData($identifier);
            
            // Lookup by hashed values
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE 
                Email = :identifier OR 
                User_ID = :identifier OR 
                Student_ID = :identifier OR 
                Employee_ID = :identifier AND Acc_Status = "approved"');
            $this->db->bind(':identifier', $identifierHash);
            $result = $this->db->single();
            
            if ($result) {
                error_log("User found with role: " . ($result->User_Role ?? 'Unknown'));
                
                // Verify password
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                $passwordWithSalt = $password . $salt;
                
                if (password_verify($passwordWithSalt, $hashedPassword)) {
                    error_log("✅ Login SUCCESSFUL");
                    return $result;
                } else {
                    error_log("❌ Password verification FAILED");
                }
            } else {
                error_log("❌ No approved user found");
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("User login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email already exists - hashes input and checks plain column
     */
    public function emailExists($email) {
        $emailHash = $this->hashData($email);
        return $this->valueExists('Email_Hash', $emailHash);
    }

    /**
     * Check if User_ID already exists - hashes input and checks plain column
     */
    public function userIdExists($userId) {
        $userIdHash = $this->hashData($userId);
        return $this->valueExists('User_ID_Hash', $userIdHash);
    }
    
    /**
     * Check if Student_ID already exists - hashes input and checks hashed column
     */
    public function studentIdExists($studentId) {
        $studentIdHash = $this->hashData($studentId);
        return $this->valueExists('Student_ID_Hash', $studentIdHash);
    }
    
    /**
     * Check if Employee_ID already exists - hashes input and checks hashed column
     */
    public function employeeIdExists($employeeId) {
        $employeeIdHash = $this->hashData($employeeId);
        return $this->valueExists('Employee_ID_Hash', $employeeIdHash);
    }
    
    /**
     * Get error message
     */
    public function getError() {
        return $this->error ?? null;
    }

    /**
     * Update user profile (course/department)
     */
    public function updateUserProfile($userId, $course = null, $department = null) {
        try {
            $query = "UPDATE USER_INFORMATION SET ";
            $params = [];
            $bindings = [];
            
            if ($course !== null) {
                $params[] = "Course = :course";
                $bindings[':course'] = $course;
            }
            
            if ($department !== null) {
                $params[] = "Department = :department";
                $bindings[':department'] = $department;
            }
            
            if (empty($params)) {
                return false; // Nothing to update
            }
            
            $query .= implode(', ', $params) . " WHERE ID = :user_id";
            $bindings[':user_id'] = $userId;
            
            $this->db->query($query);
            
            foreach ($bindings as $key => $value) {
                $this->db->bind($key, $value);
            }
            
            return $this->db->execute();
            
        } catch (Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a user exists (regardless of status)
     */
    public function checkUserExists($identifier) {
        try {
            $identifierHash = $this->hashData($identifier);
            
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE 
                Email = :identifier OR 
                User_ID = :identifier OR 
                Student_ID = :identifier OR 
                Employee_ID = :identifier');
            $this->db->bind(':identifier', $identifierHash);
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
            $identifierHash = $this->hashData($identifier);
            
            $this->db->query('SELECT Acc_Status FROM USER_INFORMATION WHERE 
                Email = :identifier OR 
                User_ID = :identifier OR 
                Student_ID = :identifier OR 
                Employee_ID = :identifier');
            $this->db->bind(':identifier', $identifierHash);
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
            error_log("=== USER MODEL loginAdmin (HASHED LOOKUP) ===");
            error_log("User_ID provided: " . $user_id);
            
            // Hash the user_id for lookup
            $userIdHash = $this->hashData($user_id);
            error_log("Hashed User_ID for lookup: " . $userIdHash);
            
            // Query using hashed value in User_ID_Hash column
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE User_ID_Hash = :user_id AND Acc_Status = "approved"');
            $this->db->bind(':user_id', $userIdHash); // Lookup hashed value in User_ID_Hash column
            $result = $this->db->single();
            
            error_log("Database query result: " . ($result ? "USER FOUND" : "USER NOT FOUND"));
            
            if ($result) {
                error_log("User found - Role: " . ($result->User_Role ?? 'unknown'));
                
                // Verify password
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                
                $passwordWithSalt = $password . $salt;
                $verificationResult = password_verify($passwordWithSalt, $hashedPassword);
                
                error_log("Password verification: " . ($verificationResult ? "SUCCESS" : "FAILED"));
                
                if ($verificationResult) {
                    error_log("✅ PASSWORD VERIFICATION SUCCESS");
                    return $result;
                } else {
                    error_log("❌ PASSWORD VERIFICATION FAILED");
                    return false;
                }
            } else {
                error_log("❌ No approved user found with hashed User_ID: " . $userIdHash);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("💥 Admin login error: " . $e->getMessage());
            return false;
        }
    }

   

    /**
     * Student/Faculty login method - ONLY allows login by Email
     */
    public function loginByEmail($email, $password) {
        error_log("=== USER MODEL loginByEmail (HASHED LOOKUP) ===");
        
        $emailHash = $this->hashData($email);
        error_log("Hashed email for lookup: " . $emailHash);
        
        // Use Email_Hash column for lookup
        $this->db->query('SELECT * FROM USER_INFORMATION WHERE Email_Hash = :email AND Acc_Status = "approved"');
        $this->db->bind(':email', $emailHash); // Lookup hashed value in Email_Hash column
        
        $row = $this->db->single();
        
        if ($row) {
            error_log("User found");
            
            $hashedPassword = $row->pswrd;
            $salt = $row->Salt;
            $passwordWithSalt = $password . $salt;
            
            if (password_verify($passwordWithSalt, $hashedPassword)) {
                error_log("✅ Password verification SUCCESS");
                return $row;
            } else {
                error_log("❌ Password verification FAILED");
            }
        } else {
            error_log("❌ No approved user found with hashed email: " . $emailHash);
        }
        
        return false;
    }

    /**
 * Check if plain text value already exists in database
 */
public function valueExists($field, $value) {
    try {
        $this->db->query("SELECT ID FROM USER_INFORMATION WHERE $field = :value");
        $this->db->bind(':value', $value);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Value exists check error: " . $e->getMessage());
        return false;
    }
}

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE ID = :user_id LIMIT 1');
            $this->db->bind(':user_id', $userId);
            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log login attempts
     */
    public function logLoginAttempt($email, $userId = null, $ipAddress = null, $userAgent = null, $success = false, $notes = '') {
        try {
            $query = "INSERT INTO LOGIN_ATTEMPTS (user_id, email, ip_address, user_agent, success, notes) 
                      VALUES (:user_id, :email, :ip_address, :user_agent, :success, :notes)";
            
            $this->db->query($query);
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':email', $email);
            $this->db->bind(':ip_address', $ipAddress);
            $this->db->bind(':user_agent', $userAgent);
            $this->db->bind(':success', $success);
            $this->db->bind(':notes', $notes);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Login attempt logging error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log user actions for audit
     */
    public function logUserAction($userId, $action, $description) {
        try {
            $query = "INSERT INTO AUDIT_LOGS (table_name, record_id, action, new_values, user_id) 
                      VALUES ('LOGIN', :user_id, :action, :description, :user_id)";
            
            $this->db->query($query);
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':action', $action);
            $this->db->bind(':description', json_encode(['description' => $description]));
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("User action logging error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent login attempts for a user
     */
    public function getRecentLoginAttempts($userId, $limit = 10) {
        try {
            $query = "SELECT * FROM LOGIN_ATTEMPTS 
                      WHERE user_id = :user_id 
                      ORDER BY attempt_time DESC 
                      LIMIT :limit";
            
            $this->db->query($query);
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':limit', $limit);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Get recent login attempts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check for suspicious login activity
     */
    public function checkSuspiciousActivity($email, $ipAddress, $timeFrameMinutes = 15) {
        try {
            $query = "SELECT COUNT(*) as attempt_count 
                      FROM LOGIN_ATTEMPTS 
                      WHERE (email = :email OR ip_address = :ip_address) 
                      AND success = 0 
                      AND attempt_time >= DATE_SUB(NOW(), INTERVAL :timeframe MINUTE)";
            
            $this->db->query($query);
            $this->db->bind(':email', $email);
            $this->db->bind(':ip_address', $ipAddress);
            $this->db->bind(':timeframe', $timeFrameMinutes);
            
            $result = $this->db->single();
            return $result->attempt_count;
        } catch (Exception $e) {
            error_log("Suspicious activity check error: " . $e->getMessage());
            return 0;
        }
    }


}
?>