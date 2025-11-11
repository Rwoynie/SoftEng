<?php
// Ensure clean JSON-only responses for public/guest endpoints
while (ob_get_level()) { ob_end_clean(); }
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
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
            $requiredFields = ['thesistitle', 'thesisadviser', 'thesisauthor', 'department', 'course', 'hardbound'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst($field) . ' is required');
                }
            }

            // Check for duplicate title
            $title = trim($_POST['thesistitle']);
            if ($this->thesisModel->titleExists($title)) {
                throw new Exception('A thesis with this title already exists. Please choose a different title.');
            }
            
            // Check for both abstract and thesis files
            if (empty($_FILES['abstract_file']) || $_FILES['abstract_file']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Please select an abstract file to upload');
            }
            
            if (empty($_FILES['thesis_file']) || $_FILES['thesis_file']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Please select a thesis file to upload');
            }

            $userId = $_SESSION['user_db_id'];
            $postData = [
                'thesistitle' => trim($_POST['thesistitle']),
                'thesisauthor' => trim($_POST['thesisauthor']),
                'thesisadviser' => trim($_POST['thesisadviser']),
                'department' => trim($_POST['department']),
                'course' => trim($_POST['course']),
                'hardbound' => trim($_POST['hardbound'])
            ];
            
            // Prepare files array for the model
            $files = [
                'abstract_file' => $_FILES['abstract_file'],
                'thesis_file' => $_FILES['thesis_file']
            ];
            
            // Upload thesis using the model
            $success = $this->thesisModel->uploadThesis($postData, $files, $userId);
            
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
            if (ob_get_length()) { ob_clean(); }
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
            if (ob_get_length()) { ob_clean(); }
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
            if (ob_get_length()) { ob_clean(); }
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
            if (ob_get_length()) { ob_clean(); }
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
                if (ob_get_length()) { ob_clean(); }
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'theses' => []
                ]);
                return;
            }

            $theses = $this->adminModel->searchTheses($searchTerm);
            if (ob_get_length()) { ob_clean(); }
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
     * Edit thesis - load data for editing
     */
    public function editThesis() {
        // COMPLETELY CLEAR EVERYTHING first
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set headers IMMEDIATELY
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        try {
            // Debug log
            error_log("EDIT THESIS CALLED - ID: " . ($_GET['id'] ?? 'NULL'));
            
            // Check permissions
            if (!isset($_SESSION['user_db_id'])) {
                throw new Exception('User not authenticated');
            }
            
            $thesisId = $_GET['id'] ?? null;
            
            if (!$thesisId) {
                throw new Exception('Thesis ID is required');
            }
            
            // Get thesis details
            $thesis = $this->thesisModel->findById($thesisId);
            
            if (!$thesis) {
                throw new Exception('Thesis not found for ID: ' . $thesisId);
            }
            
            // Check if user is admin
            if (!($_SESSION['is_admin'] ?? false)) {
                throw new Exception('Admin privileges required');
            }
            
            // Prepare response data
            $response = [
                'success' => true,
                'thesis' => [
                    'ID' => $thesis->ID,
                    'Title' => $thesis->Title,
                    'Thesis_Email' => $thesis->Thesis_Email,
                    'Adviser' => $thesis->Adviser,
                    'Thesis_Department' => $thesis->Thesis_Department,
                    'Thesis_Course' => $thesis->Thesis_Course,
                    'Author' => $thesis->Author,
                    'HardBound_Available' => $thesis->HardBound_Available
                ]
            ];
            
            // Output and exit IMMEDIATELY
            echo json_encode($response);
            exit;
            
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'error' => $e->getMessage()
            ];
            
            echo json_encode($errorResponse);
            exit;
        }
    }

    /**
     * Update thesis - FIXED VERSION
     */
    public function updateThesis() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check permissions
        if (!isset($_SESSION['user_db_id'])) {
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
            // Clear any previous output
            if (ob_get_length()) {
                ob_clean();
            }
            
            // Handle both FormData and JSON input
            $input = [];
            
            if (!empty($_POST['thesis_id'])) {
                // FormData submission (with potential files)
                $input = $_POST;
            } else {
                // JSON submission
                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }
            }
            
            if (!isset($input['thesis_id'])) {
                throw new Exception('Thesis ID is required');
            }
            
            $thesisId = (int)$input['thesis_id'];
            
            // Get thesis details first to check ownership
            $thesis = $this->thesisModel->findById($thesisId);
            
            if (!$thesis) {
                throw new Exception('Thesis not found');
            }
            
            // Check if user owns the thesis or is admin
            if (!$_SESSION['is_admin'] && $thesis->User_ID != $_SESSION['user_db_id']) {
                throw new Exception('You can only edit your own theses');
            }
            
            // Prepare data for update
            $postData = [
                'thesistitle' => $input['thesistitle'] ?? '',
                'thesisauthor' => $input['thesisauthor'] ?? '',
                'thesisadviser' => $input['thesisadviser'] ?? '',
                'department' => $input['department'] ?? '',
                'course' => $input['course'] ?? '',
                'hardbound' => $input['hardbound'] ?? 'Yes' 
            ];
            
            // Handle file uploads if provided
            $files = [];
            if (!empty($_FILES['abstract_file']['name'])) {
                $files['abstract_file'] = $_FILES['abstract_file'];
            }
            if (!empty($_FILES['thesis_file']['name'])) {
                $files['thesis_file'] = $_FILES['thesis_file'];
            }
            
            // Update thesis - pass files only if they exist
            $success = $this->thesisModel->updateThesis($thesisId, $postData, !empty($files) ? $files : null);
            
            // Ensure we only output JSON
            header('Content-Type: application/json');
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Thesis updated successfully!'
                ]);
            } else {
                throw new Exception($this->thesisModel->getError() ?: 'Failed to update thesis');
            }
            
        } catch (Exception $e) {
            // Ensure we only output JSON even for errors
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    

    /**
     * Delete thesis
     */
    public function deleteThesis() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check permissions
        if (!isset($_SESSION['user_db_id'])) {
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
            
            // Get thesis details first to check ownership
            $thesis = $this->thesisModel->findById($thesisId);
            
            if (!$thesis) {
                throw new Exception('Thesis not found');
            }
            
            // Check if user owns the thesis or is admin
            if (!$_SESSION['is_admin'] && $thesis->User_ID != $_SESSION['user_db_id']) {
                throw new Exception('You can only delete your own theses');
            }
            
            // Delete thesis
            $success = $this->thesisModel->deleteThesis($thesisId);
            
            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Thesis deleted successfully'
                ]);
            } else {
                throw new Exception($this->thesisModel->getError() ?: 'Failed to delete thesis');
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
     * View full thesis file (viewing only)
     */
    public function viewThesis() {
        try {
            // Get thesis ID from request
            $thesisId = $_GET['id'] ?? null;
            
            if (!$thesisId) {
                throw new Exception('Thesis ID is required');
            }
    
            $thesisModel = new Thesis();
            
            // Get thesis file data
            $thesisData = $thesisModel->getThesisFile($thesisId);
            
            if (!$thesisData || empty($thesisData->Thesis_File)) {
                throw new Exception('Thesis file not found');
            }
    
            // Set headers for PDF viewing only (no download)
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="view_only.pdf"'); // Generic filename
            header('Content-Length: ' . strlen($thesisData->Thesis_File));
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Additional headers to prevent downloading
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            
            // For extra security, you can add these headers
            header('Content-Security-Policy: default-src \'self\'');
            
            // Output the file content
            echo $thesisData->Thesis_File;
            
        } catch (Exception $e) {
            error_log("Error in viewThesis: " . $e->getMessage());
            http_response_code(404);
            echo "Thesis file not available for viewing: " . $e->getMessage();
        }
    }

/**
 * Get thesis view information for preview
 
public function getThesisViewInfo($thesisId) {
    try {
        $thesisModel = new Thesis();
        
        $viewInfo = $thesisModel->getThesisViewInfo($thesisId);
        
        if (!$viewInfo) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Thesis not found'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'thesis' => $viewInfo
        ]);
        
    } catch (Exception $e) {
        error_log("Error in getThesisViewInfo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Internal server error'
        ]);
    }
}
 */
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
     * Serve abstract file from BLOB
     */
    public function serveAbstractFile() {
        try {
            $thesisId = $_GET['id'] ?? null;
            
            if (!$thesisId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Thesis ID is required']);
                return;
            }
            
            $abstractFile = $this->thesisModel->getAbstractFile($thesisId);
            
            if (!$abstractFile || empty($abstractFile->Thesis_AbstractFile)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Abstract file not found']);
                return;
            }
            
            // Set appropriate headers for PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="abstract_' . $thesisId . '.pdf"');
            header('Content-Length: ' . strlen($abstractFile->Thesis_AbstractFile));
            
            // Output the abstract BLOB data
            echo $abstractFile->Thesis_AbstractFile;
            exit;
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to serve abstract file: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if thesis title exists
     */
    public function checkTitleExists() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $title = $input['title'] ?? '';
            
            if (empty($title)) {
                echo json_encode(['exists' => false]);
                return;
            }
            
            $exists = $this->thesisModel->titleExists($title);
            
            echo json_encode([
                'success' => true,
                'exists' => $exists
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to check title: ' . $e->getMessage()
            ]);
        }
    }
    
    public function debugThesis() {
        header('Content-Type: application/json');
        
        $thesisId = $_GET['id'] ?? null;
        echo json_encode([
            'debug' => true,
            'thesis_id' => $thesisId,
            'session' => isset($_SESSION['user_db_id']),
            'is_admin' => $_SESSION['is_admin'] ?? false
        ]);
        exit;
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
            case 'downloadAbstract': 
                $this->serveAbstractFile();
                break;
            case 'checkTitleExists': 
                $this->checkTitleExists();
                break;    
            case 'editThesis': 
                $this->editThesis();
                break;
            case 'updateThesis': 
                $this->updateThesis();
                break;
            case 'deleteThesis': 
                $this->deleteThesis();
                break;
            case 'viewThesis':
                $this->viewThesis();
                break;       
            default:
                if (ob_get_length()) { ob_clean(); }
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
