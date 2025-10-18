<?php
// app/Models/SystemLog.php

class SystemLog {
    private $db;
    private $table = 'system_logs';

    // Log types
    const TYPE_USER = 'user';
    const TYPE_ADMIN = 'admin';
    const TYPE_SYSTEM = 'system';
    const TYPE_SECURITY = 'security';
    const TYPE_ERROR = 'error';

    // Log actions
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_UPLOAD = 'upload';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_ARCHIVE = 'archive';
    const ACTION_RESTORE = 'restore';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create a new system log entry
     */
    public function createLog($data) {
        $query = "INSERT INTO {$this->table} (user_id, user_type, action, description, ip_address, user_agent, log_type, resource_type, resource_id, additional_data) 
                  VALUES (:user_id, :user_type, :action, :description, :ip_address, :user_agent, :log_type, :resource_type, :resource_id, :additional_data)";
        
        $this->db->query($query);
        
        // Bind parameters
        $this->db->bind(':user_id', $data['user_id'] ?? null);
        $this->db->bind(':user_type', $data['user_type'] ?? 'user');
        $this->db->bind(':action', $data['action']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':ip_address', $this->getClientIp());
        $this->db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->db->bind(':log_type', $data['log_type'] ?? self::TYPE_SYSTEM);
        $this->db->bind(':resource_type', $data['resource_type'] ?? null);
        $this->db->bind(':resource_id', $data['resource_id'] ?? null);
        $this->db->bind(':additional_data', isset($data['additional_data']) ? json_encode($data['additional_data']) : null);

        return $this->db->execute();
    }

    /**
     * Get user logs with pagination
     */
    public function getUserLogs($page = 1, $limit = 50, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT sl.*, 
                         ui.First_Name, 
                         ui.Middle_Name, 
                         ui.Last_Name,
                         ui.Extension,
                         ui.Email
                  FROM {$this->table} sl
                  LEFT JOIN USER_INFORMATION ui ON sl.user_id = ui.User_ID
                  WHERE sl.log_type IN ('user', 'system')";
        
        $params = [];

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query .= " AND sl.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND sl.action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(sl.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(sl.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (sl.description LIKE :search OR ui.First_Name LIKE :search OR ui.Last_Name LIKE :search OR ui.Email LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $query .= " ORDER BY sl.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);

        return $this->db->resultSet();
    }

    /**
     * Get admin logs with pagination
     */
    public function getAdminLogs($page = 1, $limit = 50, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT sl.*, 
                         ui.First_Name, 
                         ui.Middle_Name, 
                         ui.Last_Name,
                         ui.Extension,
                         ui.Email
                  FROM {$this->table} sl
                  LEFT JOIN USER_INFORMATION ui ON sl.user_id = ui.User_ID
                  WHERE sl.user_type = 'admin' OR sl.log_type = 'admin'";
        
        $params = [];

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query .= " AND sl.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND sl.action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(sl.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(sl.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (sl.description LIKE :search OR ui.First_Name LIKE :search OR ui.Last_Name LIKE :search OR ui.Email LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $query .= " ORDER BY sl.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);

        return $this->db->resultSet();
    }

    /**
     * Get logs count for pagination
     */
    public function getLogsCount($logType = 'user', $filters = []) {
        if ($logType === 'user') {
            $query = "SELECT COUNT(*) as total FROM {$this->table} 
                      WHERE log_type IN ('user', 'system')";
        } else {
            $query = "SELECT COUNT(*) as total FROM {$this->table} 
                      WHERE user_type = 'admin' OR log_type = 'admin'";
        }
        
        $params = [];

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $this->db->query($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        $result = $this->db->single();
        return $result->total;
    }

    /**
     * Export logs to CSV
     */
    public function exportLogsToCSV($logType = 'user', $filters = []) {
        if ($logType === 'user') {
            $logs = $this->getUserLogs(1, 10000, $filters); // Get all logs with high limit
        } else {
            $logs = $this->getAdminLogs(1, 10000, $filters);
        }

        $filename = $logType . '_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, [
            'Timestamp', 
            'User Name', 
            'Email', 
            'Action', 
            'Description', 
            'IP Address', 
            'User Agent'
        ]);

        // CSV data
        foreach ($logs as $log) {
            $userName = $log->First_Name . ' ' . $log->Last_Name;
            if (!empty($log->Extension)) {
                $userName .= ' ' . $log->Extension;
            }

            fputcsv($output, [
                $log->created_at,
                $userName,
                $log->Email ?? 'N/A',
                $log->action,
                $log->description,
                $log->ip_address,
                $log->user_agent
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Clean up old logs (keep logs for 1 year)
     */
    public function cleanupOldLogs() {
        $query = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        $this->db->query($query);
        return $this->db->execute();
    }

    /**
     * Get client IP address
     */
    private function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Get log statistics
     */
    public function getLogStatistics($days = 30) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_logs,
                    SUM(CASE WHEN action = 'login' THEN 1 ELSE 0 END) as login_count,
                    SUM(CASE WHEN action = 'download' THEN 1 ELSE 0 END) as download_count,
                    SUM(CASE WHEN action = 'upload' THEN 1 ELSE 0 END) as upload_count
                  FROM {$this->table}
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date DESC";

        $this->db->query($query);
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }
}
?>