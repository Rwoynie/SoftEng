<?php
class User extends Model {
    protected $tableName = 'USER_INFORMATION'; // Set table name
    
    /**
     * User registration method
     */
    public function register($data) {
        try {
            // Generate salt and hash password
            $salt = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($data['password'] . $salt, PASSWORD_DEFAULT);
            
            // Build the SQL query based on available data
            $fields = [];
            $values = [];
            $bindings = [];
            
            // Required fields
            $fields[] = 'pswrd';
            $values[] = ':password';
            $bindings[':password'] = $hashedPassword;
            
            $fields[] = 'Salt';
            $values[] = ':salt';
            $bindings[':salt'] = $salt;
            
            $fields[] = 'First_Name';
            $values[] = ':first_name';
            $bindings[':first_name'] = $data['first_name'] ?? '';
            
            $fields[] = 'Last_Name';
            $values[] = ':last_name';
            $bindings[':last_name'] = $data['last_name'] ?? '';
            
            $fields[] = 'Email';
            $values[] = ':email';
            $bindings[':email'] = $data['email'] ?? '';
            
            $fields[] = 'User_ID';
            $values[] = ':user_id';
            $bindings[':user_id'] = $data['student_id'] ?? $data['employee_id'] ?? '';
            
            $fields[] = 'User_Role';
            $values[] = ':user_role';
            $bindings[':user_role'] = $data['user_role'] ?? 'student';
            
            $fields[] = 'Acc_Status';
            $values[] = ':acc_status';
            $bindings[':acc_status'] = $data['acc_status'] ?? 'pending';
            
            // Optional fields
            if (!empty($data['middle_name'])) {
                $fields[] = 'Middle_Name';
                $values[] = ':middle_name';
                $bindings[':middle_name'] = $data['middle_name'];
            }
            
            if (!empty($data['extension'])) {
                $fields[] = 'Extension';
                $values[] = ':extension';
                $bindings[':extension'] = $data['extension'];
            }
            
            if (!empty($data['student_id'])) {
                $fields[] = 'Student_ID';
                $values[] = ':student_id';
                $bindings[':student_id'] = $data['student_id'];
            }
            
            // Additional fields for your system
            if (!empty($data['year_level'])) {
                $fields[] = 'Year_Level';
                $values[] = ':year_level';
                $bindings[':year_level'] = $data['year_level'];
            }
            
            if (!empty($data['course'])) {
                $fields[] = 'Course';
                $values[] = ':course';
                $bindings[':course'] = $data['course'];
            }
            
            if (!empty($data['department'])) {
                $fields[] = 'Department';
                $values[] = ':department';
                $bindings[':department'] = $data['department'];
            }
            
            if (!empty($data['designation'])) {
                $fields[] = 'Designation';
                $values[] = ':designation';
                $bindings[':designation'] = $data['designation'];
            }
            
            if (!empty($data['profile_pic'])) {
                $fields[] = 'Profile_Pic';
                $values[] = ':profile_pic';
                $bindings[':profile_pic'] = $data['profile_pic'];
            }
            
            // Build the final query
            $sql = 'INSERT INTO USER_INFORMATION (' . implode(', ', $fields) . ') 
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
            return false;
        }
    }
    
    /**
     * User login method
     */
    public function login($identifier, $password) {
        try {
            // Allow login by email or user_id
            $this->db->query('SELECT * FROM USER_INFORMATION WHERE (Email = :identifier OR User_ID = :identifier OR Student_ID = :identifier) AND Acc_Status = "approved"');
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
    // Other user-specific methods can be added here
}
?>