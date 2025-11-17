
  
  <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration</title>
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
    
    <h2>Student Registration</h2>
    <br>
    <form id="studentRegisterForm" method="POST" action="../../Controllers/RegistrationController.php" enctype="multipart/form-data" class="register-form">
      <input type="hidden" name="action" value="student_register">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

      <div class="form-grid">
        <div>
          <label>First Name</label>
          <input type="text" name="firstName" placeholder="Juan" required>
        </div>

        <div>
          <label>Middle Name</label>
          <input type="text" name="middleName" placeholder="Santos">
        </div>

        <div>
          <label>Last Name</label>
          <input type="text" name="lastName" placeholder="Dela Cruz" required>
        </div>

        <div>
          <label>Name Extension (Optional)</label>
          <input type="text" name="extension" placeholder="Jr., III, etc.">
        </div>

        <div>
          <label>Student ID number</label>
          <input type="text" name="studentId" placeholder="2025-12345" required>
        </div>

        <div>
          <label>Course / Program</label>
          <select name="course" required>
            <option value="" disabled selected>Select your program</option>
            <option>Bachelor of Technical-Vocational Teacher Education</option>
            <option>Bachelor of Special Need Education</option>
            <option>Bachelor of Early Childhood Education</option>
            <option>Bachelor of Secondary Education</option>
            <option>Bachelor of Science in Information Technology</option>
            <option>Bachelor of Elementary Education</option>
            <option>Bachelor Science in Agricultural and Biosystems Engineering</option>
          </select>
        </div>

        <div>
          <label>Email Address</label>
          <div class="input-icon">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="your.name@usep.edu.ph" required>
          </div>
          <small>Use your university email (@usep.edu.ph)</small>
          <div id="stuEmailError" class="error-message" style="display: none;"></div>
        </div>

        <div>
          <label>Password</label>
          <div class="input-icon">
            <input type="password" id="regPassword" name="password" placeholder="Enter password" required>
            <i class="far fa-eye toggle-pass" id="togglePassword"></i>
          </div>
          <div id="stuPasswordError" class="error-message" style="display: none;"></div>
        </div>

        <div>
          <label>Confirm Password</label>
          <div class="input-icon">
            <input type="password" id="regConfirmPassword" name="confirmPassword" placeholder="Confirm password" required>
            <i class="far fa-eye toggle-pass" id="toggleConfirm"></i>
          </div>
          <div id="stuConfirmError" class="error-message" style="display: none;"></div>
        </div>

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


      </div>

      <div class="form-buttons">
        <button type="submit" class="btn-primary">Create Account</button>
        <a href="indexLogin.php" class="btn-secondary">Back to Login</a>
      </div>
    </form>
  </div>

  <script>
document.getElementById('studentRegisterForm').addEventListener('submit', function (e) {
  // Clear previous errors
  clearErrors();

  const password = document.getElementById('regPassword').value.trim();
  const confirmPassword = document.getElementById('regConfirmPassword').value.trim();
  const emailInput = document.querySelector('input[name="email"]');
  const email = emailInput ? emailInput.value.trim() : '';

  let hasError = false;

  // Simple pattern for university emails
  const emailPattern = /^[a-zA-Z0-9._%+-]+@usep\.edu\.ph$/;

  if (!emailPattern.test(email)) {
    showInlineError('stuEmailError', emailInput, "Please use your university email (@usep.edu.ph).");
    hasError = true;
  }

  if (password !== confirmPassword) {
    showInlineError('stuConfirmError', document.getElementById('regConfirmPassword'), "Passwords do not match.");
    hasError = true;
  }

  // Enhanced password validation: alphanumeric only, min 8 chars, at least 1 uppercase, 1 number
  const passwordRegex = /^(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9]{8,}$/;
  if (!passwordRegex.test(password)) {
    showInlineError('stuPasswordError', document.getElementById('regPassword'), "At least 8 letters, 1 uppercase, and 1 number.");
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
  const errorIds = ['stuEmailError', 'stuPasswordError', 'stuConfirmError'];
  errorIds.forEach(id => {
    const errorDiv = document.getElementById(id);
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
  });

  const inputs = ['regPassword', 'regConfirmPassword'].map(id => document.getElementById(id));
  const emailInput = document.querySelector('input[name="email"]');
  if (emailInput) inputs.push(emailInput);
  inputs.forEach(input => input.classList.remove('error'));
}

  // Password toggles
  document.getElementById('togglePassword').addEventListener('click', function () {
    const pass = document.getElementById('regPassword');
    this.classList.toggle('fa-eye-slash');
    pass.type = pass.type === 'password' ? 'text' : 'password';
  });
  document.getElementById('toggleConfirm').addEventListener('click', function () {
    const pass = document.getElementById('regConfirmPassword');
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
