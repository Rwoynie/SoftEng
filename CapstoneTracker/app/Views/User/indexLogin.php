<?php


// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
          </div>
          <button type="submit" class="btn btn-success w-100 mb-2">Login</button>

          <div class="d-flex justify-content-center mb-2">
            <div id="googleButton"></div>
          </div>
          
          <div class="d-flex justify-content-center mb-2">
              <button id="googleModalBtn" type="button" class="btn btn-outline-danger w-100">
                  <i class="fab fa-google me-2"></i> Sign in with USeP Email
              </button>
          </div>
          
          
          <div class="text-center">
            <a href="#" id="openRegisterLink" class="btn btn-link">Not yet registered?</a>
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
            <!-- Separate Name Fields -->
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
            
            <div class="col-md-6">
              <label for="regStudentId" class="form-label">Student ID number</label>
              <input type="text" id="regStudentId" name="studentId" class="form-control" placeholder="e.g., 2025-12345" required>
            </div>
            
            <div class="col-12">
              <label for="regCourse" class="form-label">Course / Program</label>
              <select id="regCourse" name="course" class="form-select" required>
                <option value="" selected disabled>Select your program</option>
                <option>Bachelor of Technical-Vocational Teacher Education</option>
                <option>Bachelor of Special Need Education</option>
                <option>Bachelor of Early Childhood Education</option>
                <option>Bachelor of Secondary Education</option>
                <option>Bachelor of Science in Information Technology</option>
                <option>Bachelor of Elementary Education</option>
                <option>Bachelor Science in Agricultural and Biosystems Engineering</option>
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
            <div class="col-md-6">
              <label for="regPassword" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" id="regPassword" name="password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" id="regTogglePassword" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label for="regConfirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input type="password" id="regConfirmPassword" name="confirmPassword" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" id="regToggleConfirm" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-12">
              <label for="regProfilePic" class="form-label">Profile picture</label>
              <input type="file" id="regProfilePic" name="profilePic" class="form-control" accept="image/*">
              <small class="text-muted">Max 5MB. JPG/PNG preferred.</small>
            </div>
            <div class="col-12 d-grid gap-2">
              <button type="submit" class="btn btn-primary">Create account</button>
              <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Back to login</button>
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
            
            <div class="col-md-6">
              <label for="facEmployeeId" class="form-label">Employee ID number</label>
              <input type="text" id="facEmployeeId" name="employeeId" class="form-control" placeholder="e.g., EMP-12345" required>
            </div>
            <div class="col-md-6">
              <label for="facDepartment" class="form-label">Department / College</label>
              <select id="facDepartment" name="department" class="form-select" required>
                <option value="" selected disabled>Select department</option>
                <option>CTET</option>
                <option>COE</option>
              </select>
            </div>
            <div class="col-12">
              <label for="facDesignation" class="form-label">Designation / Position</label>
              <input type="text" id="facDesignation" name="designation" class="form-control" placeholder="e.g., Instructor, Professor" required>
            </div>
            <div class="col-12">
              <label for="facEmail" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="facEmail" name="email" class="form-control" placeholder="your.name@usep.edu.ph" required>
              </div>
              <small class="text-muted">Use your university email (@usep.edu.ph)</small>
            </div>
            <div class="col-md-6">
              <label for="facPassword" class="form-label">Password</label>
              <div class="input-group">
                <input type="password" id="facPassword" name="password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" id="facTogglePassword" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label for="facConfirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <input type="password" id="facConfirmPassword" name="confirmPassword" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" id="facToggleConfirm" aria-label="Show password">
                  <i class="far fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-12">
              <label for="facProfilePic" class="form-label">Profile picture (Optional)</label>
              <input type="file" id="facProfilePic" name="profilePic" class="form-control" accept="image/*">
              <small class="text-muted">Max 5MB. JPG/PNG preferred.</small>
            </div>
            <div class="col-12 d-grid gap-2">
              <button type="submit" class="btn btn-primary">Create account</button>
              <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Back to login</button>
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
    
    
</body>

    <script>
        

        // Debug output
    console.log('PHP errorMessage:', errorMessage);
    
    </script>

</html>
