<?php

require_once __DIR__ . '/Controller.php';

class SetupController extends Controller {
    private $db;
    private $schema;
    
    public function __construct() {
        require_once __DIR__ . '/../Models/Database.php';
        require_once __DIR__ . '/../../Database/config.php';
        require_once __DIR__ . '/../../Database/Tables.php';
        
        $this->db = new Database();
        $this->schema = new DatabaseSchema($this->db);
    }
    
    public function initializeDatabase() {
        // Check if database is already initialized
        if ($this->schema->isDatabaseInitialized()) {
            return true;
        }
        
        // Get credentials from config
        $host = DB_HOST;
        $username = DB_USER;
        $password = DB_PASS;
        $database = DB_NAME;
        
        try {
            // Run full setup
            return $this->schema->fullSetup($host, $username, $password, $database);
        } catch (Exception $e) {
            $this->schema->setError("Setup failed: " . $e->getMessage());
            return false;
        }
    }

    public function getSetupError() {
        return $this->schema->getError();
    }
    
    public function showSetupPage() {
        $data = [
            'title' => 'Database Setup',
            'error' => $this->getSetupError()
        ];
        $this->view('setup', $data);
    }
    
    public function handleSetup() {
        $result = $this->initializeDatabase();
        if ($result) {
            $this->redirect('login?setup=success');
        } else {
            $this->redirect('setup?error=' . urlencode($this->getSetupError()));
        }
    }
}
?>