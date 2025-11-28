
document.addEventListener('DOMContentLoaded', function() {

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
                openLogin('Researcher'); // This should now work
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

     

        if (typeof accountLocked !== 'undefined' && accountLocked && accountLocked !== 'false') {
            showLoginAttemptAlert(errorMessage, 0, true, lockoutTime);
            

            setTimeout(() => {
                openLogin('Researcher');
            }, 1500);
        } else if (typeof loginAttemptsRemaining !== 'undefined' && loginAttemptsRemaining < 3) {
            showLoginAttemptAlert(errorMessage, loginAttemptsRemaining, false, 0);
            

            setTimeout(() => {
                openLogin('Researcher');
            }, 1500);
        }
        

    if (typeof errorMessage !== 'undefined' && errorMessage && errorMessage !== '') {
            
        if (!accountLocked && loginAttemptsRemaining >= 3) {
                if (window.location.href.includes('AuthController') || 
                    errorMessage.includes('login') || 
                    errorMessage.includes('Login') ||
                    errorMessage.includes('credentials') ||
                    errorMessage.includes('pending') ||
                    errorMessage.includes('rejected')) {
                    showLoginErrorAlert(errorMessage);
                } else {
                    showErrorAlert(errorMessage);
                }
            }
            
            // Your existing modal opening code...
            const modalType = typeof errorModal !== 'undefined' ? errorModal : '';
            
            if (modalType === 'student' || errorMessage.includes('Student') || errorMessage.includes('student')) {
                setTimeout(() => {
                    openStudentRegistration();
                }, 1000);
            } else if (modalType === 'faculty' || errorMessage.includes('Faculty') || errorMessage.includes('faculty')) {
                setTimeout(() => {
                    openFacultyRegistration();
                }, 1000);
            } else {
                setTimeout(() => {
                    openLogin('Researcher');
                }, 1000);
            }
    }

    
    
    // Initialize the page functionality
    initializePage();
    setupAdminModal();
    setupAdminForm();
    setupPasswordToggles(); // Add password toggles for all modals

});


function openLogin(role) {
    const modalTitle = document.getElementById("modalTitle");
    const roleField = document.getElementById("roleField");
    
    if (modalTitle) modalTitle.innerText = role + " Login";
    if (roleField) roleField.value = role;
    
    const modalEl = document.getElementById('loginModal');
    if (!modalEl) return;
    
    const loginModal = new bootstrap.Modal(modalEl);
    loginModal.show();
    
    // Update current user role
    currentUserRole = role.toLowerCase();
    console.log('Role set to:', currentUserRole);
    
    setTimeout(() => {
        const googleModalBtn = document.getElementById('googleModalBtn');
        if (googleModalBtn) {
            googleModalBtn.onclick = function() {
                const googleButton = document.querySelector('#googleButton .abcRioButton');
                if (googleButton) {
                    googleButton.click();
                } 
            };
        }
    }, 300);
}


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
            icon = 'info';
        } else if (msg.includes('rejected')) {
            title = 'Account Rejected';
            icon = 'warning';
        } else if (msg.includes('suspended')) {
            title = 'Account Suspended';
            icon = 'error';
        } else if (msg.includes('Invalid password')) {
            title = 'Invalid Password';
            icon = 'error';
        } else if (msg.includes('No account found')) {
            title = 'Account Not Found';
            icon = 'error';
        } else if (msg.includes('Invalid credentials for the selected role')) {
            title = 'Role Mismatch';
            icon = 'warning';
        }
        
        Swal.fire({
            title: title,
            html: msg,
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


// Forgot Password with Verification Code - USER CHOOSES PASSWORD
function setupForgotPassword() {
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const forgotPasswordLinkAdmin = document.getElementById('forgotPasswordLinkAdmin');
    
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            openForgotPasswordModal();
        });
    }

    // ADD THIS - Reuse the same function for admin
    if (forgotPasswordLinkAdmin) {
        forgotPasswordLinkAdmin.addEventListener('click', function(e) {
            e.preventDefault();
            openForgotPasswordModal();
        });
    }

    const backToLoginBtn = document.getElementById('backToLoginFromForgot');
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function() {
            document.body.classList.remove('forgot-password-open');
        });
    }
    
    // Setup forgot password form
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleForgotPasswordSubmit();
        });
    }
    
    // Setup verification form
    const verificationForm = document.getElementById('verificationForm');
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleVerificationSubmit();
        });
    }
    
    // Setup resend code button
    const resendCodeBtn = document.getElementById('resendCodeBtn');
    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            const email = document.getElementById('resetEmail')?.value;
            if (email) {
                sendVerificationCode(email, true);
            } else {
                Swal.fire('Error', 'No email found. Please start the process again.', 'error');
            }
        });
    }
    
    // Setup password visibility toggles
    setupPasswordToggles();
}

function setupPasswordToggles() {
    // Toggle new password visibility (password reset)
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const newPasswordInput = document.getElementById('newPassword');
    
    if (toggleNewPassword && newPasswordInput) {
        // Remove existing event listeners
        const newToggle = toggleNewPassword.cloneNode(true);
        toggleNewPassword.parentNode.replaceChild(newToggle, toggleNewPassword);
        
        newToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = newPasswordInput.type === 'password';
            newPasswordInput.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle confirm password visibility (password reset)
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (toggleConfirmPassword && confirmPasswordInput) {
        // Remove existing event listeners
        const confirmToggle = toggleConfirmPassword.cloneNode(true);
        toggleConfirmPassword.parentNode.replaceChild(confirmToggle, toggleConfirmPassword);
        
        confirmToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = confirmPasswordInput.type === 'password';
            confirmPasswordInput.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle login modal password visibility
    const toggleLoginPassword = document.getElementById('togglePasswordBtn');
    const loginPasswordInput = document.getElementById('password');
    
    if (toggleLoginPassword && loginPasswordInput) {
        // Remove existing event listeners
        const loginToggle = toggleLoginPassword.cloneNode(true);
        toggleLoginPassword.parentNode.replaceChild(loginToggle, toggleLoginPassword);
        
        loginToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = loginPasswordInput.type === 'password';
            loginPasswordInput.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle admin modal password visibility
    const toggleAdminPassword = document.getElementById('adminTogglePassword');
    const adminPasswordInput = document.getElementById('adminPassword');
    
    if (toggleAdminPassword && adminPasswordInput) {
        // Remove existing event listeners
        const adminToggle = toggleAdminPassword.cloneNode(true);
        toggleAdminPassword.parentNode.replaceChild(adminToggle, toggleAdminPassword);
        
        adminToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = adminPasswordInput.type === 'password';
            adminPasswordInput.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle student registration password visibility
    const regTogglePassword = document.getElementById('regTogglePassword');
    const regPassword = document.getElementById('regPassword');
    
    if (regTogglePassword && regPassword) {
        // Remove existing event listeners
        const regPassToggle = regTogglePassword.cloneNode(true);
        regTogglePassword.parentNode.replaceChild(regPassToggle, regTogglePassword);
        
        regPassToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = regPassword.type === 'password';
            regPassword.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle student registration confirm password visibility
    const regToggleConfirm = document.getElementById('regToggleConfirm');
    const regConfirmPassword = document.getElementById('regConfirmPassword');
    
    if (regToggleConfirm && regConfirmPassword) {
        // Remove existing event listeners
        const regConfirmToggle = regToggleConfirm.cloneNode(true);
        regToggleConfirm.parentNode.replaceChild(regConfirmToggle, regToggleConfirm);
        
        regConfirmToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = regConfirmPassword.type === 'password';
            regConfirmPassword.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle faculty registration password visibility
    const facTogglePassword = document.getElementById('facTogglePassword');
    const facPassword = document.getElementById('facPassword');
    
    if (facTogglePassword && facPassword) {
        // Remove existing event listeners
        const facPassToggle = facTogglePassword.cloneNode(true);
        facTogglePassword.parentNode.replaceChild(facPassToggle, facTogglePassword);
        
        facPassToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = facPassword.type === 'password';
            facPassword.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Toggle faculty registration confirm password visibility
    const facToggleConfirm = document.getElementById('facToggleConfirm');
    const facConfirmPassword = document.getElementById('facConfirmPassword');
    
    if (facToggleConfirm && facConfirmPassword) {
        // Remove existing event listeners
        const facConfirmToggle = facToggleConfirm.cloneNode(true);
        facToggleConfirm.parentNode.replaceChild(facConfirmToggle, facToggleConfirm);
        
        facConfirmToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isHidden = facConfirmPassword.type === 'password';
            facConfirmPassword.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }
    
    // Password strength indicator
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
        });
    }
    
    // Password match indicator
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            checkPasswordMatch();
        });
    }
}

function togglePasswordVisibility(input, icon) {
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }
}

function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    if (!strengthBar) return;
    
    const strength = checkPasswordStrength(password);
    
    strengthBar.className = 'password-strength';
    if (password.length > 0) {
        strengthBar.classList.add('strength-' + strength.level);
    }
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword')?.value || '';
    const confirmPassword = document.getElementById('confirmPassword')?.value || '';
    const matchText = document.getElementById('passwordMatch');
    
    if (!matchText) return;
    
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
}

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

async function handleVerificationSubmit() {
    const emailInput = document.getElementById('resetEmail');
    const verificationCodeInput = document.getElementById('verificationCode');
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const verifyCodeBtn = document.getElementById('verifyCodeBtn');
    
    if (!emailInput || !verificationCodeInput || !newPasswordInput || !confirmPasswordInput || !verifyCodeBtn) {
        Swal.fire('Error', 'Form elements not found. Please refresh the page.', 'error');
        return;
    }
    
    const email = emailInput.value;
    const verificationCode = verificationCodeInput.value.trim();
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    // Check if this is an admin context by looking at modal title or stored flag
    const modalTitle = document.querySelector('#passwordResetModal .modal-title');
    const isAdminReset = modalTitle && (
        modalTitle.innerHTML.includes('Admin') || 
        modalTitle.textContent.includes('Admin')
    );
    
    // Validate inputs
    if (!verificationCode || verificationCode.length !== 6 || !/^\d+$/.test(verificationCode)) {
        Swal.fire('Error', 'Please enter a valid 6-digit code containing only numbers', 'error');
        return;
    }
    
    if (!newPassword || newPassword.length < 8) {
        Swal.fire('Error', 'Password must be at least 8 characters long', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        Swal.fire('Error', 'Passwords do not match. Please check your entries.', 'error');
        return;
    }
    
    try {
        verifyCodeBtn.disabled = true;
        verifyCodeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Setting Password...';
        
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'verify_reset_code',
                'email': email,
                'verification_code': verificationCode,
                'new_password': newPassword,
                'is_admin': isAdminReset ? '1' : '0', // Make sure this is sent
                'csrf_token': getCsrfToken()
            })
        });

        const responseText = await response.text();
        console.log('Verify code response:', responseText);
        console.log('Admin reset flag sent:', isAdminReset);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                result = JSON.parse(jsonMatch[0]);
            } else {
                throw new Error('Server returned an invalid response');
            }
        }
        
        if (result.success) {
            // Show success step
            const step1 = document.getElementById('verificationStep1');
            const step2 = document.getElementById('verificationStep2');
            
            if (step1) step1.style.display = 'none';
            if (step2) step2.style.display = 'block';
            
        } else {
            throw new Error(result.message || 'Verification failed');
        }
        
    } catch (error) {
        console.error('Verification error:', error);
        Swal.fire({
            title: 'Error',
            text: error.message || 'Invalid verification code. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        
        verifyCodeBtn.disabled = false;
        verifyCodeBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Set New Password';
    }
}

async function debugCurrentTokens() {
    const email = document.getElementById('resetEmail')?.value;
    if (!email) {
        console.error('No email found for debug');
        return;
    }
    
    console.log('=== DEBUG CURRENT TOKENS ===');
    
    try {
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'debugCurrentTokens',
                'email': email,
                'csrf_token': getCsrfToken()
            })
        });
        
        const result = await response.text();
        console.log('Current tokens:', result);
    } catch (error) {
        console.error('Debug request failed:', error);
    }
}

async function debugDatabaseState() {
    const email = document.getElementById('resetEmail')?.value;
    if (!email) {
        console.error('No email found for debug');
        return;
    }
    
    console.log('=== DATABASE DEBUG ===');
    console.log('Checking database for email:', email);
    
    try {
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'debugDatabaseState',
                'email': email,
                'csrf_token': getCsrfToken()
            })
        });
        
        const result = await response.text();
        console.log('Database debug result:', result);
    } catch (error) {
        console.error('Debug request failed:', error);
    }
}


function openForgotPasswordModal() {
    try {
        // Reset the form
        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        if (forgotPasswordForm) {
            forgotPasswordForm.reset();
        }
        
        // Reset button state
        const sendCodeBtn = document.getElementById('sendCodeBtn');
        if (sendCodeBtn) {
            sendCodeBtn.disabled = false;
            sendCodeBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Verification Code';
        }
        
        // Add class to body to trigger the tint effect
        document.body.classList.add('forgot-password-open');
        
        // Show the modal
        const forgotPasswordModal = document.getElementById('forgotPasswordModal');
        if (forgotPasswordModal) {
            const modal = new bootstrap.Modal(forgotPasswordModal);
            
            // Remove the tint when forgot password modal is closed
            forgotPasswordModal.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('forgot-password-open');
            });
            
            modal.show();
        }
    } catch (error) {
        console.error('Error opening forgot password modal:', error);
        Swal.fire('Error', 'Failed to open password reset. Please try again.', 'error');
    }
}

function openVerificationModal(email, generatedPassword) {
    try {
        // Set email and password in verification modal
        const resetEmail = document.getElementById('resetEmail');
        const generatedPasswordField = document.getElementById('generatedPassword');
        
        if (resetEmail) resetEmail.value = email;
        if (generatedPasswordField) generatedPasswordField.value = generatedPassword;
        
        // Reset verification form
        const verificationForm = document.getElementById('verificationForm');
        if (verificationForm) {
            verificationForm.reset();
        }
        
        // Show step 1, hide step 2
        const step1 = document.getElementById('verificationStep1');
        const step2 = document.getElementById('verificationStep2');
        
        if (step1) step1.style.display = 'block';
        if (step2) step2.style.display = 'none';
        
        // Reset button states
        const verifyCodeBtn = document.getElementById('verifyCodeBtn');
        const resendCodeBtn = document.getElementById('resendCodeBtn');
        
        if (verifyCodeBtn) {
            verifyCodeBtn.disabled = false;
            verifyCodeBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verify Code';
        }
        
        if (resendCodeBtn) {
            resendCodeBtn.disabled = false;
            resendCodeBtn.innerHTML = '<i class="fas fa-redo me-2"></i>Resend Code';
        }
        
        // Show the modal
        const passwordResetModal = document.getElementById('passwordResetModal');
        if (passwordResetModal) {
            const modal = new bootstrap.Modal(passwordResetModal);
            modal.show();
            
            // Focus on code input
            setTimeout(() => {
                const verificationCodeInput = document.getElementById('verificationCode');
                if (verificationCodeInput) {
                    verificationCodeInput.focus();
                }
            }, 500);
        }
    } catch (error) {
        console.error('Error opening verification modal:', error);
        Swal.fire('Error', 'Failed to open verification. Please try again.', 'error');
    }
}

async function handleForgotPasswordSubmit() {
    const emailInput = document.getElementById('forgotEmail');
    const sendCodeBtn = document.getElementById('sendCodeBtn');
    
    if (!emailInput || !sendCodeBtn) {
        Swal.fire('Error', 'Form elements not found. Please refresh the page.', 'error');
        return;
    }
    
    const email = emailInput.value.trim();
    
    if (!email) {
        Swal.fire('Error', 'Please enter your email address', 'error');
        return;
    }
    
    if (!email.endsWith('@usep.edu.ph')) {
        Swal.fire('Error', 'Please enter a valid USeP email address (@usep.edu.ph)', 'error');
        return;
    }
    
    await sendVerificationCode(email, false);
}

async function sendVerificationCode(email, isResend = false) {
    const sendCodeBtn = document.getElementById('sendCodeBtn');
    const resendCodeBtn = document.getElementById('resendCodeBtn');
    
    try {
        if (!isResend) {
            if (sendCodeBtn) {
                sendCodeBtn.disabled = true;
                sendCodeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            }
        } else {
            if (resendCodeBtn) {
                resendCodeBtn.disabled = true;
                resendCodeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resending...';
            }
        }
        
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'send_verification_code',
                'email': email,
                'csrf_token': getCsrfToken()
            })
        });

        const responseText = await response.text();
        console.log('Send code response:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            // Try to extract JSON from debug output
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                result = JSON.parse(jsonMatch[0]);
            } else {
                throw new Error('Server returned an invalid response');
            }
        }
        
        if (result.success) {
            if (!isResend) {
                // Close forgot password modal
                const forgotModal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                if (forgotModal) {
                    forgotModal.hide();
                }
                
                // Open verification modal
                setTimeout(() => {
                    openVerificationModal(email, result.generated_password);
                }, 300);
                
                Swal.fire({
                    title: 'Code Sent!',
                    text: 'Verification code sent to your email',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    title: 'Code Sent!',
                    text: 'A new verification code has been sent to your email.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                if (resendCodeBtn) {
                    resendCodeBtn.disabled = false;
                    resendCodeBtn.innerHTML = '<i class="fas fa-redo me-2"></i>Resend Code';
                }
            }
        } else {
            throw new Error(result.message || 'Failed to send verification code');
        }
        
    } catch (error) {
        console.error('Verification code error:', error);
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to send verification code. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        
        // Reset button states
        if (sendCodeBtn) {
            sendCodeBtn.disabled = false;
            sendCodeBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Verification Code';
        }
        
        if (resendCodeBtn) {
            resendCodeBtn.disabled = false;
            resendCodeBtn.innerHTML = '<i class="fas fa-redo me-2"></i>Resend Code';
        }
    }
}

function getCsrfToken() {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const verificationForm = document.getElementById('verificationForm');
    
    if (forgotPasswordForm) {
        const tokenInput = forgotPasswordForm.querySelector('input[name="csrf_token"]');
        if (tokenInput) return tokenInput.value;
    }
    
    if (verificationForm) {
        const tokenInput = verificationForm.querySelector('input[name="csrf_token"]');
        if (tokenInput) return tokenInput.value;
    }
    
    return '';
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

function setupCustomGoogleButton() {
    const googleModalBtn = document.getElementById('googleModalBtn');
    if (googleModalBtn) {
        googleModalBtn.addEventListener('click', function() {
            handleGoogleButtonClick();
        });
        console.log('Custom Google button setup completed');
    } else {
        console.error('Google modal button not found');
    }
}


let googleSignInInitialized = false;
const googleClientId = '650560808203-6v58k39kme14720dh0u78chb4i33chre.apps.googleusercontent.com'; // Use only ONE client ID

function initializeGoogleSignIn() {
    
    
    // Check if already initialized
    if (googleSignInInitialized) {
        console.log('Google Sign-In already initialized');
        return;
    }
    
    // Remove any existing Google scripts to avoid conflicts
    const existingScripts = document.querySelectorAll('script[src*="accounts.google.com"]');
    existingScripts.forEach(script => script.remove());
    
    // Load Google Identity Services
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = () => {
        
        initializeGSI();
    };
    script.onerror = (error) => {
        console.error('Failed to load Google Identity Services:', error);
        setupManualOAuth();
    };
    document.head.appendChild(script);
}



    function initializeGSI() {
        if (typeof google === 'undefined' || !google.accounts) {
            console.error('Google accounts not available');
            setupManualOAuth();
            return;
        }
        
        try {
            
            
            google.accounts.id.initialize({
                client_id: googleClientId,
                callback: handleCredentialResponse,
                auto_select: false,
                cancel_on_tap_outside: true
            });
            
            googleSignInInitialized = true;
            
            
        } catch (error) {
            console.error('GSI initialization failed:', error);
            setupManualOAuth();
        }
    }

    function setupCustomGoogleButton() {
        const googleModalBtn = document.getElementById('googleModalBtn');
        if (googleModalBtn) {
            // Remove any existing event listeners
            const newBtn = googleModalBtn.cloneNode(true);
            googleModalBtn.parentNode.replaceChild(newBtn, googleModalBtn);
            
            // Add new event listener
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleGoogleSignIn();
            });
            
            
        } else {
            console.error('Google modal button not found');
        }
    }

    function handleGoogleSignIn() {
        
        
        const customBtn = document.getElementById('googleModalBtn');
        
        // Show loading state
        if (customBtn) {
            customBtn.disabled = true;
            customBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Connecting...';
        }
        
        // Check if Google Sign-In is available
        if (!googleSignInInitialized || typeof google === 'undefined') {
            
            showGoogleSignInPopup();
            return;
        }
        
        try {
            attemptGoogleButtonRender();
        } catch (error) {
            console.error('Google Sign-In failed:', error);
            showGoogleSignInPopup();
        }
    }

    function attemptGoogleButtonRender() {
        // Create a hidden container for the Google button
        const containerId = 'hiddenGoogleButtonContainer';
        let container = document.getElementById(containerId);
        
        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.style.position = 'fixed';
            container.style.left = '-9999px';
            container.style.top = '-9999px';
            container.style.zIndex = '-9999';
            document.body.appendChild(container);
        }
        
        // Clear previous button
        container.innerHTML = '';
        
        // Render Google button
        google.accounts.id.renderButton(container, {
            type: 'standard',
            theme: 'outline',
            text: 'signin_with',
            size: 'large',
            logo_alignment: 'left',
            width: 400
        });
        
        // Wait for button to render and click it
        setTimeout(() => {
            const googleButton = container.querySelector('div[role="button"]');
            if (googleButton) {
                
                googleButton.click();
                
                // Reset button after click
                setTimeout(resetGoogleButton, 2000);
            } else {
                console.error('Google button not rendered');
                showGoogleSignInPopup();
            }
        }, 100);
    }

    function showGoogleSignInPopup() {
        console.log('Showing Google Sign-In popup instructions');
        
        Swal.fire({
            title: 'Sign in with Google',
            html: `
                <div class="text-start">
                    <p><strong>To sign in with your USeP email:</strong></p>
                    <ol>
                        <li>Click the "Open Google Sign-In" button below</li>
                        <li>Sign in with your <strong style="color: #d93025;">@usep.edu.ph</strong> email</li>
                        <li>You'll be redirected back automatically</li>
                    </ol>
                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Note:</strong> Make sure you're using your University of Southeastern Philippines email account.
                        </small>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fab fa-google me-2"></i> Open Google Sign-In',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                startManualGoogleOAuth();
            } else {
                resetGoogleButton();
            }
        });
    }

    function startManualGoogleOAuth() {
        const redirectUri = encodeURIComponent(window.location.origin);
        const scope = encodeURIComponent('email profile openid');
        
        const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?` +
                       `client_id=${googleClientId}&` +
                       `redirect_uri=${redirectUri}&` +
                       `response_type=code&` +
                       `scope=${scope}&` +
                       `access_type=online&` +
                       `prompt=select_account`;
        
        console.log('Redirecting to Google OAuth');
        window.location.href = authUrl;
    }

    function setupManualOAuth() {
        console.log('Setting up manual OAuth flow');
        const googleModalBtn = document.getElementById('googleModalBtn');
        if (googleModalBtn) {
            googleModalBtn.onclick = function() {
                startManualGoogleOAuth();
            };
        }
    }

    function handleCredentialResponse(response) {
        try {
            const responsePayload = parseJwt(response.credential);
            const userEmail = responsePayload.email;
            const userName = responsePayload.name;
            
            console.log('Google authentication for:', userEmail);
            console.log('Current user role:', currentUserRole);
    
            // Use the global role variable instead of relying on the form field
            sendGoogleCredentialToBackend(response.credential, userEmail, userName, currentUserRole);
            
        } catch (error) {
            console.error('Error processing Google credential:', error);
        }
    }

    function resetGoogleButton() {
        const customBtn = document.getElementById('googleModalBtn');
        if (customBtn) {
            customBtn.disabled = false;
            customBtn.innerHTML = '<i class="fab fa-google me-2"></i> Sign in with USeP Email';
        }
    }
    














// Send Google credential to backend with role validation
async function sendGoogleCredentialToBackend(credential, userEmail, userName, selectedRole) {
    try {
        // Show loading state
        Swal.fire({
            title: 'Signing In...',
            text: 'Please wait while we authenticate your account',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        console.log('Sending Google authentication with role:', selectedRole);
        console.log('Action should be: googleLogin');

        // Send to AuthController
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'include',
            body: new URLSearchParams({
                'action': 'googleLogin', // ← MAKE SURE THIS IS CORRECT
                'credential': credential,
                'email': userEmail,
                'name': userName,
                'role': selectedRole,
                'csrf_token': '<?php echo $_SESSION["csrf_token"] ?? ""; ?>'
            })
        });

        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let result;
        
        // Handle empty or malformed responses
        if (!responseText || responseText.trim() === '') {
            throw new Error('Server returned empty response');
        }
        
        // Try to parse as JSON
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.log('Raw response that failed to parse:', responseText);
            
            // Try to extract JSON from the response if there's extra output
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    result = JSON.parse(jsonMatch[0]);
                    console.log('Extracted JSON from response:', result);
                } catch (extractError) {
                    console.error('Could not extract JSON:', extractError);
                    throw new Error('Server returned invalid response format');
                }
            } else {
                throw new Error('Server returned non-JSON response: ' + responseText.substring(0, 100));
            }
        }
        
        handleGoogleAuthResult(result);
        
    } catch (error) {
        console.error('Google authentication error:', error);
        Swal.fire({
            title: 'Authentication Failed',
            text: error.message || 'Failed to authenticate. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        
        // Reset Google button
        resetGoogleButton();
    }
}

function handleGoogleAuthResult(result) {
    if (result.success) {
        console.log('Google login successful, redirecting to:', result.redirect_url);
        window.location.href = result.redirect_url || '../../app/Views/User/userViewPage.php';
    } else {
        // Show specific error message for role mismatch
        if (result.message.includes('registered as') && result.message.includes('Please use')) {
            Swal.fire({
                title: 'Role Mismatch',
                html: result.message + '<br><br><strong>Please:</strong><br>1. Go back to login<br>2. Select the correct role option<br>3. Try Google Sign-In again',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Authentication Failed',
                text: result.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
}

function handleAuthResult(result) {
    if (result.success) {
        Swal.fire({
            title: 'Success!',
            text: result.message,
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = result.redirect_url || '../../app/Views/User/userViewPage.php';
        });
    } else {
        throw new Error(result.message || 'Authentication failed');
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


// Sign out function
function signOut() {
    if (typeof google !== 'undefined' && google.accounts.id) {
        google.accounts.id.disableAutoSelect();
        google.accounts.id.revoke((done) => {
            console.log('Google Sign-Out completed');
        });
    }
}


//Admin Google-sign in
function setupAdminGoogleButton() {
    const adminGoogleBtn = document.getElementById('adminGoogleBtn');
    if (adminGoogleBtn) {
        adminGoogleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            handleAdminGoogleSignIn();
        });
        console.log('Admin Google button setup completed');
    }
}

function handleAdminGoogleSignIn() {
    const adminGoogleBtn = document.getElementById('adminGoogleBtn');
    
    // Show loading state
    if (adminGoogleBtn) {
        adminGoogleBtn.disabled = true;
        adminGoogleBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Connecting...';
    }
    
    // Check if Google Sign-In is available
    if (!googleSignInInitialized || typeof google === 'undefined') {
        showAdminGoogleSignInPopup();
        return;
    }
    
    try {
        attemptAdminGoogleButtonRender();
    } catch (error) {
        console.error('Admin Google Sign-In failed:', error);
        showAdminGoogleSignInPopup();
    }
}

function attemptAdminGoogleButtonRender() {
    // Create a hidden container for the Google button
    const containerId = 'hiddenAdminGoogleButtonContainer';
    let container = document.getElementById(containerId);
    
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.style.position = 'fixed';
        container.style.left = '-9999px';
        container.style.top = '-9999px';
        container.style.zIndex = '-9999';
        document.body.appendChild(container);
    }
    
    // Clear previous button
    container.innerHTML = '';
    
    // Render Google button with custom callback for admin
    google.accounts.id.initialize({
        client_id: googleClientId,
        callback: handleAdminCredentialResponse, // Use admin-specific callback
        auto_select: false,
        cancel_on_tap_outside: true
    });
    
    google.accounts.id.renderButton(container, {
        type: 'standard',
        theme: 'outline',
        text: 'signin_with',
        size: 'large',
        logo_alignment: 'left',
        width: 400
    });
    
    // Wait for button to render and click it
    setTimeout(() => {
        const googleButton = container.querySelector('div[role="button"]');
        if (googleButton) {
            googleButton.click();
            setTimeout(resetAdminGoogleButton, 2000);
        } else {
            console.error('Admin Google button not rendered');
            showAdminGoogleSignInPopup();
        }
    }, 100);
}

//admin specific creds
function handleAdminCredentialResponse(response) {
    console.log('Admin Google credential received');
    
    try {
        const responsePayload = parseJwt(response.credential);
        const userEmail = responsePayload.email;
        const userName = responsePayload.name;
        
        console.log('Admin Google authentication for:', userEmail);

        // Send to backend for admin authentication
        sendAdminGoogleCredentialToBackend(response.credential, userEmail, userName);
        
    } catch (error) {
        console.error('Error processing Admin Google credential:', error);
        Swal.fire({
            title: 'Admin Authentication Error',
            text: 'Failed to process Google sign-in. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        resetAdminGoogleButton();
    }
}

async function sendAdminGoogleCredentialToBackend(credential, userEmail, userName) {
    try {
        // Show loading state
        Swal.fire({
            title: 'Admin Authentication...',
            text: 'Please wait while we verify your admin credentials',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send to AdminController instead of AuthController
        const response = await fetch('../../Controllers/AdminController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'include',
            body: new URLSearchParams({
                'action': 'adminGoogleLogin',
                'credential': credential,
                'email': userEmail,
                'name': userName,
                'csrf_token': '<?php echo $_SESSION["csrf_token"] ?? ""; ?>'
            })
        });

        const responseText = await response.text();
        console.log('Admin Google raw response:', responseText);
        
        // Clean the response text - remove any whitespace or HTML tags
        const cleanResponse = responseText.trim();
        
        if (!cleanResponse) {
            throw new Error('Empty response from server');
        }
        
        let result;
        
        try {
            // Try to parse as JSON directly
            result = JSON.parse(cleanResponse);
        } catch (parseError) {
            console.log('Direct JSON parse failed, trying to extract JSON from response:', parseError);
            
            // Try to extract JSON from the response
            const jsonMatch = cleanResponse.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    result = JSON.parse(jsonMatch[0]);
                } catch (extractError) {
                    console.log('JSON extraction failed:', extractError);
                    throw new Error('Invalid server response format');
                }
            } else {
                throw new Error('Server returned an invalid response: ' + cleanResponse.substring(0, 100));
            }
        }
        
        handleAdminAuthResult(result);
        
    } catch (error) {
        console.error('Admin Google authentication error:', error);
        Swal.fire({
            title: 'Admin Authentication Failed',
            text: error.message || 'Failed to authenticate as admin. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        resetAdminGoogleButton();
    }
}

function handleAdminAuthResult(result) {
    if (result.success) {
        // Redirect to admin dashboard immediately without showing success message
        window.location.href = result.redirect_url || '../../app/Views/Admin/adminDashboard.php';
    } else {
        // Only show SweetAlert for errors
        Swal.fire({
            title: 'Admin Access Denied',
            text: result.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}


function showAdminGoogleSignInPopup() {
    console.log('Showing Admin Google Sign-In popup instructions');
    
    Swal.fire({
        title: 'Admin Sign in with Google',
        html: `
            <div class="text-start">
                <p><strong>To sign in as admin with Google:</strong></p>
                <ol>
                    <li>Click the "Open Google Sign-In" button below</li>
                    <li>Sign in with your <strong style="color: #d93025;">authorized admin Google account</strong></li>
                    <li>You'll be redirected back automatically</li>
                </ol>
                <div class="alert alert-warning mt-3">
                    <small>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Note:</strong> Only pre-authorized admin accounts with proper User_ID will be granted access.
                    </small>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fab fa-google me-2"></i> Open Google Sign-In',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            startManualAdminGoogleOAuth();
        } else {
            resetAdminGoogleButton();
        }
    });
}

function startManualAdminGoogleOAuth() {
    const redirectUri = encodeURIComponent(window.location.origin);
    const scope = encodeURIComponent('email profile openid');
    
    const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?` +
                   `client_id=${googleClientId}&` +
                   `redirect_uri=${redirectUri}&` +
                   `response_type=code&` +
                   `scope=${scope}&` +
                   `access_type=online&` +
                   `prompt=select_account`;
    
    console.log('Redirecting to Google OAuth for admin');
    window.location.href = authUrl;
}

function resetAdminGoogleButton() {
    const adminGoogleBtn = document.getElementById('adminGoogleBtn');
    if (adminGoogleBtn) {
        adminGoogleBtn.disabled = false;
        adminGoogleBtn.innerHTML = '<i class="fab fa-google me-2"></i> Sign in with Admin Google Account';
    }
}

// Admin Google credential handler
async function handleAdminGoogleCredentialResponse(response) {
    try {
        const responsePayload = parseJwt(response.credential);
        const userEmail = responsePayload.email;
        const userName = responsePayload.name;
        
        console.log('Admin Google authentication for:', userEmail);

        // Show loading state
        Swal.fire({
            title: 'Admin Authentication...',
            text: 'Please wait while we verify your admin credentials',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send to AuthController with admin role
        const response = await fetch('../../Controllers/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'include',
            body: new URLSearchParams({
                'action': 'google_login',
                'credential': response.credential,
                'email': userEmail,
                'name': userName,
                'role': 'admin', // Important: Set role to admin
                'csrf_token': '<?php echo $_SESSION["csrf_token"] ?? ""; ?>'
            })
        });

        const responseText = await response.text();
        console.log('Admin Google raw response:', responseText);
        
        // Handle response (similar to regular Google login)
        if (responseText.includes('PHPMailer:') || 
            responseText.includes('<br>') ||
            responseText.includes('SMTP') ||
            responseText.trim().startsWith('PHPMailer:')) {
            
            const jsonMatch = responseText.match(/\{.*\}/s);
            if (jsonMatch) {
                const result = JSON.parse(jsonMatch[0]);
                handleAdminAuthResult(result);
            } else {
                throw new Error('Server returned debug output instead of JSON');
            }
        } else {
            const result = JSON.parse(responseText);
            handleAdminAuthResult(result);
        }
        
    } catch (error) {
        console.error('Admin Google authentication error:', error);
        Swal.fire({
            title: 'Admin Authentication Failed',
            text: error.message || 'Failed to authenticate as admin. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        resetAdminGoogleButton();
    }
}




let currentUserRole = 'student';

// Initialize when document is ready
function initializePage() {
    
    setupRoleTracking();
    initializeGoogleSignIn();
    setupCustomGoogleButton();
    setupAdminGoogleButton();
    setupForgotPassword();

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

    // REMOVED THE DUPLICATE openLogin FUNCTION FROM HERE

    function setupRoleTracking() {
        const researcherBtn = document.getElementById("researcherBtn");
        const facultyBtn = document.getElementById("facultyBtn");
        
        if (researcherBtn) {
            researcherBtn.addEventListener("click", function() {
                currentUserRole = 'student'; // or 'researcher' depending on your system
                console.log('Role set to:', currentUserRole);
            });
        }
        
        if (facultyBtn) {
            facultyBtn.addEventListener("click", function() {
                currentUserRole = 'faculty';
                console.log('Role set to:', currentUserRole);
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
                'facFirstName', 'facLastName',  
                'facDepartment', 
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
            
            const username = document.getElementById('adminUsername').value.trim();
            const password = document.getElementById('adminPassword').value;
            
            if (!username || !password) {
                Swal.fire('Error', 'Please enter both Admin ID and password', 'error');
                return;
            }
            
            Swal.fire({
                title: 'Authenticating...',
                text: 'Please wait while we verify your admin credentials',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Admin login response:', data);
                this.submit();
            })
            .catch(error => {
                console.error('Admin login error:', error);
                this.submit();
            });
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

function showLoginAttemptAlert(message, attemptsRemaining, isLocked, lockoutSeconds) {
    if (isLocked) {
        const minutes = Math.ceil(lockoutSeconds / 60);
        Swal.fire({
            title: 'Account Locked',
            html: `
                <div class="text-center">
                    <i class="fas fa-lock fa-3x text-warning mb-3"></i>
                    <p>${message}</p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Time remaining:</strong> ${minutes} minute(s)
                    </div>
                    <small class="text-muted">This is a security measure to protect your account.</small>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107'
        });
    } else if (attemptsRemaining < 3) {
        Swal.fire({
            title: 'Login Failed',
            html: `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                    <p>${message}</p>
                    <div class="attempts-warning mt-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Attempts remaining:</strong> ${attemptsRemaining}
                    </div>
                    <small class="text-muted">After 3 failed attempts, your account will be locked for 5 minutes.</small>
                </div>
            `,
            icon: 'error',
            confirmButtonText: 'Try Again',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                if (loginEmail) {
                    const emailInput = document.getElementById('username');
                    const passwordInput = document.getElementById('password');
                    if (emailInput && passwordInput) {
                        emailInput.value = loginEmail;
                        passwordInput.focus();
                    }
                }
            }
        });
    } else {
        showLoginErrorAlert(message);
    }
}