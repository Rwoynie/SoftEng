<?php

require_once 'Model.php';

class User extends Model {
    protected $tableName = 'USER_INFORMATION'; // Set table name
    
    /**
     * Get database instance for external use
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * User registration method - IMPROVED VERSION
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
            $fields = ['pswrd', 'Salt', 'First_Name', 'Last_Name', 'Email', 'User_ID', 'User_Role', 'Acc_Status'];
            $values = [':password', ':salt', ':first_name', ':last_name', ':email', ':user_id', ':user_role', ':acc_status'];
            $bindings = [
                ':password' => $hashedPassword,
                ':salt' => $salt,
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':email' => $data['email'] ?? '',
                ':user_id' => $userId,
                ':user_role' => $userRole,
                ':acc_status' => $data['acc_status'] ?? 'pending'
            ];
            
            // Optional fields
            $optionalFields = [
                'middle_name' => 'Middle_Name',
                'extension' => 'Extension',
                'year_level' => 'Year_Level',
                'course' => 'Course',
                'department' => 'Department',
                'designation' => 'Designation',
                'profile_pic' => 'Profile_Pic'
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
            return $this->db->execute();
            
        } catch (Exception $e) {
            error_log("User registration error: " . $e->getMessage());
            throw $e; // Re-throw to let controller handle it
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
}
?>
?>