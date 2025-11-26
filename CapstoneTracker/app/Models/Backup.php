<?php


class Backup {
    private $db;
    private $backupPath;

    public function __construct($database) {
        $this->db = $database;
        $this->backupPath = $_SERVER['DOCUMENT_ROOT'] . '/CapstoneTracker/backups/';
        

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
            
            // Use the PHP backup method instead of mysqldump to better handle JSON
            if ($this->createBackupWithPHP($backupFilePath)) {
                error_log("Backup created successfully using PHP method");
                
                // Check if backup file was created
                if (!file_exists($backupFilePath) || filesize($backupFilePath) === 0) {
                    throw new Exception("Backup file was not created or is empty.");
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
            } else {
                throw new Exception("PHP backup method failed");
            }
            
        } catch (Exception $e) {
            error_log("Backup creation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function createBackupWithPHP($backupFilePath) {
        try {
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;
            
            $connection = new mysqli($host, $user, $pass, $name);
            
            if ($connection->connect_error) {
                throw new Exception("Connection failed: " . $connection->connect_error);
            }
            
            // Set UTF8 encoding
            $connection->set_charset("utf8mb4");
            
            $output = "-- PHP MySQL Backup\n";
            $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: " . $name . "\n\n";
            
            // Disable foreign key checks at the beginning
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            // Get all tables (excluding views)
            $tables = array();
            $result = $connection->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
            
            // First, drop all tables in reverse dependency order
            $output .= "--\n-- Drop tables\n--\n";
            $dropOrder = array_reverse($tables);
            foreach ($dropOrder as $table) {
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
            }
            $output .= "\n";
            
            // Create all tables
            foreach ($tables as $table) {
                $output .= "--\n-- Table structure for table `$table`\n--\n";
                
                // Create table statement
                $createTable = $connection->query("SHOW CREATE TABLE `$table`");
                $row = $createTable->fetch_row();
                $output .= $row[1] . ";\n\n";
            }
            
            // Now insert data in correct order to respect foreign keys
            $tableInsertOrder = $this->getTableInsertOrder($connection, $tables);
            
            foreach ($tableInsertOrder as $table) {
                $output .= "--\n-- Dumping data for table `$table`\n--\n";
                
                $data = $connection->query("SELECT * FROM `$table`");
                if ($data && $data->num_rows > 0) {
                    $numFields = $data->field_count;
                    
                    while ($row = $data->fetch_assoc()) {
                        // Use INSERT IGNORE to handle unique constraints
                        $output .= "INSERT IGNORE INTO `$table` VALUES(";
                        
                        $fieldIndex = 0;
                        foreach ($row as $value) {
                            // Handle NULL values
                            if ($value === null) {
                                $output .= "NULL";
                            } else {
                                // Check if this field is a JSON column
                                $fieldInfo = $data->fetch_field_direct($fieldIndex);
                                $isJson = $this->isJsonColumn($connection, $table, $fieldInfo->name);
                                
                                if ($isJson && !empty($value)) {
                                    // For JSON columns, ensure valid JSON
                                    $jsonValue = $this->validateJsonValue($value);
                                    $output .= "'" . $connection->real_escape_string($jsonValue) . "'";
                                } else {
                                    // Regular string escaping
                                    $output .= "'" . $connection->real_escape_string($value) . "'";
                                }
                            }
                            
                            if ($fieldIndex < ($numFields - 1)) {
                                $output .= ",";
                            }
                            $fieldIndex++;
                        }
                        $output .= ");\n";
                    }
                    $output .= "\n";
                }
            }
            
            // Backup views
            $output .= "--\n-- Views\n--\n";
            $result = $connection->query("SHOW FULL TABLES WHERE Table_Type = 'VIEW'");
            while ($row = $result->fetch_row()) {
                $viewName = $row[0];
                $createView = $connection->query("SHOW CREATE VIEW `$viewName`");
                if ($createView && $viewRow = $createView->fetch_row()) {
                    $output .= "--\n-- View structure for view `$viewName`\n--\n";
                    $output .= "DROP VIEW IF EXISTS `$viewName`;\n";
                    $output .= $viewRow[1] . ";\n\n";
                }
            }
            
            // Re-enable foreign key checks at the end
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            // Write to file
            if (file_put_contents($backupFilePath, $output) === false) {
                throw new Exception("Could not write to backup file");
            }
            
            $connection->close();
            return true;
            
        } catch (Exception $e) {
            error_log("PHP backup method failed: " . $e->getMessage());
            return false;
        }
    }

private function getTableInsertOrder($connection, $tables) {
    // Define a manual order based on your database schema
    // Parent tables should come first, then child tables
    $preferredOrder = [
        'user_information',  // Parent table
        'roles',             // Child table that references user_information
        // Add other tables in dependency order
        'audit_logs',
        'system_logs'
    ];
    
    // Filter to only include tables that exist
    $existingTables = array_intersect($preferredOrder, $tables);
    
    // Add any remaining tables that weren't in the preferred order
    $remainingTables = array_diff($tables, $existingTables);
    $finalOrder = array_merge($existingTables, $remainingTables);
    
    return $finalOrder;
}



    private function isJsonColumn($connection, $table, $column) {
        $result = $connection->query("
            SELECT DATA_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
            AND TABLE_NAME = '$table' 
            AND COLUMN_NAME = '$column'
        ");
        
        if ($result && $row = $result->fetch_assoc()) {
            return strtoupper($row['DATA_TYPE']) === 'JSON';
        }
        
        return false;
    }

    private function validateJsonValue($value) {
        // If it's already valid JSON, return as is
        if ($this->isValidJson($value)) {
            return $value;
        }
        
        // If empty, return empty JSON object
        if (empty(trim($value))) {
            return '{}';
        }
        
        // Try to fix common JSON issues
        $fixedValue = trim($value);
        
        // Ensure it starts and ends with braces/brackets
        if (!in_array($fixedValue[0], ['{', '['])) {
            $fixedValue = '{' . $fixedValue;
        }
        if (!in_array($fixedValue[strlen($fixedValue)-1], ['}', ']'])) {
            $fixedValue = $fixedValue . '}';
        }
   
        if (!$this->isValidJson($fixedValue)) {
            return json_encode($value); 
        }
        
        return $fixedValue;
    }

    private function isValidJson($string) {
        if (!is_string($string) || empty(trim($string))) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
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
        try {
            $decompressedPath = str_replace('.gz', '', $filePath);
            
            $compressed = file_get_contents($filePath);
            
            if ($compressed === false) {
                throw new Exception("Failed to read compressed backup file");
            }
            
            $data = gzdecode($compressed);
            
            if ($data === false) {
                throw new Exception("Failed to decompress backup file");
            }
            
            $result = file_put_contents($decompressedPath, $data);
            
            if ($result === false) {
                throw new Exception("Failed to write decompressed SQL file");
            }
            
            return $decompressedPath;
            
        } catch (Exception $e) {
            error_log("Decompression error: " . $e->getMessage());
            throw new Exception("Failed to decompress backup: " . $e->getMessage());
        }
    }

    public function restoreBackup($backupFileName) {
        try {
            $backupFilePath = $this->backupPath . $backupFileName;
            
            if (!file_exists($backupFilePath)) {
                throw new Exception("Backup file not found: " . $backupFileName);
            }
            
            // Decompress the backup
            $sqlFilePath = $this->decompressBackup($backupFilePath);
            
            if (!file_exists($sqlFilePath)) {
                throw new Exception("Decompressed SQL file not found");
            }
            
            // Repair the backup file before restore
            $repairedFilePath = $this->repairBackupBeforeRestore($sqlFilePath);
            
            // Get database configuration
            $host = DB_HOST;
            $user = DB_USER;
            $pass = DB_PASS;
            $name = DB_NAME;
            
            // Use PHP method for restore to better handle JSON
            if ($this->restoreWithPHP($repairedFilePath)) {
                // Delete the decompressed SQL file
                if (file_exists($repairedFilePath) && $repairedFilePath !== $sqlFilePath) {
                    unlink($repairedFilePath);
                }
                if (file_exists($sqlFilePath)) {
                    unlink($sqlFilePath);
                }
                
                // Log restore action
                $this->logBackupAction('restore', $backupFilePath);
                
                return [
                    'success' => true,
                    'message' => 'Backup restored successfully'
                ];
            } else {
                throw new Exception("PHP restore method failed");
            }
            
        } catch (Exception $e) {
            error_log("Backup restore error: " . $e->getMessage());
            
            // Clean up files
            if (isset($sqlFilePath) && file_exists($sqlFilePath)) {
                unlink($sqlFilePath);
            }
            if (isset($repairedFilePath) && file_exists($repairedFilePath) && $repairedFilePath !== $sqlFilePath) {
                unlink($repairedFilePath);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function repairBackupBeforeRestore($sqlFilePath) {
        $content = file_get_contents($sqlFilePath);
        
        // Only fix INSERT statements for audit_logs table
        $fixedContent = preg_replace_callback(
            "/INSERT INTO `audit_logs` VALUES\((.*?)\);/",
            function($matches) {
                $values = $matches[1];
                $fixedValues = $this->fixAuditLogsValues($values);
                return "INSERT INTO `audit_logs` VALUES($fixedValues);";
            },
            $content
        );
        
        $tempFile = $sqlFilePath . '.fixed';
        file_put_contents($tempFile, $fixedContent);
        
        return $tempFile;
    }

    private function fixAuditLogsValues($values) {
        $valuesArray = $this->splitSqlValues($values);
        
        $jsonPositions = [4, 5];
        
        foreach ($jsonPositions as $pos) {
            if (isset($valuesArray[$pos])) {
                $value = trim($valuesArray[$pos], "'");
                if ($value === '' || $value === 'NULL' || !$this->isValidJson($value)) {
                    $valuesArray[$pos] = "'{}'";
                }
            }
        }
        
        return implode(',', $valuesArray);
    }

    private function fixJsonValuesInInserts($content) {
        // Split content into lines for more precise processing
        $lines = explode("\n", $content);
        $fixedLines = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Only process INSERT statements (skip CREATE TABLE, DROP TABLE, etc.)
            if (strpos($trimmedLine, 'INSERT INTO') === 0) {
                $fixedLine = $this->fixJsonValuesInInsertStatement($trimmedLine);
                $fixedLines[] = $fixedLine;
            } else {
                $fixedLines[] = $line;
            }
        }
        
        return implode("\n", $fixedLines);
    }

    private function fixJsonValuesInInsertStatement($insertStatement) {
        // Pattern to match table names and values
        if (preg_match('/INSERT INTO `([^`]+)` VALUES\((.*)\);/', $insertStatement, $matches)) {
            $tableName = $matches[1];
            $values = $matches[2];
            
            // Only fix JSON columns for specific tables that have JSON columns
            $tablesWithJsonColumns = ['audit_logs', 'system_logs']; // Add other tables with JSON columns
            
            if (in_array($tableName, $tablesWithJsonColumns)) {
                $fixedValues = $this->fixJsonValuesForTable($tableName, $values);
                return "INSERT INTO `$tableName` VALUES($fixedValues);";
            }
        }
        
        return $insertStatement;
    }

    private function fixJsonValuesForTable($tableName, $values) {
        // Split values while handling quoted strings and commas inside quotes
        $valuesArray = $this->splitSqlValues($values);
        
        // Define which positions are JSON columns for each table
        $jsonColumnPositions = [
            'audit_logs' => [4, 5], // old_values and new_values positions
            'system_logs' => [2, 3]  // Adjust based on your schema
        ];
        
        if (isset($jsonColumnPositions[$tableName])) {
            foreach ($jsonColumnPositions[$tableName] as $position) {
                if (isset($valuesArray[$position])) {
                    $value = trim($valuesArray[$position], "'");
                    
                    // Fix empty or invalid JSON values
                    if ($value === '' || $value === 'NULL' || !$this->isValidJson($value)) {
                        $valuesArray[$position] = "'{}'";
                    }
                }
            }
        }
        
        return implode(',', $valuesArray);
    }

    private function splitSqlValues($valuesString) {
        $values = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';
        $escapeNext = false;
        
        for ($i = 0; $i < strlen($valuesString); $i++) {
            $char = $valuesString[$i];
            
            if ($escapeNext) {
                $current .= $char;
                $escapeNext = false;
                continue;
            }
            
            if ($char === '\\') {
                $escapeNext = true;
                $current .= $char;
                continue;
            }
            
            if (($char === "'" || $char === '"') && !$escapeNext) {
                if ($inQuotes && $char === $quoteChar) {
                    $inQuotes = false;
                } elseif (!$inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                }
            }
            
            if ($char === ',' && !$inQuotes) {
                $values[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }
        
        // Add the last value
        if (!empty($current)) {
            $values[] = $current;
        }
        
        return $values;
    }

    private function restoreWithPHP($sqlFilePath) {
        $host = DB_HOST;
        $user = DB_USER;
        $pass = DB_PASS;
        $name = DB_NAME;
        
        $connection = new mysqli($host, $user, $pass, $name);
        
        if ($connection->connect_error) {
            throw new Exception("Connection failed: " . $connection->connect_error);
        }
        
        // Disable foreign key checks before restore
        $connection->query("SET FOREIGN_KEY_CHECKS=0");
        
        // Read SQL file
        $sql = file_get_contents($sqlFilePath);
        
        // Remove DELIMITER statements and split by semicolon
        $sql = preg_replace('/DELIMITER \$\$/', '', $sql);
        $sql = preg_replace('/\$\$/', ';', $sql);
        $sql = preg_replace('/DELIMITER ;/', '', $sql);
        
        // Split SQL statements, handling multiple statements per line
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $escapeNext = false;
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if ($escapeNext) {
                $current .= $char;
                $escapeNext = false;
                continue;
            }
            
            if (($char === "'" || $char === '"' || $char === '`') && !$escapeNext) {
                if ($inString && $char === $stringChar) {
                    $inString = false;
                } elseif (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                }
            }
            
            if ($char === '\\' && $inString) {
                $escapeNext = true;
            }
            
            $current .= $char;
            
            if ($char === ';' && !$inString) {
                $trimmed = trim($current);
                if (!empty($trimmed) && $trimmed !== ';') {
                    $statements[] = $trimmed;
                }
                $current = '';
            }
        }
        
        // Add any remaining content
        $trimmed = trim($current);
        if (!empty($trimmed) && $trimmed !== ';') {
            $statements[] = $trimmed;
        }
        
        // Execute each statement individually with error handling
        foreach ($statements as $statement) {
            $trimmedStatement = trim($statement);
            if (empty($trimmedStatement)) {
                continue;
            }
            
            // Skip empty statements and comments
            if (preg_match('/^\s*(--|#|\/\*)/', $trimmedStatement)) {
                continue;
            }
            
            // Skip DROP TABLE statements if we want to preserve existing data
            if (preg_match('/^DROP TABLE/i', $trimmedStatement)) {
                continue; // Skip DROP TABLE statements to avoid losing existing data
            }
            
            // Skip CREATE TABLE if table already exists (or use CREATE TABLE IF NOT EXISTS)
            if (preg_match('/^CREATE TABLE/i', $trimmedStatement)) {
                // Convert to CREATE TABLE IF NOT EXISTS
                $statement = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $statement);
            }
            
            try {
                if (!$connection->query($statement)) {
                    // If it's a duplicate entry error, log it but continue
                    if (strpos($connection->error, 'Duplicate entry') !== false) {
                        error_log("Duplicate entry skipped: " . substr($statement, 0, 100));
                        continue;
                    }
                    // If it's a table exists error, skip it
                    if (strpos($connection->error, 'already exists') !== false) {
                        error_log("Table already exists, skipping: " . substr($statement, 0, 100));
                        continue;
                    }
                    throw new Exception("SQL Error: " . $connection->error . " in statement: " . substr($statement, 0, 100));
                }
            } catch (Exception $e) {
                // Log the error but continue with other statements
                error_log("SQL execution warning: " . $e->getMessage());
                continue;
            }
        }
        
        // Re-enable foreign key checks
        $connection->query("SET FOREIGN_KEY_CHECKS=1");
        
        $connection->close();
        return true;
    }

/**
 * Split SQL file into individual statements
 */
private function splitSqlStatements($sql) {
    $statements = [];
    $current = '';
    $inString = false;
    $stringChar = '';
    $escapeNext = false;
    
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        
        if ($escapeNext) {
            $current .= $char;
            $escapeNext = false;
            continue;
        }
        
        if ($char === '\\') {
            $escapeNext = true;
            $current .= $char;
            continue;
        }
        
        // Handle string literals
        if (($char === "'" || $char === '"') && !$inString) {
            $inString = true;
            $stringChar = $char;
        } elseif ($char === $stringChar && $inString) {
            $inString = false;
        }
        
        // Check for semicolon only when not in string
        if ($char === ';' && !$inString) {
            $statements[] = trim($current) . ';';
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    // Add the last statement if any
    if (!empty(trim($current))) {
        $statements[] = trim($current);
    }
    
    return $statements;
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

