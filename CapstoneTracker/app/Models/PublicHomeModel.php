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
    public function searchThesis($query, $department = 'all', $sort = 'recent', $limit = 8, $offset = 0) {
    try {
        $sql = "
            SELECT 
                t.ID as id,
                t.Title as title,
                t.Author as authors,
                t.Adviser as adviser,
                t.Thesis_AbstractFile as abstract,
                CONCAT(t.Thesis_Course, ' | ', 
                        CASE 
                            WHEN t.Thesis_Course LIKE '%Information Technology%' OR t.Thesis_Course = 'BSIT' THEN 'CTET | SITS'
                            WHEN t.Thesis_Course LIKE '%Agricultural%Biosystems%' OR t.Thesis_Course = 'BSABE' THEN 'COE | SABES'
                            WHEN t.Thesis_Course LIKE '%Early Childhood%' OR t.Thesis_Course = 'BECED' THEN 'CTET | AECES'
                            WHEN t.Thesis_Course LIKE '%Special Needs%' OR t.Thesis_Course = 'BSNED' THEN 'CTET | OFSET'
                            WHEN t.Thesis_Course LIKE '%Technical%Vocational%' OR t.Thesis_Course = 'BTVTED' THEN 'CTET | FTVETS'
                            WHEN t.Thesis_Course LIKE '%Elementary Education%' OR t.Thesis_Course = 'BEED' THEN 'CTET | OFEE'
                            WHEN t.Thesis_Course LIKE '%Secondary Education%' OR t.Thesis_Course = 'BSED' THEN 'CTET | AFSET'
                            ELSE CONCAT('OTHER | ', t.Thesis_Course)
                        END
                    ) as department,
                t.uploaded_at as uploadDate,
                CASE 
                    WHEN t.Thesis_Course LIKE '%Information Technology%' OR t.Thesis_Course = 'BSIT' THEN '../../../resources/images/SITS_LOGO1.png'
                    WHEN t.Thesis_Course LIKE '%Agricultural%Biosystems%' OR t.Thesis_Course = 'BSABE' THEN '../../../resources/images/SABES_LOGO1.png'
                    WHEN t.Thesis_Course LIKE '%Early Childhood%' OR t.Thesis_Course = 'BECED' THEN '../../../resources/images/AECES_LOGO1.png'
                    WHEN t.Thesis_Course LIKE '%Special Needs%' OR t.Thesis_Course = 'BSNED' THEN '../../../resources/images/OFSET_LOGO1.png'
                    WHEN t.Thesis_Course LIKE '%Technical%Vocational%' OR t.Thesis_Course = 'BTVTED' THEN '../../../resources/images/FTVETS_LOGO1.png'
                    WHEN t.Thesis_Course LIKE '%Elementary Education%' OR t.Thesis_Course = 'BEED' THEN '../../../resources/images/OFEE_LOGO1.png'
                    WHEN t.Thesis_Course LIKE '%Secondary Education%' OR t.Thesis_Course = 'BSED' THEN '../../../resources/images/AFSET_LOGO1.png'
                    ELSE '../../../resources/images/usep-logo-small.png'
                END as logo
            FROM thesis t
            WHERE 1=1
        ";
        
        // Add search condition only if query is not empty
        if (!empty($query)) {
            $sql .= " AND (t.Title LIKE :query 
               OR t.Author LIKE :query 
               OR t.Adviser LIKE :query 
               OR t.Thesis_AbstractFile LIKE :query
               OR t.Thesis_Course LIKE :query)";
        }
        
        // Add department filter if specified and not 'all'
        if ($department !== 'all') {
            // Map short codes to actual database patterns
            $coursePatterns = [
                'BSIT'   => ['%Information Technology%', 'BSIT'],
                'BSABE'  => ['%Agricultural%Biosystems%', 'BSABE'],
                'BECED'  => ['%Early Childhood%', 'BECED'],
                'BSNED'  => ['%Special Needs%', 'BSNED', '%Special Education%'],
                'BTVTED' => ['%Technical%Vocational%', 'BTVTED', '%Technology%Livelihood%'],
                'BEED'   => ['%Elementary Education%', 'BEED'],
                'BSED'   => ['%Secondary Education%', 'BSED']
            ];
            
            if (isset($coursePatterns[$department])) {
                $patterns = $coursePatterns[$department];
                $placeholders = [];
                foreach ($patterns as $index => $pattern) {
                    $placeholders[] = ":dept_pattern_$index";
                }
                $sql .= " AND (t.Thesis_Course LIKE " . implode(" OR t.Thesis_Course LIKE ", $placeholders) . ")";
            }
        }

        // Add sorting
        switch ($sort) {
            case 'title':
                $sql .= " ORDER BY t.Title ASC";
                break;
            case 'department':
                $sql .= " ORDER BY t.Thesis_Course ASC";
                break;
            default: // recent
                $sql .= " ORDER BY t.uploaded_at DESC";
        }

        // Add pagination
        $sql .= " LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        
        // Bind search parameter only if query is not empty
        if (!empty($query)) {
            $this->db->bind(':query', "%$query%");
        }
        
        // Bind department patterns if filtering
        if ($department !== 'all' && isset($coursePatterns[$department])) {
            $patterns = $coursePatterns[$department];
            foreach ($patterns as $index => $pattern) {
                $this->db->bind(":dept_pattern_$index", $pattern);
            }
        }
        
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

        // DEBUG: Log the results
        error_log("Search results for department '$department': " . count($thesisPapers) . " found");
        
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
                       CASE t.Thesis_Course 
                           WHEN 'BSIT' THEN 'SITS'
                           WHEN 'BSABE' THEN 'SABES'
                           WHEN 'BECED' THEN 'AECES'
                           WHEN 'BSNED' THEN 'OFSET'
                           WHEN 'BTVTED' THEN 'FTVETS'
                           WHEN 'BEED' THEN 'OFEE'
                           WHEN 'BSED' THEN 'AFSET'
                           ELSE t.Thesis_Course
                       END) as department,
                t.uploaded_at as uploadDate,
                CASE t.Thesis_Course 
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
                       CASE t.Thesis_Course 
                           WHEN 'BSIT' THEN 'SITS'
                           WHEN 'BSABE' THEN 'SABES'
                           WHEN 'BECED' THEN 'AECES'
                           WHEN 'BSNED' THEN 'OFSET'
                           WHEN 'BTVTED' THEN 'FTVETS'
                           WHEN 'BEED' THEN 'OFEE'
                           WHEN 'BSED' THEN 'AFSET'
                           ELSE t.Thesis_Course
                       END) as department_display,
                CASE t.Thesis_Course 
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
                (SELECT COUNT(*) FROM thesis) AS total_papers,

                /* Count total authors using comma logic */
                (
                    SELECT SUM(
                        LENGTH(Author) - LENGTH(REPLACE(Author, ',', '')) + 1
                    )
                    FROM thesis
                    WHERE Author IS NOT NULL AND Author != ''
                ) AS total_authors,

                (SELECT COUNT(DISTINCT Thesis_Course) 
                 FROM thesis 
                 WHERE Thesis_Course IS NOT NULL AND Thesis_Course != '') 
                AS total_departments
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
 */
public function getProgramThesisCounts() {
    $sql = "
        SELECT 
            p.code,
            p.name,
            COUNT(t.ID) AS thesis_count
        FROM PROGRAMS p
        LEFT JOIN THESIS t ON t.Thesis_Course = p.code
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
            WHERE 1=1
        ";
        
        if (!empty($query)) {
            $sql .= " AND (t.Title LIKE :query 
               OR t.Author LIKE :query 
               OR t.Adviser LIKE :query 
               OR t.Thesis_AbstractFile LIKE :query
               OR t.Thesis_Course LIKE :query)";
        }
        
        if ($department !== 'all') {
            $coursePatterns = [
                'BSIT'   => ['%Information Technology%', 'BSIT'],
                'BSABE'  => ['%Agricultural%Biosystems%', 'BSABE'],
                'BECED'  => ['%Early Childhood%', 'BECED'],
                'BSNED'  => ['%Special Needs%', 'BSNED', '%Special Education%'],
                'BTVTED' => ['%Technical%Vocational%', 'BTVTED', '%Technology%Livelihood%'],
                'BEED'   => ['%Elementary Education%', 'BEED'],
                'BSED'   => ['%Secondary Education%', 'BSED']
            ];
            
            if (isset($coursePatterns[$department])) {
                $patterns = $coursePatterns[$department];
                $placeholders = [];
                foreach ($patterns as $index => $pattern) {
                    $placeholders[] = ":dept_pattern_$index";
                }
                $sql .= " AND (t.Thesis_Course LIKE " . implode(" OR t.Thesis_Course LIKE ", $placeholders) . ")";
            }
        }

        $this->db->query($sql);
        
        if (!empty($query)) {
            $this->db->bind(':query', "%$query%");
        }
        
        if ($department !== 'all' && isset($coursePatterns[$department])) {
            $patterns = $coursePatterns[$department];
            foreach ($patterns as $index => $pattern) {
                $this->db->bind(":dept_pattern_$index", $pattern);
            }
        }

        $result = $this->db->single();
        return $result->total ?? 0;

    } catch (Exception $e) {
        error_log("Error getting search count: " . $e->getMessage());
        return 0;
    }
}


/**
 * Get thesis counts 
 */
public function getCourseThesisCounts() {
    try {
        $this->db->query("
            SELECT 
                Thesis_Course AS department,
                COUNT(*) AS count
            FROM thesis 
            GROUP BY Thesis_Course
            ORDER BY Thesis_Course
        ");
        
        $results = $this->db->resultSet();
        
        // First, collect raw counts by full name
        $rawCounts = [];
        foreach ($results as $row) {
            $fullName = trim($row->department ?? '');
            if ($fullName) {
                $rawCounts[$fullName] = (int)$row->count;
            }
        }
        
        // Mapping: Full name patterns → Short codes
        $nameToCodeMap = [
            'BSIT' => ['Bachelor of Science in Information Technology', 'BS Information Technology', 'Information Technology'],
            'BEED' => ['Bachelor of Elementary Education', 'BE Education', 'Elementary Education'],
            'BSED' => ['Bachelor of Secondary Education', 'BS Education', 'Secondary Education'],
            'BSABE' => ['Bachelor of Science in Agricultural and Biosystems Engineering', 'BS Agricultural Engineering', 'Agricultural and Biosystems Engineering'],
            'BECED' => ['Bachelor of Early Childhood Education', 'BE Childhood Education', 'Early Childhood Education'],
            'BSNED' => ['Bachelor of Special Needs Education', 'BS Special Education', 'Special Needs Education', 'Special Education'],
            'BTVTED' => ['Bachelor of Technology and Vocational Teacher Education', 'BTVTE', 'Technical-Vocational Education', 'Technology and Livelihood Education']
        ];
        
        // Build final counts by code
        $counts = [];
        foreach ($nameToCodeMap as $code => $patterns) {
            $totalForCode = 0;
            foreach ($patterns as $pattern) {
                foreach ($rawCounts as $storedName => $cnt) {
                    if (stripos($storedName, $pattern) !== false) {  
                        $totalForCode += $cnt;
                        unset($rawCounts[$storedName]);  
                    }
                }
            }
            $counts[$code] = $totalForCode;
        }
        
 
        $allExpected = ['BSIT', 'BSABE', 'BECED', 'BSNED', 'BTVTED', 'BEED', 'BSED'];
        foreach ($allExpected as $code) {
            if (!isset($counts[$code])) {
                $counts[$code] = 0;
            }
        }
        
        return $counts;
        
    } catch (Exception $e) {
        error_log("Error getting course thesis counts: " . $e->getMessage());
        
        return ['BSIT'=>0, 'BSABE'=>0, 'BECED'=>0, 'BSNED'=>0, 'BTVTED'=>0, 'BEED'=>0, 'BSED'=>0];
    }
}



}
?>