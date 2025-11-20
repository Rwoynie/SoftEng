
class ProfileManager {
    constructor() {
        this.profileContainer = document.getElementById('profileContainer');
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.currentStep = 'request'; // 'request', 'verify', 'reset'
        this.resetData = {};
        this.init();
    }

    init() {
        this.loadProfileData();
        this.attachEventListeners();
    }

    /**
     * Attach event listeners for profile actions
     */
    attachEventListeners() {
        // Change Password Button
        const changePasswordBtn = document.querySelector('.btn-primary');
        if (changePasswordBtn) {
            changePasswordBtn.innerHTML = '<i class="fa fa-key" aria-hidden="true"></i> Change Password';
            changePasswordBtn.addEventListener('click', () => this.showChangePasswordModal());
        }

        // Logout Button
        const logoutBtn = document.getElementById('logoutHeaderIcon');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }

        // Profile Image Upload
        this.attachProfileImageUpload();
    }

    /**
 * Attach profile image upload functionality
 */
attachProfileImageUpload() {
    const profileImageInput = document.getElementById('profileImage');
    const profileImage = document.querySelector('.profile-image');
    
    if (profileImageInput && profileImage) {
        profileImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.handleImageUpload(file);
            }
        });

        // Make the image clickable
        profileImage.style.cursor = 'pointer';
        profileImage.addEventListener('click', () => {
            profileImageInput.click();
        });
    }
}

/**
 * Handle image upload
 */
async handleImageUpload(file) {
    console.log('Starting image upload...', file);
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        console.log('Invalid file type:', file.type);
        this.showError('Please select a valid image file (JPEG, PNG, GIF)');
        return;
    }

    // Validate file size (max 5MB)
    const maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
        console.log('File too large:', file.size);
        this.showError('Image size should be less than 5MB');
        return;
    }

    try {
        const profileImage = document.querySelector('.profile-image');
        const originalSrc = profileImage.src;
        
        // Show loading state
        profileImage.style.opacity = '0.5';
        profileImage.style.transition = 'opacity 0.3s ease';

        const formData = new FormData();
        formData.append('profile_image', file);
        formData.append('csrf_token', this.csrfToken);

        console.log('Sending request to server...');
        const response = await fetch('../../../app/Controllers/ProfileController.php?action=upload_profile_image', {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Raw response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
            console.log('Parsed data:', data);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response that failed to parse:', responseText);
            throw new Error('Server returned invalid response: ' + responseText.substring(0, 100));
        }

        if (data.success) {
            console.log('Upload successful');
            
            // Reload profile data to get the updated image URL
            await this.loadProfileData();
            
            this.showSuccessMessage('Profile image updated successfully!');
            
        } else {
            console.log('Upload failed:', data.message);
            this.showError(data.message || 'Failed to upload image');
            profileImage.style.opacity = '1';
        }

    } catch (error) {
        console.error('Error uploading image:', error);
        this.showError('Error uploading image: ' + error.message);
        
        const profileImage = document.querySelector('.profile-image');
        profileImage.style.opacity = '1';
    }
}


    /**
     * Load profile data from server
     */
    async loadProfileData() {
        try {
            console.log('Loading profile data...');
            console.log('CSRF Token:', this.csrfToken);
            
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=get_profile', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'same-origin'
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log('Raw response length:', responseText.length);
            
            if (!responseText.trim()) {
                throw new Error('Empty response from server');
            }
            
            // Check for HTML errors or PHP warnings
            if (responseText.trim().startsWith('<') || responseText.includes('<b>Warning</b>') || responseText.includes('<b>Fatal error</b>')) {
                console.error('Server returned HTML error');
                throw new Error('Server configuration error. Please check PHP error logs.');
            }
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response that failed to parse (first 200 chars):', responseText.substring(0, 200));
                throw new Error('Server returned invalid data format');
            }
            
            if (data.success) {
                console.log('Profile data loaded successfully');
                this.populateProfileData(data.data);
            } else {
                console.error('Server returned error:', data.message);
                this.showError('Failed to load profile data: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            this.showError('Error loading profile data: ' + error.message);
        }
    }
    
    /**
     * Populate profile data in the UI
     */
    populateProfileData(profileData) {
        console.log('Profile data received:', profileData);
        
        if (!profileData) {
            this.showError('No profile data received');
            return;
        }
        
        // Personal Information
        this.setElementValue('profileImage', profileData.Profile_Pic);
        this.setElementValue('FullName', profileData.Full_Name || 'N/A');
        this.setElementValue('email', profileData.Email || 'N/A');
        this.setElementValue('userID', profileData.User_ID || 'N/A');
        this.setElementValue('roleHeader', profileData.role_display || 'N/A');
        this.setElementValue('course', profileData.Course || 'N/A');
        this.setElementValue('department', profileData.Department || 'N/A');
    
        // Account Settings
        this.setElementValue('member', profileData.member_since || 'N/A');
        this.setElementValue('lastlogin', profileData.last_login_formatted || 'N/A');
        this.setElementValue('acc_status', profileData.status_display || 'N/A');
        this.setElementValue('role', profileData.role_display || 'N/A');
    
        // Update status color
        this.updateStatusColor(profileData.Acc_Status);
    
        // Update profile name and title in header
        this.updateProfileHeader(profileData);
        
        // PROVEN WORKING METHOD: Update profile image with proper loading
        this.updateProfileImage(profileData.Profile_Pic);
    }
    
    /**
     * Update profile image with proper error handling
     */
    updateProfileImage(imageUrl) {
        const profileImage = document.querySelector('.profile-image');
        if (!profileImage) {
            console.warn('Profile image element not found');
            return;
        }
        
        console.log('Updating profile image with URL:', imageUrl);
        
        // Create test image to verify it loads
        const testImage = new Image();
        
        testImage.onload = () => {
            console.log('✅ Profile image loaded successfully');
            profileImage.src = imageUrl;
            profileImage.style.opacity = '1';
        };
        
        testImage.onerror = () => {
            console.error('❌ Profile image failed to load, using default');
            // Use default image with cache busting
            profileImage.src = '../../../resources/Images/profile.png?t=' + new Date().getTime();
            profileImage.style.opacity = '1';
        };
        
        // Show loading state
        profileImage.style.opacity = '0.5';
        profileImage.style.transition = 'opacity 0.3s ease';
        
        // Add cache busting to ensure fresh image
        const cacheBustedUrl = imageUrl + (imageUrl.includes('?') ? '&' : '?') + 't=' + new Date().getTime();
        testImage.src = cacheBustedUrl;
    }

    /**
     * Set element value by data attribute
     */
    setElementValue(dataAttribute, value) {
        const element = document.querySelector(`[data-value="${dataAttribute}"]`);
        if (element) {
            element.textContent = value;
        } else {
            console.warn(`Element with data-value="${dataAttribute}" not found`);
        }
    }

    /**
     * Update status color based on account status
     */
    updateStatusColor(status) {
        const statusElement = document.querySelector('[data-value="acc_status"]');
        if (statusElement) {
            statusElement.classList.remove('status-active', 'status-pending', 'status-rejected');
            
            switch (status) {
                case 'approved':
                    statusElement.classList.add('status-active');
                    break;
                case 'pending':
                    statusElement.classList.add('status-pending');
                    break;
                case 'rejected':
                    statusElement.classList.add('status-rejected');
                    break;
            }
        }
    }

    /**
     * Update profile header with user information
     */
    updateProfileHeader(profileData) {
        const profileName = document.querySelector('.profile-name');
        const profileTitle = document.querySelector('.profile-title');

        if (profileName) {
            profileName.textContent = profileData.Full_Name || 'User';
        }

        if (profileTitle) {
            profileTitle.textContent = `${profileData.role_display || 'User'}`;
        }
    }

    /**
     * Attach event listeners for profile actions
     */
    attachEventListeners() {
        // Change Password Button
        const changePasswordBtn = document.querySelector('.btn-primary');
        if (changePasswordBtn) {
            changePasswordBtn.innerHTML = '<i class="fa fa-key" aria-hidden="true"></i> Change Password';
            changePasswordBtn.addEventListener('click', () => this.showChangePasswordModal());
        }

        // Logout Button
        const logoutBtn = document.getElementById('logoutHeaderIcon');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }
    }

    /**
     * Show change password modal with enhanced UI and eye icons
     */
    showChangePasswordModal() {
        Swal.fire({
            title: '<h3 style="color: #2c3e50; margin: 0;">Change Password</h3>',
            html: `
                <div style="text-align: left;">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Current Password</label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="currentPassword" 
                                placeholder="Enter your current password" 
                                style="width: 100%; padding: 12px 45px 12px 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3498db'"
                                onblur="this.style.borderColor='#e9ecef'"
                            >
                            <button 
                                type="button" 
                                class="toggle-password" 
                                data-target="currentPassword"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 4px;"
                            >
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">New Password</label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="newPassword" 
                                placeholder="Enter new password (min. 8 characters)" 
                                style="width: 100%; padding: 12px 45px 12px 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3498db'"
                                onblur="this.style.borderColor='#e9ecef'"
                            >
                            <button 
                                type="button" 
                                class="toggle-password" 
                                data-target="newPassword"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 4px;"
                            >
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <small style="color: #6c757d; font-size: 12px; margin-top: 0.25rem; display: block;">
                            Password must be at least 8 characters long
                        </small>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Confirm New Password</label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="confirmPassword" 
                                placeholder="Re-enter your new password" 
                                style="width: 100%; padding: 12px 45px 12px 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; transition: all 0.3s;"
                                onfocus="this.style.borderColor='#3498db'"
                                onblur="this.style.borderColor='#e9ecef'"
                            >
                            <button 
                                type="button" 
                                class="toggle-password" 
                                data-target="confirmPassword"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #6c757d; cursor: pointer; padding: 4px;"
                            >
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; margin-top: 1rem;">
                        <p style="margin: 0; font-size: 12px; color: #6c757d;">
                            <i class="fa fa-info-circle" style="color: #3498db; margin-right: 5px;"></i>
                            After clicking "Request PIN", you'll receive a verification code via email.
                        </p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-paper-plane" style="margin-right: 5px;"></i> Request PIN',
            cancelButtonText: '<i class="fa fa-times" style="margin-right: 5px;"></i> Cancel',
            confirmButtonColor: '#3498db',
            cancelButtonColor: '#6c757d',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;

                // Validation
                if (!currentPassword || !newPassword || !confirmPassword) {
                    Swal.showValidationMessage('Please fill in all password fields');
                    return false;
                }

                if (newPassword.length < 8) {
                    Swal.showValidationMessage('Password must be at least 8 characters long');
                    return false;
                }

                if (newPassword !== confirmPassword) {
                    Swal.showValidationMessage('New passwords do not match');
                    return false;
                }

                // Store data for later use
                this.resetData = {
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                };

                return this.requestPin(currentPassword);
            },
            didOpen: () => {
                // Add event listeners for eye icons
                this.initializePasswordToggles();
            },
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                confirmButton: 'custom-swal-confirm-btn',
                cancelButton: 'custom-swal-cancel-btn'
            },
            width: '500px',
            padding: '2rem'
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    this.showPinVerificationModal();
                } else {
                    this.showError(result.value.message || 'Failed to request PIN');
                }
            }
        });
    }

    /**
     * Initialize password visibility toggles
     */
    initializePasswordToggles() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = button.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = button.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.className = 'far fa-eye-slash';
                    button.style.color = '#3498db';
                } else {
                    passwordInput.type = 'password';
                    icon.className = 'far fa-eye';
                    button.style.color = '#6c757d';
                }
                
                // Add focus back to input for better UX
                passwordInput.focus();
            });
            
            // Add hover effects
            button.addEventListener('mouseenter', () => {
                button.style.color = '#3498db';
            });
            
            button.addEventListener('mouseleave', () => {
                const targetId = button.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                if (passwordInput.type === 'password') {
                    button.style.color = '#6c757d';
                }
            });
        });
    }



    /**
     * Attach events to change password modal
     */
    attachChangePasswordModalEvents() {
        const modal = document.getElementById('changePasswordModal');
        const closeBtn = modal.querySelector('.modal-close');
        const cancelBtn = modal.querySelector('#cancelChangePassword');
        const requestPinBtn = modal.querySelector('#requestPin');

        const closeModal = () => modal.remove();

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        requestPinBtn.addEventListener('click', () => this.requestPin());
    }

    /**
     * Request PIN for password change
     */
    async requestPin(currentPassword) {
        try {
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=request_pin', {
                method: 'POST',
                body: new URLSearchParams({
                    csrf_token: this.csrfToken,
                    current_password: currentPassword
                })
            });
    
            const responseText = await response.text();
            let data;
            
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Server returned invalid response');
            }
    
            return data;
        } catch (error) {
            console.error('Error requesting PIN:', error);
            throw new Error('Failed to request PIN: ' + error.message);
        }
    }

    /**
     * Show PIN verification modal
     */
    showPinVerificationModal() {
    Swal.fire({
        title: '<h3 style="color: #2c3e50; margin: 0; margin-bottom: 1rem;">Verify PIN</h3>',
        html: `
            <div style="text-align: center;">
                <div style="background: #e3f2fd; border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fa fa-envelope" style="font-size: 36px; color: #1976d2;"></i>
                </div>
                
                <p style="color: #555; margin-bottom: 1.5rem; line-height: 1.5;">
                    We've sent a 6-digit verification PIN to your email address.<br>
                    Please check your inbox and enter the PIN below:
                </p>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">6-Digit PIN</label>
                    <input 
                        type="text" 
                        id="pinCode" 
                        maxlength="6" 
                        pattern="[0-9]{6}" 
                        placeholder="******" 
                        style="width: 200px; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 24px; text-align: center; letter-spacing: 8px; font-weight: bold; transition: all 0.3s;"
                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 0 3px rgba(52, 152, 219, 0.1)'"
                        onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                    >
                    <small style="color: #6c757d; font-size: 12px; margin-top: 0.5rem; display: block;">
                        Enter the 6-digit PIN sent to your email
                    </small>
                </div>
                
                <div style="margin-top: 1rem;">
                    <button 
                        type="button" 
                        id="resendPinBtn" 
                        style="background: none; border: none; color: #3498db; cursor: pointer; font-size: 14px; text-decoration: underline; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;"
                        onmouseover="this.style.color='#2980b9'; this.style.backgroundColor='#f8f9fa'" 
                        onmouseout="this.style.color='#3498db'; this.style.backgroundColor='transparent'"
                    >
                        <i class="fa fa-refresh" style="margin-right: 5px;"></i>
                        Resend PIN
                    </button>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-check" style="margin-right: 5px;"></i> Verify PIN',
        cancelButtonText: '<i class="fa fa-times" style="margin-right: 5px;"></i> Cancel',
        confirmButtonColor: '#27ae60',
        cancelButtonColor: '#6c757d',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const pinCode = document.getElementById('pinCode').value;

            if (!pinCode || pinCode.length !== 6) {
                Swal.showValidationMessage('Please enter a valid 6-digit PIN');
                return false;
            }

            return this.verifyPin(pinCode);
        },
        didOpen: () => {
            const pinInput = document.getElementById('pinCode');
            const resendBtn = document.getElementById('resendPinBtn');

            // Auto-format PIN input
            pinInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });

            // Resend PIN functionality
            resendBtn.addEventListener('click', () => {
                this.resendPin();
            });
        },
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            confirmButton: 'custom-swal-confirm-btn',
            cancelButton: 'custom-swal-cancel-btn'
        },
        width: '480px',
        padding: '2rem'
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    this.showSuccessMessage(result.value.message || 'Password changed successfully!');
                    this.resetData = {};
                } else {
                    this.showError(result.value.message || 'Failed to change password');
                }
            }
        });
    }

    /**
     * Show success message with enhanced UI
     */
    showSuccessMessage(message) {
        Swal.fire({
            
            html: `<div style="color: #2c3e50; font-size: 16px; font-weight: 600;">Password Change Success!</div>`,
            icon: 'success',
            
            
            timer: 3000,
            showConfirmButton: false
        });
    }

    /**
     * Show error message with enhanced UI
     */
    showError(message) {
        Swal.fire({
            title: '<div style="color: #e74c3c; margin-bottom: 1rem;"><i class="fa fa-exclamation-circle" style="font-size: 48px;"></i></div>',
            html: `<div style="color: #2c3e50; font-size: 16px;">${message}</div>`,
            icon: 'error',
            confirmButtonColor: '#e74c3c',
            confirmButtonText: '<i class="fa fa-times" style="margin-right: 5px;"></i> OK'
        });
    }

    /**
     * Attach events to PIN verification modal
     */
    attachPinVerificationModalEvents() {
        const modal = document.getElementById('pinVerificationModal');
        const closeBtn = modal.querySelector('.modal-close');
        const cancelBtn = modal.querySelector('#cancelPinVerification');
        const verifyBtn = modal.querySelector('#verifyPin');
        const resendBtn = modal.querySelector('#resendPin');
        const pinInput = modal.querySelector('#pinCode');

        const closeModal = () => modal.remove();

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        verifyBtn.addEventListener('click', () => this.verifyPin());
        
        resendBtn.addEventListener('click', () => this.resendPin());

        // Auto-format PIN input
        pinInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }

    /**
     * Resend PIN
     */
    async resendPin() {
        const resendBtn = document.getElementById('resendPinBtn');
        const originalHtml = resendBtn.innerHTML;
        
        // Show loading state
        resendBtn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right: 5px;"></i> Sending...';
        resendBtn.disabled = true;
    
        try {
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=request_pin', {
                method: 'POST',
                body: new URLSearchParams({
                    csrf_token: this.csrfToken,
                    current_password: this.resetData.current_password
                })
            });
    
            const responseText = await response.text();
            let data;
            
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Server returned invalid response');
            }
    
            if (data.success) {
                Swal.fire({
                    title: 'PIN Resent!',
                    text: 'A new PIN has been sent to your email.',
                    icon: 'success',
                    confirmButtonColor: '#3498db',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                this.showError(data.message || 'Failed to resend PIN');
            }
        } catch (error) {
            console.error('Error resending PIN:', error);
            this.showError('Failed to resend PIN: ' + error.message);
        } finally {
            // Restore button state
            resendBtn.innerHTML = originalHtml;
            resendBtn.disabled = false;
        }
    }
    

    /**
     * Verify PIN and change password
     */
    async verifyPin(pinCode) {
        try {
            const formData = new FormData();
            formData.append('csrf_token', this.csrfToken);
            formData.append('pin_code', pinCode);
            formData.append('current_password', this.resetData.current_password);
            formData.append('new_password', this.resetData.new_password);
            formData.append('confirm_password', this.resetData.confirm_password);
    
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=verify_pin_change_password', {
                method: 'POST',
                body: formData
            });
    
            const responseText = await response.text();
            let data;
            
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Server returned invalid response');
            }
    
            return data;
        } catch (error) {
            console.error('Error verifying PIN:', error);
            throw new Error('Failed to verify PIN: ' + error.message);
        }
    }
    

    /**
     * Handle logout
     */
    handleLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of your account",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../../../app/Controllers/AuthController.php?action=logout';
            }
        });
    }

    /**
     * Show error message
     */
    showError(message) {
        console.error('Profile Error:', message);
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

// Initialize profile manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a page with profile container
    if (document.getElementById('profileContainer')) {
        new ProfileManager();
    }
});
