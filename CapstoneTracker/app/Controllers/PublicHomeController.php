<?php

/**
 * Public Home Controller
 */

class PublicHomeController extends Controller{
    private $model;
    
    public function __construct() {
        $this->model = $this->model('PublicHomeModel');
    }
    
    public function index() {
     
        echo "<pre>";
        try {
            $db = new Database();
            echo "Database connected: " . ($db->isConnected() ? 'YES' : 'NO') . "\n";
            if ($db->isConnected()) {
                $db->query("SELECT 1");
                $db->execute();
                echo "Test query executed successfully\n";
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        echo "</pre>";

        try {
            $announcements = $this->model->getActiveAnnouncements();
            $programs = $this->model->getPrograms();
            $stats = $this->model->getThesisStats();

            $data = [
                'announcements' => $announcements,
                'programs' => $programs,
                'stats' => $stats
            ];

            $this->view('Public/home', $data); 
        } catch (Exception $e) {
            error_log("Controller error: " . $e->getMessage());
            $data = [
                'announcements' => $this->model->getFallbackAnnouncements(),
                'programs' => $this->model->getFixedPrograms(),
                'stats' => ['total_papers' => 1, 'total_authors' => 0, 'total_departments' => 0]
            ];
            $this->view('Public/home', $data);
        }
    }

    /**
     * Handle search requests (always load view, no AJAX)
     */
    public function search() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') { 
        $query = trim($_GET['query'] ?? '');
        $department = $_GET['department'] ?? 'all';
        $sort = $_GET['sort'] ?? 'recent';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 8;
        $offset = ($page - 1) * $limit;
        
        if (!empty($query)) {
            $results = $this->model->searchThesis($query, $department, $sort, $limit, $offset);
            $total = $this->model->getSearchCount($query, $department);
            $totalPages = ceil($total / $limit);
            
            $data = [
                'query' => $query,
                'results' => $results,
                'department' => $department,
                'sort' => $sort,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalPapers' => $total
            ];
            
            $this->loadView('Public/search', $data);
        } else {
            header('Location: /search.php');
            exit;
        }
    } else {
        header('Location: /search.php');
        exit;
    }
}
    
    /**
     * Browse all thesis papers
     */
    public function browse() {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 8;
        $offset = ($page - 1) * $limit;
        
        $thesisPapers = $this->model->getAllThesis($limit, $offset);
        $totalPapers = $this->model->getThesisStats()['total_papers'];
        $totalPages = ceil($totalPapers / $limit);
        
        $data = [
            'thesisPapers' => $thesisPapers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPapers' => $totalPapers
        ];
        
        $this->loadView('Public/browse', $data);
    }
    
    /**
     * Get thesis details
     */
    public function thesisDetails($id) {
        $thesis = $this->model->getThesisById($id);
        
        if (!$thesis) {
            header('HTTP/1.0 404 Not Found');
            echo "Thesis not found";
            exit;
        }
        
        $data = [
            'thesis' => $thesis
        ];
        
        $this->loadView('Public/thesis_details', $data);
    }
    
    /**
     * Load view file
     */
    private function loadView($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Load the view file
        $viewFile = __DIR__ . "/../Views/{$view}.php";
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            // View not found
            http_response_code(404);
            echo "View not found: {$view}";
            exit;
        }
    }
}
?>