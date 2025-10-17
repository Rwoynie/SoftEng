<?php
/**
 * Public Home Controller
 * Handles requests for the public home page
 */

class PublicHomeController {
    private $model;
    
    public function __construct() {
        $this->model = new PublicHomeModel();
    }
    
    /**
     * Display the home page
     */
    public function index() {
        // Get data for the home page
        $data = [
            'announcements' => $this->model->getActiveAnnouncements(),
            'programs' => $this->model->getPrograms(),
            'stats' => $this->model->getThesisStats()
        ];
        
        // Load the home view
        $this->loadView('User/home', $data);
    }
    
    /**
     * Handle search requests
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $query = trim($_POST['query'] ?? '');
            $department = $_POST['department'] ?? 'all';
            $sort = $_POST['sort'] ?? 'recent';
            
            if (!empty($query)) {
                $results = $this->model->searchThesis($query, $department, $sort);
                
                // Return JSON response for AJAX requests
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'results' => $results,
                        'count' => count($results)
                    ]);
                    exit;
                }
                
                // For non-AJAX requests, load the results page
                $data = [
                    'query' => $query,
                    'results' => $results,
                    'department' => $department,
                    'sort' => $sort
                ];
                
                $this->loadView('User/search_results', $data);
            } else {
                // Empty query, redirect to home
                header('Location: /');
                exit;
            }
        } else {
            // Invalid request method
            header('Location: /');
            exit;
        }
    }
    
    /**
     * Browse all thesis papers
     */
    public function browse() {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 12; // Papers per page
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
        
        $this->loadView('User/browse', $data);
    }
    
    /**
     * Get thesis details
     */
    public function thesisDetails($id) {
        // This would fetch detailed information about a specific thesis
        // You'll need to implement this method in the model
        
        $data = [
            'thesis' => [] // Placeholder
        ];
        
        $this->loadView('User/thesis_details', $data);
    }
    
    /**
     * API endpoint for announcements (for AJAX calls)
     */
    public function getAnnouncementsAPI() {
        header('Content-Type: application/json');
        
        $announcements = $this->model->getActiveAnnouncements();
        
        echo json_encode([
            'success' => true,
            'announcements' => $announcements
        ]);
        exit;
    }
    
    /**
     * API endpoint for programs (for AJAX calls)
     */
    public function getProgramsAPI() {
        header('Content-Type: application/json');
        
        $programs = $this->model->getPrograms();
        
        echo json_encode([
            'success' => true,
            'programs' => $programs
        ]);
        exit;
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