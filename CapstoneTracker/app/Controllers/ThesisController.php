<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/Thesis.php';
require_once __DIR__ . '/../Models/AdminDashboardModel.php';

class ThesisController {
    private $thesisModel;
    private $adminModel;
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->thesisModel = new Thesis($this->db);
        $this->adminModel = new AdminDashboardModel();
    }
    
    /**
     * Handle thesis upload
     */
    public function uploadThesis() {
       
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'You must be logged in to upload a thesis']);
            return;
        }
        
        // Check if user has permission to upload
        if (!isset($_SESSION['user_db_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid user session']);
            return;
        }
        
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        try {
            // Validate required fields
            $requiredFields = ['thesistitle', 'thesisauthor', 'department', 'course'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst($field) . ' is required');
                }
            }
            
            if (empty($_FILES['files']) || $_FILES['files']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Please select a file to upload');
            }

            $userId = $_SESSION['user_db_id'];
            $postData = [
                'thesistitle' => trim($_POST['thesistitle']),
                'thesisauthor' => trim($_POST['thesisauthor']),
                'department' => trim($_POST['department']),
                'course' => trim($_POST['course'])
            ];
            
            // Upload thesis using the model
            $success = $this->thesisModel->uploadThesis($postData, $_FILES, $userId);
            
            if ($success) {
                // Log the upload activity
                $this->logUploadActivity($userId, $postData['thesistitle']);
                
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Thesis uploaded successfully!',
                    'title' => $postData['thesistitle']
                ]);
            } else {
                throw new Exception($this->thesisModel->getError() ?: 'Failed to upload thesis');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get all theses for display
     */
    public function getAllTheses() {
        try {
            $theses = $this->thesisModel->getAllTheses();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'theses' => $theses
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch theses: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get user's theses
     */
    public function getUserTheses() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_db_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'User not authenticated']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_db_id'];
            $theses = $this->thesisModel->getUserTheses($userId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'theses' => $theses
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch user theses: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Delete a thesis
     */
    public function deleteThesis() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check permissions
        if (!isset($_SESSION['user_db_id']) && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['thesis_id'])) {
                throw new Exception('Thesis ID is required');
            }
            
            $thesisId = (int)$input['thesis_id'];
            
            // Get thesis details first to check ownership and file path
            $thesis = $this->thesisModel->findById($thesisId);
            
            if (!$thesis) {
                throw new Exception('Thesis not found');
            }
            
            // Check if user owns the thesis or is admin
            if (!$_SESSION['is_admin'] && $thesis->User_ID != $_SESSION['user_db_id']) {
                throw new Exception('You can only delete your own theses');
            }
            
            // Delete the file from server
            if (file_exists($thesis->File_Path)) {
                unlink($thesis->File_Path);
            }
            
            // Delete from database
            $success = $this->thesisModel->delete($thesisId);
            
            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Thesis deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete thesis from database');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get theses by department
     */
    public function getThesesByDepartment() {
        try {
            $department = $_GET['department'] ?? null;
            
            if (!$department || $department === 'all') {
                $theses = $this->thesisModel->getAllTheses();
            } else {
                $theses = $this->adminModel->getThesesByDepartment($department);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'theses' => $theses
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch theses by department: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get recent theses
     */
    public function getRecentTheses() {
        try {
            $theses = $this->adminModel->getRecentTheses();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'theses' => $theses
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch recent theses: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Search theses
     */
    public function searchTheses() {
        try {
            $searchTerm = $_GET['q'] ?? '';
            
            if (empty($searchTerm)) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'theses' => []
                ]);
                return;
            }

            $theses = $this->adminModel->searchTheses($searchTerm);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'theses' => $theses
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to search theses: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get thesis statistics
     */
    public function getThesisStatistics() {
        try {
            $stats = $this->adminModel->getSystemStatistics();
            
            // Extract thesis-related statistics
            $thesisStats = [
                'total_theses' => $stats['total_theses'] ?? 0,
                'theses_this_month' => $stats['theses_this_month'] ?? 0,
                'theses_this_week' => $stats['theses_this_week'] ?? 0,
                'departments' => $stats['departments'] ?? []
            ];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'statistics' => $thesisStats
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch thesis statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Serve thesis file from BLOB
     */
    public function serveThesisFile() {
        try {
            $thesisId = $_GET['id'] ?? null;
            
            if (!$thesisId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Thesis ID is required']);
                return;
            }
            
            $thesisFile = $this->thesisModel->getThesisFile($thesisId);
            
            if (!$thesisFile) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Thesis file not found']);
                return;
            }
            
            // Set appropriate headers for PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="thesis_' . $thesisId . '.pdf"');
            header('Content-Length: ' . strlen($thesisFile->Thesis_File));
            
            // Output the BLOB data
            echo $thesisFile->Thesis_File;
            exit;
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to serve file: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Log upload activity (placeholder for future implementation)
     */
    private function logUploadActivity($userId, $title) {
        // You can implement logging to a separate activity table here
        error_log("Thesis uploaded - User: $userId, Title: $title, Time: " . date('Y-m-d H:i:s'));
    }
    
    /**
     * Handle different actions
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'upload':
                $this->uploadThesis();
                break;
            case 'getAllTheses':
                $this->getAllTheses();
                break;
            case 'getUserTheses':
                $this->getUserTheses();
                break;
            case 'deleteThesis':
                $this->deleteThesis();
                break;
            case 'getThesesByDepartment':
                $this->getThesesByDepartment();
                break;
            case 'getRecentTheses':
                $this->getRecentTheses();
                break;
            case 'searchTheses':
                $this->searchTheses();
                break;
            case 'getThesisStatistics':
                $this->getThesisStatistics();
                break;
            case 'download':
                $this->serveThesisFile();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Action not found']);
                break;
        }
        ob_end_flush();
    }
}

// Handle the request if this file is called directly
if (isset($_GET['action'])) {
    $controller = new ThesisController();
    $controller->handleRequest();
    exit;
}
?>