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

    // Form submission - only add listener if form exists
    const announcementForm = document.getElementById('announcementForm');
    if (announcementForm) {
        announcementForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
    } else {
    }

    // Search and filter
    const searchInput = document.getElementById('announcementSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    }
    
    this.initializeDropdowns();
    this.initializeTypeSelection();
    this.initializePinCheckbox();

    // Character counters - only add listeners if elements exist
    const titleInput = document.getElementById('announcementTitle');
    const contentInput = document.getElementById('announcementContent');
    
    if (titleInput) {
        titleInput.addEventListener('input', (e) => this.updateCharCounter(e.target, 'titleCharCount', 200));
    }
    if (contentInput) {
        contentInput.addEventListener('input', (e) => this.updateCharCounter(e.target, 'contentCharCount', 2000));
    }

}

    initializePinCheckbox() {
    const pinCheckbox = document.getElementById('announcementIsPinned');
    const pinCheckboxCustom = document.querySelector('.form-checkbox .checkbox-custom');
    
    if (pinCheckbox && pinCheckboxCustom) {
        // Toggle on custom checkbox click
        pinCheckboxCustom.addEventListener('click', () => {
            pinCheckbox.checked = !pinCheckbox.checked;
            // Update visual state
            this.updatePinCheckboxVisual(pinCheckbox.checked);
        });

        // Also toggle on label click
        const pinLabel = document.querySelector('label[for="announcementIsPinned"]');
        if (pinLabel) {
            pinLabel.addEventListener('click', (e) => {
                e.preventDefault();
                pinCheckbox.checked = !pinCheckbox.checked;
                this.updatePinCheckboxVisual(pinCheckbox.checked);
            });
        }

        // Update visual state on checkbox change
        pinCheckbox.addEventListener('change', () => {
            this.updatePinCheckboxVisual(pinCheckbox.checked);
        });

        // Initialize visual state
        this.updatePinCheckboxVisual(pinCheckbox.checked);
    }
}

updatePinCheckboxVisual(isChecked) {
    const pinCheckboxCustom = document.querySelector('.form-checkbox .checkbox-custom');
    const pinLabel = document.querySelector('label[for="announcementIsPinned"]');
    
    if (pinCheckboxCustom) {
        if (isChecked) {
            pinCheckboxCustom.classList.add('checked');
            pinCheckboxCustom.innerHTML = '<i class="fas fa-check"></i>';
        } else {
            pinCheckboxCustom.classList.remove('checked');
            pinCheckboxCustom.innerHTML = '';
        }
    }

    if (pinLabel) {
        if (isChecked) {
            pinLabel.style.color = 'var(--primary-color)';
            pinLabel.style.fontWeight = '600';
        } else {
            pinLabel.style.color = '';
            pinLabel.style.fontWeight = '';
        }
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
            
        } else {
            
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
        
        // If switching to create view and we're editing, ensure form is ready
        if (view === 'create' && this.isEditing) {
            
            // Force a small delay to ensure DOM is updated
            setTimeout(() => {
                this.scrollToForm();
            }, 100);
        }
    }

    if (view === 'active') {
        this.loadAnnouncements();
    } else if (view === 'archived') {
        this.loadArchivedAnnouncements();
    } else if (view === 'create' && !this.isEditing) {
        this.resetForm(); // Only reset form when switching to create view for new announcement
    }
    
    
}

// Add this helper method to scroll to the form
scrollToForm() {
    const createView = document.getElementById('createAnnouncementView');
    if (createView) {
        createView.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
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
                this.debugDates();
            } else {
                throw new Error(data.error || 'Failed to load announcements');
            }
        } catch (error) {
            
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
    // Safely format dates first
    const createdDate = this.safeFormatDate(announcement.created_at);
    const startDate = this.safeFormatDate(announcement.start_date) || createdDate;
    const endDate = this.safeFormatDate(announcement.end_date);
    
    const isExpired = announcement.end_date && new Date(announcement.end_date) < new Date();
    const isPinned = announcement.is_pinned == 1 && !isArchived; // Ensure boolean check
    const typeClass = announcement.type || 'information';
    const contentPreview = this.escapeHtml(announcement.content.length > 200 ? announcement.content.substring(0, 200) + '...' : announcement.content);
    const fullContent = this.escapeHtml(announcement.content);
    
    
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
                <p class="announcement-date">${startDate}</p>
                <div class="announcement-content-text" id="content-${announcement.id}" data-full-content="${fullContent}">
                    ${contentPreview}
                </div>
                ${announcement.content.length > 200 ? 
                    `<button class="read-more-btn" data-id="${announcement.id}">Read More</button>` : ''}
            </div>

            <!-- Footer Section -->
            <div class="announcement-footer">
                <div class="announcement-dates">
                    <div>Created: ${createdDate}</div>
                    ${announcement.start_date ? `<div>Starts: ${this.safeFormatDate(announcement.start_date)}</div>` : ''}
                    ${announcement.end_date ? `<div>Ends: ${this.safeFormatDate(announcement.end_date)}</div>` : ''}
                </div>

                <div class="announcement-actions">
                    ${!isArchived ? `
                        <button class="announcement-action-btn edit-btn" data-action="edit" data-id="${announcement.id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="announcement-action-btn pin-btn" data-action="togglePin" data-id="${announcement.id}">
                            <i class="fas fa-thumbtack"></i> ${announcement.is_pinned == 1 ? 'Unpin' : 'Pin'}
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

// Add a safe date formatting method
safeFormatDate(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        return isNaN(date.getTime()) ? 'Invalid date' : this.formatDate(dateString);
    } catch (error) {
        return 'Date error';
    }
}


    attachCardEventListeners(containerId, isArchived = false) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    // Read more/less buttons
    container.querySelectorAll('.read-more-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const announcementId = e.target.getAttribute('data-id');
            const contentElement = document.getElementById(`content-${announcementId}`);
            
            if (!contentElement) {
                
                return;
            }
            
            const isExpanded = contentElement.classList.contains('expanded');
            
            if (isExpanded) {
                contentElement.classList.remove('expanded');
                e.target.textContent = 'Read More';
                // Collapse content
                const fullContent = contentElement.getAttribute('data-full-content');
                if (fullContent && fullContent.length > 200) {
                    contentElement.innerHTML = fullContent.substring(0, 200) + '...';
                }
            } else {
                contentElement.classList.add('expanded');
                e.target.textContent = 'Read Less';
                // Expand to full content
                const fullContent = contentElement.getAttribute('data-full-content') || contentElement.textContent;
                contentElement.innerHTML = fullContent;
            }
        });
    });

    // Action buttons
    container.querySelectorAll('.announcement-action-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const action = e.target.closest('.announcement-action-btn').getAttribute('data-action');
            const announcementId = e.target.closest('.announcement-action-btn').getAttribute('data-id');
            
            if (!action || !announcementId) {
                
                return;
            }
            
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
            
            this.showError('Failed to perform action');
        }
    }

    async editAnnouncement(announcementId) {
    try {
        
        
        // Show loading state
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait while we load announcement data',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const response = await fetch(`../../../app/Controllers/AdminDashboardController.php?action=getAnnouncement&id=${announcementId}`);
        
        
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        
        
        let data;
        
        // Robust JSON parsing
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            
            // Try to extract JSON from the response
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    data = JSON.parse(jsonMatch[0]);
                    
                } catch (e2) {
                    throw new Error('Invalid JSON response from server');
                }
            } else {
                throw new Error('Server returned non-JSON response');
            }
        }
        
        // Close loading
        Swal.close();
        
        
        
        if (data.success && data.announcement) {
            
            
            // Populate the form FIRST
            this.populateForm(data.announcement);
            
            // THEN switch to create view
            
            this.switchView('create');
            
            // Force the form to be visible and focused
            setTimeout(() => {
                const createView = document.getElementById('createAnnouncementView');
                if (createView && createView.style.display !== 'block') {
                    
                    createView.style.display = 'block';
                }
                
                // Focus on the title field
                const titleInput = document.getElementById('announcementTitle');
                if (titleInput) {
                    titleInput.focus();
                    
                }
            }, 200);
            
        } else {
            throw new Error(data.error || 'Failed to load announcement data');
        }
        
    } catch (error) {
        
        
        Swal.close();
        this.showError('Failed to load announcement for editing: ' + error.message);
    }
}

    populateForm(announcement) {
    
    
    // Safely set values only if elements exist
    const elements = {
        announcementId: document.getElementById('announcementId'),
        announcementTitle: document.getElementById('announcementTitle'),
        announcementContent: document.getElementById('announcementContent'),
        announcementIsPinned: document.getElementById('announcementIsPinned'),
        announcementStartDate: document.getElementById('announcementStartDate'),
        announcementEndDate: document.getElementById('announcementEndDate')
    };

    // Set basic values
    if (elements.announcementId) {
        elements.announcementId.value = announcement.id || announcement.announcement_id || '';
        
    }

    if (elements.announcementTitle) {
        elements.announcementTitle.value = announcement.title || '';
        
    }

    if (elements.announcementContent) {
        elements.announcementContent.value = announcement.content || '';
        
    }

    // Handle pin checkbox
    if (elements.announcementIsPinned) {
        const isPinned = announcement.is_pinned == 1 || announcement.pinned == 1;
        elements.announcementIsPinned.checked = isPinned;
        this.updatePinCheckboxVisual(isPinned);
        
    }

    // Set type selection - FIXED
    
    if (announcement.type) {
        const typeCards = document.querySelectorAll('.type-card');
        
        
        typeCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            const cardType = card.getAttribute('data-type');
            
            
            if (radio && cardType === announcement.type) {
                card.classList.add('selected');
                radio.checked = true;
                
            } else {
                card.classList.remove('selected');
                radio.checked = false;
            }
        });
    }

    // Format dates for datetime-local input
    
    
    
    if (elements.announcementStartDate && announcement.start_date) {
        const formattedStartDate = this.formatDateForInput(announcement.start_date);
        elements.announcementStartDate.value = formattedStartDate;
        
    }

    if (elements.announcementEndDate && announcement.end_date) {
        const formattedEndDate = this.formatDateForInput(announcement.end_date);
        elements.announcementEndDate.value = formattedEndDate;
        
    } else if (elements.announcementEndDate) {
        elements.announcementEndDate.value = ''; // Clear if no end date
        
    }

    // Update UI for edit mode
    const createAnnouncementTitle = document.getElementById('createAnnouncementTitle');
    const createAnnouncementSubtitle = document.getElementById('createAnnouncementSubtitle');
    
    if (createAnnouncementTitle) {
        createAnnouncementTitle.textContent = 'Edit Announcement';
        
    }
    if (createAnnouncementSubtitle) {
        createAnnouncementSubtitle.textContent = 'Update announcement details';
        
    }

    // Update character counters
    if (elements.announcementTitle) {
        this.updateCharCounter(elements.announcementTitle, 'titleCharCount', 200);
    }
    if (elements.announcementContent) {
        this.updateCharCounter(elements.announcementContent, 'contentCharCount', 2000);
    }

     this.isEditing = true;
    
    
    // Check form visibility after population
    setTimeout(() => {
        this.checkFormVisibility();
    }, 300);
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
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            
            
            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=togglePinAnnouncement', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            

            if (data.success) {
                this.showSuccess(data.message || 'Announcement pin status updated');
                // Force reload to ensure UI is updated correctly
                setTimeout(() => {
                    this.loadAnnouncements();
                }, 500);
            } else {
                throw new Error(data.error || 'Failed to toggle pin status');
            }
        } catch (error) {
            
            this.showError('Failed to update pin status: ' + error.message);
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
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=archiveAnnouncement', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            

            if (data.success) {
                this.showSuccess(data.message || 'Announcement archived successfully');
                this.loadAnnouncements();
            } else {
                throw new Error(data.error || 'Failed to archive announcement');
            }
        } catch (error) {
            
            this.showError('Failed to archive announcement: ' + error.message);
        }
    }
}

    async restoreAnnouncement(announcementId) {
    const result = await Swal.fire({
        title: 'Restore Announcement?',
        text: 'This announcement will be moved back to active announcements',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, restore it',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('id', announcementId);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            
            
            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=restoreAnnouncement', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            

            if (data.success) {
                this.showSuccess(data.message || 'Announcement restored successfully');
                // Reload the archived announcements to reflect the change
                setTimeout(() => {
                    this.loadArchivedAnnouncements();
                }, 500);
            } else {
                throw new Error(data.error || 'Failed to restore announcement');
            }
        } catch (error) {
            
            this.showError('Failed to restore announcement: ' + error.message);
        }
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
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=deleteAnnouncement', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            

            if (data.success) {
                this.showSuccess(data.message || 'Announcement deleted successfully');
                if (isArchived) {
                    this.loadArchivedAnnouncements();
                } else {
                    this.loadAnnouncements();
                }
            } else {
                throw new Error(data.error || 'Failed to delete announcement');
            }
        } catch (error) {
            
            this.showError('Failed to delete announcement: ' + error.message);
        }
    }
}


    async handleFormSubmit(e) {
    e.preventDefault();
    
    
    const form = document.getElementById('announcementForm');
    if (!form) {
        
        this.showError('Form not found. Please refresh the page and try again.');
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('publishAnnouncementBtn');
    if (!submitBtn) {
        
        return;
    }

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
    submitBtn.disabled = true;

    try {
        
        
        // Get form data and log it for debugging
        const formData = new FormData(form);
        
        
        
        
        // Determine the correct action based on editing state
        const action = this.isEditing ? 'updateAnnouncement' : 'createAnnouncement';
        
        
        // Validate form
        
        if (!this.validateForm()) {
            
            throw new Error('Form validation failed');
        }
        

        // Use the correct endpoint with action parameter
        
        const url = `../../../app/Controllers/AdminDashboardController.php?action=${action}`;
        
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        // Log response status
        
        

        const responseText = await response.text();
        
        
        

        // Check if response is completely empty
        if (!responseText.trim()) {
            throw new Error('Server returned empty response');
        }

        let data;
        try {
            data = JSON.parse(responseText);
            
        } catch (parseError) {
            
            
            // Try to extract JSON from the response if there's extra output
            const jsonMatch = responseText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    data = JSON.parse(jsonMatch[0]);
                    
                } catch (e) {
                    
                    // Check for common PHP errors
                    if (responseText.includes('Fatal error')) {
                        const fatalMatch = responseText.match(/Fatal error[^]*/i);
                        throw new Error('PHP Fatal Error: ' + (fatalMatch ? fatalMatch[0].substring(0, 200) : 'Check server logs'));
                    } else if (responseText.includes('Parse error')) {
                        const parseMatch = responseText.match(/Parse error[^]*/i);
                        throw new Error('PHP Parse Error: ' + (parseMatch ? parseMatch[0].substring(0, 200) : 'Check server logs'));
                    } else if (responseText.includes('Warning') || responseText.includes('Notice')) {
                        throw new Error('PHP Warning/Notice: ' + responseText.substring(0, 300));
                    } else {
                        throw new Error('Server returned non-JSON response: ' + responseText.substring(0, 200));
                    }
                }
            }
        }

        

        if (data.success) {
            
            await Swal.fire({
                title: 'Success!',
                text: this.isEditing ? 'Announcement updated successfully' : 'Announcement created successfully',
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 3000
            });
            
            this.switchView('active');
            this.resetForm();
            this.loadAnnouncements();
        } else {
            // More detailed error logging
            
            const errorMessage = data.error || data.message || data.debug || 'Unknown server error';
            
            
            // If there's a debug field, show it
            if (data.debug) {
                throw new Error(data.debug);
            }
            throw new Error(errorMessage);
        }
        
    } catch (error) {
        
        
        
        if (error.message !== 'Form validation failed') {
            this.showError(`Failed to ${this.isEditing ? 'update' : 'create'} announcement: ${error.message}`);
        }
    } finally {
        // Restore button state
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
        
    }
}




initializeEventListeners() {
    

    // View switching - check if elements exist first
    const activeBtn = document.getElementById('activeAnnouncementsBtn');
    const archivedBtn = document.getElementById('archivedAnnouncementsBtn');
    const createBtn = document.getElementById('createAnnouncementBtn');
    const cancelBtn = document.getElementById('cancelAnnouncementBtn');

    if (activeBtn) {
        activeBtn.addEventListener('click', () => this.switchView('active'));
    } else {
        
    }
    
    if (archivedBtn) {
        archivedBtn.addEventListener('click', () => this.switchView('archived'));
    } else {
        
    }
    
    if (createBtn) {
        createBtn.addEventListener('click', () => this.switchView('create'));
    } else {
        
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => this.cancelEdit());
    } else {
        
    }

    // Form submission - only add listener if form exists
    const announcementForm = document.getElementById('announcementForm');
    if (announcementForm) {
        announcementForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        
    } else {
        
    }

    // Search and filter
    const searchInput = document.getElementById('announcementSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
    }
    
    this.initializeDropdowns();
    this.initializeTypeSelection();
    this.initializePinCheckbox();

    // Character counters - only add listeners if elements exist
    const titleInput = document.getElementById('announcementTitle');
    const contentInput = document.getElementById('announcementContent');
    
    if (titleInput) {
        titleInput.addEventListener('input', (e) => this.updateCharCounter(e.target, 'titleCharCount', 200));
    } else {
        
    }
    
    if (contentInput) {
        contentInput.addEventListener('input', (e) => this.updateCharCounter(e.target, 'contentCharCount', 2000));
    } else {
        
    }

    
}


    validateForm() {
    
    
    // Safely get form elements
    const titleInput = document.getElementById('announcementTitle');
    const contentInput = document.getElementById('announcementContent');
    const selectedType = document.querySelector('input[name="type"]:checked');
    const startDateInput = document.getElementById('announcementStartDate');

    // Check if essential elements exist before accessing their values
    if (!titleInput || !contentInput || !startDateInput) {
        
        this.showError('Form elements not found. Please refresh the page and try again.');
        return false;
    }

    const title = titleInput.value.trim();
    const content = contentInput.value.trim();
    const type = selectedType ? selectedType.value : '';
    const startDate = startDateInput.value;

    
    
    if (!title) {
        
        this.showError('Please enter a title');
        titleInput.focus();
        return false;
    }

    if (!content) {
        
        this.showError('Please enter announcement content');
        contentInput.focus();
        return false;
    }

    if (!type) {
        
        this.showError('Please select an announcement type');
        return false;
    }

    if (!startDate) {
        
        this.showError('Please select a start date');
        startDateInput.focus();
        return false;
    }

    // Validate end date if provided
    const endDateInput = document.getElementById('announcementEndDate');
    const endDate = endDateInput ? endDateInput.value : '';
    if (endDate && new Date(endDate) <= new Date(startDate)) {
        
        this.showError('End date must be after start date');
        if (endDateInput) endDateInput.focus();
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
            
            this.showError('Failed to save draft');
        }
    }

     resetForm() {
    const form = document.getElementById('announcementForm');
    if (!form) return; 
    
    form.reset();

    // Reset type selection to default (information)
    const typeCards = document.querySelectorAll('.type-card');
    typeCards.forEach(card => {
        card.classList.remove('selected');
    });
    
    // Select the information type by default
    const infoTypeCard = document.querySelector('.type-card[data-type="information"]');
    if (infoTypeCard) {
        infoTypeCard.classList.add('selected');
        const radio = infoTypeCard.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    }

    // Reset pin checkbox
    const pinCheckbox = document.getElementById('announcementIsPinned');
    if (pinCheckbox) {
        pinCheckbox.checked = false;
        this.updatePinCheckboxVisual(false);
    }
    
    const announcementId = document.getElementById('announcementId');
    const announcementType = document.getElementById('announcementType');
    const announcementIsPinned = document.getElementById('announcementIsPinned');
    const announcementStartDate = document.getElementById('announcementStartDate');
    
    // Safely set values only if elements exist
    if (announcementId) announcementId.value = '';
    if (announcementType) announcementType.value = 'information';
    if (announcementIsPinned) announcementIsPinned.checked = false;

    // Set default start date to current datetime if element exists
    if (announcementStartDate) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        announcementStartDate.value = now.toISOString().slice(0, 16);
    }

    // Reset UI - safely check if elements exist
    const createAnnouncementTitle = document.getElementById('createAnnouncementTitle');
    const createAnnouncementSubtitle = document.getElementById('createAnnouncementSubtitle');
    
    if (createAnnouncementTitle) {
        createAnnouncementTitle.textContent = 'Create New Announcement';
    }
    if (createAnnouncementSubtitle) {
        createAnnouncementSubtitle.textContent = 'Share important information with users';
    }

    // Reset character counters - safely check if elements exist
    const titleInput = document.getElementById('announcementTitle');
    const contentInput = document.getElementById('announcementContent');
    
    if (titleInput) {
        this.updateCharCounter(titleInput, 'titleCharCount', 200);
    }
    if (contentInput) {
        this.updateCharCounter(contentInput, 'contentCharCount', 2000);
    }

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
    if (!dateString) return 'No date specified';
    
    
    // Handle different date formats
    let date;
    
    // Try parsing as ISO string first
    date = new Date(dateString);
    
    // If that fails, try parsing MySQL datetime format
    if (isNaN(date.getTime())) {
        // MySQL format: YYYY-MM-DD HH:MM:SS
        const mysqlFormat = dateString.replace(' ', 'T');
        date = new Date(mysqlFormat);
    }
    
    // If still invalid, try manual parsing
    if (isNaN(date.getTime())) {
        // Try common date formats
        const formats = [
            dateString, // original
            dateString.replace(/\//g, '-'), // replace slashes with dashes
            dateString.split(' ')[0], // take only date part
        ];
        
        for (const format of formats) {
            date = new Date(format);
            if (!isNaN(date.getTime())) break;
        }
    }
    
    // If still invalid, return a safe fallback
    if (isNaN(date.getTime())) {
        
        return 'Date not available';
    }
    
    // Format the valid date
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

    formatDateForInput(dateString) {
    if (!dateString) {
        
        return '';
    }
    
    
    try {
        // Handle different date formats
        let date;
        
        // Try parsing as ISO string first
        date = new Date(dateString);
        
        // If that fails, try parsing MySQL datetime format
        if (isNaN(date.getTime())) {
            // MySQL format: YYYY-MM-DD HH:MM:SS
            const mysqlFormat = dateString.replace(' ', 'T');
            date = new Date(mysqlFormat);
        }
        
        // If still invalid, try manual parsing
        if (isNaN(date.getTime())) {
            // Try common date formats
            const formats = [
                dateString,
                dateString.replace(/\//g, '-'),
                dateString.split(' ')[0],
            ];
            
            for (const format of formats) {
                date = new Date(format);
                if (!isNaN(date.getTime())) break;
            }
        }
        
        // If still invalid, return empty
        if (isNaN(date.getTime())) {
            
            return '';
        }
        
        // Convert to local timezone and format for datetime-local input
        const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
        const result = localDate.toISOString().slice(0, 16);
        
        
        return result;
        
    } catch (error) {
        
        return '';
    }
}

    showLoading(containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = `
            <div class="loading-state" 
                style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 50vh; width: 175vh; text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 10px;"></i>
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
    
    
    
    
}

    initializeTypeSelection() {
    const typeCards = document.querySelectorAll('.type-card');
    typeCards.forEach(card => {
        card.addEventListener('click', () => {
            // Remove selected class from all cards
            typeCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            card.classList.add('selected');
            
            // Check the radio button
            const radio = card.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });
}

        async loadAnnouncements() {
    try {
        this.showLoading('activeAnnouncementsGrid');
        
        const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getActiveAnnouncements');
        const data = await response.json();
        
        
        
        if (data.success) {
            // Debug: log the first announcement's dates
            if (data.announcements && data.announcements.length > 0) {
                
            }
            
            this.announcements = data.announcements;
            this.applyFiltersAndSort();
        } else {
            throw new Error(data.error || 'Failed to load announcements');
        }
    } catch (error) {
        
        this.showError('activeAnnouncementsGrid', 'Failed to load announcements');
    }
}

debugDates() {
    
    
    if (this.announcements && this.announcements.length > 0) {
        this.announcements.forEach((ann, index) => {
            
        });
    }
    
}

// Add this method to check form visibility
checkFormVisibility() {
    
    
    const createView = document.getElementById('createAnnouncementView');
    const form = document.getElementById('announcementForm');
    
    if (createView) {
        
        
    } else {
        
    }
    
    if (form) {
        
        
    }
}

// Helper method to check if element is in viewport
isElementInViewport(el) {
    if (!el) return false;
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}



}