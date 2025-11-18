/**
 * Profile Management JavaScript
 * Handles profile display, editing, and interactions
 */

class ProfileManager {
    constructor() {
        this.profileContainer = document.getElementById('profileContainer');
        this.profileSidebarIcon = document.getElementById('profileSidebarIcon');
        this.logoutHeaderIcon = document.getElementById('logoutHeaderIcon');
        this.isProfileVisible = false;
        this.currentProfileData = null; // Store current profile data
        
        this.initializeEventListeners();
        // Don't load profile data immediately, wait for profile view to be shown
    }
    
    initializeEventListeners() {
        // Profile sidebar icon click
        if (this.profileSidebarIcon) {
            this.profileSidebarIcon.addEventListener('click', () => {
                this.toggleProfileView();
            });
        }
        
        // Logout button
        if (this.logoutHeaderIcon) {
            this.logoutHeaderIcon.addEventListener('click', () => {
                this.handleLogout();
            });
        }
        
        // Edit profile button - use event delegation for dynamically created buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-primary') || 
                e.target.closest('.btn-primary')) {
                this.openEditProfileModal();
            }
        });
        
        // Close profile when clicking outside (optional)
        document.addEventListener('click', (e) => {
            if (this.isProfileVisible && 
                this.profileContainer && 
                !this.profileContainer.contains(e.target) && 
                this.profileSidebarIcon && 
                !this.profileSidebarIcon.contains(e.target)) {
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
        
        this.profileContainer.style.display = 'none';
        this.isProfileVisible = false;
        
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
                <div class="modal" style="max-width: 500px;">
                    <div class="modal-header">
                        <h2 class="modal-title">Edit Profile</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editProfileForm" class="profile-form">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" id="firstName" name="first_name" value="${this.escapeHtml(this.currentProfileData.first_name || '')}" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" id="lastName" name="last_name" value="${this.escapeHtml(this.currentProfileData.last_name || '')}" required>
                            </div>
                            <div class="form-group">
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middle_name" value="${this.escapeHtml(this.currentProfileData.middle_name || '')}">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="${this.escapeHtml(this.currentProfileData.email || '')}" required>
                            </div>
                            <div class="form-group">
                                <label for="course">Course</label>
                                <input type="text" id="course" name="course" value="${this.escapeHtml(this.currentProfileData.course || '')}">
                            </div>
                            <input type="hidden" name="csrf_token" value="${this.getCsrfToken()}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveProfile">Save Changes</button>
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
        
        saveBtn.addEventListener('click', async () => {
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
                this.loadProfileData(); // Reload profile data
                document.getElementById('editProfileModal')?.remove();
            } else {
                this.showError(data.message || 'Failed to update profile');
            }
        } catch (error) {
            console.error('Error saving profile:', error);
            this.showError('Error saving profile changes: ' + error.message);
        }
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
                // You can use your existing logout URL or this one
                window.location.href = '../../../app/Controllers/LogoutController.php';
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