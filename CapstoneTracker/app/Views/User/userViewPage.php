<?php

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show error
    header('Location: indexLogin.php');
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
    
    // Debug: Check if we got any data
    echo "<!-- Debug: Found " . count($thesis) . " theses -->";
    
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
    <link rel="stylesheet" href="../../../resources/css/User/home.css">
    <script type="text/javascript" src="../../../resources/js/User/userViewPage.js"></script>
    <script type="text/javascript" src="../../../resources/js/User/Profile.js"></script>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    
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
        <div class="more-options">

                <i class="fa fa-ellipsis-h icon" aria-hidden="true"></i>


            </div>
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
                        <img id="profilePicture" src="../../../resources/Images/profile.png" alt="Profile" class="profile-image" data-default-src="../../../resources/Images/profile.png">
                        <div class="online-status"></div>
                    </div>
                    <div class="profile-info">
                        <h2 data-value="FullName" class="profile-name"></h2>
                        <p data-value="course" class="profile-title"></p>
                        
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
                                <span class="info-label">Full Name:</span>
                                <span data-value="FullName" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span data-value="email" class="info-value"></span>
                            </div>
                            
                            
                            <div class="info-item">
                                <span class="info-label">Course:</span>
                                <span data-value="course" class="info-value"></span>
                            </div>
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
                                <span data-value="member_since" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login:</span>
                                <span data-value="last_login" class="info-value"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status:</span>
                                <span data-value="acc_status" class="info-value status-active"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Role:</span>
                                <span data-value="role" class="info-value">Student</span>
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

            <!-- Landing page (Home style) shown first -->
            <section id="landingSection" class="dashboard-banner" style="margin-bottom: 1rem;">
                <div class="banner-content">
                    <h2>Discover Academic Excellence</h2>
                    <p>Access theses across departments and programs</p>
                    <div class="search-container">
                        <div class="searchbox">
                            <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                            <input type="text" id="landingSearchInput" placeholder="Enter keywords, title, author, or adviser...">
                            <button class="search-btn" id="landingSearchBtn"><i class="fa fa-search" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number">Theses</span>
                            <span class="stat-label">Explore the repository</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">Researchers</span>
                            <span class="stat-label">Join the community</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">Departments</span>
                            <span class="stat-label">Filter by program</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Announcements (UI only, same structure as home) -->
            <section id="landingAnnouncements" class="announcements-section" style="margin-bottom: 1rem;">
                <div class="section-header">
                    <h2>Announcements</h2>
                </div>
                <div class="announcements-carousel">
                    <button class="carousel-control prev"><i class="fas fa-chevron-left"></i></button>
                    <div class="carousel-container">
                        <div class="announcement-cards">
                            <!-- Optional: cards can be injected by your existing scripts later -->
                        </div>
                    </div>
                    <button class="carousel-control next"><i class="fas fa-chevron-right"></i></button>
                    <div class="carousel-indicators"></div>
                </div>
            </section>

            <!-- Programs (UI only) -->
            <section id="landingPrograms" class="program-logos-section" style="margin-bottom: 1rem;">
                <div class="section-header">
                    <h2>Programs</h2>
                </div>
                <div class="logo-carousel">
                    <button class="carousel-control prev"><i class="fas fa-chevron-left"></i></button>
                    <div class="carousel-container">
                        <div class="logo-cards"></div>
                    </div>
                    <button class="carousel-control next"><i class="fas fa-chevron-right"></i></button>
                    <div class="carousel-indicators"></div>
                </div>
            </section>

            <!-- Quick Access (UI only) -->
            <section id="landingQuickActions" class="quick-actions" style="margin-bottom: 2rem;">
                <div class="section-header">
                    <h2>Quick Access</h2>
                </div>
                <div class="action-cards">
                    <div class="action-card">
                        <div class="action-icon"><i class="fas fa-book-open"></i></div>
                        <h3>Browse Catalog</h3>
                        <p>Explore all available thesis papers</p>
                    </div>
                    <div class="action-card">
                        <div class="action-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h3>For Researchers</h3>
                        <p>Resources and guidelines for your research</p>
                    </div>
                    <div class="action-card">
                        <div class="action-icon"><i class="fas fa-question-circle"></i></div>
                        <h3>Help Center</h3>
                        <p>Get assistance with the system</p>
                    </div>
                </div>
            </section>

            <!-- Existing app header/UI hidden until a search happens -->
            <div id="appContentShell" style="display:none;">
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
            </div>

            <div class="projects-container" id="projectsContainer" style="display: none;">
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

            <!-- Landing Footer (UI only) -->
            <footer id="landingFooter" class="main-footer" style="display:block;">
                <div class="footer-content">
                    <div class="footer-section">
                        <div>
                            <img class="logo" src="../../../resources/images/ThesisCompLogo.png" alt="Logo" />
                            <img class="logo" src="../../../resources/images/CTET_LOGO.png" alt="Logo" />
                            <h3>Thesis Compendium System</h3>
                        </div>
                        <p class="footer-description">A comprehensive digital repository for thesis papers and capstone projects.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="javascript:void(0)">Home</a></li>
                            <li><a href="javascript:void(0)">Browse</a></li>
                            <li><a href="javascript:void(0)">Guidelines</a></li>
                            <li><a href="javascript:void(0)">Profile</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4>Resources</h4>
                        <ul>
                            <li><a href="javascript:void(0)">Research Guidelines</a></li>
                            <li><a href="javascript:void(0)">Formatting Templates</a></li>
                            <li><a href="javascript:void(0)">Citation Help</a></li>
                            <li><a href="javascript:void(0)">FAQ</a></li>
                            <li><a href="javascript:void(0)">Support Center</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4>Contact Us</h4>
                        <div class="contact-info">
                            <p><i class="fas fa-envelope"></i> thesis@usep.edu.ph</p>
                            <p><i class="fas fa-phone"></i> 0123 456 7890</p>
                            <p><i class="fas fa-map-marker-alt"></i> University of Southeastern Philippines<br>Tagum-Mabini Campus</p>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 Thesis Compendium System. All rights reserved.</p>
                    <div class="footer-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Accessibility</a>
                    </div>
                </div>
            </footer>

</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
    // Bridge landing search to existing userViewPage search logic
    (function(){
        const landing = document.getElementById('landingSection');
        const appShell = document.getElementById('appContentShell');
        const landingInput = document.getElementById('landingSearchInput');
        const landingBtn = document.getElementById('landingSearchBtn');
        const mainInput = document.getElementById('searchInput');
        const projects = document.getElementById('projectsContainer');
        function revealAppWithQuery(q){
            if (!appShell || !landing) return;
            appShell.style.display = 'block';
            landing.style.display = 'none';
            if (projects) projects.style.display = 'block';
            if (mainInput) {
                mainInput.value = q || '';
                // Trigger input event so existing listeners filter immediately
                const ev = new Event('input', { bubbles: true });
                mainInput.dispatchEvent(ev);
                mainInput.focus();
            }
        }
        function onSubmit(){
            const q = (landingInput && landingInput.value || '').trim();
            revealAppWithQuery(q);
        }
        if (landingBtn) landingBtn.addEventListener('click', onSubmit);
        if (landingInput) landingInput.addEventListener('keypress', function(e){ if (e.key === 'Enter') onSubmit(); });
    })();

    // Populate announcements and programs for the landing page (UI only)
    (function(){
        const annCards = document.querySelector('#landingAnnouncements .announcement-cards');
        const annIndicators = document.querySelector('#landingAnnouncements .carousel-indicators');
        const annPrev = document.querySelector('#landingAnnouncements .carousel-control.prev');
        const annNext = document.querySelector('#landingAnnouncements .carousel-control.next');

        const progCards = document.querySelector('#landingPrograms .logo-cards');
        const progIndicators = document.querySelector('#landingPrograms .carousel-indicators');
        const progPrev = document.querySelector('#landingPrograms .carousel-control.prev');
        const progNext = document.querySelector('#landingPrograms .carousel-control.next');

        const fallbackAnnouncements = [
            { title:'System Update', description:'Welcome to the Thesis Compendium System.', date:'2025-01-01', image:'../../../resources/images/Announcement_pic.png', type:'info' },
        ];
        const fallbackPrograms = [
            { name:'SITS', meaning:'Society of Information Technology Students', image:'../../../resources/images/SITS_LOGO.png' },
            { name:'AECES', meaning:'Association of Early Childhood Education', image:'../../../resources/images/AECES_LOGO.png' },
            { name:'AFSET', meaning:'Association of Future Secondary Teachers', image:'../../../resources/images/AFSET_LOGO.png' }
        ];

        function formatDate(s){ try { return new Date(s).toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'}); } catch(e){ return s; } }

        function mountAnnouncements(items){
            if (!annCards) return;
            annCards.innerHTML = '';
            items.forEach((a, i) => {
                const card = document.createElement('div');
                card.className = 'announcement-card';
                card.innerHTML = `
                    <div class="card-badge ${a.type || 'info'}">${(a.type || 'info')[0].toUpperCase() + (a.type || 'info').slice(1)}</div>
                    <div class="card-image"><img src="${a.image}" alt="${a.title}" class="Anncmnt_pic"></div>
                    <div class="card-content">
                        <div class="card-header"><h3>${a.title}</h3><div class="date">${formatDate(a.date)}</div></div>
                        <p>${a.description}</p>
                        <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>`;
                annCards.appendChild(card);
                if (annIndicators){
                    const ind = document.createElement('div');
                    ind.className = `indicator ${i===0?'active':''}`;
                    annIndicators.appendChild(ind);
                }
            });
            wireCarousel('#landingAnnouncements');
        }

        function mountPrograms(items){
            if (!progCards) return;
            progCards.innerHTML = '';
            items.forEach((p,i)=>{
                const card = document.createElement('div');
                card.className = 'logo-card';
                card.innerHTML = `
                    <div class="card-image"><img src="${p.image}" alt="${p.name}" class="dept_pic"></div>
                    <div class="card-content"><div class="card-header"><h3>${p.name}</h3><div class="meaning">${p.meaning}</div></div>
                    <a href="#" class="read-more">View Department <i class="fas fa-arrow-right"></i></a></div>`;
                progCards.appendChild(card);
                if (progIndicators){
                    const ind = document.createElement('div');
                    ind.className = `indicator ${i===0?'active':''}`;
                    progIndicators.appendChild(ind);
                }
            });
            wireCarousel('#landingPrograms');
        }

        function wireCarousel(sel){
            const carousel = document.querySelector(sel);
            if (!carousel) return;
            const cards = carousel.querySelector('.announcement-cards') || carousel.querySelector('.logo-cards');
            const prev = carousel.querySelector('.carousel-control.prev');
            const next = carousel.querySelector('.carousel-control.next');
            const indicators = carousel.querySelectorAll('.indicator');
            if (!cards || cards.children.length === 0) return;
            let current = 0;
            function update(){
                const gap = 24;
                const child = cards.children[0];
                const w = (child && child.getBoundingClientRect().width) ? child.getBoundingClientRect().width + gap : 300;
                cards.scrollTo({ left: current * w, behavior:'smooth' });
                indicators.forEach((d,idx)=>d.classList.toggle('active', idx===current));
            }
            if (prev) prev.addEventListener('click', ()=>{ current = Math.max(0, current-1); update(); });
            if (next) next.addEventListener('click', ()=>{ current = Math.min(cards.children.length-1, current+1); update(); });
        }

        async function loadAnnouncements(){
            try {
                const res = await fetch('../../../app/Controllers/PublicHomeController.php?action=getAnnouncementsAPI');
                const data = await res.json();
                if (data && data.success && Array.isArray(data.announcements) && data.announcements.length) {
                    mountAnnouncements(data.announcements);
                    return;
                }
            } catch(e) { /* fall back */ }
            mountAnnouncements(fallbackAnnouncements);
        }

        async function loadPrograms(){
            try {
                const res = await fetch('../../../app/Controllers/PublicHomeController.php?action=getProgramsAPI');
                const data = await res.json();
                if (data && data.success && Array.isArray(data.programs) && data.programs.length) {
                    mountPrograms(data.programs);
                    return;
                }
            } catch(e) { /* fall back */ }
            mountPrograms(fallbackPrograms);
        }

        // Initialize if the sections are present
        if (annCards) loadAnnouncements();
        if (progCards) loadPrograms();
    })();
</script>
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