<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

// AdminDashboard.php - At the VERY TOP of the file
require_once '../../../Database/config.php'; 
require_once '../../../app/Controllers/AdminDashboardController.php';
require_once '../../../app/Models/Thesis.php';

try {
    $db = new Database();
    $thesisModel = new Thesis($db);
    $thesis = $thesisModel->getAllTheses();
    
    foreach ($thesis as $theses) {
        $uploadDate = new DateTime($theses->uploaded_at);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($uploadDate);
        $theses->days_ago = $interval->days;
        $theses->is_recent = $interval->days <= 7;
    }
    
    
} catch (Exception $e) {
    error_log("Error loading theses: " . $e->getMessage());
    $thesis = [];
}

// Department Manager Class for handling department counts
// Updated Department Manager Class for handling department counts
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
            '<div data-value="all">All Departments (%d)</div>',
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
    
    // Method to get all departments with their counts (useful for debugging)
    public function getDepartmentsWithCounts() {
        $result = [];
        foreach ($this->departments as $dept) {
            $result[] = [
                'value' => $dept['value'],
                'name' => $dept['name'],
                'course_codes' => $dept['course_codes'],
                'count' => $this->getDepartmentCount($dept['course_codes'])
            ];
        }
        return $result;
    }
}

$departmentManager = new DepartmentManager($db);

$departmentManager->addDepartment('cs', 'BECED | AECES', ['Bachelor of Early Childhood Education']);
$departmentManager->addDepartment('ee', 'BSED | AFSET', ['Bachelor of Secondary Education']);
$departmentManager->addDepartment('me', 'BTVTED | FTVETS', ['Bachelor of Technical-Vocational Teacher Education']);
$departmentManager->addDepartment('ee', 'BEED | OFEE', ['Bachelor of Elementary Education']);
$departmentManager->addDepartment('me', 'BSNED | OFSET', ['Bachelor of Special Needs Education']);
$departmentManager->addDepartment('ce', 'BSABE | SABES', ['Bachelor of Science in Agriculture and Biosystems Engineering']);
$departmentManager->addDepartment('it', 'BSIT | SITS', ['Bachelor of Science in Information Technology']);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                        <?php
                        if (count($thesis) > 0) {
                            foreach ($thesis as $theses) {
                                if ($theses->is_recent) {
                                    $hasRecentTheses = true;
                                    displayThesisItem($theses);
                                }

                                
                            }
                        }
                        ?>
                    </ul> 

                    <!-- Rest of your existing HTML for other project items -->
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
                        <input type="text" name="search" placeholder="Search accounts..." class="search-text" id="userLogSearchInput">
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
                        <input type="text" name="search" placeholder="Search accounts..." class="search-text" id="adminLogSearchInput">
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
    <form class="modal" action="../../../app/Controllers/ThesisController.php?action=upload" method="POST" enctype="multipart/form-data" id="uploadForm">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="modal-header">
            <h2 class="modal-title">Upload Thesis</h2>
            <div id="uploadError" class="error-message" style="display: none;"></div>
            <i class="fa-solid fa-file" id="notif"></i>
        </div>
        <div class="modal-body">
            <div class="thesis-form">
                <div class="thesis-form-group">
                    <h3>Thesis Title *</h3>
                    <input type="text" name="thesistitle" placeholder="Enter thesis title" class="thesis-form-input" id="thesisTitle" required>
                </div>
                
                <div class="thesis-form-group">
                    <h3>Author Emails *</h3>
                    <input type="text" name="thesisauthor" placeholder="Enter author emails separated by commas (e.g., author1@email.com, author2@email.com)" class="thesis-form-input" id="thesisAuthor" required>
                    <small style="color: var(--color-lite-grey); font-size: 0.8rem; margin-top: 5px; display: block;">
                        Separate multiple author emails with commas
                    </small>
                </div>

                <div class="thesis-form-group">
                <h3>Thesis Adviser Email *</h3>
                    <input type="text" name="thesisadviser" placeholder="Enter adviser email" class="thesis-form-input" id="thesisAdviser" required>
                </div>

                <div class="thesis-form-group">
                    <h3>Department *</h3>
                    <select name="department" class="thesis-form-input dropdown" id="departmentSelect" required>
                        <option value="" selected disabled>Select Department</option>
                        <option value="COE">COE</option>
                        <option value="CTET">CTET</option>
                    </select>
                </div>

                
                <div class="thesis-form-group">
                    <h3>Course *</h3>
                    <select name="course" class="thesis-form-input dropdown" id="courseInput" required disabled>
                        <option value="" selected disabled>Select your program</option>
                    </select>
                </div>

                <div class="thesis-form-group">
                    <h3>Hardbound Available *</h3>
                    <select name="hardbound" class="thesis-form-input dropdown" id="hardboundSelect" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>

                
            </div>
            <div class="upload-area-container">
                <!-- Abstract File Upload Area -->
                <div class="upload-area" id="abstractDropArea">
                    <div class="upload-icon">
                        <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                    </div>
                    <div class="upload-text">
                        <h3>Abstract File</h3>
                        <p>Supported files: PDF only</p>
                    </div>
                    <div class="browse-btn">Browse files</div>
                    <input type="file" class="file-input" id="abstractFileInput" name="abstract_file" accept=".pdf">
                </div>
                
                <!-- Thesis File Upload Area -->
                <div class="upload-area" id="thesisDropArea">
                    <div class="upload-icon">
                        <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                    </div>
                    <div class="upload-text">
                        <h3>Thesis File</h3>
                        <p>Supported files: PDF only</p>
                    </div>
                    <div class="browse-btn">Browse files</div>
                    <input type="file" class="file-input" id="thesisFileInput" name="thesis_file" accept=".pdf">
                </div>
            </div>

            <div class="file-previews">
                <!-- Abstract Files Section -->
                <div class="file-category">
                    <h4>Abstract Files</h4>
                    <div class="file-list-grid" id="abstractFileList">
                        <div class="empty-state">
                            <i class="far fa-file-pdf"></i>
                            <p>No abstract files selected</p>
                        </div>
                    </div>
                </div>
                
                <!-- Thesis Files Section -->
                <div class="file-category">
                    <h4>Thesis Files</h4>
                    <div class="file-list-grid" id="thesisFileList">
                        <div class="empty-state">
                            <i class="far fa-file-pdf"></i>
                            <p>No thesis files selected</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel">Cancel</button>
            <button type="submit" class="btn btn-upload" id="uploadBtn" disabled>Upload Thesis</button>
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
                <button class="btn btn-tertiary"><i class="fa-solid fa-eye"></i>
                    View Thesis
                </button>
                <button class="btn btn-secondary btn-cancel">Close</button>
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
    
    $thesisId = $theses->ID;
    $Author = htmlspecialchars($theses->Author);
    $Adviser = htmlspecialchars($theses->Adviser ?? 'Not specified');
    $Title = htmlspecialchars($theses->Title);
    $hardboundValue =  htmlspecialchars($theses->HardBound_Available);
    $Department = htmlspecialchars($theses->Thesis_Department);
    $depWeight = '900';
    $depSize = '1vw';
    $margin = '1vw';
    $Course = htmlspecialchars($theses->Thesis_Course);

    $formattedDate = date('M j, Y', strtotime($theses->uploaded_at));
    $daysAgo = $theses->days_ago;
    
    
    $affirmativeValues = ['Yes', 'true', '1', 'available', 'y'];
    $isHardboundAvailable = in_array($hardboundValue, $affirmativeValues);

    $iconColor = $isHardboundAvailable ? '#55dcb3' : '#ff6b6b';
    $statusText = $isHardboundAvailable ? 'Hardbound Available' : 'Hardbound Unavailable';
    $iconClass = $isHardboundAvailable ? 'fa-circle-check' : 'fa-circle-xmark';

    // Truncate title if too long using line-clamp (removed manual truncation)
    $displayTitle = $Title;
    
    // Handle multiple authors display using line-clamp (removed manual truncation)
    $displayAuthor = $Author;
    
    // Determine days ago text
    $daysAgoText = '';
    if ($daysAgo == 0) {
        $daysAgoText = 'Today';
    } elseif ($daysAgo == 1) {
        $daysAgoText = 'Yesterday';
    } else {
        $daysAgoText = $daysAgo . ' days ago';
    }
    
    echo '<li class="project-item" data-tags="" data-thesis-id="' . $thesisId . '" data-days-ago="' . $daysAgo . '" data-is-recent="' . ($theses->is_recent ? 'true' : 'false') . '">';
    echo '<div class="logo-row">';
    echo '<img src="/CapstoneTracker/resources/Images/usep-logo-small.png" alt="Logo" />';
    echo '<div class="icon"> <i class="fa fa-ellipsis-h" aria-hidden="true"></i> </div>';
    echo '<div class="moreOptions">';
    echo '<button><i class="fa-solid fa-pen"></i>Edit</button>';
    echo '<button><i class="fa-solid fa-trash-can"></i>Delete</button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="title-row">';
    echo '<h3>' . $displayTitle . '</h3>';
    
    echo '<div class="links">';
    echo '<p style="font-weight: ' . $depWeight . '; font-size: ' . $depSize . '; ">' . $Department . '</p>';
    echo '<p style="margin-bottom:'. $margin .'">' . $Course . '</p>';
    echo '<p href="#">' . $formattedDate . '</p>';
    
    echo '</div>';
    echo '</div>';
    echo '<div class="desc-row">';
    echo '<p class="author"><strong>Author:</strong> ' . $displayAuthor . '</p>';
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