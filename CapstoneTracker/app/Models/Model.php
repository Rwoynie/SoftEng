<?php
/**
 * Base Model Class
 * Provides common functionality for all models
 */
class Model {
    /**
     * Database instance
     * @var Database
     */
    protected $db;
    
    /**
     * Table name for the model
     * @var string
     */
    protected $tableName;
    
    /**
     * Constructor - initializes database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all records from the table
     * @return array|bool Array of objects or false on failure
     */
    protected function validateTableName($table) {
        // Simple validation - allow only alphanumeric and underscore
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new Exception("Invalid table name: " . $table);
        }
        return $table;
    }
    
    // Update all database methods to use validated table names:
    public function findAll() {
        try {
            $table = $this->validateTableName($this->tableName);
            $this->db->query("SELECT * FROM `{$table}`");
            return $this->db->resultSet();
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
            return false;
        }
    }
    
    public function findById($id) {
        try {
            $table = $this->validateTableName($this->tableName);
            $this->db->query("SELECT * FROM `{$table}` WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $table = $this->validateTableName($this->tableName);
            $this->db->query("DELETE FROM `{$table}` WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
            return false;
        }
    }
    
    public function count() {
        try {
            $table = $this->validateTableName($this->tableName);
            $this->db->query("SELECT COUNT(*) as total FROM `{$table}`");
            $result = $this->db->single();
            return $result->total;
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Sanitize input data to prevent XSS and SQL injection
     * @param mixed $data Input data to sanitize
     * @return mixed Sanitized data
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate required fields
     * @param array $data Data array to validate
     * @param array $fields Required field names
     * @return array Array of error messages
     */
    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst($field) . " is required";
            }
        }
        return $errors;
    }
    
    /**
     * Validate email format
     * @param string $email Email to validate
     * @return bool True if valid, false otherwise
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Handle errors consistently across all models
     * @param string $error Error message
     * @param bool $throwException Whether to throw exception
     * @throws Exception If $throwException is true
     */
    protected function handleError($error, $throwException = false) {
        error_log("Model Error [" . get_class($this) . "]: " . $error);
        
        if ($throwException) {
            throw new Exception($error);
        }
    }
    
    /**
     * Get the last inserted ID
     * @return int Last inserted ID
     */
    protected function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    /**
     * Begin transaction
     * @return bool True on success
     */
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     * @return bool True on success
     */
    protected function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     * @return bool True on success
     */
    protected function rollBack() {
        return $this->db->rollBack();
    }
    
    /**
     * Get the database instance (for advanced usage)
     * @return Database Database instance
     */
    public function getDb() {
        return $this->db;
    }
    
    /**
     * Get the table name
     * @return string Table name
     */
    public function getTableName() {
        return $this->tableName;
    }
}
?>