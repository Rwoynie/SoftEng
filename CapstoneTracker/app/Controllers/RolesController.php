<?php
// RolesController.php
ob_start();
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/RoleModel.php';

class RolesController {
    private $roleModel;
    private $error = null;

    public function __construct($database = null) {
        $this->roleModel = new RoleModel($database);
    }

    /**
     * Get role information for a specific user
     */
    public function getUserRole($userId) {
        $role = $this->roleModel->getRoleByUserId($userId);
        
        if ($role) {
            return $role;
        }
        
        // If no custom role exists, return default permissions
        return $this->getDefaultRoleByUserType($userId);
    }

    /**
     * Get default role permissions based on user type
     */
    private function getDefaultRoleByUserType($userId) {
        try {
            $db = $this->roleModel->getDb();
            $db->query("SELECT User_Role, Acc_Status FROM USER_INFORMATION WHERE ID = :user_id");
            $db->bind(':user_id', $userId);
            $user = $db->singleAssoc();
            
            if (!$user) {
                return false;
            }
            
            $defaultRole = [
                'User_ID' => $userId,
                'User_Role' => $user['User_Role'],
                'Acc_Status' => $user['Acc_Status']
            ];
            
            // Set default permissions based on user role
            switch ($user['User_Role']) {
                case 'superAdmin':
                case 'admin':
                    $defaultRole['Sub_Admin'] = 'Yes';
                    $defaultRole['Can_Edit'] = 'Yes';
                    $defaultRole['Manage_Access'] = 'Yes';
                    break;
                case 'faculty':
                    $defaultRole['Sub_Admin'] = 'No';
                    $defaultRole['Can_Edit'] = 'Yes';
                    $defaultRole['Manage_Access'] = 'No';
                    break;
                
                case 'student':
                default:
                    $defaultRole['Sub_Admin'] = 'No';
                    $defaultRole['Can_Edit'] = 'No';
                    $defaultRole['Manage_Access'] = 'No';
                    break;
            }
            
            return $defaultRole;
            
        } catch (Exception $e) {
            $this->error = "Error getting default role: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    

    /**
     * Create or update role permissions for a user
     */
    public function updateUserRole($userId, $subAdmin, $canEdit, $manageAccess) {
        $result = $this->roleModel->saveRole($userId, $subAdmin, $canEdit, $manageAccess);
        
        if (!$result) {
            $this->error = $this->roleModel->getError();
        }
        
        return $result;
    }

    /**
     * Check if user has sub-admin privileges
     */
    public function isSubAdmin($userId) {
        $hasPermission = $this->roleModel->hasPermission($userId, 'sub_admin');
        
        // If user has sub-admin permission but isn't marked as subAdmin in user role, update it
        if ($hasPermission) {
            try {
                $db = $this->roleModel->getDb();
                
                // Check current user role
                $db->query("SELECT User_Role FROM USER_INFORMATION WHERE ID = :user_id");
                $db->bind(':user_id', $userId);
                $user = $db->singleAssoc();
                
                // If user doesn't have subAdmin role, update it
                if ($user && $user['User_Role'] !== 'subAdmin') {
                    $db->query("UPDATE USER_INFORMATION SET User_Role = 'subAdmin' WHERE ID = :user_id");
                    $db->bind(':user_id', $userId);
                    $db->execute();
                    
                    // Update session if this is the current user
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
                        $_SESSION['user_role'] = 'subAdmin';
                    }
                }
            } catch (Exception $e) {
                error_log("Error updating user role to subAdmin: " . $e->getMessage());
                // Continue anyway since the permission check passed
            }
        }
        
        return $hasPermission;
    }

    /**
     * Check if user can edit content
     */
    public function canEdit($userId) {
        return $this->roleModel->hasPermission($userId, 'can_edit');
    }

    /**
     * Check if user can manage access
     */
    public function canManageAccess($userId) {
        return $this->roleModel->hasPermission($userId, 'manage_access');
    }

    /**
     * Check if user can access management features (hide for subAdmin)
     */
    public function canAccessManagement($userId) {
        // Get user's actual role from database
        try {
            $db = $this->roleModel->getDb();
            $db->query("SELECT User_Role FROM USER_INFORMATION WHERE ID = :user_id");
            $db->bind(':user_id', $userId);
            $user = $db->singleAssoc();
            
            // SubAdmin users cannot access management
            if ($user && $user['User_Role'] === 'subAdmin') {
                return false;
            }
            
            // For other users, use the existing permission check
            return $this->canManageAccess($userId);
            
        } catch (Exception $e) {
            error_log("Error checking management access: " . $e->getMessage());
            return $this->canManageAccess($userId);
        }
    }


    /**
     * Get all users with their role permissions
     */
    public function getAllUsersWithRoles($filters = []) {
        $users = $this->roleModel->getUsersWithRoles($filters);
        
        if ($users === false) {
            $this->error = $this->roleModel->getError();
            return false;
        }
        
        return $users;
    }

    /**
     * Get all data from ROLES table
     */
    public function getAllRolesData() {
        $rolesData = $this->roleModel->getAllRolesData();
        
        if ($rolesData === false) {
            $this->error = $this->roleModel->getError();
            return false;
        }
        
        return $rolesData;
    }

    /**
     * Get all users with complete role information
     */
    public function getAllUsersWithCompleteRoles() {
        $users = $this->roleModel->getAllUsersWithCompleteRoles();
        
        if ($users === false) {
            $this->error = $this->roleModel->getError();
            return false;
        }
        
        return $users;
    }

    /**
     * Update multiple users' roles in batch
     */
    public function updateBatchUserRoles($roleUpdates) {
        try {
            $db = $this->roleModel->getDb();
            $db->beginTransaction();

            foreach ($roleUpdates as $update) {
                $userId = $update['user_id'];
                $subAdmin = $update['sub_admin'] ?? 'No';
                $canEdit = $update['can_edit'] ?? 'No';
                $manageAccess = $update['manage_access'] ?? 'No';

                if (!$this->roleModel->saveRole($userId, $subAdmin, $canEdit, $manageAccess)) {
                    $db->rollBack();
                    $this->error = $this->roleModel->getError();
                    return false;
                }
            }

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            $this->error = "Error in batch role update: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    

    /**
     * Delete role record for a user (will revert to defaults)
     */
    public function deleteUserRole($userId) {
        $result = $this->roleModel->deleteRoleByUserId($userId);
        
        if (!$result) {
            $this->error = $this->roleModel->getError();
        }
        
        return $result;
    }

    /**
     * Get role statistics
     */
    public function getRoleStatistics() {
        $stats = $this->roleModel->getRoleStats();
        
        if (!$stats) {
            $this->error = $this->roleModel->getError();
            return false;
        }
        
        return $stats;
    }

    /**
     * Get error message
     */
    public function getError() {
        return $this->error ?: $this->roleModel->getError();
    }

    /**
     * Validate if current user can modify roles (for authorization)
     */
    public function canModifyRoles($currentUserId) {
        return $this->canAccessManagement($currentUserId);
    }
}

// Handle AJAX requests if accessed directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $rolesController = new RolesController();
    $response = ['success' => false, 'message' => ''];
    
    $csrfValid = true;
    if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
        $csrfValid = ($_POST['csrf_token'] === $_SESSION['csrf_token']);
    }
    
    if (!$csrfValid) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'get_user_roles':
            $filters = $_POST['filters'] ?? [];
            $users = $rolesController->getAllUsersWithRoles($filters);
            if ($users !== false) {
                $response['success'] = true;
                $response['users'] = $users;
            } else {
                $response['message'] = $rolesController->getError();
            }
            break;
            
        case 'update_user_role':
            if (isset($_POST['user_id'], $_POST['sub_admin'], $_POST['can_edit'], $_POST['manage_access'])) {
                $success = $rolesController->updateUserRole(
                    $_POST['user_id'],
                    $_POST['sub_admin'],
                    $_POST['can_edit'],
                    $_POST['manage_access']
                );
                if ($success) {
                    $response['success'] = true;
                    $response['message'] = 'Role updated successfully';
                } else {
                    $response['message'] = $rolesController->getError();
                }
            } else {
                $response['message'] = 'Missing required parameters';
            }
            break;
            
        case 'update_batch_roles':
            if (isset($_POST['role_updates']) && is_array($_POST['role_updates'])) {
                $success = $rolesController->updateBatchUserRoles($_POST['role_updates']);
                if ($success) {
                    $response['success'] = true;
                    $response['message'] = 'Roles updated successfully';
                } else {
                    $response['message'] = $rolesController->getError();
                }
            } else {
                $response['message'] = 'Invalid role updates data';
            }
            break;
            case 'get_all_roles_data':
                $rolesData = $rolesController->getAllRolesData();
                if ($rolesData !== false) {
                    $response['success'] = true;
                    $response['roles_data'] = $rolesData;
                } else {
                    $response['message'] = $rolesController->getError();
                }
                break;
            
            case 'get_all_users_complete_roles':
            $users = $rolesController->getAllUsersWithCompleteRoles();
            if ($users !== false) {
                $response['success'] = true;
                $response['users'] = $users;
                // Add debug info
                $response['debug'] = [
                    'user_count' => count($users),
                    'query_executed' => true
                ];
            } else {
                $response['message'] = $rolesController->getError();
            }
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
    
    echo json_encode($response);
    exit;
}
?>