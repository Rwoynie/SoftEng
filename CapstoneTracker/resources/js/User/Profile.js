/**
 * Profile Management JavaScript
 * Handles profile display, editing, and interactions
 */

class ProfileManager {
    constructor() {
        this.profileContainer = document.getElementById('profileContainer');
        this.profileSidebarIcon = document.getElementById('profileSidebarIcon');
        this.logoutHeaderIcon = document.getElementById('logoutHeaderIcon');
        this.moreOptionsIcon = document.querySelector('.more-options');
        this.userMenuPopover = document.getElementById('userMenuPopover');
        this.sidebarLogoutBtn = document.getElementById('sidebarLogoutBtn');
        this.isProfileVisible = false;
        this.currentProfileData = null; // Store current profile data
        
        this.initializeEventListeners();
        // Don't load profile data immediately, wait for profile view to be shown
    }
    
    initializeEventListeners() {
        // Profile sidebar icon click
        if (this.profileSidebarIcon) {
            this.profileSidebarIcon.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleProfileView();
            });
        }
        
        // Logout button
        if (this.logoutHeaderIcon) {
            this.logoutHeaderIcon.addEventListener('click', () => {
                this.handleLogout();
            });
        }

        // Sidebar three-dots (more options) toggles user menu popover
        if (this.moreOptionsIcon && this.userMenuPopover) {
            this.moreOptionsIcon.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isVisible = this.userMenuPopover.style.display === 'block';
                this.userMenuPopover.style.display = isVisible ? 'none' : 'block';
            });
        }

        // Logout from popover button
        if (this.sidebarLogoutBtn) {
            this.sidebarLogoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.userMenuPopover) {
                    this.userMenuPopover.style.display = 'none';
                }
                this.handleLogout();
            });
        }
        
        // Edit profile button - use event delegation for dynamically created buttons
        document.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.btn-primary');
            if (editBtn && editBtn.textContent.includes('Edit Profile')) {
                e.preventDefault();
                e.stopPropagation();
                this.openEditProfileModal();
            }
        });
        
        // Close profile / popover when clicking outside (optional)
        document.addEventListener('click', (e) => {
            const clickedInsideProfile = this.profileContainer && this.profileContainer.contains(e.target);
            const clickedSidebarIcon = this.profileSidebarIcon && this.profileSidebarIcon.contains(e.target);
            const clickedMoreOptions = this.moreOptionsIcon && this.moreOptionsIcon.contains(e.target);
            const clickedUserMenu = this.userMenuPopover && this.userMenuPopover.contains(e.target);

            // if (this.isProfileVisible && !clickedInsideProfile && !clickedSidebarIcon && !document.getElementById('editProfileModal')) {
            //     this.hideProfile();
            // }

            // Close user popover if clicking outside it and the three-dots
            if (this.userMenuPopover && this.userMenuPopover.style.display === 'block' &&
                !clickedMoreOptions && !clickedUserMenu) {
                this.userMenuPopover.style.display = 'none';
            }
        });
        
        // Also handle clicks on other sidebar icons to hide profile
        document.addEventListener('click', (e) => {
            const clickedLi = e.target.closest('.menu-options li');
            if (clickedLi && clickedLi !== this.profileSidebarIcon?.parentElement && this.isProfileVisible) {
                this.hideProfile();
            }
        });
    }
    
    toggleProfileView() {
        if (this.isProfileVisible) {
            this.hideProfile();
        } else {
            this.showProfile();
        }
    }
    
    showProfile() {
        if (!this.profileContainer) return;
        
        // Hide main content
        const appContentHeader = document.querySelector('.app-content-header');
        const recentView = document.getElementById('recentView');
        const allView = document.getElementById('allView');
        // Hide landing-only sections so they don't appear in profile
        const landingIds = ['landingSection','landingAnnouncements','landingPrograms','landingQuickActions','landingFooter'];
        landingIds.forEach(id=>{ const el = document.getElementById(id); if (el) el.style.display = 'none'; });
        
        if (appContentHeader) appContentHeader.style.display = 'none';
        if (recentView) recentView.style.display = 'none';
        if (allView) allView.style.display = 'none';
        
        // Show profile container
        this.profileContainer.style.display = 'block';
        this.isProfileVisible = true;
        
        // Update sidebar icon state
        document.querySelectorAll('.menu-options li').forEach(li => {
            li.classList.remove('selected');
        });
        if (this.profileSidebarIcon && this.profileSidebarIcon.parentElement) {
            this.profileSidebarIcon.parentElement.classList.add('selected');
        }
        
        // Load profile data when showing profile
        this.loadProfileData();
    }
    
    hideProfile() {
        if (!this.profileContainer) return;
        
        // Hide profile container
        this.profileContainer.style.display = 'none';
        this.isProfileVisible = false;
        
        // Show main content
        const appContentHeader = document.querySelector('.app-content-header');
        const recentView = document.getElementById('recentView');
        const allView = document.getElementById('allView');
        const recentButton = document.getElementById('recentButton');
        const allButton = document.getElementById('allButton');
        // Restore landing sections if they haven't been replaced by a search
        const projects = document.getElementById('projectsContainer');
        const landingIds = ['landingSection','landingAnnouncements','landingPrograms','landingQuickActions','landingFooter'];
        if (projects && projects.style.display !== 'block') {
            landingIds.forEach(id=>{ const el = document.getElementById(id); if (el) el.style.display = 'block'; });
        }
        
        if (appContentHeader) appContentHeader.style.display = 'flex';
        
        // Show the appropriate view based on which button is selected
        if (allButton && allButton.classList.contains('selected')) {
            if (allView) allView.style.display = 'grid';
            if (recentView) recentView.style.display = 'none';
        } else {
            if (recentView) recentView.style.display = 'grid';
            if (allView) allView.style.display = 'none';
        }
        
        // Reset sidebar selection to dashboard
        document.querySelectorAll('.menu-options li').forEach(li => {
            li.classList.remove('selected');
        });
        const firstMenuItem = document.querySelector('.menu-options li:first-child');
        if (firstMenuItem) {
            firstMenuItem.classList.add('selected');
        }
    }
    
    async loadProfileData() {
        try {
            console.log('Loading profile data...');
            
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=get', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            // Get the response text first to see what's actually being returned
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            // Check if response is OK
            if (!response.ok) {
                // Try to parse as JSON for error message
                let errorMessage = `HTTP error! status: ${response.status}`;
                try {
                    const errorData = JSON.parse(responseText);
                    errorMessage = errorData.message || errorMessage;
                } catch (e) {
                    // If not JSON, use the raw text
                    errorMessage = responseText || errorMessage;
                }
                throw new Error(errorMessage);
            }
            
            // Parse the JSON response
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Invalid response text:', responseText);
                throw new Error('Server returned invalid JSON: ' + responseText.substring(0, 100));
            }
            
            if (data.success && data.profile) {
                this.currentProfileData = data.profile;
                this.populateProfileData(data.profile);
                console.log('Profile data loaded successfully:', data.profile);
            } else {
                console.error('Failed to load profile:', data.message);
                this.showError(data.message || 'Failed to load profile data');
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            this.showError('Error loading profile data: ' + error.message);
        }
    }
    
    populateProfileData(profileData) {
        if (!profileData) {
            console.error('No profile data provided');
            return;
        }
        
        console.log('Populating profile data:', profileData);
        
        // Update profile picture
        this.updateProfilePicture(profileData.profile_pic);
        
        // Update all elements with data-value attributes
        const dataValueElements = document.querySelectorAll('[data-value]');
        
        dataValueElements.forEach(element => {
            const key = element.getAttribute('data-value');
            
            if (profileData[key] !== undefined && profileData[key] !== null) {
                const value = profileData[key];
                
                if (element.classList.contains('info-value')) {
                    element.textContent = value;
                } else if (element.classList.contains('profile-name')) {
                    element.textContent = value;
                } else if (element.classList.contains('profile-title')) {
                    element.textContent = value;
                } else {
                    element.textContent = value;
                }
                
                // Special handling for status
                if (key === 'acc_status') {
                    element.className = 'info-value'; // Reset classes
                    if (value.toLowerCase() === 'active') {
                        element.classList.add('status-active');
                    } else {
                        element.classList.add('status-inactive');
                    }
                }
            } else {
                console.warn(`Profile data missing for key: ${key}`);
                element.textContent = 'Not available';
            }
        });
        
        // Update online status based on last login
        this.updateOnlineStatus(profileData.last_login);

        // Update sidebar user popover (email + ID)
        const emailEl = document.getElementById('userMenuEmail');
        const idEl = document.getElementById('userMenuId');

        if (emailEl) {
            emailEl.textContent = profileData.email || 'No email available';
        }

        if (idEl) {
            let idText = '';
            if (profileData.student_id) {
                idText = `Student ID: ${profileData.student_id}`;
            } else if (profileData.employee_id) {
                idText = `Employee ID: ${profileData.employee_id}`;
            } else if (profileData.user_id) {
                idText = `User ID: ${profileData.user_id}`;
            } else {
                idText = '';
            }
            idEl.textContent = idText;
        }
    }
    
    updateProfilePicture(profilePicData) {
        const profileImg = document.getElementById('profilePicture');
        if (!profileImg) return;
        
        if (profilePicData && typeof profilePicData === 'string') {
            // Accept either data URL (data:...) or http/https/absolute/relative URL
            profileImg.src = profilePicData;
            profileImg.onerror = () => {
                const defaultSrc = profileImg.getAttribute('data-default-src');
                if (defaultSrc) profileImg.src = defaultSrc;
            };
            return;
        }

        // Fallback to default profile picture
        const defaultSrc = profileImg.getAttribute('data-default-src');
        if (defaultSrc) {
            profileImg.src = defaultSrc;
        }
    }
    
    updateOnlineStatus(lastLogin) {
        const onlineStatus = document.querySelector('.online-status');
        if (!onlineStatus) return;
        
        // Simple logic: if last login is today, consider online
        try {
            const today = new Date().toDateString();
            const lastLoginDate = new Date(lastLogin).toDateString();
            
            if (today === lastLoginDate && lastLogin !== 'Not available') {
                onlineStatus.classList.add('online');
                onlineStatus.classList.remove('offline');
                onlineStatus.title = 'Online - Last login today';
            } else {
                onlineStatus.classList.add('offline');
                onlineStatus.classList.remove('online');
                onlineStatus.title = `Offline - Last login: ${lastLogin}`;
            }
        } catch (error) {
            console.error('Error updating online status:', error);
            onlineStatus.classList.add('offline');
            onlineStatus.title = 'Offline - Status unknown';
        }
    }
    
    openEditProfileModal() {
        if (!this.currentProfileData) {
            this.showError('Please load profile data first');
            return;
        }
        
        // Create edit profile modal
        const modalHtml = `
            <div class="modal-overlay active" id="editProfileModal">
                <div class="modal edit-profile-modal" style="max-width: 720px;">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title">Edit Profile</h2>
                            <p class="modal-subtitle">Update your personal details and profile photo</p>
                        </div>
                        <button class="modal-close" aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editProfileForm" class="profile-form" enctype="multipart/form-data">
                            <div class="edit-profile-grid">
                                <div class="avatar-uploader">
                                    <div class="avatar-ring">
                                        <img id="editProfilePreview" src="${this.escapeHtml(this.currentProfileData.profile_pic || document.getElementById('profilePicture')?.getAttribute('data-default-src') || '')}" alt="Profile preview" />
                                    </div>
                                    <label for="profilePic" class="btn btn-secondary small" style="cursor:pointer; margin-top: 10px;">Change Picture</label>
                                    <input type="file" id="profilePic" name="profile_pic" accept="image/*" style="display:none;" />
                                    <small class="hint">JPG, PNG, GIF, WEBP. Max 5MB.</small>
                                </div>
                                <div class="form-fields">
                                    <div class="form-row three-col">
                                        <div class="form-group">
                                            <label for="firstName">First Name</label>
                                            <input type="text" id="firstName" name="first_name" value="${this.escapeHtml(this.currentProfileData.first_name || '')}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="middleName">Middle Name</label>
                                            <input type="text" id="middleName" name="middle_name" value="${this.escapeHtml(this.currentProfileData.middle_name || '')}">
                                        </div>
                                        <div class="form-group">
                                            <label for="lastName">Last Name</label>
                                            <input type="text" id="lastName" name="last_name" value="${this.escapeHtml(this.currentProfileData.last_name || '')}" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="course">Course</label>
                                            <select id="course" name="course" class="select-input">
                                                ${this.getCourseOptions(this.currentProfileData.course)}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row two-col">
                                        <div class="form-group">
                                            <label for="newPassword">New Password</label>
                                            <div class="input-with-icon">
                                                <input type="password" id="newPassword" name="password" placeholder="Leave blank to keep current">
                                                <button type="button" class="toggle-visibility" id="togglePassword" aria-label="Show password">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <small class="hint">Min of 8 characters.</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirmPassword">Confirm Password</label>
                                            <div class="input-with-icon">
                                                <input type="password" id="confirmPassword" name="confirm_password" placeholder="Repeat new password">
                                                <button type="button" class="toggle-visibility" id="toggleConfirm" aria-label="Show password">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="csrf_token" value="${this.getCsrfToken()}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="footer-actions">
                            <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveProfile">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('editProfileModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.initializeEditModal();
    }
    
    initializeEditModal() {
        const modal = document.getElementById('editProfileModal');
        if (!modal) return;
        
        // Event listeners for modal
        const closeBtn = modal.querySelector('.modal-close');
        const cancelBtn = modal.querySelector('#cancelEdit');
        const saveBtn = modal.querySelector('#saveProfile');
        
        const closeModal = () => {
            modal.remove();
        };
        
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        // Live preview for profile picture
        const fileInput = modal.querySelector('#profilePic');
        const previewImg = modal.querySelector('#editProfilePreview');
        if (fileInput && previewImg) {
            fileInput.addEventListener('change', () => {
                const file = fileInput.files && fileInput.files[0];
                if (!file) return;
                if (!/^image\//.test(file.type)) {
                    this.showError('Please select a valid image file.');
                    fileInput.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    this.showError('Image must be less than 5MB.');
                    fileInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        // Password visibility toggles
        const togglePassword = modal.querySelector('#togglePassword');
        const toggleConfirm = modal.querySelector('#toggleConfirm');
        const newPassword = modal.querySelector('#newPassword');
        const confirmPassword = modal.querySelector('#confirmPassword');
        const wireToggle = (btn, input) => {
            if (!btn || !input) return;
            btn.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
                btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        };
        wireToggle(togglePassword, newPassword);
        wireToggle(toggleConfirm, confirmPassword);

        saveBtn.addEventListener('click', async () => {
            // Optional client-side validation for password match
            if (newPassword && confirmPassword && (newPassword.value || confirmPassword.value)) {
                if (newPassword.value.length < 8) {
                    this.showError('Password must be at least 8 characters.');
                    return;
                }
                if (newPassword.value !== confirmPassword.value) {
                    this.showError('Passwords do not match.');
                    return;
                }
            }
            await this.saveProfileChanges();
        });
        
        // Close on overlay click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
        
        // Enter key support
        modal.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveBtn.click();
            }
        });
    }
    
    async saveProfileChanges() {
        const form = document.getElementById('editProfileForm');
        if (!form) {
            this.showError('Edit form not found');
            return;
        }
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('../../../app/Controllers/ProfileController.php?action=update', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            let data;
            
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid JSON response from server');
            }
            
            if (data.success) {
                this.showSuccess(data.message || 'Profile updated successfully');
                // Reload profile data immediately to show updated information
                await this.loadProfileData();
                document.getElementById('editProfileModal')?.remove();
            } else {
                this.showError(data.message || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Error saving profile:', error);
            this.showError('Error saving profile changes: ' + error.message);
        }
    }

    getCourseOptions(selected) {
        const courses = [
            'Bachelor of Technical-Vocational Teacher Education',
            'Bachelor of Special Need Education',
            'Bachelor of Early Childhood Education',
            'Bachelor of Secondary Education',
            'Bachelor of Science in Information Technology',
            'Bachelor of Elementary Education',
            'Bachelor of Science in Agricultural and Biosystems Engineering',
            'Bachelor of Science in Agriculture and Biosystems Engineering'
        ];
        const current = (selected || '').toLowerCase();
        return courses
            .map(c => `<option value="${this.escapeHtml(c)}" ${current === c.toLowerCase() ? 'selected' : ''}>${this.escapeHtml(c)}</option>`) 
            .join('');
    }
    
    handleLogout() {
        Swal.fire({
            title: 'Logout Confirmation',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Use AuthController logout action
                window.location.href = '../../../app/Controllers/AuthController.php?action=logout';
            }
        });
    }
    
    getCsrfToken() {
        // Try different methods to get CSRF token
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
               document.querySelector('input[name="csrf_token"]')?.value ||
               window.csrfToken || 
               '';
    }
    
    escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }
    
    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            timer: 5000
        });
    }
}

// Initialize Profile Manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.profileManager = new ProfileManager();
});