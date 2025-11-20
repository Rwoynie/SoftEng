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
    private function hashData($data) {
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
            
            // Hash sensitive identifiers
            $emailHash = $this->hashData($data['email']);
            $userIdHash = $this->hashData($userId);
            
            // Check if hashed values already exist
            if ($this->hashedValueExists('Email_Hash', $emailHash)) {
                throw new Exception("Email address is already registered");
            }
            
            if ($this->hashedValueExists('User_ID_Hash', $userIdHash)) {
                throw new Exception("User ID is already registered");
            }
            
            // Build the SQL query with ONLY hashed fields
            $fields = ['pswrd', 'Salt', 'First_Name', 'Last_Name', 'Email_Hash', 'User_ID_Hash', 'User_Role', 'Acc_Status', 'Profile_Pic'];
            $values = [':password', ':salt', ':first_name', ':last_name', ':email_hash', ':user_id_hash', ':user_role', ':acc_status', ':profile_pic'];
            $bindings = [
                ':password' => $hashedPassword,
                ':salt' => $salt,
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':email_hash' => $emailHash,
                ':user_id_hash' => $userIdHash,
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
            
            // Add hashed Student_ID or Employee_ID
            if (($userRole === 'student' || $userRole === 'researcher') && !empty($data['student_id'])) {
                $studentIdHash = $this->hashData($data['student_id']);
                
                if ($this->hashedValueExists('Student_ID_Hash', $studentIdHash)) {
                    throw new Exception("Student ID is already registered");
                }
                
                $fields[] = 'Student_ID_Hash';
                $values[] = ':student_id_hash';
                $bindings[':student_id_hash'] = $studentIdHash;
            } elseif ($userRole === 'faculty' && !empty($data['employee_id'])) {
                $employeeIdHash = $this->hashData($data['employee_id']);
                
                if ($this->hashedValueExists('Employee_ID_Hash', $employeeIdHash)) {
                    throw new Exception("Employee ID is already registered");
                }
                
                $fields[] = 'Employee_ID_Hash';
                $values[] = ':employee_id_hash';
                $bindings[':employee_id_hash'] = $employeeIdHash;
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
            throw $e;
        }
    }

    /**
     * Find user by email - USES HASHED LOOKUP
     */
    public function findByEmail($email) {
        $emailHash = $this->hashData($email);
        
        $this->db->query('SELECT * FROM USER_INFORMATION WHERE Email_Hash = :email_hash LIMIT 1');
        $this->db->bind(':email_hash', $emailHash);
        $result = $this->db->single();
        
        // Convert object to array if needed
        if (is_object($result)) {
            $result = (array)$result;
        }
        
        error_log("findByEmail result: " . ($result ? 'FOUND' : 'NOT FOUND'));
        return $result;
    }

    /**
     * Check if hashed value already exists in database
     */
    private function hashedValueExists($hashField, $hashValue) {
        try {
            $this->db->query("SELECT ID FROM USER_INFORMATION WHERE $hashField = :hash_value");
            $this->db->bind(':hash_value', $hashValue);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Hashed value exists check error: " . $e->getMessage());
            return false;
        }
    }

  
    /**
     * Register a user from Google Sign-In - SEPARATE METHOD with different requirements
     */
    public function registerGoogleUser($data) {
        try {
            error_log("=== USER MODEL GOOGLE REGISTRATION (HASHED) ===");
            error_log("Received data: " . print_r($data, true));
            
            // Validate required fields
            if (empty($data['email']) || empty($data['user_role'])) {
                throw new Exception("Email and user role are required for Google registration");
            }
            
            $userRole = $data['user_role'];
            
            // Generate salt and hash password
            $salt = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($data['password'] . $salt, PASSWORD_DEFAULT);
            
            // Generate unique User_ID based on role
            $userId = $this->generateUserId($userRole, $data);
            
            // Hash sensitive identifiers - SAME METHOD AS REGULAR REGISTRATION
            $emailHash = $this->hashData($data['email']);
            $userIdHash = $this->hashData($userId);
            
            // Check if hashed values already exist
            if ($this->hashedValueExists('Email_Hash', $emailHash)) {
                throw new Exception("Email address is already registered");
            }
            
            if ($this->hashedValueExists('User_ID_Hash', $userIdHash)) {
                throw new Exception("User ID is already registered");
            }
            
            // Build the SQL query with ONLY hashed fields - SAME AS REGULAR REGISTRATION
            $fields = ['pswrd', 'Salt', 'First_Name', 'Last_Name', 'Email_Hash', 'User_ID_Hash', 'User_Role', 'Acc_Status'];
            $values = [':password', ':salt', ':first_name', ':last_name', ':email_hash', ':user_id_hash', ':user_role', ':acc_status'];
            $bindings = [
                ':password' => $hashedPassword,
                ':salt' => $salt,
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':email_hash' => $emailHash,
                ':user_id_hash' => $userIdHash,
                ':user_role' => $userRole,
                ':acc_status' => 'approved' // Google users are auto-approved
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
            
            // Add hashed Student_ID or Employee_ID - SAME AS REGULAR REGISTRATION
            if (($userRole === 'student') && !empty($data['student_id'])) {
                $studentIdHash = $this->hashData($data['student_id']);
                
                if ($this->hashedValueExists('Student_ID_Hash', $studentIdHash)) {
                    throw new Exception("Student ID is already registered");
                }
                
                $fields[] = 'Student_ID_Hash';
                $values[] = ':student_id_hash';
                $bindings[':student_id_hash'] = $studentIdHash;
            } elseif ($userRole === 'faculty' && !empty($data['employee_id'])) {
                $employeeIdHash = $this->hashData($data['employee_id']);
                
                if ($this->hashedValueExists('Employee_ID_Hash', $employeeIdHash)) {
                    throw new Exception("Employee ID is already registered");
                }
                
                $fields[] = 'Employee_ID_Hash';
                $values[] = ':employee_id_hash';
                $bindings[':employee_id_hash'] = $employeeIdHash;
            }
            
            // Build the final query
            $sql = 'INSERT INTO ' . $this->tableName . ' (' . implode(', ', $fields) . ') 
                    VALUES (' . implode(', ', $values) . ')';
            
            error_log("Google registration SQL: " . $sql);
            
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
            
            error_log("Google registration result: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
            
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
private function generateUserId($role, $data) {
    error_log("=== GENERATE USER ID DEBUG ===");
    error_log("Role: " . $role);
    error_log("Data keys: " . implode(', ', array_keys($data)));
    
    // Convert role to lowercase for consistent comparison
    $role = strtolower($role);
    
    // Handle student/researcher roles
    if ($role === 'student' || $role === 'researcher') {
        $studentId = $data['student_id'] ?? '';
        error_log("Student ID from data: " . $studentId);
        
        if (empty($studentId)) {
            // Generate a fallback student ID if not provided
            $studentId = 'STU' . date('Y') . '-' . random_int(1000, 9999);
            error_log("Generated fallback Student ID: " . $studentId);
        }
        return $studentId; // Just return the student_id without prefix
        
    } elseif ($role === 'faculty') {
        $employeeId = $data['employee_id'] ?? '';
        error_log("Employee ID from data: " . $employeeId);
        
        if (empty($employeeId)) {
            // Generate a fallback employee ID if not provided
            $employeeId = 'FAC' . date('Y') . '-' . random_int(1000, 9999);
            error_log("Generated fallback Employee ID: " . $employeeId);
        }
        return $employeeId; // Just return the employee_id without prefix
        
    } elseif ($role === 'admin' || $role === 'superadmin' || $role === 'subadmin') {
        $employeeId = $data['employee_id'] ?? '';
        if (!empty($employeeId)) {
            return $employeeId;
        } else {
            return 'ADM' . date('Y') . '-' . random_int(1000, 9999);
        }
    } else {
        // For other unknown roles
        $userId = $data['user_id'] ?? 'USR' . date('Y') . '-' . random_int(1000, 9999);
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
                Email_Hash = :identifier_hash OR 
                User_ID_Hash = :identifier_hash OR 
                Student_ID_Hash = :identifier_hash OR 
                Employee_ID_Hash = :identifier_hash AND Acc_Status = "approved"');
            $this->db->bind(':identifier_hash', $identifierHash);
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
     * Check if email already exists
     */
    public function emailExists($email) {
        $emailHash = $this->hashData($email);
        return $this->hashedValueExists('Email_Hash', $emailHash);
    }
    
    /**
     * Check if User_ID already exists
     */
    public function userIdExists($userId) {
        $userIdHash = $this->hashData($userId);
        return $this->hashedValueExists('User_ID_Hash', $userIdHash);
    }
    
    /**
     * Check if Student_ID already exists
     */
    public function studentIdExists($studentId) {
        $studentIdHash = $this->hashData($studentId);
        return $this->hashedValueExists('Student_ID_Hash', $studentIdHash);
    }
    
    /**
     * Check if Employee_ID already exists
     */
    public function employeeIdExists($employeeId) {
        $employeeIdHash = $this->hashData($employeeId);
        return $this->hashedValueExists('Employee_ID_Hash', $employeeIdHash);
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
            $identifierHash = $this->hashData($identifier);
            
            $this->db->query('SELECT ID FROM USER_INFORMATION WHERE 
                Email_Hash = :identifier_hash OR 
                User_ID_Hash = :identifier_hash OR 
                Student_ID_Hash = :identifier_hash OR 
                Employee_ID_Hash = :identifier_hash');
            $this->db->bind(':identifier_hash', $identifierHash);
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
                Email_Hash = :identifier_hash OR 
                User_ID_Hash = :identifier_hash OR 
                Student_ID_Hash = :identifier_hash OR 
                Employee_ID_Hash = :identifier_hash');
            $this->db->bind(':identifier_hash', $identifierHash);
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
            error_log("=== USER MODEL loginAdmin DETAILED DEBUG ===");
            error_log("User_ID provided: " . $user_id);
            error_log("Password provided: " . (!empty($password) ? "SET" : "EMPTY"));
            
            // Hash the user_id for lookup
            $userIdHash = $this->hashData($user_id);
            error_log("Hashed User_ID: " . $userIdHash);
            
            // Query using the hashed field - FIX: Remove Acc_Status check temporarily for debugging
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE User_ID_Hash = :user_id_hash');
            $this->db->bind(':user_id_hash', $userIdHash);
            $result = $this->db->single();
            
            if ($result) {
                error_log("✅ USER FOUND IN DATABASE");
                error_log("User Role: " . ($result->User_Role ?? 'unknown'));
                error_log("Account Status: " . ($result->Acc_Status ?? 'unknown'));
                error_log("First Name: " . ($result->First_Name ?? 'unknown'));
                
                // Check if account is approved
                if ($result->Acc_Status !== 'approved') {
                    error_log("❌ ACCOUNT NOT APPROVED. Status: " . $result->Acc_Status);
                    return false;
                }
                
                // Verify password
                $hashedPassword = $result->pswrd;
                $salt = $result->Salt;
                
                error_log("Stored hash: " . substr($hashedPassword, 0, 20) . "...");
                error_log("Stored salt: " . $salt);
                
                $passwordWithSalt = $password . $salt;
                $verificationResult = password_verify($passwordWithSalt, $hashedPassword);
                
                error_log("Password verification: " . ($verificationResult ? "SUCCESS" : "FAILED"));
                
                if ($verificationResult) {
                    error_log("✅ PASSWORD VERIFICATION SUCCESS - Returning user object");
                    return $result;
                } else {
                    error_log("❌ PASSWORD VERIFICATION FAILED");
                    
                    // Additional debug: Let's see what the actual password + salt combination is
                    error_log("Password + Salt combination: '" . $password . "' + '" . $salt . "'");
                    error_log("Combined length: " . strlen($passwordWithSalt));
                    
                    // Test with default password
                    $defaultPassword = 'compendiumSystemAdmin';
                    $defaultWithSalt = $defaultPassword . $salt;
                    $defaultCheck = password_verify($defaultWithSalt, $hashedPassword);
                    error_log("Default password test: " . ($defaultCheck ? "WORKS" : "FAILS"));
                    
                    return false;
                }
            } else {
                error_log("❌ NO USER FOUND with User_ID_Hash: " . $userIdHash);
                
                // Let's debug what hashes actually exist in the database
                $this->db->query('SELECT ID, User_Role, Acc_Status, User_ID_Hash FROM USER_INFORMATION WHERE User_Role IN ("superAdmin", "SubAdmin", "admin")');
                $admins = $this->db->resultSet();
                error_log("Admin users in database: " . count($admins));
                foreach ($admins as $admin) {
                    $shortHash = substr($admin->User_ID_Hash ?? '', 0, 16) . '...';
                    error_log("Admin - ID: " . $admin->ID . ", Role: " . $admin->User_Role . ", Status: " . $admin->Acc_Status . ", Hash: " . $shortHash);
                }
                
                return false;
            }
            
        } catch (Exception $e) {
            error_log("💥 Admin login error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

   

    /**
     * Student/Faculty login method - ONLY allows login by Email
     */
    public function loginByEmail($email, $password) {
        error_log("=== USER MODEL loginByEmail ===");
        
        $emailHash = $this->hashData($email);
        
        $this->db->query('SELECT * FROM USER_INFORMATION WHERE Email_Hash = :email_hash AND Acc_Status = "approved"');
        $this->db->bind(':email_hash', $emailHash);
        
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
            error_log("❌ No approved user found");
        }
        
        return false;
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
}
?>