<?php
// AdminDashboardController.php
require_once __DIR__ . '/../Models/AdminDashboardModel.php';
require_once __DIR__ . '/../Models/Model.php';

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
            case 'dashboard':
            default:
                $this->showDashboard();
                break;
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

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            $this->jsonResponse(['error' => 'Missing user ID'], 400);
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
            $this->validateCsrfToken();
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
        $this->validateCsrfToken();
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
            $this->validateCsrfToken();

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
    private function validateCsrfToken() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new Exception("Invalid CSRF token");
        }
    }

    /**
     * Send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Handle the request if this file is called directly
if (basename($_SERVER['PHP_SELF']) === 'AdminDashboardController.php') {
    $controller = new AdminDashboardController();
    $controller->handleRequest();
}
?>