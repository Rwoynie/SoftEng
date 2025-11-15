<?php
// AdminDashboardController.php
require_once __DIR__ . '/../Models/AdminDashboardModel.php';
require_once __DIR__ . '/../Models/Model.php';

$vendorAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    $vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($vendorAutoload)) {
        require_once $vendorAutoload;
    } else {
        error_log("Dompdf not found. Please install via composer: composer require dompdf/dompdf");
    }
}

use Dompdf\Dompdf;
use Dompdf\Options;

class AdminDashboardController {
     private $model;
    private $currentUser;
    private $modelMain;

    public function __construct() {
        $this->model = new AdminDashboardModel();
        $this->modelMain = new Model();
        $this->checkAdminAccess();
    }

    /**
     * Get users data for direct PHP usage (not AJAX)
     */
    public function getUsersData() {
        $users = $this->model->getAllUsers();
        $roleCounts = $this->model->getUserCountByRole();
        $statusCounts = $this->model->getUserCountByStatus();

        return [
            'users' => $users,
            'role_counts' => $roleCounts,
            'status_counts' => $statusCounts
        ];
    }

    /**
     * Check if user has admin access
     */
    private function checkAdminAccess() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || 
            !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            header('Location: ../User/indexLogin.php');
            exit();
        }

        $this->currentUser = [
            'user_name' => $_SESSION['user_name'] ?? '',
            'user_role' => $_SESSION['user_role'] ?? '',
            'user_db_id' => $_SESSION['user_db_id'] ?? ''
        ];
    }


    /**
     * Handle different actions
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? 'dashboard';
    
        switch ($action) {
            case 'getUsers':
                $this->getUsers();
                break;
            case 'getTheses':
                $this->getTheses();
                break;
            case 'updateUserRole':
                $this->updateUserRole();
                break;
            case 'updateUserStatus':
                $this->updateUserStatus();
                break;
            case 'deleteUser':
                $this->deleteUser();
                break;
            case 'getStatistics':
                $this->getStatistics();
                break;
            case 'getActivityLogs':
                $this->getActivityLogs();
                break;
            case 'searchTheses':
                $this->searchTheses();
                break;
            case 'checkUserRoles':
                $this->checkUserRoles();
                break;
            case 'getActiveAnnouncements':
                $this->getActiveAnnouncements();
                break;
            case 'getArchivedAnnouncements':
                $this->getArchivedAnnouncements();
                break;
            case 'getAnnouncement':
                $this->getAnnouncement();
                break;
            case 'createAnnouncement':
                $this->createAnnouncement();
                break;
            case 'updateAnnouncement':
                $this->updateAnnouncement();
                break;
            case 'saveAnnouncementDraft':
                $this->saveAnnouncementDraft();
                break;
            case 'archiveAnnouncement':
                $this->archiveAnnouncement();
                break;
            case 'restoreAnnouncement':
                $this->restoreAnnouncement();
                break;
            case 'deleteAnnouncement':
                $this->deleteAnnouncement();
                break;
            case 'togglePinAnnouncement':
                $this->togglePinAnnouncement();
                break;
            case 'logout':
                $this->logout();
                break;

                case 'debugLogs':
                $this->debugLogs();
                break;

            case 'getAuditLogs':
                $this->getAuditLogs();
                break;
            case 'getLoginAttempts':
                $this->getLoginAttempts();
                break;
            case 'getSecurityAlerts':
                $this->getSecurityAlerts();
                break;
            case 'getNotifications':
                $this->getNotifications();
                break;
            case 'markNotificationRead':
                $this->markNotificationRead();
                break;

            case 'testAuditQuery':
                $this->testAuditQuery();
                break;
            case 'getReports':
                $this->getReports();
                break;
            
            case 'generateReport':
                $this->generateReport();
                break;

          


           




            case 'dashboard':
            default:
                $this->showDashboard();
                break;

        }
    }


    /**
 * Debug logs - test if data exists
 */
private function debugLogs() {
    header('Content-Type: application/json');
    
    try {
        $debugInfo = [];
        
        // Check if audit_logs table exists and has data
        try {
            $this->model->getDatabase()->query("SELECT COUNT(*) as count FROM AUDIT_LOGS");
            $auditCount = $this->model->getDatabase()->single();
            $debugInfo['audit_logs_count'] = $auditCount->count;
            
            // Get sample data from audit_logs
            $this->model->getDatabase()->query("SELECT * FROM AUDIT_LOGS ORDER BY changed_at DESC LIMIT 5");
            $sampleAuditLogs = $this->model->getDatabase()->resultSet();
            $debugInfo['sample_audit_logs'] = $sampleAuditLogs;
        } catch (Exception $e) {
            $debugInfo['audit_logs_error'] = $e->getMessage();
            $debugInfo['audit_logs_count'] = 0;
            $debugInfo['sample_audit_logs'] = [];
        }
        
        // Check if login_attempts table exists and has data
        try {
            $this->model->getDatabase()->query("SELECT COUNT(*) as count FROM LOGIN_ATTEMPTS");
            $loginCount = $this->model->getDatabase()->single();
            $debugInfo['login_attempts_count'] = $loginCount->count;
            
            // Get sample data from login_attempts
            $this->model->getDatabase()->query("SELECT * FROM LOGIN_ATTEMPTS ORDER BY attempt_time DESC LIMIT 5");
            $sampleLoginAttempts = $this->model->getDatabase()->resultSet();
            $debugInfo['sample_login_attempts'] = $sampleLoginAttempts;
        } catch (Exception $e) {
            $debugInfo['login_attempts_error'] = $e->getMessage();
            $debugInfo['login_attempts_count'] = 0;
            $debugInfo['sample_login_attempts'] = [];
        }
        
        // Also check what tables actually exist in the database
        try {
            $this->model->getDatabase()->query("SHOW TABLES");
            $tables = $this->model->getDatabase()->resultSet();
            $debugInfo['all_tables'] = $tables;
        } catch (Exception $e) {
            $debugInfo['tables_error'] = $e->getMessage();
        }
        
        $this->jsonResponse([
            'success' => true,
            'debug_info' => $debugInfo,
            'message' => 'Debug information retrieved successfully'
        ]);
        
    } catch (Exception $e) {
        $this->jsonResponse([
            'success' => false,
            'error' => $e->getMessage(),
            'debug_info' => ['error' => $e->getMessage()]
        ]);
    }
}

    /**
     * Show main dashboard
     */
    private function showDashboard() {
        $data = [
            'user' => $this->currentUser,
            'stats' => $this->model->getSystemStatistics(),
            'recent_activity' => $this->model->getRecentActivity(10)
        ];

        $this->jsonResponse($data);
    }

    /**
     * Get users data
     */
    private function getUsers() {
        $role = $_GET['role'] ?? null;
        $status = $_GET['status'] ?? null;

        if ($role) {
            $users = $this->model->getUsersByRole($role);
        } elseif ($status) {
            $users = $this->model->getUsersByStatus($status);
        } else {
            $users = $this->model->getAllUsers();
        }

        $roleCounts = $this->model->getUserCountByRole();
        $statusCounts = $this->model->getUserCountByStatus();

        $this->jsonResponse([
            'users' => $users,
            'role_counts' => $roleCounts,
            'status_counts' => $statusCounts
        ]);
    }

    /**
     * Get theses data
     */
    private function getTheses() {
        $department = $_GET['department'] ?? null;
        $search = $_GET['search'] ?? null;
        $type = $_GET['type'] ?? 'all';

        if ($search) {
            $theses = $this->model->searchTheses($search);
        } elseif ($department && $department !== 'all') {
            $theses = $this->model->getThesesByDepartment($department);
        } elseif ($type === 'recent') {
            $theses = $this->model->getRecentTheses();
        } else {
            $theses = $this->model->getAllTheses();
        }

        $this->jsonResponse(['theses' => $theses]);
    }

    /**
     * Update user role
     */
    private function updateUserRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? null;
        $newRole = $data['role'] ?? null;

        if (!$userId || !$newRole) {
            $this->jsonResponse(['error' => 'Missing required parameters'], 400);
            return;
        }

        $validRoles = ['student', 'faculty', 'admin', 'superAdmin'];
        if (!in_array($newRole, $validRoles)) {
            $this->jsonResponse(['error' => 'Invalid role'], 400);
            return;
        }

        // Set current user ID for audit trigger
        $this->setCurrentUserForAudit();

        $success = $this->model->updateUserRole($userId, $newRole);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'User role updated successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update user role'], 500);
        }
    }

    /**
     * Update user account status
     */
    private function updateUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? null;
        $newStatus = $data['status'] ?? null;

        if (!$userId || !$newStatus) {
            $this->jsonResponse(['error' => 'Missing required parameters'], 400);
            return;
        }

        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->jsonResponse(['error' => 'Invalid status'], 400);
            return;
        }

        // Set current user ID for audit trigger
        $this->setCurrentUserForAudit();

        $success = $this->model->updateUserStatus($userId, $newStatus);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'User status updated successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update user status'], 500);
        }
    }

    /**
     * Delete user account
     */
    private function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        $csrfToken = $_POST['csrf_token'] ?? '';

        if (!$userId) {
            $this->jsonResponse(['error' => 'Missing user ID'], 400);
            return;
        }
    
        // Validate CSRF token
        if (!$this->validateCsrfToken($csrfToken)) {
            $this->jsonResponse(['error' => 'Invalid CSRF token'], 400);
            return;
        }

        // Prevent admin from deleting their own account
        if ($userId == $this->currentUser['user_db_id']) {
            $this->jsonResponse(['error' => 'Cannot delete your own account'], 400);
            return;
        }

        $success = $this->model->deleteUser($userId);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to delete user'], 500);
        }
    }

    

    /**
     * Get system statistics
     */
    private function getStatistics() {
        $stats = $this->model->getSystemStatistics();
        $this->jsonResponse(['statistics' => $stats]);
    }

    /**
     * Get activity logs
     */
    private function getActivityLogs() {
        $limit = $_GET['limit'] ?? 20;
        $activity = $this->model->getRecentActivity($limit);
        $this->jsonResponse(['activity' => $activity]);
    }

    /**
     * Search theses
     */
    private function searchTheses() {
        $searchTerm = $_GET['q'] ?? '';
        
        if (empty($searchTerm)) {
            $this->jsonResponse(['theses' => []]);
            return;
        }

        $theses = $this->model->searchTheses($searchTerm);
        $this->jsonResponse(['theses' => $theses]);
    }

    /**
     * Admin logout
     */
    private function logout() {
        session_start();
        session_unset();
        session_destroy();
        
        $this->jsonResponse(['success' => true, 'redirect' => '../User/indexLogin.php']);
    }

    /**
     * Check user roles for validation (for upload form)
     */
    private function checkUserRoles() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 400);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $emails = $input['emails'] ?? [];
           
            $facultyUsers = [];
            $nonFacultyUsers = [];
            $userRoles = [];
            
            foreach ($emails as $email) {
                $cleanEmail = trim($email);
                if (empty($cleanEmail)) continue;
                
                $user = $this->modelMain->findByEmail($cleanEmail);
                if ($user) {
                    $userRoles[$cleanEmail] = $user->User_Role;
                    
                    if ($user->User_Role === 'faculty') {
                        $facultyUsers[] = $cleanEmail;
                    } else {
                        $nonFacultyUsers[] = $cleanEmail;
                    }
                } else {
                    // User not found in database
                    $userRoles[$cleanEmail] = 'not_found';
                    $nonFacultyUsers[] = $cleanEmail;
                }
            }
            
            $response = [
                'success' => true,
                'userRoles' => $userRoles,
                'facultyUsers' => $facultyUsers,
                'nonFacultyUsers' => $nonFacultyUsers
            ];
            
            $this->jsonResponse($response);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Get active announcements
 */
    private function getActiveAnnouncements() {
    try {
        $announcements = $this->model->getActiveAnnouncements();
        
        // DEBUG: Add this to see what's being returned
        error_log("Active announcements count: " . count($announcements));
        error_log("Active announcements data: " . json_encode($announcements));
        
        $this->jsonResponse([
            'success' => true, 
            'announcements' => $announcements,
            'debug_count' => count($announcements) // Add this for debugging
        ]);
    } catch (Exception $e) {
        error_log("Error in getActiveAnnouncements: " . $e->getMessage());
        $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}

    /**
     * Get archived announcements 
     */
    private function getArchivedAnnouncements() {
        try {
            $announcements = $this->model->getArchivedAnnouncements();
            $this->jsonResponse(['success' => true, 'announcements' => $announcements]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get specific announcement 
     */
    private function getAnnouncement() {
        try {
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                throw new Exception('Announcement ID is required');
            }

            $announcement = $this->model->getAnnouncementById($id);
            if (!$announcement) {
                throw new Exception('Announcement not found');
            }

            $this->jsonResponse(['success' => true, 'announcement' => $announcement]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Create new announcement 
     */
    private function createAnnouncement() {
        try {
            if (!$this->validateCsrfToken($_POST['csrf_token']??'')){
                throw new Exception('Invalid CSRF token');
            }
            $this->validateAnnouncementData();
            date_default_timezone_set('Asia/Manila');

            $data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'type' => $_POST['type'],
                'priority' => $_POST['priority'] ?? 'normal',
                'start_date' => $_POST['start_date'],
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
                'status' => 'published',
                'created_by' => $_SESSION['user_id'] ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $success = $this->model->createAnnouncement($data);

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Announcement created successfully']);
            } else {
                throw new Exception('Failed to create announcement');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
 * Update existing announcement 
 */
private function updateAnnouncement() {
    try {
        if (!$this->validateCsrfToken($_POST['csrf_token']??'')){
                throw new Exception('Invalid CSRF token');
            }
        $this->validateAnnouncementData();

        $id = $_POST['announcement_id'] ?? '';
        if (empty($id)) {
            throw new Exception('Announcement ID is required');
        }

        $data = [
            'title' => trim($_POST['title']),
            'content' => trim($_POST['content']),
            'type' => $_POST['type'],
            // Remove priority since it's not in the form
            'start_date' => $_POST['start_date'],
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0
        ];

        $success = $this->model->updateAnnouncement($id, $data);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Announcement updated successfully']);
        } else {
            throw new Exception('Failed to update announcement');
        }
    } catch (Exception $e) {
        $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}

    /**
     * Save announcement as draft 
     */
    private function saveAnnouncementDraft() {
        try {
           if (!$this->validateCsrfToken($_POST['csrf_token']??'')){
                throw new Exception('Invalid CSRF token');
            }

            $data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'type' => $_POST['type'],
                'priority' => $_POST['priority'] ?? 'normal',
                'start_date' => $_POST['start_date'],
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
                'status' => 'draft',
                'created_by' => $_SESSION['user_id'] ?? 1
            ];

            $success = $this->model->createAnnouncement($data);

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Draft saved successfully']);
            } else {
                throw new Exception('Failed to save draft');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Archive announcement 
     */
    private function archiveAnnouncement() {
        try {
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception('Announcement ID is required');
            }

            $success = $this->model->updateAnnouncementStatus($id, 'archived');
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Announcement archived successfully']);
            } else {
                throw new Exception('Failed to archive announcement');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Restore announcement from archive 
     */
    private function restoreAnnouncement() {
        try {
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception('Announcement ID is required');
            }

            $success = $this->model->updateAnnouncementStatus($id, 'published');
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Announcement restored successfully']);
            } else {
                throw new Exception('Failed to restore announcement');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Delete announcement permanently 
     */
    private function deleteAnnouncement() {
        try {
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception('Announcement ID is required');
            }

            $success = $this->model->deleteAnnouncement($id);
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Announcement deleted successfully']);
            } else {
                throw new Exception('Failed to delete announcement');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle pin status of announcement 
     */
    private function togglePinAnnouncement() {
        try {
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception('Announcement ID is required');
            }

            // Get current pin status
            $announcement = $this->model->getAnnouncementById($id);
            if (!$announcement) {
                throw new Exception('Announcement not found');
            }

            $newPinStatus = $announcement->is_pinned ? 0 : 1;
            $success = $this->model->togglePinAnnouncement($id, $newPinStatus);
            
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Pin status updated successfully']);
            } else {
                throw new Exception('Failed to update pin status');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Validate announcement data
     */
    private function validateAnnouncementData() {
        $required = ['title', 'content', 'type', 'start_date'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Validate title length
        if (strlen($_POST['title']) > 200) {
            throw new Exception("Title must be less than 200 characters");
        }

        // Validate content length
        if (strlen($_POST['content']) > 2000) {
            throw new Exception("Content must be less than 2000 characters");
        }

        // Validate dates
        if ($_POST['start_date'] && !strtotime($_POST['start_date'])) {
            throw new Exception("Invalid start date format");
        }

        if (!empty($_POST['end_date']) && !strtotime($_POST['end_date'])) {
            throw new Exception("Invalid end date format");
        }

        if (!empty($_POST['end_date']) && strtotime($_POST['end_date']) <= strtotime($_POST['start_date'])) {
            throw new Exception("End date must be after start date");
        }
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken($token = null) {
        if (!isset($token)) {
            $token = null;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If no token provided, try common sources (POST/GET header)
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
            if ($token === null && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        }

        if (!isset($_SESSION['csrf_token']) || $token === null) {
            return false;
        }

        // Use timing-safe comparison
        return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        // Ensure $statusCode is defined and an integer to avoid "undefined variable" or invalid type errors
        $statusCode = isset($statusCode) ? (int)$statusCode : 200;

        // Ensure $data is defined to prevent "Undefined variable" if function was called without arguments
        if (!isset($data)) {
            $data = null;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get audit logs
     */
private function getAuditLogs() {
    try {
        error_log("=== DIRECT CONTROLLER getAuditLogs ===");
        
        // Bypass the model and query directly
        $limit = $_GET['limit'] ?? 100;
        $tableName = $_GET['table'] ?? null;
        $action = $_GET['action_type'] ?? null;
        
        $db = new Database();
        
        $sql = "SELECT * FROM AUDIT_LOGS WHERE 1=1";
        $params = [];
        
        if ($tableName) {
            $sql .= " AND table_name = :table_name";
            $params[':table_name'] = $tableName;
        }
        
        if ($action) {
            $sql .= " AND action = :action";
            $params[':action'] = $action;
        }
        
        $sql .= " ORDER BY changed_at DESC LIMIT :limit";
        $params[':limit'] = $limit;
        
        error_log("Direct controller SQL: " . $sql);
        
        $db->query($sql);
        foreach ($params as $key => $value) {
            $db->bind($key, $value);
        }
        
        $logs = $db->resultSet();
        error_log("Direct controller found: " . count($logs) . " logs");
        
        $this->jsonResponse([
            'success' => true,
            'logs' => $logs,
            'total' => count($logs),
            'debug' => [
                'query_used' => $sql,
                'parameters' => $params
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Direct controller error: " . $e->getMessage());
        $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}



/**
 * Direct test of audit logs query
 */
private function testAuditQuery() {
    header('Content-Type: application/json');
    
    try {
        error_log("=== DIRECT AUDIT QUERY TEST ===");
        
        // Test 1: Simple count
        $this->model->getDatabase()->query("SELECT COUNT(*) as count FROM AUDIT_LOGS");
        $countResult = $this->model->getDatabase()->single();
        error_log("Simple count: " . $countResult->count);
        
        // Test 2: Simple select without joins
        $this->model->getDatabase()->query("SELECT * FROM AUDIT_LOGS ORDER BY changed_at DESC LIMIT 5");
        $simpleResults = $this->model->getDatabase()->resultSet();
        error_log("Simple select count: " . count($simpleResults));
        
        // Test 3: Select with joins (like your actual method)
        $this->model->getDatabase()->query("
            SELECT al.*, ui.First_Name, ui.Last_Name, ui.Email 
            FROM AUDIT_LOGS al 
            LEFT JOIN USER_INFORMATION ui ON al.user_id = ui.ID 
            ORDER BY al.changed_at DESC 
            LIMIT 5
        ");
        $joinResults = $this->model->getDatabase()->resultSet();
        error_log("Join select count: " . count($joinResults));
        
        // Test 4: Check if user_id column exists and has data
        $this->model->getDatabase()->query("SELECT user_id FROM AUDIT_LOGS LIMIT 5");
        $userIds = $this->model->getDatabase()->resultSet();
        error_log("User IDs in audit logs: " . json_encode($userIds));
        
        $this->jsonResponse([
            'success' => true,
            'test_results' => [
                'simple_count' => $countResult->count,
                'simple_select_count' => count($simpleResults),
                'join_select_count' => count($joinResults),
                'user_ids_sample' => $userIds,
                'simple_results_sample' => $simpleResults,
                'join_results_sample' => $joinResults
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Direct test error: " . $e->getMessage());
        $this->jsonResponse([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

        /**
         * Set current user ID and IP for audit triggers
         */
        private function setCurrentUserForAudit() {
            try {
                $userId = $this->currentUser['user_db_id'] ?? null;
                $userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                
                if ($userId) {
                    $this->model->getDatabase()->query("SET @current_user_id = :user_id, @current_user_ip = :user_ip");
                    $this->model->getDatabase()->bind(':user_id', $userId);
                    $this->model->getDatabase()->bind(':user_ip', $userIp);
                    $this->model->getDatabase()->execute();
                }
            } catch (Exception $e) {
                error_log("Error setting current user for audit: " . $e->getMessage());
            }
        }

    /**
     * Get login attempts
     */
    private function getLoginAttempts() {
        try {
            $limit = $_GET['limit'] ?? 50;
            
            error_log("Getting login attempts - limit: $limit");

            $attempts = $this->model->getRecentLoginAttempts($limit);
            
            error_log("Found " . count($attempts) . " login attempts");
            
            $this->jsonResponse([
                'success' => true,
                'attempts' => $attempts,
                'total' => count($attempts)
            ]);
        } catch (Exception $e) {
            error_log("Error in getLoginAttempts: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get security alerts
     */
    private function getSecurityAlerts() {
        try {
            $limit = $_GET['limit'] ?? 10;
            $alerts = $this->model->getSecurityAlerts($limit);
            
            $this->jsonResponse([
                'success' => true,
                'alerts' => $alerts,
                'total' => count($alerts)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get user notifications
     */
    private function getNotifications() {
        try {
            $userId = $this->currentUser['user_db_id'] ?? null;
            $limit = $_GET['limit'] ?? 20;
            $unreadOnly = $_GET['unread_only'] ?? false;

            if (!$userId) {
                throw new Exception('User ID not found');
            }

            $notifications = $this->model->getUserNotifications($userId, $limit, $unreadOnly);
            
            $this->jsonResponse([
                'success' => true,
                'notifications' => $notifications,
                'total' => count($notifications)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Mark notification as read
     */
    private function markNotificationRead() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $notificationId = $data['notification_id'] ?? null;

            if (!$notificationId) {
                throw new Exception('Notification ID is required');
            }

            $success = $this->model->markNotificationAsRead($notificationId);

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                throw new Exception('Failed to mark notification as read');
            }
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    
    /**
     * Get reports data for charts and statistics
     */
    private function getReports() {
        try {
            $reportType = $_GET['type'] ?? 'overview';
            $department = $_GET['department'] ?? 'all';
            
            switch ($reportType) {
                case 'thesis':
                    $data = $this->getThesisReports($department);
                    break;
                case 'users':
                    $data = $this->getUserReports($department);
                    break;
                case 'department':
                    $data = $this->getDepartmentReports($department);
                    break;
                case 'overview':
                default:
                    $data = $this->getOverviewReports($department);
                    break;
            }
            
            $this->jsonResponse([
                'success' => true,
                'data' => $data,
                'report_type' => $reportType,
                'department' => $department
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

/**
 * Get overview reports data for charts
 */
private function getOverviewReports($department = 'all') {
    $stats = $this->model->getReportsStats($department);
    $courseDistribution = $this->model->getCourseDistribution($department);
    $monthlyUploads = $this->model->getMonthlyThesisUploads($department);
    $programCounts = $this->model->getProgramThesisCounts();

    return [
        'stats' => $stats,
        'course_distribution' => $courseDistribution,
        'monthly_uploads' => $monthlyUploads,
        'program_counts' => $programCounts
    ];
}

    /**
     * Get course distribution data for pie chart
     */
private function getCourseDistribution($department = 'all') {
    try {
        return $this->model->getCourseDistribution($department);
    } catch (Exception $e) {
        error_log("Error getting course distribution: " . $e->getMessage());
        return [];
    }
}

    /**
     * Get monthly uploads data for bar chart
     */
    private function getMonthlyUploads($department = 'all') {
            try {
                return $this->model->getMonthlyThesisUploads($department);
            } catch (Exception $e) {
                error_log("Error getting monthly uploads: " . $e->getMessage());
                return $this->getEmptyMonthlyData();
            }
        }


    /**
     * Get reports statistics
     */

    private function getReportsStats($department = 'all') {
    try {
        return $this->model->getReportsStats($department);
    } catch (Exception $e) {
        error_log("Error getting reports stats: " . $e->getMessage());
        return [
            'total_theses' => 0,
            'total_students' => 0,
            'recent_theses' => 0
        ];
    }
}

    /**
     * Map department values to course codes
     */
    private function getCourseCodesByDepartment($department) {
        $department = $department ?? 'all';
       
        $departmentMap = [
            'beced' => ['Bachelor of Early Childhood Education'],
            'bsed' => ['Bachelor of Secondary Education'],
            'btvted' => ['Bachelor of Technical-Vocational Teacher Education'],
            'beed' => ['Bachelor of Elementary Education'],
            'bsned' => ['Bachelor of Special Needs Education'],
            'bsabe' => [
                'Bachelor of Science in Agricultural and Biosystems Engineering', 
                'Bachelor of Science in Agriculture and Biosystems Engineering'
            ],
            'bsit' => ['Bachelor of Science in Information Technology']
        ];
        
        return $departmentMap[$department] ?? [];
    }

    /**
     * Get empty monthly data structure
     */
    private function getEmptyMonthlyData() {
        $months = [
            ['month' => 'Jan', 'month_name' => 'January', 'upload_count' => 0],
            ['month' => 'Feb', 'month_name' => 'February', 'upload_count' => 0],
            ['month' => 'Mar', 'month_name' => 'March', 'upload_count' => 0],
            ['month' => 'Apr', 'month_name' => 'April', 'upload_count' => 0],
            ['month' => 'May', 'month_name' => 'May', 'upload_count' => 0],
            ['month' => 'Jun', 'month_name' => 'June', 'upload_count' => 0],
            ['month' => 'Jul', 'month_name' => 'July', 'upload_count' => 0],
            ['month' => 'Aug', 'month_name' => 'August', 'upload_count' => 0],
            ['month' => 'Sep', 'month_name' => 'September', 'upload_count' => 0],
            ['month' => 'Oct', 'month_name' => 'October', 'upload_count' => 0],
            ['month' => 'Nov', 'month_name' => 'November', 'upload_count' => 0],
            ['month' => 'Dec', 'month_name' => 'December', 'upload_count' => 0]
        ];
        
        return $months;
    }

    private function getThesisReports($department = 'all') {
        $department = $department ?? 'all';
        $theses = $department === 'all' 
            ? $this->model->getAllTheses() 
            : $this->model->getThesesByDepartment($department);
        
        $thesisStats = $this->model->getThesisStatistics($department);
        $uploadTrends = $this->model->getThesisUploadTrends($department);
        
        return [
            'theses' => $theses,
            'stats' => $thesisStats,
            'trends' => $uploadTrends
        ];
    }

    private function getUserReports($department = 'all') {
        $department = $department ?? 'all';
        $users = $department === 'all' 
            ? $this->model->getAllUsers() 
            : $this->model->getUsersByDepartment($department);
        
        $userStats = $this->model->getUserStatistics($department);
        $registrationTrends = $this->model->getUserRegistrationTrends($department);
        
        return [
            'users' => $users,
            'stats' => $userStats,
            'trends' => $registrationTrends
        ];
    }

    private function getDepartmentReports($department = 'all') {
        $department = $department ?? 'all';
        if ($department === 'all') {
            return $this->model->getAllDepartmentReports();
        }
        
        return $this->model->getDepartmentReport($department);
    }


    private function handleGetAuditLogs() {
    $table = $_GET['table'] ?? null;
    $limit = $_GET['limit'] ?? 50;
    $logs = $this->model->getAuditLogs($limit, $table);
    $this->jsonResponse(['success' => true, 'logs' => $logs]);
}

private function handleGetLoginAttempts() {
    $limit = $_GET['limit'] ?? 50;
    $attempts = $this->model->getLoginAttempts($limit);
    $this->jsonResponse(['success' => true, 'attempts' => $attempts]);
}

    
/**
 * Generate PDF report - WORKING VERSION
 */
private function generateReport() {
    try {
        error_log("=== GENERATE REPORT METHOD CALLED ===");
        
        $department = $_GET['department'] ?? 'all';
        error_log("Processing department: " . $department);
        
        // Get report data
        $reportData = $this->getOverviewReports($department);
        error_log("Report data retrieved successfully");
        
        // Check if dompdf is available
        if (!class_exists('Dompdf\Dompdf')) {
            throw new Exception('Dompdf library not found. Please install via composer: composer require dompdf/dompdf');
        }
        
        // Generate HTML content
        $html = $this->generateReportHTML($reportData, $department);
        
        // Configure dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Generate filename
        $timestamp = date('Y-m-d');
        $deptName = $this->getDepartmentDisplayName($department);
        $filename = "Thesis_Report_{$deptName}_{$timestamp}.pdf";
        
        // Output the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        
        echo $dompdf->output();
        exit;
        
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Return JSON error instead of dying
        $this->jsonResponse([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'PDF generation failed'
        ], 500);
    }
}

/**
 * Generate HTML content for PDF report with charts - FIXED VERSION
 */
private function generateReportHTML($reportData, $department) {
    $deptName = $this->getDepartmentDisplayName($department);
    $currentDate = date('F j, Y');
    
    // Safely extract data with proper object/array access
    $stats = $reportData['stats'] ?? [];
    $programCounts = $reportData['program_counts'] ?? [];
    $courseDistribution = $reportData['course_distribution'] ?? [];
    $monthlyUploads = $reportData['monthly_uploads'] ?? [];
    
    // Safely access stats with both object and array syntax
    $totalTheses = 0;
    $totalStudents = 0;
    $recentTheses = 0;
    
    if (is_object($stats)) {
        $totalTheses = $stats->total_theses ?? 0;
        $totalStudents = $stats->total_students ?? 0;
        $recentTheses = $stats->recent_theses ?? 0;
    } else if (is_array($stats)) {
        $totalTheses = $stats['total_theses'] ?? 0;
        $totalStudents = $stats['total_students'] ?? 0;
        $recentTheses = $stats['recent_theses'] ?? 0;
    }
    
    // Prepare chart data
    $pieChartData = $this->preparePieChartData($courseDistribution);
    $barChartData = $this->prepareBarChartData($monthlyUploads);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Thesis Management System Report</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 40px; 
                color: #333;
                line-height: 1.6;
            }
            .header { 
                text-align: center; 
                border-bottom: 3px solid #ba1e1f; 
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .header h1 { 
                color: #ba1e1f; 
                margin: 0; 
                font-size: 28px;
            }
            .header h2 { 
                color: #666; 
                margin: 10px 0; 
                font-size: 20px;
                font-weight: normal;
            }
            .stats-section { 
                margin: 30px 0; 
            }
            .stats-grid {
                display: flex;
                justify-content: space-between;
                margin: 20px 0;
            }
            .stat-card {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                flex: 1;
                margin: 0 10px;
            }
            .stat-number {
                font-size: 32px;
                font-weight: bold;
                color: #ba1e1f;
                margin: 10px 0;
            }
            .stat-label {
                color: #666;
                font-size: 14px;
                font-weight: bold;
            }
            .charts-section {
                margin: 40px 0;
            }
            .chart-container {
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 30px;
            }
            .chart-title {
                color: #ba1e1f;
                border-bottom: 2px solid #ba1e1f;
                padding-bottom: 10px;
                margin-bottom: 20px;
                font-size: 18px;
            }
            .table-section {
                margin: 40px 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
            }
            th {
                background-color: #ba1e1f;
                color: white;
                font-weight: bold;
            }
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .footer {
                margin-top: 50px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .chart-table {
                width: 100%;
                margin: 20px 0;
            }
            .chart-table th {
                background-color: #495057;
            }
            .color-swatch {
                display: inline-block;
                width: 12px;
                height: 12px;
                margin-right: 8px;
                border-radius: 2px;
            }
            .program-stats {
                background: #fff;
                border-radius: 8px;
                padding: 15px;
                margin: 15px 0;
            }
            .section-title {
                color: #ba1e1f;
                border-bottom: 2px solid #ba1e1f;
                padding-bottom: 10px;
                margin-bottom: 20px;
                font-size: 20px;
            }
            .charts-row {
                display: flex;
                gap: 30px;
                margin: 30px 0;
            }
            .chart-half {
                flex: 1;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Thesis Management System</h1>
            <h2>Program Report - ' . htmlspecialchars($deptName) . '</h2>
            <p><strong>Generated on:</strong> ' . $currentDate . '</p>
        </div>
        
        <div class="stats-section">
            <h3 class="section-title">Overview Statistics</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">' . $totalTheses . '</div>
                    <div class="stat-label">Total Theses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">' . $totalStudents . '</div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">' . $recentTheses . '</div>
                    <div class="stat-label">Recent Theses</div>
                </div>
            </div>
        </div>';
        
    // Add Charts Section
    $html .= '
        <div class="charts-section">
            <h3 class="section-title">Data Visualizations</h3>
            
            <div class="charts-row">
                <!-- Student Distribution Pie Chart -->
                <div class="chart-half">
                    <div class="chart-container">
                        <h3 class="chart-title">Student Distribution by Program</h3>
                        ' . $this->generatePieChartTable($pieChartData) . '
                    </div>
                </div>
                
                <!-- Thesis Uploads Bar Chart -->
                <div class="chart-half">
                    <div class="chart-container">
                        <h3 class="chart-title">Thesis Uploads (Last 12 Months)</h3>
                        ' . $this->generateBarChartTable($barChartData) . '
                    </div>
                </div>
            </div>
        </div>';
        
    // Add program counts table if available
    if (!empty($programCounts)) {
        $html .= '
        <div class="table-section">
            <h3 class="section-title">Thesis Count by Program</h3>
            <table>
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Thesis Count</th>
                    </tr>
                </thead>
                <tbody>';
        
        $totalCount = 0;
        foreach ($programCounts as $program) {
            // Handle both object and array access
            if (is_object($program)) {
                $programName = $program->program ?? 'Unknown';
                $count = $program->thesis_count ?? 0;
            } else {
                $programName = $program['program'] ?? 'Unknown';
                $count = $program['thesis_count'] ?? 0;
            }
            $totalCount += $count;
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($programName) . '</td>
                        <td>' . $count . '</td>
                    </tr>';
        }
        
        $html .= '
                    <tr style="font-weight: bold; background-color: #e9ecef;">
                        <td><strong>Total</strong></td>
                        <td><strong>' . $totalCount . '</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>';
    }
    
    $html .= '
        <div class="footer">
            <p><strong>Generated by Thesis Management System | University of Southeastern Philippines</strong></p>
            <p>This is an automated report. For questions, contact system administrator.</p>
        </div>
    </body>
    </html>';
    
    return $html;
}


/**
 * Prepare pie chart data for PDF - FIXED VERSION
 */
private function preparePieChartData($courseDistribution) {
    if (empty($courseDistribution)) {
        return [
            ['label' => 'No Data Available', 'value' => 100, 'color' => '#CCCCCC']
        ];
    }

    $colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', 
        '#FFEAA7', '#cd84cd', '#48ffd1', '#FFA726',
        '#AB47BC', '#26C6DA', '#D4E157', '#FF7043'
    ];

    $data = [];
    $colorIndex = 0;
    
    foreach ($courseDistribution as $course) {
        // Handle both object and array access
        if (is_object($course)) {
            $label = $course->course ?? 'Unknown';
            $value = $course->student_count ?? 0;
        } else {
            $label = $course['course'] ?? 'Unknown';
            $value = $course['student_count'] ?? 0;
        }
        
        $data[] = [
            'label' => $label,
            'value' => $value,
            'color' => $colors[$colorIndex % count($colors)]
        ];
        $colorIndex++;
    }
    
    return $data;
}

/**
 * Prepare bar chart data for PDF - FIXED VERSION
 */
private function prepareBarChartData($monthlyUploads) {
    if (empty($monthlyUploads)) {
        // Return empty data for all months
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = [];
        foreach ($months as $month) {
            $data[] = [
                'month' => $month,
                'count' => 0
            ];
        }
        return $data;
    }
    
    $processedData = [];
    foreach ($monthlyUploads as $upload) {
        // Handle both object and array access
        if (is_object($upload)) {
            $month = $upload->month ?? 'Unknown';
            $count = $upload->upload_count ?? $upload->count ?? 0;
        } else {
            $month = $upload['month'] ?? 'Unknown';
            $count = $upload['upload_count'] ?? $upload['count'] ?? 0;
        }
        
        $processedData[] = [
            'month' => $month,
            'count' => $count
        ];
    }
    
    return $processedData;
}

/**
 * Generate pie chart as a table for PDF
 */
private function generatePieChartTable($pieChartData) {
    $html = '<table class="chart-table">';
    $html .= '<thead><tr><th>Program</th><th>Students</th><th>Percentage</th></tr></thead><tbody>';
    
    $totalStudents = array_sum(array_column($pieChartData, 'value'));
    
    foreach ($pieChartData as $item) {
        $percentage = $totalStudents > 0 ? round(($item['value'] / $totalStudents) * 100, 1) : 0;
        $html .= '
            <tr>
                <td>
                    <span class="color-swatch" style="background-color: ' . $item['color'] . '"></span>
                    ' . htmlspecialchars($item['label']) . '
                </td>
                <td>' . $item['value'] . '</td>
                <td>' . $percentage . '%</td>
            </tr>';
    }
    
    $html .= '
        <tr style="font-weight: bold; background-color: #e9ecef;">
            <td>Total</td>
            <td>' . $totalStudents . '</td>
            <td>100%</td>
        </tr>
    </tbody></table>';
    
    return $html;
}

/**
 * Generate bar chart as a table for PDF
 */
private function generateBarChartTable($barChartData) {
    $html = '<table class="chart-table">';
    $html .= '<thead><tr><th>Month</th><th>Thesis Uploads</th></tr></thead><tbody>';
    
    $totalUploads = 0;
    $maxUploads = 0;
    $peakMonth = '';
    
    foreach ($barChartData as $item) {
        $count = $item['upload_count'] ?? $item['count'] ?? 0;
        $month = $item['month'] ?? 'Unknown';
        $totalUploads += $count;
        
        if ($count > $maxUploads) {
            $maxUploads = $count;
            $peakMonth = $month;
        }
        
        $html .= '
            <tr>
                <td>' . htmlspecialchars($month) . '</td>
                <td>' . $count . '</td>
            </tr>';
    }
    
    $html .= '
        <tr style="font-weight: bold; background-color: #e9ecef;">
            <td>Total Year</td>
            <td>' . $totalUploads . '</td>
        </tr>
        <tr style="font-weight: bold; background-color: #d1ecf1;">
            <td>Peak Month (' . htmlspecialchars($peakMonth) . ')</td>
            <td>' . $maxUploads . '</td>
        </tr>
    </tbody></table>';
    
    return $html;
}

/**
 * Get department display name
 */
private function getDepartmentDisplayName($department) {
    $departmentMap = [
        'all' => 'All Programs',
        'bsit' => 'BSIT - Information Technology',
        'beced' => 'BECED - Early Childhood Education',
        'bsed' => 'BSED - Secondary Education',
        'btvted' => 'BTVTED - Technical-Vocational',
        'beed' => 'BEED - Elementary Education',
        'bsned' => 'BSNED - Special Needs Education',
        'bsabe' => 'BSABE - Agricultural Engineering'
    ];
    
    return $departmentMap[$department] ?? 'All Programs';
}



}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $model = new AdminDashboardModel();

    if ($action === 'getAuditLogs') {
        $table = $_GET['table'] ?? null;
        $limit = $_GET['limit'] ?? 50;
        $logs = $model->getAuditLogs($limit, $table);
        echo json_encode(['success' => true, 'logs' => $logs]);
        exit;
    }

    if ($action === 'getLoginAttempts') {
        $limit = $_GET['limit'] ?? 50;
        $attempts = $model->getLoginAttempts($limit);
        echo json_encode(['success' => true, 'attempts' => $attempts]);
        exit;
    }
}

// Handle the request if this file is called directly
if (basename($_SERVER['PHP_SELF']) === 'AdminDashboardController.php') {
    $controller = new AdminDashboardController();
    $controller->handleRequest();
}
?>