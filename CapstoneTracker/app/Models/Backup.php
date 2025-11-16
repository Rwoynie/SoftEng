<?php
// app/Models/Backup.php

class Backup {
    private $db;
    private $backupPath;

    public function __construct($database) {
        $this->db = $database;
        $this->backupPath = $_SERVER['DOCUMENT_ROOT'] . '/CapstoneTracker/backups/';
        
        // Create backup directory if it doesn't exist
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    public function createBackup() {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupFileName = "backup_{$timestamp}.sql";
            $backupFilePath = $this->backupPath . $backupFileName;
            
            // Get database configuration
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;
            
            // XAMPP mysqldump path
            $mysqldumpPath = '"C:\\xampp\\mysql\\bin\\mysqldump.exe"';
            
            // Create MySQL dump command
            $command = "{$mysqldumpPath} --host={$host} --user={$user} --password={$pass} {$name} > \"{$backupFilePath}\" 2>&1";
            
            // Debug: Log the command (without password)
            $debugCommand = "{$mysqldumpPath} --host={$host} --user={$user} --password=*** {$name} > \"{$backupFilePath}\" 2>&1";
            error_log("Backup command: " . $debugCommand);
            
            // Execute command
            exec($command, $output, $returnVar);
            
            error_log("Backup output: " . implode("\n", $output));
            error_log("Backup return code: " . $returnVar);
            
            if ($returnVar !== 0) {
                $errorMsg = "Backup failed with code {$returnVar}";
                if (!empty($output)) {
                    $errorMsg .= ": " . implode("\n", $output);
                }
                throw new Exception($errorMsg);
            }
            
            // Check if backup file was created
            if (!file_exists($backupFilePath) || filesize($backupFilePath) === 0) {
                throw new Exception("Backup file was not created or is empty. Check if backup directory is writable.");
            }
            
            // Compress the backup
            $compressedPath = $this->compressBackup($backupFilePath);
            
            // Delete the original SQL file
            unlink($backupFilePath);
            
            // Log backup creation
            $this->logBackupAction('create', $compressedPath);
            
            return [
                'success' => true,
                'file_path' => $compressedPath,
                'file_name' => basename($compressedPath),
                'size' => filesize($compressedPath),
                'size_formatted' => $this->formatBytes(filesize($compressedPath)),
                'timestamp' => $timestamp
            ];
            
        } catch (Exception $e) {
            error_log("Backup creation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function compressBackup($filePath) {
        $compressedPath = $filePath . '.gz';
        
        // Read the file
        $data = file_get_contents($filePath);
        
        // Compress the data
        $compressed = gzencode($data, 9);
        
        // Write compressed data
        file_put_contents($compressedPath, $compressed);
        
        return $compressedPath;
    }

    private function decompressBackup($filePath) {
        $decompressedPath = str_replace('.gz', '', $filePath);
        
        // Read compressed data
        $compressed = file_get_contents($filePath);
        
        // Decompress the data
        $data = gzdecode($compressed);
        
        // Write decompressed data
        file_put_contents($decompressedPath, $data);
        
        return $decompressedPath;
    }

    public function restoreBackup($backupFileName) {
        try {
            $backupFilePath = $this->backupPath . $backupFileName;
            
            if (!file_exists($backupFilePath)) {
                throw new Exception("Backup file not found: " . $backupFileName);
            }
            
            // Decompress the backup
            $sqlFilePath = $this->decompressBackup($backupFilePath);
            
            // Get database configuration
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;
            
            // Restore MySQL dump
            $command = "mysql --host={$host} --user={$user} --password={$pass} {$name} < {$sqlFilePath} 2>&1";
            exec($command, $output, $returnVar);
            
            // Delete the decompressed SQL file
            unlink($sqlFilePath);
            
            if ($returnVar !== 0) {
                throw new Exception("Restore failed: " . implode("\n", $output));
            }
            
            // Log restore action
            $this->logBackupAction('restore', $backupFilePath);
            
            return [
                'success' => true,
                'message' => 'Backup restored successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Backup restore error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getBackupHistory() {
        try {
            $backups = [];
            $files = scandir($this->backupPath);
            
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'gz') {
                    $filePath = $this->backupPath . $file;
                    $fileSize = filesize($filePath);
                    
                    $backups[] = [
                        'file_name' => $file,
                        'file_path' => $filePath,
                        'size' => $fileSize,
                        'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'size_formatted' => $this->formatBytes($fileSize) // Ensure this is here
                    ];
                }
            }
            
            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return $backups;
            
        } catch (Exception $e) {
            error_log("Backup history error: " . $e->getMessage());
            return [];
        }
    }

    public function deleteBackup($backupFileName) {
        try {
            $backupFilePath = $this->backupPath . $backupFileName;
            
            if (!file_exists($backupFilePath)) {
                throw new Exception("Backup file not found: " . $backupFileName);
            }
            
            if (unlink($backupFilePath)) {
                // Log delete action
                $this->logBackupAction('delete', $backupFilePath);
                
                return [
                    'success' => true,
                    'message' => 'Backup deleted successfully'
                ];
            } else {
                throw new Exception("Failed to delete backup file");
            }
            
        } catch (Exception $e) {
            error_log("Backup deletion error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function logBackupAction($action, $filePath) {
        try {
            $user_id = $_SESSION['user_id'] ?? 0;
            $user_name = $_SESSION['user_name'] ?? 'System';
            $timestamp = date('Y-m-d H:i:s');
            $fileName = basename($filePath);
            
            $logMessage = "[{$timestamp}] User: {$user_name} (ID: {$user_id}) - Action: {$action} - File: {$fileName}";
            
            $logFile = $this->backupPath . 'backup_log.txt';
            file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            error_log("Backup log error: " . $e->getMessage());
        }
    }

    public function getBackupInfo() {
        $backups = $this->getBackupHistory();
        $latestBackup = !empty($backups) ? $backups[0] : null;
        
        return [
            'last_backup' => $latestBackup ? $latestBackup['created_at'] : 'Never',
            'last_backup_size' => $latestBackup ? $latestBackup['size_formatted'] : '0 MB',
            'total_backups' => count($backups),
            'auto_backup_status' => 'Disabled',
            'next_backup_date' => 'Not scheduled',
            'backup_location' => $this->backupPath
        ];
    }

    public function getBackupPath() {
        return $this->backupPath;
    }
}
?>