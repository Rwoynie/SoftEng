<?php
// AdminDashboard.php - At the VERY TOP of the file
require_once '../../../Database/config.php'; 
require_once '../../../app/Controllers/AdminDashboardController.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../User/indexLogin.php');
    exit();
}

// Get only the necessary user data for display (not the entire session)
$displayUserData = [
    'user_name' => $_SESSION['user_name'] ?? '',
    'user_role' => $_SESSION['user_role'] ?? '',
    
];

// Debug output (remove in production)
error_log("AdminDashboard loaded for user: " . ($_SESSION['user_db_id'] ?? 'Unknown'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="../../../resources/css/Admin/AdminDashboard.css">
    <script type="text/javascript" src="../../../resources/js/Admin/AdminDashboard.js"></script>
    <script>
        const userDisplayData = <?php echo json_encode($displayUserData); ?>;
    </script>
    </head>
<body>
<div class="dashboard-container">
    <section class="sidebar">
        <div class="logo">
            <i class="fa-solid fa-user-tie icon"></i>
        </div>

        <nav>
        <ul class="menu-options">
            <li class="selected" data-view="dashboard"> <i class="fa fa-th-large icon" aria-hidden="true"></i> </li>
            <li id="" data-view="users"><i class="fa-solid fa-fingerprint" aria-hidden="true"></i></li>
            <li id="" data-view="accounts"><i class="fa-solid fa-users" aria-hidden="true"></i></li>
            <li id="" data-view="logs"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></li>
        </ul>
        </nav>

        <div class="more-options">
        
            <i class="fa fa-ellipsis-h icon" aria-hidden="true"></i>
            
            
        </div>
        <div id="user-info-display" style="display: none;">
            <span id="user-full-name"><?php echo htmlspecialchars($displayUserData['user_name']); ?></span>
            <span id="user-role"><?php echo htmlspecialchars($displayUserData['user_role']); ?></span>
        </div>
        
    </section>

    <section class="main-content">
    
                <header class="header" id="header">
                    <div class="title">Published Titles</div>
                    <div class="menu">
                        <button class="selected" id="recentButton"> Recent </button>
                        <button id="allButton"> All </button>
                        
                    </div>

                    
                </header>

        <section class="app-content">
        <div class="app-content-header">
                    <div class="searchbox">
                        <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                        <input type="text" name="search" placeholder="Search thesis..." class="search-text" id="searchInput">
                    </div>

                    <div class="app-list-options">
                <!-- Department Filter Dropdown -->
                <div class="select" id="departmentFilterDropdown">
                    <div class="selected">
                        <span>All Departments</span>
                        
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                            <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                        </svg>
                    </div>
                    <div class="options">
                        <div data-value="all">All Departments</div>
                        <div data-value="cs">Computer Science</div>
                        <div data-value="it">Information Technology</div>
                        <div data-value="ce">Computer Engineering</div>
                        <div data-value="ee">Electrical Engineering</div>
                        <div data-value="me">Mechanical Engineering</div>
                    </div>
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
                        <div data-value="recent">Most Recent</div>
                        <div data-value="popular">Most Viewed</div>
                        <div data-value="title">Title (A-Z)</div>
                        <div data-value="department">Department</div>
                    </div>
                </div>

                <div class="display-group">
                    <div class="icon" id="listViewIcon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
                    <div class="icon selected" id="gridViewIcon"> <i class="fa fa-th" aria-hidden="true"></i> </div>
                </div>
            </div>
                </div>
    

            

            <div class="projects-container">
            <div class="fab-icon"> + </div>

                    

                <ul class="projects" id="recentView">
                    <li class="project-item" data-tags="important enhancement" data-file-url="../Images/Case Study.pdf" data-file-type="pdf">
                        <div  class="logo-row">
                            <img src="/CapstoneTracker/resources/Images/usep-logo-small.png" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3>Sample Thesis Title</h3>
                            <div class="links">
                                <p href="#">Uploaded: September 10, 2025</p>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>John Doe, Jane Smith</p>
                        </div>
                        <div class="users">
                                <p class="available"><i class="fa-solid fa-circle-check" style="color: #63E6BE;"></i>&nbsp&nbspHardbound Available</p>
                                
                            </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 2 days ago
                            </div>
                            
                        </div>
                    </li>

                    <li class="project-item" data-tags="announcement news" data-file-url="../Images/IS-Project-Progress-MonitoringWeek-2.pdf" data-file-type="docx">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?logo" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Homechoice </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> homchoice.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Platform for home decoration ideas and interior design inspiration.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="64"></p>
                            <progress max="100" value="64" data-value="64"> 64% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 4 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?man" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?user" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="discussion interesting" data-file-url="https://file-examples.com/storage/fe8c7eef0c6364f6c9504cc/2017/02/file-sample_1MB.docx" data-file-type="docx">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?estate" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Big Money Real Estate </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> bigmoneyrealestate.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Luxury real estate platform featuring high-end properties worldwide.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="59"></p>
                            <progress max="100" value="59" data-value="59"> 59% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days warning">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 5 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?person" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?profile" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="cannot-fix off-topic" data-file-url="https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf" data-file-type="pdf">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?media" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Springfield Media </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> springfieldmedia.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Digital media company specializing in content creation and distribution.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="94"></p>
                            <progress max="100" value="94" data-value="94"> 94% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 7 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?man" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?woman" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="enhancement change-declined" data-file-url="https://file-examples.com/storage/fe8c7eef0c6364f6c9504cc/2017/02/file-sample_1MB.docx" data-file-type="docx">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?logistics" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Regular Logistics </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> regularlogistics.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Supply chain management solution for small and medium businesses.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="44"></p>
                            <progress max="100" value="44" data-value="44"> 44% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 9 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?user" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?person" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="important news" data-file-url="https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf" data-file-type="pdf">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?agency" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Foursquare Agency </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> foursquareagency.com </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Full-service digital marketing agency focused on growth strategies.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="39"></p>
                            <progress max="100" value="39" data-value="39"> 39% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days danger">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 11 days left
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?profile" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?man" alt="User" />
                            </div>
                        </div>
                    </li>
                </ul>

                <ul class="projects all-projects" id="allView">
                    <li class="project-item" data-tags="cannot-fix off-topic" data-file-url="https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf" data-file-type="pdf">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?network" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> 5G Network Optimization </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> 5goptimization.tech </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Advanced algorithms for optimizing 5G network performance and coverage in urban areas.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="78"></p>
                            <progress max="100" value="78" data-value="78"> 78% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 2 weeks ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?engineer" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?technician" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item " data-tags="enhancement change-declined" data-file-url="https://file-examples.com/storage/fe8c7eef0c6364f6c9504cc/2017/02/file-sample_1MB.docx" data-file-type="docx">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?robot" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Autonomous Robotics </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> autonobot.org </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Development of autonomous navigation systems for industrial and service robotics applications.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="82"></p>
                            <progress max="100" value="82" data-value="82"> 82% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 3 weeks ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?roboticist" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?mechanic" alt="User" />
                            </div>
                        </div>
                    </li>

                    <li class="project-item" data-tags="news discussion" data-file-url="https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf" data-file-type="pdf">
                        <div class="logo-row">
                            <img src="https://source.unsplash.com/50x50/?data" alt="Logo" />
                            <div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>
                        </div>
                        <div class="title-row">
                            <h3> Big Data Analytics </h3>
                            <div class="links">
                                <i class="fa fa-external-link icon" aria-hidden="true"></i>
                                <a href="#"> bigdataresearch.edu </a>
                            </div>
                        </div>
                        <div class="desc-row">
                            <p>Novel approaches to processing and analyzing large-scale datasets for scientific research.</p>
                        </div>
                        <div class="progress-row">
                            <p class="value-label" data-value="91"></p>
                            <progress max="100" value="91" data-value="91"> 91% </progress>
                        </div>
                        <div class="footer-row">
                            <div class="days">
                                <i class="fa fa-clock-o icon" aria-hidden="true"></i> 1 month ago
                            </div>
                            <div class="users">
                                <img src="https://source.unsplash.com/30x30/?analyst" alt="User" />
                                <img src="https://source.unsplash.com/30x30/?statistician" alt="User" />
                            </div>
                        </div>
                    </li>
                </ul>

                <p class="notFound" id="notFound">No Results Found.</p>
            </div>


            <div id="access-container" class="content-container" style="display: none;">
                <header class="header" id="header">
                    <div class="title">Access Management</div>
                </header>

                <div id="accessHeader2" class="content-header">
                    <div class="access-card" id="access-card">
                        <div class="access-list">
                            <button id="adminAccess" class="access-item accessCard">
                                <div class="access-info">
                                    <h4>Administrator</h4>
                                    <p>Full system access</p>
                                </div>
                                <div class="access-count">0 users</div>
                            </button>

                            <button id="facultyAccess" class="access-item accessCard">
                                <div class="access-info">
                                    <h4>Faculty</h4>
                                    <p>Can edit content but not manage users</p>
                                </div>
                                <div class="access-count">0 users</div>
                            </button>

                            <button id="studentAccess" class="access-item accessCard">
                                <div class="access-info">
                                    <h4>Student</h4>
                                    <p>Read-only and download access</p>
                                </div>
                                <div class="access-count">0 users</div>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="content-body">
                    <div id="adminAccessPanel" class="access-card">
                        <div class="searchbox" id="accessSearch">
                            <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                            <input type="text" placeholder="Search users..." class="search-text" id="adminUserSearch">
                        </div>
                        
                        <div class="adminUserListContainer">
                            <div class="access-list" id="adminUserList">
                                <!-- Users will be populated dynamically -->
                                <div class="loading-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading users...</p>
                                </div>
                            </div>
                            
                            <p class="notFound" id="adminNotFound">No users found matching your search.</p>
                        </div>
                    </div>
                </div>
                <div class="fab-icon save-fab" id="saveAdminChangesBtn" title="Save Changes">
                    <i class="fas fa-save"></i>
                </div>
            </div>

            <div id="accounts-container" class="content-container" style="display: none;">
                <header class="logHeader" id="accountHeader">
                    <div class="title">Account Management</div>
                    <div class="logMenu">
                        <button class="selected" id="allAccountsButton">All Accounts</button>
                        <button id="pendingButton">Pending</button>
                        <button id="approvedButton">Approved</button>
                    </div>
                </header>
                
                <div class="app-content-header accountHeader">
                    <div class="searchbox">
                        <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                        <input type="text" name="search" placeholder="Search accounts..." class="search-text" id="accountSearchInput">
                    </div>

                    <div class="app-list-options">
                        <div class="select" id="accountFilterDropdown">
                            <div class="selected">
                                <span>Recently Joined</span>
                                <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512" class="arrow">
                                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"></path>
                                </svg>
                            </div>
                            <div class="options">
                                <div data-value="recent">Recently Joined</div>
                                <div data-value="admin">Administrator</div>
                                <div data-value="faculty">Faculty</div>
                                <div data-value="student">Student</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accounts-table-container">
                    <table class="accounts-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th>Join Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
            // Include the controller
            require_once '../../../app/Controllers/AdminDashboardController.php';
            
            try {
                // Create controller instance
                $adminController = new AdminDashboardController();
                
                // Get users data through the controller
                $usersData = $adminController->getUsersData();
                $users = $usersData['users'] ?? [];
                
                if (count($users) > 0) {
                    foreach ($users as $user) {
                        // Format full name
                        $fullName = htmlspecialchars($user->First_Name);
                        if (!empty($user->Middle_Name)) {
                            $fullName .= ' ' . htmlspecialchars($user->Middle_Name);
                        }
                        $fullName .= ' ' . htmlspecialchars($user->Last_Name);
                        if (!empty($user->Extension)) {
                            $fullName .= ' ' . htmlspecialchars($user->Extension);
                        }
                        
                        // Format email
                        $email = htmlspecialchars($user->Email);
                        
                        // Determine status badge class
                        $statusClass = 'status-pending';
                        $statusText = 'Pending';
                        if ($user->Acc_Status === 'approved') {
                            $statusClass = 'status-approved';
                            $statusText = 'Approved';
                        } elseif ($user->Acc_Status === 'rejected') {
                            $statusClass = 'status-rejected';
                            $statusText = 'Rejected';
                        }
                        
                        // Determine role badge class and display text
                        $roleClass = 'role-student';
                        $roleText = 'Student';
                        if ($user->User_Role === 'admin' || $user->User_Role === 'superAdmin') {
                            $roleClass = 'role-admin';
                            $roleText = 'Admin';
                        } elseif ($user->User_Role === 'faculty') {
                            $roleClass = 'role-faculty';
                            $roleText = 'Faculty';
                        }
                        
                        // Format join date
                        $joinDate = date('M j, Y', strtotime($user->created_at));
                        
                        // Determine if approve button should be disabled
                        $approveDisabled = $user->Acc_Status === 'approved' ? 'disabled' : '';
                        $approveClass = $user->Acc_Status === 'approved' ? 'disabled' : '';
                        
                        ?>
                        <tr data-status="<?php echo strtolower($user->Acc_Status); ?>">
    <td><?php echo $fullName; ?></td>
    <td><?php echo $email; ?></td>
    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
    <td><span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span></td>
    <td><?php echo $joinDate; ?></td>
    <td class="action-buttons">
        <button class="action-btn approve-btn <?php echo $approveClass; ?>" 
                title="<?php echo $user->Acc_Status === 'approved' ? 'Account Already Approved' : 'Approve Account'; ?>"
                data-user-id="<?php echo $user->ID; ?>"
                data-user-status="<?php echo $user->Acc_Status; ?>"
                <?php echo $approveDisabled; ?>>
            <i class="fa fa-check"></i>
        </button>
        <button class="action-btn delete-btn" 
                title="Delete Account"
                data-user-id="<?php echo $user->ID; ?>"
                data-user-name="<?php echo $fullName; ?>">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>
                        <?php
                    }
                } else {
                    // No users found
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            <div class="no-accounts-found">
                                <i class="fas fa-users" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                                <p>No accounts found in the database.</p>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                
            } catch (Exception $e) {
                error_log("Error loading accounts: " . $e->getMessage());
                ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #d32f2f;">
                        <div class="error-loading-accounts">
                            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #d32f2f; margin-bottom: 10px;"></i>
                            <p>Error loading accounts. Please try again later.</p>
                            <small>Error: <?php echo htmlspecialchars($e->getMessage()); ?></small>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
                            
                        </tbody>
                    </table>
                </div>

                <div id="allAccounts-container" class="accounts-content">
                    <!-- All accounts table will be shown here -->
                </div>

                <div id="pendingAccounts-container" class="accounts-content" style="display: none;">
                    <!-- Pending accounts table will be shown here -->
                </div>

                <div id="approvedAccounts-container" class="accounts-content" style="display: none;">
                    <!-- Approved accounts table will be shown here -->
                </div>
                
                <div class="table-pagination-container">
                    <div class="table-pagination">
                        <button class="pagination-btn" disabled>
                            <i class="fa fa-chevron-left"></i>
                        </button>
                        <span class="pagination-info">Page 1 of 3</span>
                        <button class="pagination-btn">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="logs-container" class="content-container" style="display: none;">
    <header class="logHeader" id="logHeader">
        <div class="title">System Logs</div>
        <div class="logMenu">
            <button class="selected" id="userButton"> User </button>
            <button id="adminButton"> Admin </button>
        </div>
    </header>
    
    <!-- User Log Container -->
        <div id="userLog-container" class="log-content">
            <div class="log-filter-bar">
            <div class="searchbox">
                        <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                        <input type="text" name="search" placeholder="Search thesis..." class="search-text" id="searchInput">
                    </div>
                <div class="log-filter-options">
                    <button class="log-filter-btn active" data-filter="all">All</button>
                    <button class="log-filter-btn" data-filter="login">Logins</button>
                    <button class="log-filter-btn" data-filter="upload">Uploads</button>
                    <button class="log-filter-btn" data-filter="management">Management</button>
                </div>
            </div>
            
            <div class="logs-table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-log-type="login">
                            <td>2023-10-15 14:32</td>
                            <td>admin@example.com</td>
                            <td><span class="log-action action-login">Login</span></td>
                            <td>Successful login from IP 192.168.1.1</td>
                        </tr>
                        <tr data-log-type="upload">
                            <td>2023-10-15 13:45</td>
                            <td>editor@example.com</td>
                            <td><span class="log-action action-upload">Thesis Upload</span></td>
                            <td>Uploaded "Advanced AI Research" (3.2MB)</td>
                        </tr>
                        <tr data-log-type="management">
                            <td>2023-10-15 12:18</td>
                            <td>admin@example.com</td>
                            <td><span class="log-action action-management">User Management</span></td>
                            <td>Updated permissions for editor@example.com</td>
                        </tr>
                        <tr data-log-type="login">
                            <td>2023-10-15 11:30</td>
                            <td>user@example.com</td>
                            <td><span class="log-action action-login">Login Failed</span></td>
                            <td>Failed login attempt - incorrect password</td>
                        </tr>
                        <tr data-log-type="upload">
                            <td>2023-10-15 10:15</td>
                            <td>researcher@example.com</td>
                            <td><span class="log-action action-upload">Thesis Update</span></td>
                            <td>Updated metadata for "Machine Learning Applications"</td>
                        </tr>
                        <tr data-log-type="upload">
                            <td>2023-10-15 10:15</td>
                            <td>researcher@example.com</td>
                            <td><span class="log-action action-upload">Thesis Update</span></td>
                            <td>Updated metadata for "Machine Learning Applications"</td>
                        </tr>
                        <tr data-log-type="upload">
                            <td>2023-10-15 10:15</td>
                            <td>researcher@example.com</td>
                            <td><span class="log-action action-upload">Thesis Update</span></td>
                            <td>Updated metadata for "Machine Learning Applications"</td>
                        </tr>
                        <tr data-log-type="upload">
                            <td>2023-10-15 10:15</td>
                            <td>researcher@example.com</td>
                            <td><span class="log-action action-upload">Thesis Update</span></td>
                            <td>Updated metadata for "Machine Learning Applications"</td>
                        </tr>
                        <tr data-log-type="upload">
                            <td>2023-10-15 10:15</td>
                            <td>researcher@example.com</td>
                            <td><span class="log-action action-upload">Thesis Update</span></td>
                            <td>Updated metadata for "Machine Learning Applications"</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Admin Log Container -->
        <div id="adminLog-container" class="log-content" style="display: none;">
            <div class="log-filter-bar">
            <div class="searchbox">
                        <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                        <input type="text" name="search" placeholder="Search thesis..." class="search-text" id="searchInput">
                    </div>
                <div class="log-filter-options">
                    <button class="log-filter-btn active" data-filter="all">All</button>
                    <button class="log-filter-btn" data-filter="system">System</button>
                    <button class="log-filter-btn" data-filter="management">Management</button>
                    <button class="log-filter-btn" data-filter="security">Security</button>
                </div>
            </div>
            
            <div class="logs-table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-log-type="system">
                            <td>2023-10-16 09:15</td>
                            <td>superadmin@example.com</td>
                            <td><span class="log-action action-system">System Update</span></td>
                            <td>Applied security patches to database server</td>
                        </tr>
                        <tr data-log-type="management">
                            <td>2023-10-15 16:30</td>
                            <td>admin@example.com</td>
                            <td><span class="log-action action-management">User Creation</span></td>
                            <td>Created new editor account: editor2@example.com</td>
                        </tr>
                        <tr data-log-type="system">
                            <td>2023-10-15 11:05</td>
                            <td>superadmin@example.com</td>
                            <td><span class="log-action action-system">Database Backup</span></td>
                            <td>Performed full system backup (2.4GB)</td>
                        </tr>
                        <tr data-log-type="security">
                            <td>2023-10-15 09:45</td>
                            <td>admin@example.com</td>
                            <td><span class="log-action action-system">Security Audit</span></td>
                            <td>Ran security audit - no vulnerabilities found</td>
                        </tr>
                        <tr data-log-type="management">
                            <td>2023-10-14 17:20</td>
                            <td>admin@example.com</td>
                            <td><span class="log-action action-management">Role Update</span></td>
                            <td>Changed user permissions for research team</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

            
        </section>
    </section>
</div>



<!-- Upload Thesis Modal -->
<div class="modal-overlay" id="uploadModal">
<form class="modal" action="../../../app/Controllers/UserController.php?action=upload" method="POST" enctype="multipart/form-data" id="uploadForm">
     <div class="modal-header">
            <h2 class="modal-title">Upload Thesis</h2>

            <?php if (!empty($data['error'])): ?>
            <div class="error-message">
                <?php echo $data['error']; ?>
            </div>
            <?php endif; ?>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="thesis-form">
                <div class="thesis-form-group">
                    <h3>Thesis Title *</h3>
                    <input type="text" name="thesistitle" placeholder="Enter thesis title" class="thesis-form-input" id="thesisTitle" required>
                </div>
                
                <div class="thesis-form-group">
                    <h3>Author/s</h3>
                    <input type="text" name="thesisauthor" placeholder="Enter author name(s) separated with commas ','" class="thesis-form-input" id="thesisAuthor">
                </div>
                
            </div>
            
            <div class="upload-area" id="dropArea">
                <div class="upload-icon">
                    <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                </div>
                <div class="upload-text">
                    <h3>Drag & Drop your files here</h3>
                    <p>Supported files: docx, pdf, zip</p>
                </div>
                <div class="browse-btn">Browse files</div>
                <input type="file" class="file-input" id="fileInput" name="files[]" multiple accept=".pdf">
            </div>
            
            <div class="file-previews">
                <h4>Selected Files</h4>
                <div class="file-list-grid" id="fileList">
                    <div class="empty-state">
                        <i class="far fa-folder-open"></i>
                        <p>No files selected</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel">Cancel</button>
            <button onclick="" class="btn btn-upload" id="uploadBtn" disabled>Upload Thesis</button>
        </div>
    </form>
</div>

<!-- Document Preview Modal -->
<div class="modal-overlay preview-modal" id="previewModal">
    <div class="modal preview-modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Document Preview</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="project-info-preview" class="project-info-preview"></div>
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
            <button class="btn btn-cancel" id="closePreview">Close</button>
            <a id="download-link" class="btn btn-primary" download>Download</a>
        </div>
    </div>
</div>

</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>


</html>