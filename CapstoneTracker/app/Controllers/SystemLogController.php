<?php
// app/Controllers/SystemLogController.php

class SystemLogController {
    private $systemLogModel;

    public function __construct() {
        $this->systemLogModel = new SystemLog();
    }

    /**
     * Main controller method to handle different actions
     */
    public function handleRequest() {
        if (!isset($_GET['action'])) {
            return $this->jsonResponse(false, 'No action specified');
        }

        $action = $_GET['action'];

        switch ($action) {
            case 'getUserLogs':
                $this->getUserLogs();
                break;
            case 'getAdminLogs':
                $this->getAdminLogs();
                break;
            case 'exportUserLogs':
                $this->exportUserLogs();
                break;
            case 'exportAdminLogs':
                $this->exportAdminLogs();
                break;
            case 'getLogStatistics':
                $this->getLogStatistics();
                break;
            case 'cleanupLogs':
                $this->cleanupLogs();
                break;
            default:
                $this->jsonResponse(false, 'Invalid action');
                break;
        }
    }

    /**
     * Get user logs with pagination and filtering
     */
    private function getUserLogs() {
        try {
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            
            $filters = [];
            if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['action']) && !empty($_GET['action'])) {
                $filters['action'] = $_GET['action'];
            }
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }

            $logs = $this->systemLogModel->getUserLogs($page, $limit, $filters);
            $total = $this->systemLogModel->getLogsCount('user', $filters);

            $this->jsonResponse(true, 'User logs retrieved successfully', [
                'logs' => $logs,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_records' => $total,
                    'limit' => $limit
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(false, 'Failed to retrieve user logs: ' . $e->getMessage());
        }
    }

    /**
     * Get admin logs with pagination and filtering
     */
    private function getAdminLogs() {
        try {
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            
            $filters = [];
            if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['action']) && !empty($_GET['action'])) {
                $filters['action'] = $_GET['action'];
            }
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }

            $logs = $this->systemLogModel->getAdminLogs($page, $limit, $filters);
            $total = $this->systemLogModel->getLogsCount('admin', $filters);

            $this->jsonResponse(true, 'Admin logs retrieved successfully', [
                'logs' => $logs,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_records' => $total,
                    'limit' => $limit
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(false, 'Failed to retrieve admin logs: ' . $e->getMessage());
        }
    }

    /**
     * Export user logs to CSV
     */
    private function exportUserLogs() {
        try {
            $filters = [];
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['action']) && !empty($_GET['action'])) {
                $filters['action'] = $_GET['action'];
            }

            $this->systemLogModel->exportLogsToCSV('user', $filters);

        } catch (Exception $e) {
            $this->jsonResponse(false, 'Failed to export user logs: ' . $e->getMessage());
        }
    }

    /**
     * Export admin logs to CSV
     */
    private function exportAdminLogs() {
        try {
            $filters = [];
            if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            if (isset($_GET['action']) && !empty($_GET['action'])) {
                $filters['action'] = $_GET['action'];
            }

            $this->systemLogModel->exportLogsToCSV('admin', $filters);

        } catch (Exception $e) {
            $this->jsonResponse(false, 'Failed to export admin logs: ' . $e->getMessage());
        }
    }

    /**
     * Get log statistics
     */
    private function getLogStatistics() {
        try {
            $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
            $statistics = $this->systemLogModel->getLogStatistics($days);

            $this->jsonResponse(true, 'Log statistics retrieved successfully', [
                'statistics' => $statistics
            ]);

        } catch (Exception $e) {
            $this->jsonResponse(false, 'Failed to retrieve log statistics: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old logs
     */
    private function cleanupLogs() {
        try {
            $result = $this->systemLogModel->cleanupOldLogs();
            
            if ($result) {
                $this->jsonResponse(true, 'Old logs cleaned up successfully');
            } else {
                $this->jsonResponse(false, 'Failed to clean up old logs');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, 'Failed to clean up logs: ' . $e->getMessage());
        }
    }

    /**
     * Static method to create log entries from anywhere in the application
     */
    public static function log($data) {
        try {
            $systemLog = new SystemLog();
            return $systemLog->createLog($data);
        } catch (Exception $e) {
            error_log('Failed to create system log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper method for JSON responses
     */
    private function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Initialize and handle request if accessed directly
if (isset($_GET['action']) && basename($_SERVER['PHP_SELF']) == 'SystemLogController.php') {
    $controller = new SystemLogController();
    $controller->handleRequest();
}
?>