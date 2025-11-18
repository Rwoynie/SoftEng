<?php


// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$session_expired = false;
if (isset($_SESSION['session_expired']) && $_SESSION['session_expired']) {
    $session_expired = true;
    session_unset();
    session_destroy();
    session_start(); 
}

if (isset($_GET['session_expired']) && $_GET['session_expired'] == 1) {
    $session_expired = true;
    session_unset();
    session_destroy();
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Secure random token
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


  
  // Reload page when the modal close (X) is clicked for login/admin modals
  (function () {
    const modalIds = ['#loginModal', '#adminLoginModal']; // add any other modal IDs as needed

    modalIds.forEach(id => {
      const modal = document.querySelector(id);
      if (!modal) return;

      // Listen for clicks on elements that close the modal (Bootstrap: data-bs-dismiss="modal" or .btn-close)
      modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close').forEach(btn => {
        btn.addEventListener('click', () => {
          // small delay to allow Bootstrap animation to start/finish
          setTimeout(() => {
            window.location.reload();
          }, 120);
        });
      });
    });
  })();

  // Toggle password for user login modal
  (function () {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('password');
    if (toggleBtn && pwdInput) {
      const icon = toggleBtn.querySelector('i');
      toggleBtn.addEventListener('click', function () {
        if (pwdInput.type === 'password') {
          pwdInput.type = 'text';
          if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
        } else {
          pwdInput.type = 'password';
          if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
        }
        // keep focus on input after toggle
        pwdInput.focus();
      });
    }

  })();
</script>



</body>
</html>
