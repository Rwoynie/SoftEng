<?php
// RoleModel.php

require_once __DIR__ . '/Database.php';

class RoleModel {
    private $db;
    private $error = null;

    public function __construct($database = null) {
        if ($database) {
            $this->db = $database;
        } else {
            $this->db = new Database();
        }
    }

    /**
     * Get role record by User_ID
     */
    public function getRoleByUserId($userId) {
        try {
            $this->db->query("
                SELECT r.*, ui.User_Role, ui.Acc_Status 
                FROM ROLES r 
                JOIN USER_INFORMATION ui ON r.User_ID = ui.ID 
                WHERE r.User_ID = :user_id
            ");
            $this->db->bind(':user_id', $userId);
            
            $result = $this->db->singleAssoc();
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->error = "Error getting role by user ID: " . $e->getMessage();
            error_log($this->error);
            return null;
        }
    }

    /**
     * Get role record by Role_ID
     */
    public function getRoleById($roleId) {
        try {
            $this->db->query("
                SELECT r.*, ui.User_Role, ui.Acc_Status 
                FROM ROLES r 
                JOIN USER_INFORMATION ui ON r.User_ID = ui.ID 
                WHERE r.Role_ID = :role_id
            ");
            $this->db->bind(':role_id', $roleId);
            
            $result = $this->db->singleAssoc();
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->error = "Error getting role by ID: " . $e->getMessage();
            error_log($this->error);
            return null;
        }
    }

    /**
     * Create a new role record
     */
    public function createRole($userId, $subAdmin, $canEdit, $manageAccess) {
        try {
            // Validate input values
            if (!$this->validateRoleValues($subAdmin, $canEdit, $manageAccess)) {
                $this->error = "Invalid role values provided";
                return false;
            }

            // Check if user exists
            if (!$this->userExists($userId)) {
                $this->error = "User does not exist";
                return false;
            }

            // Check if role already exists
            if ($this->getRoleByUserId($userId)) {
                $this->error = "Role already exists for this user";
                return false;
            }

            $this->db->query("
                INSERT INTO ROLES (User_ID, Sub_Admin, Can_Edit, Manage_Access) 
                VALUES (:user_id, :sub_admin, :can_edit, :manage_access)
            ");

            $this->db->bind(':user_id', $userId);
            $this->db->bind(':sub_admin', $subAdmin);
            $this->db->bind(':can_edit', $canEdit);
            $this->db->bind(':manage_access', $manageAccess);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                $this->error = "Failed to create role record";
                return false;
            }

        } catch (Exception $e) {
            $this->error = "Error creating role: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Update an existing role record
     */
    public function updateRole($userId, $subAdmin, $canEdit, $manageAccess) {
        try {
            // Validate input values
            if (!$this->validateRoleValues($subAdmin, $canEdit, $manageAccess)) {
                $this->error = "Invalid role values provided";
                return false;
            }

            $this->db->query("
                UPDATE ROLES 
                SET Sub_Admin = :sub_admin, Can_Edit = :can_edit, Manage_Access = :manage_access 
                WHERE User_ID = :user_id
            ");

            $this->db->bind(':user_id', $userId);
            $this->db->bind(':sub_admin', $subAdmin);
            $this->db->bind(':can_edit', $canEdit);
            $this->db->bind(':manage_access', $manageAccess);

            if ($this->db->execute()) {
                return $this->db->rowCount() > 0;
            } else {
                $this->error = "Failed to update role record";
                return false;
            }

        } catch (Exception $e) {
            $this->error = "Error updating role: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Create or update role (upsert operation)
     */
    public function saveRole($userId, $subAdmin, $canEdit, $manageAccess) {
        $existingRole = $this->getRoleByUserId($userId);
        
        if ($existingRole) {
            return $this->updateRole($userId, $subAdmin, $canEdit, $manageAccess);
        } else {
            return $this->createRole($userId, $subAdmin, $canEdit, $manageAccess);
        }
    }

    /**
     * Delete role record by User_ID
     */
    public function deleteRoleByUserId($userId) {
        try {
            $this->db->query("DELETE FROM ROLES WHERE User_ID = :user_id");
            $this->db->bind(':user_id', $userId);
            
            if ($this->db->execute()) {
                return $this->db->rowCount() > 0;
            } else {
                $this->error = "Failed to delete role record";
                return false;
            }

        } catch (Exception $e) {
            $this->error = "Error deleting role: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Delete role record by Role_ID
     */
    public function deleteRoleById($roleId) {
        try {
            $this->db->query("DELETE FROM ROLES WHERE Role_ID = :role_id");
            $this->db->bind(':role_id', $roleId);
            
            if ($this->db->execute()) {
                return $this->db->rowCount() > 0;
            } else {
                $this->error = "Failed to delete role record";
                return false;
            }

        } catch (Exception $e) {
            $this->error = "Error deleting role: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get all role records with user information
     */
    public function getAllRoles($filters = []) {
        try {
            $baseQuery = "
                SELECT 
                    r.Role_ID,
                    r.User_ID,
                    r.Sub_Admin,
                    r.Can_Edit,
                    r.Manage_Access,
                    ui.First_Name,
                    ui.Middle_Name,
                    ui.Last_Name,
                    ui.Extension,
                    ui.Email,
                    ui.User_Role,
                    ui.Acc_Status,
                    ui.Department,
                    ui.Course,
                    ui.created_at
                FROM ROLES r
                JOIN USER_INFORMATION ui ON r.User_ID = ui.ID
                WHERE 1=1
            ";

            $params = [];

            // Apply filters
            if (!empty($filters['user_role'])) {
                $baseQuery .= " AND ui.User_Role = :user_role";
                $params[':user_role'] = $filters['user_role'];
            }

            if (!empty($filters['acc_status'])) {
                $baseQuery .= " AND ui.Acc_Status = :acc_status";
                $params[':acc_status'] = $filters['acc_status'];
            }

            if (!empty($filters['sub_admin'])) {
                $baseQuery .= " AND r.Sub_Admin = :sub_admin";
                $params[':sub_admin'] = $filters['sub_admin'];
            }

            if (!empty($filters['can_edit'])) {
                $baseQuery .= " AND r.Can_Edit = :can_edit";
                $params[':can_edit'] = $filters['can_edit'];
            }

            if (!empty($filters['manage_access'])) {
                $baseQuery .= " AND r.Manage_Access = :manage_access";
                $params[':manage_access'] = $filters['manage_access'];
            }

            if (!empty($filters['search'])) {
                $baseQuery .= " AND (ui.First_Name LIKE :search OR ui.Last_Name LIKE :search OR ui.Email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            // Add ordering
            $baseQuery .= " ORDER BY ui.created_at DESC";

            $this->db->query($baseQuery);
            
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }

            $results = $this->db->resultSetAssoc();
            return $results;

        } catch (Exception $e) {
            $this->error = "Error getting all roles: " . $e->getMessage();
            error_log($this->error);
            return [];
        }
    }

    /**
     * Get users with their roles (including users without custom roles)
     */
    public function getUsersWithRoles($filters = []) {
        try {
            $baseQuery = "
                SELECT 
                    ui.ID as User_ID,
                    ui.First_Name, 
                    ui.Middle_Name, 
                    ui.Last_Name, 
                    ui.Extension,
                    ui.Email,
                    ui.User_Role,
                    ui.Acc_Status,
                    ui.Department,
                    ui.Course,
                    ui.created_at,
                    r.Role_ID,
                    COALESCE(r.Sub_Admin, 'No') as Sub_Admin,
                    COALESCE(r.Can_Edit, 
                        CASE 
                            WHEN ui.User_Role IN ('admin', 'superAdmin', 'faculty') THEN 'Yes' 
                            ELSE 'No' 
                        END
                    ) as Can_Edit,
                    COALESCE(r.Manage_Access, 
                        CASE 
                            WHEN ui.User_Role IN ('admin', 'superAdmin') THEN 'Yes' 
                            ELSE 'No' 
                        END
                    ) as Manage_Access
                FROM USER_INFORMATION ui
                LEFT JOIN ROLES r ON ui.ID = r.User_ID
                WHERE 1=1
            ";

            $params = [];

            // Apply filters
            if (!empty($filters['user_role'])) {
                $baseQuery .= " AND ui.User_Role = :user_role";
                $params[':user_role'] = $filters['user_role'];
            }

            if (!empty($filters['acc_status'])) {
                $baseQuery .= " AND ui.Acc_Status = :acc_status";
                $params[':acc_status'] = $filters['acc_status'];
            }

            if (!empty($filters['has_custom_role'])) {
                if ($filters['has_custom_role'] === 'yes') {
                    $baseQuery .= " AND r.Role_ID IS NOT NULL";
                } elseif ($filters['has_custom_role'] === 'no') {
                    $baseQuery .= " AND r.Role_ID IS NULL";
                }
            }

            if (!empty($filters['search'])) {
                $baseQuery .= " AND (ui.First_Name LIKE :search OR ui.Last_Name LIKE :search OR ui.Email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            $baseQuery .= " ORDER BY ui.created_at DESC";

            $this->db->query($baseQuery);
            
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }

            $results = $this->db->resultSetAssoc();
            return $results;

        } catch (Exception $e) {
            $this->error = "Error getting users with roles: " . $e->getMessage();
            error_log($this->error);
            return [];
        }
    }

    /**
     * Get role statistics
     */
    public function getRoleStats() {
        try {
            $stats = [];

            // Total custom roles
            $this->db->query("SELECT COUNT(*) as total_custom_roles FROM ROLES");
            $stats['total_custom_roles'] = $this->db->singleAssoc()['total_custom_roles'];

            // Sub-admin count
            $this->db->query("SELECT COUNT(*) as sub_admin_count FROM ROLES WHERE Sub_Admin = 'Yes'");
            $stats['sub_admin_count'] = $this->db->singleAssoc()['sub_admin_count'];

            // Can edit count
            $this->db->query("SELECT COUNT(*) as can_edit_count FROM ROLES WHERE Can_Edit = 'Yes'");
            $stats['can_edit_count'] = $this->db->singleAssoc()['can_edit_count'];

            // Manage access count
            $this->db->query("SELECT COUNT(*) as manage_access_count FROM ROLES WHERE Manage_Access = 'Yes'");
            $stats['manage_access_count'] = $this->db->singleAssoc()['manage_access_count'];

            // Roles by user type
            $this->db->query("
                SELECT 
                    ui.User_Role,
                    COUNT(r.Role_ID) as role_count
                FROM ROLES r
                JOIN USER_INFORMATION ui ON r.User_ID = ui.ID
                GROUP BY ui.User_Role
            ");
            $stats['roles_by_user_type'] = $this->db->resultSetAssoc();

            return $stats;

        } catch (Exception $e) {
            $this->error = "Error getting role statistics: " . $e->getMessage();
            error_log($this->error);
            return [];
        }
    }

    /**
     * Check if a user has a specific permission
     */
    public function hasPermission($userId, $permissionType) {
        try {
            $role = $this->getRoleByUserId($userId);
            
            if (!$role) {
                // Return default permissions based on user role
                return $this->getDefaultPermission($userId, $permissionType);
            }

            switch ($permissionType) {
                case 'sub_admin':
                    return $role['Sub_Admin'] === 'Yes';
                case 'can_edit':
                    return $role['Can_Edit'] === 'Yes';
                case 'manage_access':
                    return $role['Manage_Access'] === 'Yes';
                default:
                    return false;
            }

        } catch (Exception $e) {
            $this->error = "Error checking permission: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get default permission based on user role
     */
    private function getDefaultPermission($userId, $permissionType) {
        try {
            $this->db->query("SELECT User_Role FROM USER_INFORMATION WHERE ID = :user_id");
            $this->db->bind(':user_id', $userId);
            $user = $this->db->singleAssoc();
            
            if (!$user) {
                return false;
            }

            switch ($user['User_Role']) {
                case 'superAdmin':
                case 'admin':
                    return true; // Admins have all permissions by default
                case 'faculty':
                    return $permissionType === 'can_edit'; // Faculty can only edit by default
                case 'student':
                default:
                    return false; // Students have no permissions by default
            }

        } catch (Exception $e) {
            $this->error = "Error getting default permission: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get complete user data with roles (including users without custom roles)
     */
    public function getAllUsersWithCompleteRoles() {
        try {
            $this->db->query("
                SELECT 
                    ui.ID as User_ID,
                    ui.First_Name, 
                    ui.Middle_Name, 
                    ui.Last_Name, 
                    ui.Extension,
                    ui.Email,
                    ui.User_Role,
                    ui.Acc_Status,
                    ui.Department,
                    ui.Course,
                    ui.created_at,
                    r.Role_ID,
                    r.Sub_Admin,
                    r.Can_Edit,
                    r.Manage_Access,
                    r.created_at as role_created_at,
                    r.updated_at as role_updated_at
                FROM USER_INFORMATION ui
                LEFT JOIN ROLES r ON ui.ID = r.User_ID
                ORDER BY ui.created_at DESC
            ");
            
            $results = $this->db->resultSetAssoc();
            return $results;
            
        } catch (Exception $e) {
            $this->error = "Error getting all users with complete roles: " . $e->getMessage();
            error_log($this->error);
            return [];
        }
    }


    public function getAllRolesData() {
        try {
            $this->db->query("
                SELECT 
                    r.*,
                    ui.First_Name,
                    ui.Middle_Name,
                    ui.Last_Name,
                    ui.Extension,
                    ui.Email,
                    ui.User_Role,
                    ui.Acc_Status,
                    ui.Department,
                    ui.Course,
                    ui.created_at as user_created_at
                FROM ROLES r
                JOIN USER_INFORMATION ui ON r.User_ID = ui.ID
                ORDER BY r.Role_ID DESC
            ");
            
            $results = $this->db->resultSetAssoc();
            return $results;
            
        } catch (Exception $e) {
            $this->error = "Error getting all roles data: " . $e->getMessage();
            error_log($this->error);
            return [];
        }
    }

    
    /**
     * Validate role values
     */
    private function validateRoleValues($subAdmin, $canEdit, $manageAccess) {
        $validValues = ['Yes', 'No'];
        return in_array($subAdmin, $validValues) && 
               in_array($canEdit, $validValues) && 
               in_array($manageAccess, $validValues);
    }

    /**
     * Check if user exists
     */
    private function userExists($userId) {
        try {
            $this->db->query("SELECT ID FROM USER_INFORMATION WHERE ID = :user_id");
            $this->db->bind(':user_id', $userId);
            $result = $this->db->singleAssoc();
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Get the database instance (for transactions)
     */
    public function getDb() {
        return $this->db;
    }
}
?>