
// Show error message as SweetAlert and reopen modal if there is an error
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should show the modal based on error message
    if (event.ShiftKey && event.key === 'h') {
        event.preventDefault();
        
        const adminModal = document.getElementById('adminLoginModal');
        if (adminModal) {
            const adminModalInstance = new bootstrap.Modal(adminModal);
            adminModalInstance.show();
            
            // Show access notification
            showAdminAccessNotification();
            
            console.log('Admin login panel opened via Shift+H');
        }
    }

    if (typeof errorMessage !== 'undefined' && errorMessage && errorMessage !== '') {
        // Determine if it's a login error or registration error
        if (window.location.href.includes('AuthController') || 
            errorMessage.includes('login') || 
            errorMessage.includes('Login') ||
            errorMessage.includes('credentials') ||
            errorMessage.includes('pending') ||
            errorMessage.includes('rejected')) {
            showLoginErrorAlert(errorMessage);
        } else {
            showErrorAlert(errorMessage); // Keep the original for now
        }
        
        // Safely check errorModal with proper undefined handling
        const modalType = typeof errorModal !== 'undefined' ? errorModal : '';
        
        // Determine which modal to open based on the error context
        if (modalType === 'student' || errorMessage.includes('Student') || errorMessage.includes('student')) {
            setTimeout(() => {
                openStudentRegistration();
            }, 1000);
        } else if (modalType === 'faculty' || errorMessage.includes('Faculty') || errorMessage.includes('faculty')) {
            setTimeout(() => {
                openFacultyRegistration();
            }, 1000);
        } else {
            // Default to login modal for general errors
            setTimeout(() => {
                openLogin('Researcher');
            }, 1000);
        }
    }

    if (typeof successMessage !== 'undefined' && successMessage && successMessage !== '') {
        showSuccessAlert(successMessage);
    }

    if (typeof adminErrorMessage !== 'undefined' && adminErrorMessage && adminErrorMessage !== '') {
        showAdminErrorAlert(adminErrorMessage);
        
        // Automatically open the admin modal if there's an admin error
        setTimeout(() => {
            const adminModal = document.getElementById('adminLoginModal');
            if (adminModal) {
                const adminModalInstance = new bootstrap.Modal(adminModal);
                adminModalInstance.show();
            }
        }, 1000);
    }

    
    
    // Initialize the page functionality
    initializePage();
    setupAdminModal();
    setupAdminForm();

});

//Sequence Error Notif
function showSequenceErrorNotification() {
    // Remove any existing notification
    const existingNotification = document.querySelector('.sequence-error-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create error notification
    const notification = document.createElement('div');
    notification.className = 'sequence-error-notification';
    notification.innerHTML = `
        <div>
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Incorrect Sequence</strong>
            <br>
            <span class="ms-2">Access denied</span>
        </div>
    `;
    document.body.appendChild(notification);
    
    // Add animation - slide in from top
    notification.style.top = '-100px';
    notification.style.opacity = '0';
    setTimeout(() => {
        notification.style.transition = 'all 0.3s ease';
        notification.style.top = '20px';
        notification.style.opacity = '1';
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.top = '-100px';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

//Admin Access Notif
function showAdminAccessNotification() {
    // Remove any existing notification
    const existingNotification = document.querySelector('.admin-access-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create a subtle notification
    const notification = document.createElement('div');
    notification.className = 'admin-access-notification';
    notification.innerHTML = `
        <div>
            <i class="fas fa-shield-alt me-2"></i><strong>Admin Login Access Granted</strong>
            
        </div>
    `;
    document.body.appendChild(notification);
    
    // Add animation - slide in from left
    notification.style.transform = 'translateX(-100px)';
    notification.style.opacity = '0';
    setTimeout(() => {
        notification.style.transition = 'all 0.3s ease';
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(-100px)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

function showSuccessAlert(message) {
    console.log('Success message:', message);
    
    const msg = String(message || '').trim();
    const emptyValues = ['undefined', 'null', "'undefined'", "'null'", '"undefined"', '"null"', 'false', '0', '', '[]', '{}', 'NaN'];
    
    if (!msg || emptyValues.includes(msg)) {
        Swal.fire({
            title: 'Registration Successful',
            text: 'Your account has been created successfully!',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    } else {
        Swal.fire({
            title: 'Registration Successful',
            text: msg,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    }
}

function showErrorAlert(message) {
    console.log('Raw error message:', message, 'Type:', typeof message);
    
    // Convert to string and trim
    const msg = String(message || '').trim();
    
    // List of values that should be considered as "no real message"
    const emptyValues = [
        'undefined', 'null', "'undefined'", "'null'", 
        '"undefined"', '"null"', 'false', '0', '',
        '[]', '{}', 'NaN'
    ];
    
    // Check if message is empty or in our invalid list
    if (!msg || emptyValues.includes(msg)) {
        Swal.fire({
            title: 'Registration Failed',
            text: 'Registration failed. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } else {
        // Show the actual error message
        Swal.fire({
            title: 'Registration Failed',
            text: msg,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function showLoginErrorAlert(message) {
    console.log('Login error message:', message);
    
    const msg = String(message || '').trim();
    const emptyValues = ['undefined', 'null', "'undefined'", "'null'", '"undefined"', '"null"', 'false', '0', '', '[]', '{}', 'NaN'];
    
    if (!msg || emptyValues.includes(msg)) {
        Swal.fire({
            title: 'Login Failed',
            text: 'Login failed. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } else {
        let title = 'Login Failed';
        let icon = 'error';
        
        // Customize based on message content
        if (msg.includes('pending') || msg.includes('approval')) {
            title = 'Account Pending';
            icon = 'info'; // Change to info icon for pending accounts
        } else if (msg.includes('rejected')) {
            title = 'Account Rejected';
            icon = 'warning'; // Change to warning icon for rejected accounts
        }
        
        Swal.fire({
            title: title,
            html: msg, // Use html instead of text to render HTML tags
            icon: icon,
            confirmButtonText: 'OK'
        });
    }
}

function showRegistrationErrorAlert(message) {
    console.log('Registration error message:', message);
    
    const msg = String(message || '').trim();
    const emptyValues = ['undefined', 'null', "'undefined'", "'null'", '"undefined"', '"null"', 'false', '0', '', '[]', '{}', 'NaN'];
    
    if (!msg || emptyValues.includes(msg)) {
        Swal.fire({
            title: 'Registration Failed',
            text: 'Registration failed. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } else {
        Swal.fire({
            title: 'Registration Failed',
            text: msg,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function openStudentRegistration() {
    const studentModal = new bootstrap.Modal(document.getElementById('studentRegisterModal'));
    studentModal.show();
}

function openFacultyRegistration() {
    const facultyModal = new bootstrap.Modal(document.getElementById('facultyRegisterModal'));
    facultyModal.show();
}



// Prevent form submission from closing modal on error
function setupFormHandlers() {
    const loginForm = document.querySelector('#loginModal form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Let the form submit normally - the server will handle validation
            console.log('Form submitted to AuthController');
        });
    }
}


function forgotPassword() {
    Swal.fire({
        title: 'Forgot Password?',
        text: 'Please contact the administrator to reset your password.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function showLoading() {
    const btn = document.getElementById('customGoogleBtn');
    if (btn) {
        btn.classList.add('loading');
    }
}

function hideLoading() {
    const btn = document.getElementById('customGoogleBtn');
    if (btn) {
        btn.classList.remove('loading');
    }
}

function initializeGoogleSignIn() {
    console.log('Initializing Google Identity Services...');
    
    // Load Google Identity Services
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = () => {
        console.log('Google Identity Services loaded');
        renderGoogleButton();
    };
    script.onerror = (error) => {
        console.error('Failed to load Google Identity Services:', error);
    };
    document.head.appendChild(script);
}

function loadGoogleAPI() {
    return new Promise((resolve, reject) => {
        if (window.gapi) {
            resolve();
            return;
        }
        
        const script = document.createElement('script');
        script.src = 'https://apis.google.com/js/platform.js';
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function renderGoogleButton() {
    if (typeof google === 'undefined') {
        console.error('Google Identity Services not loaded');
        return;
    }
    
    try {
        // Initialize Google Identity Services
        google.accounts.id.initialize({
            client_id: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com', // REPLACE WITH YOUR ACTUAL CLIENT ID
            callback: handleCredentialResponse,
            auto_select: false,
            cancel_on_tap_outside: true,
            context: 'signin',
            ux_mode: 'popup',
            itp_support: true
        });
        
        // Optional: Display the One Tap prompt
         google.accounts.id.prompt((notification) => {
             if (notification.isNotDisplayed() || notification.isSkipped()) {
                 console.log('One Tap not displayed');
             }
         });
        
        console.log('Google Identity Services initialized successfully');
        
    } catch (error) {
        console.error('Error initializing Google Sign-In:', error);
    }
}

function handleCredentialResponse(response) {
    console.log('Google Sign-In response received');
    
    try {
        // Decode the JWT token to get user info
        const responsePayload = parseJwt(response.credential);
        
        console.log('Google User Info:', {
            id: responsePayload.sub,
            name: responsePayload.name,
            email: responsePayload.email,
            picture: responsePayload.picture
        });
        
        // Validate USeP email
        if (!responsePayload.email.endsWith('@usep.edu.ph')) {
            Swal.fire({
                title: 'Invalid Email',
                text: 'Please use your USeP (@usep.edu.ph) email address.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            
            // Sign out the user
            google.accounts.id.disableAutoSelect();
            google.accounts.id.revoke(responsePayload.email, (done) => {
                console.log('Credentials revoked for non-USeP email');
            });
            return;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Signing In...',
            text: 'Please wait while we authenticate your account',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Send the credential to your backend for verification
        sendGoogleCredentialToBackend(response.credential);
        
    } catch (error) {
        console.error('Error processing Google credential:', error);
        Swal.fire({
            title: 'Authentication Error',
            text: 'Failed to process Google sign-in. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        
        // Reset custom button
        resetGoogleButton();
    }
}

async function sendGoogleCredentialToBackend(credential) {
    try {
        const response = await fetch('/AuthController/google-auth', { // Update with your actual endpoint
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                credential: credential,
                _token: 'YOUR_CSRF_TOKEN' // Add if using Laravel or similar
            })
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            // Successful login - redirect or show success
            Swal.fire({
                title: 'Success!',
                text: 'Signed in successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = result.redirect_url || '/dashboard';
            });
        } else {
            // Handle errors from backend
            throw new Error(result.message || 'Authentication failed');
        }
        
    } catch (error) {
        console.error('Backend authentication error:', error);
        Swal.fire({
            title: 'Authentication Failed',
            text: error.message || 'Failed to authenticate with server',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        
        // Revoke Google auth on failure
        google.accounts.id.disableAutoSelect();
        resetGoogleButton();
    }
}

function parseJwt(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (error) {
        console.error('Error parsing JWT:', error);
        throw new Error('Invalid token format');
    }
}

function signOut() {
    if (typeof google !== 'undefined' && google.accounts.id) {
        google.accounts.id.disableAutoSelect();
        google.accounts.id.revoke((done) => {
            console.log('Google Sign-Out completed');
        });
    }
}

function handleGoogleButtonClick() {
    console.log('Custom Google button clicked');
    
    // Show loading state on custom button
    const customBtn = document.getElementById('googleModalBtn');
    if (customBtn) {
        customBtn.disabled = true;
        customBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Connecting to Google...';
    }
    
    // Trigger Google Sign-In
    if (typeof google !== 'undefined' && google.accounts.id) {
        google.accounts.id.prompt(); // Show the Google One Tap or regular prompt
        
        // Alternatively, you can render a button explicitly:
        // google.accounts.id.renderButton(
        //     document.getElementById('googleButtonContainer'),
        //     { theme: 'outline', size: 'large', text: 'signin_with' }
        // );
    } else {
        console.error('Google Identity Services not available');
        Swal.fire({
            title: 'Service Unavailable',
            text: 'Google Sign-In is not available at the moment. Please try again later.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        resetGoogleButton();
    }
}

function resetGoogleButton() {
    const customBtn = document.getElementById('googleModalBtn');
    if (customBtn) {
        customBtn.disabled = false;
        customBtn.innerHTML = '<i class="fab fa-google me-2"></i> Sign in with USeP Email';
    }
}

function initializePage() {
    console.log('Initializing page functionality...');
    
    setupCustomGoogleButton();
    // Start Google button initialization
    renderGoogleButton();

    
    
    // Add a fallback in case the Google API doesn't load properly
    setTimeout(function() {
        const googleButton = document.querySelector('#googleButton iframe, #googleButton .abcRioButton');
        if (!googleButton) {
            console.log('Google button still not available after timeout');
            // You can choose to hide the Google button container here if needed
            // document.getElementById('googleButton').style.display = 'none';
        }
    }, 5000);

    // Modal open buttons - ADD NULL CHECKS
    const researcherBtn = document.getElementById("researcherBtn");
    const facultyBtn = document.getElementById("facultyBtn");
    const modalTitle = document.getElementById("modalTitle");
    const roleField = document.getElementById("roleField");
    const googleModalBtn = document.getElementById("googleModalBtn");
    const registerForm = document.getElementById('studentRegisterForm');
    const facultyRegisterForm = document.getElementById('facultyRegisterForm');
    const regTogglePassword = document.getElementById('regTogglePassword');
    const regToggleConfirm = document.getElementById('regToggleConfirm');
    const regPassword = document.getElementById('regPassword');
    const regConfirmPassword = document.getElementById('regConfirmPassword');
    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const passwordInput = document.getElementById("password");

    let adminSequence = [];
    let shiftPressed = false;
    const requiredSequence = ['a', 'd', 'm', 'i', 'n', '!', '@','#'];

    function openLogin(role){
        if (modalTitle) modalTitle.innerText = role + " Login";
        if (roleField) roleField.value = role;
        const modalEl = document.getElementById('loginModal');
        if (!modalEl) return;
        const loginModal = new bootstrap.Modal(modalEl);
        loginModal.show();
        
        
    }

    function setupCustomGoogleButton() {
        const googleModalBtn = document.getElementById('googleModalBtn');
        if (googleModalBtn) {
            googleModalBtn.addEventListener('click', function() {
                handleGoogleButtonClick();
            });
        }
    }

    // ADD NULL CHECKS FOR EVENT LISTENERS
    if (researcherBtn) {
        researcherBtn.addEventListener("click", () => openLogin("Researcher"));
    }
    
    if (facultyBtn) {
        facultyBtn.addEventListener("click", () => openLogin("Faculty"));
    }

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    }

    if (registerForm) {
      registerForm.addEventListener('submit', function (e) {
          e.preventDefault();
          
          const email = document.getElementById('regEmail').value.trim().toLowerCase();
          const pass = document.getElementById('regPassword').value;
          const confirm = document.getElementById('regConfirmPassword').value;
          
          if (!email.endsWith('@usep.edu.ph')) {
              Swal.fire('Invalid email', 'Please use your USeP (@usep.edu.ph) email.', 'error');
              return;
          }
          
          if (pass !== confirm) {
              Swal.fire('Passwords do not match', 'Please re-enter your password.', 'error');
              return;
          }
          
          // Show loading state
          Swal.fire({
              title: 'Processing...',
              text: 'Please wait while we create your account',
              allowOutsideClick: false,
              didOpen: () => {
                  Swal.showLoading();
              }
          });
          
          // Submit the form to AuthController
          this.submit();
      });
  }

    if (facultyRegisterForm) {
        facultyRegisterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            const email = document.getElementById('facEmail').value.trim().toLowerCase();
            const pass = document.getElementById('facPassword').value;
            const confirm = document.getElementById('facConfirmPassword').value;
            
            // Validate USeP email
            if (!email.endsWith('@usep.edu.ph')) {
                Swal.fire('Invalid email', 'Please use your USeP (@usep.edu.ph) email.', 'error');
                return;
            }
            
            // Validate password match
            if (pass !== confirm) {
                Swal.fire('Passwords do not match', 'Please re-enter your password.', 'error');
                return;
            }
            
            // Validate password length
            if (pass.length < 8) {
                Swal.fire('Password too short', 'Password must be at least 8 characters long.', 'error');
                return;
            }
            
            // Validate required fields
            const requiredFields = [
                'facFirstName', 'facLastName', 'facEmployeeId', 
                'facDepartment', 'facDesignation'
            ];
            
            for (const fieldId of requiredFields) {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    Swal.fire('Missing information', `Please fill in the ${field.labels[0].textContent}`, 'error');
                    field.focus();
                    return;
                }
            }
            
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we create your faculty account',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form to RegistrationController
            this.submit();
        });
    }

    function wireToggle(btn, input) {
      if (btn && input) {
        btn.addEventListener('click', function() {
          const isHidden = input.type === 'password';
          input.type = isHidden ? 'text' : 'password';
          const icon = this.querySelector('i');
          if (icon) {
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
          }
          this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
      }
    }

    wireToggle(regTogglePassword, regPassword);
    wireToggle(regToggleConfirm, regConfirmPassword);
    wireToggle(document.getElementById('facTogglePassword'), document.getElementById('facPassword'));
    wireToggle(document.getElementById('facToggleConfirm'), document.getElementById('facConfirmPassword'));

    // Open respective registration modal based on selected role
    const openRegisterLink = document.getElementById('openRegisterLink');
    if (openRegisterLink) {
      openRegisterLink.addEventListener('click', function() {
        const role = (roleField && roleField.value) || 'Researcher';
        const currentModal = document.getElementById('loginModal');
        const modalInstance = bootstrap.Modal.getInstance(currentModal) || new bootstrap.Modal(currentModal);
        modalInstance.hide();
        setTimeout(() => {
          const targetId = role.toLowerCase() === 'faculty' ? 'facultyRegisterModal' : 'studentRegisterModal';
          const targetEl = document.getElementById(targetId);
          if (targetEl) new bootstrap.Modal(targetEl).show();
        }, 250);
      });
    }

    document.addEventListener('keydown', function(event) {
        // Don't listen for sequence if admin modal is open
        if (adminModalOpen) return;
        
        // Check if Shift key is pressed
        if (event.shiftKey && !shiftPressed) {
            shiftPressed = true;
            adminSequence = []; // Reset sequence when Shift is first pressed
            console.log('Shift pressed - listening for admin sequence...');
            return;
        }
        
        // If Shift is pressed, listen for the sequence
        if (shiftPressed && event.key.length === 1) { // Only single character keys
            const key = event.key.toLowerCase();
            
            // Add to sequence
            adminSequence.push(key);
         // Keep commented   console.log('Sequence progress:', adminSequence.join(''));
            
            // Check if sequence matches
            if (adminSequence.length === requiredSequence.length) {
                if (JSON.stringify(adminSequence) === JSON.stringify(requiredSequence)) {
                    event.preventDefault();
                    openAdminPanel();
                } else {
                    // Show error notification for incorrect sequence
                    showSequenceErrorNotification();
                    // Reset sequence
                    adminSequence = [];
                    console.log('Sequence incorrect - resetting');
                }
            }
            
            // If sequence is longer than required, reset
            if (adminSequence.length > requiredSequence.length) {
                adminSequence = [];
            }
        }
    });
    
    document.addEventListener('keyup', function(event) {
        // Reset when Shift is released
        if (event.key === 'Shift') { // Changed from 'Control' to 'Shift'
            shiftPressed = false;
            adminSequence = [];
            console.log('Shift released - sequence reset');
        }
    });
    
};


//Open Admin Modal
let adminModalOpen = false;

function openAdminPanel() {
    // Reset sequence state
    shiftPressed = false;
    adminSequence = [];
    
    const adminModal = document.getElementById('adminLoginModal');
    if (adminModal) {
        const adminModalInstance = new bootstrap.Modal(adminModal);
        adminModalInstance.show();
        
        // Set modal state to open
        adminModalOpen = true;
        
        // Show access notification
        showAdminAccessNotification();
        
        console.log('Admin login panel opened via Shift+admin sequence');
    }
}


function setupAdminModal() {
    const adminToggleBtn = document.getElementById('adminTogglePassword');
    const adminPasswordInput = document.getElementById('adminPassword');
    const adminModal = document.getElementById('adminLoginModal');
    
    if (adminModal) {
        adminModal.addEventListener('hidden.bs.modal', function () {
            adminModalOpen = false;
            console.log('Admin modal closed - sequence detection re-enabled');
        });
        
        adminModal.addEventListener('shown.bs.modal', function () {
            adminModalOpen = true;
            console.log('Admin modal opened - sequence detection disabled');
        });
    }
    
    if (adminToggleBtn && adminPasswordInput) {
        adminToggleBtn.addEventListener('click', function() {
            const isHidden = adminPasswordInput.type === 'password';
            adminPasswordInput.type = isHidden ? 'text' : 'password';
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    }
}

function setupAdminForm() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('adminUsername').value;
            const password = document.getElementById('adminPassword').value;
            
            if (!username || !password) {
                Swal.fire('Error', 'Please enter both Admin ID and password', 'error');
                return;
            }
            
            // Show loading state
            Swal.fire({
                title: 'Authenticating...',
                text: 'Please wait while we verify your credentials',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form
            this.submit();
        });
    }
}

function showAdminErrorAlert(message) {
    console.log('Admin login error message:', message);
    
    const msg = String(message || '').trim();
    const emptyValues = ['undefined', 'null', "'undefined'", "'null'", '"undefined"', '"null"', 'false', '0', '', '[]', '{}', 'NaN'];
    
    if (!msg || emptyValues.includes(msg)) {
        Swal.fire({
            title: 'Admin Login Failed',
            text: 'Invalid admin credentials. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } else {
        Swal.fire({
            title: 'Admin Login Failed',
            text: msg,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}
