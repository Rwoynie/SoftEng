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
        
        // Prevent any output
        ob_start();
        
        require_once '../Models/Database.php';
        require_once '../Models/Backup.php';
        
        $db = new Database();
        $this->backupModel = new Backup($db);
        
        // Clean any potential output
        ob_clean();
    }

    public function handleRequest() {
        // Clear any output buffers completely
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Start fresh output buffer
        ob_start();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is admin
        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Access denied']);
        }

        // Verify CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Invalid CSRF token']);
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
                    $this->sendJsonResponse(['success' => false, 'error' => 'Invalid action']);
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    private function sendJsonResponse($data) {
        // Clean any output
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        echo json_encode($data);
        exit;
    }

    private function downloadBackup() {
        try {
            $backupFileName = $_GET['file'] ?? '';
            if (empty($backupFileName)) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Backup file name is required']);
            }
    
            // Verify CSRF token for GET requests too
            if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Invalid CSRF token']);
            }
    
            $backupFilePath = $this->backupModel->getBackupPath() . $backupFileName;
            
            if (!file_exists($backupFilePath)) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Backup file not found']);
            }
    
            // Clean output for file download
            while (ob_get_level()) {
                ob_end_clean();
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
            $this->sendJsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function createBackup() {
        $result = $this->backupModel->createBackup();
        
        if ($result['success']) {
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup_file' => $result['file_name'],
                'size' => $result['size_formatted'],
                'timestamp' => $result['timestamp']
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'error' => $result['error']]);
        }
    }

    private function restoreBackup() {
        $backupFileName = $_POST['backup_file'] ?? '';
        
        if (empty($backupFileName)) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Backup file name is required']);
        }

        $result = $this->backupModel->restoreBackup($backupFileName);
        if ($result['success']) {
            $this->sendJsonResponse([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'error' => $result['error']]);
        }
    }

    private function getBackupHistory() {
        $backups = $this->backupModel->getBackupHistory();
        $this->sendJsonResponse(['success' => true, 'backups' => $backups]);
    }

    private function deleteBackup() {
        $backupFileName = $_POST['backup_file'] ?? '';
        
        if (empty($backupFileName)) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Backup file name is required']);
        }

        $result = $this->backupModel->deleteBackup($backupFileName);
        
        if ($result['success']) {
            $this->sendJsonResponse([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'error' => $result['error']]);
        }
    }

    private function getBackupInfo() {
        $info = $this->backupModel->getBackupInfo();
        $this->sendJsonResponse(['success' => true, 'info' => $info]);
    }
}

// Only run if this is a direct request to the controller
if (isset($_POST['action']) || (isset($_GET['action']) && $_GET['action'] !== '')) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $backupController = new BackupController();
    $backupController->handleRequest();
}
?>