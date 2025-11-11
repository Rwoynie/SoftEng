<?php


// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    <script src="https://apis.google.com/js/platform.js?onload=onGoogleLoad" async defer></script>
    <meta name="google-signin-client_id" content="650560808203-4me7u51pnnkiggjd7sp935egrj2p4vrd.apps.googleusercontent.com">
   
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
            <br>
            <br>
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
        <form id="loginForm" method="POST" action="../../Controllers/AuthController.php" enctype="multipart/form-data">
          <input type="hidden" name="action" value="login">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
          <input type="hidden" id="roleField" name="role">
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
            <div id="passwordError" class="password-error-message" style="display: none;"></div>
          </div>
          <div class="mb-2 text-end">
            <a href="#" id="forgotPasswordLink" class="text-decoration-none" style="color: #28a745; font-size: 0.9rem;">Forgot Password?</a>
          </div>
          <button type="submit" class="btn btn-success w-100 mb-2">Login</button>

          <div class="d-flex justify-content-center mb-2" style="display: none !important;">
              <div id="googleButton"></div>
          </div>
          <div id="g_id_onload"
     data-client_id="YOUR_GOOGLE_CLIENT_ID"
     data-context="signin"
     data-ux_mode="popup"
     data-callback="handleCredentialResponse"
     data-auto_prompt="false">
</div>

<div class="g_id_signin"
     data-type="standard"
     data-shape="rectangular"
     data-theme="outline"
     data-text="signin_with"
     data-size="large"
     data-logo_alignment="left">
</div>
          <div class="g-signin2" data-onsuccess="onSignIn"></div>
          
          <div class="text-center">
  <a>Not yet registered?</a>
  <a href="#" id="createAccountLink" class="btn btn-link"> Create an account</a>
</div>


        </form>
      </div>
    </div>
  </div>

  <div class="fab-icon save-fab" id="saveAdminChangesBtn" title="Save Changes">
        <i class="fas fa-save"></i>
    </div>
</div>

<!-- FORGOT PASSWORD MODAL -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/Images/ThesisCompLogo.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title">Forgot Password</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <div id="forgotPasswordStep1">
          <p class="text-muted mb-4">Enter your email address and we'll send you instructions to reset your password.</p>
          <form id="forgotPasswordForm" method="POST" action="../../Controllers/AuthController.php">
            <input type="hidden" name="action" value="forgot_password">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" id="forgotPasswordRole" name="role">
            <div class="mb-3">
              <label for="forgotPasswordEmail" class="form-label">Email Address</label>
              <input type="email" id="forgotPasswordEmail" name="email" class="form-control" placeholder="Enter your USeP email" required>
              <div id="forgotPasswordError" class="text-danger mt-2" style="display: none;"></div>
              <div id="forgotPasswordSuccess" class="text-success mt-2" style="display: none;"></div>
            </div>
            <button type="submit" class="btn btn-success w-100 mb-2">
              <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
            </button>
            <div class="text-center">
              <a href="#" id="backToLoginLink" class="text-decoration-none">Back to Login</a>
            </div>
          </form>
        </div>
        <div id="forgotPasswordStep2" style="display: none;">
          <div class="text-center mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
            <h5 class="mt-3">Reset Code Sent!</h5>
            <p class="text-muted">Please check your email for the reset code. Enter it below to reset your password.</p>
          </div>
          <form id="resetPasswordForm" method="POST" action="../../Controllers/AuthController.php">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <input type="hidden" id="resetPasswordEmail" name="email">
            <input type="hidden" id="resetPasswordRole" name="role">
            <div class="mb-3">
              <label for="resetCode" class="form-label">Reset Code</label>
              <input type="text" id="resetCode" name="reset_code" class="form-control" placeholder="Enter 6-digit code" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric">
            </div>
            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <div class="input-group">
                <input type="password" id="newPassword" name="new_password" class="form-control" placeholder="Enter new password" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleNewPasswordBtn" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
              <div class="input-group">
                <input type="password" id="confirmNewPassword" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmNewPasswordBtn" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>
            <div id="resetPasswordError" class="text-danger mb-2" style="display: none;"></div>
            <button type="submit" class="btn btn-success w-100 mb-2">
              <i class="fas fa-key me-2"></i>Reset Password
            </button>
            <div class="text-center">
              <a href="#" id="resendCodeLink" class="text-decoration-none">Resend Code</a>
            </div>
          </form>
        </div>
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
              <br>
              <button type="submit" class="btn btn-danger w-100 mb-2">
                <i class="fas fa-sign-in-alt me-2"></i>Admin Login
              </button>
              
              
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <script>
  // Element references
  const studentBtn = document.getElementById('researcherBtn'); // your Student button
  const facultyBtn = document.getElementById('facultyBtn');    // your Faculty button
  const loginModal = document.getElementById('loginModal');
  const roleField = document.getElementById('roleField');
  const modalTitle = document.getElementById('modalTitle');
  const createLink = document.getElementById('createAccountLink');

  // Registration pages
  const studentRegisterPage = '../../../app/Views/User/student_register.php';
  const facultyRegisterPage = '../../../app/Views/User/faculty_register.php';
  

  // Function to open modal and set correct role
  function openLoginModal(role) {
    if (!roleField) return;

    // Set role in hidden input for AuthController
    roleField.value = role;

    // Change modal title visually
    modalTitle.textContent = role === 'faculty' ? 'Faculty Login' : 'Student Login';

    // Set correct registration link
    createLink.href = role === 'faculty' ? facultyRegisterPage : studentRegisterPage;

    // Clear any previous error state
    const passwordField = document.getElementById('password');
    const passwordError = document.getElementById('passwordError');
    if (passwordField) {
      passwordField.classList.remove('password-error');
      passwordField.value = '';
    }
    if (passwordError) {
      passwordError.style.display = 'none';
      passwordError.textContent = '';
    }

    // Open the login modal
    const modal = new bootstrap.Modal(loginModal);
    modal.show();
  }

  // Student button opens modal as "Student"
  studentBtn?.addEventListener('click', function (e) {
    e.preventDefault();
    openLoginModal('student');
  });

  // Faculty button opens modal as "Faculty"
  facultyBtn?.addEventListener('click', function (e) {
    e.preventDefault();
    openLoginModal('faculty');
  });

  // Forgot Password Link Handler
  const forgotPasswordLink = document.getElementById('forgotPasswordLink');
  const forgotPasswordModal = document.getElementById('forgotPasswordModal');
  const forgotPasswordRole = document.getElementById('forgotPasswordRole');
  const resetPasswordRole = document.getElementById('resetPasswordRole');
  
  if (forgotPasswordLink) {
    forgotPasswordLink.addEventListener('click', function(e) {
      e.preventDefault();
      // Get current role from login modal
      const currentRole = roleField ? roleField.value : 'student';
      if (forgotPasswordRole) forgotPasswordRole.value = currentRole;
      if (resetPasswordRole) resetPasswordRole.value = currentRole;
      
      // Close login modal
      const loginModalInstance = bootstrap.Modal.getInstance(loginModal);
      if (loginModalInstance) loginModalInstance.hide();
      
      // Reset forgot password modal to step 1
      document.getElementById('forgotPasswordStep1').style.display = 'block';
      document.getElementById('forgotPasswordStep2').style.display = 'none';
      document.getElementById('forgotPasswordError').style.display = 'none';
      document.getElementById('forgotPasswordSuccess').style.display = 'none';
      document.getElementById('forgotPasswordEmail').value = '';
      
      // Open forgot password modal
      const forgotModal = new bootstrap.Modal(forgotPasswordModal);
      forgotModal.show();
    });
  }

  // Back to Login Link Handler
  const backToLoginLink = document.getElementById('backToLoginLink');
  if (backToLoginLink) {
    backToLoginLink.addEventListener('click', function(e) {
      e.preventDefault();
      const forgotModalInstance = bootstrap.Modal.getInstance(forgotPasswordModal);
      if (forgotModalInstance) forgotModalInstance.hide();
      
      const loginModalInstance = new bootstrap.Modal(loginModal);
      loginModalInstance.show();
    });
  }

  // Handle Forgot Password Form Submission
  const forgotPasswordForm = document.getElementById('forgotPasswordForm');
  if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const errorDiv = document.getElementById('forgotPasswordError');
      const successDiv = document.getElementById('forgotPasswordSuccess');
      const email = document.getElementById('forgotPasswordEmail').value;
      
      errorDiv.style.display = 'none';
      successDiv.style.display = 'none';
      
      const formData = new FormData(this);
      formData.append('ajax', '1');
      
      try {
        const response = await fetch('../../Controllers/AuthController.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          // Extract reset code from message if present (for development/fallback)
          let resetCodeMessage = result.message || '';
          // Show step 2 (reset code form)
          document.getElementById('forgotPasswordStep1').style.display = 'none';
          document.getElementById('forgotPasswordStep2').style.display = 'block';
          document.getElementById('resetPasswordEmail').value = email;
          document.getElementById('resetPasswordRole').value = document.getElementById('forgotPasswordRole').value;
          
          // Clear reset code field
          document.getElementById('resetCode').value = '';
          
          // If reset code is in the message (development mode or email failed), show it
          if (resetCodeMessage.includes('Reset code:')) {
            const codeMatch = resetCodeMessage.match(/Reset code:\s*(\d+)/);
            if (codeMatch) {
              // Pre-fill the reset code field for convenience in development
              document.getElementById('resetCode').value = codeMatch[1];
              // Also show a helpful message
              const step2Message = document.querySelector('#forgotPasswordStep2 .text-muted');
              if (step2Message) {
                step2Message.innerHTML = 'Reset code: <strong>' + codeMatch[1] + '</strong><br><small class="text-warning">(Email sending failed - code displayed for testing. Check email configuration.)</small>';
              }
            }
          } else {
            // Normal flow - email was sent successfully
            const step2Message = document.querySelector('#forgotPasswordStep2 .text-muted');
            if (step2Message) {
              step2Message.innerHTML = 'Please check your email (' + email + ') for the reset code. It may take a few minutes to arrive.';
            }
          }
        } else {
          errorDiv.textContent = result.error || 'An error occurred. Please try again.';
          errorDiv.style.display = 'block';
        }
      } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
      }
    });
  }

  // Handle Reset Password Form Submission
  const resetPasswordForm = document.getElementById('resetPasswordForm');
  if (resetPasswordForm) {
    resetPasswordForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const errorDiv = document.getElementById('resetPasswordError');
      const newPassword = document.getElementById('newPassword').value;
      const confirmPassword = document.getElementById('confirmNewPassword').value;
      
      errorDiv.style.display = 'none';
      
      // Validate passwords match
      if (newPassword !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match.';
        errorDiv.style.display = 'block';
        return;
      }
      
      // Validate password length
      if (newPassword.length < 8) {
        errorDiv.textContent = 'Password must be at least 8 characters long.';
        errorDiv.style.display = 'block';
        return;
      }
      
      const formData = new FormData(this);
      formData.append('ajax', '1');
      
      try {
        const response = await fetch('../../Controllers/AuthController.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          Swal.fire({
            title: 'Success!',
            text: result.message || 'Password reset successfully. You can now login with your new password.',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            // Close modal and redirect to login
            const forgotModalInstance = bootstrap.Modal.getInstance(forgotPasswordModal);
            if (forgotModalInstance) forgotModalInstance.hide();
            
            const loginModalInstance = new bootstrap.Modal(loginModal);
            loginModalInstance.show();
          });
        } else {
          errorDiv.textContent = result.error || 'An error occurred. Please try again.';
          errorDiv.style.display = 'block';
        }
      } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
      }
    });
  }

  // Resend Code Link Handler
  const resendCodeLink = document.getElementById('resendCodeLink');
  if (resendCodeLink) {
    resendCodeLink.addEventListener('click', function(e) {
      e.preventDefault();
      // Go back to step 1
      document.getElementById('forgotPasswordStep2').style.display = 'none';
      document.getElementById('forgotPasswordStep1').style.display = 'block';
      document.getElementById('forgotPasswordEmail').value = document.getElementById('resetPasswordEmail').value;
    });
  }

  // Restrict reset code input to numbers only
  const resetCodeInput = document.getElementById('resetCode');
  if (resetCodeInput) {
    resetCodeInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  }

  // Toggle password visibility for new password
  document.getElementById('toggleNewPasswordBtn')?.addEventListener('click', function () {
    const passwordField = document.getElementById('newPassword');
    const icon = this.querySelector('i');
    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      passwordField.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });

  // Toggle password visibility for confirm new password
  document.getElementById('toggleConfirmNewPasswordBtn')?.addEventListener('click', function () {
    const passwordField = document.getElementById('confirmNewPassword');
    const icon = this.querySelector('i');
    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      passwordField.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });

  // Toggle password visibility
  document.getElementById('togglePasswordBtn')?.addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      passwordField.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }

    // Debug output
    console.log('PHP errorMessage:', errorMessage);
  });

  // Clear error when user starts typing in password field
  const passwordField = document.getElementById('password');
  if (passwordField) {
    passwordField.addEventListener('input', function() {
      this.classList.remove('password-error');
      const passwordError = document.getElementById('passwordError');
      if (passwordError) {
        passwordError.style.display = 'none';
        passwordError.textContent = '';
      }
    });
  }

  // Handle login form submission with AJAX
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const passwordField = document.getElementById('password');
      const passwordError = document.getElementById('passwordError');
      const emailField = document.getElementById('username');
      
      // Remove any previous error styling
      passwordField.classList.remove('password-error');
      passwordError.style.display = 'none';
      passwordError.textContent = '';
      
      // Get form data
      const formData = new FormData(this);
      // Add ajax flag to form data (more reliable than headers with FormData)
      formData.append('ajax', '1');
      
      try {
        const response = await fetch('../../Controllers/AuthController.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: formData
        });
        
        // Check if response is OK
        if (!response.ok) {
          console.error('Response not OK:', response.status, response.statusText);
          Swal.fire({
            title: 'Login Failed',
            text: 'An error occurred. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
          return;
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          // Not a JSON response, might be a redirect or HTML error page
          console.warn('Response is not JSON, content-type:', contentType);
          // Clone response to read text without consuming it
          const clonedResponse = response.clone();
          const text = await clonedResponse.text();
          console.log('Response text:', text.substring(0, 200)); // First 200 chars
          Swal.fire({
            title: 'Login Failed',
            text: 'Invalid credentials. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
          return;
        }
        
        const result = await response.json();
        console.log('Login response:', result);
        
        if (result.success) {
          // Success - redirect to user page
          // If redirect is relative, use it as-is; otherwise use same directory
          const redirectUrl = result.redirect || 'userViewPage.php';
          console.log('Redirecting to:', redirectUrl);
          window.location.href = redirectUrl;
        } else {
          // Check if it's a password error (wrong password)
          const errorLower = result.error ? result.error.toLowerCase() : '';
          if (result.error && (
            errorLower.includes('invalid credentials') || 
            errorLower.includes('wrong password') ||
            errorLower.includes('incorrect password') ||
            errorLower.includes('invalid password')
          )) {
            // Show inline error for wrong password
            passwordField.classList.add('password-error');
            passwordField.value = ''; // Clear password field
            passwordError.textContent = 'Wrong password';
            passwordError.style.display = 'block';
            passwordField.focus();
          } else {
            // For other errors (pending approval, etc.), show alert but keep modal open
            if (result.error && (errorLower.includes('pending') || errorLower.includes('approval'))) {
              Swal.fire({
                title: 'Account Pending',
                text: result.error,
                icon: 'info',
                confirmButtonText: 'OK'
              });
            } else {
              Swal.fire({
                title: 'Login Failed',
                text: result.error,
                icon: 'error',
                confirmButtonText: 'OK'
              });
            }
          }
        }
      } catch (error) {
        console.error('Login error:', error);
        Swal.fire({
          title: 'Error',
          text: 'An error occurred. Please try again.',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });
  }

  // === SHIFT + admin123 Shortcut for Admin Login Modal ===
  (function() {
    let inputBuffer = "";
    let lastKeyTime = Date.now();

    document.addEventListener("keydown", function(e) {
      // Don't trigger while typing in input boxes
      if (["INPUT", "TEXTAREA"].includes(document.activeElement.tagName)) return;

      // Only activate when Shift key is held down
      if (!e.shiftKey) return;

      const now = Date.now();

      // Reset buffer if user types too slowly
      if (now - lastKeyTime > 1000) {
        inputBuffer = "";
      }
      lastKeyTime = now;

      // Append key pressed (convert to lowercase for uniformity)
      inputBuffer += e.key.toLowerCase();

      // Once full sequence "admin123" is typed, trigger modal
      if (inputBuffer.endsWith("admin123")) {
        inputBuffer = ""; // reset buffer

        // Open the Admin Login Modal
        const modalElement = document.getElementById("adminLoginModal");
        if (modalElement) {
          const modal = new bootstrap.Modal(modalElement);
          modal.show();
        }
      }
    });
  })();
  
</script>



</body>
</html>
