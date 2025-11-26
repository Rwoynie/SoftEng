<?php



if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2));
}

require_once __DIR__ . '/Controller.php';

class AuthController extends Controller {
    
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
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    // Regenerate after successful validation (one-time use)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return true;
}

public function handleRequest() {
    // Start session at the beginning
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        $this->generateCsrfToken();
    }
    
    error_log("=== AUTH CONTROLLER HANDLE REQUEST ===");
    error_log("POST DATA: " . print_r($_POST, true));
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        error_log("ACTION DETECTED: " . $action);
        
        // Google Login (multiple ways it can be triggered)
        if ($action === 'googleLogin' || 
            $action === 'google_login' || 
            (isset($_POST['credential']) && isset($_POST['email']))) {
            
            error_log("GOOGLE LOGIN DETECTED - Calling googleLogin()");
            $this->googleLogin();
            return;
        }
        // Normal Email/Password Login
        elseif ($action === 'login') {
            error_log("Calling processLogin()");
            $this->processLogin();
            return;
        }
        // Forgot Password: Send Verification Code
        elseif ($action === 'send_verification_code') {
            error_log("Calling sendVerificationCode()");
            $this->sendVerificationCode();
            return;
        }
        // Forgot Password: Verify Code & Reset Password
        elseif ($action === 'verify_reset_code') {
            error_log("Calling verifyResetCode()");
            $this->verifyResetCode();
            return;
        }
        // Unknown action → reject
        else {
            error_log("UNKNOWN ACTION: " . $action);
            $_SESSION['error_message'] = "Invalid request.";
            header('Location: ../../app/Views/User/indexLogin.php');
            exit();
        }
    } 
    elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'logout') {
            $this->logout();
        }
    }
}
    
    public function processLogin() {
        // Get form data
        error_log("=== LOGIN ATTEMPT DEBUG ===");
        error_log("Login attempt - Email: " . ($_POST['email'] ?? 'empty'));
        error_log("Login attempt - Password: " . (($_POST['password'] ?? 'empty') ? '***' : 'empty'));
        error_log("Login attempt - Role: " . ($_POST['role'] ?? 'empty'));

        // Get client information for logging
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $email = $_POST['email'] ?? '';

        // Log login attempt (initially as failed)
        $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Login attempt started');

        // Validate CSRF token first
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($csrfToken)) {
            $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Invalid CSRF token');
            $this->redirectWithError('Invalid security token. Please try again.');
            return;
        }

        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Missing email or password');
            $this->redirectWithError('All fields are required.');
            return;
        }
        
        // Clear any existing error message
        if (isset($_SESSION['error_message'])) {
            unset($_SESSION['error_message']);
        }
        
        error_log("Attempting to authenticate user: " . $username);
        
        // Authenticate user
        $user = $this->authenticateUser($username, $password, $role);
        
        if ($user) {
            error_log("✅ Login SUCCESS for: " . $username);
            
            // Log successful login
            $this->logLoginAttempt($email, $user['id'], $ipAddress, $userAgent, true, 'Login successful');
            
            // Log user action for audit
            $this->logUserAction($user['id'], 'login', 'User logged in successfully');
            
            // Create session and redirect
            $this->createUserSession($user);
            $this->redirect('../../app/Views/User/userViewPage.php');
        } else {
            // Log failed login
            $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Invalid credentials');
            
            // Debug: Log the error message that was set
            $errorMsg = $_SESSION['error_message'] ?? 'No error message set';
            error_log("❌ Login FAILED for: " . $username);
            error_log("Error message: " . $errorMsg);
            
            // Ensure we have an error message
            if (!isset($_SESSION['error_message']) || empty($_SESSION['error_message'])) {
                $_SESSION['error_message'] = 'Invalid credentials. Please try again.';
            }
            
            header('Location: ../../app/Views/User/indexLogin.php');
            exit();
        }
    }

    /**
     * Log login attempts
     */
    private function logLoginAttempt($email, $userId = null, $ipAddress = null, $userAgent = null, $success = false, $notes = '') {
        try {
            require_once ROOT_DIR . '\app\Models\User.php';
            $userModel = new User();
            $userModel->logLoginAttempt($email, $userId, $ipAddress, $userAgent, $success, $notes);
        } catch (Exception $e) {
            // Log error but don't break login process
            error_log("Failed to log login attempt: " . $e->getMessage());
        }
    }

    /**
     * Log user actions for audit
     */
    private function logUserAction($userId, $action, $description) {
        try {
            require_once ROOT_DIR . '\app\Models\User.php';
            $userModel = new User();
            $userModel->logUserAction($userId, $action, $description);
        } catch (Exception $e) {
            error_log("Failed to log user action: " . $e->getMessage());
        }
    }



    

   /**
 * Send verification code for password reset - NO AUTO PASSWORD
 */
public function sendVerificationCode() {
    // Clear output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    try {
        $email = $_POST['email'] ?? '';
        
        // Basic validation
        if (empty($email)) {
            throw new Exception('Email address is required.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@usep.edu.ph')) {
            throw new Exception('Please use a valid USeP email address (@usep.edu.ph).');
        }
        
        error_log("Sending verification code to: " . $email);
        
        // Check if user exists
        $user = $this->findByEmail($email);
        if (!$user) {
            // For security, don't reveal whether email exists
            error_log("Email not found: " . $email);
            echo json_encode([
                'success' => true,
                'message' => 'If the email exists in our system, a verification code has been sent.'
            ]);
            exit();
        }
        
        // Convert to array if object
        if (is_object($user)) {
            $user = (array)$user;
        }
        
        $userId = $user['ID'] ?? null;
        $userEmail = $user['Email'] ?? $email;
        $userName = ($user['First_Name'] ?? '') . ' ' . ($user['Last_Name'] ?? '');
        
        if (!$userId) {
            throw new Exception('User account error.');
        }
        
        // Generate verification code (6 digits)
        $verificationCode = sprintf("%06d", random_int(0, 999999));
        
        // Store code in database (without generated password)
        $codeStored = $this->storeVerificationCode($userId, $verificationCode);
        
        if (!$codeStored) {
            throw new Exception('Failed to generate verification code. Please try again.');
        }
        
        // Send verification email
        $emailSent = $this->sendVerificationEmail($userEmail, $userName, $verificationCode);
        
        if ($emailSent) {
            error_log("Verification email sent to: " . $userEmail);
            echo json_encode([
                'success' => true,
                'message' => 'Verification code sent to your email.'
            ]);
        } else {
            throw new Exception('Failed to send verification email. Please try again later.');
        }
        
    } catch (Exception $e) {
        error_log("Send verification code error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

/**
 * Verify reset code and update password
 */
public function verifyResetCode() {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');

    try {
        $email = trim($_POST['email'] ?? '');
        $code  = $_POST['verification_code'] ?? '';
        $pass  = $_POST['new_password'] ?? '';

        if (empty($email) || empty($code) || empty($pass)) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit;
        }

        $user = $this->findByEmail($email);
        if (!$user || !isset($user->ID)) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        if (!$this->validateVerificationCode($email, $code)) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired code']);
            exit;
        }

        // Generate new salt + hash
        $newSalt = bin2hex(random_bytes(16));
        $hashed = password_hash($pass . $newSalt, PASSWORD_DEFAULT);

        // USE YOUR DATABASE CLASS STYLE — bind() instead of execute([array])
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();

        $db->query("UPDATE USER_INFORMATION SET pswrd = :hash, Salt = :salt WHERE ID = :id");
        $db->bind(':hash', $hashed);
        $db->bind(':salt', $newSalt);
        $db->bind(':id', $user->ID);

        $db->execute();

        if ($db->rowCount() > 0) {
            $this->markVerificationCodeAsUsed($email, $code);
            echo json_encode(['success' => true, 'message' => 'Password reset successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made (possible duplicate)']);
        }

    } catch (Exception $e) {
        error_log("verifyResetCode ERROR: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error']);
    }
    exit;
}


private function debugCurrentDatabaseState($email, $expectedCode, $expectedPassword) {
    try {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        error_log("=== CURRENT DATABASE STATE DEBUG ===");
        
        // Get user
        $db->query('SELECT ID FROM USER_INFORMATION WHERE Email = :email');
        $db->bind(':email', $email);
        $user = $db->single();
        
        if (!$user) {
            error_log("❌ User not found in database");
            return;
        }
        
        $userId = is_object($user) ? $user->ID : $user['ID'];
        error_log("📋 User ID: " . $userId);
        
        // Get all tokens for this user
        $db->query('SELECT * FROM PASSWORD_RESET_TOKENS WHERE user_id = :user_id ORDER BY created_at DESC');
        $db->bind(':user_id', $userId);
        $tokens = $db->resultSet();
        
        error_log("📊 Tokens found: " . count($tokens));
        
        foreach ($tokens as $index => $token) {
            $tokenData = is_object($token) ? $token->token : $token['token'];
            $expiresAt = is_object($token) ? $token->expires_at : $token['expires_at'];
            $isUsed = is_object($token) ? $token->is_used : $token['is_used'];
            $createdAt = is_object($token) ? $token->created_at : $token['created_at'];
            
            $parts = explode('|', $tokenData);
            $storedCode = $parts[0] ?? 'NO_CODE';
            $storedPassword = $parts[1] ?? 'NO_PASSWORD';
            
            error_log("--- Token #" . ($index + 1) . " ---");
            error_log("   Raw token: " . $tokenData);
            error_log("   Stored Code: " . $storedCode);
            error_log("   Stored Password: " . $storedPassword);
            error_log("   Expected Code: " . $expectedCode);
            error_log("   Expected Password: " . $expectedPassword);
            error_log("   Expires: " . $expiresAt);
            error_log("   Used: " . ($isUsed ? 'YES' : 'NO'));
            error_log("   Created: " . $createdAt);
            error_log("   Code Match: " . ($storedCode === $expectedCode ? 'YES' : 'NO'));
            error_log("   Password Match: " . ($storedPassword === $expectedPassword ? 'YES' : 'NO'));
            error_log("   Not Expired: " . (strtotime($expiresAt) > time() ? 'YES' : 'NO'));
            error_log("   Not Used: " . (!$isUsed ? 'YES' : 'NO'));
            error_log("   Valid: " . (
                $storedCode === $expectedCode && 
                $storedPassword === $expectedPassword && 
                !$isUsed && 
                strtotime($expiresAt) > time() 
                ? 'YES' : 'NO'
            ));
        }
        
    } catch (Exception $e) {
        error_log("Debug state error: " . $e->getMessage());
    }
}
    /**
     * Generate a strong random password
     */
    private function generateStrongPassword() {
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    /**
 * Store verification code in database - WITH PROPER TIME CALCULATION
 */
private function storeVerificationCode($userId, $code) {
    try {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        error_log("=== STORE VERIFICATION CODE ===");
        error_log("User ID: " . $userId);
        error_log("Code: " . $code);
        
        // Delete old tokens for this user
        $db->query('DELETE FROM PASSWORD_RESET_TOKENS WHERE user_id = :user_id OR expires_at < NOW()');
        $db->bind(':user_id', $userId);
        $db->execute();
        
        // Use database's NOW() function to avoid timezone issues
        $db->query('INSERT INTO PASSWORD_RESET_TOKENS (user_id, token, expires_at) 
                   VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 15 MINUTE))');
        $db->bind(':user_id', $userId);
        $db->bind(':token', $code);
        
        $result = $db->execute();
        
        // Verify what was stored
        $db->query('SELECT * FROM PASSWORD_RESET_TOKENS WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
        $db->bind(':user_id', $userId);
        $storedToken = $db->single();
        
        if ($storedToken) {
            $storedCode = is_object($storedToken) ? $storedToken->token : $storedToken['token'];
            $expiresAt = is_object($storedToken) ? $storedToken->expires_at : $storedToken['expires_at'];
            $createdAt = is_object($storedToken) ? $storedToken->created_at : $storedToken['created_at'];
            
            error_log("✅ Token stored successfully:");
            error_log("   Code: " . $storedCode);
            error_log("   Created: " . $createdAt);
            error_log("   Expires: " . $expiresAt);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("💥 Error storing verification code: " . $e->getMessage());
        return false;
    }
}

    /**
 * Validate verification code - WITH TIMEZONE FIX
 */
private function validateVerificationCode($email, $code) {
    try {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        error_log("=== VALIDATE VERIFICATION CODE ===");
        error_log("Email: " . $email);
        error_log("Code to validate: " . $code);
        
        // TEMPORARY: Remove expiration check to test
        $db->query('
            SELECT prt.* 
            FROM PASSWORD_RESET_TOKENS prt 
            JOIN USER_INFORMATION ui ON prt.user_id = ui.ID 
            WHERE ui.Email = :email 
            AND prt.token = :code
            AND prt.is_used = FALSE
            -- AND prt.expires_at > NOW()  -- Temporarily commented out
            LIMIT 1
        ');
        $db->bind(':email', $email);
        $db->bind(':code', $code);
        
        $result = $db->single();
        
        if ($result) {
            $storedToken = is_object($result) ? $result->token : $result['token'];
            $expiresAt = is_object($result) ? $result->expires_at : $result['expires_at'];
            
            error_log("✅ Token found:");
            error_log("   Stored: '" . $storedToken . "'");
            error_log("   Provided: '" . $code . "'");
            error_log("   Expires: " . $expiresAt);
            error_log("   Match: " . ($storedToken === $code ? 'YES' : 'NO'));
            
            if ($storedToken === $code) {
                error_log("🎉 CODE VALIDATION SUCCESS");
                return true;
            }
        } else {
            error_log("❌ No matching token found");
            
            // Debug what tokens exist
            $db->query('
                SELECT prt.* 
                FROM PASSWORD_RESET_TOKENS prt 
                JOIN USER_INFORMATION ui ON prt.user_id = ui.ID 
                WHERE ui.Email = :email
            ');
            $db->bind(':email', $email);
            $allTokens = $db->resultSet();
            
            error_log("All tokens for this email:");
            foreach ($allTokens as $token) {
                $tokenData = is_object($token) ? $token->token : $token['token'];
                $expires = is_object($token) ? $token->expires_at : $token['expires_at'];
                $used = is_object($token) ? $token->is_used : $token['is_used'];
                error_log("   Token: '" . $tokenData . "', Expires: " . $expires . ", Used: " . $used);
            }
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("💥 Error validating verification code: " . $e->getMessage());
        return false;
    }
}

public function debugCurrentTokens() {
    // Clear output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    try {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode(['error' => 'No email provided']);
            exit();
        }
        
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        $db->query('
            SELECT prt.*, ui.Email 
            FROM PASSWORD_RESET_TOKENS prt 
            JOIN USER_INFORMATION ui ON prt.user_id = ui.ID 
            WHERE ui.Email = :email
            ORDER BY prt.created_at DESC
        ');
        $db->bind(':email', $email);
        $tokens = $db->resultSet();
        
        $debugInfo = [
            'email' => $email,
            'tokens_found' => count($tokens),
            'tokens' => $tokens,
            'current_time' => date('Y-m-d H:i:s')
        ];
        
        foreach ($tokens as $token) {
            $tokenData = is_object($token) ? $token->token : $token['token'];
            $expiresAt = is_object($token) ? $token->expires_at : $token['expires_at'];
            $isUsed = is_object($token) ? $token->is_used : $token['is_used'];
            
            error_log("Debug - Token: '" . $tokenData . "', Expires: " . $expiresAt . ", Used: " . $isUsed);
        }
        
        echo json_encode($debugInfo, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

    /**
 * Mark verification code as used - WITH DEBUGGING
 */
private function markVerificationCodeAsUsed($email, $code) {
    try {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        $db->query('
            UPDATE PASSWORD_RESET_TOKENS prt 
            JOIN USER_INFORMATION ui ON prt.user_id = ui.ID 
            SET prt.is_used = TRUE 
            WHERE ui.Email = :email 
            AND prt.token = :code
        ');
        $db->bind(':email', $email);
        $db->bind(':code', $code);
        
        return $db->execute();
        
    } catch (Exception $e) {
        error_log("Error marking code as used: " . $e->getMessage());
        return false;
    }
}

    /**
     * Send verification email with code
     */
    private function sendVerificationEmail($email, $name, $verificationCode) {
        try {
            $emailSenderPath = __DIR__ . '/EmailSender.php';
            if (!file_exists($emailSenderPath)) {
                $emailSenderPath = ROOT_DIR . '/app/Utils/EmailSender.php';
            }
            
            if (!file_exists($emailSenderPath)) {
                error_log("EmailSender not found");
                return false;
            }
            
            require_once $emailSenderPath;
            $emailSender = new EmailSender();
            
            $subject = 'Password Reset Verification - Compendium System';
            $body = $this->getVerificationEmailBody($name, $verificationCode);
            
            return $emailSender->sendHtmlEmail($email, $name, $subject, $body);
            
        } catch (Exception $e) {
            error_log("Verification email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate verification email body
     */
    private function getVerificationEmailBody($name, $verificationCode) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
                .code-box { background: #e74c3c; color: white; padding: 15px; border-radius: 5px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; }
                .info-box { background: #ecf0f1; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Compendium System</h1>
                    <p>University of Southeastern Philippines</p>
                </div>
                
                <div class='content'>
                    <h2>Password Reset Verification</h2>
                    <p>Hello {$name},</p>
                    <p>You requested to reset your password. Use the verification code below to complete the process.</p>
                    
                    <div class='code-box'>
                        {$verificationCode}
                    </div>
                    
                    <div class='info-box'>
                        <p><strong>Important Information:</strong></p>
                        <ul>
                            <li>Enter this 6-digit code in the verification form</li>
                            <li>This code will expire in 15 minutes</li>
                            <li>You will be able to set your new password after verification</li>
                            <li>If you didn't request this reset, please ignore this email</li>
                        </ul>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " University of Southeastern Philippines | Compendium System</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    


    /**
 * Debug method to check database state
 */
public function debugDatabaseState() {
    // Clear output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    try {
        $email = $_POST['email'] ?? '';
        
        error_log("=== DATABASE DEBUG REQUEST ===");
        error_log("Debug email: " . $email);
        
        if (empty($email)) {
            echo json_encode(['error' => 'No email provided']);
            exit();
        }
        
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        // 1. Check if user exists
        $db->query('SELECT ID, Email, First_Name, Last_Name FROM USER_INFORMATION WHERE Email = :email');
        $db->bind(':email', $email);
        $user = $db->single();
        
        $debugInfo = [
            'user_exists' => !empty($user),
            'user_data' => $user,
            'tokens_in_database' => []
        ];
        
        if ($user) {
            $userId = is_object($user) ? $user->ID : $user['ID'];
            
            // 2. Check all tokens for this user
            $db->query('SELECT * FROM PASSWORD_RESET_TOKENS WHERE user_id = :user_id ORDER BY created_at DESC');
            $db->bind(':user_id', $userId);
            $tokens = $db->resultSet();
            
            $debugInfo['tokens_in_database'] = $tokens;
            $debugInfo['user_id'] = $userId;
            
            error_log("User found - ID: " . $userId);
            error_log("Tokens count: " . count($tokens));
            
            foreach ($tokens as $token) {
                $tokenData = is_object($token) ? $token->token : $token['token'];
                $parts = explode('|', $tokenData);
                $storedCode = $parts[0] ?? 'NO_CODE';
                $storedPassword = $parts[1] ?? 'NO_PASSWORD';
                
                error_log("Token: " . $tokenData);
                error_log("  - Code: " . $storedCode);
                error_log("  - Password: " . $storedPassword);
                error_log("  - Expires: " . (is_object($token) ? $token->expires_at : $token['expires_at']));
                error_log("  - Used: " . (is_object($token) ? $token->is_used : $token['is_used']));
            }
        }
        
        echo json_encode($debugInfo, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        error_log("Debug error: " . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}



public function googleLogin() {
        // Start output buffering
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        try {
            error_log("=== GOOGLE LOGIN - PROPER EXISTING USER HANDLING ===");
            
            // Set header for JSON response
            header('Content-Type: application/json');
            
            $credential = $_POST['credential'] ?? '';
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
            $selectedRole = $_POST['role'] ?? 'student';

            // Get client information for logging
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            // Log Google login attempt
            $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Google login attempt started');
        
            // Basic validation
            if (empty($credential) || empty($email)) {
                $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Missing Google authentication data');
                throw new Exception('Missing required Google authentication data.');
            }
        
            error_log("=== DEBUGGING USER EXISTENCE CHECK ===");
    
            // Check if user exists in database FIRST
            $user = $this->findByEmail($email);
    
            error_log("findByEmail result: " . ($user ? 'USER FOUND' : 'USER NOT FOUND'));
            error_log("findByEmail return type: " . gettype($user));
            error_log("findByEmail return value: " . print_r($user, true));
            
            if ($user) {
                error_log("✅ SHOULD GO TO LOGIN FLOW");
                // Convert user to array if it's an object
                if (is_object($user)) {
                    $user = (array)$user;
                }
                
                // Safely get user properties
                $accStatus = $user['Acc_Status'] ?? 'unknown';
                $userId = $user['ID'] ?? null;
                $userEmail = $user['Email'] ?? '';
                $firstName = $user['First_Name'] ?? '';
                $lastName = $user['Last_Name'] ?? '';
                $userRole = $user['User_Role'] ?? '';
                
                error_log("User details - ID: $userId, Status: $accStatus, Role: $userRole");
                
                // ✅ ROLE VALIDATION
                $normalizedUserRole = strtolower($userRole);
                $normalizedSelectedRole = strtolower($selectedRole);

                // Map role names for compatibility
                if ($normalizedSelectedRole === 'researcher') {
                    $normalizedSelectedRole = 'student';
                }

                if ($normalizedUserRole !== $normalizedSelectedRole) {
                    $this->logLoginAttempt($email, $userId, $ipAddress, $userAgent, false, "Role mismatch: $normalizedUserRole vs $normalizedSelectedRole");
                    error_log("❌ ROLE MISMATCH: User role ($normalizedUserRole) vs selected role ($normalizedSelectedRole)");
                    throw new Exception("This account is registered as a " . ucfirst($normalizedUserRole) . ". Please use the " . ucfirst($normalizedUserRole) . " login option.");
                }
                
                // ✅ Check account status
                if ($accStatus === 'pending') {
                    $this->logLoginAttempt($email, $userId, $ipAddress, $userAgent, false, 'Account pending approval');
                    throw new Exception('Your account is pending approval. Please wait for administrator approval.');
                } else if ($accStatus === 'rejected') {
                    $this->logLoginAttempt($email, $userId, $ipAddress, $userAgent, false, 'Account rejected');
                    throw new Exception('Your account registration was rejected. Please contact the administrator.');
                } else if ($accStatus === 'approved') {
                    // ✅ EXISTING APPROVED USER: Log them in
                    error_log("✅ Account approved - logging in existing user");
                    
                    $this->createUserSession([
                        'id' => $userId,
                        'email' => $userEmail,
                        'name' => $firstName . ' ' . $lastName,
                        'role' => $userRole
                    ]);
                    
                    // Log successful Google login
                    $this->logLoginAttempt($email, $userId, $ipAddress, $userAgent, true, 'Google login successful');
                    $this->logUserAction($userId, 'google_login', 'User logged in via Google');
                    
                    error_log("✅ Session created successfully for existing user ID: $userId");
                    
                    $redirectUrl = $this->getRedirectUrlByRole($userRole);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful!',
                        'redirect_url' => $redirectUrl
                    ]);
                    exit();
                } else {
                    $this->logLoginAttempt($email, $userId, $ipAddress, $userAgent, false, 'Invalid account status');
                    throw new Exception('Your account status is invalid. Please contact administrator.');
                }
            } else {
                error_log("❌ GOING TO REGISTRATION FLOW (THIS SHOULD NOT HAPPEN FOR EXISTING USERS)");
                $registrationResult = $this->autoRegisterGoogleUser($email, $name, $selectedRole);

                if ($registrationResult['success']) {
                    $userId = $registrationResult['user_id'] ?? null;
                    
                    if ($userId) {
                        // Log the user in after registration
                        $this->createUserSession([
                            'id' => $userId,
                            'email' => $email,
                            'name' => $name,
                            'role' => $selectedRole
                        ]);
                        
                        // Log successful Google registration and login
                        $this->logLoginAttempt($email, $userId, $ipAddress, $userAgent, true, 'Google registration and login successful');
                        $this->logUserAction($userId, 'google_register', 'User registered and logged in via Google');
                        
                        $redirectUrl = $this->getRedirectUrlByRole($selectedRole);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Account created successfully!',
                            'redirect_url' => $redirectUrl
                        ]);
                        exit();
                    } else {
                        $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Account created but login failed');
                        throw new Exception('Account created but login failed. Please try logging in manually.');
                    }
                } else {
                    $this->logLoginAttempt($email, null, $ipAddress, $userAgent, false, 'Google registration failed: ' . $registrationResult['message']);
                    throw new Exception($registrationResult['message']);
                }
            }
    
        } catch (Exception $e) {
            error_log("Google login exception: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

private function getRedirectUrlByRole($userRole) {
    $normalizedRole = strtolower($userRole);
    
    switch ($normalizedRole) {
        case 'student':
        case 'researcher':
            return '../../Views/User/userViewPage.php';
            
        case 'faculty':
            return '../../Views/User/userViewPage.php'; // Adjust path as needed
            
        case 'superadmin':
        case 'subadmin':
        case 'admin':
            return '../../Views/Admin/AdminDashboard.php';
            
        default:
            error_log("Unknown role for redirect: " . $userRole);
            return '../../Views/User/userViewPage.php'; // Default fallback
    }
}

    
/**
 * Validate and format name (capitalize first letter, lowercase the rest)
 */
private function validateAndFormatName($name, $fieldName) {
    if (empty($name)) {
        return $name;
    }
    
    // Remove extra whitespace
    $name = trim($name);
    
    // Check if name contains only letters, spaces, hyphens, and apostrophes
    if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
        throw new Exception("$fieldName can only contain letters, spaces, hyphens (-), apostrophes ('), and periods (.)");
    }
    
    // Check for consecutive special characters
    if (preg_match('/[\-\'\\.]{2,}/', $name)) {
        throw new Exception("$fieldName cannot have consecutive special characters");
    }
    
    // Capitalize first letter of each word
    $formattedName = $this->properCaseName($name);
    
    return $formattedName;
}

/**
 * Convert name to proper case (First Letter Capital, rest lowercase)
 */
private function properCaseName($name) {
    $words = explode(' ', $name);
    $properWords = [];
    
    foreach ($words as $word) {
        // Handle hyphenated names (like Mary-Ann)
        if (strpos($word, '-') !== false) {
            $hyphenated = explode('-', $word);
            $properHyphenated = [];
            foreach ($hyphenated as $hWord) {
                $properHyphenated[] = ucfirst(strtolower($hWord));
            }
            $properWords[] = implode('-', $properHyphenated);
        }
        // Handle apostrophe names (like O'Connor)
        elseif (strpos($word, "'") !== false) {
            $apostropheParts = explode("'", $word);
            $properApostrophe = [];
            foreach ($apostropheParts as $aPart) {
                $properApostrophe[] = ucfirst(strtolower($aPart));
            }
            $properWords[] = implode("'", $properApostrophe);
        }
        // Normal case - capitalize first letter, lowercase the rest
        else {
            $properWords[] = ucfirst(strtolower($word));
        }
    }
    
    return implode(' ', $properWords);
}

   /**
 * Auto-register a user from Google Sign-In - WITH PROPER ROLE ASSIGNMENT
 */
public function autoRegisterGoogleUser($email, $name, $selectedRole) {
    error_log("=== AUTO REGISTRATION CALLED ===");
    
    require_once ROOT_DIR . '\app\Models\User.php';
    
    try {
        $userModel = new User();
        
        // DOUBLE CHECK - user should not exist at this point, but check anyway
        $existingUser = $this->findByEmail($email);
        if ($existingUser) {
            error_log("⚠️ USER ALREADY EXISTS - this should not happen here!");
            throw new Exception("User already exists. Please try logging in instead.");
        }
        
        // Proceed with new user registration
        $autoPassword = $this->generateAutoPassword();
        $nameParts = $this->parseName($name);
        $firstName = $nameParts['first_name'];
        $lastName = $nameParts['last_name'];
        
        $userRole = ($selectedRole === 'student' || $selectedRole === 'researcher') ? 'student' : $selectedRole;

        $userData = [
            'password' => $autoPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'user_role' => $userRole,
            'acc_status' => 'approved',
            'profile_pic' => base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7')
        ];
        
        // Pass empty IDs so the model generates them
        if ($userRole === 'student') {
            $userData['student_id'] = '';
        } else if ($userRole === 'faculty') {
            $userData['employee_id'] = '';
        }
        
        error_log("Calling userModel->registerGoogleUser()");
        $result = $userModel->registerGoogleUser($userData);
        
        if ($result && isset($result['db_id'])) {
            $userId = $result['db_id'];
            $userIdentifier = $result['user_identifier']; // This is the original unhashed User_ID
            
            error_log("✅ User registered successfully:");
            error_log("   - Database ID: " . $userId);
            error_log("   - User_ID for email: " . $userIdentifier);
            
            // ✅ SEND WELCOME EMAIL WITH ACTUAL UNHASHED USER_ID
            $this->sendWelcomeEmail($email, $name, $autoPassword, $userRole, $userIdentifier);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Account created successfully as ' . ucfirst($userRole)
            ];
        } else {
            $modelError = $userModel->getError();
            error_log("❌ Registration failed: " . $modelError);
            throw new Exception($modelError ?: 'Failed to create account.');
        }
        
    } catch (Exception $e) {
        error_log("Auto-registration exception: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

    /**
 * Restore a previously deleted user
 */
private function restoreDeletedUser($email) {
    try {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        
        // Update the user status to approved and clear deletion flags
        $db = $userModel->getDb();
        
        // Build update query based on your database structure
        $updateData = [
            'Acc_Status' => 'approved',
            'is_deleted' => 0,
            'deleted_at' => null
        ];
        
        $db->query('UPDATE USER_INFORMATION SET Acc_Status = :acc_status, is_deleted = 0, deleted_at = NULL WHERE Email = :email');
        $db->bind(':acc_status', 'approved');
        $db->bind(':email', $email);
        
        $result = $db->execute();
        error_log("User restore result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error restoring user: " . $e->getMessage());
        return false;
    }
}


    /**
     * Generate auto password for Google users
     */
    private function generateAutoPassword() {
        // Generate a random 12-character password
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * Send welcome email to new Google Sign-In users
     */
    private function sendWelcomeEmail($email, $name, $password, $role, $userIdentifier) {
        try {
            // Use __DIR__ to get the current directory path
            $emailSenderPath = __DIR__ . '/EmailSender.php';
            
            error_log("Looking for EmailSender at: " . $emailSenderPath);
            
            if (!file_exists($emailSenderPath)) {
                error_log("EmailSender.php not found at: " . $emailSenderPath);
                
                // Try alternative relative path
                $emailSenderPath = ROOT_DIR . '/app/Utils/EmailSender.php';
                error_log("Trying alternative path: " . $emailSenderPath);
                
                if (!file_exists($emailSenderPath)) {
                    error_log("EmailSender.php not found at alternative path: " . $emailSenderPath);
                    return false;
                }
            }
            
            error_log("Loading EmailSender from: " . $emailSenderPath);
            require_once $emailSenderPath;
            
            $emailSender = new EmailSender();
            
            // Log what we're sending
            error_log("Sending welcome email with User Identifier: " . $userIdentifier);
            
            return $emailSender->sendWelcomeEmail($email, $name, $password, $role, $userIdentifier);
            
        } catch (Exception $e) {
            error_log("Welcome email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
 * Send welcome back email for restored users
 */
private function sendWelcomeBackEmail($email, $name, $role) {
    try {
        $emailSenderPath = ROOT_DIR . '\app\Utils\EmailSender.php';
        if (!file_exists($emailSenderPath)) {
            error_log("EmailSender.php not found at: " . $emailSenderPath);
            return false;
        }
        
        require_once $emailSenderPath;
        
        $emailSender = new EmailSender();
        
        // Create a welcome back email
        $subject = 'Welcome Back to Compendium System';
        $body = $this->getWelcomeBackBody($name, $email, $role);
        
        return $emailSender->sendHtmlEmail($email, $name, $subject, $body);
        
    } catch (Exception $e) {
        error_log("Welcome back email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate welcome back email body
 */
private function getWelcomeBackBody($name, $email, $role) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .footer { background: #34495e; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
            .info-box { background: #ecf0f1; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Compendium System</h1>
                <p>University of Southeastern Philippines</p>
            </div>
            
            <div class='content'>
                <h2>Welcome Back, {$name}!</h2>
                <p>Your account has been successfully restored in the Compendium System.</p>
                
                <div class='info-box'>
                    <p><strong>Account Details:</strong></p>
                    <ul>
                        <li><strong>Email:</strong> {$email}</li>
                        <li><strong>Role:</strong> " . ucfirst($role) . "</li>
                        <li><strong>Login Method:</strong> Google Sign-In</li>
                    </ul>
                </div>
                
                <div class='info-box'>
                    <p><strong>Important Information:</strong></p>
                    <ul>
                        <li>You can continue using your previous password or use Google Sign-In</li>
                        <li>If you forgot your password, please contact the administrator</li>
                        <li>All your previous data has been restored</li>
                    </ul>
                </div>
                
                <p><strong>Access the system:</strong> <a href='http://localhost:3000'>Compendium System Portal</a></p>
                
                <p>If you have any questions, please contact the system administrator.</p>
            </div>
            
            <div class='footer'>
                <p>&copy; " . date('Y') . " University of Southeastern Philippines | Compendium System</p>
                <p>This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

    /**
     * Parse full name into first and last name
     */
    private function parseName($fullName) {
        // First, format the entire name
        $formattedFullName = $this->validateAndFormatName($fullName, 'Full Name');
        
        $nameParts = explode(' ', trim($formattedFullName));
        
        $firstName = $nameParts[0] ?? '';
        $lastName = end($nameParts) ?? '';
        
        // If there's a middle name, include it in first name or handle as needed
        if (count($nameParts) > 2) {
            $firstName = $nameParts[0] . ' ' . $nameParts[1];
            $lastName = $nameParts[2] ?? $lastName;
        }
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }

    /**
     * Generate user ID number based on role
     */
    


	/**
     * Find user by email for Google login flow
     */
    public function findByEmail($email) {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        
        error_log("=== AUTH CONTROLLER FIND BY EMAIL ===");
        error_log("Looking for: " . $email);
        
        // Method 1: Use the User model's method (with hashing)
        $user = $userModel->findByEmail($email);
        
        if ($user) {
            error_log("✅ User found via User model");
            return $user;
        }
        
        error_log("❌ User not found via User model, trying direct lookup...");
        
        // Method 2: Try direct database lookup (without hashing) for debugging
        try {
            $db = $userModel->getDb();
            
            // Look for any user with similar email pattern
            $db->query('SELECT * FROM USER_INFORMATION WHERE Email LIKE :pattern LIMIT 5');
            $db->bind(':pattern', '%' . $email . '%');
            $similarUsers = $db->resultSet();
            
            error_log("Similar users found: " . count($similarUsers));
            foreach ($similarUsers as $index => $similarUser) {
                $userEmail = is_object($similarUser) ? $similarUser->Email : $similarUser['Email'];
                $userId = is_object($similarUser) ? $similarUser->ID : $similarUser['ID'];
                error_log("  User $index - ID: $userId, Email: $userEmail");
            }
            
        } catch (Exception $e) {
            error_log("Direct lookup error: " . $e->getMessage());
        }
        
        return null;
    }

   

    

    private function isAuthorizedAdminEmail($email) {
        // Define your authorized admin emails
        $authorizedAdmins = [
            
            'superadmin@usep.edu.ph',
            // Add other authorized admin emails
        ];
        
        return in_array($email, $authorizedAdmins);
    }

    private function authenticateUser($username, $password, $role) {
        require_once ROOT_DIR . '\app\Models\User.php';
        
        try {
            $userModel = new User();
            
            error_log("=== AUTHENTICATE USER DEBUG ===");
            error_log("Username: " . $username);
            error_log("Role: " . $role);
            
            // MODIFIED: Use loginByEmail which now returns user regardless of status
            $user = $userModel->loginByEmail($username, $password);
            
            if ($user) {
                error_log("✅ User found in database");
                
                // Check if user role matches the selected role
                $userRole = strtolower($user->User_Role ?? '');
                $selectedRole = strtolower($role);
                
                // Map role names for compatibility
                $roleMapping = [
                    'researcher' => 'student',
                    'faculty' => 'faculty'
                ];
                
                $mappedRole = $roleMapping[$selectedRole] ?? $selectedRole;
                
                error_log("User role: " . $userRole);
                error_log("Selected role: " . $mappedRole);
                error_log("Account status: " . ($user->Acc_Status ?? 'unknown'));
                
                if ($userRole === $mappedRole) {
                    // Check account status before allowing login
                    if ($user->Acc_Status === 'pending') {
                        error_log("❌ Account pending approval");
                        $_SESSION['error_message'] = "Your account is pending approval. Please wait for administrator approval before logging in.";
                        return false;
                    } else if ($user->Acc_Status === 'rejected') {
                        error_log("❌ Account rejected");
                        $_SESSION['error_message'] = "Your account registration was rejected. Please contact the administrator for more information.";
                        return false;
                    } else if ($user->Acc_Status === 'approved') {
                        // Account is approved - allow login
                        error_log("✅ Account approved - login allowed");
                        return [
                            'id' => $user->ID,
                            'username' => $user->Email,
                            'email' => $user->Email,
                            'name' => $user->First_Name . ' ' . $user->Last_Name,
                            'role' => $user->User_Role
                        ];
                    } else {
                        // Unknown status
                        error_log("❌ Unknown account status: " . ($user->Acc_Status ?? 'unknown'));
                        $_SESSION['error_message'] = 'Your account status is invalid. Please contact administrator.';
                        return false;
                    }
                } else {
                    error_log("❌ Role mismatch: User role is $userRole, but selected role is $selectedRole");
                    $_SESSION['error_message'] = 'Invalid credentials for the selected role.';
                    return false;
                }
            } else {
                // No user found or password incorrect
                error_log("❌ No user found or password incorrect");
                
                // Debug: Check if user exists but password is wrong
                $userExists = $userModel->findByEmail($username);
                if ($userExists) {
                    error_log("⚠️ User exists but password verification failed");
                    error_log("Stored password hash: " . ($userExists->pswrd ?? 'not found'));
                    
                    // Test password verification
                    $storedPassword = $userExists->pswrd ?? '';
                    $passwordValid = password_verify($password, $storedPassword);
                    error_log("Password verification result: " . ($passwordValid ? 'VALID' : 'INVALID'));
                } else {
                    error_log("⚠️ User not found with email: " . $username);
                }
                
                $_SESSION['error_message'] = 'Invalid credentials. Please try again.';
                return false;
            }
            
        } catch (Exception $e) {
            error_log("💥 Authentication error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Authentication error: ' . $e->getMessage();
            return false;
        }
    }

    
    public function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['logged_in'] = true;

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
     public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logUserAction($_SESSION['user_id'], 'logout', 'User logged out');
        }

        $_SESSION = array();

        session_destroy();

        $this->redirect('../../app/Views/User/indexLogin.php');
    }

    private function redirectWithError($message) {
        $_SESSION['error_message'] = $message;
        header('Location: ../../app/Views/User/indexLogin.php');
        exit();
    }

    protected function redirect($location) {
        header("Location: $location");
        exit();
    }
}

// Instantiate and handle the request if this file is accessed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Check if this is a Google login request
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') === 'google_login') {
        $authController = new AuthController();
        $authController->googleLogin();
        exit();
    } else {
        // Handle other requests normally
        $authController = new AuthController();
        $authController->handleRequest();
    }
}
?>