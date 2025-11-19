<?php

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show error
    header('Location: indexlogin.php');
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


error_reporting(E_ALL);
ini_set('display_errors', 1); // Changed to 1 to see errors during development

require_once '../../../Database/config.php'; 
require_once '../../../app/Controllers/AdminDashboardController.php';
require_once '../../../app/Models/Thesis.php';
require_once '../../../app/Controllers/RolesController.php';

try {
    $db = new Database();
    $thesisModel = new Thesis($db);
    $thesis = $thesisModel->getAllTheses();

    // Get user role information
    $userId = $_SESSION['user_id'];
    $rolesController = new RolesController($db);
    
    // ACTUALLY CALL THE METHOD TO GET THE DATA
    $userRoleData = $rolesController->getUserRole($userId);
    
    // Extract the actual role string
    $userRole = $userRoleData['User_Role'] ?? 'student'; // Default to student if not found
    
    // Determine if user is student
    $isStudent = ($userRole === 'student');
    
    // Debug information
    echo "<!-- Debug: Found " . count($thesis) . " theses -->";
    echo "<!-- Debug: User role data: " . print_r($userRoleData, true) . " -->";
    echo "<!-- Debug: User role: " . $userRole . " -->";
    echo "<!-- Debug: Is student: " . ($isStudent ? 'true' : 'false') . " -->";
    
    foreach ($thesis as $theses) {
        $uploadDate = new DateTime($theses->uploaded_at);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($uploadDate);
        $theses->days_ago = $interval->days;
        $theses->is_recent = $interval->days <= 7;
    }
    
} catch (Exception $e) {
    error_log("Error loading theses: " . $e->getMessage());
    echo "<!-- Error: " . $e->getMessage() . " -->";
    $thesis = [];
    $userRole = 'student'; // Default fallback
    $isStudent = true;
}

class DepartmentManager {
    private $departments = [];
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function addDepartment($value, $name, $courseCodes = []) {
        $this->departments[] = [
            'value' => $value,
            'name' => $name,
            'course_codes' => $courseCodes
        ];
    }
    
    public function getDepartmentCount($courseCodes) {
        if (empty($courseCodes)) {
            return 0;
        }
        
        try {
            // Create placeholders for the IN clause
            $placeholders = str_repeat('?,', count($courseCodes) - 1) . '?';
            $sql = "SELECT COUNT(*) as count FROM thesis WHERE Thesis_Course IN ($placeholders)";
            
            $this->db->query($sql);
            
            // Bind parameters
            foreach ($courseCodes as $index => $courseCode) {
                $this->db->bind($index + 1, $courseCode);
            }
            
            $result = $this->db->singleAssoc();
            return $result['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error getting department count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getTotalCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM thesis";
            $this->db->query($sql);
            $result = $this->db->singleAssoc();
            
            return $result['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error getting total count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function displayOptions() {
        // Get total count for "All Departments"
        $totalCount = $this->getTotalCount();
        
        echo '<div class="options">';
        printf(
            '<div data-value="all">All Courses (%d)</div>',
            $totalCount
        );
        
        foreach ($this->departments as $dept) {
            $count = $this->getDepartmentCount($dept['course_codes']);
            printf(
                '<div data-value="%s">%s (%d)</div>',
                htmlspecialchars($dept['value']),
                htmlspecialchars($dept['name']),
                $count
            );
        }
        echo '</div>';
    }
    
    // Method to get course codes for a specific department
    public function getCourseCodesByDepartment($departmentValue) {
        if ($departmentValue === 'all') {
            return []; // Empty array means all courses
        }
        
        foreach ($this->departments as $dept) {
            if ($dept['value'] === $departmentValue) {
                return $dept['course_codes'];
            }
        }
        
        return []; // Return empty array if department not found
    }
}

$departmentManager = new DepartmentManager($db);

$departmentManager->addDepartment('beced', 'BECED | AECES', ['Bachelor of Early Childhood Education']);
$departmentManager->addDepartment('bsed', 'BSED | AFSET', ['Bachelor of Secondary Education']);
$departmentManager->addDepartment('btvted', 'BTVTED | FTVETS', ['Bachelor of Technical-Vocational Teacher Education']);
$departmentManager->addDepartment('beed', 'BEED | OFEE', ['Bachelor of Elementary Education']);
$departmentManager->addDepartment('bsned', 'BSNED | OFSET', ['Bachelor of Special Needs Education']);
$departmentManager->addDepartment('bsabe', 'BSABE | SABES', [
    'Bachelor of Science in Agricultural and Biosystems Engineering', 'Bachelor of Science in Agriculture and Biosystems Engineering'
]);
$departmentManager->addDepartment('bsit', 'BSIT | SITS', ['Bachelor of Science in Information Technology']);
// Start session




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Compendium Dashboard</title>
    <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="../../../Resources/css/User/userViewPage.css">
    <script type="text/javascript" src="../../../resources/js/User/userViewPage.js"></script>
    <script type="text/javascript" src="../../../resources/js/User/Profile.js"></script>
    
</head>
<body>
<div class="dashboard-container">
    <section class="sidebar">
        <div class="logo">
             <i class="fa fa-graduation-cap icon" aria-hidden="true"></i>
        </div>

        <nav>
            <ul class="menu-options">
                <li class="selected"> <i class="fa fa-th-large icon" aria-hidden="true"></i> </li>
                <li id="profileSidebarIcon"> <i class="fa fa-user-o icon" aria-hidden="true"></i> </li>
            </ul>
        </nav>
    </section>

    <section class="main-content">
    <header class="header" id="header">
            <div class="title">Thesis Repository</div>
            <div class="menu">
                <button class="selected" id="recentButton"> Recent </button>
                <button id="allButton"> All </button>
            </div>
        </header>

        <section class="app-content">
           <!-- Profile Container (initially hidden) -->
        <div class="profile-container" id="profileContainer" style="display: none;">
        <header class="header" id="header">
            <div class="title">Profile</div>
        </header>
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="../../../resources/Images/profile.png" alt="Profile" class="profile-image">
                        <div class="online-status"></div>
                    </div>
                    <div class="profile-info">
                        <h2 data-value="FullName" class="profile-name"></h2>
                        <p data-value="roleHeader" class="profile-title"></p>
                        
                    </div>
                </div>

                <div class="profile-content">
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fa fa-user-o icon" aria-hidden="true"></i>
                            Personal Information
                        </h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">User ID:</span>
                                <span data-value="userID" class="info-value"></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span data-value="email" class="info-value"></span>
                            </div>
                            <?php if (!$isStudent): // Show department for non-students (faculty, admin, etc.) ?>
                            <div class="info-item">
                                <span class="info-label">Department:</span>
                                <span data-value="department" class="info-value"></span>
                            </div>
                            <?php else: // Show course for students ?>
                            <div class="info-item">
                                <span class="info-label">Course:</span>
                                <span data-value="course" class="info-value"></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fa fa-cog icon" aria-hidden="true"></i>
                            Account Settings
                        </h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Member Since:</span>
                                <span data-value="member" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login:</span>
                                <span data-value="lastlogin" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span data-value="acc_status" class="info-value status-active"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Role:</span>
                                <span data-value="role" class="info-value"></span>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-primary">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                Edit Profile
                            </button>
                            <button class="btn btn-secondary" id="logoutHeaderIcon">
                                <i class="fa fa-sign-out" aria-hidden="true"></i>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <div class="app-content-header">
                <div class="searchbox">
                    <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                    <input type="text" name="search" placeholder="Search thesis" class="search-text" id="searchInput">
                </div>

                <div class="app-list-options">
                    <!-- Department Filter Dropdown -->
                    <div class="select" id="departmentFilterDropdown">
                    <div class="selected">
                        <span>All Courses</span>
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                            <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                        </svg>
                    </div>
                    <?php $departmentManager->displayOptions(); ?>
                </div>
                    
                    <!-- Sort Dropdown (renamed from filterDropdown) -->
                    <div class="select" id="sortDropdown">
                            <div class="selected">
                        <span>Sort by: Recent</span>
                                <i class="fa fa-filter" style="margin-left: 3vw; position: absolute; right: 2.5vw;" aria-hidden="true"></i>
                                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                                </svg>
                            </div>
                            <div class="options">
                        <div data-value="recent">Recent</div>
                        <div data-value="Oldest">Oldest</div>
                        <div data-value="title">Title (A-Z)</div>
                        <div data-value="titleReversed">Title (Z-A)</div>
                            </div>
                        </div>

                    <div class="display-group">
                        <div class="icon" id="listViewIcon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
                        <div class="icon selected" id="gridViewIcon"> <i class="fa fa-th" aria-hidden="true"></i> </div>
                    </div>
                </div>
            </div>

            <div class="projects-container">
                <!-- Recent View -->
                <ul class="projects" id="recentView">
                    
                    <?php
                    $hasRecentTheses = false;
                    if (count($thesis) > 0) {
                        foreach ($thesis as $theses) {
                            if ($theses->is_recent) {
                                $hasRecentTheses = true;
                                displayThesisItem($theses);
                            }
                        }
                    }
                    if (!$hasRecentTheses) {
                        echo '<li class="no-theses">No recent theses found.</li>';
                    }
                    ?>
                </ul> 

                <!-- All View -->
                <ul class="projects all-projects" id="allView">
                    <?php
                    if (count($thesis) > 0) {
                        foreach ($thesis as $theses) {
                            displayThesisItem($theses);
                        }
                    } else {
                        echo '<li class="no-theses">No theses found.</li>';
                    }
                    ?>
                </ul>

                <p class="notFound" id="notFound" style="display: none;">No Results Found.</p>
            </div>
        </section>
    </section>
</div>

<!-- Document Preview Modal -->
<div class="modal-overlay preview-modal" id="previewModal">
    <div class="modal preview-modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Document Preview</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="project-info-preview" class="project-info-preview">

            </div>
            <div id="document-viewer">
                <iframe id="doc-viewer-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                <div id="pdf-viewer" style="display: none; width: 100%; height: 500px;"></div>
                <div id="unsupported-file" style="display: none; text-align: center; padding: 50px;">
                    <i class="fa fa-exclamation-triangle" style="font-size: 48px; color: #ff9800;"></i>
                    <h3>Preview not available</h3>
                    <p>This file type cannot be previewed in the browser.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <!-- PDF Footer Controls - positioned on the left -->
            <div id="pdf-footer-controls" class="pdf-footer-controls" style="display: none;">
                <button id="prev-page-footer" type="button" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <span class="pdf-page-info">
                    Page <span id="pdf-page-num-footer">1</span> of <span id="pdf-total-pages">0</span>
                </span>
                <button id="next-page-footer" type="button" class="btn btn-outline-secondary">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Action buttons - positioned on the right -->
            <div class="modal-footer-actions">
                
                <a id="download-link" class="btn btn-primary" style="display: none;">
                    <i class="fas fa-download"></i> Download Abstract
                </a>
                
                
            </div>
        </div>
    </div>
</div>

</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
</html>

<?php
// Helper function to display thesis item
function displayThesisItem($theses) {
    // Debug: Check if ID exists
    if (!isset($theses->ID) || empty($theses->ID)) {
        error_log("Thesis ID missing for thesis: " . ($theses->Title ?? 'Unknown Title'));
        // Use a fallback or skip this item
        return; // Skip items without ID
    }
    
    // Set default values for missing properties
    $thesisId = $theses->ID ?? 'unknown';
    $Author = htmlspecialchars($theses->Author ?? 'Unknown Author');
    $Adviser = htmlspecialchars($theses->Adviser ?? 'Not specified');
    $Title = htmlspecialchars($theses->Title ?? 'Untitled Thesis');
    $hardboundValue = htmlspecialchars($theses->HardBound_Available ?? 'No');
    $Department = htmlspecialchars($theses->Thesis_Department ?? 'Unknown Department');
    $depWeight = '900';
    $depSize = '1vw';
    $margin = '1vw';
    $Course = htmlspecialchars($theses->Thesis_Course ?? 'Unknown Course');

    $formattedDate = isset($theses->uploaded_at) ? date('M j, Y', strtotime($theses->uploaded_at)) : 'Unknown date';
    $daysAgo = $theses->days_ago ?? 0;
    
    $affirmativeValues = ['Yes', 'true', '1', 'available', 'y'];
    $isHardboundAvailable = in_array(strtolower($hardboundValue), array_map('strtolower', $affirmativeValues));

    $iconColor = $isHardboundAvailable ? '#55dcb3' : '#ff6b6b';
    $statusText = $isHardboundAvailable ? 'Hardbound Available' : 'Hardbound Unavailable';
    $iconClass = $isHardboundAvailable ? 'fa-circle-check' : 'fa-circle-xmark';

    // Determine days ago text
    $daysAgoText = '';
    if ($daysAgo == 0) {
        $daysAgoText = 'Today';
    } elseif ($daysAgo == 1) {
        $daysAgoText = 'Yesterday';
    } else {
        $daysAgoText = $daysAgo . ' days ago';
    }
    
    echo '<li class="project-item" data-tags="" data-thesis-id="' . $thesisId . '" data-upload-date="' . ($theses->uploaded_at ?? '') . '" data-days-ago="' . $daysAgo . '" data-is-recent="' . ($theses->is_recent ? 'true' : 'false') . '">';
    echo '<div class="logo-row">';
    echo '<img src="/CapstoneTracker/resources/Images/usep-logo-small.png" alt="Logo" />';
    
    echo '<div class="moreOptions" style="display: none;">';
    echo '<button><i class="fa-solid fa-pen"></i>Edit</button>';
    echo '<button><i class="fa-solid fa-trash-can"></i>Delete</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="title-row">';
    echo '<h3>' . $Title . '</h3>';
    
    echo '<div class="links">';
    echo '<p style="font-weight: ' . $depWeight . '; font-size: ' . $depSize . '; ">' . $Department . '</p>';
    echo '<p style="margin-bottom:'. $margin .'">' . $Course . '</p>';
    echo '<p href="#">' . $formattedDate . '</p>';
    
    echo '</div>';
    echo '</div>';
    echo '<div class="desc-row">';
    echo '<p class="author"><strong>Author:</strong> ' . $Author . '</p>';
    echo '<p class="adviser"><strong>Adviser:</strong> ' . $Adviser . '</p>';
    echo '</div>';
    echo '<div class="users">';
    echo '<p class="available" style="color: ' . $iconColor . ' !important;">';
    echo '<i class="fa-solid ' . $iconClass . '" style="color: ' . $iconColor . ' !important;"></i>';
    echo '&nbsp;&nbsp;' . $statusText;
    echo '</p>';
    echo '</div>';
    echo '<div class="footer-row">';
    echo '<div class="days warning">';
    echo '<i class="fa fa-clock-o icon" aria-hidden="true"></i> ' . $daysAgoText;
    echo '</div>';
    echo '</div>';
    echo '</li>';
}
?>