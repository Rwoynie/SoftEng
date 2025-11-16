<?php
// app/Controllers/BackupController.php

// Turn off error display but log them
error_reporting(E_ALL);
ini_set('display_errors', 0);

class BackupController {
    private $backupModel;

    public function __construct() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Calculate the correct base path
 //       $basePath = dirname(__FILE__) . '/../../'; // Goes up 2 levels from app/Controllers to CapstoneTracker
        
        require_once '../Models/Database.php';
        require_once '../Models/Backup.php';
        
        $db = new Database();
        $this->backupModel = new Backup($db);
    }

    public function handleRequest() {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set JSON header
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is admin
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }

        // Verify CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
                exit;
            }
        }

        $action = $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'create_backup':
                    $this->createBackup();
                    break;
                case 'download':
                    $this->downloadBackup();
                    break;    
                case 'restore_backup':
                    $this->restoreBackup();
                    break;
                case 'get_backup_history':
                    $this->getBackupHistory();
                    break;
                case 'delete_backup':
                    $this->deleteBackup();
                    break;
                case 'get_backup_info':
                    $this->getBackupInfo();
                    break;
                default:
                    echo json_encode(['success' => false, 'error' => 'Invalid action']);
                    exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
            exit;
        }
    }

    private function downloadBackup() {
        try {
            $backupFileName = $_GET['file'] ?? '';
            
            if (empty($backupFileName)) {
                echo json_encode(['success' => false, 'error' => 'Backup file name is required']);
                exit;
            }
    
            // Verify CSRF token for GET requests too
            if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
                exit;
            }
    
            $backupFilePath = $this->backupModel->getBackupPath() . $backupFileName;
            
            if (!file_exists($backupFilePath)) {
                echo json_encode(['success' => false, 'error' => 'Backup file not found']);
                exit;
            }
    
            // Set headers for file download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backupFileName . '"');
            header('Content-Length: ' . filesize($backupFilePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: 0');
            
            readfile($backupFilePath);
            exit;
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    private function createBackup() {
        $result = $this->backupModel->createBackup();
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup_file' => $result['file_name'],
                'size' => $result['size_formatted'],
                'timestamp' => $result['timestamp']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
        exit;
    }

    private function restoreBackup() {
        $backupFileName = $_POST['backup_file'] ?? '';
        
        if (empty($backupFileName)) {
            echo json_encode(['success' => false, 'error' => 'Backup file name is required']);
            exit;
        }

        $result = $this->backupModel->restoreBackup($backupFileName);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
        exit;
    }

    private function getBackupHistory() {
        $backups = $this->backupModel->getBackupHistory();
        echo json_encode(['success' => true, 'backups' => $backups]);
        exit;
    }

    private function deleteBackup() {
        $backupFileName = $_POST['backup_file'] ?? '';
        
        if (empty($backupFileName)) {
            echo json_encode(['success' => false, 'error' => 'Backup file name is required']);
            exit;
        }

        $result = $this->backupModel->deleteBackup($backupFileName);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
        exit;
    }

    private function getBackupInfo() {
        $info = $this->backupModel->getBackupInfo();
        echo json_encode(['success' => true, 'info' => $info]);
        exit;
    }
}

// Only run if this is a direct request to the controller
if (isset($_POST['action']) || (isset($_GET['action']) && $_GET['action'] !== '')) {
    // Clear any previous output
    if (ob_get_length()) {
        ob_clean();
    }
    $backupController = new BackupController();
    $backupController->handleRequest();
}
?>