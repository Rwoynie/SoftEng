<?php

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
            $sql = "SELECT 
                        u.id as user_id,
                        u.email,
                        u.first_name,
                        u.last_name,
                        u.middle_name,
                        u.course,
                        u.created_at as member_since,
                        u.last_login,
                        u.status as account_status,
                        r.name as role_name
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    WHERE u.id = ?";
            
            $this->db->query($sql);
            $this->db->bind(1, $userId);
            
            $result = $this->db->singleAssoc();
            
            if ($result) {
                // Format the data for display
                return $this->formatProfileData($result);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error getting user profile: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Format profile data for display
     */
    private function formatProfileData($data) {
        $formatted = [
            'user_id' => $data['user_id'],
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'middle_name' => $data['middle_name'] ?? '',
            'FullName' => $this->getFullName($data),
            'course' => $data['course'] ?? 'Not specified',
            'member_since' => $this->formatDate($data['member_since']),
            'last_login' => $this->formatDate($data['last_login']),
            'acc_status' => $this->getStatusText($data['account_status']),
            'role' => $data['role_name'] ?? 'Student'
        ];
        
        return $formatted;
    }
    
    /**
     * Get full name from first, middle, and last names
     */
    private function getFullName($data) {
        $firstName = $data['first_name'] ?? '';
        $middleName = $data['middle_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        
        $fullName = $firstName;
        if (!empty($middleName)) {
            $fullName .= ' ' . $middleName;
        }
        if (!empty($lastName)) {
            $fullName .= ' ' . $lastName;
        }
        
        return !empty(trim($fullName)) ? trim($fullName) : 'Unknown User';
    }
    
    /**
     * Format date for display
     */
    private function formatDate($dateString) {
        if (empty($dateString) || $dateString === '0000-00-00 00:00:00') {
            return 'Not available';
        }
        
        try {
            $date = new DateTime($dateString);
            return $date->format('F j, Y');
        } catch (Exception $e) {
            return 'Invalid date';
        }
    }
    
    /**
     * Get status text for display
     */
    private function getStatusText($status) {
        $statusMap = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending',
            'suspended' => 'Suspended'
        ];
        
        $statusKey = strtolower($status);
        return $statusMap[$statusKey] ?? ucfirst($status);
    }
    
    /**
     * Update user profile
     */
    public function updateUserProfile($userId, $updateData) {
        try {
            // Build the update query dynamically based on provided fields
            $allowedFields = ['first_name', 'last_name', 'middle_name', 'email', 'course'];
            $updateFields = [];
            $bindValues = [];
            
            foreach ($allowedFields as $field) {
                if (isset($updateData[$field])) {
                    $updateFields[] = "$field = ?";
                    $bindValues[] = $updateData[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false; // No valid fields to update
            }
            
            // Add user ID to bind values
            $bindValues[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            $this->db->query($sql);
            
            // Bind values
            foreach ($bindValues as $index => $value) {
                $this->db->bind($index + 1, $value);
            }
            
            return $this->db->execute();
            
        } catch (Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }
}
?>