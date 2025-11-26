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
            case 'generateLogsReport':
                $this->generateLogsReport();
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
        $logs = $this->getDetailedAuditLogs();
        
        $this->jsonResponse([
            'success' => true,
            'logs' => $logs,
            'total' => count($logs)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in getAuditLogs: " . $e->getMessage());
        $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get detailed audit logs with exact timestamps and full details
 */
private function getDetailedAuditLogs() {
    try {
        $limit = $_GET['limit'] ?? 100;
        
        $db = new Database();
        
        $sql = "SELECT 
                    al.*,
                    ui.First_Name,
                    ui.Last_Name, 
                    ui.Email,
                    ui.User_Role,
                    DATE_FORMAT(al.changed_at, '%Y-%m-%d %H:%i:%s') as exact_timestamp,
                    al.ip_address
                FROM AUDIT_LOGS al 
                LEFT JOIN USER_INFORMATION ui ON al.user_id = ui.ID 
                ORDER BY al.changed_at DESC 
                LIMIT :limit";
        
        $db->query($sql);
        $db->bind(':limit', $limit);
        
        $logs = $db->resultSet();
        
        // Format the logs with detailed information
        $formattedLogs = [];
        foreach ($logs as $log) {
            $formattedLogs[] = [
                'id' => $log->id,
                'timestamp' => $log->exact_timestamp,
                'ip_address' => $log->ip_address ?: 'N/A',
                'user_name' => $log->First_Name && $log->Last_Name ? 
                    $log->First_Name . ' ' . $log->Last_Name : 
                    ($log->user_name ?: 'System'),
                'user_role' => $log->User_Role ?: 'System',
                'action' => $this->getDetailedAction($log),
                'details' => $this->getFullDetails($log),
                'table_name' => $log->table_name,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values
            ];
        }
        
        return $formattedLogs;
        
    } catch (Exception $e) {
        error_log("Error getting detailed audit logs: " . $e->getMessage());
        return [];
    }
}

/**
 * Get detailed action description
 */
private function getDetailedAction($log) {
    $action = strtoupper($log->action);
    $table = $log->table_name;
    
    switch ($action) {
        case 'INSERT':
            if ($table === 'ANNOUNCEMENTS') return 'Created Announcement';
            if ($table === 'THESIS') return 'Uploaded Thesis';
            if ($table === 'USER_INFORMATION') return 'Registered User';
            return 'Created Record';
            
        case 'UPDATE':
            if ($table === 'ANNOUNCEMENTS') return 'Updated Announcement';
            if ($table === 'USER_INFORMATION') return 'Updated User Account';
            if ($table === 'THESIS') return 'Updated Thesis';
            return 'Updated Record';
            
        case 'DELETE':
            if ($table === 'ANNOUNCEMENTS') return 'Deleted Announcement';
            if ($table === 'USER_INFORMATION') return 'Deleted User Account';
            if ($table === 'THESIS') return 'Deleted Thesis';
            return 'Deleted Record';
            
        default:
            return $log->action;
    }
}

/**
 * Get full detailed description
 */
private function getFullDetails($log) {
    $action = strtoupper($log->action);
    $table = $log->table_name;
    
    try {
        $oldValues = $log->old_values ? json_decode($log->old_values, true) : [];
        $newValues = $log->new_values ? json_decode($log->new_values, true) : [];
        
        switch ($table) {
            case 'ANNOUNCEMENTS':
                if ($action === 'INSERT') {
                    $title = $newValues['title'] ?? 'Unknown Title';
                    return "Created new announcement: '{$title}' with type: " . ($newValues['type'] ?? 'information');
                }
                if ($action === 'UPDATE') {
                    $title = $newValues['title'] ?? $oldValues['title'] ?? 'Unknown Title';
                    $changes = [];
                    if (isset($newValues['title']) && isset($oldValues['title']) && $newValues['title'] !== $oldValues['title']) {
                        $changes[] = "title from '{$oldValues['title']}' to '{$newValues['title']}'";
                    }
                    if (isset($newValues['content']) && isset($oldValues['content']) && $newValues['content'] !== $oldValues['content']) {
                        $changes[] = "content updated";
                    }
                    if (isset($newValues['status']) && isset($oldValues['status']) && $newValues['status'] !== $oldValues['status']) {
                        $changes[] = "status from {$oldValues['status']} to {$newValues['status']}";
                    }
                    return "Updated announcement '{$title}': " . implode(', ', $changes);
                }
                if ($action === 'DELETE') {
                    $title = $oldValues['title'] ?? 'Unknown Title';
                    return "Permanently deleted announcement: '{$title}'";
                }
                break;
                
            case 'USER_INFORMATION':
                if ($action === 'INSERT') {
                    $name = ($newValues['First_Name'] ?? '') . ' ' . ($newValues['Last_Name'] ?? '');
                    return "Registered new user: {$name} with role: " . ($newValues['User_Role'] ?? 'student');
                }
                if ($action === 'UPDATE') {
                    $name = ($newValues['First_Name'] ?? $oldValues['First_Name'] ?? '') . ' ' . ($newValues['Last_Name'] ?? $oldValues['Last_Name'] ?? '');
                    $changes = [];
                    if (isset($newValues['User_Role']) && isset($oldValues['User_Role']) && $newValues['User_Role'] !== $oldValues['User_Role']) {
                        $changes[] = "role from {$oldValues['User_Role']} to {$newValues['User_Role']}";
                    }
                    if (isset($newValues['Acc_Status']) && isset($oldValues['Acc_Status']) && $newValues['Acc_Status'] !== $oldValues['Acc_Status']) {
                        $changes[] = "status from {$oldValues['Acc_Status']} to {$newValues['Acc_Status']}";
                    }
                    return "Updated user {$name}: " . implode(', ', $changes);
                }
                if ($action === 'DELETE') {
                    $name = ($oldValues['First_Name'] ?? '') . ' ' . ($oldValues['Last_Name'] ?? '');
                    return "Permanently deleted user account: {$name}";
                }
                break;
                
            case 'THESIS':
                if ($action === 'INSERT') {
                    $title = $newValues['Title'] ?? 'Unknown Title';
                    return "Uploaded new thesis: '{$title}' by " . ($newValues['Thesis_Email'] ?? 'unknown authors');
                }
                if ($action === 'UPDATE') {
                    $title = $newValues['Title'] ?? $oldValues['Title'] ?? 'Unknown Title';
                    return "Updated thesis details for: '{$title}'";
                }
                if ($action === 'DELETE') {
                    $title = $oldValues['Title'] ?? 'Unknown Title';
                    return "Permanently deleted thesis: '{$title}'";
                }
                break;
        }
        
        // Generic fallback
        return "Performed {$action} operation on {$table} table";
        
    } catch (Exception $e) {
        return "Performed {$action} operation on {$table} table";
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
        $course = $_GET['course'] ?? 'all'; // New course parameter
        
        switch ($reportType) {
            case 'thesis':
                $data = $this->getThesisReports($department, $course);
                break;
            case 'users':
                $data = $this->getUserReports($department, $course);
                break;
            case 'department':
                $data = $this->getDepartmentReports($department, $course);
                break;
            case 'overview':
            default:
                $data = $this->getOverviewReports($department, $course);
                break;
        }
        
        $this->jsonResponse([
            'success' => true,
            'data' => $data,
            'report_type' => $reportType,
            'department' => $department,
            'course' => $course
        ]);
        
    } catch (Exception $e) {
        $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get overview reports data for charts
 */
private function getOverviewReports($department = 'all', $course = 'all') {
    $stats = $this->model->getReportsStats($department, $course);
    $userDistribution = $this->model->getUserDistributionByRole($department, $course); 
    $programThesisCounts = $this->model->getThesisPerProgram($department, $course);
    $programCounts = $this->model->getDepartmentThesisCounts();

    // Get available courses for the selected department
    $availableCourses = $this->model->getCoursesByDepartment($department);

    return [
        'stats' => $stats,
        'user_distribution' => $userDistribution, 
        'program_thesis_counts' => $programThesisCounts,
        'program_counts' => $programCounts,
        'available_courses' => $availableCourses
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


private function getUserCountsByRole() {
    try {
        $this->model->getDatabase()->query("
            SELECT 
                User_Role,
                COUNT(*) as user_count
            FROM USER_INFORMATION 
            WHERE Acc_Status = 'approved'
            GROUP BY User_Role
        ");
        return $this->model->getDatabase()->resultSet();
    } catch (Exception $e) {
        error_log("Error getting user counts by role: " . $e->getMessage());
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
private function generateReport() {
    // Add custom error handler to catch warnings
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        throw new Exception("Error in $errfile on line $errline: $errstr");
    });

    $department = $_GET['department'] ?? 'all';
    $course = $_GET['course'] ?? 'all';

    try {
        error_log("=== STARTING PDF GENERATION ===");
        error_log("Department: $department, Course: $course");
        
        $stats = $this->model->getReportsStats($department, $course);
        error_log("Stats received: " . print_r($stats, true));
        
        // Safely extract and convert to numbers
        $totalTheses = 0;
        $totalUsers = 0;
        
        if (is_object($stats)) {
            $totalTheses = is_numeric($stats->total_theses ?? 0) ? (float)$stats->total_theses : 0;
            $totalUsers = is_numeric($stats->total_students ?? $stats->total_users ?? 0) ? (float)($stats->total_students ?? $stats->total_users) : 0;
        } elseif (is_array($stats)) {
            $totalTheses = is_numeric($stats['total_theses'] ?? 0) ? (float)$stats['total_theses'] : 0;
            $totalUsers = is_numeric($stats['total_students'] ?? $stats['total_users'] ?? 0) ? (float)($stats['total_students'] ?? $stats['total_users']) : 0;
        } else {
            error_log("Stats is not object or array: " . gettype($stats));
        }
        
        error_log("Total theses: $totalTheses (type: " . gettype($totalTheses) . ")");
        error_log("Total users: $totalUsers (type: " . gettype($totalUsers) . ")");

        error_log("Preparing pie chart data...");
        $pieData = $this->preparePieChartData($department, $course);
        error_log("Pie data prepared successfully");
        
        error_log("Preparing bar chart data...");
        $barData = $this->prepareBarChartData($department, $course);
        error_log("Bar data prepared successfully");

        $currentDate = date('F j, Y');
        $deptName = strtoupper($department) === 'ALL' ? 'All Programs' : strtoupper($department);
        $courseName = $course === 'all' ? 'All Courses' : $course;

        error_log("Generating HTML content...");
        $html = $this->generateReportHTML($deptName, $courseName, $currentDate, $totalTheses, $totalUsers, $pieData, $barData);
        
        if (empty($html)) {
            throw new Exception("HTML content generation failed");
        }

        error_log("HTML content generated successfully");

        if (!class_exists('Dompdf\Dompdf')) {
            throw new Exception('Dompdf library not found');
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        error_log("PDF generation completed successfully");
        $dompdf->stream("Thesis_Report_{$deptName}_" . date('Y-m-d') . ".pdf", ['Attachment' => true]);
        
        exit;

    } catch (Exception $e) {
        error_log("=== PDF GENERATION FAILED ===");
        error_log("Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        // Restore error handler
        restore_error_handler();
        
        $this->jsonResponse([
            'success' => false,
            'error' => 'Failed to generate PDF report: ' . $e->getMessage(),
            'debug_info' => [
                'department' => $department,
                'course' => $course,
                'stats_type' => gettype($stats),
                'stats_value' => $stats
            ]
        ], 500);
    } finally {
        // Ensure error handler is restored
        restore_error_handler();
    }
}

private function generateReportHTML($deptName, $courseName, $currentDate, $totalTheses, $totalUsers, $pieData, $barData) {
    $pieTable = $this->generatePieChartTable($pieData);
    $barTable = $this->generateBarChartTable($barData);

    // Safely format numbers - ensure they are numeric
    $formattedTheses = number_format((float)$totalTheses, 0);
    $formattedUsers = number_format((float)$totalUsers, 0);

    return '<!DOCTYPE html>
    <html><head><meta charset="utf-8"><title>Report - ' . htmlspecialchars($deptName) . '</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 40px; color: #333; line-height: 1.6; }
        .header { text-align: center; border-bottom: 5px double #ba1e1f; padding-bottom: 20px; }
        .header h1 { margin: 10px 0; color: #ba1e1f; font-size: 30px; }
        .header h2 { margin: 10px 0; font-size: 22px; color: #555; }
        .stats-grid { display: flex; gap: 30px; margin: 40px 0; flex-wrap: wrap; justify-content: center; }
        .stat-card { background: #ba1e1f; color: white; padding: 25px; border-radius: 12px; min-width: 200px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .stat-number { font-size: 42px; font-weight: bold; margin-bottom: 8px; }
        .section-title { font-size: 24px; color: #ba1e1f; border-bottom: 3px solid #ba1e1f; padding-bottom: 10px; margin: 50px 0 25px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 15px; }
        th { background: #ba1e1f; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f8f9fa; }
        .color-swatch { display: inline-block; width: 16px; height: 16px; border-radius: 4px; margin-right: 10px; vertical-align: middle; }
        .footer { margin-top: 80px; text-align: center; color: #777; font-size: 12px; padding-top: 20px; border-top: 1px solid #eee; }
    </style></head><body>
        <div class="header">
            <h1>Compendium System</h1>
            <h2>System Report - ' . htmlspecialchars($deptName) . '</h2>
            <p><strong>Course:</strong> ' . htmlspecialchars($courseName) . '</p>
            <p><strong>Generated on:</strong> ' . $currentDate . '</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">' . $formattedTheses . '</div>
                <div class="stat-label">Total Theses Uploaded</div>
            </div>
            <br>
            <div class="stat-card">
                <div class="stat-number">' . $formattedUsers . '</div>
                <div class="stat-label">Registered & Approved Users</div>
            </div>
        </div>
        <br><br><br>
        <h3 class="section-title">User Distribution by Role</h3>
        ' . $pieTable . '

        <h3 class="section-title">Total Thesis per Program</h3>
        ' . $barTable . '

        <div class="footer">
            <p><strong>Compendium System • University of Southeastern Philippines</strong></p>
            <p>Automated Report • ' . $currentDate . '</p>
        </div>
    </body></html>';
}

/**
 * Prepare pie chart data: User Distribution by Role (Approved Users Only)
 */
private function preparePieChartData($department = 'all', $course = 'all') {
    try {
        $sql = "SELECT User_Role, COUNT(*) as count 
                FROM USER_INFORMATION 
                WHERE Acc_Status = 'approved'";

        $params = [];
        
        if ($department !== 'all') {
            $courseCodes = $this->getCourseCodesByDepartment($department);
            if (!empty($courseCodes)) {
                $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
                $sql .= " AND Course IN ($placeholders)";
                $params = array_merge($params, $courseCodes);
            }
        }

        // Apply specific course filtering
        if ($course !== 'all' && !empty($course)) {
            $sql .= " AND Course = ?";
            $params[] = $course;
        }

        $sql .= " GROUP BY User_Role ORDER BY count DESC";

        $this->model->getDatabase()->query($sql);
        foreach ($params as $i => $value) {
            $this->model->getDatabase()->bind($i + 1, $value);
        }

        $results = $this->model->getDatabase()->resultSet();

        // Debug: Check what results we're getting
        error_log("Pie chart query results: " . print_r($results, true));
        error_log("Results type: " . gettype($results));
        error_log("Results count: " . count($results));

        // Ensure results is an array
        if (!is_array($results)) {
            error_log("Results is not an array, converting...");
            $results = [];
        }

        $colors = ['#ba1e1f', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4'];
        $roleLabels = [
            'student' => 'Students',
            'faculty' => 'Faculty',
            'SubAdmin' => 'Sub-Admins',
            'superAdmin' => 'Super Admins',
            'admin' => 'Admins'
        ];

        $data = [];
        
        // Safely iterate through results
        if (is_array($results) && !empty($results)) {
            foreach ($results as $idx => $row) {
                // Handle both object and array formats safely
                $role = '';
                $count = 0;
                
                if (is_object($row)) {
                    $role = $row->User_Role ?? 'unknown';
                    $count = (int)($row->count ?? 0);
                } elseif (is_array($row)) {
                    $role = $row['User_Role'] ?? 'unknown';
                    $count = (int)($row['count'] ?? 0);
                }
                
                // Only add if we have valid data
                if (!empty($role) && $count > 0) {
                    $data[] = [
                        'label' => $roleLabels[$role] ?? ucfirst($role),
                        'value' => $count,
                        'color' => $colors[$idx % count($colors)]
                    ];
                }
            }
        }

        // Fallback if no users
        if (empty($data)) {
            error_log("No valid user data found, using fallback");
            $data[] = ['label' => 'No Approved Users', 'value' => 1, 'color' => '#cccccc'];
        }

        error_log("Final pie chart data: " . print_r($data, true));
        return $data;
        
    } catch (Exception $e) {
        error_log("Pie chart error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [['label' => 'Error Loading Data', 'value' => 1, 'color' => '#ff0000']];
    }
}

/**
 * Prepare bar chart data: Thesis Count per Program
 */
private function prepareBarChartData($department = 'all', $course = 'all') {
    try {
        $programCounts = $this->model->getThesisCountsByProgram($department, $course);

        // Debug: Check what we're getting
        error_log("Raw program counts: " . print_r($programCounts, true));
        error_log("Program counts type: " . gettype($programCounts));

        // Ensure programCounts is an array
        if (!is_array($programCounts)) {
            error_log("Program counts is not an array, converting to empty array");
            $programCounts = [];
        }

        $shortNames = [
            'Bachelor of Science in Information Technology' => 'BSIT',
            'Bachelor of Early Childhood Education' => 'BECED',
            'Bachelor of Secondary Education' => 'BSED',
            'Bachelor of Technical-Vocational Teacher Education' => 'BTVTED',
            'Bachelor of Elementary Education' => 'BEED',
            'Bachelor of Special Needs Education' => 'BSNED',
            'Bachelor of Science in Agricultural and Biosystems Engineering' => 'BSABE',
            'Bachelor of Science in Agriculture and Biosystems Engineering' => 'BSABE',
        ];

        $data = [];
        
        // Safely iterate through programCounts
        if (is_array($programCounts) && !empty($programCounts)) {
            foreach ($programCounts as $row) {
                // Handle both object and array formats safely
                $fullName = '';
                $count = 0;
                
                if (is_object($row)) {
                    $fullName = $row->program ?? $row->Thesis_Course ?? 'Unknown';
                    $count = (int)($row->thesis_count ?? 0);
                } elseif (is_array($row)) {
                    $fullName = $row['program'] ?? $row['Thesis_Course'] ?? 'Unknown';
                    $count = (int)($row['thesis_count'] ?? 0);
                }

                $displayName = $shortNames[$fullName] ?? 'Other';

                if ($count > 0) {
                    $data[] = [
                        'program' => $displayName,
                        'thesis_count' => $count
                    ];
                }
            }
        }

        // Sort by count descending
        usort($data, function($a, $b) {
            return ($b['thesis_count'] ?? 0) <=> ($a['thesis_count'] ?? 0);
        });

        // Fallback if no data
        if (empty($data)) {
            error_log("No thesis data found, using fallback");
            $data[] = ['program' => 'No Theses', 'thesis_count' => 0];
        }

        error_log("Final bar chart data: " . print_r($data, true));
        return $data;
        
    } catch (Exception $e) {
        error_log("Bar chart error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [['program' => 'Error', 'thesis_count' => 0]];
    }
}

/**
 * Generate pie chart as a table for PDF
 */
private function generatePieChartTable($pieChartData) {
    // Ensure pieChartData is an array
    if (!is_array($pieChartData)) {
        error_log("Pie chart table data is not array: " . gettype($pieChartData));
        $pieChartData = [];
    }
    
    $html = '<table class="chart-table">';
    $html .= '<thead><tr><th>User Role</th><th>User Count</th><th>Percentage</th></tr></thead><tbody>';
    
    $totalStudents = 0;
    
    // Safely calculate total
    if (is_array($pieChartData)) {
        foreach ($pieChartData as $item) {
            if (is_array($item)) {
                $totalStudents += $item['value'] ?? 0;
            }
        }
    }
    
    // Safely generate rows
    if (is_array($pieChartData)) {
        foreach ($pieChartData as $item) {
            if (!is_array($item)) continue;
            
            $percentage = $totalStudents > 0 ? round(($item['value'] / $totalStudents) * 100, 1) : 0;
            $html .= '
                <tr>
                    <td>
                        <span class="color-swatch" style="background-color: ' . ($item['color'] ?? '#cccccc') . '"></span>
                        ' . htmlspecialchars($item['label'] ?? 'Unknown') . '
                    </td>
                    <td>' . ($item['value'] ?? 0) . '</td>
                    <td>' . $percentage . '%</td>
                </tr>';
        }
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

private function generateBarChartTable($barChartData) {
    // Ensure barChartData is an array
    if (!is_array($barChartData)) {
        error_log("Bar chart table data is not array: " . gettype($barChartData));
        $barChartData = [];
    }
    
    $html = '<table class="chart-table">';
    $html .= '<thead><tr><th>Program</th><th>Thesis Count</th></tr></thead><tbody>';
    
    $totalTheses = 0;
    
    // Safely generate rows
    if (is_array($barChartData)) {
        foreach ($barChartData as $item) {
            if (!is_array($item)) continue;
            
            $count = (int)($item['thesis_count'] ?? 0);
            $program = $item['program'] ?? 'Unknown';
            $totalTheses += $count;
            
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($program) . '</td>
                    <td>' . $count . '</td>
                </tr>';
        }
    }
    
    $html .= '
        <tr style="font-weight: bold; background-color: #e9ecef;">
            <td>Total Theses</td>
            <td>' . $totalTheses . '</td>
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


/**
 * Generate PDF report for system logs
 */
private function generateLogsReport() {
    try {
        error_log("=== GENERATE LOGS REPORT METHOD CALLED ===");
        
        $logType = $_GET['log_type'] ?? 'all';
        $filter = $_GET['filter'] ?? 'all';
        $page = $_GET['page'] ?? 1;
        
        error_log("Processing log type: " . $logType . ", filter: " . $filter . ", page: " . $page);
        
        // Get log data based on type and filter
        $logData = $this->getLogsForReport($logType, $filter, $page);
        error_log("Log data retrieved successfully");
        
        // Check if dompdf is available
        if (!class_exists('Dompdf\Dompdf')) {
            throw new Exception('Dompdf library not found. Please install via composer: composer require dompdf/dompdf');
        }
        
        // Generate HTML content
        $html = $this->generateLogsReportHTML($logData, $logType, $filter, $page);
        
        // Configure dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // Generate filename
        $timestamp = date('Y-m-d_H-i-s');
        $logTypeName = $this->getLogTypeDisplayName($logType);
        $filename = "System_Logs_{$logTypeName}_{$timestamp}.pdf";
        
        // Output the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        
        echo $dompdf->output();
        exit;
        
    } catch (Exception $e) {
        error_log("PDF Logs Generation Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $this->jsonResponse([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'PDF logs generation failed'
        ], 500);
    }
}

/**
 * Get logs data for PDF report
 */
private function getLogsForReport($logType, $filter, $page) {
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    try {
        $db = new Database();
        $logs = [];
        $totalLogs = 0;
        
        // Build query based on log type and filter
        $sql = "SELECT al.*, ui.First_Name, ui.Last_Name, ui.Email, ui.User_Role 
                FROM AUDIT_LOGS al 
                LEFT JOIN USER_INFORMATION ui ON al.user_id = ui.ID 
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters based on log type
        switch($logType) {
            case 'user':
                $sql .= " AND al.table_name = 'USER_INFORMATION'";
                break;
            case 'admin':
                $sql .= " AND (al.table_name IN ('ANNOUNCEMENTS', 'SYSTEM_LOGS', 'THESIS', 'BACKUP_LOGS') OR ui.User_Role IN ('admin', 'superAdmin', 'SubAdmin'))";
                break;
            case 'all':
            default:
                // No additional filters for 'all'
                break;
        }
        
        // Apply specific filters
        if ($filter !== 'all') {
            switch($filter) {
                case 'login':
                    $sql .= " AND al.table_name = 'LOGIN_ATTEMPTS'";
                    break;
                case 'user':
                    $sql .= " AND al.table_name = 'USER_INFORMATION'";
                    break;
                case 'thesis':
                    $sql .= " AND al.table_name = 'THESIS'";
                    break;
                case 'announcement':
                    $sql .= " AND al.table_name = 'ANNOUNCEMENTS'";
                    break;
                case 'backup':
                    $sql .= " AND al.table_name = 'BACKUP_LOGS'";
                    break;
                case 'management':
                    $sql .= " AND al.table_name = 'USER_INFORMATION' AND al.action IN ('INSERT', 'UPDATE', 'DELETE')";
                    break;
            }
        }
        
        // Count total logs
        $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_table";
        $db->query($countSql);
        foreach ($params as $key => $value) {
            $db->bind($key, $value);
        }
        $countResult = $db->single();
        $totalLogs = $countResult->total;
        
        // Get logs with pagination
        $sql .= " ORDER BY al.changed_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $db->query($sql);
        foreach ($params as $key => $value) {
            $db->bind($key, $value);
        }
        
        $logs = $db->resultSet();
        
        return [
            'logs' => $logs,
            'total_logs' => $totalLogs,
            'current_page' => $page,
            'total_pages' => ceil($totalLogs / $limit),
            'logs_per_page' => $limit
        ];
        
    } catch (Exception $e) {
        error_log("Error getting logs for report: " . $e->getMessage());
        return [
            'logs' => [],
            'total_logs' => 0,
            'current_page' => $page,
            'total_pages' => 0,
            'logs_per_page' => $limit
        ];
    }
}

/**
 * Generate HTML content for logs PDF report
 */
private function generateLogsReportHTML($logData, $logType, $filter, $page) {
    $logTypeName = $this->getLogTypeDisplayName($logType);
    $filterName = $this->getFilterDisplayName($filter);
    $currentDate = date('F j, Y g:i A');
    
    $logs = $logData['logs'] ?? [];
    $totalLogs = $logData['total_logs'] ?? 0;
    $currentPage = $logData['current_page'] ?? 1;
    $totalPages = $logData['total_pages'] ?? 1;
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>System Logs Report</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                color: #333;
                line-height: 1.4;
                font-size: 10px;
            }
            .header { 
                text-align: center; 
                border-bottom: 3px solid #ba1e1f; 
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .header h1 { 
                color: #ba1e1f; 
                margin: 0; 
                font-size: 20px;
            }
            .header h2 { 
                color: #666; 
                margin: 5px 0; 
                font-size: 14px;
                font-weight: normal;
            }
            .report-info {
                display: flex;
                justify-content: space-between;
                margin: 15px 0;
                padding: 10px;
                background: #f8f9fa;
                border-radius: 5px;
            }
            .info-item {
                text-align: center;
            }
            .info-label {
                font-size: 9px;
                color: #666;
                display: block;
            }
            .info-value {
                font-size: 11px;
                font-weight: bold;
                color: #ba1e1f;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                font-size: 9px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 6px;
                text-align: left;
                word-wrap: break-word;
            }
            th {
                background-color: #ba1e1f;
                color: white;
                font-weight: bold;
                font-size: 8px;
            }
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .timestamp {
                font-size: 8px;
                color: #666;
            }
            .user-info {
                font-weight: bold;
            }
            .user-role {
                font-size: 8px;
                color: #666;
            }
            .action {
                font-weight: bold;
            }
            .action-login { color: #28a745; }
            .action-create { color: #007bff; }
            .action-update { color: #ffc107; }
            .action-delete { color: #dc3545; }
            .footer {
                margin-top: 20px;
                text-align: center;
                color: #666;
                font-size: 8px;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
            .page-info {
                text-align: right;
                font-size: 9px;
                color: #666;
                margin-bottom: 10px;
            }
            .no-logs {
                text-align: center;
                padding: 30px;
                color: #666;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>System Logs Report</h1>
            <h2>' . htmlspecialchars($logTypeName) . ' - ' . htmlspecialchars($filterName) . '</h2>
            <p><strong>Generated on:</strong> ' . $currentDate . '</p>
        </div>
        
        <div class="report-info">
            <div class="info-item">
                <span class="info-label">Total Logs</span>
                <span class="info-value">' . $totalLogs . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Current Page</span>
                <span class="info-value">' . $currentPage . ' of ' . $totalPages . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Logs Per Page</span>
                <span class="info-value">50</span>
            </div>
        </div>
        
        <div class="page-info">
            Page ' . $currentPage . ' of ' . $totalPages . '
        </div>';
        
    if (empty($logs)) {
        $html .= '
        <div class="no-logs">
            <h3>No Logs Found</h3>
            <p>No system logs available for the selected criteria.</p>
        </div>';
    } else {
        $html .= '
        <table>
            <thead>
                <tr>
                    <th style="width: 15%">Time & IP</th>
                    <th style="width: 15%">User</th>
                    <th style="width: 15%">Action</th>
                    <th style="width: 55%">Details</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($logs as $log) {
            $timestamp = date('M j, Y H:i:s', strtotime($log->changed_at));
            $ip = $log->ip_address ?? 'N/A';
            
            $userName = 'System';
            if ($log->First_Name && $log->Last_Name) {
                $userName = $log->First_Name . ' ' . $log->Last_Name;
            } elseif ($log->Email) {
                $userName = $log->Email;
            }
            
            $userRole = $this->formatUserRoleForReport($log->User_Role ?? 'System');
            $action = $this->formatActionForReport($log->action ?? 'Unknown');
            $details = $this->formatDetailsForReport($log);
            
            $actionClass = $this->getActionClassForReport($log->action);
            
            $html .= '
                <tr>
                    <td>
                        <div class="timestamp">' . $timestamp . '</div>
                        <strong>IP:</strong> ' . $ip . '
                    </td>
                    <td>
                        <div class="user-info">' . htmlspecialchars($userName) . '</div>
                        <div class="user-role">' . htmlspecialchars($userRole) . '</div>
                    </td>
                    <td>
                        <span class="action ' . $actionClass . '">' . htmlspecialchars($action) . '</span>
                    </td>
                    <td>' . htmlspecialchars($details) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>';
    }
    
    $html .= '
        <div class="footer">
            <p><strong>Generated by Compendium System | University of Southeastern Philippines</strong></p>
            <p>This is an automated system logs report. For questions, contact system administrator.</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Helper methods for report generation
 */
private function getLogTypeDisplayName($logType) {
    $types = [
        'all' => 'All Logs',
        'user' => 'User Logs', 
        'admin' => 'Admin Logs'
    ];
    return $types[$logType] ?? 'All Logs';
}

private function getFilterDisplayName($filter) {
    $filters = [
        'all' => 'All Activities',
        'login' => 'Logins',
        'user' => 'User Management',
        'thesis' => 'Thesis',
        'announcement' => 'Announcements',
        'backup' => 'Backup',
        'management' => 'Management'
    ];
    return $filters[$filter] ?? 'All Activities';
}

private function formatUserRoleForReport($role) {
    $roleMap = [
        'superAdmin' => 'Super Admin',
        'admin' => 'Administrator',
        'SubAdmin' => 'Sub-Admin',
        'faculty' => 'Faculty',
        'student' => 'Student'
    ];
    return $roleMap[$role] ?? $role;
}

private function formatActionForReport($action) {
    $actionMap = [
        'INSERT'  => 'Created',
        'UPDATE'  => 'Updated',
        'DELETE'  => 'Deleted',
        'LOGIN'   => 'Login',
        'LOGOUT'  => 'Logout',
        'APPROVE' => 'Approved',
        'REJECT'  => 'Rejected'
    ];

    return $actionMap[$action] ?? 'System Action';
}

private function formatDetailsForReport($log) {
    if (!$log) return 'Unknown operation';

    $table = $log->table_name ?? '';
    $action = $log->action ?? '';

    $user = trim(($log->First_Name ?? '') . ' ' . ($log->Last_Name ?? ''));
    if (!$user || $user === ' ') $user = $log->Email ?? 'Unknown User';

    $map = [
        'USER_INFORMATION' => "User account '{$user}' was {$this->formatActionForReport($action)}",
        'THESIS'           => "Thesis record was {$this->formatActionForReport($action)}",
        'ANNOUNCEMENTS'    => "Announcement was {$this->formatActionForReport($action)}",
        'LOGIN_ATTEMPTS'   => $action === 'LOGIN' ? 'User login attempt' : 'Authentication event'
    ];

    return $map[$table] ?? "Database operation on {$table}";
}

private function getActionClassForReport($action) {
    $actionMap = [
        'INSERT' => 'action-create',
        'UPDATE' => 'action-update',
        'DELETE' => 'action-delete',
        'LOGIN' => 'action-login'
    ];
    return $actionMap[$action] ?? '';
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