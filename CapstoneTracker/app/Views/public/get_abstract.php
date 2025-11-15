<?php
require_once '../../../Database/config.php';
require_once '../../Controllers/Controller.php';  
require_once '../../Controllers/PublicHomeController.php';  
require_once '../../Models/Thesis.php';  
require_once '../../Models/PublicHomeModel.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesis_id'])) {
    $thesisId = $_POST['thesis_id'];
    
    try {
        $model = new PublicHomeModel();
        $thesis = $model->getThesisById($thesisId);
        
        if ($thesis && isset($thesis['abstract'])) {
            $abstract = $thesis['abstract'];
            
            // Clean the abstract
            $cleanAbstract = strip_tags($abstract);
            $cleanAbstract = preg_replace('/[^\x20-\x7E]/', '', $cleanAbstract);
            $cleanAbstract = trim($cleanAbstract);
            
            if (str_starts_with($cleanAbstract, '%PDF')) {
                $cleanAbstract = 'The full abstract is available in the PDF file. Please view the full thesis to read the complete abstract.';
            }
            
            if (empty($cleanAbstract)) {
                $cleanAbstract = 'Abstract not available for this thesis.';
            }
            
            echo json_encode([
                'success' => true,
                'abstract' => $cleanAbstract
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Thesis not found'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request'
    ]);
}
?>