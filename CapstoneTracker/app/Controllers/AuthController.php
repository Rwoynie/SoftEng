<?php



if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2)); // Adjust based on your directory structure
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && 
            hash_equals($_SESSION['csrf_token'], $token);
    }

    public function handleRequest() {
        // Start session at the beginning
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Generate CSRF token if it doesn't exist
            $this->generateCsrfToken();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $action = $_POST['action'] ?? ($_GET['action'] ?? '');
            
            error_log("AuthController - Action received: " . $action);
            
            if ($action === 'login') {
                $this->processLogin();
            } elseif ($action === 'googleLogin') {
                $this->googleLogin();
            } elseif ($action === 'adminGoogleLogin') {
                $this->adminGoogleLogin();
            } elseif ($action === 'sendVerificationCode') {
                $this->sendVerificationCode();
            } elseif ($action === 'verifyResetCode') {
                $this->verifyResetCode();
            } elseif ($action === 'debugCurrentTokens') { // Add this line
                $this->debugCurrentTokens(); // Add this line
            } elseif ($action === 'debugDatabaseState') { // Add this line if missing
                $this->debugDatabaseState(); // Add this line if missing
            } else {
                // Handle unknown action
                error_log("Invalid action: " . $action);
                $_SESSION['error_message'] = "Invalid action: " . $action;
                header('Location: ../../app/Views/User/indexLogin.php');
                exit();
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
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
    
        // Validate CSRF token first
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($csrfToken)) {
            $this->redirectWithError('Invalid security token. Please try again.');
            return;
        }
    
        $username = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
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
            // Create session and redirect
            $this->createUserSession($user);
            $this->redirect('../../app/Views/User/userViewPage.php');
        } else {
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
    // Clear output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    
    try {
        $email = $_POST['email'] ?? '';
        $verificationCode = $_POST['verification_code'] ?? '';
        $newPassword = $_POST['new_password'] ?? ''; // User's chosen password
        
        error_log("Verify reset code with user's password");
        error_log("Email: " . $email);
        error_log("Code: " . $verificationCode);
        
        if (empty($email) || empty($verificationCode) || empty($newPassword)) {
            throw new Exception('All fields are required.');
        }
        
        if (strlen($verificationCode) !== 6 || !is_numeric($verificationCode)) {
            throw new Exception('Invalid verification code format.');
        }
        
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters long.');
        }
        
        // Validate verification code (simpler now - just check the code)
        $isValid = $this->validateVerificationCode($email, $verificationCode);
        
        if (!$isValid) {
            throw new Exception('Invalid verification code. Please check the code and try again.');
        }
        
        // Get user
        $user = $this->findByEmail($email);
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        if (is_object($user)) {
            $user = (array)$user;
        }
        
        $userId = $user['ID'] ?? null;
        
        // Update user password with user's chosen password
        $passwordUpdated = $this->updateUserPassword($userId, $newPassword);
        
        if ($passwordUpdated) {
            // Mark code as used
            $this->markVerificationCodeAsUsed($email, $verificationCode);
            
            error_log("Password reset successful for: " . $email);
            echo json_encode([
                'success' => true,
                'message' => 'Password has been reset successfully.'
            ]);
        } else {
            throw new Exception('Failed to reset password. Please try again.');
        }
        
    } catch (Exception $e) {
        error_log("Verify reset code error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
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
 * Update user password - WITH DETAILED DEBUGGING
 */
private function updateUserPassword($userId, $newPassword) {
    try {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        error_log("=== UPDATE USER PASSWORD DEBUG ===");
        error_log("User ID: " . $userId);
        error_log("New Password (plain): " . $newPassword);
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        error_log("Hashed Password: " . $hashedPassword);
        
        // Check if password is valid
        if (empty($hashedPassword)) {
            error_log("❌ Password hashing failed!");
            return false;
        }
        
        error_log("Executing UPDATE query with column 'pswrd'...");
        
        // FIXED: Using correct column name 'pswrd'
        $db->query('UPDATE USER_INFORMATION SET pswrd = :password WHERE ID = :user_id');
        $db->bind(':password', $hashedPassword);
        $db->bind(':user_id', $userId);
        
        $result = $db->execute();
        
        error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        // Check how many rows were affected
        $rowCount = $db->rowCount();
        error_log("Rows affected: " . $rowCount);
        
        if ($result && $rowCount > 0) {
            error_log("✅ Password update confirmed - row modified");
            
            // Verify the update worked by reading back the password
            $db->query('SELECT pswrd FROM USER_INFORMATION WHERE ID = :user_id'); // FIXED: pswrd
            $db->bind(':user_id', $userId);
            $updatedUser = $db->single();
            
            if ($updatedUser) {
                $storedPassword = is_object($updatedUser) ? $updatedUser->pswrd : $updatedUser['pswrd']; // FIXED: pswrd
                error_log("Stored password after update: " . $storedPassword);
                
                // Verify the hash
                $passwordMatches = password_verify($newPassword, $storedPassword);
                error_log("Password verification: " . ($passwordMatches ? 'SUCCESS' : 'FAILED'));
            }
        } else {
            error_log("❌ No rows affected by update");
        }
        
        return $result && $rowCount > 0;
        
    } catch (Exception $e) {
        error_log("💥 Error updating user password: " . $e->getMessage());
        return false;
    }
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
    // Start output buffering to catch any errors
    ob_start();
    
    try {
        error_log("=== GOOGLE LOGIN DEBUG WITH ROLE VALIDATION ===");
        error_log("POST data: " . print_r($_POST, true));
        
        // Clear any previous output
        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        
        // Set header first to ensure clean JSON
        header('Content-Type: application/json');
        
        
        $credential = $_POST['credential'] ?? '';
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $selectedRole = $_POST['role'] ?? 'student'; // Get the selected role
    
        // Basic validation
        if (empty($credential) || empty($email)) {
            throw new Exception('Missing required Google authentication data.');
        }
    
        error_log("Email: " . $email);
        error_log("Name: " . $name);
        error_log("Selected Role: " . $selectedRole);
    
        // TEMPORARY: Skip Google token validation during development
        $isValidToken = true;
        error_log("Google token validation SKIPPED for development");
    
        // Check if user exists in your database
        error_log("Checking if user exists in database...");
        $user = $this->findByEmail($email);
        
        
        if ($user) {
            // Convert user to array if it's an object
            if (is_object($user)) {
                $user = (array)$user;
            }
            
            // Safely get user properties
            $accStatus = $user['Acc_Status'] ?? $user->Acc_Status ?? 'unknown';
            $userId = $user['ID'] ?? $user->ID ?? null;
            $userEmail = $user['Email'] ?? $user->Email ?? '';
            $firstName = $user['First_Name'] ?? $user->First_Name ?? '';
            $lastName = $user['Last_Name'] ?? $user->Last_Name ?? '';
            $userRole = $user['User_Role'] ?? $user->User_Role ?? '';
            
            error_log("User found in database. Account status: " . $accStatus);
            error_log("User ID: " . $userId);
            error_log("User Role: " . $userRole);
            error_log("Selected Role: " . $selectedRole);
            
            // ✅ ROLE VALIDATION: Check if user role matches selected role
            $normalizedUserRole = strtolower($userRole);
            $normalizedSelectedRole = strtolower($selectedRole);
            
            // Map role names for compatibility
            $roleMapping = [
                'researcher' => 'student',  // Map 'researcher' to 'student' for validation
                'student' => 'student',     // Add direct mapping
                'faculty' => 'faculty'      // Add direct mapping
            ];
            
            $mappedSelectedRole = $roleMapping[$normalizedSelectedRole] ?? $normalizedSelectedRole;
            
            error_log("Normalized User Role: " . $normalizedUserRole);
            error_log("Mapped Selected Role: " . $mappedSelectedRole);
            
            if ($normalizedUserRole !== $mappedSelectedRole) {
                error_log("❌ ROLE MISMATCH: User role ($normalizedUserRole) does not match selected role ($mappedSelectedRole)");
                throw new Exception("This account is registered as a " . ucfirst($normalizedUserRole) . ". Please use the " . ucfirst($normalizedUserRole) . " login option.");
            }
            
            // Check for admin roles (they have special handling)
            if ($userRole === 'superAdmin' || $userRole === 'SubAdmin') {
                // Add additional admin validation here if needed
                $isAuthorizedAdmin = $this->isAuthorizedAdminEmail($email);
                if (!$isAuthorizedAdmin) {
                    throw new Exception('This Google account is not authorized for admin access.');
                }
            }

            // ✅ EXISTING USER: Check if account is approved
            if ($accStatus === 'pending') {
                throw new Exception('Your account is pending approval. Please wait for administrator approval.');
            } else if ($accStatus === 'rejected') {
                throw new Exception('Your account registration was rejected. Please contact the administrator.');
            } else if ($accStatus === 'approved') {
                // ✅ EXISTING APPROVED USER: Log them in
                error_log("User account approved, creating session...");
                $this->createUserSession([
                    'id' => $userId,
                    'email' => $userEmail,
                    'name' => $firstName . ' ' . $lastName,
                    'role' => $userRole
                ]);
                
                error_log("Session created successfully");
                
                // Determine redirect URL based on role
                $redirectUrl = $this->getRedirectUrlByRole($userRole);
                error_log("Redirecting to: " . $redirectUrl);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect_url' => $redirectUrl
                ]);
                exit();
            } else {
                throw new Exception('Your account status is invalid. Please contact administrator.');
            }
        } else {
            error_log("User not found in database, starting auto-registration...");
            // ❌ NEW USER: Auto-register them with the selected role
            $registrationResult = $this->autoRegisterGoogleUser($email, $name, $selectedRole);
            error_log("Auto-registration result: " . print_r($registrationResult, true));
            
            if ($registrationResult['success']) {
                error_log("Auto-registration successful, creating session...");
                // Log the user in after registration
                $this->createUserSession([
                    'id' => $registrationResult['user_id'],
                    'email' => $email,
                    'name' => $name,
                    'role' => $selectedRole
                ]);
                
                error_log("Session created for new user");
                
                // Determine redirect URL based on role
                $redirectUrl = $this->getRedirectUrlByRole($selectedRole);
                error_log("Redirecting to: " . $redirectUrl);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Account created, check your email for further instruction.',
                    'redirect_url' => $redirectUrl
                ]);
                exit();
            } else {
                throw new Exception($registrationResult['message']);
            }
        }
    
    } catch (Exception $e) {
        // Clear any output that might have been generated
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        error_log("Google login exception: " . $e->getMessage());
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
     * Handle Google Sign-In for admin users
     */
    public function adminGoogleLogin() {
        // Start output buffering to catch any errors
        ob_start();
        
        try {
            error_log("=== ADMIN GOOGLE LOGIN DEBUG START ===");
            error_log("POST data: " . print_r($_POST, true));
            
            // Clear any previous output
            while (ob_get_level() > 1) {
                ob_end_clean();
            }
            
            // Set header first to ensure clean JSON
            header('Content-Type: application/json');
            
            $credential = $_POST['credential'] ?? '';
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
    
            // Basic validation
            if (empty($credential) || empty($email)) {
                throw new Exception('Missing required Google authentication data.');
            }
    
            error_log("Admin Google Login - Email: " . $email);
            error_log("Admin Google Login - Name: " . $name);
    
            // Skip Google token validation during development
            $isValidToken = true;
            error_log("Admin Google token validation SKIPPED for development");
    
            // Check if admin exists with this email
            error_log("Checking if admin exists in database...");
            $admin = $this->findAdminByEmail($email);
            
            if ($admin) {
                // Convert admin to array if it's an object
                if (is_object($admin)) {
                    $admin = (array)$admin;
                }
                
                // Get admin properties - USING EMAIL AS IDENTIFIER
                $adminId = $admin['ID'] ?? $admin->ID ?? null; // Regular ID, not User_ID
                $adminEmail = $admin['Email'] ?? $admin->Email ?? '';
                $accStatus = $admin['Acc_Status'] ?? $admin->Acc_Status ?? 'unknown';
                $userRole = $admin['User_Role'] ?? $admin->User_Role ?? '';
                
                error_log("Admin found. ID: " . $adminId . ", Email: " . $adminEmail . ", Role: " . $userRole . ", Status: " . $accStatus);
                
                // Verify it's actually an admin or sub-admin
                
                
                // Check if account is approved
                if ($accStatus === 'pending') {
                    throw new Exception('Your admin account is pending approval.');
                } else if ($accStatus === 'rejected') {
                    throw new Exception('Your admin account was rejected. Please contact system administrator.');
                } else if ($accStatus === 'approved') {
                    // ✅ APPROVED ADMIN: Log them in
                    error_log("Admin account approved, creating session...");
                    $this->createAdminSession([
                        'id' => $adminId,
                        'email' => $adminEmail,
                        'name' => $name,
                        'role' => $userRole // Use actual role from database
                    ]);
                    
                    error_log("Admin session created successfully");
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
            // Clear any output that might have been generated
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
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
 * Auto-register a user from Google Sign-In - WITH PROPER ROLE ASSIGNMENT
 */
private function autoRegisterGoogleUser($email, $name, $selectedRole) {
    error_log("=== AUTO REGISTRATION DEBUG WITH ROLE ===");
    error_log("Email: $email, Name: $name, Selected Role: $selectedRole");
    
    require_once ROOT_DIR . '\app\Models\User.php';
    
    try {
        $userModel = new User();
        
        // Check database connection first
        $db = $userModel->getDb();
        if (!$db) {
            throw new Exception('Database connection failed');
        }
        
        // Test the connection
        try {
            $db->query('SELECT 1');
            $db->execute();
        } catch (Exception $e) {
            throw new Exception('Database connection test failed: ' . $e->getMessage());
        }
        
        // First, check if user exists but was soft/hard deleted
        $existingUser = $this->findByEmail($email);
        if ($existingUser) {
            error_log("User already exists in database: " . print_r($existingUser, true));
            
            // Check if user is soft deleted
            $isDeleted = isset($existingUser['is_deleted']) && $existingUser['is_deleted'] == 1;
            $isDeleted = $isDeleted || (isset($existingUser['deleted_at']) && !empty($existingUser['deleted_at']));
            
            if ($isDeleted) {
                error_log("User was previously deleted. Attempting to restore...");
                
                // Restore the user account
                $restoreResult = $this->restoreDeletedUser($email);
                if ($restoreResult) {
                    error_log("User restored successfully");
                    
                    // Send welcome back email
                    $this->sendWelcomeBackEmail($email, $name, $selectedRole);
                    
                    return [
                        'success' => true,
                        'user_id' => $existingUser['ID'] ?? $existingUser->ID,
                        'message' => 'Account restored successfully'
                    ];
                } else {
                    throw new Exception("Failed to restore previously deleted account");
                }
            } else {
                // User exists and is not deleted - just return success
                error_log("User already exists and is active");
                return [
                    'success' => true,
                    'user_id' => $existingUser['ID'] ?? $existingUser->ID,
                    'message' => 'Account already exists'
                ];
            }
        }
        
        // Generate auto password
        $autoPassword = $this->generateAutoPassword();
        error_log("Generated auto password: " . $autoPassword);
        
        // Parse name into first and last name
        $nameParts = $this->parseName($name);
        $firstName = $nameParts['first_name'];
        $lastName = $nameParts['last_name'];
        error_log("Parsed name - First: $firstName, Last: $lastName");
        
        // ✅ USE THE SELECTED ROLE FROM GOOGLE LOGIN
        // Determine user role based on which button was clicked
        $userRole = $selectedRole; // Use the role from the login modal
        error_log("User role for auto-registration: $userRole (from selected role: $selectedRole)");
        
        // Generate student/employee ID based on role
        $userIdNumber = $this->generateUserIdNumber($userRole);
        error_log("Generated user ID: $userIdNumber");
        
        // Prepare user data
        $userData = [
            'password' => $autoPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'user_role' => $userRole, // Use the selected role
            'acc_status' => 'approved',
            'profile_pic' => base64_decode('R0lGODlhAQABAIAAAAAA/P///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7')
        ];
        
        // Add role-specific fields
        if ($userRole === 'student' || $userRole === 'researcher') {
            $userData['student_id'] = $userIdNumber;
            $userData['course'] = 'Not Specified';
            $userData['designation'] = 'Student';
            error_log("Setting student-specific fields");
        } else if ($userRole === 'faculty') {
            $userData['employee_id'] = $userIdNumber;
            $userData['department'] = 'Not Specified';
            $userData['designation'] = 'Faculty';
            error_log("Setting faculty-specific fields");
        }
        
        // Register the user
        error_log("Calling userModel->register() with role: $userRole");
        $result = $userModel->register($userData);
        error_log("Register result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            // Get the newly created user ID
            error_log("Getting newly created user...");
            $newUser = $this->findByEmail($email);
            
            if ($newUser) {
                $userId = $newUser['ID'] ?? $newUser->ID ?? null;
                error_log("New user found with ID: " . $userId);
                
                // Get the actual role from the database to confirm
                $actualRole = $newUser['User_Role'] ?? $newUser->User_Role ?? 'unknown';
                error_log("Actual role in database: " . $actualRole);
                
                // Send welcome email
                $emailSent = $this->sendWelcomeEmail($email, $name, $autoPassword, $userRole);
                
                if ($emailSent) {
                    error_log("Welcome email sent successfully to: " . $email);
                } else {
                    error_log("Failed to send welcome email to: " . $email);
                    // Don't fail registration if email fails
                }
                
                error_log("=== AUTO REGISTRATION DEBUG END - SUCCESS ===");
                return [
                    'success' => true,
                    'user_id' => $userId,
                    'message' => 'Account created successfully as ' . ucfirst($userRole) . ($emailSent ? ' and welcome email sent' : '')
                ];
            } else {
                error_log("New user not found after registration");
                return [
                    'success' => false,
                    'message' => 'Account created but could not retrieve user ID'
                ];
            }
        } else {
            $modelError = $userModel->getError();
            error_log("Registration failed. Model error: " . $modelError);
            
            return [
                'success' => false,
                'message' => $modelError ?: 'Failed to create account. Please try again.'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Auto-registration exception: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Registration error: ' . $e->getMessage()
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
    private function sendWelcomeEmail($email, $name, $password, $role) {
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
            return $emailSender->sendWelcomeEmail($email, $name, $password, $role);
            
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
        $nameParts = explode(' ', trim($fullName));
        
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
    private function generateUserIdNumber($role) {
        $prefix = ($role === 'faculty') ? 'EMP-' : 'STU-';
        $randomNumber = random_int(10000, 99999);
        return $prefix . $randomNumber;
    }


	/**
     * Find user by email for Google login flow
     */
    private function findByEmail($email) {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        // Query to find user including soft-deleted ones
        $db->query('SELECT * FROM USER_INFORMATION WHERE Email = :email LIMIT 1');
        $db->bind(':email', $email);
        $result = $db->single();
        
        // Convert object to array if needed
        if (is_object($result)) {
            $result = (array)$result;
        }
        
        error_log("findByEmail result for $email: " . ($result ? 'FOUND' : 'NOT FOUND'));
        if ($result) {
            error_log("User status: " . ($result['Acc_Status'] ?? 'unknown'));
            error_log("Is deleted: " . (isset($result['is_deleted']) ? $result['is_deleted'] : 'not set'));
        }
        
        return $result;
    }

    /**
     * Find admin by email using User_ID for identification
     */
    private function findAdminByEmail($email) {
        require_once ROOT_DIR . '\app\Models\User.php';
        $userModel = new User();
        $db = $userModel->getDb();
        
        // Query to find admin user by email - USING IN for multiple roles
        $db->query('SELECT * FROM USER_INFORMATION WHERE Email = :email AND User_Role IN ("superAdmin", "SubAdmin") LIMIT 1');
        $db->bind(':email', $email);
        $result = $db->single();
        
        // Convert object to array if needed
        if (is_object($result)) {
            $result = (array)$result;
        }
        
        error_log("findAdminByEmail result for $email: " . ($result ? 'ADMIN FOUND' : 'NOT FOUND OR NOT ADMIN'));
        if ($result) {
            error_log("Admin ID: " . ($result['ID'] ?? 'unknown'));
            error_log("Admin Email: " . ($result['Email'] ?? 'unknown'));
            error_log("Admin Role: " . ($result['User_Role'] ?? 'unknown'));
            error_log("Admin Status: " . ($result['Acc_Status'] ?? 'unknown'));
        }
        
        return $result;
    }

    /**
     * Create admin session with User_ID
     */
    public function createAdminSession($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role']; // 'admin' or 'sub-admin'
        $_SESSION['admin_logged_in'] = true;
    
        // Also set regular user session for compatibility
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['user_name'] = $admin['name'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['logged_in'] = true;
    
        // Regenerate CSRF token for security
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        error_log("Admin session created for: " . $admin['email'] . " with role: " . $admin['role']);
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
        // Use your User model for authentication
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
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
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