<?php

require_once __DIR__ . '/Database.php';  
/**
 * Public Home Model
 * Handles data operations for the public home page
 */

class PublicHomeModel {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new Database();
            $this->testConnection();
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    private function testConnection() {
        try {
            $this->db->query("SELECT 1");
            $this->db->execute();
        } catch (Exception $e) {
            throw new Exception("Database test query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get all active announcements (lowercase table)
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
                is_pinned,
                status,
                '../../../resources/images/Announcement_pic.png' as image 
            FROM ANNOUNCEMENTS 
            WHERE status = 'published' 
            AND (end_date IS NULL OR end_date >= NOW())
            ORDER BY is_pinned DESC, start_date DESC
        ");
        
        $results = $this->db->resultSet();
        
        $announcements = array_map(function($obj) {
            return (array) $obj;
        }, $results ?? []);
        
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
        // Since programs are fixed, we return the static data
        return $this->getFixedPrograms();
    }
    
    /**
     * Search thesis papers (lowercase table)
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
                        ELSE '../../../resources/images/usep-logo-small.png'
                    END as logo
                FROM thesis t
                WHERE (t.Title LIKE :query 
                   OR t.Author LIKE :query 
                   OR t.Adviser LIKE :query 
                   OR t.Thesis_AbstractFile LIKE :query
                   OR t.Thesis_Department LIKE :query)
            ";
            
            // Add department filter if specified
            if ($department !== 'all') {
                $sql .= " AND t.Thesis_Department = :department";
            }

            // Add sorting
            switch ($sort) {
                case 'popular':
                    $sql .= " ORDER BY t.views DESC"; 
                    break;
                case 'title':
                    $sql .= " ORDER BY t.Title ASC";
                    break;
                case 'department':
                    $sql .= " ORDER BY t.Thesis_Department ASC";
                    break;
                default: // recent
                    $sql .= " ORDER BY t.uploaded_at DESC";
            }

            $this->db->query($sql);
            $this->db->bind(':query', "%$query%");
            if ($department !== 'all') {
                $this->db->bind(':department', $department);
            }

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
            error_log("Error searching thesis: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all thesis with pagination (lowercase table)
     */
    public function getAllThesis($limit = 10, $offset = 0) {
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
                FROM thesis t
                ORDER BY t.uploaded_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            $this->db->bind(':offset', $offset, PDO::PARAM_INT);
            
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

    /**
     * Get thesis by ID (lowercase table)
     */
    public function getThesisById($id) {
        try {
            $this->db->query("
                SELECT 
                    t.*,
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
                           END) as department_display,
                    CASE t.Thesis_Department 
                        WHEN 'BSIT' THEN '../../../resources/images/SITS_LOGO.png'
                        WHEN 'BSABE' THEN '../../../resources/images/SABES_LOGO.png'
                        WHEN 'BECED' THEN '../../../resources/images/AECES_LOGO.png'
                        WHEN 'BSNED' THEN '../../../resources/images/OFSET_LOGO.png'
                        WHEN 'BTVTED' THEN '../../../resources/images/FTVETS_LOGO.png'
                        WHEN 'BEED' THEN '../../../resources/images/OFEE_LOGO.png'
                        WHEN 'BSED' THEN '../../../resources/images/AFSET_LOGO.png'
                        ELSE '../../../resources/images/usep-logo-small.png'
                    END as logo
                FROM thesis t
                WHERE t.ID = :id
            ");
            
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            
            return $result ? (array)$result : null;
            
        } catch (Exception $e) {
            error_log("Error fetching thesis by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Fixed programs data
     */
    private function getFixedPrograms() {
        return [
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
    }
    
   
    /**
     * Get thesis stats (lowercase table + subqueries for robustness)
     */
    public function getThesisStats() {
    try {
        $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM thesis) as total_papers,
                (SELECT COUNT(DISTINCT Author) FROM thesis WHERE Author IS NOT NULL AND Author != '') as total_authors,
                (SELECT COUNT(DISTINCT Thesis_Course) FROM thesis WHERE Thesis_Course IS NOT NULL AND Thesis_Course != '') as total_departments
        ");

        $result = $this->db->single();

        // DEBUG LOG
        error_log("STATS RESULT: " . print_r($result, true));

        return [
            'total_papers'       => (int)($result->total_papers ?? 0),
            'total_authors'      => (int)($result->total_authors ?? 0),
            'total_departments'  => (int)($result->total_departments ?? 0)
        ];

    } catch (Exception $e) {
        error_log("STATS ERROR: " . $e->getMessage());
        return [0, 0, 0];
    }
}

/**
 * Get all programs with the number of approved thesis papers
 * @return array [code, name, thesis_count]
 */
public function getProgramThesisCounts() {
    $sql = "
        SELECT 
            p.code,
            p.name,
            COUNT(t.ID) AS thesis_count
        FROM PROGRAMS p
        LEFT JOIN THESIS t ON t.Thesis_Department = p.code
            AND t.HardBound_Available = 'Yes'
        GROUP BY p.code, p.name
        ORDER BY p.name
    ";

    $this->db->query($sql);
    $this->db->execute();
    return $this->db->resultSet();
}


/**
 * Get search count for pagination
 */
public function getSearchCount($query, $department = 'all') {
    try {
        $sql = "
            SELECT COUNT(*) as total
            FROM thesis t
            WHERE (t.Title LIKE :query 
               OR t.Author LIKE :query 
               OR t.Adviser LIKE :query 
               OR t.Thesis_AbstractFile LIKE :query
               OR t.Thesis_Department LIKE :query)
        ";
        
        if ($department !== 'all') {
            $sql .= " AND t.Thesis_Department = :department";
        }

        $this->db->query($sql);
        $this->db->bind(':query', "%$query%");
        if ($department !== 'all') {
            $this->db->bind(':department', $department);
        }

        $result = $this->db->single();
        return $result->total ?? 0;

    } catch (Exception $e) {
        error_log("Error getting search count: " . $e->getMessage());
        return 0;
    }
}
}
?>