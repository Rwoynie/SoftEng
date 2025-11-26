<?php


// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  
}


if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include the configuration file
require_once '..\..\..\Database\config.php';

// Initialize database if needed
$setupError = null;
try {
    require_once '..\..\..\app\Controllers\SetupController.php';
    $setupController = new SetupController();
    $isDatabaseReady = $setupController->initializeDatabase();
    
    if (!$isDatabaseReady) {
        $setupError = $setupController->getSetupError();
        // Don't redirect, just show error on the page
    }
} catch (Exception $e) {
    $setupError = "Setup error: " . $e->getMessage();
    error_log("Database setup error: " . $e->getMessage());
}

require_once '..\..\..\app\Models\Model.php';
require_once '..\..\..\app\Controllers\Controller.php';

// Store error message for SweetAlert
$errorMessage = '';
$errorModal = '';
$showModal = false;
$successMessage = '';
$adminErrorMessage = '';

if (isset($_SESSION['error_message'])) {
  $errorMessage = $_SESSION['error_message'];
  $errorModal = $_SESSION['error_modal'] ?? '';
  $showModal = true;
  unset($_SESSION['error_message']);
  unset($_SESSION['error_modal']);
}

if (isset($_SESSION['success_message'])) {
  $successMessage = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
}

if (isset($_SESSION['admin_error_message'])) {
  $adminErrorMessage = $_SESSION['admin_error_message'];
  unset($_SESSION['admin_error_message']);
}



if ($setupError) {
  echo '<div class="alert alert-danger position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">';
  echo 'Setup Error: ' . htmlspecialchars($setupError);
  echo '</div>';
  
  // Also log the detailed error
  error_log("Database setup error details: " . $setupError);
}
?> 

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
    <title>User | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../../resources/css/User/indexLogin.css">
    <script type="text/javascript" src="../../../resources/js/User/indexLogin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
    

    <script>
        const errorMessage = "<?php echo addslashes($errorMessage); ?>";
        const errorModal = "<?php echo addslashes($errorModal); ?>";
        const showModal = <?php echo $showModal ? 'true' : 'false'; ?>;
        const successMessage = "<?php echo addslashes($successMessage); ?>";
        const adminErrorMessage = "<?php echo addslashes($adminErrorMessage); ?>";
    </script>
</head> 
<body>

    
<div class="container" onclick="onclick">
  <div class="top"></div>
  <div class="bottom"></div>
  <div class="center">
  <div class="container1">
        <div class="container2">
            <img class="sysLogo" src="../../../resources/Images/ThesisCompLogo.png" alt="Compendium System Logo">
            <h1>Compendium System</h1>
            <p class="tagline">A digital library for USeP student research.</p>
            <div class="d-flex justify-content-center gap-2" style="margin-top: 30px;">
                <button id="researcherBtn" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-graduate me-2"></i>
                    Student
                </button>   
                <span class="align-self-center text-muted">|</span>
                <button id="facultyBtn" class="btn btn-outline-danger btn-lg">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Faculty
                </button>
            </div>
            <div class="text-center mt-3">
                <a href="../../../app/Views/public/home.php" class="text-decoration-none link-secondary" >View as guest</a>
            </div>
        </div>  
    </div>
  </div>
</div>

<!-- LOGIN MODAL (ADDED) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title" id="modalTitle">Login</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <form method="POST" action="../../Controllers/AuthController.php" enctype="multipart/form-data">
          <input type="hidden" name="action" value="login">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <input type="hidden" id="roleField" name="role" value="student">
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" id="username" name="email" class="form-control" placeholder="Enter USeP email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" aria-label="Show password">
                <i class="far fa-eye"></i>
              </button>
            </div>

            <div class="text-center mt-5">
            <a href="#" id="forgotPasswordLink" class="text-decoration-none">Forgot password?</a>
          </div>
          </div>
          <button type="submit" class="btn btn-success w-100 mb-2">Login</button>
          

          <hr>
          <div class="d-flex justify-content-center mb-2">
            <div id="googleButton"></div>
          </div>
          
          <div class="d-flex justify-content-center mb-2">
              <button id="googleModalBtn" type="button" class="btn btn-outline-danger w-100">
                  <i class="fab fa-google me-2"></i> Sign in with USeP Email
              </button>
          </div>
          
          
          <div class="text-center">
            <p class="accountCreate">Not yet registered?<a href="#" id="openRegisterLink" class="btn btn-link">Create an account</a></p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="fab-icon save-fab" id="saveAdminChangesBtn" title="Save Changes">
        <i class="fas fa-save"></i>
    </div>
</div>

<!-- STUDENT REGISTRATION MODAL -->
<div class="modal fade" id="studentRegisterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title">Student Registration</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
      <form id="studentRegisterForm" method="POST" action="../../Controllers/RegistrationController.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="student_register">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="regFirstName" class="form-label">First Name</label>
              <input type="text" id="regFirstName" name="firstName" class="form-control" placeholder="Juan" required>
            </div>
            <div class="col-md-4">
              <label for="regMiddleName" class="form-label">Middle Name</label>
              <input type="text" id="regMiddleName" name="middleName" class="form-control" placeholder="Santos">
            </div>
            <div class="col-md-4">
              <label for="regLastName" class="form-label">Last Name</label>
              <input type="text" id="regLastName" name="lastName" class="form-control" placeholder="Dela Cruz" required>
            </div>
            <div class="col-12">
              <label for="regExtension" class="form-label">Name Extension (Optional)</label>
              <input type="text" id="regExtension" name="extension" class="form-control" placeholder="Jr., III, etc.">
            </div>
            
            
            
            <div class="col-12">
              <label for="regCourse" class="form-label">Course</label>
              <select id="regCourse" name="course" class="form-select" required>
                <option value="" selected disabled>Select your program</option>
                <option>Bachelor of Technical-Vocational Teacher Education</option>
                <option>Bachelor of Special Needs Education</option>
                <option>Bachelor of Early Childhood Education</option>
                <option>Bachelor of Secondary Education</option>
                <option>Bachelor of Science in Information Technology</option>
                <option>Bachelor of Elementary Education</option>
                <option>Bachelor of Science in Agricultural and Biosystems Engineering</option>
              </select>
            </div>
            <div class="col-12">
              <label for="regEmail" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="regEmail" name="email" class="form-control" placeholder="your.name@usep.edu.ph" required>
              </div>
              <small class="text-muted">Use your university email (@usep.edu.ph)</small>
            </div>
            <div class="col-12">
              <label for="regPassword" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" id="regPassword" name="password" class="form-control" required
                       pattern="^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$"
                       oninput="validatePassword(this)">
                <button class="btn btn-outline-secondary" type="button" id="regTogglePassword" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              <small id="passwordHelp" class="form-text text-muted">
                Password must be at least 8 characters long, include 1 uppercase letter and 1 number.
              </small>
              <div id="passwordError" class="invalid-feedback">
                Please enter a valid password (min 8 chars, 1 uppercase, 1 number)
              </div>
            </div>
            <div class="col-12">
              <label for="regConfirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input type="password" id="regConfirmPassword" name="confirmPassword" class="form-control" required
                       oninput="checkPasswordMatch()">
                <button class="btn btn-outline-secondary" type="button" id="regToggleConfirm" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              <div id="confirmPasswordError" class="invalid-feedback">
                Passwords do not match
              </div>
            </div>
            <div class="col-12">
              <label for="regProfilePic" class="form-label">Profile picture</label>
              <input type="file" id="regProfilePic" name="profilePic" class="form-control" accept="image/*">
              <small class="text-muted">Max 5MB. JPG/PNG preferred.</small>
            </div>
            <div class="col-12 d-grid gap-2">
              <button type="submit" class="btn btn-primary">Create account</button>
              <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                <i class="fas fa-arrow-left me-2"></i> Back to login
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- FACULTY REGISTRATION MODAL -->
<div class="modal fade" id="facultyRegisterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title">Faculty Registration</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
      <form id="facultyRegisterForm" method="POST" action="../../Controllers/RegistrationController.php" enctype="multipart/form-data">
         
          <input type="hidden" name="action" value="faculty_register">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <div class="row g-3">
            <!-- Separate Name Fields -->
            <div class="col-md-4">
              <label for="facFirstName" class="form-label">First Name</label>
              <input type="text" id="facFirstName" name="firstName" class="form-control" placeholder="Maria" required>
            </div>
            <div class="col-md-4">
              <label for="facMiddleName" class="form-label">Middle Name</label>
              <input type="text" id="facMiddleName" name="middleName" class="form-control" placeholder="Santos">
            </div>
            <div class="col-md-4">
              <label for="facLastName" class="form-label">Last Name</label>
              <input type="text" id="facLastName" name="lastName" class="form-control" placeholder="Reyes" required>
            </div>
            <div class="col-12">
              <label for="facExtension" class="form-label">Name Extension (Optional)</label>
              <input type="text" id="facExtension" name="extension" class="form-control" placeholder="Jr., III, etc.">
            </div>
            
            
            <div class="col-12">
              <label for="facDepartment" class="form-label">Department</label>
              <select id="facDepartment" name="department" class="form-select" required>
                <option value="" selected disabled>Select department</option>
                <option>CTET</option>
                <option>COE</option>
              </select>
            </div>
            
            <div class="col-12">
              <label for="facEmail" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="facEmail" name="email" class="form-control" placeholder="your.name@usep.edu.ph" required>
              </div>
              <small class="text-muted">Use your university email (@usep.edu.ph)</small>
            </div>
            <div class="col-12">
              <label for="facPassword" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" id="facPassword" name="password" class="form-control" required
                       pattern="^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$"
                       oninput="validatePassword(this)">
                <button class="btn btn-outline-secondary" type="button" id="facTogglePassword" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              <small id="facPasswordHelp" class="form-text text-muted">
                Password must be at least 8 characters long, include 1 uppercase letter and 1 number.
              </small>
              <div id="facPasswordError" class="invalid-feedback">
                Please enter a valid password (min 8 chars, 1 uppercase, 1 number)
              </div>
            </div>
            <div class="col-12">
              <label for="facConfirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input type="password" id="facConfirmPassword" name="confirmPassword" class="form-control" required
                       oninput="checkPasswordMatch()">
                <button class="btn btn-outline-secondary" type="button" id="facToggleConfirm" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              <div id="facConfirmPasswordError" class="invalid-feedback">
                Passwords do not match
              </div>
            </div>
            <div class="col-12">
              <label for="facProfilePic" class="form-label">Profile picture (Optional)</label>
              <input type="file" id="facProfilePic" name="profilePic" class="form-control" accept="image/*">
              <small class="text-muted">Max 5MB. JPG/PNG preferred.</small>
            </div>
            <div class="col-12 d-grid gap-2">
              <button type="submit" class="btn btn-primary">Create account</button>
              <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                <i class="fas fa-arrow-left me-2"></i> Back to login
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

    <footer class="login-footer">
        <p>&copy; 2025 University of Southeastern Philippines | Thesis Repository</p>
    </footer>

    <!-- ADMIN LOGIN MODAL (HIDDEN - ACCESSED VIA CTRL+H) -->
    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
          <div class="modal-header text-center w-100 d-block position-relative">
            <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
            <h5 class="modal-title">
                <i class="fas fa-shield-alt admin-icon"></i>
                Admin Login
            </h5>
            <button type="button" class="btn btn-link text-white position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
          </div>
          <div class="modal-body">
            <div class="admin-warning mb-3">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Restricted Access:</strong> Authorized personnel only.
            </div>
            
            <!-- Regular Admin Login Form -->
            <form id="adminLoginForm" method="POST" action="../../Controllers/AdminController.php">
              <input type="hidden" name="action" value="login">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
              <div class="mb-3">
                <label for="adminUsername" class="form-label">Admin ID</label>
                <input type="text" id="adminUsername" name="admin_username" class="form-control" placeholder="Enter admin ID" required>
              </div>
              <div class="mb-3">
                <label for="adminPassword" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" id="adminPassword" name="admin_password" class="form-control" placeholder="Enter admin password" required>
                  <button class="btn btn-outline-secondary" type="button" id="adminTogglePassword" aria-label="Show password">
                    <i class="far fa-eye"></i>
                  </button>
                </div>
              </div>
              <div class="text-center mt-5">
                <a href="#" id="forgotPasswordLinkAdmin" class="text-decoration-none">Forgot password?</a>
              </div>
              <br>
              <button type="submit" class="btn btn-danger w-100 mb-2">
                <i class="fas fa-sign-in-alt me-2"></i>Admin Login
              </button>
            </form>
            
            <!-- Admin Google Sign-In Section -->
            <div class="admin-google-section mt-4 pt-3 border-top">
              <div class="text-center mb-3">
                <small class="text-muted">Or sign in with Google</small>
              </div>
              
              <div class="d-flex justify-content-center mb-2">
                <button id="adminGoogleBtn" type="button" class="btn btn-outline-dark w-100">
                    <i class="fab fa-google me-2"></i> Sign in with Admin Google Account
                </button>
              </div>
              
              <div class="admin-google-note text-center">
                <small class="text-muted">
                  <i class="fas fa-info-circle me-1"></i>
                  Must use authorized admin Google account
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
<!-- FORGOT PASSWORD MODAL -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title">Reset Password</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-4">Enter your email address and we'll send you a verification code to reset your password.</p>
        <form id="forgotPasswordForm">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <div class="mb-3">
            <label for="forgotEmail" class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              <input type="email" id="forgotEmail" name="email" class="form-control" placeholder="your.email@usep.edu.ph" required>
            </div>
            <small class="text-muted">Use your registered USeP email address</small>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" id="sendCodeBtn">
              <i class="fas fa-paper-plane me-2"></i>Send Verification Code
            </button>
            <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
              <i class="fas fa-arrow-left me-2"></i> Back to login
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

    <!-- PASSWORD RESET VERIFICATION MODAL -->
<!-- PASSWORD RESET VERIFICATION MODAL -->
<div class="modal fade" id="passwordResetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title">Set New Password</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <div id="verificationStep1">
          <p class="text-muted mb-4">We've sent a 6-digit verification code to your email. Enter it below and set your new password.</p>
          <form id="verificationForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" id="resetEmail" name="email">
            
            <div class="mb-3">
              <label for="verificationCode" class="form-label">Verification Code</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                <input type="text" id="verificationCode" name="verification_code" class="form-control text-center" 
                       placeholder="000000" maxlength="6" required pattern="[0-9]{6}">
              </div>
              <small class="text-muted">Enter the 6-digit code sent to your email</small>
            </div>

            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <div class="input-group">
                <input type="password" id="newPassword" name="new_password" class="form-control" 
                       placeholder="Enter your new password" required minlength="8">
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
                <input type="password" id="confirmPassword" name="confirm_password" class="form-control" 
                       placeholder="Confirm your new password" required minlength="8">
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                  <i class="far fa-eye"></i>
                </button>
              </div>
              <div class="form-text" id="passwordMatch"></div>
            </div>
            
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary" id="verifyCodeBtn">
                <i class="fas fa-check-circle me-2"></i>Set New Password
              </button>
              <button type="button" class="btn btn-link" id="resendCodeBtn">
                <i class="fas fa-redo me-2"></i>Resend Code
              </button>
            </div>
          </form>
        </div>
        
        <div id="verificationStep2" style="display: none;">
          <div class="text-center">
            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
            <h5 class="text-success">Password Reset Successful!</h5>
            <p class="text-muted">Your password has been reset successfully. You can now login with your new password.</p>
            
            <div class="alert alert-success mt-3">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Success!</strong> You can now use your new password to login.
            </div>
            
            <button type="button" class="btn btn-outline-danger w-100" data-bs-dismiss="modal">
              <i class="fas fa-arrow-left me-2"></i>Return to Login
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
    
</body>

    <script>
        // Password validation functions
        function validatePassword(input) {
            const password = input.value;
            const formId = input.closest('form').id;
            const isStudentForm = formId === 'studentRegisterForm';
            
            const passwordHelp = document.getElementById(isStudentForm ? 'passwordHelp' : 'facPasswordHelp');
            const passwordError = document.getElementById(isStudentForm ? 'passwordError' : 'facPasswordError');
            
            // Check if password meets requirements
            const hasMinLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasNumber = /\d/.test(password);
            
            // Toggle error state
            if (!hasMinLength || !hasUppercase || !hasNumber) {
                input.classList.add('is-invalid');
                if (passwordError) passwordError.style.display = 'block';
            } else {
                input.classList.remove('is-invalid');
                if (passwordError) passwordError.style.display = 'none';
            }
            
            // Update password help text with current status
            const status = [];
            if (!hasMinLength) status.push('at least 8 characters');
            if (!hasUppercase) status.push('1 uppercase letter');
            if (!hasNumber) status.push('1 number');
            
            if (passwordHelp) {
                if (status.length > 0) {
                    passwordHelp.innerHTML = `Password needs: ${status.join(', ')}.`;
                    passwordHelp.className = 'form-text text-danger';
                } else {
                    passwordHelp.innerHTML = 'Password meets all requirements.';
                    passwordHelp.className = 'form-text text-success';
                }
            }
            
            // Trigger password match check if confirm password is not empty
            const confirmPasswordId = isStudentForm ? 'regConfirmPassword' : 'facConfirmPassword';
            if (document.getElementById(confirmPasswordId).value) {
                checkPasswordMatch();
            }
        }
        
        function checkPasswordMatch() {
            const formId = event ? event.target.closest('form').id : 
                         (document.activeElement ? document.activeElement.closest('form').id : 'studentRegisterForm');
            const isStudentForm = formId === 'studentRegisterForm';
            
            const passwordId = isStudentForm ? 'regPassword' : 'facPassword';
            const confirmPasswordId = isStudentForm ? 'regConfirmPassword' : 'facConfirmPassword';
            const confirmErrorId = isStudentForm ? 'confirmPasswordError' : 'facConfirmPasswordError';
            
            const password = document.getElementById(passwordId);
            const confirmPassword = document.getElementById(confirmPasswordId);
            const confirmError = document.getElementById(confirmErrorId);
            
            if (!password || !confirmPassword) return;
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                if (confirmError) confirmError.style.display = 'block';
                return false;
            } else {
                confirmPassword.classList.remove('is-invalid');
                if (confirmError) confirmError.style.display = 'none';
                return true;
            }
        }
        
        // Form submission validation
        const forms = document.querySelectorAll('#studentRegisterForm, #facultyRegisterForm');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const isStudentForm = form.id === 'studentRegisterForm';
                const passwordId = isStudentForm ? 'regPassword' : 'facPassword';
                
                const password = document.getElementById(passwordId).value;
                const hasMinLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasNumber = /\d/.test(password);
                
                if (!hasMinLength || !hasUppercase || !hasNumber || !checkPasswordMatch()) {
                    e.preventDefault();
                    
                    // Validate password
                    const passwordInput = document.getElementById(passwordId);
                    if (passwordInput) validatePassword(passwordInput);
                    
                    // Check password match
                    checkPasswordMatch();
                    
                    // Scroll to first error
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
        
        // Debug output
        console.log('PHP errorMessage:', errorMessage);
    </script>

</html>
