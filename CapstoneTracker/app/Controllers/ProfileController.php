<?php

date_default_timezone_set('Asia/Manila');
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging - but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

try {
    // Correct file paths for your structure
    $configPath = __DIR__ . '/../../Database/config.php';
    $profileModelPath = __DIR__ . '/../Models/Profile.php';
    $emailSenderPath = __DIR__ . '/../Utils/EmailSender.php';
    
    // Check if files exist before requiring them
    if (!file_exists($configPath)) {
        throw new Exception('Database configuration file not found at: ' . $configPath);
    }
    if (!file_exists($profileModelPath)) {
        throw new Exception('Profile model file not found at: ' . $profileModelPath);
    }
    
    require_once $configPath;
    require_once $profileModelPath;
    
    // Only require EmailSender if needed
    if (file_exists($emailSenderPath)) {
        require_once $emailSenderPath;
    } else {
        error_log("EmailSender file not found at: " . $emailSenderPath);
    }

    class ProfileController {
        private $profileModel;
        private $emailSender;
        private $db;
        
        public function __construct($database) {
            $this->db = $database;
            $this->profileModel = new Profile($database);
            // Initialize email sender only if needed and available
            if (class_exists('EmailSender')) {
                $this->emailSender = new EmailSender();
                error_log("EmailSender initialized successfully");
            } else {
                error_log("EmailSender class not available");
            }
        }
        
        /**
         * Handle profile data request
         */
        public function getProfileData($userId) {
            try {
                error_log("=== GET PROFILE DATA DEBUG ===");
                error_log("Requested user ID: " . $userId);
                
                // Validate user ID
                if (empty($userId) || !is_numeric($userId)) {
                    error_log("Invalid user ID: " . $userId);
                    return [
                        'success' => false,
                        'message' => 'Invalid user ID'
                    ];
                }
                
                $profileData = $this->profileModel->getUserProfile($userId);
                
                error_log("Profile data result: " . ($profileData ? "FOUND" : "NOT FOUND"));
                
                if ($profileData) {
                    error_log("Profile data details:");
                    error_log("- Name: " . ($profileData['Full_Name'] ?? 'N/A'));
                    error_log("- Email: " . ($profileData['Email'] ?? 'N/A'));
                    error_log("- Profile Pic: " . ($profileData['Profile_Pic'] ?? 'N/A'));
                    
                    return [
                        'success' => true,
                        'data' => $profileData
                    ];
                } else {
                    error_log("Profile not found for user ID: " . $userId);
                    return [
                        'success' => false,
                        'message' => 'Profile not found for user ID: ' . $userId
                    ];
                }
                
            } catch (Exception $e) {
                error_log("Profile Controller Error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error retrieving profile data: ' . $e->getMessage()
                ];
            }
        }

        /**
         * Serve profile image as image data
         */
        public function serveProfileImage($userId) {
            try {
                error_log("=== SERVING PROFILE IMAGE ===");
                error_log("User ID: " . $userId);
                
                $imageBlob = $this->profileModel->getProfileImageBlob($userId);
                
                if ($imageBlob) {
                    // Detect MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_buffer($finfo, $imageBlob);
                    finfo_close($finfo);
                    
                    error_log("Image MIME type: " . $mimeType);
                    error_log("Image size: " . strlen($imageBlob) . " bytes");
                    
                    // Set appropriate headers
                    header('Content-Type: ' . $mimeType);
                    header('Cache-Control: max-age=3600'); // Cache for 1 hour
                    header('Content-Length: ' . strlen($imageBlob));
                    
                    // Output the image data
                    echo $imageBlob;
                    error_log("Image served successfully");
                } else {
                    error_log("No profile image found, serving default");
                    // Serve default image
                    $defaultImagePath = realpath(__DIR__ . '/../../resources/Images/profile.png');
                    if (file_exists($defaultImagePath)) {
                        header('Content-Type: image/png');
                        readfile($defaultImagePath);
                    } else {
                        error_log("Default image not found at: " . $defaultImagePath);
                        http_response_code(404);
                        echo "Default image not found";
                    }
                }
                
            } catch (Exception $e) {
                error_log("Error serving profile image: " . $e->getMessage());
                http_response_code(500);
                echo "Error serving image";
            }
            exit();
        }

        /**
         * Handle profile image upload
         */
        public function handleProfileImageUpload($userId, $files, $postData) {
            try {
                error_log("=== HANDLING PROFILE IMAGE UPLOAD ===");
                
                // Validate CSRF token
                if (!$this->validateCsrfToken($postData['csrf_token'] ?? '')) {
                    return [
                        'success' => false,
                        'message' => 'Invalid CSRF token'
                    ];
                }

                if (!isset($files['profile_image']) || $files['profile_image']['error'] !== UPLOAD_ERR_OK) {
                    return [
                        'success' => false,
                        'message' => 'No image file uploaded or upload error'
                    ];
                }

                $uploadedFile = $files['profile_image'];
                error_log("Uploaded file: " . $uploadedFile['name'] . ", Size: " . $uploadedFile['size']);
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
                finfo_close($finfo);
                
                error_log("Detected MIME type: " . $mimeType);
                
                if (!in_array($mimeType, $allowedTypes)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid file type. Please upload JPEG, PNG, or GIF images only.'
                    ];
                }

                // Validate file size (5MB max)
                if ($uploadedFile['size'] > 5 * 1024 * 1024) {
                    return [
                        'success' => false,
                        'message' => 'File size too large. Maximum size is 5MB.'
                    ];
                }

                // Read the file content
                $imageData = file_get_contents($uploadedFile['tmp_name']);
                
                if ($imageData === false) {
                    return [
                        'success' => false,
                        'message' => 'Failed to read uploaded file'
                    ];
                }

                error_log("Image data read successfully, size: " . strlen($imageData) . " bytes");
                
                // Update profile picture in database
                $this->db->query("UPDATE USER_INFORMATION SET Profile_Pic = :profile_pic WHERE ID = :user_id");
                
                // Use PDO::PARAM_LOB for BLOB data
                $this->db->bind(':profile_pic', $imageData, PDO::PARAM_LOB);
                $this->db->bind(':user_id', $userId);
                
                if ($this->db->execute()) {
                    error_log("Profile image updated successfully in database");
                    return [
                        'success' => true,
                        'message' => 'Profile image updated successfully',
                        'image_url' => '../../../app/Controllers/ProfileController.php?action=get_profile_image&user_id=' . $userId . '&t=' . time()
                    ];
                } else {
                    error_log("Failed to update profile image in database");
                    return [
                        'success' => false,
                        'message' => 'Failed to update profile image in database'
                    ];
                }

            } catch (Exception $e) {
                error_log("Profile Image Upload Error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error uploading image: ' . $e->getMessage()
                ];
            }
        }

        /**
         * Handle PIN request for password change
         */
        public function requestPin($userId, $postData) {
            try {
                error_log("Starting PIN request for user ID: " . $userId);
                
                // Validate CSRF token
                if (!$this->validateCsrfToken($postData['csrf_token'] ?? '')) {
                    error_log("CSRF token validation failed");
                    return [
                        'success' => false,
                        'message' => 'Invalid CSRF token'
                    ];
                }
                
                $currentPassword = $postData['current_password'] ?? '';
                
                if (empty($currentPassword)) {
                    error_log("Current password is empty");
                    return [
                        'success' => false,
                        'message' => 'Current password is required'
                    ];
                }
                
                // Verify current password
                error_log("Verifying current password for user: " . $userId);
                if (!$this->profileModel->verifyCurrentPassword($userId, $currentPassword)) {
                    error_log("Current password verification failed for user: " . $userId);
                    return [
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ];
                }
                
                error_log("Current password verified successfully");
                
                // Generate 6-digit PIN
                $pin = sprintf("%06d", mt_rand(1, 999999));
                $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                error_log("Generated PIN: " . $pin . " for user: " . $userId);
                
                // Store PIN in database
                error_log("Storing PIN in database");
                if ($this->profileModel->storePasswordChangePin($userId, $pin, $expiryTime)) {
                    error_log("PIN stored successfully");
                    
                    // Get user email and name
                    $userData = $this->profileModel->getUserProfile($userId);
                    
                    if ($userData) {
                        error_log("User data retrieved: " . $userData['Email']);
                        
                        if ($this->sendPinEmail($userData['Email'], $userData['Full_Name'], $pin)) {
                            error_log("PIN email sent successfully to: " . $userData['Email']);
                            return [
                                'success' => true,
                                'message' => 'PIN sent to your email'
                            ];
                        } else {
                            error_log("Failed to send PIN email to: " . $userData['Email']);
                            return [
                                'success' => false,
                                'message' => 'Failed to send PIN email. Please try again later.'
                            ];
                        }
                    } else {
                        error_log("Failed to get user profile data for user ID: " . $userId);
                        return [
                            'success' => false,
                            'message' => 'User data not found'
                        ];
                    }
                } else {
                    error_log("Failed to store PIN in database for user: " . $userId);
                    return [
                        'success' => false,
                        'message' => 'Failed to generate PIN'
                    ];
                }
                
            } catch (Exception $e) {
                error_log("PIN Request Controller Error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error processing PIN request: ' . $e->getMessage()
                ];
            }
        }
        
        /**
         * Verify PIN and change password
         */
        public function verifyPinAndChangePassword($userId, $postData) {
            try {
                error_log("Starting PIN verification for user ID: " . $userId);
                
                // Validate CSRF token
                if (!$this->validateCsrfToken($postData['csrf_token'] ?? '')) {
                    return [
                        'success' => false,
                        'message' => 'Invalid CSRF token'
                    ];
                }
                
                $pinCode = $postData['pin_code'] ?? '';
                $currentPassword = $postData['current_password'] ?? '';
                $newPassword = $postData['new_password'] ?? '';
                $confirmPassword = $postData['confirm_password'] ?? '';
                
                // Basic validation
                if (empty($pinCode) || strlen($pinCode) !== 6) {
                    return [
                        'success' => false,
                        'message' => 'Invalid PIN code'
                    ];
                }
                
                if (empty($newPassword) || strlen($newPassword) < 8) {
                    return [
                        'success' => false,
                        'message' => 'New password must be at least 8 characters long'
                    ];
                }
                
                if ($newPassword !== $confirmPassword) {
                    return [
                        'success' => false,
                        'message' => 'New passwords do not match'
                    ];
                }
                
                // Verify current password again
                if (!$this->profileModel->verifyCurrentPassword($userId, $currentPassword)) {
                    return [
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ];
                }
                
                // Verify PIN
                if (!$this->profileModel->verifyPasswordChangePin($userId, $pinCode)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid or expired PIN'
                    ];
                }
                
                // Change password
                if ($this->profileModel->changePassword($userId, $currentPassword, $newPassword)) {
                    // Clear used PIN
                    $this->profileModel->clearPasswordChangePin($userId);
                    
                    return [
                        'success' => true,
                        'message' => 'Password changed successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to change password'
                    ];
                }
                
            } catch (Exception $e) {
                error_log("PIN Verification Controller Error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error changing password'
                ];
            }
        }
        
        /**
         * Send PIN email
         */
        private function sendPinEmail($email, $name, $pin) {
            if (!$this->emailSender) {
                error_log("Email sender not available in sendPinEmail");
                return false;
            }
            
            if (!$this->emailSender->mailerAvailable) {
                error_log("Email sender reports SMTP not available");
                return false;
            }
            
            $subject = "Password Change Verification PIN - Compendium System";
            $body = $this->getPinEmailBody($name, $pin);
            
            error_log("Attempting to send PIN email to: " . $email);
            
            try {
                $result = $this->emailSender->sendHtmlEmail($email, $name, $subject, $body);
                error_log("Email send result: " . ($result ? 'SUCCESS' : 'FAILED'));
                return $result;
            } catch (Exception $e) {
                error_log("Exception in sendPinEmail: " . $e->getMessage());
                return false;
            }
        }
        
        /**
         * Get PIN email body
         */
        private function getPinEmailBody($name, $pin) {
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
                    .pin-box { background: #e74c3c; color: white; padding: 20px; border-radius: 5px; font-weight: bold; text-align: center; margin: 20px 0; font-size: 32px; letter-spacing: 5px; }
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
                        <h2>Password Change Verification</h2>
                        <p>Dear {$name},</p>
                        <p>You have requested to change your password. Please use the following verification PIN to complete the process:</p>
                        
                        <div class='pin-box'>
                            {$pin}
                        </div>
                        
                        <div class='info-box'>
                            <p><strong>Important Information:</strong></p>
                            <ul>
                                <li>This PIN will expire in 10 minutes</li>
                                <li>Do not share this PIN with anyone</li>
                                <li>If you didn't request this change, please ignore this email and contact administrator</li>
                            </ul>
                        </div>
                        
                        <p>Enter this PIN in the verification window to complete your password change.</p>
                        
                        <p>Best regards,<br>Compendium System Team</p>
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
         * Handle profile field updates (course/department)
         */
        public function updateProfile($userId, $postData) {
            try {
                error_log("=== UPDATE PROFILE ===");
                error_log("User ID: " . $userId);
                error_log("Post data: " . print_r($postData, true));
                
                // Validate CSRF token
                if (!$this->validateCsrfToken($postData['csrf_token'] ?? '')) {
                    return [
                        'success' => false,
                        'message' => 'Invalid CSRF token'
                    ];
                }
                
                $field = $postData['field'] ?? '';
                $value = $postData['value'] ?? '';
                
                error_log("Field: " . $field . ", Value: " . $value);
                
                // Validate field
                if (!in_array($field, ['course', 'department'])) {
                    return [
                        'success' => false,
                        'message' => 'Invalid field'
                    ];
                }
                
                // Get user data to check login method
                $userData = $this->profileModel->getUserProfile($userId);
                if (!$userData) {
                    return [
                        'success' => false,
                        'message' => 'User not found'
                    ];
                }
                
                // Check if user logged in with Google
                if (($userData['Login_Method'] ?? 'manual') !== 'google') {
                    return [
                        'success' => false,
                        'message' => 'Only Google users can edit this field'
                    ];
                }
                
                // Update the profile
                require_once '../Models/User.php';
                $userModel = new User($this->db);
                
                if ($field === 'course') {
                    $result = $userModel->updateUserProfile($userId, $value, null);
                } elseif ($field === 'department') {
                    $result = $userModel->updateUserProfile($userId, null, $value);
                }
                
                if ($result) {
                    error_log("Profile updated successfully");
                    return [
                        'success' => true,
                        'message' => ucfirst($field) . ' updated successfully'
                    ];
                } else {
                    error_log("Failed to update profile");
                    return [
                        'success' => false,
                        'message' => 'Failed to update ' . $field
                    ];
                }
                
            } catch (Exception $e) {
                error_log("Profile update error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error updating profile: ' . $e->getMessage()
                ];
            }
        }
        
        /**
         * Validate CSRF token
         */
        private function validateCsrfToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }

    // Handle AJAX requests
    if (isset($_GET['action'])) {
        $db = new Database();
        $controller = new ProfileController($db);
        
        $action = $_GET['action'];
        $userId = $_SESSION['user_id'];
        
        error_log("Processing action: " . $action . " for user ID: " . $userId);
        
        switch ($action) {
            case 'get_profile':
                $response = $controller->getProfileData($userId);
                echo json_encode($response);
                break;
                
            case 'request_pin':
                $response = $controller->requestPin($userId, $_POST);
                echo json_encode($response);
                break;
                
            case 'verify_pin_change_password':
                $response = $controller->verifyPinAndChangePassword($userId, $_POST);
                echo json_encode($response);
                break;
                
            case 'get_profile_image':
                $controller->serveProfileImage($userId);
                break;
                
            case 'upload_profile_image':
                $response = $controller->handleProfileImageUpload($userId, $_FILES, $_POST);
                echo json_encode($response);
                break;
                
            case 'update_profile':
                $response = $controller->updateProfile($userId, $_POST);
                echo json_encode($response);
                break;
                
            default:
                $response = [
                    'success' => false,
                    'message' => 'Invalid action'
                ];
                echo json_encode($response);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No action specified'
        ]);
    }

} catch (Exception $e) {
    error_log("Profile Controller Main Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
exit();
?>