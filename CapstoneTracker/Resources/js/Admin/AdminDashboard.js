// AnnouncementManager Class 
class AnnouncementManager {
      constructor() {
        this.currentView = 'active';
        this.announcements = [];
        this.filteredAnnouncements = [];
        this.currentFilter = 'all';
        this.currentSort = 'newest';
        this.isEditing = false;
        
        // Use setTimeout to ensure DOM is ready
        setTimeout(() => {
            this.initializeEventListeners();
            this.loadAnnouncements();
        }, 100);
    }

     initializeEventListeners() {
    // View switching - check if elements exist first
    const activeBtn = document.getElementById('activeAnnouncementsBtn');
    const archivedBtn = document.getElementById('archivedAnnouncementsBtn');
    const createBtn = document.getElementById('createAnnouncementBtn');
    const cancelBtn = document.getElementById('cancelAnnouncementBtn');

    if (activeBtn) {
        activeBtn.addEventListener('click', () => this.switchView('active'));
    }
    if (archivedBtn) {
        archivedBtn.addEventListener('click', () => this.switchView('archived'));
    }
    if (createBtn) {
        createBtn.addEventListener('click', () => this.switchView('create'));
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => this.cancelEdit());
    }

    // Form submission
    const announcementForm = document.getElementById('announcementForm');
    if (announcementForm) {
        announcementForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
    }

    // Search and filter
    const searchInput = document.getElementById('announcementSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    }
    
    this.initializeDropdowns();

    // Character counters
    const titleInput = document.getElementById('announcementTitle');
    const contentInput = document.getElementById('announcementContent');
    
    if (titleInput) {
        titleInput.addEventListener('input', (e) => this.updateCharCounter(e.target, 'titleCharCount', 200));
    }
    if (contentInput) {
        contentInput.addEventListener('input', (e) => this.updateCharCounter(e.target, 'contentCharCount', 2000));
    }
}

initializeDropdowns() {
    // Filter dropdown
    const filterDropdown = document.getElementById('announcementFilterDropdown');
    if (filterDropdown) {
        const selectedText = filterDropdown.querySelector('.selected span');
        const options = filterDropdown.querySelectorAll('.options div');
        
        // Toggle dropdown on click
        filterDropdown.querySelector('.selected').addEventListener('click', (e) => {
            e.stopPropagation();
            filterDropdown.classList.toggle('active');
        });
        
        // Handle option selection
        options.forEach(option => {
            option.addEventListener('click', () => {
                const value = option.getAttribute('data-value');
                selectedText.textContent = option.textContent;
                filterDropdown.classList.remove('active');
                
                // Update current filter and apply
                this.currentFilter = value;
                this.applyFiltersAndSort();
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!filterDropdown.contains(e.target)) {
                filterDropdown.classList.remove('active');
            }
        });
    }

    // Sort dropdown
    const sortDropdown = document.getElementById('announcementSortDropdown');
    if (sortDropdown) {
        const selectedText = sortDropdown.querySelector('.selected span');
        const options = sortDropdown.querySelectorAll('.options div');
        
        // Toggle dropdown on click
        sortDropdown.querySelector('.selected').addEventListener('click', (e) => {
            e.stopPropagation();
            sortDropdown.classList.toggle('active');
        });
        
        // Handle option selection
        options.forEach(option => {
            option.addEventListener('click', () => {
                const value = option.getAttribute('data-value');
                selectedText.textContent = option.textContent;
                sortDropdown.classList.remove('active');
                
                // Update current sort and apply
                this.currentSort = value;
                this.applyFiltersAndSort();
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('active');
            }
        });
    }
}

    switchView(view) {
        this.currentView = view;
        
        // Update button states - safely check if elements exist
        const menuButtons = document.querySelectorAll('#announcementHeader .menu button');
        if (menuButtons.length > 0) {
            menuButtons.forEach(btn => {
                btn.classList.remove('selected');
            });
            
            const targetBtn = document.getElementById(`${view}AnnouncementsBtn`);
            if (targetBtn) {
                targetBtn.classList.add('selected');
            }
        }

        // Show/hide views - safely check if elements exist
        const activeView = document.getElementById('activeAnnouncementsView');
        const archivedView = document.getElementById('archivedAnnouncementsView');
        const createView = document.getElementById('createAnnouncementView');

        if (activeView) {
            activeView.style.display = view === 'active' ? 'block' : 'none';
        }
        if (archivedView) {
            archivedView.style.display = view === 'archived' ? 'block' : 'none';
        }
        if (createView) {
            createView.style.display = view === 'create' ? 'block' : 'none';
        }

        if (view === 'active') {
            this.loadAnnouncements();
        } else if (view === 'archived') {
            this.loadArchivedAnnouncements();
        } else if (view === 'create') {
            this.resetForm();
        }
    }

    async loadAnnouncements() {
        try {
            this.showLoading('activeAnnouncementsGrid');
            
            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getActiveAnnouncements');
            const data = await response.json();
            
            if (data.success) {
                this.announcements = data.announcements;
                this.applyFiltersAndSort();
            } else {
                throw new Error(data.error || 'Failed to load announcements');
            }
        } catch (error) {
            console.error('Error loading announcements:', error);
            this.showError('activeAnnouncementsGrid', 'Failed to load announcements');
        }
    }

    async loadArchivedAnnouncements() {
        try {
            this.showLoading('archivedAnnouncementsGrid');
            
            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getArchivedAnnouncements');
            const data = await response.json();
            
            if (data.success) {
                this.displayAnnouncements(data.announcements, 'archivedAnnouncementsGrid', true);
            } else {
                throw new Error(data.error || 'Failed to load archived announcements');
            }
        } catch (error) {
            console.error('Error loading archived announcements:', error);
            this.showError('archivedAnnouncementsGrid', 'Failed to load archived announcements');
        }
    }

    applyFiltersAndSort() {
        this.applyFilters();
        this.applySorting();
    }

    applyFilters() {
        let filtered = this.announcements;

        // Apply type filter
        if (this.currentFilter !== 'all') {
            filtered = filtered.filter(announcement => announcement.type === this.currentFilter);
        }

        this.filteredAnnouncements = filtered;
        this.displayAnnouncements(filtered, 'activeAnnouncementsGrid');
    }

    applySorting() {
        let sorted = [...this.filteredAnnouncements];

        switch (this.currentSort) {
            case 'newest':
                sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
            case 'oldest':
                sorted.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                break;
            case 'title':
                sorted.sort((a, b) => a.title.localeCompare(b.title));
                break;
            case 'expiring':
                sorted.sort((a, b) => {
                    const aDate = a.end_date ? new Date(a.end_date) : new Date('9999-12-31');
                    const bDate = b.end_date ? new Date(b.end_date) : new Date('9999-12-31');
                    return aDate - bDate;
                });
                break;
        }

        this.displayAnnouncements(sorted, 'activeAnnouncementsGrid');
    }

    displayAnnouncements(announcements, containerId, isArchived = false) {
        const container = document.getElementById(containerId);
        
        if (!announcements || announcements.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-${isArchived ? 'archive' : 'bullhorn'}"></i>
                    <h4>No ${isArchived ? 'Archived' : 'Active'} Announcements</h4>
                    <p>${isArchived ? 'Archived announcements will appear here' : 'Create your first announcement to get started'}</p>
                </div>
            `;
            return;
        }

        container.innerHTML = announcements.map(announcement => this.createAnnouncementCard(announcement, isArchived)).join('');
        
        // Add event listeners to action buttons
        this.attachCardEventListeners(containerId, isArchived);
    }

    createAnnouncementCard(announcement, isArchived = false) {
    const isExpired = announcement.end_date && new Date(announcement.end_date) < new Date();
    const isPinned = announcement.is_pinned && !isArchived;
    const typeClass = announcement.type || 'information';
    const contentPreview = this.escapeHtml(announcement.content.length > 200 ? announcement.content.substring(0, 200) + '...' : announcement.content);
    
    return `
        <div class="announcement-card-ui ${typeClass} ${isPinned ? 'pinned' : ''} ${isExpired ? 'expired' : ''}" data-id="${announcement.id}">
            ${isPinned ? '<span class="pinned-badge">📌 Pinned</span>' : ''}

            <!-- Banner Section -->
            <div class="announcement-card-banner">
            <img src="../../../resources/images/Announcement_pic.png" alt="Banner" class="announcement-bg"/>
            <div class="announcement-badge ${typeClass}">${typeClass}</div>
            
            </div>


            <!-- Content Section -->
            <div class="announcement-card-content">
                <h3 class="announcement-title">${this.escapeHtml(announcement.title)}</h3>
                <p class="announcement-date">
                    ${announcement.start_date ? this.formatDate(announcement.start_date) : this.formatDate(announcement.created_at)}
                </p>
                <div class="announcement-content-text" id="content-${announcement.id}">
                    ${contentPreview}
                </div>
                ${announcement.content.length > 200 ? 
                    `<button class="read-more-btn" data-id="${announcement.id}">Read More</button>` : ''}
            </div>

            <!-- Footer Section -->
            <div class="announcement-footer">
                <div class="announcement-dates">
                    <div>Created: ${this.formatDate(announcement.created_at)}</div>
                    ${announcement.start_date ? `<div>Starts: ${this.formatDate(announcement.start_date)}</div>` : ''}
                    ${announcement.end_date ? `<div>Ends: ${this.formatDate(announcement.end_date)}</div>` : ''}
                </div>

                <div class="announcement-actions">
                    ${!isArchived ? `
                        <button class="announcement-action-btn edit-btn" data-action="edit" data-id="${announcement.id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="announcement-action-btn pin-btn" data-action="togglePin" data-id="${announcement.id}">
                            <i class="fas fa-thumbtack"></i> ${announcement.is_pinned ? 'Unpin' : 'Pin'}
                        </button>
                        <button class="announcement-action-btn archive-btn" data-action="archive" data-id="${announcement.id}">
                            <i class="fas fa-archive"></i> Archive
                        </button>
                    ` : `
                        <button class="announcement-action-btn edit-btn" data-action="restore" data-id="${announcement.id}">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    `}
                    <button class="announcement-action-btn delete-btn" data-action="delete" data-id="${announcement.id}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    `;
}


    attachCardEventListeners(containerId, isArchived = false) {
        const container = document.getElementById(containerId);
        
        // Read more/less buttons
        container.querySelectorAll('.read-more-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const announcementId = e.target.getAttribute('data-id');
                const contentElement = document.getElementById(`content-${announcementId}`);
                const isExpanded = contentElement.classList.contains('expanded');
                
                if (isExpanded) {
                    contentElement.classList.remove('expanded');
                    e.target.textContent = 'Read More';
                } else {
                    contentElement.classList.add('expanded');
                    e.target.textContent = 'Read Less';
                }
            });
        });

        // Action buttons
        container.querySelectorAll('.announcement-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const action = e.target.getAttribute('data-action');
                const announcementId = e.target.getAttribute('data-id');
                
                this.handleCardAction(action, announcementId, isArchived);
            });
        });
    }

    async handleCardAction(action, announcementId, isArchived = false) {
        try {
            switch (action) {
                case 'edit':
                    await this.editAnnouncement(announcementId);
                    break;
                case 'togglePin':
                    await this.togglePinAnnouncement(announcementId);
                    break;
                case 'archive':
                    await this.archiveAnnouncement(announcementId);
                    break;
                case 'restore':
                    await this.restoreAnnouncement(announcementId);
                    break;
                case 'delete':
                    await this.deleteAnnouncement(announcementId, isArchived);
                    break;
            }
        } catch (error) {
            console.error('Error handling card action:', error);
            this.showError('Failed to perform action');
        }
    }

    async editAnnouncement(announcementId) {
        try {
            const response = await fetch(`../../../app/Controllers/AdminDashboardController.php?action=getAnnouncement&id=${announcementId}`);
            const data = await response.json();
            
            if (data.success) {
                this.populateForm(data.announcement);
                this.switchView('create');
                this.isEditing = true;
            } else {
                throw new Error(data.error || 'Failed to load announcement data');
            }
        } catch (error) {
            console.error('Error loading announcement for edit:', error);
            this.showError('Failed to load announcement for editing');
        }
    }

     populateForm(announcement) {
        document.getElementById('announcementId').value = announcement.id;
        document.getElementById('announcementTitle').value = announcement.title;
        document.getElementById('announcementType').value = announcement.type;
        
        document.getElementById('announcementContent').value = announcement.content;
        document.getElementById('announcementIsPinned').checked = announcement.is_pinned == 1;

        // Format dates for datetime-local input
        if (announcement.start_date) {
            document.getElementById('announcementStartDate').value = this.formatDateForInput(announcement.start_date);
        }
        if (announcement.end_date) {
            document.getElementById('announcementEndDate').value = this.formatDateForInput(announcement.end_date);
        }

        // Update UI for edit mode
        document.getElementById('createAnnouncementTitle').textContent = 'Edit Announcement';
        document.getElementById('createAnnouncementSubtitle').textContent = 'Update announcement details';

        // Update character counters
        this.updateCharCounter(document.getElementById('announcementTitle'), 'titleCharCount', 200);
        this.updateCharCounter(document.getElementById('announcementContent'), 'contentCharCount', 2000);
    }

    async togglePinAnnouncement(announcementId) {
        const result = await Swal.fire({
            title: 'Toggle Pin?',
            text: 'Do you want to pin/unpin this announcement?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, toggle pin',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id', announcementId);
                formData.append('action', 'togglePinAnnouncement');

                const response = await fetch('../../../app/Controllers/AdminDashboardController.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showSuccess('Announcement pin status updated');
                    this.loadAnnouncements();
                } else {
                    throw new Error(data.error || 'Failed to toggle pin status');
                }
            } catch (error) {
                console.error('Error toggling pin status:', error);
                this.showError('Failed to update pin status');
            }
        }
    }

    async archiveAnnouncement(announcementId) {
        const result = await Swal.fire({
            title: 'Archive Announcement?',
            text: 'This announcement will be moved to archived section',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id', announcementId);
                formData.append('action', 'archiveAnnouncement');

                const response = await fetch('../../../app/Controllers/AdminDashboardController.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showSuccess('Announcement archived successfully');
                    this.loadAnnouncements();
                } else {
                    throw new Error(data.error || 'Failed to archive announcement');
                }
            } catch (error) {
                console.error('Error archiving announcement:', error);
                this.showError('Failed to archive announcement');
            }
        }
    }

    async restoreAnnouncement(announcementId) {
        try {
            const formData = new FormData();
            formData.append('id', announcementId);
            formData.append('action', 'restoreAnnouncement');

            const response = await fetch('../../../app/Controllers/AdminDashboardController.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Announcement restored successfully');
                this.loadArchivedAnnouncements();
            } else {
                throw new Error(data.error || 'Failed to restore announcement');
            }
        } catch (error) {
            console.error('Error restoring announcement:', error);
            this.showError('Failed to restore announcement');
        }
    }

    async deleteAnnouncement(announcementId, isArchived = false) {
        const result = await Swal.fire({
            title: 'Delete Announcement?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id', announcementId);
                formData.append('action', 'deleteAnnouncement');

                const response = await fetch('../../../app/Controllers/AdminDashboardController.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showSuccess('Announcement deleted successfully');
                    if (isArchived) {
                        this.loadArchivedAnnouncements();
                    } else {
                        this.loadAnnouncements();
                    }
                } else {
                    throw new Error(data.error || 'Failed to delete announcement');
                }
            } catch (error) {
                console.error('Error deleting announcement:', error);
                this.showError('Failed to delete announcement');
            }
        }
    }

     async handleFormSubmit(e) {
    e.preventDefault();
    
    // Show loading state
    const submitBtn = document.getElementById('publishAnnouncementBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
    submitBtn.disabled = true;

    try {
        console.log('Starting announcement submission...');
        
        // Get form data
        const formData = new FormData(e.target);
        formData.append('action', this.isEditing ? 'updateAnnouncement' : 'createAnnouncement');

        // Log form data for debugging
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        // Validate form
        if (!this.validateForm()) {
            throw new Error('Form validation failed');
        }

        // In AdminDashboard.js - replace the fetch call in handleFormSubmit method
console.log('Sending request to server...');

// Use the correct endpoint with action parameter
const action = this.isEditing ? 'updateAnnouncement' : 'createAnnouncement';
const response = await fetch(`../../../app/Controllers/AdminDashboardController.php?action=${action}`, {
    method: 'POST',
    body: formData
});

        // Log response status
        console.log('Response status:', response.status, response.statusText);

        const responseText = await response.text();
        console.log('Raw server response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            
            // Try to extract JSON from the response
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    data = JSON.parse(jsonMatch[0]);
                    console.log('Successfully extracted JSON from response');
                } catch (e) {
                    console.error('Failed to parse extracted JSON:', e);
                    throw new Error('Server returned invalid JSON format. Raw response: ' + responseText.substring(0, 200));
                }
            } else {
                // Check if it's a PHP error
                if (responseText.includes('Fatal error') || responseText.includes('Parse error') || responseText.includes('Warning') || responseText.includes('Notice')) {
                    throw new Error('PHP error detected: ' + responseText.substring(0, 300));
                } else {
                    throw new Error('Server returned non-JSON response: ' + responseText.substring(0, 200));
                }
            }
        }

        console.log('Parsed response data:', data);

        if (data.success) {
            this.showSuccess(this.isEditing ? 'Announcement updated successfully' : 'Announcement created successfully');
            this.switchView('active');
            this.resetForm();
            this.loadAnnouncements();
        } else {
            // Show the actual error message from server
            const errorMessage = data.error || data.message || 'Unknown server error';
            console.error('Server returned error:', errorMessage);
            throw new Error(errorMessage);
        }
        
    } catch (error) {
        console.error('Error saving announcement:', error);
        this.showError(`Failed to ${this.isEditing ? 'update' : 'create'} announcement: ${error.message}`);
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

    validateForm() {
        const title = document.getElementById('announcementTitle').value.trim();
        const content = document.getElementById('announcementContent').value.trim();
        const type = document.getElementById('announcementType').value;
        const startDate = document.getElementById('announcementStartDate').value;

        if (!title) {
            this.showError('Please enter a title');
            return false;
        }

        if (!content) {
            this.showError('Please enter announcement content');
            return false;
        }

        if (!type) {
            this.showError('Please select an announcement type');
            return false;
        }

        if (!startDate) {
            this.showError('Please select a start date');
            return false;
        }

        // Validate end date if provided
        const endDate = document.getElementById('announcementEndDate').value;
        if (endDate && new Date(endDate) <= new Date(startDate)) {
            this.showError('End date must be after start date');
            return false;
        }

        return true;
    }

    async saveAsDraft() {
        const formData = new FormData(document.getElementById('announcementForm'));
        formData.append('action', 'saveAnnouncementDraft');
        formData.append('status', 'draft');

        try {
            const response = await fetch('../../../app/Controllers/AdminDashboardController.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Draft saved successfully');
                this.switchView('active');
                this.resetForm();
                this.loadAnnouncements();
            } else {
                throw new Error(data.error || 'Failed to save draft');
            }
        } catch (error) {
            console.error('Error saving draft:', error);
            this.showError('Failed to save draft');
        }
    }

     resetForm() {
        document.getElementById('announcementForm').reset();
        document.getElementById('announcementId').value = '';
        document.getElementById('announcementType').value = 'information';
       
        document.getElementById('announcementIsPinned').checked = false;

        // Set default start date to current datetime
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('announcementStartDate').value = now.toISOString().slice(0, 16);

        // Reset UI
        document.getElementById('createAnnouncementTitle').textContent = 'Create New Announcement';
        document.getElementById('createAnnouncementSubtitle').textContent = 'Share important information with users';

        // Reset character counters
        this.updateCharCounter(document.getElementById('announcementTitle'), 'titleCharCount', 200);
        this.updateCharCounter(document.getElementById('announcementContent'), 'contentCharCount', 2000);

        this.isEditing = false;
    }

    updatePublishButtonText(status) {
    const publishBtn = document.getElementById('publishAnnouncementBtn');
    const buttonText = document.getElementById('publishButtonText');
    
    if (publishBtn && buttonText) {
        if (status === 'draft') {
            buttonText.textContent = 'Publish';
            publishBtn.className = 'btn btn-secondary';
        } else {
            buttonText.textContent = 'Publish Announcement';
            publishBtn.className = 'btn btn-primary';
        }
    }
}

    cancelEdit() {
        if (this.isEditing) {
            Swal.fire({
                title: 'Cancel Editing?',
                text: 'Any unsaved changes will be lost',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel',
                cancelButtonText: 'Continue editing'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.switchView('active');
                    this.resetForm();
                }
            });
        } else {
            this.switchView('active');
        }
    }

    handleSearch(searchTerm) {
        if (!searchTerm) {
            this.applyFilters();
            return;
        }

        const filtered = this.filteredAnnouncements.filter(announcement => 
            announcement.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
            announcement.content.toLowerCase().includes(searchTerm.toLowerCase())
        );

        this.displayAnnouncements(filtered, 'activeAnnouncementsGrid');
    }

    updateCharCounter(element, counterId, maxLength) {
        const counter = document.getElementById(counterId);
        const currentLength = element.value.length;
        
        counter.textContent = currentLength;
        
        // Update color based on length
        counter.className = 'char-counter';
        if (currentLength > maxLength * 0.8) {
            counter.classList.add('warning');
        }
        if (currentLength > maxLength) {
            counter.classList.add('error');
        }
    }

    // Utility methods
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatDateForInput(dateString) {
        const date = new Date(dateString);
        return date.toISOString().slice(0, 16);
    }

    showLoading(containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading announcements...</p>
            </div>
        `;
    }

    showError(containerId, message) {
        const container = document.getElementById(containerId);
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Error Loading Content</h4>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="announcementManager.loadAnnouncements()">Try Again</button>
            </div>
        `;
    }

    showSuccess(message) {
        Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK',
            timer: 3000
        });
    }

    showError(message) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }

    // Add this method to your AnnouncementManager class
debugFormData() {
    const form = document.getElementById('announcementForm');
    const formData = new FormData(form);
    
    console.log('=== FORM DATA DEBUG ===');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
    }
    console.log('=== END FORM DATA ===');
}


}

// Now the DOMContentLoaded event starts here
console.log('AnnouncementManager available:', typeof AnnouncementManager !== 'undefined');

document.addEventListener('DOMContentLoaded', function() {
    // Profile functionality (existing code)
    let uploadedFiles = {
        abstract: [],
        thesis: []
    };
    const logoutBtn = document.getElementById('logoutHeaderIcon');

    const fabIcon = document.querySelector('.fab-icon');
    const uploadModal = document.getElementById('uploadModal');
    const previewModal = document.getElementById('previewModal');

    const abstractDropArea = document.getElementById('abstractDropArea');
    const thesisDropArea = document.getElementById('thesisDropArea');
    const abstractFileInput = document.getElementById('abstractFileInput');
    const thesisFileInput = document.getElementById('thesisFileInput');

    const btnUpload = document.querySelector('.btn-upload');
    
    const downloadLink = document.getElementById('download-link');
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const pdfViewer = document.getElementById('pdf-viewer');
    const unsupportedFile = document.getElementById('unsupported-file');

    const allButton = document.getElementById('allButton');
    const recentButton = document.getElementById('recentButton');
    const allView = document.getElementById('allView');
    const recentView = document.getElementById('recentView');
    const userButton = document.getElementById('userButton');
    const adminButton = document.getElementById('adminButton');
    const userLogView = document.getElementById('userLog-container');
    const adminLogView = document.getElementById('adminLog-container');

    const adminAccessBtn = document.getElementById('adminAccess');
    const facultyAccessBtn = document.getElementById('facultyAccess');
    const studentAccessBtn = document.getElementById('studentAccess');

    const isSubAdmin = userDisplayData.user_role === 'subAdmin';


    // logout
    const moreOptionsIcon = document.querySelector('.more-options .fa-ellipsis-h');
    const logoutMenu = document.createElement('div');
    logoutMenu.id = 'logoutMenu';
    logoutMenu.className = 'logout-menu';

    //session data
    const userName = userDisplayData ? userDisplayData.user_name : '';
    const userRole = userDisplayData ? userDisplayData.user_role : '';

    const displayName = userName;

    // Changed to select buttons instead of li elements
    const menuButtons = document.querySelectorAll('.header .menu button');

    // for log buttons
    const logMenuButtons = document.querySelectorAll('.header .logMenu button');


    // Function to switch log views
    function switchLogView(viewToShow, buttonToSelect) {
        // Get references to log views and buttons (they might not exist initially)
        const userLogView = document.getElementById('userLog-container');
        const adminLogView = document.getElementById('adminLog-container');
        const userButton = document.getElementById('userButton');
        const adminButton = document.getElementById('adminButton');
        
        // Hide all log views if they exist
        if (userLogView) userLogView.style.display = 'none';
        if (adminLogView) adminLogView.style.display = 'none';
        
        // Remove active class from all buttons if they exist
        if (userButton) userButton.classList.remove('selected');
        if (adminButton) adminButton.classList.remove('selected');
        
        // Show selected log view and activate button
        if (viewToShow && buttonToSelect) {
            viewToShow.style.display = 'block';
            buttonToSelect.classList.add('selected');
        }
    }
    
    function initializeSidebar() {
    const sidebarOptions = document.querySelectorAll('.menu-options li');
    const contentContainers = {
        'dashboard': document.querySelector('.projects-container'),
        'users': document.getElementById('access-container'),
        'accounts': document.getElementById('accounts-container'),
        'logs': document.getElementById('logs-container'),
        'announcement': document.getElementById('announcement-container')
    };

    // Function to switch sidebar views
    function switchSidebarView(viewId) {
        const header = document.querySelector('.header');
        const appContentHeader = document.querySelector('.app-content-header');
        const mainContent = document.querySelector('.main-content');
        
        // Hide all content containers
        Object.values(contentContainers).forEach(container => {
            if (container) {
                container.style.display = 'none';
                container.classList.remove('content-container-active');
            }
        });
        
        // Show the selected content container
        if (contentContainers[viewId]) {
            contentContainers[viewId].style.display = 'block';
            contentContainers[viewId].classList.add('content-container-active');
    
            // Show app-content-header only for dashboard view
            if (viewId === 'dashboard') {
                if (appContentHeader) appContentHeader.style.display = 'flex';
            } else {
                if (appContentHeader) appContentHeader.style.display = 'none';
            }
            
            // Special handling for logs view
            if (viewId === 'logs') {
                // Ensure user log is shown by default
                const userLogView = document.getElementById('userLog-container');
                const userButton = document.getElementById('userButton');
                if (userLogView && userButton) {
                    switchLogView(userLogView, userButton);
                }
            }
            
            // NEW: Reset access management state when switching to users view
            if (viewId === 'users') {
                resetAccessManagementState();
            }
            
            // Initialize announcement functionality when announcement view is shown
            if (viewId === 'announcement') {
                setTimeout(() => {
                    if (typeof AnnouncementManager !== 'undefined') {
                        if (!window.announcementManager) {
                            console.log('Creating AnnouncementManager instance');
                            window.announcementManager = new AnnouncementManager();
                    } else {
                            console.log('AnnouncementManager instance already exists');
                // Ensure the view is properly set
                window.announcementManager.switchView('active');
            }
            
            // Ensure the container is properly displayed
            const announcementContainer = document.getElementById('announcement-container');
            if (announcementContainer) {
                console.log('Announcement container found and displayed');
            }
        } else {
            console.error('AnnouncementManager class not found');
        }
    }, 300); // Increased delay to ensure DOM is ready
}
        }
        
        // Update active states in sidebar
        sidebarOptions.forEach(option => {
            option.classList.remove('selected');
        });
        
        // Find and select the clicked option
        const clickedOption = Array.from(sidebarOptions).find(option => {
            return option.getAttribute('data-view') === viewId;
        });
        
        if (clickedOption) {
            clickedOption.classList.add('selected');
        }
    }

    // Add event listeners to sidebar options
    sidebarOptions.forEach((option, index) => {
        // Set data attributes to identify each option
        const viewIds = ['dashboard', 'users', 'accounts', 'logs', 'announcement'];
        option.setAttribute('data-view', viewIds[index] || `option-${index}`);
        
        option.addEventListener('click', function() {
            const viewId = this.getAttribute('data-view');
            switchSidebarView(viewId);
        });
    });

    // Initialize with dashboard view
    switchSidebarView('dashboard');
}

    // Initialize sidebar
    initializeSidebar();

    // Function to switch views
    function switchView(viewToShow, buttonToSelect) {
        // Hide all views
        allView.style.display = 'none';
        recentView.style.display = 'none';
    
        // Show selected view
        viewToShow.style.display = 'grid';
    
        // Update button states - ensure All button is always selected when showing all view
        menuButtons.forEach(button => button.classList.remove('selected'));
        
        if (viewToShow === allView) {
            allButton.classList.add('selected');
        } else {
            buttonToSelect.classList.add('selected');
        }
    
        // Reset sort when switching views
        resetSortState();
        
        // Preserve list/grid view setting
        const isListView = listViewIcon.classList.contains('selected');
        const projectsContainers = document.querySelectorAll('.projects');
        
        projectsContainers.forEach(container => {
            if (isListView) {
                container.style.gridTemplateColumns = '1fr';
            } else {
                container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
            }
        });
        
        // Re-run animations after switching views
        animateOnScroll();
    }

    // Event listeners for log buttons
    if (userButton && adminButton) {
        userButton.addEventListener('click', function() {
            switchLogView(userLogView, userButton);
        });
    
        adminButton.addEventListener('click', function() {
            switchLogView(adminLogView, adminButton);
        });
    }
    
    // Initialize with user log view visible when logs container is shown
    if (userLogView && userButton) {
        switchLogView(userLogView, userButton);
    }

    // Event listeners for recent & all buttons
    if (allButton && recentButton) {
        allButton.addEventListener('click', function() {
            switchView(allView, allButton);
        });
    
        recentButton.addEventListener('click', function() {
            switchView(recentView, recentButton);
        });
    }

    document.addEventListener('click', function(e) {
        const projectItem = e.target.closest('.project-item');
        if (projectItem && !e.target.closest('.project-item .logo-row .icon') && !e.target.closest('.moreOptions')) {
            handleProjectItemClick(projectItem);
        }
    });
    
    function initializeUploadArea(dropArea, fileInput) {
        if (!dropArea || !fileInput) return;
        
        const fileType = fileInput.id === 'abstractFileInput' ? 'abstract' : 'thesis';
        
        // File input change event
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                handleFiles(this.files, fileType);
            }
        });
        
        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('dragover');
        }
        
        function unhighlight() {
            dropArea.classList.remove('dragover');
        }
        
        dropArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files, fileType);
        });
    }

    // Open modal when FAB is clicked
    if (fabIcon && uploadModal) {
        fabIcon.addEventListener('click', function() {
            console.log('FAB clicked, opening modal');
            try {
            uploadModal.classList.add('active');
            document.body.style.overflow = 'hidden';
                console.log('Modal opened successfully');
            } catch (error) {
                console.error('Error opening modal:', error);
            }
        });
    } else {
        console.error('FAB icon or upload modal not found');
    }

    function initializeModalCloseHandlers() {
        // Close buttons for all modals
        const modalCloseButtons = document.querySelectorAll('.modal-close, .btn-cancel');
        
        modalCloseButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Find the closest modal overlay
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    closeModal(modal);
                }
            });
        });
        
        // Close modal when clicking outside
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this);
                        // Reset form when modal closes
                        if (this.id === 'uploadModal') {
                            resetUploadForm();
                        }
                    }
                });
            }
        });
    }
    
    // Close modal functions
    function closeModal(modal) {
        if (!modal) {
            console.error('Modal element not provided');
            return;
        }
        
        try {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // Clean up PDF resources if it's the preview modal
            if (modal.id === 'previewModal') {
                if (window.currentPdfBlobUrl) {
                    URL.revokeObjectURL(window.currentPdfBlobUrl);
                    window.currentPdfBlobUrl = null;
                }
                
                // Reset PDF state
                window.currentPdfDoc = null;
                window.currentPageNum = 1;
                
                // Hide footer controls
                const pdfFooterControls = document.getElementById('pdf-footer-controls');
                if (pdfFooterControls) {
                    pdfFooterControls.style.display = 'none';
                }
                
                // Reset download link
                const downloadLink = document.getElementById('download-link');
                if (downloadLink) {
                    downloadLink.style.display = 'none';
                    downloadLink.href = '#';
                }
                
                // Reset viewer states
                const docViewerIframe = document.getElementById('doc-viewer-iframe');
                const pdfViewer = document.getElementById('pdf-viewer');
                const unsupportedFile = document.getElementById('unsupported-file');
                
                if (docViewerIframe) {
                    docViewerIframe.style.display = 'none';
                    docViewerIframe.src = '';
                }
                
                if (pdfViewer) {
                    pdfViewer.style.display = 'none';
                    pdfViewer.innerHTML = '';
                }
                
                if (unsupportedFile) {
                    unsupportedFile.style.display = 'none';
                }
            }
            
            console.log('Modal closed successfully');
        } catch (error) {
            console.error('Error closing modal:', error);
        }
    }
    
    initializeModalCloseHandlers();
    
    // Initialize both upload areas
    initializeUploadArea(abstractDropArea, abstractFileInput);
    initializeUploadArea(thesisDropArea, thesisFileInput);

    // File input handling via browse buttons
    const browseBtns = document.querySelectorAll('.browse-btn');
    browseBtns.forEach((browseBtn, index) => {
        browseBtn.addEventListener('click', function() {
            if (index === 0) {
                abstractFileInput.click();
            } else {
                thesisFileInput.click();
            }
        });
    });
    
    // Handle the selected files
    function handleFiles(files, fileType) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Check if file type is supported (only PDF)
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (fileExtension !== 'pdf') {
                Swal.fire({
                    title: 'Unsupported File Type',
                    text: 'Please upload only PDF files.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                continue;
            }
            
            // Check file size (max 50MB)
            const maxFileSize = 50 * 1024 * 1024;
            if (file.size > maxFileSize) {
                Swal.fire({
                    title: 'File Too Large',
                    text: 'Please upload files smaller than 50MB.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                continue;
            }
            
            if (file.size === 0) {
                Swal.fire({
                    title: 'Empty File',
                    text: 'The selected file is empty.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                continue;
            }
            
            // FIX: Check if file is already in the list using a more reliable method
            const isDuplicate = uploadedFiles[fileType].some(existingFile => 
                existingFile.name === file.name && 
                existingFile.size === file.size &&
                existingFile.lastModified === file.lastModified
            );
            
            if (isDuplicate) {
                Swal.fire({
                    title: 'File Already Added',
                    text: 'This file has already been added to the upload list.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                continue;
            }
            
            // Add file to the appropriate array
            uploadedFiles[fileType].push(file);
            displayFile(file, fileType);
        }
        
        // Update upload button state
        updateUploadButtonState();
    }
    
    // Display file in the list with preview
    function displayFile(file, fileType) {
        console.log('Displaying file:', file.name, 'Type:', fileType, 'Size:', file.size);
        
        const fileListId = fileType === 'abstract' ? 'abstractFileList' : 'thesisFileList';
        const fileList = document.getElementById(fileListId);
        
        // FIX: Check if file already exists in the display before adding
        const existingFileItems = fileList.querySelectorAll('.file-item-card');
        for (let existingItem of existingFileItems) {
            const existingFileName = existingItem.querySelector('.file-name-preview').textContent;
            if (existingFileName === file.name) {
                console.log('File already displayed:', file.name);
                return; // Don't add duplicate display
            }
        }
        
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item-card animate__animated animate__fadeInUp';
        fileItem.setAttribute('data-file-type', fileType);
        fileItem.setAttribute('data-file-name', file.name);
        
        let fileIconClass = 'file-icon-preview pdf';
        
        const fileSize = formatFileSize(file.size);
        
        fileItem.innerHTML = `
            <div class="${fileIconClass}">
                <i class="far fa-file-pdf"></i>
            </div>
            <div class="file-info-preview">
                <div class="file-name-preview">${file.name}</div>
                <div class="file-size-preview">${fileSize}</div>
            </div>
            <div class="file-actions-preview">
                <button type="button" class="file-action-btn-preview file-download-preview" data-filename="${file.name}" data-filetype="${fileType}">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="file-action-btn-preview file-remove-preview" data-filename="${file.name}" data-filetype="${fileType}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        fileList.appendChild(fileItem);
        
        // Remove empty state if files are added
        const emptyState = fileList.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }
        
        // Add event listener to remove button
        const removeBtn = fileItem.querySelector('.file-remove-preview');
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const fileName = this.getAttribute('data-filename');
            const fileType = this.getAttribute('data-filetype');
            console.log('Removing file:', fileName, 'Type:', fileType);
            
            removeFile(fileName, fileType);
            
            // Animate removal
            fileItem.classList.add('animate__fadeOut');
            setTimeout(() => {
                fileItem.remove();
                // Show empty state if no files left in this category
                if (uploadedFiles[fileType].length === 0) {
                    showEmptyState(fileType);
                }
            }, 500);
        });
        
        // Add event listener to preview button
        const previewBtn = fileItem.querySelector('.file-download-preview');
        previewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const fileName = this.getAttribute('data-filename');
            const fileType = this.getAttribute('data-filetype');
            previewFile(fileName, fileType);
        });
    }

    // Show empty state when no files
    function showEmptyState(fileType) {
        const fileListId = fileType === 'abstract' ? 'abstractFileList' : 'thesisFileList';
        const fileList = document.getElementById(fileListId);
        
        fileList.innerHTML = `
            <div class="empty-state">
                <i class="far fa-file-pdf"></i>
                <p>No ${fileType} files selected</p>
            </div>
        `;
    }
    
    // Format file size to human readable format
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Remove file from the list
    function removeFile(fileName, fileType) {
        // Remove from uploadedFiles array
        uploadedFiles[fileType] = uploadedFiles[fileType].filter(file => file.name !== fileName);
        
        // FIX: Clear the file input value to allow re-selection of the same file
        if (fileType === 'abstract' && abstractFileInput) {
            abstractFileInput.value = '';
        } else if (fileType === 'thesis' && thesisFileInput) {
            thesisFileInput.value = '';
        }
        
        updateUploadButtonState();
    }

    // Preview file using Google Docs Viewer for docx and PDF.js for pdf
    function previewFile(fileName, fileType) {
        const file = uploadedFiles[fileType].find(f => f.name === fileName);
        if (!file) return;
        
        // Reset viewer states
        docViewerIframe.style.display = 'none';
        pdfViewer.style.display = 'none';
        unsupportedFile.style.display = 'none';
        
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const fileUrl = URL.createObjectURL(file);
        
        // Set download link
        downloadLink.href = fileUrl;
        downloadLink.download = file.name;
        
        if (fileExtension === 'pdf') {
            // Use PDF.js for PDF preview
            previewPdf(fileUrl);
            pdfViewer.style.display = 'block';
        } else if (fileExtension === 'docx') {
            // Use Google Docs Viewer for DOCX files
            const previewUrl = `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`;
            docViewerIframe.src = previewUrl;
            docViewerIframe.style.display = 'block';
        } else {
            // Show unsupported message for other file types
            unsupportedFile.style.display = 'block';
        }
        
        // Show preview modal
        previewModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // PDF.js functions for PDF preview
    function previewPdf(url) {
        // Ensure PDF.js is available
        if (typeof pdfjsLib === 'undefined') {
            console.error('PDF.js library not loaded');
            showPdfError('PDF viewer library not loaded. Please refresh the page.');
            return;
        }
        
        const pdfViewer = document.getElementById('pdf-viewer');
        
        // Clear previous content and show loading
        pdfViewer.innerHTML = '<div class="loading-preview"><i class="fas fa-spinner fa-spin"></i><p>Loading Abstract...</p></div>';
        
        // Set up PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        
        // Clear previous PDF document
        if (window.currentPdfDoc) {
            window.currentPdfDoc.destroy();
        }
        
        // Hide footer controls initially
        const pdfFooterControls = document.getElementById('pdf-footer-controls');
        pdfFooterControls.style.display = 'none';
        
        // Load the PDF document
        pdfjsLib.getDocument(url).promise.then(function(pdfDoc) {
            console.log('PDF loaded successfully, pages:', pdfDoc.numPages);
            
            // Store the PDF document globally
            window.currentPdfDoc = pdfDoc;
            window.currentPageNum = 1;
            
            // Clear loading state
            pdfViewer.innerHTML = '';
            
            // Show footer controls
            pdfFooterControls.style.display = 'flex';
            
            // Update total pages
            document.getElementById('pdf-total-pages').textContent = pdfDoc.numPages;
            
            // Render the first page
            renderPage(window.currentPageNum);
            
            // Add PDF controls to footer
            addPdfFooterControls(pdfDoc);
            
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            showPdfError(`Failed to load PDF: ${error.message}`);
        });
    }

    function addPdfFooterControls(pdfDoc) {
        // Remove any existing event listeners first
        const prevBtn = document.getElementById('prev-page-footer');
        const nextBtn = document.getElementById('next-page-footer');
        
        // Clone and replace to remove old event listeners
        if (prevBtn && nextBtn) {
            const newPrevBtn = prevBtn.cloneNode(true);
            const newNextBtn = nextBtn.cloneNode(true);
            
            prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
            nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
        }
        
        // Add event listeners to footer controls
        document.getElementById('prev-page-footer').addEventListener('click', function() {
            if (window.currentPageNum <= 1) return;
            window.currentPageNum--;
            renderPage(window.currentPageNum);
            updatePdfFooterControls();
        });
        
        document.getElementById('next-page-footer').addEventListener('click', function() {
            if (window.currentPageNum >= window.currentPdfDoc.numPages) return;
            window.currentPageNum++;
            renderPage(window.currentPageNum);
            updatePdfFooterControls();
        });
        
        // Initial controls update
        updatePdfFooterControls();
    }

    function updatePdfFooterControls() {
        const prevBtn = document.getElementById('prev-page-footer');
        const nextBtn = document.getElementById('next-page-footer');
        
        if (prevBtn && nextBtn && window.currentPdfDoc) {
            prevBtn.disabled = window.currentPageNum <= 1;
            nextBtn.disabled = window.currentPageNum >= window.currentPdfDoc.numPages;
            
            // Update page number in footer
            const pageNumElement = document.getElementById('pdf-page-num-footer');
            if (pageNumElement) {
                pageNumElement.textContent = window.currentPageNum;
            }
        }
    }
    
    function renderPage(pageNum) {
        if (!window.currentPdfDoc || typeof window.currentPdfDoc.getPage !== 'function') {
            console.error('Invalid PDF document');
            showPdfError('Invalid PDF document');
            return;
        }
        
        const pdfViewer = document.getElementById('pdf-viewer');
        
        window.currentPdfDoc.getPage(pageNum).then(function(page) {
            console.log('Rendering page:', pageNum);
            
            const scale = 1.2;
            const viewport = page.getViewport({ scale: scale });
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            canvas.style.display = 'block';
            canvas.style.margin = '0 auto';
            canvas.style.border = '1px solid #ddd';
            canvas.style.maxWidth = '100%';
            
            // Clear previous canvas
            const existingCanvas = pdfViewer.querySelector('canvas');
            if (existingCanvas) {
                existingCanvas.remove();
            }
            
            // Add canvas to viewer
            pdfViewer.appendChild(canvas);
            
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            return page.render(renderContext).promise;
            
        }).then(function() {
            console.log('Page rendered successfully');
            // Update footer page number
            updatePdfFooterControls();
            
        }).catch(function(error) {
            console.error('Error rendering page:', error);
            showPdfError(`Error rendering page: ${error.message}`);
        });
    }

    function showPdfError(message) {
        const pdfViewer = document.getElementById('pdf-viewer');
        const unsupportedFile = document.getElementById('unsupported-file');
        
        pdfViewer.style.display = 'none';
        unsupportedFile.innerHTML = `
            <div class="error-preview">
                <i class="fas fa-file-pdf" style="font-size: 48px; color: #e74c3c;"></i>
                <h3>Abstract Preview Unavailable</h3>
                <p>${message}</p>
                <p><small>You can still download the abstract using the download button above.</small></p>
            </div>
        `;
        unsupportedFile.style.display = 'block';
    }
    
    function addPdfControls(pdfDoc) {
        const pdfViewer = document.getElementById('pdf-viewer');
        
        // Remove existing controls
        const existingControls = pdfViewer.querySelector('.pdf-controls');
        if (existingControls) {
            existingControls.remove();
        }
        
        const controlsHtml = `
            <div class="pdf-controls">
                <button id="prev-page" type="button">
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <span class="pdf-page-info">
                    Page <span id="pdf-page-num">1</span> of ${pdfDoc.numPages}
                </span>
                <button id="next-page" type="button">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
        
        pdfViewer.insertAdjacentHTML('afterbegin', controlsHtml);
        
        // Add event listeners
        document.getElementById('prev-page').addEventListener('click', function() {
            if (window.currentPageNum <= 1) return;
            window.currentPageNum--;
            renderPage(window.currentPdfDoc, window.currentPageNum);
            updatePdfControls();
        });
        
        document.getElementById('next-page').addEventListener('click', function() {
            if (window.currentPageNum >= window.currentPdfDoc.numPages) return;
            window.currentPageNum++;
            renderPage(window.currentPdfDoc, window.currentPageNum);
            updatePdfControls();
        });
        
        // Initial controls update
        updatePdfControls();
    }
    
    function updatePdfControls() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        if (prevBtn && nextBtn && window.currentPdfDoc) {
            prevBtn.disabled = window.currentPageNum <= 1;
            nextBtn.disabled = window.currentPageNum >= window.currentPdfDoc.numPages;
        }
    }

    // Upload button functionality
    if (btnUpload) {
        // Change to form submit event instead of button click
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Check if both file types have files
                if (uploadedFiles.abstract.length === 0) {
                    Swal.fire({
                        title: 'Abstract File Required',
                        text: 'Please select at least one abstract file.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                if (uploadedFiles.thesis.length === 0) {
                    Swal.fire({
                        title: 'Thesis File Required',
                        text: 'Please select at least one thesis file.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            
            // Validate thesis title
            const thesisTitleInput = document.getElementById('thesisTitle');
            if (thesisTitleInput && !thesisTitleInput.value.trim()) {
                Swal.fire({
                    title: 'Thesis Title Required',
                    text: 'Please enter a title for your thesis.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            });
        }
    }
    
    // Function to reset upload form
    function resetUploadForm() {
        // Clear uploaded files arrays
        uploadedFiles.abstract = [];
        uploadedFiles.thesis = [];
        
        // Clear file displays
        showEmptyState('abstract');
        showEmptyState('thesis');
        
        // Clear both file inputs
        if (abstractFileInput) abstractFileInput.value = '';
        if (thesisFileInput) thesisFileInput.value = '';
        
        // Clear all form fields
        const formFields = [
            'thesisTitle',
            'thesisAuthor',
            'thesisAdviser'
        ];
        
        formFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                field.style.borderColor = '#ddd';
            }
        });
        
        // Reset department select
        const departmentSelect = document.getElementById('departmentSelect');
        const courseInput = document.getElementById('courseInput');

        if (departmentSelect) {
            departmentSelect.selectedIndex = 0;
            departmentSelect.style.borderColor = '#ddd';
        }

        if (courseInput) {
            courseInput.innerHTML = '<option value="" selected disabled>Select your program</option>';
            courseInput.disabled = true;
            courseInput.style.borderColor = '#ddd';
        }
        
        // Update button state
        updateUploadButtonState();
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
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
                    // Redirect to logout page or perform logout action
                    Swal.fire(
                        'Logged out!',
                        'You have been successfully logged out.',
                        'success'
                    ).then(() => {
                        // Redirect to login page after successful logout
                        window.location.href = 'publicView.php'; // Change to your actual login page
                    });
                }
            });
        });
    }

    // NEW: Filter dropdown functionality
    const filterDropdown = document.getElementById('filterDropdown');
    if (filterDropdown) {
        const selectedText = filterDropdown.querySelector('.selected span');
        const options = filterDropdown.querySelectorAll('.options div');
        
        // Toggle dropdown on click
        filterDropdown.querySelector('.selected').addEventListener('click', function(e) {
            e.stopPropagation();
            filterDropdown.classList.toggle('active');
        });
        
        // Handle option selection
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                selectedText.textContent = this.textContent;
                filterDropdown.classList.remove('active');
                
                // Filter projects based on selected value
                const projectItems = document.querySelectorAll('.project-item');
                
                if (value === 'all') {
                    // Show all items if "All" is selected
                    projectItems.forEach(item => {
                        item.style.display = 'flex';
                    });
                } else {
                    // Hide items that don't match the filter
                    projectItems.forEach(item => {
                        const tags = item.getAttribute('data-tags').split(' ');
                        if (tags.includes(value)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }
                
                // Re-run animations after filtering
                animateOnScroll();
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (filterDropdown && !filterDropdown.contains(e.target)) {
                filterDropdown.classList.remove('active');
            }
        });
    }

    // ACCOUNT FILTER NEW: Filter dropdown functionality
    const departmentFilterDropdown = document.getElementById('departmentFilterDropdown');
    const sortDropdown = document.getElementById('sortDropdown');

    // Add this function to initialize both dropdowns
    function initializeFilterDropdowns() {
        // Department Filter Dropdown
        initializeDepartmentFilter();
        
        // Sort Dropdown - FIXED
        if (sortDropdown) {
            const sortSelectedText = sortDropdown.querySelector('.selected span');
            const sortOptions = sortDropdown.querySelectorAll('.options div');
            
            // Toggle dropdown on click
            sortDropdown.querySelector('.selected').addEventListener('click', function(e) {
                e.stopPropagation();
                sortDropdown.classList.toggle('active');
            });
            
            // Handle option selection
            sortOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const displayText = this.textContent;
                    sortSelectedText.textContent = "Sort by: " + displayText;
                    sortDropdown.classList.remove('active');
                    
                    // Sort projects based on selected criteria
                    sortProjects(value);
                });
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (departmentFilterDropdown && !departmentFilterDropdown.contains(e.target)) {
                departmentFilterDropdown.classList.remove('active');
            }
            if (sortDropdown && !sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('active');
            }
        });
    }

    // Add these filter and sort functions
    function filterProjectsByDepartment(department) {
        const projectItems = document.querySelectorAll('.project-item');
        const notFound = document.getElementById('notFound');
        let foundResults = false;
        
        projectItems.forEach(item => {
            // Add data-department attribute to your project items in HTML
            // Example: <li class="project-item" data-department="cs" ...>
            const itemDepartment = item.getAttribute('data-department');
            
            if (department === 'all' || itemDepartment === department) {
                item.style.display = 'flex';
                foundResults = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show/hide the "No Results Found" message
        if (foundResults || department === 'all') {
            notFound.style.display = 'none';
        } else {
            notFound.style.display = 'flex';
        }
        
        // Re-run animations after filtering
        animateOnScroll();
    }

    function resetSortState() {
        const sortDropdown = document.getElementById('sortDropdown');
        if (sortDropdown) {
            const sortSelectedText = sortDropdown.querySelector('.selected span');
            sortSelectedText.textContent = "Sort by: Recent";
        }
        
        // Clear current sort criteria
        window.currentSortCriteria = null;
        
        // Reset to default sorting (by date, most recent first)
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');
        const currentView = recentView.style.display !== 'none' ? recentView : allView;
        
        const projectItems = currentView.querySelectorAll('.project-item');
        const projectItemsArray = Array.from(projectItems);
        
        // Sort by most recent by default
        projectItemsArray.sort((a, b) => {
            const dateA = new Date(a.getAttribute('data-upload-date'));
            const dateB = new Date(b.getAttribute('data-upload-date'));
            return dateB - dateA;
        });
        
        // Re-insert items
        currentView.innerHTML = '';
        projectItemsArray.forEach(item => {
            currentView.appendChild(item);
        });
    }

    function sortProjects(criteria) {
        console.log('Sorting by:', criteria);
        
        // Get the current active view (allView or recentView)
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');
        const currentView = recentView.style.display !== 'none' ? recentView : allView;
        
        // Get project items from the CURRENTLY VISIBLE view only
        const projectItems = currentView.querySelectorAll('.project-item');
        const projectItemsArray = Array.from(projectItems);
        
        if (projectItemsArray.length === 0) {
            console.log('No project items found in current view');
            return;
        }
    
        // Sort the array based on criteria
        switch(criteria) {
            case 'recent':
                // Most recent first (newest dates first)
                projectItemsArray.sort((a, b) => {
                    const dateA = new Date(a.getAttribute('data-upload-date'));
                    const dateB = new Date(b.getAttribute('data-upload-date'));
                    return dateB - dateA;
                });
                break;
                
            case 'Oldest':
                // Oldest first (oldest dates first)
                projectItemsArray.sort((a, b) => {
                    const dateA = new Date(a.getAttribute('data-upload-date'));
                    const dateB = new Date(b.getAttribute('data-upload-date'));
                    return dateA - dateB;
                });
                break;
                
            case 'title':
                // Title A-Z
                projectItemsArray.sort((a, b) => {
                    const titleA = a.querySelector('h3').textContent.toLowerCase().trim();
                    const titleB = b.querySelector('h3').textContent.toLowerCase().trim();
                    return titleA.localeCompare(titleB);
                });
                break;
                
            case 'titleReversed':
                // Title Z-A
                projectItemsArray.sort((a, b) => {
                    const titleA = a.querySelector('h3').textContent.toLowerCase().trim();
                    const titleB = b.querySelector('h3').textContent.toLowerCase().trim();
                    return titleB.localeCompare(titleA);
                });
                break;
        }
    
        // Clear and re-insert sorted items into the CURRENT view only
        currentView.innerHTML = '';
        projectItemsArray.forEach(item => {
            currentView.appendChild(item);
        });
    
        console.log('Sorting completed for criteria:', criteria, 'in current view');
        
        // Re-run animations
        animateOnScroll();
    }

    initializeFilterDropdowns();

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const projectItems = document.querySelectorAll('.project-item');
            const notFound = document.getElementById('notFound');
            
            let foundResults = false;
            
            projectItems.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                
                // Only search by title/name now (removed description and tags search)
                if (title.includes(searchTerm)) {
                    item.style.display = 'flex';
                    foundResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide the "No Results Found" message based on whether we found any results
            if (foundResults || searchTerm === '') {
                notFound.style.display = 'none';
            } else {
                notFound.style.display = 'flex';
            }
            
            // Re-run animations after searching
            animateOnScroll();
        });
    }

    // Display toggle functionality
    const listViewIcon = document.getElementById('listViewIcon');
    const gridViewIcon = document.getElementById('gridViewIcon');

    if (listViewIcon && gridViewIcon) {
        const displayGroupIcons = document.querySelectorAll('.display-group .icon');

        listViewIcon.addEventListener('click', function() {
            // Switch to list view for ALL project containers
            const projectsContainers = document.querySelectorAll('.projects');
            projectsContainers.forEach(container => {
                container.style.gridTemplateColumns = '1fr';
            });
            
            // Update icon states
            displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });

        gridViewIcon.addEventListener('click', function() {
            // Switch to grid view for ALL project containers
            const projectsContainers = document.querySelectorAll('.projects');
            projectsContainers.forEach(container => {
                container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
            });
            
            // Update icon states
            displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });
    }

    // Animation on scroll functionality
    const animateOnScroll = function() {
        const projectItems = document.querySelectorAll('.project-item');
        
        // Remove any existing animation classes
        projectItems.forEach(item => {
            item.classList.remove('animate__animated', 'animate__fadeInUp', 'animate__fast');
        });
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp', 'animate__fast');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });
        
        // Observe all project items
        projectItems.forEach(item => {
            // Only observe if the item is visible
            if (window.getComputedStyle(item).display !== 'none') {
                observer.observe(item);
            }
        });
    };
    
    // Call the animation function
    animateOnScroll();

    // Initialize with recent view visible
    if (allView && allButton) {
        switchView(allView, allButton);
    }

    //download functionality for logs with SweetAlert confirmation
    const logDownloadButtons = document.querySelectorAll('.fa-file-arrow-down');
    logDownloadButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Determine which log type this button is for
            const logContainer = this.closest('.log-content');
            const logType = logContainer.id.includes('user') ? 'User' : 'Admin';
            
            Swal.fire({
                title: `Download ${logType} Logs?`,
                text: `Do you want to download the ${logType.toLowerCase()} logs as a CSV file?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, download!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulate download process
                    Swal.fire({
                        title: 'Download Started!',
                        text: `${logType} logs are being downloaded.`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // In a real application, you would trigger the actual download here
                    // For demonstration, we'll create a dummy download
                    setTimeout(() => {
                        // Create a dummy CSV content
                        const csvContent = "data:text/csv;charset=utf-8,";
                        
                        // Create a temporary link element
                        const encodedUri = encodeURI(csvContent);
                        const link = document.createElement("a");
                        link.setAttribute("href", encodedUri);
                        link.setAttribute("download", `${logType.toLowerCase()}_logs_${new Date().toISOString().split('T')[0]}.csv`);
                        document.body.appendChild(link);
                        
                        // Trigger the download
                        link.click();
                        
                        // Clean up
                        document.body.removeChild(link);
                    }, 1000);
                }
            });
        });
    });

    // Function to filter users by role
    function filterUsersByRole(role) {
        const userItems = document.querySelectorAll('.admin-user-item');
        let foundResults = false;
        
        userItems.forEach(item => {
            const userRole = getUserRoleFromItem(item);
            
            if (role === 'all' || userRole === role) {
                item.style.display = 'flex';
                foundResults = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        const notFound = document.getElementById('adminNotFound');
        if (foundResults || role === 'all') {
            if (notFound) notFound.style.display = 'none';
        } else {
            if (notFound) notFound.style.display = 'block';
        }
    }

    function resetAccessManagementState() {
        // Remove active class from all access cards
        document.querySelectorAll('.accessCard').forEach(card => {
            card.classList.remove('active');
        });
        
        // Reset search input
        const adminUserSearch = document.getElementById('adminUserSearch');
        if (adminUserSearch) {
            adminUserSearch.value = '';
            adminUserSearch.setAttribute('data-current-filter', 'all');
        }
        
        // Reset to show all users
        filterUsersByRole('all');
        
        // Reset any other access management state if needed
        const allAccessBtn = document.getElementById('allAccessBtn');
        if (allAccessBtn) {
            allAccessBtn.classList.add('active');
        }
        
        // Reset role changes if any
        roleChanges = {};
        changesMade = false;
        updateSaveButtonVisibility();
    }

    function updateSaveButtonVisibility() {
        const saveBtn = document.getElementById('saveAdminChangesBtn');
        if (saveBtn) {
            if (changesMade && Object.keys(roleChanges).length > 0) {
                saveBtn.style.display = 'flex';
            } else {
                saveBtn.style.display = 'none';
            }
        }
    }
    
    function getUserRoleFromItem(userItem) {
        const roleText = userItem.querySelector('.role-checkbox p')?.textContent || '';
        
        if (roleText.includes('Admin')) {
            return 'admin';
        } else if (roleText.includes('Faculty')) {
            return 'faculty';
        } else if (roleText.includes('Student')) {
            return 'student';
        }
        
        return ''; // Default if no role found
    }

    // Event listeners for access cards---------------------------------------------------------------------------------------------
    if (adminAccessBtn) {
        adminAccessBtn.addEventListener('click', function() {
            // Remove active class from all cards
            document.querySelectorAll('.accessCard').forEach(card => {
                card.classList.remove('active');
            });
            
            // Add active class to clicked card
            this.classList.add('active');
            
            filterUsersByRole('admin');
            
            // Update search to work with current filter
            const searchInput = document.getElementById('adminUserSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.setAttribute('data-current-filter', 'admin');
            }
        });
    }
    
    if (facultyAccessBtn) {
        facultyAccessBtn.addEventListener('click', function() {
            // Remove active class from all cards
            document.querySelectorAll('.accessCard').forEach(card => {
                card.classList.remove('active');
            });
            
            // Add active class to clicked card
            this.classList.add('active');
            
            filterUsersByRole('faculty');
            
            // Update search to work with current filter
            const searchInput = document.getElementById('adminUserSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.setAttribute('data-current-filter', 'faculty');
            }
        });
    }
    
    if (studentAccessBtn) {
        studentAccessBtn.addEventListener('click', function() {
            // Remove active class from all cards
            document.querySelectorAll('.accessCard').forEach(card => {
                card.classList.remove('active');
            });
            
            // Add active class to clicked card
            this.classList.add('active');
            
            filterUsersByRole('student');
            
            // Update search to work with current filter
            const searchInput = document.getElementById('adminUserSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.setAttribute('data-current-filter', 'student');
            }
        });
    }

    function initializeAllFilter() {
        const allAccessBtn = document.getElementById('allAccessBtn'); 
        if (allAccessBtn) {
            allAccessBtn.addEventListener('click', function() {
                // Remove active class from all cards
                document.querySelectorAll('.accessCard').forEach(card => {
                    card.classList.remove('active');
                });
                
                // Add active class to clicked card
                this.classList.add('active');
                
                filterUsersByRole('all');
                
                // Update search to work with current filter
                const searchInput = document.getElementById('adminUserSearch');
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.setAttribute('data-current-filter', 'all');
                }
            });
        }
    }

    // Admin Access Management Search Functionality - FIXED
    const adminUserSearch = document.getElementById('adminUserSearch');
    if (adminUserSearch) {
        adminUserSearch.oninput = function() {
            const searchTerm = this.value.toLowerCase().trim();
            const userItems = document.querySelectorAll('.admin-user-item');
            const notFound = document.getElementById('adminNotFound');
            const currentFilter = this.getAttribute('data-current-filter') || 'all';
            
            let foundResults = false;
            
            userItems.forEach(item => {
                const userName = item.querySelector('h4').textContent.toLowerCase();
                const userEmail = item.querySelector('p').textContent.toLowerCase();
                
                // Check if item matches search term
                const matchesSearch = userName.includes(searchTerm) || userEmail.includes(searchTerm);
                
                // Check if item matches current filter
                let matchesFilter = true;
                if (currentFilter && currentFilter !== 'all') {
                    const userRole = getUserRoleFromItem(item);
                    matchesFilter = userRole === currentFilter;
                }
                
                if (matchesSearch && matchesFilter) {
                    item.style.display = 'flex';
                    foundResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide the "No Results Found" message
            if (foundResults || searchTerm === '') {
                if (notFound) notFound.style.display = 'none';
            } else {
                if (notFound) notFound.style.display = 'block';
            }
        };
    }

    // Initialize role change handling
    function initializeRoleChangeHandling() {
        const roleCheckboxes = document.querySelectorAll('.role-checkbox input');
        
        roleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const userId = this.getAttribute('data-user-id');
                const role = this.getAttribute('name');
                const isChecked = this.checked;
                
                trackRoleChange(userId, role, isChecked);
            });
        });
    }

    // Save Admin Changes Function - FIXED (moved inside DOMContentLoaded)
    const saveAdminChangesBtn = document.getElementById('saveAdminChangesBtn');
    if (saveAdminChangesBtn) {
        saveAdminChangesBtn.addEventListener('click', function() {
            if (!changesMade) {
                Swal.fire({
                    title: 'No Changes',
                    text: 'You haven\'t made any changes to save.',
                    icon: 'info',
                    confirmButtonColor: 'var(--primary-color)'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Changes',
                text: 'Are you sure you want to save these role changes?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--primary-color)',
                cancelButtonColor: 'var(--color-lite-grey)',
                confirmButtonText: 'Yes, save changes!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    // Simulate API call
                    setTimeout(() => {
                        // Reset changes
                        changesMade = false;
                        roleChanges = {};
                        
                        // Restore button content
                        this.innerHTML = originalHtml;
                        
                        // Show success message
                        Swal.fire({
                            title: 'Saved!',
                            text: 'User roles have been updated.',
                            icon: 'success',
                            confirmButtonColor: 'var(--primary-color)'
                        });
                    }, 1500);
                }
            });
        });
    }

    // Initialize the role change handling when the page loads
    initializeRoleChangeHandling();

    // Account Management Filtering
    const allAccountsButton = document.getElementById('allAccountsButton');
    const pendingButton = document.getElementById('pendingButton');
    const approvedButton = document.getElementById('approvedButton');

    // Account status containers
    const allAccountsContainer = document.getElementById('allAccounts-container');
    const pendingAccountsContainer = document.getElementById('pendingAccounts-container');
    const approvedAccountsContainer = document.getElementById('approvedAccounts-container');

    // Function to switch account views
    function switchAccountView(viewToShow, buttonToSelect) {
        // Hide all account views
        const allAccountsView = document.getElementById('allAccounts-container');
        const pendingAccountsView = document.getElementById('pendingAccounts-container');
        const approvedAccountsView = document.getElementById('approvedAccounts-container');
        
        if (allAccountsView) allAccountsView.style.display = 'none';
        if (pendingAccountsView) pendingAccountsView.style.display = 'none';
        if (approvedAccountsView) approvedAccountsView.style.display = 'none';
        
        // Remove active class from all buttons
        const accountButtons = document.querySelectorAll('.logMenu button');
        accountButtons.forEach(button => button.classList.remove('selected'));
        
        // Show selected account view and activate button
        if (viewToShow && buttonToSelect) {
            viewToShow.style.display = 'block';
            buttonToSelect.classList.add('selected');
        }
        
        // Filter table rows based on selected view
        filterTableRows(buttonToSelect.id);
    }

    function filterTableRows(buttonId) {
        const tableRows = document.querySelectorAll('.accounts-table tbody tr');
        const notFound = document.getElementById('notFound'); // You might want to add this for accounts
        
        let foundResults = false;
        
        tableRows.forEach(row => {
            const statusBadge = row.querySelector('.status-badge');
            const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
            
            switch(buttonId) {
                case 'allAccountsButton':
                    row.style.display = '';
                    foundResults = true;
                    break;
                case 'pendingButton':
                    if (status === 'pending') {
                        row.style.display = '';
                        foundResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                    break;
                case 'approvedButton':
                    if (status === 'approved') {
                        row.style.display = '';
                        foundResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                    break;
                default:
                    row.style.display = '';
                    foundResults = true;
            }
        });
        
        // Show/hide no results message if you add one
        // if (notFound) {
        //     notFound.style.display = foundResults ? 'none' : 'block';
        // }
    }

    // Event listeners for account filter buttons
    if (allAccountsButton && pendingButton && approvedButton) {
        allAccountsButton.addEventListener('click', function() {
            switchAccountView(document.getElementById('allAccounts-container'), allAccountsButton);
        });
        
        pendingButton.addEventListener('click', function() {
            switchAccountView(document.getElementById('pendingAccounts-container'), pendingButton);
        });
        
        approvedButton.addEventListener('click', function() {
            switchAccountView(document.getElementById('approvedAccounts-container'), approvedButton);
        });
    }

    // Initialize with all accounts view
    if (allAccountsContainer && allAccountsButton) {
        switchAccountView(allAccountsContainer, allAccountsButton);
    }

    // Account search functionality
    const accountSearchInput = document.getElementById('accountSearchInput');
    if (accountSearchInput) {
    accountSearchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const accountRows = document.querySelectorAll('.accounts-table tbody tr');
        const activeButton = document.querySelector('.logMenu button.selected');
        const activeFilter = activeButton ? activeButton.id : 'allAccountsButton';
        
        accountRows.forEach(row => {
            const name = row.querySelector('td:first-child').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const statusBadge = row.querySelector('.status-badge');
            const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
            
            // Check if row matches search term
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            
            // Check if row matches current filter
            let matchesFilter = true;
            switch(activeFilter) {
                case 'pendingButton':
                    matchesFilter = status === 'pending';
                    break;
                case 'approvedButton':
                    matchesFilter = status === 'approved';
                    break;
                default:
                    matchesFilter = true; // allAccountsButton shows all
            }
            
            if (matchesSearch && matchesFilter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    }

    //Logout Function
    logoutMenu.innerHTML = `
        <div class="user-info">
            <div class="user-name">${displayName}</div>
            <div class="user-role">${userRole}</div>
        </div>
        <button class="logout-menu-btn">
            <i class="fas fa-sign-out-alt"></i>Logout
        </button>
    `;

    // Add the logout menu to the sidebar
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.appendChild(logoutMenu);
    }

    // Toggle logout menu visibility
    if (moreOptionsIcon) {
        moreOptionsIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = logoutMenu.style.display === 'block';
            
            // Hide all other open menus first
            document.querySelectorAll('.logout-menu').forEach(menu => {
                menu.style.display = 'none';
            });
            
            logoutMenu.style.display = isVisible ? 'none' : 'block';
        });
    }

    // Close logout menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!logoutMenu.contains(e.target) && e.target !== moreOptionsIcon) {
            logoutMenu.style.display = 'none';
        }
    });

    // Logout functionality from the menu
    const logoutMenuBtn = logoutMenu.querySelector('.logout-menu-btn');
    if (logoutMenuBtn) {
        logoutMenuBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of your admin account",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to admin logout endpoint
                    window.location.href = '../../../app/Controllers/AdminController.php?action=logout';
                }
            });
            
            // Close the menu after clicking
            logoutMenu.style.display = 'none';
        });
    }
    
    // Function to track role changes
    function trackRoleChange(userId, role, isChecked) {
        if (!roleChanges[userId]) {
            roleChanges[userId] = {};
        }
        
        // If this role is being checked, uncheck others for this user
        if (isChecked) {
            const userItem = document.querySelector(`.admin-user-item[data-user-id="${userId}"]`);
            const otherCheckboxes = userItem.querySelectorAll(`.role-checkbox input:not([name="${role}"])`);
            
            otherCheckboxes.forEach(otherCheckbox => {
                const otherRole = otherCheckbox.getAttribute('name');
                roleChanges[userId][otherRole] = false;
                otherCheckbox.checked = false;
            });
        }
        
        roleChanges[userId][role] = isChecked;
        changesMade = true;
        updateSaveButtonVisibility();
    }
    
    // Add hover effect to the ellipsis icon
    if (moreOptionsIcon) {
        moreOptionsIcon.addEventListener('mouseenter', function() {
            this.style.color = 'var(--color-white)';
        });
        
        moreOptionsIcon.addEventListener('mouseleave', function() {
            this.style.color = 'var(--color-lite)';
        });
    }
    
    
    // Function to approve account
    async function approveAccount(userId, currentStatus, button) {
        try {
            Swal.fire({
                title: 'Approve Account?',
                text: 'Are you sure you want to approve this account?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve!',
                cancelButtonText: 'Cancel'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const originalHtml = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    button.disabled = true;
                    
                    try {
                        const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=updateUserStatus', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                status: 'approved'
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            Swal.fire({
                                title: 'Approved!',
                                text: result.message || 'Account has been approved successfully.',
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                // Reload the page to reflect changes
                                location.reload();
                            });
                        } else {
                            throw new Error(result.error || 'Failed to approve account');
                        }
                    } catch (error) {
                        console.error('Error approving account:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to approve account. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        
                        // Restore button state
                        button.innerHTML = originalHtml;
                        button.disabled = false;
                    }
                }
            });
        } catch (error) {
            console.error('Error in approveAccount:', error);
        }
    }

    // Function to delete account
    async function deleteAccount(userId, userName, button) {
        try {
            Swal.fire({
                title: 'Delete Account?',
                html: `Are you sure you want to delete <strong>${userName}</strong>'s account? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const originalHtml = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    button.disabled = true;
                    
                    try {
                        const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=deleteUser', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                user_id: userId
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: result.message || 'Account has been deleted successfully.',
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                // Reload the page to reflect changes
                                location.reload();
                            });
                        } else {
                            throw new Error(result.error || 'Failed to delete account');
                        }
                    } catch (error) {
                        console.error('Error deleting account:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to delete account. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        
                        // Restore button state
                        button.innerHTML = originalHtml;
                        button.disabled = false;
                    }
                }
            });
        } catch (error) {
            console.error('Error in deleteAccount:', error);
        }
    }

    // Account management event listeners
    document.addEventListener('click', function(e) {
        // Approve account functionality
        if (e.target.closest('.approve-btn') && !e.target.closest('.approve-btn').disabled) {
            const button = e.target.closest('.approve-btn');
            const userId = button.getAttribute('data-user-id');
            const currentStatus = button.getAttribute('data-user-status');
            approveAccount(userId, currentStatus, button);
        }
        
        // Delete account functionality
        if (e.target.closest('.delete-btn')) {
            const button = e.target.closest('.delete-btn');
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            deleteAccount(userId, userName, button);
        }
    });

    // Load users data when accounts section is shown
    const accountsOption = document.querySelector('.menu-options li[data-view="accounts"]');
    if (accountsOption) {
        accountsOption.addEventListener('click', function() {
            // The table is already populated with PHP, so no need to load via AJAX
            // But you can add any initialization code here if needed
            console.log('Accounts section loaded');
        });
    }

    //Upload Functionality
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email.trim());
    }

    // Function to validate multiple USEP emails separated by commas
    function validateAuthorEmails(emailString) {
        if (!emailString.trim()) return { isValid: false, emails: [] };
        
        const emails = emailString.split(',').map(email => email.trim()).filter(email => email !== '');
        
        // Check if all emails are valid
        const invalidEmails = emails.filter(email => !isValidEmail(email));
        
        return {
            isValid: invalidEmails.length === 0,
            emails: emails,
            invalidEmails: invalidEmails
        };
    }

    function validateAuthorsBeforeUpload(authorEmails) {
        return new Promise((resolve, reject) => {
            const emails = authorEmails.split(',').map(email => email.trim()).filter(email => email !== '');
            
            if (emails.length === 0) {
                resolve();
                return;
            }
            
            // Check if any emails belong to faculty
            fetch('../../../app/Controllers/AdminDashboardController.php?action=checkUserRoles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    emails: emails,
                    check_type: 'authors'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (data.facultyUsers && data.facultyUsers.length > 0) {
                        reject(`Faculty users cannot be listed as authors. Please remove the following faculty emails: ${data.facultyUsers.join(', ')}`);
                    } else {
                        resolve();
                    }
                } else {
                    reject(data.error || 'Error checking user roles');
                }
            })
            .catch(error => {
                console.error('Error validating authors:', error);
                reject('Unable to verify user roles. Please try again.');
            });
        });
    }

    function validateAdviserBeforeUpload(adviserEmail) {
        return new Promise((resolve, reject) => {
            if (!adviserEmail.trim()) {
                resolve();
                return;
            }
            
            // Check if adviser email belongs to faculty
            fetch('../../../app/Controllers/AdminDashboardController.php?action=checkUserRoles', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    emails: [adviserEmail.trim()],
                    check_type: 'advisers'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Check if the adviser is faculty
                    const isFaculty = data.userRoles && data.userRoles[adviserEmail] === 'faculty';
                    const userExists = data.userRoles && data.userRoles[adviserEmail] !== 'not_found';
                    
                    if (!userExists) {
                        reject(`The adviser email "${adviserEmail}" was not found in the system.`);
                    } else if (!isFaculty) {
                        reject(`The adviser email must belong to a faculty member. "${adviserEmail}" is not a faculty user.`);
                    } else {
                        resolve();
                    }
                } else {
                    reject(data.error || 'Error checking adviser role');
                }
            })
            .catch(error => {
                console.error('Error validating adviser:', error);
                reject('Unable to verify adviser role. Please try again.');
            });
        });
    }

    function updateUploadButtonState() {
        const thesisTitle = document.getElementById('thesisTitle');
        const thesisAuthor = document.getElementById('thesisAuthor');
        const thesisAdviser = document.getElementById('thesisAdviser');
        const departmentSelect = document.getElementById('departmentSelect');
        const courseInput = document.getElementById('courseInput');
        const hardboundSelect = document.getElementById('hardboundSelect');
        const uploadBtn = document.getElementById('uploadBtn');
        
        // Validate author emails
        const authorEmailValidation = validateAuthorEmails(thesisAuthor.value);
        
        // Validate adviser email
        const adviserEmailValidation = {
            isValid: thesisAdviser.value.trim() === '' ? true : isValidEmail(thesisAdviser.value.trim())
        };
        
        // Check if department has a selected value
        const isDepartmentSelected = departmentSelect && departmentSelect.value !== '';
        const isCourseSelected = courseInput && courseInput.value !== '' && !courseInput.disabled;
        
        // Check if both file types have at least one file
        const hasAbstractFiles = uploadedFiles.abstract.length > 0;
        const hasThesisFiles = uploadedFiles.thesis.length > 0;

        const isTitleValid = thesisTitle.value.trim() !== '';
        
        const isFormValid = isTitleValid &&
                       thesisAuthor.value.trim() !== '' &&
                       thesisAdviser.value.trim() !== '' &&
                       authorEmailValidation.isValid &&
                       adviserEmailValidation.isValid &&
                       isDepartmentSelected &&
                       isCourseSelected &&
                       hasAbstractFiles &&
                       hasThesisFiles;
        
        if (uploadBtn) {
            uploadBtn.disabled = !isFormValid;
        }
        
        // Update visual feedback for author email field
        if (thesisAuthor) {
            if (thesisAuthor.value.trim() === '') {
                thesisAuthor.style.borderColor = '#ddd';
            } else if (!authorEmailValidation.isValid) {
                thesisAuthor.style.borderColor = 'var(--color-danger)';
            } else {
                thesisAuthor.style.borderColor = '#51cf66';
            }
        }
        
        // Update visual feedback for title field
        if (thesisTitle) {
            if (thesisTitle.value.trim() === '') {
                thesisTitle.style.borderColor = '#ddd';
            } else {
                // Only check title existence if we're NOT in edit mode
                const uploadBtn = document.getElementById('uploadBtn');
                const isEditMode = uploadBtn && uploadBtn.getAttribute('data-thesis-id');
                
                if (!isEditMode) {
                    // Check title availability in real-time (debounced) only for new uploads
                    clearTimeout(window.titleCheckTimeout);
                    window.titleCheckTimeout = setTimeout(() => {
                        checkTitleExists(thesisTitle.value.trim()).then(exists => {
                            if (exists) {
                                thesisTitle.style.borderColor = 'var(--color-danger)';
                                showTitleWarning('This title already exists');
                            } else {
                                thesisTitle.style.borderColor = '#51cf66';
                                hideTitleWarning();
                            }
                        });
                    }, 500);
                } else {
                    // In edit mode, just show valid state
                    thesisTitle.style.borderColor = '#51cf66';
                    hideTitleWarning();
                }
            }
        }

        function showTitleWarning(message) {
            let warningElement = document.getElementById('titleWarning');
            if (!warningElement) {
                warningElement = document.createElement('div');
                warningElement.id = 'titleWarning';
                warningElement.className = 'title-warning';
                warningElement.style.color = 'var(--color-danger)';
                warningElement.style.fontSize = '12px';
                warningElement.style.marginTop = '5px';
                
                const titleInput = document.getElementById('thesisTitle');
                titleInput.parentNode.appendChild(warningElement);
            }
            warningElement.textContent = message;
            warningElement.style.display = 'block';
        }
        
        function hideTitleWarning() {
            const warningElement = document.getElementById('titleWarning');
            if (warningElement) {
                warningElement.style.display = 'none';
            }
        }
        
        // Update visual feedback for adviser email field
        if (thesisAdviser) {
            if (thesisAdviser.value.trim() === '') {
                thesisAdviser.style.borderColor = '#ddd';
            } else if (!adviserEmailValidation.isValid) {
                thesisAdviser.style.borderColor = 'var(--color-danger)';
            } else {
                thesisAdviser.style.borderColor = '#51cf66';
            }
        }
        
        // Update visual feedback for department field
        if (departmentSelect) {
            if (!isDepartmentSelected) {
                departmentSelect.style.borderColor = 'rgb(221, 221, 221)';
            } else {
                departmentSelect.style.borderColor = '#51cf66';
            }
        }

        // Update visual feedback for course field
        if (courseInput) {
            if (!isDepartmentSelected) {
                courseInput.style.borderColor = '#ddd';
            } else if (!isCourseSelected) {
                courseInput.style.borderColor = 'var(--color-danger)';
            } else {
                courseInput.style.borderColor = '#51cf66';
            }
        }

        if(hardboundSelect.value == 'No') {
            hardboundSelect.style.borderColor = '#ddd';
            } else {
                hardboundSelect.style.borderColor = '#51cf66';
            }
        
        // Update file icon state
        updateFileIconState();
    }

    function updateFileIconState() {
        const thesisTitle = document.getElementById('thesisTitle');
        const thesisAuthor = document.getElementById('thesisAuthor');
        const thesisAdviser = document.getElementById('thesisAdviser');
        const departmentSelect = document.getElementById('departmentSelect');
        const courseInput = document.getElementById('courseInput');
        
        const fileIcon = document.getElementById('notif');
        
        if (!fileIcon) return;
        
        // Validate emails
        const authorEmailValidation = validateAuthorEmails(thesisAuthor.value);
        const adviserEmailValidation = {
            isValid: thesisAdviser.value.trim() === '' ? true : isValidEmail(thesisAdviser.value.trim())
        };
        
        // Check if department has a selected value
        const isDepartmentSelected = departmentSelect && departmentSelect.value !== '';
        
        // Check file requirements
        const hasAbstractFiles = uploadedFiles.abstract.length > 0;
        const hasThesisFiles = uploadedFiles.thesis.length > 0;
        
        const isFormValid = thesisTitle.value.trim() !== '' &&
                           thesisAuthor.value.trim() !== '' &&
                           thesisAdviser.value.trim() !== '' &&
                           authorEmailValidation.isValid &&
                           adviserEmailValidation.isValid &&
                           isDepartmentSelected &&
                           courseInput.value.trim() !== '' &&
                           hasAbstractFiles &&
                           hasThesisFiles;
        
        const hasSomeFiles = hasAbstractFiles || hasThesisFiles;
        const hasSomeFormData = thesisTitle.value.trim() !== '';
        
        // Remove all state classes
        fileIcon.classList.remove('good', 'error', 'warning');
        
        if (isFormValid) {
            // All requirements met - ready state (green)
            fileIcon.classList.add('good');
        } else if ((hasAbstractFiles || hasThesisFiles) && hasSomeFormData) {
            // Some requirements met but not all - warning state (orange)
            fileIcon.classList.add('warning');
        }
    }

    async function checkTitleExists(title) {
        try {
            const response = await fetch('../../../app/Controllers/ThesisController.php?action=checkTitleExists', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title: title })
            });
            
            const data = await response.json();
            return data.exists;
        } catch (error) {
            console.error('Error checking title:', error);
            return false;
        }
    }

    // Function to validate title before upload
    function validateTitleBeforeUpload(title) {
        return new Promise((resolve, reject) => {
            if (!title.trim()) {
                reject('Thesis title is required');
                return;
            }
            
            checkTitleExists(title.trim())
                .then(exists => {
                    if (exists) {
                        reject('A thesis with this title already exists. Please choose a different title.');
                    } else {
                        resolve();
                    }
                })
                .catch(error => {
                    reject('Unable to verify title availability. Please try again.');
                });
        });
    }

    function initializeUploadModal() {
        const fabIcon = document.querySelector('.fab-icon');
        const uploadModal = document.getElementById('uploadModal');
        
        if (fabIcon && uploadModal) {
            fabIcon.addEventListener('click', function() {
                console.log('FAB clicked, opening modal');
                try {
                    // FIX: Reset file inputs when modal opens
                    if (abstractFileInput) abstractFileInput.value = '';
                    if (thesisFileInput) thesisFileInput.value = '';
                    
                    uploadModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    console.log('Modal opened successfully');
                } catch (error) {
                    console.error('Error opening modal:', error);
                }
            });
        }
    }

    initializeUploadModal();
    
    function initializeUploadFormValidation() {
        const thesisAuthor = document.getElementById('thesisAuthor');
        const thesisAdviser = document.getElementById('thesisAdviser');
        const hardboundSelect = document.getElementById('hardboundSelect');
    
        // Hardbound select change event
        if (hardboundSelect) {
            hardboundSelect.addEventListener('change', function() {
                updateUploadButtonState();
            });
        }

        // Author email validation
        if (thesisAuthor) {
            thesisAuthor.addEventListener('blur', function() {
                const emailValidation = validateAuthorEmails(this.value);
                
                if (this.value.trim() && !emailValidation.isValid) {
                    const invalidEmailsList = emailValidation.invalidEmails.join(', ');
                    Swal.fire({
                        title: 'Invalid Email Format',
                        html: `The following emails are invalid: <strong>${invalidEmailsList}</strong><br><br>
                               Please enter valid email addresses separated by commas.`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
                
                updateUploadButtonState();
            });
            
            // Real-time validation as user types
            thesisAuthor.addEventListener('input', function() {
                updateUploadButtonState();
            });
        }
        
        // Adviser email validation
        if (thesisAdviser) {
            thesisAdviser.addEventListener('blur', function() {
                if (this.value.trim() && !isValidEmail(this.value.trim())) {
                    Swal.fire({
                        title: 'Invalid Email Format',
                        text: 'Please enter a valid email address for the adviser.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
                
                updateUploadButtonState();
            });
            
            // Real-time validation as user types
            thesisAdviser.addEventListener('input', function() {
                updateUploadButtonState();
            });
        }
        
        // Add event listeners to other form fields
        const otherFormFields = [
            'thesisTitle',
            'departmentSelect',
            'courseInput',
            'hardboundSelect'
        ];
        
        otherFormFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', updateUploadButtonState);
                field.addEventListener('change', updateUploadButtonState);
            }
        });
        
        // Add event listeners for file uploads
        if (abstractFileInput) {
            abstractFileInput.addEventListener('change', updateUploadButtonState);
        }
        if (thesisFileInput) {
            thesisFileInput.addEventListener('change', updateUploadButtonState);
        }
    }

    function initializeUploadFormSubmission() {
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm && btnUpload) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
    
                const uploadBtn = document.getElementById('uploadBtn');
                const thesisId = uploadBtn.getAttribute('data-thesis-id');
                
                if (thesisId) {
                    updateThesis(thesisId);
                    return; // Stop further execution for update
                }
                
                // Check if both file types have files
                if (uploadedFiles.abstract.length === 0) {
                    Swal.fire({
                        title: 'Abstract File Required',
                        text: 'Please select at least one abstract file.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                if (uploadedFiles.thesis.length === 0) {
                    Swal.fire({
                        title: 'Thesis File Required',
                        text: 'Please select at least one thesis file.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
    
                // Validate thesis title
                const thesisTitleInput = document.getElementById('thesisTitle');
                if (thesisTitleInput && !thesisTitleInput.value.trim()) {
                    Swal.fire({
                        title: 'Thesis Title Required',
                        text: 'Please enter a title for your thesis.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                // Validate author emails
                const thesisAuthorInput = document.getElementById('thesisAuthor');
                const authorEmailValidation = validateAuthorEmails(thesisAuthorInput.value);
                
                if (!authorEmailValidation.isValid) {
                    const invalidEmailsList = authorEmailValidation.invalidEmails.join(', ');
                    Swal.fire({
                        title: 'Invalid Email Addresses',
                        html: `The following author emails are not valid: <strong>${invalidEmailsList}</strong><br><br>
                               Please use valid email addresses for all authors.`,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                // Validate adviser email
                const thesisAdviserInput = document.getElementById('thesisAdviser');
                if (thesisAdviserInput.value.trim() && !isValidEmail(thesisAdviserInput.value.trim())) {
                    Swal.fire({
                        title: 'Invalid Adviser Email',
                        text: 'Please enter a valid email address for the adviser.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                // Validate department
                const departmentSelect = document.getElementById('departmentSelect');
                if (!departmentSelect || !departmentSelect.value) {
                    Swal.fire({
                        title: 'Department Required',
                        text: 'Please select a department.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                // Validate course
                const courseInput = document.getElementById('courseInput');
                if (!courseInput.value.trim()) {
                    Swal.fire({
                        title: 'Course Required',
                        text: 'Please enter a course/program.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const hardboundSelect = document.getElementById('hardboundSelect');
                const hardboundValue = hardboundSelect ? hardboundSelect.value : 'Yes'; // Default to 'Yes'
                
                // NEW: Validate authors don't include faculty users
                validateTitleBeforeUpload(thesisTitleInput.value)
                    .then(() => {
                        // Validate authors don't include faculty users
                        return validateAuthorsBeforeUpload(thesisAuthorInput.value);
                    })
                    .then(() => {
                        // Validate adviser is faculty
                        if (thesisAdviserInput.value.trim()) {
                            return validateAdviserBeforeUpload(thesisAdviserInput.value.trim());
                        } else {
                            return Promise.resolve();
                        }
                    })
                    .then(() => {
                        // Proceed with upload if validation passes
                        const authorEmails = authorEmailValidation.emails.join(', ');
                        const departmentText = departmentSelect.options[departmentSelect.selectedIndex].text;
                        
                        const totalFiles = (uploadedFiles.abstract.length + uploadedFiles.thesis.length);
    
                        Swal.fire({
                            title: 'Confirm Upload',
                            html: `Are you sure you want to upload <strong>${thesisTitleInput.value}</strong>?<br><br>
                                <strong>Authors:</strong> ${authorEmails}<br>
                                <strong>Adviser:</strong> ${thesisAdviserInput.value}<br>
                                <strong>Department:</strong> ${departmentText}<br>
                                <strong>Course:</strong> ${courseInput.value}<br>
                                <strong>Hardbound Available:</strong> ${hardboundValue}<br>
                                <strong>Abstract Files:</strong> ${uploadedFiles.abstract.length} file(s)<br>
                                <strong>Thesis Files:</strong> ${uploadedFiles.thesis.length} file(s)<br>
                                <strong>Total Files:</strong> ${totalFiles} file(s)`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, upload it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show loading state
                                const originalText = btnUpload.textContent;
                                btnUpload.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                                btnUpload.disabled = true;
                                
                                // Create FormData and submit the form
                                const formData = new FormData(uploadForm);
                                
                                // Append the uploaded files to FormData
                                // Append abstract files
                                uploadedFiles.abstract.forEach((file, index) => {
                                    formData.append(`abstract_files[]`, file);
                                });
                                
                                // Append thesis files  
                                uploadedFiles.thesis.forEach((file, index) => {
                                    formData.append(`thesis_files[]`, file);
                                });
                                
                                // Debug: Log form data before sending
                                console.log('Form data being sent:');
                                for (let [key, value] of formData.entries()) {
                                    if (value instanceof File) {
                                        console.log(key + ': ' + value.name + ' (' + value.size + ' bytes)');
                                    } else {
                                        console.log(key + ': ' + value);
                                    }
                                }
                                
                                // Send the request
                                fetch(uploadForm.action, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => {
                                    console.log('Response status:', response.status);
                                    return response.text().then(text => {
                                        console.log('Raw response:', text);
                                        let data;
                                        try {
                                            data = JSON.parse(text);
                                        } catch (e) {
                                            const jsonMatch = text.match(/\{.*\}/s);
                                            if (jsonMatch) {
                                                try {
                                                    data = JSON.parse(jsonMatch[0]);
                                                } catch (e2) {
                                                    throw new Error('Invalid server response format');
                                                }
                                            } else {
                                                throw new Error('Invalid server response format');
                                            }
                                        }
                                        return data;
                                    });
                                })
                                .then(data => {
                                    console.log('Upload response:', data);
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Upload Successful!',
                                            text: data.message || 'Your thesis has been uploaded successfully.',
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            resetUploadForm();
                                            closeModal(uploadModal);
                                            // RELOAD THE PAGE HERE
                                            location.reload();
                                        });
                                    } else {
                                        throw new Error(data.error || 'Upload failed');
                                    }
                                })
                                .catch(error => {
                                    console.error('Upload error:', error);
                                    Swal.fire({
                                        title: 'Upload Failed',
                                        text: error.message || 'Failed to upload thesis. Please try again.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                })
                                .finally(() => {
                                    // Restore button state
                                    btnUpload.textContent = originalText;
                                    btnUpload.disabled = false;
                                });
                            }
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Validation Error',
                            text: error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            });
        }
    }

    function initializeDepartmentFilter() {
        const departmentDropdown = document.getElementById('departmentFilterDropdown');
        if (!departmentDropdown) return;
    
        const selectedElement = departmentDropdown.querySelector('.selected');
        const options = departmentDropdown.querySelectorAll('.options > div');
        
        // Toggle dropdown on click
        selectedElement.addEventListener('click', function(e) {
            e.stopPropagation();
            departmentDropdown.classList.toggle('active');
        });
        
        // Handle option selection
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.textContent.split(' (')[0]; // Remove count from display
                
                // Update selected display
                selectedElement.querySelector('span').textContent = text;
                
                // Close dropdown
                departmentDropdown.classList.remove('active');
                
                // Filter theses based on department
                filterThesesByDepartment(value);
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!departmentDropdown.contains(e.target)) {
                departmentDropdown.classList.remove('active');
            }
        });
    }

    function filterThesesByDepartment(departmentValue) {
        const projectItems = document.querySelectorAll('.project-item');
        const notFound = document.getElementById('notFound');
        let foundResults = false;
        
        console.log('Filtering by department:', departmentValue);
        
        projectItems.forEach(item => {
            // Get the course from the project item
            const courseElement = item.querySelector('.links p:nth-child(2)'); // Second paragraph in links div
            const course = courseElement ? courseElement.textContent.trim() : '';
            
            console.log('Project course:', course);
            
            let shouldShow = false;
            
            if (departmentValue === 'all') {
                shouldShow = true;
            } else {
                // Get course codes for the selected department
                const courseCodes = getCourseCodesForDepartment(departmentValue);
                console.log('Course codes for department:', courseCodes);
                
                // Check if this project's course matches any course in the department
                shouldShow = courseCodes.some(courseCode => course.includes(courseCode) || courseCode.includes(course));
            }
            
            if (shouldShow) {
                item.style.display = 'flex';
                foundResults = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show/hide "No Results Found" message
        if (foundResults || departmentValue === 'all') {
            if (notFound) notFound.style.display = 'none';
        } else {
            if (notFound) notFound.style.display = 'flex';
        }
        
        // Re-run animations after filtering
        animateOnScroll();
    }

    function getCourseCodesForDepartment(departmentValue) {
        // This should match the course codes defined in your PHP DepartmentManager
        const departmentMap = {
            'cs': ['Bachelor of Early Childhood Education'],
            'ee': ['Bachelor of Secondary Education', 'Bachelor of Elementary Education'],
            'me': ['Bachelor of Technical-Vocational Teacher Education', 'Bachelor of Special Needs Education'],
            'ce': ['Bachelor of Science in Agriculture and Biosystems Engineering'],
            'it': ['Bachelor of Science in Information Technology']
        };
        
        return departmentMap[departmentValue] || [];
    }

    function initializeDepartmentCourseLogic() {
        const departmentSelect = document.getElementById('departmentSelect');
        const courseInput = document.getElementById('courseInput');
        
        // Define courses for each department - CORRECTED to match your PHP
        const departmentCourses = {
            'COE': [
                'Bachelor of Science in Agricultural and Biosystems Engineering'
            ],
            'CTET': [
                'Bachelor of Science in Information Technology',
                'Bachelor of Elementary Education',
                'Bachelor of Early Childhood Education',
                'Bachelor of Special Needs Education',
                'Bachelor of Secondary Education',
                'Bachelor of Technical-Vocational Teacher Education'
            ]
        };
        
        // Department change event
        if (departmentSelect) {
            departmentSelect.addEventListener('change', function() {
                const selectedDepartment = this.value;
                
                // Reset and enable/disable course dropdown
                if (courseInput) {
                    courseInput.innerHTML = '<option value="" selected disabled>Select your program</option>';
                    courseInput.disabled = !selectedDepartment;
                    
                    if (selectedDepartment && departmentCourses[selectedDepartment]) {
                        // Add courses for selected department
                        departmentCourses[selectedDepartment].forEach(course => {
                            const option = document.createElement('option');
                            option.value = course;
                            option.textContent = course;
                            courseInput.appendChild(option);
                        });
                        
                        // Enable course selection
                        courseInput.disabled = false;
                    } else {
                        // No department selected or invalid department
                        courseInput.disabled = true;
                    }
                }
                
                // Update upload button state
                updateUploadButtonState();
            });
        }
        
        // Course change event
        if (courseInput) {
            courseInput.addEventListener('change', function() {
                updateUploadButtonState();
            });
        }
        
        // Initialize the state on page load
        if (departmentSelect && courseInput) {
            // Trigger change event to set initial state
            departmentSelect.dispatchEvent(new Event('change'));
        }
    }

    function initializeMoreOptions() {
        // Close all moreOptions when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.moreOptions') && !e.target.closest('.project-item .logo-row .icon')) {
                document.querySelectorAll('.moreOptions').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
    
        // Toggle moreOptions when ellipsis is clicked
        document.addEventListener('click', function(e) {
            const ellipsisIcon = e.target.closest('.project-item .logo-row .icon');
            if (ellipsisIcon) {
                e.preventDefault();
                e.stopPropagation();
                
                const projectItem = ellipsisIcon.closest('.project-item');
                const moreOptions = projectItem.querySelector('.moreOptions');
                
                // Close all other menus
                document.querySelectorAll('.moreOptions').forEach(menu => {
                    if (menu !== moreOptions) {
                        menu.style.display = 'none';
                    }
                });
                
                // Toggle current menu
                if (moreOptions.style.display === 'block') {
                    moreOptions.style.display = 'none';
                } else {
                    moreOptions.style.display = 'block';
                }
            }
        });
    
        // Handle moreOptions button clicks
        document.addEventListener('click', function(e) {
            const moreOptionsBtn = e.target.closest('.moreOptions button');
            if (moreOptionsBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                const moreOptions = moreOptionsBtn.closest('.moreOptions');
                const projectItem = moreOptions.closest('.project-item');
                const thesisId = projectItem.getAttribute('data-thesis-id');
                const thesisTitle = projectItem.querySelector('h3').textContent;
                
                // Determine which button was clicked
                if (moreOptionsBtn.innerHTML.includes('fa-pen')) {
                    // Edit button clicked
                    handleEditThesis(thesisId, thesisTitle);
                } else if (moreOptionsBtn.innerHTML.includes('fa-trash-can')) {
                    // Delete button clicked
                    handleDeleteThesis(thesisId, thesisTitle);
                }
                
                // Close the menu
                moreOptions.style.display = 'none';
            }
        });
    }
    
    async function handleEditThesis(thesisId, thesisTitle) {
        try {
            console.log('Starting edit process for thesis ID:', thesisId);
            
            // Fetch thesis data
            const response = await fetch(`../../../app/Controllers/ThesisController.php?action=editThesis&id=${thesisId}`);
            console.log('Response status:', response.status);
            
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            let data;
            
            // More robust JSON parsing
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                
                // Try to extract JSON from the response if there's extra output
                const jsonMatch = responseText.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    try {
                        data = JSON.parse(jsonMatch[0]);
                        console.log('Successfully extracted JSON from response');
                    } catch (e2) {
                        console.error('Failed to parse extracted JSON:', e2);
                        throw new Error('Invalid JSON response from server');
                    }
                } else {
                    // If no JSON found, check if it's an error message
                    if (responseText.includes('error') || responseText.includes('Error')) {
                        throw new Error('Server error: ' + responseText.substring(0, 100));
                    } else {
                        throw new Error('Invalid server response format');
                    }
                }
            }
            
            console.log('Parsed data:', data);
            
            if (data.success) {
                console.log('Thesis data loaded successfully:', data.thesis);
                // Populate the upload modal with existing data
                populateEditForm(data.thesis);
                
                // Change modal title and button text
                const modalTitle = document.querySelector('.upload-modal .modal-title');
                const uploadBtn = document.getElementById('uploadBtn');
                
                if (modalTitle) modalTitle.textContent = 'Edit Thesis';
                if (uploadBtn) {
                    uploadBtn.textContent = 'Update Thesis';
                    uploadBtn.setAttribute('data-thesis-id', thesisId);
                }
                
                // Show the upload modal in edit mode
                const uploadModal = document.getElementById('uploadModal');
                if (uploadModal) {
                    uploadModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    throw new Error('Upload modal not found');
                }
                
            } else {
                throw new Error(data.error || 'Failed to load thesis data');
            }
        } catch (error) {
            console.error('Error loading thesis for edit:', error);
            Swal.fire({
                title: 'Error',
                text: `Failed to load thesis data for editing: ${error.message}`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }

    // Populate form with existing thesis data
    function populateEditForm(thesis) {
        console.log('Thesis data received:', thesis);
        
        // Populate form fields
        document.getElementById('thesisTitle').value = thesis.Title || '';
        document.getElementById('thesisAuthor').value = thesis.Thesis_Email || '';
        document.getElementById('thesisAdviser').value = thesis.Adviser || '';
        
        // FIX: Set hardbound availability - handle both property names and ensure proper value setting
        const hardboundSelect = document.getElementById('hardboundSelect');
        if (hardboundSelect) {
            // Try all possible property names from your database
            const hardboundValue = thesis.HardBound_Available || thesis.Hardbound || thesis.hardbound || thesis.Hardbound_Available || 'Yes';
            console.log('Setting hardbound value:', hardboundValue); // Debug log
            
            // Set the value and trigger change event
            hardboundSelect.value = hardboundValue;
            hardboundSelect.dispatchEvent(new Event('change'));
        }
    
        // Set department and trigger change event
        const departmentSelect = document.getElementById('departmentSelect');
        if (departmentSelect) {
            departmentSelect.value = thesis.Thesis_Department || '';
            
            // Trigger department change to populate courses
            departmentSelect.dispatchEvent(new Event('change'));
            
            // Set course after a short delay to ensure options are populated
            setTimeout(() => {
                const courseInput = document.getElementById('courseInput');
                if (courseInput && thesis.Thesis_Course) {
                    courseInput.value = thesis.Thesis_Course;
                    courseInput.dispatchEvent(new Event('change'));
                }
            }, 200);
        }
        
        // Clear existing files from upload arrays
        uploadedFiles.abstract = [];
        uploadedFiles.thesis = [];
        
        // Show existing files as read-only or with download links
        showExistingFiles(thesis);
        
        // Update button state
        updateUploadButtonState();
    }
    
    function showExistingFiles(thesis) {
        const abstractList = document.getElementById('abstractFileList');
        const thesisList = document.getElementById('thesisFileList');
        
        // Clear existing file displays
        showEmptyState('abstract');
        showEmptyState('thesis');
        
        // Add existing abstract file info
        if (thesis.Thesis_AbstractFile) {
            abstractList.innerHTML = `
                <div class="file-item-card">
                    <div class="file-icon-preview pdf">
                        <i class="far fa-file-pdf"></i>
                    </div>
                    <div class="file-info-preview">
                        <div class="file-name-preview">Existing Abstract File</div>
                        <div class="file-size-preview">Uploaded previously</div>
                    </div>
                    <div class="file-actions-preview">
                        <button type="button" class="file-action-btn-preview file-download-preview" onclick="downloadExistingFile(${thesis.ID}, 'abstract')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Add existing thesis file info
        if (thesis.Thesis_File) {
            thesisList.innerHTML = `
                <div class="file-item-card">
                    <div class="file-icon-preview pdf">
                        <i class="far fa-file-pdf"></i>
                    </div>
                    <div class="file-info-preview">
                        <div class="file-name-preview">Existing Thesis File</div>
                        <div class="file-size-preview">Uploaded previously</div>
                    </div>
                    <div class="file-actions-preview">
                        <button type="button" class="file-action-btn-preview file-download-preview" onclick="downloadExistingFile(${thesis.ID}, 'thesis')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            `;
        }
    }

    async function updateThesis(thesisId) {
        try {
            // Validate form data
            const thesisTitleInput = document.getElementById('thesisTitle');
            const thesisAuthorInput = document.getElementById('thesisAuthor');
            const thesisAdviserInput = document.getElementById('thesisAdviser');
            const departmentSelect = document.getElementById('departmentSelect');
            const courseInput = document.getElementById('courseInput');
            const hardboundSelect = document.getElementById('hardboundSelect');
            const hardboundValue = hardboundSelect ? hardboundSelect.value : 'Yes';
            
            // Basic validation
            if (!thesisTitleInput.value.trim()) {
                Swal.fire({
                    title: 'Thesis Title Required',
                    text: 'Please enter a title for your thesis.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validate author emails
            const authorEmailValidation = validateAuthorEmails(thesisAuthorInput.value);
            if (!authorEmailValidation.isValid) {
                const invalidEmailsList = authorEmailValidation.invalidEmails.join(', ');
                Swal.fire({
                    title: 'Invalid Email Addresses',
                    html: `The following author emails are not valid: <strong>${invalidEmailsList}</strong>`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Validate adviser email
            if (thesisAdviserInput.value.trim() && !isValidEmail(thesisAdviserInput.value.trim())) {
                Swal.fire({
                    title: 'Invalid Adviser Email',
                    text: 'Please enter a valid email address for the adviser.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show confirmation dialog
            const result = await Swal.fire({
                title: 'Update Thesis?',
                html: `Are you sure you want to update <strong>${thesisTitleInput.value}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
                cancelButtonText: 'Cancel'
            });
            
            if (result.isConfirmed) {
                // Show loading state
                const uploadBtn = document.getElementById('uploadBtn');
               
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                uploadBtn.disabled = true;
                
                // Prepare form data for update
                const formData = new FormData();
                formData.append('thesis_id', thesisId);
                formData.append('thesistitle', thesisTitleInput.value.trim());
                formData.append('thesisauthor', thesisAuthorInput.value.trim());
                formData.append('thesisadviser', thesisAdviserInput.value.trim());
                formData.append('department', departmentSelect.value);
                formData.append('course', courseInput.value);
                formData.append('hardbound', hardboundValue);
                
                // Append new files if uploaded
                if (uploadedFiles.abstract.length > 0) {
                    uploadedFiles.abstract.forEach(file => {
                        formData.append('abstract_file', file);
                    });
                }
                
                if (uploadedFiles.thesis.length > 0) {
                    uploadedFiles.thesis.forEach(file => {
                        formData.append('thesis_file', file);
                    });
                }
                
                console.log('Sending update request for thesis ID:', thesisId);
                
                // Send update request
                const response = await fetch('../../../app/Controllers/ThesisController.php?action=updateThesis', {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                console.log('Raw update response:', responseText);
                
                let data;
                try {
                    // Try to parse as JSON
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    // Try to extract JSON from response
                    const jsonMatch = responseText.match(/\{[\s\S]*\}/);
                    if (jsonMatch) {
                        try {
                            data = JSON.parse(jsonMatch[0]);
                        } catch (e) {
                            throw new Error('Invalid server response format');
                        }
                    } else {
                        throw new Error('Server returned invalid response');
                    }
                }
                
                console.log('Parsed update response:', data);
                
                if (data.success) {
                    Swal.fire({
                        title: 'Update Successful!',
                        text: data.message || 'Your thesis has been updated successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        resetUploadForm();
                        closeModal(document.getElementById('uploadModal'));
                        location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Update failed');
                }
            }
        } catch (error) {
            console.error('Update error:', error);
            Swal.fire({
                title: 'Update Failed',
                text: error.message || 'Failed to update thesis. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } finally {
            // Restore button state
            const uploadBtn = document.getElementById('uploadBtn');
            if (uploadBtn) {
                uploadBtn.textContent = 'Update Thesis';
                uploadBtn.disabled = false;
            }
        }
    }

    async function handleEditThesis(thesisId, thesisTitle) {
        try {
            
            // Show loading state
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while we load thesis data',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fetch thesis data with error handling
            const response = await fetch(`../../../app/Controllers/ThesisController.php?action=editThesis&id=${thesisId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            
            // Check if response is empty
            if (!responseText.trim()) {
                throw new Error('Server returned empty response');
            }
            
            let data;
            
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                // Try to extract JSON from any output buffering
                const jsonMatch = responseText.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    try {
                        data = JSON.parse(jsonMatch[0]);
                        console.log('Successfully extracted JSON from response');
                    } catch (e2) {
                        console.error('Failed to parse extracted JSON:', e2);
                        throw new Error('Server returned invalid JSON format');
                    }
                } else {
                    // If no JSON found, check common error patterns
                    if (responseText.includes('Warning:') || responseText.includes('Notice:') || responseText.includes('Error:')) {
                        throw new Error('PHP errors detected in response');
                    } else {
                        throw new Error('Server returned non-JSON response');
                    }
                }
            }
            
            // Close loading SweetAlert
            Swal.close();
            
            if (data.success && data.thesis) {
                // Populate the upload modal with existing data
                populateEditForm(data.thesis);
                
                // Change modal title and button text
                const modalTitle = document.querySelector('.upload-modal .modal-title');
                const uploadBtn = document.getElementById('uploadBtn');
                
                if (modalTitle) modalTitle.textContent = 'Edit Thesis';
                if (uploadBtn) {
                    uploadBtn.textContent = 'Update Thesis';
                    uploadBtn.setAttribute('data-thesis-id', thesisId);
                }
                
                // Show the upload modal in edit mode
                const uploadModal = document.getElementById('uploadModal');
                if (uploadModal) {
                    uploadModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
                
            } else {
                throw new Error(data.error || 'Failed to load thesis data');
            }
        } catch (error) {
            Swal.close(); // Ensure loading dialog is closed
            
            Swal.fire({
                title: 'Error',
                text: `Failed to load thesis data: ${error.message}`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }

    function downloadExistingFile(thesisId, fileType) {
        const url = fileType === 'abstract' 
            ? `../../../app/Controllers/ThesisController.php?action=downloadAbstract&id=${thesisId}`
            : `../../../app/Controllers/ThesisController.php?action=download&id=${thesisId}`;
        
        window.open(url, '_blank');
    }

    // Delete thesis function
    async function handleDeleteThesis(thesisId, thesisTitle) {
        try {
            const result = await Swal.fire({
                title: 'Delete Thesis?',
                html: `Are you sure you want to delete <strong>"${thesisTitle}"</strong>?<br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });
            
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the thesis.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send delete request
                const response = await fetch('../../../app/Controllers/ThesisController.php?action=deleteThesis', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ thesis_id: thesisId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message || 'The thesis has been deleted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // FIX: Properly remove the item from DOM
                        const projectItem = document.querySelector(`.project-item[data-thesis-id="${thesisId}"]`);
                        if (projectItem) {
                            // Add animation for removal
                            projectItem.style.opacity = '0';
                            projectItem.style.transform = 'translateX(-100%)';
                            projectItem.style.transition = 'all 0.3s ease';
                            
                            setTimeout(() => {
                                projectItem.remove();
                                
                                // Check if any items left
                                const remainingItems = document.querySelectorAll('.project-item');
                                if (remainingItems.length === 0) {
                                    // No items left, reload the page to refresh everything
                                    location.reload();
                                } else {
                                    // Re-run animations for remaining items
                                    animateOnScroll();
                                }
                            }, 300);
                        } else {
                            // If we can't find the specific item, reload the page
                            location.reload();
                        }
                    });
                } else {
                    throw new Error(data.error || 'Delete failed');
                }
            }
        } catch (error) {
            console.error('Delete error:', error);
            Swal.fire({
                title: 'Delete Failed',
                text: error.message || 'Failed to delete thesis. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }

    function resetUploadForm() {
        // Clear uploaded files arrays
        uploadedFiles.abstract = [];
        uploadedFiles.thesis = [];
        
        // Clear file displays
        showEmptyState('abstract');
        showEmptyState('thesis');
        
        // Clear both file inputs
        if (abstractFileInput) abstractFileInput.value = '';
        if (thesisFileInput) thesisFileInput.value = '';
        
        // Clear all form fields
        const formFields = [
            'thesisTitle',
            'thesisAuthor',
            'thesisAdviser',
            'courseInput'
        ];
        
        formFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = '';
                field.style.borderColor = '#ddd';
            }
        });
        
        // Reset department select
        const departmentSelect = document.getElementById('departmentSelect');
        if (departmentSelect) {
            departmentSelect.selectedIndex = 0;
            departmentSelect.style.borderColor = '#ddd';
        }
        
        // Reset course input
        const courseInput = document.getElementById('courseInput');
        if (courseInput) {
            courseInput.innerHTML = '<option value="" selected disabled>Select your program</option>';
            courseInput.disabled = true;
            courseInput.style.borderColor = '#ddd';
        }

        const hardboundSelect = document.getElementById('hardboundSelect');
        if (hardboundSelect) {
            hardboundSelect.value = 'Yes';
            hardboundSelect.style.borderColor = '#51cf66';
        }
        
        // Reset modal to create mode
        const uploadBtn = document.getElementById('uploadBtn');
        const modalTitle = document.querySelector('.upload-modal .modal-title');
        
        if (uploadBtn) {
            uploadBtn.textContent = 'Upload Thesis';
            uploadBtn.removeAttribute('data-thesis-id');
        }
        
        if (modalTitle) {
            modalTitle.textContent = 'Upload New Thesis';
        }
        
        // Update button state
        updateUploadButtonState();
    }

    if (uploadModal) {
        uploadModal.addEventListener('click', function(e) {
            if (e.target === uploadModal || e.target.classList.contains('modal-close') || e.target.classList.contains('btn-cancel')) {
                initializeUploadFormValidation();
            }
        });
    }

    // Announcement Management Functionality - SIMPLIFIED INITIALIZATION
    function initializeAnnouncementTab() {
        console.log('Initializing announcement tab...');
        
        // Initialize announcement functionality when announcement tab is shown
        const announcementOption = document.querySelector('.menu-options li[data-view="announcement"]');
        if (announcementOption) {
            announcementOption.addEventListener('click', function() {
                console.log('Announcement tab clicked');
                // Small delay to ensure the container is visible
                setTimeout(() => {
                    if (typeof AnnouncementManager !== 'undefined') {
                        console.log('AnnouncementManager is available');
                        if (!window.announcementManager) {
                            console.log('Creating new AnnouncementManager instance');
                            window.announcementManager = new AnnouncementManager();
                        } else {
                            console.log('AnnouncementManager instance already exists');
                        }
                        // Ensure the container is properly displayed
                        const announcementContainer = document.getElementById('announcement-container');
                        if (announcementContainer) {
                            console.log('Announcement container found:', announcementContainer);
                            announcementContainer.style.display = 'block';
                        } else {
                            console.error('Announcement container not found');
                        }
                    } else {
                        console.error('AnnouncementManager class not found');
                    }
                }, 100);
            });
        }

        // Also initialize when the page loads if we're already on the announcement view
        const currentView = document.querySelector('.content-container-active');
        if (currentView && currentView.id === 'announcement-container') {
            console.log('Already on announcement view, initializing...');
            setTimeout(() => {
                if (typeof AnnouncementManager !== 'undefined' && !window.announcementManager) {
                    window.announcementManager = new AnnouncementManager();
                }
            }, 100);
        }
    }

    // Initialize announcement functionality
    initializeAnnouncementTab();

    // Initialize Functions inside DOM----------------------------------------------------------------------
    initializeUserData();

    // Initialize save functionality
    initializeSaveFunctionality();
    
    // Initialize role change handling
    initializeRoleChangeHandling();
    
    // Hide save button initially

    // Initialize upload form submission
    initializeUploadFormSubmission();

    // Initialize upload form validation
    initializeUploadFormValidation();
    
    initializeDepartmentCourseLogic();
    
    initializeMoreOptions();

    initializeRoleBox();

    initializeAllFilter();

 
});

let changesMade = false;
    let roleChanges = {};

    

    function showDashboardView() {
        // Hide all content containers
        document.querySelectorAll('.content-container').forEach(container => {
            container.style.display = 'none';
        });
        
        // Show dashboard
        document.querySelector('.app-content').style.display = 'block';
        
        // Update sidebar selection
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        document.querySelector('[data-view="dashboard"]').classList.add('selected');
    }


// Enhanced save functionality
function initializeSaveFunctionality() {
    const saveBtn = document.getElementById('saveAdminChangesBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            if (!changesMade || Object.keys(roleChanges).length === 0) {
                Swal.fire({
                    title: 'No Changes',
                    text: 'You haven\'t made any changes to save.',
                    icon: 'info',
                    confirmButtonColor: 'var(--primary-color)'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Changes',
                html: `Are you sure you want to save ${Object.keys(roleChanges).length} user role change(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--primary-color)',
                cancelButtonColor: 'var(--color-lite-grey)',
                confirmButtonText: 'Yes, save changes!',
                cancelButtonText: 'Cancel'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.style.pointerEvents = 'none';
                    
                    try {
                        // Save all changes
                        const savePromises = [];
                        let successCount = 0;
                        let errorCount = 0;
                        
                        for (const [userId, roles] of Object.entries(roleChanges)) {
                            // Find the selected role
                            let selectedRole = null;
                            for (const [role, isSelected] of Object.entries(roles)) {
                                if (isSelected) {
                                    selectedRole = role;
                                    break;
                                }
                            }
                            
                            if (selectedRole) {
                                try {
                                    await updateUserRole(userId, selectedRole);
                                    successCount++;
                                } catch (error) {
                                    console.error(`Failed to update user ${userId}:`, error);
                                    errorCount++;
                                }
                            }
                        }
                        
                        // Show result message
                        if (errorCount === 0) {
                            Swal.fire({
                                title: 'Success!',
                                text: `All ${successCount} user role changes saved successfully.`,
                                icon: 'success',
                                confirmButtonColor: 'var(--primary-color)',
                                timer: 2000
                            });
                        } else {
                            Swal.fire({
                                title: 'Partial Success',
                                html: `Successfully updated ${successCount} users.<br>Failed to update ${errorCount} users.`,
                                icon: 'warning',
                                confirmButtonColor: 'var(--primary-color)'
                            });
                        }
                        
                        // Refresh user data to reflect changes
                        await fetchAndDisplayUsers();
                        
                        // Reset changes
                        roleChanges = {};
                        changesMade = false;
                        
                    } catch (error) {
                        console.error('Error saving changes:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to save changes. Please try again.',
                            icon: 'error',
                            confirmButtonColor: 'var(--primary-color)'
                        });
                    } finally {
                        // Restore button content
                        this.innerHTML = originalHtml;
                        this.style.pointerEvents = 'auto';
                    }
                }
            });
        });
    }
}

// Function to initialize user data
function initializeUserData() {
    // Load users when access management is shown
    const usersOption = document.querySelector('.menu-options li[data-view="users"]');
    if (usersOption) {
        usersOption.addEventListener('click', function() {
            // Small delay to ensure the container is visible
            setTimeout(() => {
                fetchAndDisplayUsers();
            }, 100);
        });
    }
    
    // Also load when page loads if we're on the users view
    const currentView = document.querySelector('.content-container-active');
    if (currentView && currentView.id === 'access-container') {
        fetchAndDisplayUsers();
    }
}

//Thesis view abstract
function handleProjectItemClick(projectItem) {
    if (!projectItem) return;
    
    const title = projectItem.querySelector('h3')?.textContent || 'No title';
    const uploadedDate = projectItem.querySelector('.links p')?.textContent || 'Unknown date';
    const authors = projectItem.querySelector('.desc-row p')?.textContent || 'Unknown authors';
    
    // FIX: Use data attribute or querySelector with class
    const adviserElement = projectItem.querySelector('.adviser');
    const adviser = adviserElement ? adviserElement.getAttribute('data-adviser') || 
                   adviserElement.textContent.replace('Adviser:', '').trim() : 'Unknown adviser';
    
    // Get the thesis ID from a data attribute
    const thesisId = projectItem.getAttribute('data-thesis-id');
    
    if (thesisId) {
        showProjectPreview(thesisId, title, uploadedDate, authors, adviser);
    } else {
        console.error('No thesis ID found for project item');
        Swal.fire({
            title: 'Preview Unavailable',
            text: 'Thesis information is missing.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function showProjectPreview(thesisId, title, uploadedDate, authors, adviser, fallbackFileUrl = null) {
    const modalTitle = document.querySelector('.preview-modal .modal-title');
    if (modalTitle) {
        modalTitle.textContent = title;
    }
    
    // Create a container for project info
    const projectInfo = document.getElementById('project-info-preview');
    if (projectInfo) {
        projectInfo.innerHTML = `
            <div class="project-detail-container">
                <div>
                    <div class="project-detail">
                        <strong>Uploaded:</strong> ${uploadedDate}
                    </div>
                    <div class="project-detail">
                        <strong>Authors:</strong> ${authors}
                    </div>
                </div>

                <div>
                    <div class="project-detail">
                        <strong>Adviser:</strong> ${adviser}
                    </div>
                    ${thesisId ? `<div class="project-detail">
                        <strong>Thesis ID:</strong> ${thesisId}
                    </div>` : ''}
                </div>
            </div>
        `;
    }
    
    // Reset viewer states and hide footer controls
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const pdfViewer = document.getElementById('pdf-viewer');
    const unsupportedFile = document.getElementById('unsupported-file');
    const pdfFooterControls = document.getElementById('pdf-footer-controls');
    
    docViewerIframe.style.display = 'none';
    pdfViewer.style.display = 'none';
    unsupportedFile.style.display = 'none';
    pdfFooterControls.style.display = 'none';
    
    // Set download link
    const downloadLink = document.getElementById('download-link');
    
    if (thesisId) {
        // Fetch from database via controller
        fetchThesisFile(thesisId, title);
    } else if (fallbackFileUrl) {
        downloadLink.href = fallbackFileUrl;
        downloadLink.download = title;
        previewLocalFile(fallbackFileUrl);
    } else {
        unsupportedFile.style.display = 'block';
        downloadLink.href = '#';
        downloadLink.onclick = function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Download Unavailable',
                text: 'File download is not available for this thesis.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        };
    }
    
    // Show preview modal
    const previewModal = document.getElementById('previewModal');
    previewModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

async function fetchThesisFile(thesisId, title) {
    try {
        // Show loading state
        const pdfViewer = document.getElementById('pdf-viewer');
        const docViewerIframe = document.getElementById('doc-viewer-iframe');
        const unsupportedFile = document.getElementById('unsupported-file');
        
        pdfViewer.innerHTML = '<div class="loading-preview"><i class="fas fa-spinner fa-spin"></i><p>Loading abstract...</p></div>';
        pdfViewer.style.display = 'block';
        docViewerIframe.style.display = 'none';
        unsupportedFile.style.display = 'none';
        
        // Clean up any previous blob URLs
        if (window.currentPdfBlobUrl) {
            URL.revokeObjectURL(window.currentPdfBlobUrl);
        }
        
        // Fetch the ABSTRACT file (this is correct - it calls downloadAbstract action)
        const response = await fetch(`../../../app/Controllers/ThesisController.php?action=downloadAbstract&id=${thesisId}`);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        
        // Check if response is PDF
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('pdf')) {
            const text = await response.text();
            if (text.includes('error') || text.includes('false')) {
                // Try to parse as JSON error
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.error || 'Abstract not available');
                } catch (e) {
                    throw new Error('Abstract file is not a valid PDF');
                }
            }
            throw new Error('Abstract file is not a valid PDF');
        }
        
        // Get the abstract as blob
        const blob = await response.blob();
        
        if (blob.size === 0) {
            throw new Error('Abstract file is empty');
        }
        
        // Create object URL for the blob
        window.currentPdfBlobUrl = URL.createObjectURL(blob);
        
        // Set download link for abstract only
        const downloadLink = document.getElementById('download-link');
        downloadLink.href = window.currentPdfBlobUrl;
        downloadLink.download = `${title.replace(/\s+/g, '_')}_abstract.pdf`;
        downloadLink.style.display = 'block';
        
        // Preview the abstract PDF
        previewPdf(window.currentPdfBlobUrl);
        
    } catch (error) {
        console.error('Error fetching abstract file:', error);
        showPdfError(`Failed to load abstract: ${error.message}`);
        
        // Still allow download if we have the thesisId
        const downloadLink = document.getElementById('download-link');
        if (thesisId) {
            downloadLink.href = `../../../app/Controllers/ThesisController.php?action=downloadAbstract&id=${thesisId}`;
            downloadLink.download = `${title.replace(/\s+/g, '_')}_abstract.pdf`;
            downloadLink.style.display = 'block';
        } else {
            downloadLink.style.display = 'none';
        }
    }
}

document.querySelectorAll('.modal-close, .btn-cancel').forEach(btn => {
    btn.addEventListener('click', function() {
        if (window.currentPdfBlobUrl) {
            URL.revokeObjectURL(window.currentPdfBlobUrl);
            window.currentPdfBlobUrl = null;
        }
        window.currentPdfDoc = null;
        window.currentPageNum = 1;
        
        // Hide footer controls when modal closes
        const pdfFooterControls = document.getElementById('pdf-footer-controls');
        if (pdfFooterControls) {
            pdfFooterControls.style.display = 'none';
        }
    });
});

function previewLocalFile(fileUrl) {
    const fileExtension = fileUrl.split('.').pop().toLowerCase();
    
    if (fileExtension === 'pdf') {
        // Use PDF.js for PDF preview
        previewPdf(fileUrl);
        pdfViewer.style.display = 'block';
    } else {
        // Show unsupported message for other file types
        unsupportedFile.style.display = 'block';
    }
}

// PDF.js functions for PDF preview
function previewPdf(url) {
    // Ensure PDF.js is available
    if (typeof pdfjsLib === 'undefined') {
        console.error('PDF.js library not loaded');
        showPdfError('PDF viewer library not loaded. Please refresh the page.');
        return;
    }
    
    const pdfViewer = document.getElementById('pdf-viewer');
    
    // Clear previous content and show loading
    pdfViewer.innerHTML = '<div class="loading-preview"><i class="fas fa-spinner fa-spin"></i><p>Loading Abstract...</p></div>';
    
    // Set up PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    
    // Clear previous PDF document
    if (window.currentPdfDoc) {
        window.currentPdfDoc.destroy();
    }
    
    // Load the PDF document
    pdfjsLib.getDocument(url).promise.then(function(pdfDoc) {
        console.log('PDF loaded successfully, pages:', pdfDoc.numPages);
        
        // Store the PDF document globally
        window.currentPdfDoc = pdfDoc;
        window.currentPageNum = 1;
        
        // Clear loading state
        pdfViewer.innerHTML = '';
        
        // Render the first page
        renderPage(window.currentPageNum);
        
        // Add PDF controls
        addPdfControls();
        
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        showPdfError(`Failed to load PDF: ${error.message}`);
    });
}

function renderPage(pageNum) {
    if (!window.currentPdfDoc || typeof window.currentPdfDoc.getPage !== 'function') {
        console.error('Invalid PDF document');
        showPdfError('Invalid PDF document');
        return;
    }
    
    const pdfViewer = document.getElementById('pdf-viewer');
    
    window.currentPdfDoc.getPage(pageNum).then(function(page) {
        console.log('Rendering page:', pageNum);
        
        const scale = 1.2;
        const viewport = page.getViewport({ scale: scale });
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        canvas.style.display = 'block';
        canvas.style.margin = '0 auto';
        canvas.style.border = '1px solid #ddd';
        
        // Clear previous canvas but keep controls
        const existingCanvas = pdfViewer.querySelector('canvas');
        const controls = pdfViewer.querySelector('.pdf-controls');
        
        // Remove existing canvas
        if (existingCanvas) {
            existingCanvas.remove();
        }
        
        // Add canvas after controls
        if (controls) {
            controls.after(canvas);
        } else {
            pdfViewer.appendChild(canvas);
        }
        
        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        
        return page.render(renderContext).promise;
        
    }).then(function() {
        console.log('Page rendered successfully');
        // Update page number display
        const pageNumElement = document.getElementById('pdf-page-num');
        if (pageNumElement) {
            pageNumElement.textContent = pageNum;
        }
        
    }).catch(function(error) {
        console.error('Error rendering page:', error);
        showPdfError(`Error rendering page: ${error.message}`);
    });
}

function addPdfControls() {
    if (!window.currentPdfDoc) return;
    
    const pdfViewer = document.getElementById('pdf-viewer');
    
    // Remove existing controls
    const existingControls = pdfViewer.querySelector('.pdf-controls');
    if (existingControls) {
        existingControls.remove();
    }
    
    const controlsHtml = `
        <div class="pdf-controls">
            <button id="prev-page" type="button">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <span class="pdf-page-info">
                Page <span id="pdf-page-num">1</span> of ${window.currentPdfDoc.numPages}
            </span>
            <button id="next-page" type="button">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;
    
    pdfViewer.insertAdjacentHTML('afterbegin', controlsHtml);
    
    // Add event listeners
    document.getElementById('prev-page').addEventListener('click', function() {
        if (window.currentPageNum <= 1) return;
        window.currentPageNum--;
        renderPage(window.currentPageNum);
        updatePdfControls();
    });
    
    document.getElementById('next-page').addEventListener('click', function() {
        if (window.currentPageNum >= window.currentPdfDoc.numPages) return;
        window.currentPageNum++;
        renderPage(window.currentPageNum);
        updatePdfControls();
    });
    
    // Initial controls update
    updatePdfControls();
}

function updatePdfControls() {
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');
    
    if (prevBtn && nextBtn && window.currentPdfDoc) {
        prevBtn.disabled = window.currentPageNum <= 1;
        nextBtn.disabled = window.currentPageNum >= window.currentPdfDoc.numPages;
    }
}

    

    

// Admin Access Management
async function fetchAndDisplayUsers() {
    try {
        console.log('Fetching users with CSRF token...');
        
        // Get the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        console.log('Using CSRF token:', csrfToken);
        
        // Use FormData for POST request with CSRF token
        const formData = new FormData();
        formData.append('action', 'get_all_users_complete_roles');
        formData.append('csrf_token', csrfToken);
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
        console.log('Raw response:', rawText);
        
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            // Try to extract JSON if there's extra output
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                data = JSON.parse(jsonMatch[0]);
                console.log('Successfully extracted JSON from response');
            } else {
                throw new Error('Server returned invalid JSON response');
            }
        }
        
    //    console.log('Parsed data:', data);
        
        if (data.success && data.users) {
      //      console.log(`✅ Successfully loaded ${data.users.length} users with role data`);
            displayUsersInAccessManagement(data.users);
            
            // ADD THIS LINE: Update the user counts in access cards
            updateUserCounts(data.users);
        } else {
            throw new Error(data.message || 'Failed to load user data');
        }
        
    } catch (error) {
        console.error('Error fetching users:', error);
        
        // Show error in UI
        const adminUserList = document.getElementById('adminUserList');
        if (adminUserList) {
            adminUserList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Failed to Load Users</h3>
                    <p>${error.message}</p>
                    <button onclick="fetchAndDisplayUsers()" class="retry-btn">Retry</button>
                </div>
            `;
        }
        
        Swal.fire({
            title: 'Load Error',
            text: error.message,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}
/*

async function testRolesController() {
    try {
        console.log('Testing RolesController...');
        
        const formData = new FormData();
        formData.append('action', 'get_all_users_complete_roles');
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
        console.log('=== RAW RESPONSE ===');
        console.log(rawText);
        console.log('=== END RAW RESPONSE ===');
        
        // Check if it contains common PHP errors
        if (rawText.includes('Warning:') || rawText.includes('Notice:') || rawText.includes('Fatal error') || rawText.includes('Parse error')) {
            console.error('PHP ERRORS DETECTED IN RESPONSE');
        }
        
        if (rawText.includes('CSRF')) {
            console.error('CSRF ERROR DETECTED');
        }
        
        // Try to extract JSON if it's wrapped in other output
        const jsonMatch = rawText.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
            try {
                const jsonData = JSON.parse(jsonMatch[0]);
                console.log('EXTRACTED JSON:', jsonData);
            } catch (e) {
                console.error('Failed to parse extracted JSON:', e);
            }
        }
        
    } catch (error) {
        console.error('Test failed:', error);
    }
}

function debugFindCsrfToken() {
    console.log('=== CSRF TOKEN DEBUG ===');
    
    // Check meta tags
    const metaTags = document.querySelectorAll('meta');
    metaTags.forEach(meta => {
        const name = meta.getAttribute('name');
        if (name && (name.includes('csrf') || name.includes('token'))) {
            console.log('Meta tag found:', name, '=', meta.getAttribute('content'));
        }
    });
    
    // Check hidden inputs
    const inputs = document.querySelectorAll('input[type="hidden"]');
    inputs.forEach(input => {
        const name = input.name;
        if (name && (name.includes('csrf') || name.includes('token'))) {
            console.log('Hidden input found:', name, '=', input.value);
        }
    });
    
    // Check JavaScript variables
    console.log('window.csrfToken:', window.csrfToken);
    console.log('window._token:', window._token);
    
    // Check for any element with CSRF in ID or class
    const csrfElements = document.querySelectorAll('[id*="csrf"], [class*="csrf"], [id*="token"], [class*="token"]');
    csrfElements.forEach(el => {
        console.log('Potential CSRF element:', el);
    });
    
    console.log('=== END DEBUG ===');
}

*/

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function resetAccessManagementState() {
    // Remove active class from all access cards
    document.querySelectorAll('.accessCard').forEach(card => {
        card.classList.remove('active');
    });
    
    // Reset search input
    const adminUserSearch = document.getElementById('adminUserSearch');
    if (adminUserSearch) {
        adminUserSearch.value = '';
        adminUserSearch.setAttribute('data-current-filter', 'all');
    }
    
    // Reset to show all users
    filterUsersByRole('all');
    
    // Reset any other access management state if needed
    const allAccessBtn = document.getElementById('allAccessBtn');
    if (allAccessBtn) {
        allAccessBtn.classList.add('active');
    }
    
    // Reset role changes if any
    roleChanges = {};
    changesMade = false;
    updateSaveButtonVisibility();
}

// Function to display users in access management
function displayUsersInAccessManagement(users) {
    const adminUserList = document.getElementById('adminUserList');
    
        if (!adminUserList) return;
        
        adminUserList.innerHTML = '';
        
    users.forEach(user => {
        const userItem = createUserItem(user);
        adminUserList.appendChild(userItem);
    });
}

// Function to create user item HTML
function createUserItem(user) {
    const userItem = document.createElement('div');
    userItem.className = 'access-item admin-user-item';
    userItem.setAttribute('data-user-id', user.User_ID);
    
    // Get role display text from User_Role (USER_INFORMATION table)
    let userRoleDisplay = '';
    if (user.User_Role === 'faculty') {
        userRoleDisplay = 'Faculty';
    } else if (user.User_Role === 'student') {
        userRoleDisplay = 'Student';
    } else if (user.User_Role === 'superAdmin') {
        userRoleDisplay = 'Admin';
    } else if (user.User_Role === 'SubAdmin') {
        userRoleDisplay = 'Sub-Admin';
    } else {
        userRoleDisplay = user.User_Role || 'User';
    }
    
    // NOW USING ACTUAL ROLES TABLE DATA INSTEAD OF DEFAULTS
    // These come from the ROLES table via getAllUsersWithCompleteRoles()
    const subAdminValue = user.Sub_Admin || 'No'; // Direct from ROLES table
    const canEditValue = user.Can_Edit || 'No';    // Direct from ROLES table  
    const manageAccessValue = user.Manage_Access || 'No'; // Direct from ROLES table
    
    // Set colors based on actual ROLES table values
    const subAdminColor = subAdminValue === 'Yes' ? 'red' : 'gray';
    const canEditColor = canEditValue === 'Yes' ? 'red' : 'gray';
    const manageAccessColor = manageAccessValue === 'Yes' ? 'red' : 'gray';
    
    userItem.innerHTML = `
        <div class="access-info">
            <h4>${user.First_Name} ${user.Middle_Name || ''} ${user.Last_Name} ${user.Extension || ''}</h4>
            <p>${user.Email} • ${user.Department || 'No Department'} • Status: ${user.Acc_Status}</p>
        </div>
        <div class="role-checkbox-container">
            <label class="role-checkbox">
                <p>${userRoleDisplay}</p>
            </label>
            <button class="role-button" title="Manage Roles">
                <i class="fa-solid fa-circle-plus"></i>
            </button>
            <div class="roleBox">
                <button class="role-action-btn" data-permission="sub_admin" data-current-value="${subAdminValue}">
                    <i class="fa-solid fa-user-shield" style="color: ${subAdminColor};"></i> Sub-Admin
                </button>
                <button class="role-action-btn" data-permission="can_edit" data-current-value="${canEditValue}">
                    <i class="fa-solid fa-file-pen" style="color: ${canEditColor};"></i> Modify Thesis
                </button>
                <button class="role-action-btn" data-permission="manage_access" data-current-value="${manageAccessValue}">
                    <i class="fa-solid fa-key" style="color: ${manageAccessColor};"></i> Manage Access
                </button>
            </div>
        </div>
    `;
    
    return userItem;
}

async function getAllRolesData() {
    try {
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_all_roles_data',
                csrf_token: 'your_csrf_token_here' // You'll need to implement CSRF token handling
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('All roles data:', data.roles_data);
            return data.roles_data;
        } else {
            console.error('Failed to fetch roles data:', data.message);
            return [];
        }
    } catch (error) {
        console.error('Error fetching roles data:', error);
        return [];
    }
}



async function getAllUsersWithCompleteRoles() {
    try {
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_all_users_complete_roles',
                csrf_token: 'your_csrf_token_here'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('All users with complete roles:', data.users);
            return data.users;
        } else {
            console.error('Failed to fetch users with roles:', data.message);
            return [];
        }
    } catch (error) {
        console.error('Error fetching users with roles:', error);
        return [];
    }
}


function initializeRoleBox() {
    // Create overlay for closing roleBox when clicking outside
    const overlay = document.createElement('div');
    overlay.className = 'roleBox-overlay';
    document.body.appendChild(overlay);
    
    // Close all roleBoxes when clicking overlay
    overlay.addEventListener('click', function() {
        closeAllRoleBoxes();
    });
    
    // Handle role button clicks
    document.addEventListener('click', function(e) {
        const roleButton = e.target.closest('.role-button');
        if (roleButton) {
            e.preventDefault();
            e.stopPropagation();
            
            const accessItem = roleButton.closest('.access-item');
            const roleBox = accessItem.querySelector('.roleBox');
            
            
            // Close all other roleBoxes
            closeAllRoleBoxes();
            
            // Toggle current roleBox
            if (roleBox) {
                roleBox.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        }
        
        // Handle roleBox button clicks
        const roleBoxButton = e.target.closest('.roleBox button');
        if (roleBoxButton) {
            e.preventDefault();
            e.stopPropagation();
            
            const roleBox = roleBoxButton.closest('.roleBox');
            const accessItem = roleBox.closest('.access-item');
            const userId = accessItem.getAttribute('data-user-id');
            const userName = accessItem.querySelector('h4').textContent;
            const action = roleBoxButton.textContent.trim();
            
            handleRoleAction(userId, userName, action);
            
            // Close the roleBox after action
            closeAllRoleBoxes();
        }
    });
    
    // Close roleBox when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllRoleBoxes();
        }
    });
}

function closeAllRoleBoxes() {
    document.querySelectorAll('.roleBox').forEach(box => {
        box.classList.remove('active');
    });
    document.querySelector('.roleBox-overlay').classList.remove('active');
}

function handleRoleAction(userId, userName, action) {
    console.log('Role action:', { userId, userName, action });
    
    // Map action names to actual roles or functions
    const actionMap = {
        'SubAdmin': 'admin',
        'Modify Thesis': 'faculty',
        'Manage Access': 'admin',
        // Add more actions as needed
    };
    
    const role = actionMap[action];
    
    if (role) {
        Swal.fire({
            title: `Assign ${action}?`,
            html: `Are you sure you want to assign <strong>${action}</strong> role to <strong>${userName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, assign role!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Assigning Role...',
                    text: 'Please wait while we update the user role.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Simulate API call - replace with actual API call
                setTimeout(() => {
                    updateUserRole(userId, role)
                    .then(() => {
                        Swal.fire({
                            title: 'Success!',
                            text: `Successfully ${newValue === 'Yes' ? 'granted' : 'revoked'} ${action} permission for ${userName}.`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Update the specific button's indicator
                            const roleButton = userItem.querySelector(`[data-permission="${permissionType}"]`);
                            if (roleButton) {
                                const icon = roleButton.querySelector('i');
                                icon.style.color = newValue === 'Yes' ? 'red' : 'gray';
                                roleButton.setAttribute('data-current-value', newValue);
                            }
                            
                            // Also update all indicators by refreshing user data
                            fetchAndDisplayUsers();
                        });
                    })
                        .catch(error => {
                            console.error('Error updating user role:', error);
                            Swal.fire({
                                title: 'Error',
                                text: `Failed to assign role: ${error.message}`,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }, 1000);
            }
        });
    } else {
        Swal.fire({
            title: 'Action Not Available',
            text: `The action "${action}" is not yet implemented.`,
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }
}

// Function to update user counts in access cards
function updateUserCounts(users) {
    const counts = {
        'admin': 0,
        'faculty': 0,
        'student': 0,
        'all': users.length // Total count for "All" card
    };
    
    // Count users by role
    users.forEach(user => {
        if (user.User_Role === 'SubAdmin' || user.User_Role === 'superAdmin') {
            counts.admin++;
        } else if (user.User_Role === 'faculty') {
            counts.faculty++;
        } else if (user.User_Role === 'student') {
            counts.student++;
        }
    });
    
    // Update the access cards
    const allCountElement = document.querySelector('#allAccessBtn .access-count');
    const adminCountElement = document.querySelector('#adminAccess .access-count');
    const facultyCountElement = document.querySelector('#facultyAccess .access-count');
    const studentCountElement = document.querySelector('#studentAccess .access-count');
    
    if (allCountElement) allCountElement.textContent = `${counts.all} users`;
    if (adminCountElement) adminCountElement.textContent = `${counts.admin} users`;
    if (facultyCountElement) facultyCountElement.textContent = `${counts.faculty} users`;
    if (studentCountElement) studentCountElement.textContent = `${counts.student} users`;
    
    
}





