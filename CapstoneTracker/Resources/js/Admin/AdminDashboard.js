
// Now the DOMContentLoaded event starts here
console.log('AnnouncementManager available:', typeof AnnouncementManager !== 'undefined');

// Add PDF.js library loading at the top of your file (before DOMContentLoaded)
function loadPdfJsLibrary() {
    if (typeof pdfjsLib === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js';
        script.onload = function() {
            console.log('PDF.js library loaded successfully');
            // Set worker source after library loads
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        };
        document.head.appendChild(script);
    }
}

// Call this function early
loadPdfJsLibrary();


let uploadedFiles = {
    abstract: [],
    thesis: []
};

document.addEventListener('DOMContentLoaded', function() {
    // Profile functionality (existing code)
   
    const logoutBtn = document.getElementById('logoutHeaderIcon');

    const fabIcon = document.querySelector('.fab-icon');
    const uploadModal = document.getElementById('uploadModal');
    

    const abstractDropArea = document.getElementById('abstractDropArea');
    const thesisDropArea = document.getElementById('thesisDropArea');
    const abstractFileInput = document.getElementById('abstractFileInput');
    const thesisFileInput = document.getElementById('thesisFileInput');

    const btnUpload = document.querySelector('.btn-upload');
    
    

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


    window.systemLogsManager = new SystemLogsManager();


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
    window.accountPagination = new AccountPagination();
    
    // Initialize when accounts section is shown
    const accountsOption = document.querySelector('.menu-options li[data-view="accounts"]');
    if (accountsOption) {
        accountsOption.addEventListener('click', function() {
            setTimeout(() => {
                if (window.accountPagination) {
                    window.accountPagination.initialize();
                }
            }, 100);
        });
    }

    const currentView = document.querySelector('.content-container-active');
    if (currentView && currentView.id === 'accounts-container') {
        setTimeout(() => {
            if (window.accountPagination) {
                window.accountPagination.initialize();
            }
        }, 100);
    }


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
                // In the initializeSidebar function, update the logs section:
                if (viewId === 'logs') {
                    // Initialize system logs when logs view is shown
                    setTimeout(() => {
                        if (window.systemLogsManager) {
                            window.systemLogsManager.initialize();
                            // Set default view to 'all'
                            window.systemLogsManager.switchLogView('all');
                        }
                    }, 100);
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
                                
                                window.announcementManager = new AnnouncementManager();
                            } else {
                                
                                // Ensure the view is properly set
                                window.announcementManager.switchView('active');
                            }
                            
                            // Ensure the container is properly displayed
                            const announcementContainer = document.getElementById('announcement-container');
                            if (announcementContainer) {
                                
                            }
                        } else {
                           
                        }
                    }, 300);
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
    
        // Update button states - respect which button was actually clicked
        menuButtons.forEach(button => button.classList.remove('selected'));
        
        // Always select the button that was passed to the function
        if (buttonToSelect) {
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
           
            try {
            uploadModal.classList.add('active');
            document.body.style.overflow = 'hidden';
                
            } catch (error) {
               
            }
        });
    } else {
        
    }

    function initializeModalCloseHandlers() {
        // Close buttons for all modals - these should reset the form
        const modalCloseButtons = document.querySelectorAll('.modal-close, .btn-cancel');
        
        modalCloseButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Find the closest modal overlay
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    // For upload modal, reset the form when explicitly cancelled
                    if (modal.id === 'uploadModal') {
                        resetUploadForm();
                    }
                    closeModal(modal);
                }
            });
        });
        
        // Close modal when clicking outside - DON'T reset the form
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        // Only close the modal, don't reset the form when clicking outside
                        closeModal(this);
                    }
                });
            }
        });
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
       
        
        const fileListId = fileType === 'abstract' ? 'abstractFileList' : 'thesisFileList';
        const fileList = document.getElementById(fileListId);
        const fileCategory = fileList.closest('.file-category');
        if (fileCategory) {
            fileCategory.classList.add('has-files');
        }
        
        // FIX: Check if file already exists in the display before adding
        const existingFileItems = fileList.querySelectorAll('.file-item-card');
        for (let existingItem of existingFileItems) {
            const existingFileName = existingItem.querySelector('.file-name-preview').textContent;
            if (existingFileName === file.name) {
               
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
        
        const fileListId = fileType === 'abstract' ? 'abstractFileList' : 'thesisFileList';
        const fileList = document.getElementById(fileListId);
        if (uploadedFiles[fileType].length === 0) {
            const fileCategory = fileList.closest('.file-category');
            if (fileCategory) {
                fileCategory.classList.remove('has-files');
            }
        }

        // FIX: Clear the file input value to allow re-selection of the same file
        if (fileType === 'abstract' && abstractFileInput) {
            abstractFileInput.value = '';
        } else if (fileType === 'thesis' && thesisFileInput) {
            thesisFileInput.value = '';
        }
        
        updateUploadButtonState();
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
    
        
        // Get the current active view (allView or recentView)
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');
        const currentView = recentView.style.display !== 'none' ? recentView : allView;
        
        // Get project items from the CURRENTLY VISIBLE view only
        const projectItems = currentView.querySelectorAll('.project-item');
        const projectItemsArray = Array.from(projectItems);
        
        if (projectItemsArray.length === 0) {
       
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
        
        // Remove any existing animation classes and ensure hidden state
        projectItems.forEach(item => {
            item.classList.remove('animate__animated', 'animate__fadeInUp', 'animate__fast');
            // Ensure items start hidden
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
        });
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp', 'animate__fast');
                    // Make sure item becomes visible
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px' // Trigger animation when 50px from viewport
        });
        
        // Observe all project items
        projectItems.forEach(item => {
            // Only observe if the item is visible
            if (window.getComputedStyle(item).display !== 'none') {
                observer.observe(item);
            }
        });
        
        // Force animation for items already in viewport after a short delay
        setTimeout(() => {
            projectItems.forEach(item => {
                const rect = item.getBoundingClientRect();
                const isInViewport = (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
                
                if (isInViewport && window.getComputedStyle(item).display !== 'none') {
                    item.classList.add('animate__animated', 'animate__fadeInUp', 'animate__fast');
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }
            });
        }, 100);
    };
    
    // Call the animation function
    animateOnScroll();

    // Initialize with recent view visible
    if (recentView && recentButton) {
        switchView(recentView, recentButton);
    } else if (allView && allButton) {
        // Fallback to all view if recent view is not available
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

        <div class="menu-item" id="colorSchemeToggle">
            <i class="fas fa-palette"></i>
            <span>Change Color</span>
            <div class="toggle-switch">
                <input type="checkbox" id="colorSchemeCheckbox">
                <span class="toggle-slider"></span>
            </div>
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
                                // UPDATE THE UI WITHOUT RELOADING THE PAGE
                                updateUserStatusInUI(userId, 'approved', button);
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

    function updateUserStatusInUI(userId, newStatus, button) {
        // Find the user row in the table
        const userRow = button.closest('tr');
        if (!userRow) return;
    
        // Update the status badge
        const statusBadge = userRow.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.textContent = newStatus;
            statusBadge.className = 'status-badge'; // Reset classes
            
            // Update badge styling based on status
            if (newStatus === 'approved') {
                statusBadge.classList.add('status-approved');
            } else if (newStatus === 'pending') {
                statusBadge.classList.add('status-pending');
            } else if (newStatus === 'rejected') {
                statusBadge.classList.add('status-rejected');
            }
        }
    
        // Update the approve button - disable it and change appearance
        if (button && newStatus === 'approved') {
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('disabled');
            button.disabled = true;
            button.title = 'Account Approved';
            
            // Remove click event listener to prevent further actions
            button.replaceWith(button.cloneNode(true));
        }
    
        // Update counts in the filter tabs if they exist
        updateAccountCounts();
    
        // If we're in a filtered view (pending only), remove the row from view
        const currentFilter = getCurrentAccountFilter();
        if (currentFilter === 'pendingButton' && newStatus === 'approved') {
            // Add fade out animation
            userRow.style.transition = 'all 0.3s ease';
            userRow.style.opacity = '0';
            userRow.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                userRow.style.display = 'none';
                
                // Check if no more pending accounts are visible
                const visibleRows = userRow.parentElement.querySelectorAll('tr');
                const hasVisibleRows = Array.from(visibleRows).some(row => 
                    row.style.display !== 'none' && 
                    row.querySelector('.status-badge')?.textContent === 'pending'
                );
                
                if (!hasVisibleRows) {
                    showNoResultsMessage();
                }
            }, 300);
        }
    }
    
    // Helper function to get current account filter
    function getCurrentAccountFilter() {
        const activeButton = document.querySelector('.logMenu button.selected');
        return activeButton ? activeButton.id : 'allAccountsButton';
    }
    
   
    
    // Helper function to show no results message
    function showNoResultsMessage() {
        const tableBody = document.querySelector('.accounts-table tbody');
        if (!tableBody.querySelector('.no-results-row')) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="5" class="text-center">
                    <div class="no-results-message">
                        <i class="fas fa-inbox"></i>
                        <h3>No Pending Accounts</h3>
                        <p>All accounts have been processed.</p>
                    </div>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        }
    }

    // Function to delete account
    async function deleteAccount(userId, userName, button) {
    try {
        const result = await Swal.fire({
            title: 'Delete Account?',
            html: `Are you sure you want to delete <strong>${userName}</strong>'s account? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            // Show loading state
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            try {
                // Use the correct endpoint for account deletion
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);

                const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=deleteUser', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: result.message || 'Account has been deleted successfully.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(result.error || 'Failed to delete account');
                }
            } catch (error) {
               
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
    } catch (error) {
      
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
                    // DON'T reset the form when opening modal - keep existing files
                    // resetUploadForm(); // REMOVE THIS LINE
                    
                    uploadModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    console.log('Modal opened successfully');
                    
                    // Update button state in case files are already selected
                    updateUploadButtonState();
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
                                        // Send email notifications
                                        const thesisTitle = document.getElementById('thesisTitle').value;
                                        const authorEmails = document.getElementById('thesisAuthor').value.split(',').map(email => email.trim());
                                        const adviserEmail = document.getElementById('thesisAdviser').value.trim();
                                        
                                        // Send notifications (fire and forget - don't wait for response)
                                        sendThesisUploadNotifications(data.thesis_id, thesisTitle, authorEmails, adviserEmail);
                                        
                                        Swal.fire({
                                            title: 'Upload Successful!',
                                            text: data.message || 'Your thesis has been uploaded successfully.',
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            resetUploadForm();
                                            closeModal(uploadModal);
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
            // Get the course from the project item - FIXED SELECTOR
            const courseElement = item.querySelector('.links p:nth-child(2)'); // Second paragraph in links div
            const course = courseElement ? courseElement.textContent.trim() : '';
            
            console.log('Project course:', course, 'for item:', item.querySelector('h3').textContent);
            
            let shouldShow = false;
            
            if (departmentValue === 'all') {
                shouldShow = true;
            } else {
                // Get course codes for the selected department
                const courseCodes = getCourseCodesForDepartment(departmentValue);
                console.log('Course codes for department:', courseCodes);
                
                // Check if this project's course matches any course in the department
                shouldShow = courseCodes.some(courseCode => {
                    // More flexible matching - check if course contains courseCode or vice versa
                    const match = course.toLowerCase().includes(courseCode.toLowerCase()) || 
                           courseCode.toLowerCase().includes(course.toLowerCase());
                    console.log(`Comparing "${course}" with "${courseCode}": ${match}`);
                    return match;
                });
            }
            
            if (shouldShow) {
                item.style.display = 'flex';
                foundResults = true;
                console.log('SHOWING item:', item.querySelector('h3').textContent);
            } else {
                item.style.display = 'none';
                console.log('HIDING item:', item.querySelector('h3').textContent);
            }
        });
        
        // Show/hide "No Results Found" message
        if (foundResults || departmentValue === 'all') {
            if (notFound) notFound.style.display = 'none';
        } else {
            if (notFound) notFound.style.display = 'flex';
        }
        
        console.log('Filter complete. Found results:', foundResults);
        
        // Re-run animations after filtering
        animateOnScroll();
    }

    function getCourseCodesForDepartment(departmentValue) {
        // Updated to match the actual course names from your PHP
        const departmentMap = {
            'beced': ['Bachelor of Early Childhood Education'],
            'bsed': ['Bachelor of Secondary Education'],
            'btvted': ['Bachelor of Technical-Vocational Teacher Education'],
            'beed': ['Bachelor of Elementary Education'],
            'bsned': ['Bachelor of Special Needs Education'],
            'bsabe': [
                'Bachelor of Science in Agricultural and Biosystems Engineering',
                'Bachelor of Science in Agriculture and Biosystems Engineering' // Include both variations
            ],
            'bsit': ['Bachelor of Science in Information Technology']
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

    const sendThesisUploadNotifications = async (thesisId, thesisTitle, authorEmails, adviserEmail) => {
        try {
            console.log('Sending notifications for thesis:', thesisId);
            
            const payload = {
                thesis_id: thesisId,
                thesis_title: thesisTitle,
                author_emails: Array.isArray(authorEmails) ? authorEmails : [authorEmails],
                adviser_email: adviserEmail
            };
    
            console.log('Payload:', payload);
    
            const response = await fetch('ThesisController.php?action=sendThesisNotifications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            });
    
            const result = await response.json();
            
            if (result.success) {
                Swal.fire('Success!', 'Notifications sent successfully.', 'success');
                return true;
            } else {
                throw new Error(result.error || 'Unknown error occurred');
            }
            
        } catch (error) {
            console.error('Notification error:', error);
            Swal.fire('Warning', `Thesis uploaded but notifications failed: ${error.message}`, 'warning');
            return false;
        }
    };

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

    const viewThesisBtn = document.querySelector('.btn-tertiary');
    if (viewThesisBtn) {
        viewThesisBtn.addEventListener('click', function() {
            // Get the active project item (the one currently being viewed)
            const activeProjectItem = document.querySelector('.project-item.active-preview');
            
            if (activeProjectItem) {
                const thesisId = activeProjectItem.getAttribute('data-thesis-id');
                const title = activeProjectItem.querySelector('h3')?.textContent || 'Thesis';
                
                console.log('Thesis ID from data attribute:', thesisId); // Debug
                
                if (thesisId && !isNaN(thesisId)) {
                    fetchThesisFileForView(thesisId, title);
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Invalid thesis ID',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'No thesis selected',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
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

    async function testDebugMethod() {
    try {
        
        const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=debugLogs');
        const data = await response.json();
     
        
        
    } catch (error) {
       
    }
}

    let inactivityTime = function() {
        let time;
        
        const resetTimer = function() {
            clearTimeout(time);
            time = setTimeout(() => {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired due to inactivity. You will be redirected to the login page.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    window.location.href = '../User/indexLogin.php';
                });
            }, 600000);
        };
        
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;
        document.onmousedown = resetTimer;
        document.ontouchstart = resetTimer;
        
        resetTimer();
    };
    
    inactivityTime();
    
    let warningTime;
    const setWarningTimer = function() {
        clearTimeout(warningTime);
        warningTime = setTimeout(() => {
            Swal.fire({
                title: 'Session About to Expire',
                text: 'Your session will expire in 1 minute due to inactivity. Press Button to continue.',
                icon: 'info',
                timer: 60000, 
                timerProgressBar: true,
                showConfirmButton: true,
                confirmButtonText: 'Continue Session',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    inactivityTime();
                    setWarningTimer();
                }
            });
        }, 540000);
    };
    
    setWarningTimer();
    
    const resetWarningTimer = function() {
        setWarningTimer();
    };
    
    document.addEventListener('mousemove', resetWarningTimer);
    document.addEventListener('keypress', resetWarningTimer);
    document.addEventListener('click', resetWarningTimer);

    //testinggggg

    console.log('Session timeout set to 20 seconds. Warning at 10 seconds.');


    const moreOptionsToggle = document.getElementById('moreOptionsToggle');
    const moreOptionsMenu = document.getElementById('moreOptionsMenu');
    
    if (moreOptionsToggle && moreOptionsMenu) {
        moreOptionsToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            moreOptionsMenu.classList.toggle('active');
        });
        

        document.addEventListener('click', function() {
            moreOptionsMenu.classList.remove('active');
        });
        

        moreOptionsMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
 
    const colorSchemeToggle = document.getElementById('colorSchemeToggle');

    const colorSchemeCheckbox = document.getElementById('colorSchemeCheckbox');
    
    if (colorSchemeToggle && colorSchemeCheckbox) {
        const savedTheme = localStorage.getItem('adminDashboardTheme');
        if (savedTheme === 'dark') {
            
            document.documentElement.setAttribute('data-theme', 'dark');
            
            colorSchemeCheckbox.checked = true;
            updateToggleLabel(true);
        } else {
            
            
            updateToggleLabel(false);


        }
        
        colorSchemeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');

                localStorage.setItem('adminDashboardTheme', 'dark');
                updateToggleLabel(true);


            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('adminDashboardTheme', 'light');
                updateToggleLabel(false);

            }
        });
        

        colorSchemeToggle.addEventListener('click', function(e) {
            if (e.target !== colorSchemeCheckbox) {
                colorSchemeCheckbox.checked = !colorSchemeCheckbox.checked;

                colorSchemeCheckbox.dispatchEvent(new Event('change'));

            }


        });
    }
    
    function updateToggleLabel(isDark) {
        const toggleLabel = document.querySelector('#colorSchemeToggle span');
        if (toggleLabel) {
            toggleLabel.textContent = isDark ? 'Change Color' : 'Change Color';
        }
    }
    
    const logoutOption = document.getElementById('logoutOption');
    
    if (logoutOption) {

        logoutOption.addEventListener('click', function() {
            window.location.href = '../User/indexLogin.php';
        });

    }


testDebugMethod();

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

    document.querySelectorAll('.project-item').forEach(item => {
        item.classList.remove('active-preview');
    });

    projectItem.classList.add('active-preview');
    
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
    if (!file) {
        console.error('File not found:', fileName);
        return;
    }
    
    // Get modal elements
    const previewModal = document.getElementById('previewModal');
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const pdfViewer = document.getElementById('pdf-viewer');
    const unsupportedFile = document.getElementById('unsupported-file');
    const downloadLink = document.getElementById('download-link');
    
    if (!previewModal || !docViewerIframe || !pdfViewer || !unsupportedFile || !downloadLink) {
        console.error('Preview modal elements not found');
        return;
    }
    
    // Reset viewer states
    docViewerIframe.style.display = 'none';
    pdfViewer.style.display = 'none';
    unsupportedFile.style.display = 'none';
    
    const fileExtension = file.name.split('.').pop().toLowerCase();
    const fileUrl = URL.createObjectURL(file);
    
    // Set download link
    downloadLink.href = fileUrl;
    downloadLink.download = file.name;
    downloadLink.style.display = 'block';
    
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
    const pdfViewer = document.getElementById('pdf-viewer');
    
    if (!pdfViewer) {
        console.error('PDF viewer element not found');
        return;
    }
    
    // Clear previous content and show loading
    pdfViewer.innerHTML = '<div class="loading-preview"><i class="fas fa-spinner fa-spin"></i><p>Loading PDF...</p></div>';
    pdfViewer.style.display = 'block';
    
    // Check if PDF.js is available
    if (typeof pdfjsLib === 'undefined') {
        console.error('PDF.js library not loaded');
        showPdfError('PDF viewer library not loaded. Please refresh the page.');
        return;
    }
    
    // Ensure worker is set
    if (!pdfjsLib.GlobalWorkerOptions.workerSrc) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    }
    
    // Clear previous PDF document
    if (window.currentPdfDoc) {
        window.currentPdfDoc.destroy();
        window.currentPdfDoc = null;
    }
    
    // Show footer controls
    const pdfFooterControls = document.getElementById('pdf-footer-controls');
    if (pdfFooterControls) {
        pdfFooterControls.style.display = 'flex';
    }
    
    // Load the PDF document
    pdfjsLib.getDocument(url).promise.then(function(pdfDoc) {
        console.log('PDF loaded successfully, pages:', pdfDoc.numPages);
        
        // Store the PDF document globally
        window.currentPdfDoc = pdfDoc;
        window.currentPageNum = 1;
        
        // Clear loading state
        pdfViewer.innerHTML = '';
        
        // Update total pages in footer
        const totalPagesElement = document.getElementById('pdf-total-pages');
        if (totalPagesElement) {
            totalPagesElement.textContent = pdfDoc.numPages;
        }
        
        // Render the first page
        renderPage(window.currentPageNum);
        
        // Add event listeners to footer controls
        addPdfFooterControls(pdfDoc);
        
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        showPdfError(`Failed to load PDF: ${error.message}`);
    });
}

function showPdfError(message) {
    const pdfViewer = document.getElementById('pdf-viewer');
    if (pdfViewer) {
        pdfViewer.innerHTML = `
            <div class="error-preview">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
                <button class="btn-retry" onclick="location.reload()">
                    Reload Page
                </button>
            </div>
        `;
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

function addPdfFooterControls(pdfDoc) {
    const prevBtn = document.getElementById('prev-page-footer');
    const nextBtn = document.getElementById('next-page-footer');
    
    if (prevBtn && nextBtn) {
        // Remove existing event listeners first
        const newPrevBtn = prevBtn.cloneNode(true);
        const newNextBtn = nextBtn.cloneNode(true);
        
        prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
        nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
        
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
    }
    
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

function closeModal(modal) {
    if (!modal) {
        console.error('Modal element not provided');
        return;
    }
    
    try {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Clean up resources based on modal type
        if (modal.id === 'previewModal') {
            // Clean up blob URLs
            if (window.currentPdfBlobUrl) {
                URL.revokeObjectURL(window.currentPdfBlobUrl);
                window.currentPdfBlobUrl = null;
            }
            
            // Clean up PDF.js resources
            if (window.currentPdfDoc) {
                window.currentPdfDoc.destroy();
                window.currentPdfDoc = null;
            }
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
        // REMOVED: Don't reset upload form when modal closes
        
        console.log('Modal closed successfully');
    } catch (error) {
        console.error('Error closing modal:', error);
    }
}




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





class SystemLogsManager {
    constructor() {
        this.currentLogView = 'all';
        this.currentLogFilter = 'all';
        this.logsData = {
            all: [],
            user: [],
            admin: []
        };
    }

    initialize() {
        this.initializeEventListeners();
        this.loadAllLogs();
    }

    initializeEventListeners() {
        // Log view switching
        const allLogsButton = document.getElementById('allLogsButton');
        const userLogsButton = document.getElementById('userLogsButton');
        const adminLogsButton = document.getElementById('adminLogsButton');
        
        if (allLogsButton) {
            allLogsButton.addEventListener('click', () => this.switchLogView('all'));
        }
        if (userLogsButton) {
            userLogsButton.addEventListener('click', () => this.switchLogView('user'));
        }
        if (adminLogsButton) {
            adminLogsButton.addEventListener('click', () => this.switchLogView('admin'));
        }

        // Log filtering
        const logFilterButtons = document.querySelectorAll('.log-filter-btn');
        logFilterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const filter = e.target.getAttribute('data-filter');
                this.applyLogFilter(filter);
            });
        });

        // Search functionality
        const allLogSearch = document.getElementById('allLogSearchInput');
        const userLogSearch = document.getElementById('userLogSearchInput');
        const adminLogSearch = document.getElementById('adminLogSearchInput');
        
        if (allLogSearch) {
            allLogSearch.addEventListener('input', (e) => this.handleLogSearch(e.target.value, 'all'));
        }
        if (userLogSearch) {
            userLogSearch.addEventListener('input', (e) => this.handleLogSearch(e.target.value, 'user'));
        }
        if (adminLogSearch) {
            adminLogSearch.addEventListener('input', (e) => this.handleLogSearch(e.target.value, 'admin'));
        }
    }

        switchLogView(view) {
            this.currentLogView = view;
            
            // Update button states
            const allLogsButton = document.getElementById('allLogsButton');
            const userLogsButton = document.getElementById('userLogsButton');
            const adminLogsButton = document.getElementById('adminLogsButton');
            
            const allLogsView = document.getElementById('allLogs-container');
            const userLogsView = document.getElementById('userLogs-container');
            const adminLogsView = document.getElementById('adminLogs-container');

            if (allLogsButton && userLogsButton && adminLogsButton && 
                allLogsView && userLogsView && adminLogsView) {
                
                // Update buttons
                allLogsButton.classList.remove('selected');
                userLogsButton.classList.remove('selected');
                adminLogsButton.classList.remove('selected');
                
                // Hide all views
                allLogsView.style.display = 'none';
                userLogsView.style.display = 'none';
                adminLogsView.style.display = 'none';
                
                // Show selected view and activate button
                if (view === 'all') {
                    allLogsButton.classList.add('selected');
                    allLogsView.style.display = 'block';
                    if (this.logsData.all.length === 0) {
                        this.loadAllLogs();
                    }
                } else if (view === 'user') {
                    userLogsButton.classList.add('selected');
                    userLogsView.style.display = 'block';
                    if (this.logsData.user.length === 0) {
                        this.loadUserLogs();
                    }
                } else {
                    adminLogsButton.classList.add('selected');
                    adminLogsView.style.display = 'block';
                    if (this.logsData.admin.length === 0) {
                        this.loadAdminLogs();
                    }
                }
            }
        }

        async loadAllLogs() {
            try {
                console.log('=== LOADING ALL LOGS ===');
                this.showLogsLoading('all');
                
                // Load audit logs
                const auditLogsResponse = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getAuditLogs&limit=100');
                const auditLogsData = await auditLogsResponse.json();
                
                // Load login attempts  
                const loginAttemptsResponse = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getLoginAttempts&limit=100');
                const loginAttemptsData = await loginAttemptsResponse.json();
                
                // Process the data
                const auditLogs = auditLogsData.success ? auditLogsData.logs : [];
                const loginAttempts = loginAttemptsData.success ? loginAttemptsData.attempts : [];
                
                console.log('Audit logs:', auditLogs.length, 'Login attempts:', loginAttempts.length);
                
                // Combine and format the data
                const formattedLogs = this.formatAllLogs(auditLogs, loginAttempts);
                
                this.logsData.all = formattedLogs;
                this.displayLogs(formattedLogs, 'all');
                
            } catch (error) {
                console.error('Error loading all logs:', error);
                this.showLogsError('all', `Failed to load logs: ${error.message}`);
            }
        }

        async loadUserLogs() {
            try {
                this.showLogsLoading('user');
                
                // Load user-specific logs (login attempts and user-related audit logs)
                const loginAttemptsResponse = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getLoginAttempts&limit=100');
                const loginAttemptsData = await loginAttemptsResponse.json();
                
                const auditLogsResponse = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getAuditLogs&table=USER_INFORMATION&limit=50');
                const auditLogsData = await auditLogsResponse.json();
                
                const loginAttempts = loginAttemptsData.success ? loginAttemptsData.attempts : [];
                const userAuditLogs = auditLogsData.success ? auditLogsData.logs : [];
                
                const formattedLogs = this.formatUserLogs(userAuditLogs, loginAttempts);
                this.logsData.user = formattedLogs;
                this.displayLogs(formattedLogs, 'user');
                
            } catch (error) {
                console.error('Error loading user logs:', error);
                this.showLogsError('user', `Failed to load user logs: ${error.message}`);
            }
        }

        async loadAdminLogs() {
            try {
                this.showLogsLoading('admin');
                
                // Load admin-specific logs (system actions, announcements, etc.)
                const auditLogsResponse = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getAuditLogs&limit=100');
                const auditLogsData = await auditLogsResponse.json();
                
                const adminLogs = auditLogsData.success ? auditLogsData.logs : [];
                const formattedLogs = this.formatAdminLogs(adminLogs);
                
                this.logsData.admin = formattedLogs;
                this.displayLogs(formattedLogs, 'admin');
                
            } catch (error) {
                console.error('Error loading admin logs:', error);
                this.showLogsError('admin', `Failed to load admin logs: ${error.message}`);
            }
        }

        formatAllLogs(auditLogs, loginAttempts) {
            const formattedLogs = [];

            // Format audit logs
            auditLogs.forEach(log => {
                if (!log) return;

                const userName = this.getUserNameFromLog(log);
                const action = this.getActionFromLog(log);
                const details = this.getDetailsFromLog(log);
                const logType = this.getLogTypeFromLog(log);

                formattedLogs.push({
                    type: logType,
                    timestamp: log.changed_at || log.timestamp || new Date().toISOString(),
                    user: userName,
                    action: action,
                    details: details,
                    ip: log.ip_address || 'N/A',
                    source: 'audit'
                });
            });

            // Format login attempts
            loginAttempts.forEach(attempt => {
                if (!attempt) return;

                const userName = attempt.First_Name && attempt.Last_Name ? 
                    `${attempt.First_Name} ${attempt.Last_Name}` : 
                    (attempt.email || 'Unknown User');
                    
                const isSuccess = attempt.success === true || attempt.success === '1' || attempt.status === 'success';
                
                formattedLogs.push({
                    type: 'login',
                    timestamp: attempt.attempt_time || attempt.timestamp || new Date().toISOString(),
                    user: userName,
                    action: isSuccess ? 'Login Successful' : 'Login Failed',
                    details: isSuccess ? 
                        `Successful login from IP ${attempt.ip_address || 'Unknown'}` :
                        `Failed login attempt from IP ${attempt.ip_address || 'Unknown'}`,
                    ip: attempt.ip_address || 'N/A',
                    source: 'login'
                });
            });

            // Sort by timestamp (newest first)
            return formattedLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        }

        formatUserLogs(userAuditLogs, loginAttempts) {
            // Filter and format user-specific logs
            const formattedLogs = [];

            userAuditLogs.forEach(log => {
                if (log.table_name === 'USER_INFORMATION') {
                    const userName = this.getUserNameFromLog(log);
                    const action = this.getActionFromLog(log);
                    const details = this.getDetailsFromLog(log);

                    formattedLogs.push({
                        type: 'user_management',
                        timestamp: log.changed_at,
                        user: userName,
                        action: action,
                        details: details,
                        ip: log.ip_address || 'N/A'
                    });
                }
            });

            // Add login attempts
            loginAttempts.forEach(attempt => {
                const userName = attempt.First_Name && attempt.Last_Name ? 
                    `${attempt.First_Name} ${attempt.Last_Name}` : 
                    (attempt.email || 'Unknown User');
                    
                const isSuccess = attempt.success === true || attempt.success === '1' || attempt.status === 'success';
                
                formattedLogs.push({
                    type: 'login',
                    timestamp: attempt.attempt_time,
                    user: userName,
                    action: isSuccess ? 'Login Successful' : 'Login Failed',
                    details: `IP: ${attempt.ip_address || 'Unknown'}`,
                    ip: attempt.ip_address || 'N/A'
                });
            });

            return formattedLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        }

        formatAdminLogs(auditLogs) {
            // Filter and format admin-specific logs
            const formattedLogs = auditLogs
                .filter(log => log.table_name && ['ANNOUNCEMENTS', 'SYSTEM_LOGS', 'THESIS'].includes(log.table_name))
                .map(log => {
                    const userName = this.getUserNameFromLog(log);
                    const action = this.getActionFromLog(log);
                    const details = this.getDetailsFromLog(log);
                    const logType = this.getLogTypeFromLog(log);

                    return {
                        type: logType,
                        timestamp: log.changed_at,
                        user: userName,
                        action: action,
                        details: details,
                        ip: log.ip_address || 'N/A'
                    };
                });

            return formattedLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        }

        getUserNameFromLog(log) {
            // Try to get user name from various possible fields
            if (log.First_Name && log.Last_Name) {
                return `${log.First_Name} ${log.Last_Name}`;
            }
            if (log.user_name) {
                return log.user_name;
            }
            if (log.email) {
                return log.email;
            }
            if (log.user_id) {
                return `User ID: ${log.user_id}`;
            }
            return 'System';
        }

        getActionFromLog(log) {
            if (log.action) {
                switch (log.action.toUpperCase()) {
                    case 'INSERT':
                        if (log.table_name === 'ANNOUNCEMENTS') return 'Announcement Created';
                        if (log.table_name === 'THESIS') return 'Thesis Uploaded';
                        return 'Record Created';
                    case 'UPDATE':
                        if (log.table_name === 'ANNOUNCEMENTS') return 'Announcement Updated';
                        if (log.table_name === 'USER_INFORMATION') return 'User Updated';
                        return 'Record Updated';
                    case 'DELETE':
                        if (log.table_name === 'ANNOUNCEMENTS') return 'Announcement Deleted';
                        return 'Record Deleted';
                    default:
                        return log.action;
                }
            }
            return 'System Action';
        }

        getDetailsFromLog(log) {
            if (log.table_name === 'ANNOUNCEMENTS') {
                try {
                    if (log.new_values) {
                        const newValues = typeof log.new_values === 'string' ? JSON.parse(log.new_values) : log.new_values;
                        return `Title: "${newValues.title || 'Unknown'}", Type: ${newValues.type || 'information'}`;
                    }
                    if (log.old_values) {
                        const oldValues = typeof log.old_values === 'string' ? JSON.parse(log.old_values) : log.old_values;
                        return `Title: "${oldValues.title || 'Unknown'}"`;
                    }
                } catch (e) {
                    return 'Announcement modified';
                }
            }
            
            if (log.table_name === 'USER_INFORMATION') {
                try {
                    if (log.new_values) {
                        const newValues = typeof log.new_values === 'string' ? JSON.parse(log.new_values) : log.new_values;
                        const changes = [];
                        if (newValues.User_Role) changes.push(`Role: ${newValues.User_Role}`);
                        if (newValues.Acc_Status) changes.push(`Status: ${newValues.Acc_Status}`);
                        return changes.length > 0 ? changes.join('; ') : 'User information updated';
                    }
                } catch (e) {
                    return 'User account modified';
                }
            }

            return log.details || 'Details not available';
        }

        getLogTypeFromLog(log) {
            if (log.table_name === 'ANNOUNCEMENTS') return 'announcement';
            if (log.table_name === 'USER_INFORMATION') return 'user';
            if (log.table_name === 'THESIS') return 'thesis';
            return 'system';
        }

        displayLogs(logs, type) {
            const containerId = type === 'all' ? 'allLogsTableBody' : 
                            type === 'user' ? 'userLogsTableBody' : 'adminLogsTableBody';
            const container = document.getElementById(containerId);
            
            if (!container) return;

            if (!logs || logs.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="logs-empty-state">
                                <i class="fas fa-inbox"></i>
                                <h3>No Logs Found</h3>
                                <p>No ${type} logs available for the selected criteria.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            container.innerHTML = logs.map(log => this.createLogRow(log)).join('');
        }

        createLogRow(log) {
            const timestamp = this.formatTimestamp(log.timestamp);
            const actionClass = this.getActionClass(log.action);
            
            return `
                <tr data-log-type="${log.type}">
                    <td>
                        <div class="log-timestamp">${timestamp}</div>
                        <small class="log-ip">${log.ip}</small>
                    </td>
                    <td>
                        <div class="log-user">${this.escapeHtml(log.user)}</div>
                    </td>
                    <td>
                        <span class="log-action ${actionClass}">${this.escapeHtml(log.action)}</span>
                    </td>
                    <td>
                        <div class="log-details">${this.escapeHtml(log.details)}</div>
                    </td>
                </tr>
            `;
        }

        applyLogFilter(filter) {
            this.currentLogFilter = filter;
            
            // Update filter button states
            const currentView = this.currentLogView;
            const filterContainer = document.querySelector(`#${currentView}Logs-container .log-filter-options`);
            if (filterContainer) {
                filterContainer.querySelectorAll('.log-filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                filterContainer.querySelector(`.log-filter-btn[data-filter="${filter}"]`).classList.add('active');
            }
            
            const logs = this.logsData[this.currentLogView];
            const filteredLogs = filter === 'all' ? 
                logs : 
                logs.filter(log => log.type === filter);
            
            this.displayLogs(filteredLogs, this.currentLogView);
        }

        handleLogSearch(searchTerm, type) {
            const logs = this.logsData[type];
            
            if (!searchTerm.trim()) {
                this.applyLogFilter(this.currentLogFilter);
                return;
            }
            
            const filteredLogs = logs.filter(log => 
                log.user.toLowerCase().includes(searchTerm.toLowerCase()) ||
                log.action.toLowerCase().includes(searchTerm.toLowerCase()) ||
                log.details.toLowerCase().includes(searchTerm.toLowerCase())
            );
            
            this.displayLogs(filteredLogs, type);
        }

        showLogsLoading(type) {
            const containerId = type === 'all' ? 'allLogsTableBody' : 
                            type === 'user' ? 'userLogsTableBody' : 'adminLogsTableBody';
            const container = document.getElementById(containerId);
            
            if (container) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="loading-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading ${type} logs...</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        showLogsError(type, message) {
            const containerId = type === 'all' ? 'allLogsTableBody' : 
                            type === 'user' ? 'userLogsTableBody' : 'adminLogsTableBody';
            const container = document.getElementById(containerId);
            
            if (container) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="error-state">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>${message}</p>
                                <button class="btn-retry" onclick="systemLogsManager.load${type.charAt(0).toUpperCase() + type.slice(1)}Logs()">
                                    Retry
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        formatTimestamp(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) {
                return 'Just now';
            } else if (diffMins < 60) {
                return `${diffMins}m ago`;
            } else if (diffHours < 24) {
                return `${diffHours}h ago`;
            } else if (diffDays < 7) {
                return `${diffDays}d ago`;
            } else {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        getActionClass(action) {
            const actionMap = {
                'Login Successful': 'action-login-success',
                'Login Failed': 'action-login-failed',
                'User Registered': 'action-user-create',
                'User Updated': 'action-user-update',
                'User Deleted': 'action-user-delete',
                'Announcement Created': 'action-announcement-create',
                'Announcement Updated': 'action-announcement-update',
                'Announcement Deleted': 'action-announcement-delete',
                'Thesis Uploaded': 'action-thesis-upload'
            };
            
            return actionMap[action] || 'action-system';
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }


setTimeout(testAuditQuery, 500);






