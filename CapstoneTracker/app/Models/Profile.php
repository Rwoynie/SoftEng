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
            // Ensure userId is an integer
            $userId = (int)$userId;
            
            if ($userId <= 0) {
                error_log("Invalid user ID provided: " . $userId);
                return null;
            }
            
            error_log("Fetching profile for user ID: " . $userId);
            
            $sql = "SELECT 
                        ui.ID as user_id,
                        ui.Email as email,
                        ui.First_Name as first_name,
                        ui.Last_Name as last_name,
                        ui.Middle_Name as middle_name,
                        ui.Course as course,
                        ui.Student_ID as student_id,
                        ui.Employee_ID as employee_id,
                        ui.created_at as member_since,
                        ui.updated_at as last_login,
                        ui.Acc_Status as account_status,
                        ui.User_Role as role_name,
                        ui.Profile_Pic as profile_pic
                    FROM USER_INFORMATION ui
                    WHERE ui.ID = ?";
            
            $this->db->query($sql);
            $this->db->bind(1, $userId);
            
            $result = $this->db->singleAssoc();
            
            if ($result) {
                error_log("Profile found for user ID: " . $userId . " - Email: " . ($result['email'] ?? 'N/A') . ", Name: " . ($result['first_name'] ?? 'N/A') . " " . ($result['last_name'] ?? 'N/A'));
                // Format the data for display
                return $this->formatProfileData($result);
            } else {
                error_log("No profile found for user ID: " . $userId);
                return null;
            }
            
        } catch (Exception $e) {
            error_log("Error getting user profile: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Format profile data for display
     */
    private function formatProfileData($data) {
        $profilePic = $this->formatProfilePicture($data['profile_pic'] ?? null);
        
        $formatted = [
            'user_id' => $data['user_id'],
            'email' => $data['email'],
            'student_id' => $data['student_id'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'middle_name' => $data['middle_name'] ?? '',
            'FullName' => $this->getFullName($data),
            'course' => $data['course'] ?? 'Not specified',
            'member_since' => $this->formatDate($data['member_since']),
            'last_login' => $this->formatDate($data['last_login']),
            'acc_status' => $this->getStatusText($data['account_status']),
            'role' => $data['role_name'] ?? 'Student',
            'profile_pic' => $profilePic
        ];
        
        return $formatted;
    }
    
    /**
     * Format profile picture for display
     */
    private function formatProfilePicture($profilePicValue) {
        if ($profilePicValue === null || $profilePicValue === '') {
            return null; // No profile picture stored
        }

        // Try to treat as raw image (BLOB)
        if (is_string($profilePicValue)) {
            $imageInfo = @getimagesizefromstring($profilePicValue);
            if ($imageInfo !== false) {
                $mimeType = $imageInfo['mime'];
                $imageData = base64_encode($profilePicValue);
                return 'data:' . $mimeType . ';base64,' . $imageData;
            }

            // If not a valid binary image, assume it's a file path stored in DB
            $path = trim($profilePicValue);
            // If already absolute URL or absolute path, return as-is
            if (preg_match('/^https?:\/\//i', $path) === 1 || str_starts_with($path, '/')) {
                return $path;
            }

            // Normalize common stored paths
            // Examples seen: "uploads/profile_pictures/...", "resources/Uploads/...", or just filename
            if (stripos($path, 'uploads/') === 0 || stripos($path, 'resources/') === 0) {
                return '/CapstoneTracker/' . $path;
            }

            // Default to resources upload directory if only filename was stored
            return '/CapstoneTracker/resources/Uploads/' . $path;
        }

        return null;
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
            // Map form field names to database column names
            $fieldMapping = [
                'first_name' => 'First_Name',
                'last_name' => 'Last_Name',
                'middle_name' => 'Middle_Name',
                'email' => 'Email',
                'course' => 'Course',
                'profile_pic' => 'Profile_Pic',
                'pswrd' => 'pswrd',
                'salt' => 'Salt'
            ];
            
            $updateFields = [];
            $bindValues = [];
            $bindTypes = [];
            $paramIndex = 1;
            
            // Build update query with correct database column names
            foreach ($updateData as $formField => $value) {
                if (isset($fieldMapping[$formField])) {
                    $dbColumn = $fieldMapping[$formField];
                    $updateFields[] = "$dbColumn = ?";
                    if ($dbColumn === 'Profile_Pic') {
                        $bindValues[] = $value; // binary string
                        $bindTypes[] = PDO::PARAM_LOB;
                    } else {
                        $bindValues[] = is_string($value) ? trim($value) : $value;
                        $bindTypes[] = null; // let Database decide
                    }
                }
            }
            
            if (empty($updateFields)) {
                error_log("No valid fields to update");
                return false; // No valid fields to update
            }
            
            // Add user ID to bind values
            $bindValues[] = $userId;
            $bindTypes[] = PDO::PARAM_INT;
            
            $sql = "UPDATE USER_INFORMATION SET " . implode(', ', $updateFields) . " WHERE ID = ?";
            
            $this->db->query($sql);
            
            // Bind values
            foreach ($bindValues as $index => $value) {
                $type = $bindTypes[$index] ?? null;
                $this->db->bind($index + 1, $value, $type);
            }
            
            
            $result = $this->db->execute();
            
            if ($result) {
                error_log("Profile updated successfully for user ID: $userId");
            } else {
                error_log("Failed to execute profile update for user ID: $userId");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }
}