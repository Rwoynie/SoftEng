<?php
class User extends Model {
    protected $tableName = 'users'; // Set table name
    
    // User-specific methods
    public function register($data) {
        $salt = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $this->db->query('INSERT INTO USER_INFORMATION 
            (pswrd, Salt, First_Name, Middle_Name, Last_Name, Extension, Email, Student_ID, User_Role) 
            VALUES (:password, :salt, :first_name, :middle_name, :last_name, :extension, :email, :student_id, )');
        
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':salt', $salt);
        // ... bind other parameters
    }
    
    public function login($email, $password) {
        // Can use parent's database connection
        $this->db->query('SELECT * FROM users ...');
        // ... user-specific logic
    }
}
?>