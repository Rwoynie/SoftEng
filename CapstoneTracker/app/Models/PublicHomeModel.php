<?php
/**
 * Public Home Model
 * Handles data operations for the public home page
 */

class PublicHomeModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all active announcements
     */
    public function getActiveAnnouncements() {
        try {
            $this->db->query("
                SELECT 
                    id,
                    title,
                    content as description,
                    type,
                    start_date as date,
                    'Announcement_pic.png' as image
                FROM ANNOUNCEMENTS 
                WHERE status = 'published' 
                AND (end_date IS NULL OR end_date >= NOW())
                AND start_date <= NOW()
                ORDER BY is_pinned DESC, start_date DESC
                LIMIT 10
            ");
            
            $results = $this->db->resultSet();
            
            // Format the results to match your frontend structure
            $announcements = [];
            foreach ($results as $result) {
                $announcements[] = [
                    'title' => $result->title,
                    'description' => $result->description,
                    'date' => $result->date,
                    'image' => '../../../resources/images/Announcement_pic.png',
                    'type' => $result->type
                ];
            }
            
            return $announcements;
            
        } catch (Exception $e) {
            error_log("Error fetching announcements: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all programs/departments
     */
    public function getPrograms() {
        try {
            // This would typically come from a PROGRAMS table
            // For now, we'll return the static data structure
            $programs = [
                [
                    'name' => 'SITS',
                    'meaning' => 'Society of Information Technology Students',
                    'image' => '../../../resources/images/SITS_LOGO.png'
                ],
                [
                    'name' => 'AECES',
                    'meaning' => 'Association of Early Childhood Education',
                    'image' => '../../../resources/images/AECES_LOGO.png'
                ],
                [
                    'name' => 'AFSET',
                    'meaning' => 'Association of Future Secondary Teachers',
                    'image' => '../../../resources/images/AFSET_LOGO.png'
                ],
                [
                    'name' => 'FTVETS',
                    'meaning' => "Future Technical-Vocational Educators' and Trainers' Society",
                    'image' => '../../../resources/images/FTVETS_LOGO.png'
                ],
                [
                    'name' => 'OFEE',
                    'meaning' => 'Organization of Future Elementary Educators',
                    'image' => '../../../resources/images/OFEE_LOGO.png'
                ],
                [
                    'name' => 'OFSET',
                    'meaning' => 'Organization of Future Special Education Teachers',
                    'image' => '../../../resources/images/OFSET_LOGO.png'
                ],
                [
                    'name' => 'SABES',
                    'meaning' => 'Society of Agricultural and Biosystems Engineering Students',
                    'image' => '../../../resources/images/SABES_LOGO.png'
                ]
            ];
            
            return $programs;
            
        } catch (Exception $e) {
            error_log("Error fetching programs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search thesis papers
     */
    public function searchThesis($query, $department = 'all', $sort = 'recent') {
        try {
            $sql = "
                SELECT 
                    t.ID as id,
                    t.Title as title,
                    t.Author as authors,
                    t.Adviser as adviser,
                    t.Thesis_AbstractFile as abstract,
                    CONCAT(t.Thesis_Course, ' | ', 
                           CASE t.Thesis_Department 
                               WHEN 'BSIT' THEN 'SITS'
                               WHEN 'BSABE' THEN 'SABES'
                               WHEN 'BECED' THEN 'AECES'
                               WHEN 'BSNED' THEN 'OFSET'
                               WHEN 'BTVTED' THEN 'FTVETS'
                               WHEN 'BEED' THEN 'OFEE'
                               WHEN 'BSED' THEN 'AFSET'
                               ELSE t.Thesis_Department
                           END) as department,
                    t.uploaded_at as uploadDate,
                    CASE t.Thesis_Department 
                        WHEN 'BSIT' THEN '../../../resources/images/SITS_LOGO.png'
                        WHEN 'BSABE' THEN '../../../resources/images/SABES_LOGO.png'
                        WHEN 'BECED' THEN '../../../resources/images/AECES_LOGO.png'
                        WHEN 'BSNED' THEN '../../../resources/images/OFSET_LOGO.png'
                        WHEN 'BTVTED' THEN '../../../resources/images/FTVETS_LOGO.png'
                        WHEN 'BEED' THEN '../../../resources/images/OFEE_LOGO.png'
                        WHEN 'BSED' THEN '../../../resources/images/AFSET_LOGO.png'
                        ELSE '../../../resources/images/default_logo.png'
                    END as logo
                FROM THESIS t
                WHERE t.Title LIKE :query 
                   OR t.Author LIKE :query 
                   OR t.Adviser LIKE :query 
                   OR t.Thesis_AbstractFile LIKE :query
                   OR t.Thesis_Department LIKE :query
            ";
            
            // Add department filter if specified
            if ($department !== 'all') {
                $sql .= " AND t.Thesis_Department = :department";
            }
            
            // Add sorting
            switch ($sort) {
                case 'title':
                    $sql .= " ORDER BY t.Title ASC";
                    break;
                case 'popular':
                    // You might want to add view counts to your THESIS table
                    $sql .= " ORDER BY t.ID DESC";
                    break;
                case 'department':
                    $sql .= " ORDER BY t.Thesis_Department ASC, t.Title ASC";
                    break;
                case 'recent':
                default:
                    $sql .= " ORDER BY t.uploaded_at DESC";
                    break;
            }
            
            $this->db->query($sql);
            $this->db->bind(':query', '%' . $query . '%');
            
            if ($department !== 'all') {
                $this->db->bind(':department', $department);
            }
            
            $results = $this->db->resultSet();
            
            // Convert to array format expected by frontend
            $thesisPapers = [];
            foreach ($results as $result) {
                $thesisPapers[] = [
                    'id' => $result->id,
                    'title' => $result->title,
                    'authors' => $result->authors,
                    'adviser' => $result->adviser,
                    'abstract' => $result->abstract,
                    'department' => $result->department,
                    'uploadDate' => $result->uploadDate,
                    'logo' => $result->logo
                ];
            }
            
            return $thesisPapers;
            
        } catch (Exception $e) {
            error_log("Error searching thesis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get thesis count statistics
     */
    public function getThesisStats() {
        try {
            $this->db->query("
                SELECT 
                    COUNT(*) as total_papers,
                    COUNT(DISTINCT Author) as total_authors,
                    COUNT(DISTINCT Thesis_Department) as total_departments
                FROM THESIS
            ");
            
            $result = $this->db->single();
            
            return [
                'total_papers' => $result->total_papers ?? 0,
                'total_authors' => $result->total_authors ?? 0,
                'total_departments' => $result->total_departments ?? 0
            ];
            
        } catch (Exception $e) {
            error_log("Error fetching thesis stats: " . $e->getMessage());
            return [
                'total_papers' => 200,
                'total_authors' => 150,
                'total_departments' => 1
            ];
        }
    }
    
    /**
     * Get all thesis papers (for browse functionality)
     */
    public function getAllThesis($limit = 50, $offset = 0) {
        try {
            $this->db->query("
                SELECT 
                    t.ID as id,
                    t.Title as title,
                    t.Author as authors,
                    t.Adviser as adviser,
                    t.Thesis_AbstractFile as abstract,
                    CONCAT(t.Thesis_Course, ' | ', 
                           CASE t.Thesis_Department 
                               WHEN 'BSIT' THEN 'SITS'
                               WHEN 'BSABE' THEN 'SABES'
                               WHEN 'BECED' THEN 'AECES'
                               WHEN 'BSNED' THEN 'OFSET'
                               WHEN 'BTVTED' THEN 'FTVETS'
                               WHEN 'BEED' THEN 'OFEE'
                               WHEN 'BSED' THEN 'AFSET'
                               ELSE t.Thesis_Department
                           END) as department,
                    t.uploaded_at as uploadDate,
                    CASE t.Thesis_Department 
                        WHEN 'BSIT' THEN '../../../resources/images/SITS_LOGO.png'
                        WHEN 'BSABE' THEN '../../../resources/images/SABES_LOGO.png'
                        WHEN 'BECED' THEN '../../../resources/images/AECES_LOGO.png'
                        WHEN 'BSNED' THEN '../../../resources/images/OFSET_LOGO.png'
                        WHEN 'BTVTED' THEN '../../../resources/images/FTVETS_LOGO.png'
                        WHEN 'BEED' THEN '../../../resources/images/OFEE_LOGO.png'
                        WHEN 'BSED' THEN '../../../resources/images/AFSET_LOGO.png'
                        ELSE '../../../resources/images/default_logo.png'
                    END as logo
                FROM THESIS t
                ORDER BY t.uploaded_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $this->db->bind(':limit', $limit);
            $this->db->bind(':offset', $offset);
            
            $results = $this->db->resultSet();
            
            $thesisPapers = [];
            foreach ($results as $result) {
                $thesisPapers[] = [
                    'id' => $result->id,
                    'title' => $result->title,
                    'authors' => $result->authors,
                    'adviser' => $result->adviser,
                    'abstract' => $result->abstract,
                    'department' => $result->department,
                    'uploadDate' => $result->uploadDate,
                    'logo' => $result->logo
                ];
            }
            
            return $thesisPapers;
            
        } catch (Exception $e) {
            error_log("Error fetching all thesis: " . $e->getMessage());
            return [];
        }
    }
}
?>