<?php
// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../../../Database/config.php';
require_once '../../../app/Models/Model.php';
require_once '../../../app/Controllers/Controller.php';
require_once '../../../app/Controllers/AuthController.php';

$errorMessage = '';
$successMessage = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$userEmail = '';

// Validate token and check if it's valid
if (!empty($token)) {
    $authController = new AuthController();
    $tokenData = $authController->validateResetToken($token);
    
    if ($tokenData) {
        $validToken = true;
        // Get user email for display
        require_once '../../../app/Models/User.php';
        $userModel = new User();
        $user = $userModel->getUserById($tokenData['user_id']);
        if ($user) {
            $userEmail = $user->Email ?? '';
        }
    } else {
        $errorMessage = 'Invalid or expired reset link. Please request a new password reset.';
    }
} else {
    $errorMessage = 'No reset token provided.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resetPassword') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'Please fill in all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } elseif (strlen($newPassword) < 8) {
        $errorMessage = 'Password must be at least 8 characters long.';
    } else {
        $authController = new AuthController();
        $result = $authController->processPasswordReset($token, $newPassword);
        
        if ($result['success']) {
            $successMessage = $result['message'];
            $validToken = false; // Token is now used
        } else {
            $errorMessage = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
    <title>Reset Password | Compendium System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../../resources/css/User/indexLogin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
        }
        .strength-weak { background-color: #dc3545; width: 25%; }
        .strength-fair { background-color: #fd7e14; width: 50%; }
        .strength-good { background-color: #ffc107; width: 75%; }
        .strength-strong { background-color: #198754; width: 100%; }
    </style>
</head>
<body>
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="row w-100 justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <img src="../../../resources/Images/ThesisCompLogo.png" alt="Compendium System Logo" class="mb-3" style="width: 80px;">
                            <h3 class="card-title">Reset Your Password</h3>
                            <?php if ($userEmail): ?>
                                <p class="text-muted">For account: <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>
                            <?php endif; ?>
                        </div>

                        <!-- Error Message -->
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($errorMessage); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Success Message -->
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($successMessage); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="indexLogin.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Return to Login
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Password Reset Form -->
                        <?php if ($validToken && empty($successMessage)): ?>
                            <form id="resetPasswordForm" method="POST" action="">
                                <input type="hidden" name="action" value="resetPassword">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="newPassword" name="new_password" 
                                               placeholder="Enter new password" required minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2" id="passwordStrength"></div>
                                    <div class="form-text">
                                        Password must be at least 8 characters long
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                                               placeholder="Confirm new password" required minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text" id="passwordMatch"></div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2" id="resetButton">
                                    <i class="fas fa-key me-2"></i>Reset Password
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <a href="indexLogin.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                                </a>
                            </div>
                        <?php elseif (empty($successMessage)): ?>
                            <!-- Invalid Token Message -->
                            <div class="text-center">
                                <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
                                <p class="text-muted">This password reset link is invalid or has expired.</p>
                                <a href="indexLogin.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Return to Login
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-white mb-0">&copy; 2025 University of Southeastern Philippines | Compendium System</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('newPassword');
            const icon = this.querySelector('i');
            togglePasswordVisibility(passwordInput, icon);
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirmPassword');
            const icon = this.querySelector('i');
            togglePasswordVisibility(passwordInput, icon);
        });

        function togglePasswordVisibility(input, icon) {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        // Password strength indicator
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strength = checkPasswordStrength(password);
            
            strengthBar.className = 'password-strength';
            if (password.length > 0) {
                strengthBar.classList.add('strength-' + strength.level);
            }
        });

        // Password match indicator
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchText.innerHTML = '';
                matchText.className = 'form-text';
            } else if (newPassword === confirmPassword) {
                matchText.innerHTML = '<i class="fas fa-check text-success me-1"></i>Passwords match';
                matchText.className = 'form-text text-success';
            } else {
                matchText.innerHTML = '<i class="fas fa-times text-danger me-1"></i>Passwords do not match';
                matchText.className = 'form-text text-danger';
            }
        });

        // Password strength checker
        function checkPasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score++;
            if (password.match(/[a-z]/)) score++;
            if (password.match(/[A-Z]/)) score++;
            if (password.match(/[0-9]/)) score++;
            if (password.match(/[^a-zA-Z0-9]/)) score++;
            
            const levels = [
                { level: 'weak', text: 'Weak' },
                { level: 'weak', text: 'Weak' },
                { level: 'fair', text: 'Fair' },
                { level: 'good', text: 'Good' },
                { level: 'strong', text: 'Strong' },
                { level: 'strong', text: 'Very Strong' }
            ];
            
            return levels[Math.min(score, levels.length - 1)];
        }

        // Form submission handling
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const resetButton = document.getElementById('resetButton');
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire('Error', 'Passwords do not match. Please check your entries.', 'error');
                return;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                Swal.fire('Error', 'Password must be at least 8 characters long.', 'error');
                return;
            }
            
            // Show loading state
            resetButton.disabled = true;
            resetButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting...';
        });
    </script>
</body>
</html>
