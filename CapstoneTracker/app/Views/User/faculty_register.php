<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Registration</title>
  <link rel="stylesheet" href="../../../resources/css/User/indexLogin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<?php
session_start();

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? null;

unset($_SESSION['errors'], $_SESSION['success']);
?>

<body class="register-page">

<a href="../../../app/Views/public/home.php" class="home-logo">
  <img src="../../../resources/Images/ThesisCompLogo.png" alt="Home Logo">
</a>

<div class="register-container">
  <h2>Faculty Registration</h2>
  <br>
  <form id="facultyRegisterForm" method="POST" action="../../Controllers/RegistrationController.php" enctype="multipart/form-data" class="register-form">
    <input type="hidden" name="action" value="faculty_register">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

    <div class="form-grid">
      <!-- Name fields -->
      <div>
        <label>First Name</label>
        <input type="text" name="firstName" placeholder="Maria" required>
      </div>
      <div>
        <label>Middle Name</label>
        <input type="text" name="middleName" placeholder="Santos">
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" name="lastName" placeholder="Reyes" required>
      </div>
      <div>
        <label>Name Extension (Optional)</label>
        <input type="text" name="extension" placeholder="Jr., III, etc.">
      </div>

      <!-- Employee ID & Department -->
      <div>
        <label>Employee ID number</label>
        <input type="text" name="employeeId" placeholder="e.g., EMP-12345" required>
      </div>
      <div>
        <label>Department / College</label>
        <select name="department" required>
          <option value="" disabled selected>Select department</option>
          <option>CTET</option>
          <option>COE</option>
          <option>CBAA</option>
          <option>CAS</option>
        </select>
      </div>
    </div>
<br>
    <!-- Designation + Email in one row -->
    <div class="form-two-col">
      <div>
        <label>Designation / Position</label>
        <input type="text" name="designation" placeholder="Instructor, Professor, etc." required>
      </div>
      <div>
        <label>Email Address</label>
        <div class="input-icon">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="your.name@usep.edu.ph" required>
        </div>
        <div id="facEmailError" class="error-message" style="display: none;"></div>
      </div>
    </div>

    <!-- Password + Confirm Password in one row -->
    <div class="form-two-col">
      <div>
        <label>Password</label>
        <div class="input-icon">
          <input type="password" id="facPassword" name="password" placeholder="Enter password" required>
          <i class="far fa-eye toggle-pass" id="togglePassword"></i>
        </div>
        <div id="facPasswordError" class="error-message" style="display: none;"></div>
      </div>
      <div>
        <label>Confirm Password</label>
        <div class="input-icon">
          <input type="password" id="facConfirmPassword" name="confirmPassword" placeholder="Confirm password" required>
          <i class="far fa-eye toggle-pass" id="toggleConfirm"></i>
        </div>
        <div id="facConfirmError" class="error-message" style="display: none;"></div>
      </div>
    </div>

    <!-- Profile Picture -->
    <div class="profile-upload">
      <label>Profile Picture</label>
      <div class="upload-box" id="uploadBox">
        <input type="file" id="profilePic" name="profilePic" accept="image/*" hidden>
        <div class="upload-content" id="uploadPrompt" onclick="document.getElementById('profilePic').click()">
          <i class="fas fa-cloud-upload-alt"></i>
          <p>Click to upload<br><small>(Max 5MB, JPG/PNG)</small></p>
        </div>
        <div class="preview-container" id="previewContainer" style="display:none;">
          <button type="button" class="remove-x" id="removeImage" aria-label="Remove image">
            <i class="fas fa-times"></i>
          </button>
          <img id="previewImage" src="" alt="Preview">
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="form-buttons">
      <button type="submit" class="btn-primary">Create Account</button>
      <a href="indexLogin.php" class="btn-secondary">Back to Login</a>
    </div>
  </form>
</div>

<script>
  // Form validation
  document.getElementById('facultyRegisterForm').addEventListener('submit', function (e) {
    // Clear previous errors
    clearErrors();

    const password = document.getElementById('facPassword').value.trim();
    const confirmPassword = document.getElementById('facConfirmPassword').value.trim();
    const emailInput = document.querySelector('input[name="email"]');
    const email = emailInput ? emailInput.value.trim() : '';

    let hasError = false;

    // Simple pattern for university emails
    const emailPattern = /^[a-zA-Z0-9._%+-]+@usep\.edu\.ph$/;

    if (!emailPattern.test(email)) {
      showInlineError('facEmailError', emailInput, "Please use your university email (@usep.edu.ph).");
      hasError = true;
    }

    if (password !== confirmPassword) {
      showInlineError('facConfirmError', document.getElementById('facConfirmPassword'), "Passwords do not match.");
      hasError = true;
    }

    // Enhanced password validation: alphanumeric only, min 8 chars, at least 1 uppercase, 1 number
    const passwordRegex = /^(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9]{8,}$/;
    if (!passwordRegex.test(password)) {
      showInlineError('facPasswordError', document.getElementById('facPassword'), "At least 8 chars, 1 uppercase, and 1 number.");
      hasError = true;
    }

    if (hasError) {
      e.preventDefault();
    }
  });

  function showInlineError(errorId, inputElement, message) {
    const errorDiv = document.getElementById(errorId);
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    inputElement.classList.add('error');
  }

  function clearErrors() {
    const errorIds = ['facEmailError', 'facPasswordError', 'facConfirmError'];
    errorIds.forEach(id => {
      const errorDiv = document.getElementById(id);
      errorDiv.style.display = 'none';
      errorDiv.textContent = '';
    });

    const inputs = ['facPassword', 'facConfirmPassword'].map(id => document.getElementById(id));
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) inputs.push(emailInput);
    inputs.forEach(input => input.classList.remove('error'));
  }

  // Password toggles
  document.getElementById('togglePassword').addEventListener('click', function () {
    const pass = document.getElementById('facPassword');
    this.classList.toggle('fa-eye-slash');
    pass.type = pass.type === 'password' ? 'text' : 'password';
  });
  document.getElementById('toggleConfirm').addEventListener('click', function () {
    const pass = document.getElementById('facConfirmPassword');
    this.classList.toggle('fa-eye-slash');
    pass.type = pass.type === 'password' ? 'text' : 'password';
  });

  // Profile image preview + remove
  const fileInput = document.getElementById('profilePic');
  const previewImage = document.getElementById('previewImage');
  const uploadPrompt = document.getElementById('uploadPrompt');
  const previewContainer = document.getElementById('previewContainer');
  const removeBtn = document.getElementById('removeImage');

  fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => {
        previewImage.src = e.target.result;
        previewContainer.style.display = 'block';
        uploadPrompt.style.display = 'none';
      };
      reader.readAsDataURL(file);
    }
  });

  removeBtn.addEventListener('click', function () {
    fileInput.value = "";
    previewImage.src = "";
    previewContainer.style.display = 'none';
    uploadPrompt.style.display = 'block';
  });
</script>

<style>
  /* Add this CSS below or in indexLogin.css if not yet present */
  .form-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 15px;
  }

  @media (max-width: 768px) {
    .form-two-col {
      grid-template-columns: 1fr;
    }
  }

  .error-message {
    color: #d32f2f;
    font-size: 0.875rem;
    margin-top: 5px;
  }

  .error {
    border-color: #d32f2f !important;
    box-shadow: 0 0 0 0.2rem rgba(211, 47, 47, 0.25) !important;
  }
</style>

</body>
</html>
