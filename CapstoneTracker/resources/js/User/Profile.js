
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
     * Load profile data from server
     */
    async loadProfileData() {
        try {
            console.log('Loading profile data...');
            
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=get_profile', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });
            
            if (!response) {
                throw new Error('No response from server');
            }
            
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            if (!responseText.trim()) {
                throw new Error('Empty response from server');
            }
            
            if (responseText.trim().startsWith('<') || responseText.includes('<b>Warning</b>') || responseText.includes('<b>Fatal error</b>')) {
                console.error('Server returned HTML error:', responseText);
                throw new Error('Server configuration error. Please check PHP error logs.');
            }
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response that failed to parse:', responseText.substring(0, 200));
                throw new Error('Server returned invalid data format');
            }
            
            if (data.success) {
                console.log('Profile data:', data.data);
                this.populateProfileData(data.data);
                console.log('Profile data loaded successfully');
            } else {
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
        this.setElementValue('FullName', profileData.Full_Name || 'N/A');
        this.setElementValue('email', profileData.Email || 'N/A');
        this.setElementValue('userID', profileData.User_ID || 'N/A');
        
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
            profileTitle.textContent = `${profileData.role_display || 'User'} • ${profileData.Department || 'Unknown Department'}`;
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
     * Show change password modal
     */
    showChangePasswordModal() {
        // Create modal HTML
        const modalHtml = `
            <div class="modal-overlay active" id="changePasswordModal">
                <div class="modal" style="max-width: 450px;">
                    <div class="modal-header">
                        <h2 class="modal-title">Change Password</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="changePasswordForm">
                            <div class="form-group">
                                <label for="currentPassword">Current Password *</label>
                                <input type="password" id="currentPassword" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password *</label>
                                <input type="password" id="newPassword" name="new_password" required minlength="8">
                                <small>Password must be at least 8 characters long</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Re-enter New Password *</label>
                                <input type="password" id="confirmPassword" name="confirm_password" required minlength="8">
                            </div>
                            <input type="hidden" name="csrf_token" value="${this.csrfToken}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelChangePassword">Cancel</button>
                        <button type="button" class="btn btn-primary" id="requestPin">Request PIN</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.attachChangePasswordModalEvents();
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
    async requestPin() {
        const form = document.getElementById('changePasswordForm');
        const formData = new FormData(form);
        
        const currentPassword = formData.get('current_password');
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');

        // Validation
        if (!currentPassword || !newPassword || !confirmPassword) {
            this.showError('Please fill in all password fields');
            return;
        }

        if (newPassword !== confirmPassword) {
            this.showError('New passwords do not match');
            return;
        }

        if (newPassword.length < 8) {
            this.showError('Password must be at least 8 characters long');
            return;
        }

        // Store the password data for later use
        this.resetData = {
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        };

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

            if (data.success) {
                this.showPinVerificationModal();
            } else {
                this.showError(data.message || 'Failed to request PIN');
            }
        } catch (error) {
            console.error('Error requesting PIN:', error);
            this.showError('Failed to request PIN: ' + error.message);
        }
    }

    /**
     * Show PIN verification modal
     */
    showPinVerificationModal() {
        // Remove the current modal
        const currentModal = document.getElementById('changePasswordModal');
        if (currentModal) currentModal.remove();

        // Create PIN verification modal
        const modalHtml = `
            <div class="modal-overlay active" id="pinVerificationModal">
                <div class="modal" style="max-width: 400px;">
                    <div class="modal-header">
                        <h2 class="modal-title">Verify PIN</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <i class="fa fa-envelope" style="font-size: 48px; color: #007bff; margin-bottom: 15px;"></i>
                            <p>We've sent a 6-digit PIN to your email address.</p>
                            <p>Please check your inbox and enter the PIN below:</p>
                        </div>
                        <form id="pinVerificationForm">
                            <div class="form-group">
                                <label for="pinCode">6-Digit PIN *</label>
                                <input type="text" id="pinCode" name="pin_code" maxlength="6" pattern="[0-9]{6}" required 
                                       placeholder="Enter 6-digit PIN" style="text-align: center; font-size: 18px; letter-spacing: 3px;">
                                <small>Enter the 6-digit PIN sent to your email</small>
                            </div>
                            <input type="hidden" name="csrf_token" value="${this.csrfToken}">
                        </form>
                        <div style="text-align: center; margin-top: 15px;">
                            <button type="button" id="resendPin" class="btn btn-link" style="color: #007bff; text-decoration: none;">
                                <i class="fa fa-refresh"></i> Resend PIN
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelPinVerification">Cancel</button>
                        <button type="button" class="btn btn-primary" id="verifyPin">Verify PIN</button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.attachPinVerificationModalEvents();
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
                    confirmButtonText: 'OK'
                });
            } else {
                this.showError(data.message || 'Failed to resend PIN');
            }
        } catch (error) {
            console.error('Error resending PIN:', error);
            this.showError('Failed to resend PIN: ' + error.message);
        }
    }

    /**
     * Verify PIN and change password
     */
    async verifyPin() {
        const pinCode = document.getElementById('pinCode').value;

        if (!pinCode || pinCode.length !== 6) {
            this.showError('Please enter a valid 6-digit PIN');
            return;
        }

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

            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message || 'Password changed successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    const modal = document.getElementById('pinVerificationModal');
                    if (modal) modal.remove();
                    // Clear stored data
                    this.resetData = {};
                });
            } else {
                this.showError(data.message || 'Failed to change password');
            }
        } catch (error) {
            console.error('Error verifying PIN:', error);
            this.showError('Failed to verify PIN: ' + error.message);
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
                window.location.href = '../../../app/Controllers/LogoutController.php';
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
