<?php

// Error reporting - ONLY for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("=== ADMIN CONTROLLER ACCESSED ===");
error_log("Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));
error_log("Action: " . ($_POST['action'] ?? $_GET['action'] ?? 'NONE'));

require_once __DIR__ . '/Controller.php';

class AdminController extends Controller {

    public function __construct() {
       
    }

    /**
     * Generate and store CSRF token
     */
    private function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        $isValid = hash_equals($_SESSION['csrf_token'], $token);
        
        // Regenerate token after validation
        if ($isValid) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $isValid;
    }
    
    public function login() {
        // Start session once
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Ensure CSRF token exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Check if user is already logged in as admin
        if ($this->isLoggedIn() && $this->isAdmin()) {
            $this->redirectToAdminDashboard();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processAdminLogin();
        } else {
            // Check if admin parameter is set in URL
            if (isset($_GET['admin']) && $_GET['admin'] == '1') {
                $this->showAdminLoginForm();
            } else {
                // Default to showing admin login form
                $this->showAdminLoginForm();
            }
        }
    }
    
    private function processAdminLogin() {
        error_log("=== PROCESS ADMIN LOGIN STARTED ===");

        $identifier = $_POST['admin_username'] ?? '';
    $password = $_POST['admin_password'] ?? '';
    $this->debugAuthenticationFlow($identifier, $password);
    
        usleep(100000); // 100ms delay
    
        $csrfToken = $_POST['csrf_token'] ?? '';
        error_log("CSRF Token from form: " . $csrfToken);
        error_log("CSRF Token from session: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
    
        if (!$this->validateCsrfToken($csrfToken)) {
            error_log("CSRF TOKEN VALIDATION FAILED");
            $_SESSION['admin_error_message'] = 'Invalid security token. Please try again.';
            header('Location: ../../app/Views/User/indexLogin.php?admin=1');
            exit();
        }
    
        // Get form data
        $identifier = $_POST['admin_username'] ?? '';
        $password = $_POST['admin_password'] ?? '';
    
        error_log("=== ADMIN LOGIN CREDENTIALS DEBUG ===");
        error_log("Username/Identifier: " . $identifier);
        error_log("Password: " . (!empty($password) ? "SET" : "EMPTY"));
        
        // Validate input
        if (empty($identifier) || empty($password)) {
            error_log("❌ EMPTY IDENTIFIER OR PASSWORD");
            $this->redirectWithError('Admin ID and password are required.');
        }
        
        // Authenticate using AuthController logic
        error_log("Calling authenticateAdmin...");
        $user = $this->authenticateAdmin($identifier, $password);
        
        if ($user) {
            error_log("✅ AUTHENTICATE ADMIN SUCCESS");
            // Create admin session and redirect to dashboard
            $this->createAdminSession($user);
            $this->redirectToAdminDashboard();
        } else {
            error_log("❌ AUTHENTICATE ADMIN FAILED");
            $this->redirectWithError('Invalid admin credentials or insufficient privileges.');
        }
    }

    private function debugAuthenticationFlow($identifier, $password) {
        error_log("=== DEBUG AUTHENTICATION FLOW ===");
        
        // Test the User model directly
        require_once '../Models/User.php';
        $userModel = new User();
        
        error_log("Testing User->loginAdmin directly...");
        $user = $userModel->loginAdmin($identifier, $password);
        
        if ($user) {
            error_log("✅ User model login SUCCESS");
            error_log("User object type: " . gettype($user));
            error_log("User properties:");
            error_log("  ID: " . ($user->ID ?? 'NULL'));
            error_log("  Role: " . ($user->User_Role ?? 'NULL'));
            error_log("  Status: " . ($user->Acc_Status ?? 'NULL'));
            
            // Test role check
            $userRole = strtolower($user->User_Role ?? '');
            $adminRoles = ['superadmin', 'subadmin', 'admin'];
            $isAdmin = in_array($userRole, $adminRoles);
            error_log("Is admin role: " . ($isAdmin ? 'YES' : 'NO'));
            
        } else {
            error_log("❌ User model login FAILED");
        }
    }
    
    private function authenticateAdmin($identifier, $password) {
        try {
            error_log("=== AUTHENTICATE ADMIN DEBUG ===");
            error_log("Identifier: " . $identifier);
    
            require_once '../Models/User.php';
            
            $userModel = new User();
    
            // Call loginAdmin which returns a User object or false
            error_log("Calling loginAdmin with identifier: " . $identifier);
            $user = $userModel->loginAdmin($identifier, $password);
            
            error_log("loginAdmin result: " . ($user ? "USER OBJECT" : "FALSE"));
            
            if ($user && is_object($user)) {
                // Debug the returned user object
                error_log("User found - ID: " . ($user->ID ?? 'NULL'));
                error_log("User found - Role: " . ($user->User_Role ?? 'NULL'));
                error_log("User found - Status: " . ($user->Acc_Status ?? 'NULL'));
                
                // Check if user has admin role
                $userRole = strtolower($user->User_Role ?? '');
                error_log("Normalized User Role: " . $userRole);
                
                // Include all admin roles
                $adminRoles = ['superadmin', 'subadmin', 'admin'];
                if (in_array($userRole, $adminRoles)) {
                    error_log("✅ Admin user authenticated: " . $identifier . " (Role: " . $userRole . ")");
                    
                    // Convert User object to array for session creation
                    $userData = [
                        'id' => $user->ID,
                        'user_id' => $user->ID,
                        'first_name' => $user->First_Name,
                        'last_name' => $user->Last_Name,
                        'middle_name' => $user->Middle_Name ?? '',
                        'extension' => $user->Extension ?? '',
                        'role' => $user->User_Role,
                        'course' => $user->Course ?? null,
                        'department' => $user->Department ?? null,
                        'acc_status' => $user->Acc_Status
                    ];
                    
                    error_log("User data for session: " . print_r($userData, true));
                    return $userData;
                } else {
                    error_log("❌ User found but not admin role: " . $userRole);
                    return false;
                }
            } else {
                error_log("❌ loginAdmin returned false - invalid credentials");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("💥 Admin authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    private function createAdminSession($userData) {
        // Store user data in session
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_db_id'] = $userData['user_id'];
        $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
        $_SESSION['user_role'] = $userData['role'];
        $_SESSION['first_name'] = $userData['first_name'];
        $_SESSION['last_name'] = $userData['last_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
        
        error_log("✅ Admin session created for: " . $_SESSION['user_name']);
        error_log("Session user_id: " . $_SESSION['user_id']);
        error_log("Session user_role: " . $_SESSION['user_role']);
    }

    /**
     * Handle Google Sign-In for admin users
     */
    public function adminGoogleLogin() {
        // Clear all output buffers completely
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Start fresh output buffer
        ob_start();
        
        try {
            error_log("=== ADMIN GOOGLE LOGIN DEBUG START ===");
            error_log("POST data: " . print_r($_POST, true));
            
            // Check if ROOT_DIR is defined
            if (!defined('ROOT_DIR')) {
                error_log("ROOT_DIR not defined, trying to define it...");
                define('ROOT_DIR', dirname(__DIR__, 2)); // Adjust based on your directory structure
            }
            
            $credential = $_POST['credential'] ?? '';
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
        
            // Basic validation
            if (empty($credential) || empty($email)) {
                throw new Exception('Missing required Google authentication data.');
            }
        
            error_log("Admin Google Login - Email: " . $email);
            error_log("Admin Google Login - Name: " . $name);
        
            // Check if admin exists with this email
            error_log("Checking if admin exists in database...");
            $admin = $this->findAdminByEmail($email);
            
            if ($admin) {
                // Convert admin to array if it's an object
                if (is_object($admin)) {
                    $admin = (array)$admin;
                }
                
                // Get admin properties
                $adminId = $admin['ID'] ?? $admin->ID ?? null;
                $adminEmail = $admin['Email'] ?? $admin->Email ?? '';
                $accStatus = $admin['Acc_Status'] ?? $admin->Acc_Status ?? 'unknown';
                $userRole = $admin['User_Role'] ?? $admin->User_Role ?? '';
                $firstName = $admin['First_Name'] ?? $admin->First_Name ?? '';
                $lastName = $admin['Last_Name'] ?? $admin->Last_Name ?? '';
                
                error_log("Admin found. ID: " . $adminId . ", Email: " . $adminEmail . ", Role: " . $userRole . ", Status: " . $accStatus);
                
                // Verify it's actually an admin or sub-admin
                if (!in_array(strtolower($userRole), ['superadmin', 'subadmin', 'admin'])) {
                    throw new Exception('This account does not have admin privileges.');
                }
                
                // Check if account is approved
                if ($accStatus === 'pending') {
                    throw new Exception('Your admin account is pending approval.');
                } else if ($accStatus === 'rejected') {
                    throw new Exception('Your admin account was rejected. Please contact system administrator.');
                } else if ($accStatus === 'approved') {
                    // ✅ APPROVED ADMIN: Log them in
                    error_log("Admin account approved, creating session...");
                    
                    // Prepare user data for session using the existing createAdminSession format
                    $userData = [
                        'id' => $adminId,
                        'user_id' => $adminId, // Use same ID for user_db_id
                        'email' => $adminEmail,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'role' => $userRole
                    ];
                    
                    $this->createAdminSession($userData);
                    
                    error_log("Admin session created successfully");
                    
                    // Clear buffer and send clean JSON
                    ob_clean();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Admin login successful',
                        'redirect_url' => '../../Views/Admin/AdminDashboard.php'
                    ]);
                    exit();
                } else {
                    throw new Exception('Your admin account status is invalid. Please contact administrator.');
                }
            } else {
                // Admin not found
                throw new Exception('No admin account found with this email. Please use traditional admin login.');
            }
        
        } catch (Exception $e) {
            // Clear buffer and send clean error JSON
            ob_clean();
            header('Content-Type: application/json');
            error_log("Admin Google login exception: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
        } finally {
            // Ensure no extra output
            if (ob_get_length()) {
                ob_end_clean();
            }
        }
    }

    /**
     * Find admin by email
     */
    private function findAdminByEmail($email) {
        try {
            error_log("=== FIND ADMIN BY EMAIL DEBUG ===");
            error_log("Original email: " . $email);
            
            // Check if ROOT_DIR is defined
            if (!defined('ROOT_DIR')) {
                define('ROOT_DIR', dirname(__DIR__, 2));
            }
            
            $userModelPath = ROOT_DIR . '/app/Models/User.php';
            
            if (!file_exists($userModelPath)) {
                $userModelPath = __DIR__ . '/../Models/User.php';
            }
            
            if (!file_exists($userModelPath)) {
                throw new Exception('User model file not found');
            }
            
            require_once $userModelPath;
            
            $userModel = new User();
            $db = $userModel->getDb();
            
            // Hash the email for lookup
            $emailHash = hash('sha256', $email);
            error_log("Hashed email: " . $emailHash);
            
            // Query to find admin user by hashed email
            $db->query('SELECT * FROM USER_INFORMATION WHERE Email = :email AND User_Role IN ("superAdmin", "SubAdmin", "admin") LIMIT 1');
            $db->bind(':email', $emailHash);
            $result = $db->single();
            
            // Convert object to array if needed
            if (is_object($result)) {
                $result = (array)$result;
            }
            
            error_log("Database query result: " . ($result ? 'ADMIN FOUND' : 'NOT FOUND OR NOT ADMIN'));
            if ($result) {
                error_log("Admin details:");
                error_log("  - ID: " . ($result['ID'] ?? 'unknown'));
                error_log("  - Role: " . ($result['User_Role'] ?? 'unknown'));
                error_log("  - Status: " . ($result['Acc_Status'] ?? 'unknown'));
                error_log("  - First Name: " . ($result['First_Name'] ?? 'unknown'));
                error_log("  - Last Name: " . ($result['Last_Name'] ?? 'unknown'));
            } else {
                error_log("❌ No admin found with email hash: " . $emailHash);
                
                // Debug: Check what users exist with admin roles
                $db->query('SELECT ID, User_Role, Acc_Status, First_Name, Last_Name FROM USER_INFORMATION WHERE User_Role IN ("superAdmin", "SubAdmin", "admin")');
                $allAdmins = $db->resultSet();
                error_log("All admin users in database: " . count($allAdmins));
                foreach ($allAdmins as $admin) {
                    error_log("  - ID: " . ($admin->ID ?? 'unknown') . ", Role: " . ($admin->User_Role ?? 'unknown') . ", Status: " . ($admin->Acc_Status ?? 'unknown'));
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("💥 Error in findAdminByEmail: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function isAuthorizedAdminEmail($email) {
        // Define your authorized admin emails
        $authorizedAdmins = [
            'superadmin@usep.edu.ph',
            // Add other authorized admin emails
        ];
        
        return in_array($email, $authorizedAdmins);
    }

        public function lockSystem() {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!$this->isLoggedIn() || !$this->isAdmin()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Unauthorized access'
                ]);
                exit;
            }
            
            $_SESSION['system_locked'] = true;
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'System locked successfully'
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log("Error locking system: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error locking system: ' . $e->getMessage()
            ]);
            exit;
        }
    }

      /**
     * Verify admin password for system unlock
     */
    public function verifyAdminPassword($user_id, $password) {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Optional minimal logging (remove or comment in production if you want silence)
            error_log("Unlock attempt for User ID (auto-inc): {$user_id}");

            if (empty($user_id) || empty($password)) {
                return ['success' => false, 'error' => 'Missing credentials'];
            }

            // Use relative path – works on every machine
            require_once __DIR__ . '/../Models/User.php';
            $userModel = new User();

            $user = $userModel->getUserById($user_id);

            if (!$user) {
                error_log("Unlock failed – user not found (ID: {$user_id})");
                return ['success' => false, 'error' => 'User not found'];
            }

            // Critical fixes: correct column names + salt concatenation
            if (empty($user->pswrd) || empty($user->Salt)) {
                return ['success' => false, 'error' => 'Password not configured'];
            }

            $verified = password_verify($password . $user->Salt, $user->pswrd);

            if ($verified) {
                $_SESSION['system_locked'] = false;
                error_log("System unlocked successfully for {$_SESSION['user_name']}");
                return ['success' => true, 'message' => 'System unlocked'];
            } else {
                error_log("Unlock failed – wrong password for {$_SESSION['user_name']}");
                return ['success' => false, 'error' => 'Invalid password'];
            }

        } catch (Exception $e) {
            error_log("verifyAdminPassword exception: " . $e->getMessage());
            return ['success' => false, 'error' => 'Server error'];
        }
    }

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirectWithError('Access denied. Admin privileges required.');
            return;
        }
        

        $data = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_db_id' => $_SESSION['user_db_id'] ?? null, 
            'user_email' => $_SESSION['user_email'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
            'first_name' => $_SESSION['first_name'] ?? null,
            'last_name' => $_SESSION['last_name'] ?? null,
            'middle_name' => $_SESSION['middle_name'] ?? null,
            'extension' => $_SESSION['extension'] ?? null,
            'course' => $_SESSION['course'] ?? null,
            'department' => $_SESSION['department'] ?? null,
            'designation' => $_SESSION['designation'] ?? null,
            'employee_id' => $_SESSION['employee_id'] ?? null,
            'student_id' => $_SESSION['student_id'] ?? null,
            'profile_pic' => $_SESSION['profile_pic'] ?? null,
            'date_created' => $_SESSION['date_created'] ?? null,
            'last_login' => $_SESSION['last_login'] ?? null
        ];
        
        // Debug: Log session data
        error_log("=== ADMIN DASHBOARD SESSION DATA ===");
        error_log("Session ID: " . session_id());
        error_log("User DB ID: " . ($data['user_db_id'] ?? 'NOT SET'));
        error_log("All session data: " . print_r($_SESSION, true));
        error_log("Passing to view: " . print_r($data, true));
        
        // Include the AdminDashboard view with data
        $this->loadView('Admin/AdminDashboard', $data);
    }

    private function loadView($viewPath, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            error_log("View file not found: " . $viewFile);
            die("View file not found.");
        }
        exit();
    }
    
    // Also fix the isAdmin() method to check for both admin and superAdmin
    private function isAdmin() {
        $role = $_SESSION['user_role'] ?? '';
        return in_array(strtolower($role), ['superadmin', 'subadmin']);
    }
    
    
    private function redirectToAdminDashboard() {
        header('Location: ../../app/Views/Admin/AdminDashboard.php');
        exit();
    }
    
    private function showAdminLoginForm() {
        // You can create a separate admin login view or use the existing one
        // For now, we'll redirect to the modal in indexLogin.php
        header('Location: ../../app/Views/User/indexLogin.php?admin=1');
        exit();
    }
    
    private function redirectWithError($message) {
        $_SESSION['admin_error_message'] = $message;
        header('Location: ../../app/Views/User/indexLogin.php?admin=1');
        exit();
    }
    
    protected function redirect($location) {
        header("Location: $location");
        exit();
    }
    
    // Logout method for admin
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        $this->redirect('../../app/Views/User/indexLogin.php');
    }
}


// Error reporting - TURN OFF for production to avoid HTML output
error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminController = new AdminController();

// Handle the request based on action
$action = $_POST['action'] ?? $_GET['action'] ?? 'login';

if ($action === 'login') {
    $adminController->login();
} elseif ($action === 'dashboard') {
    $adminController->dashboard();
} elseif ($action === 'logout') {
    $adminController->logout();
} elseif ($action === 'adminGoogleLogin') {
    $adminController->adminGoogleLogin();
} elseif ($action === 'lockSystem') {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();
    
    try {
        $adminController->lockSystem();
    } catch (Exception $e) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    } finally {
        if (ob_get_length()) {
            ob_end_clean();
        }
    }
} elseif ($action === 'verifyAdminPassword') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();

        try {
            $user_id  = $_SESSION['user_id'];           
            $password = $_POST['password'] ?? '';     

            if (empty($user_id) || empty($password)) {
                throw new Exception('User ID and password are required');
            }

            $result = $adminController->verifyAdminPassword($user_id, $password);

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;

        } catch (Exception $e) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
}
?>