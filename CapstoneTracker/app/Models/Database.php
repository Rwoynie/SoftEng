<?php
require_once __DIR__ . '/../../Database/config.php';
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $dbh;
    private $stmt;
    private $error;
    private $isConnected = false;
    
    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];
        
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            $this->isConnected = true;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database connection error: " . $this->error);
            $this->isConnected = false;
            // Don't throw exception here, let the caller check isConnected()
        }
    }

    public function isConnected() {
        return $this->isConnected;
    }
    
    public function getError() {
        return $this->error;
    }
    
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function prepare($sql) {
        $this->stmt = $this->dbh->prepare($sql);
        return $this;
    }

    public function fetch($sql = null, $params = []) {
        if ($sql) {
            $this->prepare($sql);
        }
        
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $this->bind($param, $value);
            }
        }
        
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            error_log("Query execution error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    // Additional helpers for callers that prefer associative arrays
    public function resultSetAssoc() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function singleAssoc() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction() {
        if (!$this->isConnected) {
            return false;
        }
        return $this->dbh->beginTransaction();
    }

    public function commit() {
        if (!$this->isConnected) {
            return false;
        }
        return $this->dbh->commit();
    }

    public function rollBack() {
        if (!$this->isConnected) {
            return false;
        }
        return $this->dbh->rollBack();
    }
}