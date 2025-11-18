document.addEventListener('DOMContentLoaded', function() {
    // Profile functionality (existing code)
    const profileHeaderIcon = document.getElementById('profileHeaderIcon');
    const profileSidebarIcon = document.getElementById('profileSidebarIcon');
    const profileContainer = document.getElementById('profileContainer');
    const projectsContainer = document.getElementById('projectsGrid');
    const appContentHeader = document.querySelector('.app-content-header');
    const logoutBtn = document.getElementById('logoutHeaderIcon');

    const fabIcon = document.querySelector('.fab-icon');
    const uploadModal = document.getElementById('uploadModal');
    const previewModal = document.getElementById('previewModal');
    const modalClose = document.querySelectorAll('.modal-close');
    const btnCancel = document.querySelector('.btn-cancel');
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const btnUpload = document.querySelector('.btn-upload');
    const browseBtn = document.querySelector('.browse-btn');
    const uploadBtn = document.getElementById('uploadBtn');
    const closePreview = document.getElementById('closePreview');
    const downloadLink = document.getElementById('download-link');
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const pdfViewer = document.getElementById('pdf-viewer');
    const unsupportedFile = document.getElementById('unsupported-file');

    const allButton = document.getElementById('allButton');
    const recentButton = document.getElementById('recentButton');
    const allView = document.getElementById('allView');
    const recentView = document.getElementById('recentView');

    // Initialize project container functionality
    initializeProjectContainer();

    // ... rest of your existing userViewPage.js code ...

    // PROJECT CONTAINER FUNCTIONS
    function initializeProjectContainer() {
        console.log('Initializing project container...');
        
        // Wait a bit to ensure DOM is fully loaded
        setTimeout(() => {
            // Get the view elements
            const allView = document.getElementById('allView');
            const recentView = document.getElementById('recentView');
            const allButton = document.getElementById('allButton');
            const recentButton = document.getElementById('recentButton');
            
            console.log('View elements found:', {
                allView: !!allView,
                recentView: !!recentView,
                allButton: !!allButton,
                recentButton: !!recentButton
            });
    
            // Initialize all components first
            initializeFilterDropdowns();
            initializeSearch();
            initializeDisplayToggle();
            initializeViewButtons();
            
            // Set the default view - hide all views first, then show recent
            if (allView && recentView) {
                allView.style.display = 'none';
                recentView.style.display = 'none';
                
                // Show recent view by default
                recentView.style.display = 'grid';
                console.log('Default view set to recent');
            }
    
            // Update button states
            if (allButton && recentButton) {
                allButton.classList.remove('selected');
                recentButton.classList.add('selected');
            }
    
            // Run animations
            animateOnScroll();
    }, 100);
    }

    function initializeViewButtons() {
        const allButton = document.getElementById('allButton');
        const recentButton = document.getElementById('recentButton');
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');

        if (allButton && recentButton && allView && recentView) {
            allButton.addEventListener('click', function() {
                console.log('All button clicked');
                switchView(allView, allButton);
            });

            recentButton.addEventListener('click', function() {
                console.log('Recent button clicked');
                switchView(recentView, recentButton);
            });
        } else {
            console.error('View buttons or containers not found');
        }
    }

    function switchView(viewToShow, buttonToSelect) {
        console.log('Switching view to:', viewToShow.id);
        
        // Hide all views
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');
        
        if (allView) allView.style.display = 'none';
        if (recentView) recentView.style.display = 'none';
    
        // Show selected view
        if (viewToShow) {
            viewToShow.style.display = 'grid';
            console.log('View displayed:', viewToShow.id, 'with items:', viewToShow.querySelectorAll('.project-item').length);
        }
    
        // Update button states
        const menuButtons = document.querySelectorAll('.header .menu button');
        menuButtons.forEach(button => button.classList.remove('selected'));
        
        if (buttonToSelect) {
            buttonToSelect.classList.add('selected');
        }
    
        // Reset sort when switching views
        resetSortState();
        
        // Preserve list/grid view setting
        const listViewIcon = document.getElementById('listViewIcon');
        if (listViewIcon) {
            const isListView = listViewIcon.classList.contains('selected');
            const projectsContainers = document.querySelectorAll('.projects');
            
            projectsContainers.forEach(container => {
                if (isListView) {
                    container.style.gridTemplateColumns = '1fr';
                } else {
                    container.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
                }
            });
        }
        
        // Re-run animations after switching views
        animateOnScroll();
    }

    function resetSortState() {
        const sortDropdown = document.getElementById('sortDropdown');
        if (sortDropdown) {
            const sortSelectedText = sortDropdown.querySelector('.selected span');
            if (sortSelectedText) {
                sortSelectedText.textContent = "Sort by: Recent";
            }
        }
        
        // Reset to default sorting (by date, most recent first)
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');
        const currentView = recentView && recentView.style.display !== 'none' ? recentView : allView;
        
        if (currentView) {
            const projectItems = currentView.querySelectorAll('.project-item');
            const projectItemsArray = Array.from(projectItems);
            
            // Sort by most recent by default
            projectItemsArray.sort((a, b) => {
                const dateA = new Date(a.getAttribute('data-upload-date') || 0);
                const dateB = new Date(b.getAttribute('data-upload-date') || 0);
                return dateB - dateA;
            });
            
            // Re-insert items
            currentView.innerHTML = '';
            projectItemsArray.forEach(item => {
                currentView.appendChild(item);
            });
        }
    }

    function sortProjects(criteria) {
        console.log('Sorting by:', criteria);
        
        // Get the current active view (allView or recentView)
        const allView = document.getElementById('allView');
        const recentView = document.getElementById('recentView');
        const currentView = recentView && recentView.style.display !== 'none' ? recentView : allView;
        
        if (!currentView) {
            console.log('No current view found');
            return;
        }
        
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
                    const dateA = new Date(a.getAttribute('data-upload-date') || 0);
                    const dateB = new Date(b.getAttribute('data-upload-date') || 0);
                    return dateB - dateA;
                });
                break;
                
            case 'Oldest':
                // Oldest first (oldest dates first)
                projectItemsArray.sort((a, b) => {
                    const dateA = new Date(a.getAttribute('data-upload-date') || 0);
                    const dateB = new Date(b.getAttribute('data-upload-date') || 0);
                    return dateA - dateB;
                });
                break;
                
            case 'title':
                // Title A-Z
                projectItemsArray.sort((a, b) => {
                    const titleA = a.querySelector('h3')?.textContent.toLowerCase().trim() || '';
                    const titleB = b.querySelector('h3')?.textContent.toLowerCase().trim() || '';
                    return titleA.localeCompare(titleB);
                });
                break;
                
            case 'titleReversed':
                // Title Z-A
                projectItemsArray.sort((a, b) => {
                    const titleA = a.querySelector('h3')?.textContent.toLowerCase().trim() || '';
                    const titleB = b.querySelector('h3')?.textContent.toLowerCase().trim() || '';
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

    function filterProjectsByDepartment(departmentValue) {
        const projectItems = document.querySelectorAll('.project-item');
        const notFound = document.getElementById('notFound');
        let foundResults = false;
        
        console.log('Filtering by department:', departmentValue);
        
        projectItems.forEach(item => {
            // Get the course from the project item
            const courseElement = item.querySelector('.links p:nth-child(2)'); // Second paragraph in links div
            const course = courseElement ? courseElement.textContent.trim() : '';
            
            let shouldShow = false;
            
            if (departmentValue === 'all') {
                shouldShow = true;
            } else {
                // Get course codes for the selected department
                const courseCodes = getCourseCodesForDepartment(departmentValue);
                
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
        if (notFound) {
            if (foundResults || departmentValue === 'all') {
                notFound.style.display = 'none';
            } else {
                notFound.style.display = 'flex';
            }
        }
        
        // Re-run animations after filtering
        animateOnScroll();
    }

    function getCourseCodesForDepartment(departmentValue) {
        // Updated to match the new descriptive department values from PHP
        const departmentMap = {
            'beced': ['Bachelor of Early Childhood Education'],
            'bsed': ['Bachelor of Secondary Education'],
            'btvted': ['Bachelor of Technical-Vocational Teacher Education'],
            'beed': ['Bachelor of Elementary Education'],
            'bsned': ['Bachelor of Special Needs Education'],
            'bsabe': ['Bachelor of Science in Agriculture and Biosystems Engineering'],
            'bsit': ['Bachelor of Science in Information Technology']
        };
        
        return departmentMap[departmentValue] || [];
    }

    function initializeDepartmentFilter() {
        const departmentDropdown = document.getElementById('departmentFilterDropdown');
        if (!departmentDropdown) {
            console.error('Department filter dropdown not found');
            return;
        }

        const selectedElement = departmentDropdown.querySelector('.selected');
        const options = departmentDropdown.querySelectorAll('.options > div');
        
        if (!selectedElement || options.length === 0) {
            console.error('Department dropdown elements not found');
            return;
        }
        
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
                const selectedSpan = selectedElement.querySelector('span');
                if (selectedSpan) {
                    selectedSpan.textContent = text;
                }
                
                // Close dropdown
                departmentDropdown.classList.remove('active');
                
                // Filter theses based on department
                filterProjectsByDepartment(value);
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!departmentDropdown.contains(e.target)) {
                departmentDropdown.classList.remove('active');
            }
        });
    }

    function initializeFilterDropdowns() {
        console.log('Initializing filter dropdowns...');
        
        // Department Filter Dropdown
        initializeDepartmentFilter();
        
        // Sort Dropdown
        const sortDropdown = document.getElementById('sortDropdown');
        if (sortDropdown) {
            const sortSelectedText = sortDropdown.querySelector('.selected span');
            const sortOptions = sortDropdown.querySelectorAll('.options div');
            
            if (sortSelectedText && sortOptions.length > 0) {
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
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const departmentFilterDropdown = document.getElementById('departmentFilterDropdown');
            const sortDropdown = document.getElementById('sortDropdown');
            
            if (departmentFilterDropdown && !departmentFilterDropdown.contains(e.target)) {
                departmentFilterDropdown.classList.remove('active');
            }
            if (sortDropdown && !sortDropdown.contains(e.target)) {
                sortDropdown.classList.remove('active');
            }
        });
    }

    
    function animateOnScroll() {
        const projectItems = document.querySelectorAll('.project-item');
        
        // Remove any existing animation classes but preserve display state
        projectItems.forEach(item => {
            // Only remove animation classes, don't touch display property
            item.classList.remove('animate__animated', 'animate__fadeInUp', 'animate__fast');
        });
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Only animate if the element is visible (not filtered out)
                    if (window.getComputedStyle(entry.target).display !== 'none') {
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp', 'animate__fast');
                        observer.unobserve(entry.target);
                    }
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        // Observe all project items
        projectItems.forEach(item => {
            observer.observe(item);
        });
    }

    let uploadedFiles = [];
    let currentPdfDoc = null;
    let currentPageNum = 1;
    let pdfPageRendering = false;
    let pdfPageNumPending = null;
    
    // Open modal when FAB is clicked
    if (fabIcon) {
        fabIcon.addEventListener('click', function() {
            uploadModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal functions
    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Close all modals
    modalClose.forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            closeModal(modal);
        });
    });
    
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            closeModal(uploadModal);
        });
    }
    
    if (closePreview) {
        closePreview.addEventListener('click', function() {
            closeModal(previewModal);
        });
    }
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
    
    // File input handling via browse button
    if (browseBtn) {
        browseBtn.addEventListener('click', function() {
            fileInput.click();
        });
    }
    
    // File input change event
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });
    }
    
    // Drag and drop functionality
    if (dropArea) {
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
            handleFiles(files);
        });
    }
    
    // Handle the selected files
    function handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Check if file type is supported
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!['docx', 'pdf', 'zip'].includes(fileExtension)) {
                Swal.fire({
                    title: 'Unsupported File Type',
                    text: 'Please upload only docx, pdf, or zip files.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                continue;
            }
            
            // Check if file is already in the list
            if (uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                continue;
            }
            
            uploadedFiles.push(file);
            displayFile(file);
        }
        
        // Enable upload button if there are files
        btnUpload.disabled = uploadedFiles.length === 0;
        
        // Remove empty state if files are added
        if (uploadedFiles.length > 0) {
            const emptyState = fileList.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }
        }
    }
    
    // Display file in the list with preview
    function displayFile(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item-card animate__animated animate__fadeInUp';
        
        // Get appropriate icon based on file type
        let fileIconClass = 'file-icon-preview generic';
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (fileExtension === 'pdf') {
            fileIconClass = 'file-icon-preview pdf';
        } else if (fileExtension === 'docx') {
            fileIconClass = 'file-icon-preview word';
        } else if (fileExtension === 'zip') {
            fileIconClass = 'file-icon-preview zip';
        }
        
        // Format file size
        const fileSize = formatFileSize(file.size);
        
        fileItem.innerHTML = `
            <div class="${fileIconClass}">
                <i class="far fa-file-${fileExtension === 'docx' ? 'word' : fileExtension}"></i>
            </div>
            <div class="file-info-preview">
                <div class="file-name-preview">${file.name}</div>
                <div class="file-size-preview">${fileSize}</div>
            </div>
            <div class="file-actions-preview">
                <button class="file-action-btn-preview file-download-preview" data-filename="${file.name}">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="file-action-btn-preview file-remove-preview" data-filename="${file.name}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        fileList.appendChild(fileItem);
        
        // Add event listener to remove button
        const removeBtn = fileItem.querySelector('.file-remove-preview');
        removeBtn.addEventListener('click', function() {
            const fileName = this.getAttribute('data-filename');
            removeFile(fileName);
            fileItem.classList.add('animate__fadeOut');
            setTimeout(() => {
                fileItem.remove();
                if (uploadedFiles.length === 0) {
                    showEmptyState();
                }
            }, 500);
        });
        
        // Add event listener to preview button
        const previewBtn = fileItem.querySelector('.file-download-preview');
        previewBtn.addEventListener('click', function() {
            const fileName = this.getAttribute('data-filename');
            previewFile(fileName);
        });
    }

    // Show empty state when no files
    function showEmptyState() {
        fileList.innerHTML = `
            <div class="empty-state">
                <i class="far fa-folder-open"></i>
                <p>No files selected</p>
            </div>
        `;
    }
    
    document.addEventListener('click', function(e) {
        const projectItem = e.target.closest('.project-item');
        if (projectItem && !e.target.closest('.project-item .logo-row .icon') && !e.target.closest('.moreOptions')) {
            handleProjectItemClick(projectItem);
        }
    });

    function handleProjectItemClick(projectItem) {
        if (!projectItem) return;
        
        const title = projectItem.querySelector('h3')?.textContent || 'No title';
        const uploadedDate = projectItem.querySelector('.links p')?.textContent || 'Unknown date';
        const authors = projectItem.querySelector('.desc-row p')?.textContent || 'Unknown authors';
        
        // Get adviser information
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

    // VIEW ABSTRACT
    function showProjectPreview(thesisId, title, uploadedDate, authors, adviser) {
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
        if (pdfFooterControls) {
            pdfFooterControls.style.display = 'none';
        }
        
        // Set download link
        const downloadLink = document.getElementById('download-link');
        
        if (thesisId) {
            // Fetch from database via controller
            fetchThesisFile(thesisId, title);
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
        if (previewModal) {
            previewModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    // FOR ABSTRACT
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
            
            // Fetch the ABSTRACT file
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
    
    
    // Preview file using Google Docs Viewer for docx and PDF.js for pdf
    function previewFile(fileName) {
        const file = uploadedFiles.find(f => f.name === fileName);
        if (!file) return;
        
        // Reset viewer states
        docViewerIframe.style.display = 'none';
        pdfViewer.style.display = 'none';
        unsupportedFile.style.display = 'none';
        
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const fileUrl = URL.createObjectURL(file);
        
        // Store the blob URL for cleanup
        window.currentPdfBlobUrl = fileUrl;
        
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
            
            // Show footer controls if they exist
            const pdfFooterControls = document.getElementById('pdf-footer-controls');
            if (pdfFooterControls) {
                pdfFooterControls.style.display = 'flex';
                // Update total pages
                document.getElementById('pdf-total-pages').textContent = pdfDoc.numPages;
            }
            
            // Render the first page
            renderPage(window.currentPageNum);
            
            // Add PDF controls to footer if available
            if (pdfFooterControls) {
                addPdfFooterControls(pdfDoc);
            }
            
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            showPdfError(`Failed to load PDF: ${error.message}`);
        });
    }
    
    
    function addPdfFooterControls(pdfDoc) {
        // Remove any existing event listeners first
        const prevBtn = document.getElementById('prev-page-footer');
        const nextBtn = document.getElementById('next-page-footer');
        
        if (prevBtn && nextBtn) {
            // Add event listeners to footer controls
            prevBtn.addEventListener('click', function() {
                if (window.currentPageNum <= 1) return;
                window.currentPageNum--;
                renderPage(window.currentPageNum);
                updatePdfFooterControls();
            });
            
            nextBtn.addEventListener('click', function() {
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

    function closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Clean up PDF resources if it's the preview modal
        if (modal.id === 'previewModal') {
            if (window.currentPdfBlobUrl) {
                URL.revokeObjectURL(window.currentPdfBlobUrl);
                window.currentPdfBlobUrl = null;
            }
            
            // Reset PDF state
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

    // Function to show profile and hide projects
    function showProfile() {
        profileContainer.style.display = 'block';
        // Hide all project views
        allView.style.display = 'none';
        recentView.style.display = 'none';
        appContentHeader.style.display = 'none';
    
        // Update active states - ensure only profile icon is selected
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        // Get the parent li of the profile icon
        profileSidebarIcon.closest('li').classList.add('selected');
    }
    

    // Function to hide profile and show projects
    function hideProfile() {
        profileContainer.style.display = 'none';
        appContentHeader.style.display = 'flex';
        
        // Show the appropriate view based on which button is selected
        if (allButton.classList.contains('selected')) {
            allView.style.display = 'grid';
        } else {
            recentView.style.display = 'grid';
        }
    
        // Reset active states - select the dashboard icon
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        document.querySelector('.menu-options li:first-child').classList.add('selected');
    }

    // Add click event to profile icon
    if (profileSidebarIcon) {
        profileSidebarIcon.addEventListener('click', function() {
            showProfile();
        });
    }

    // Add click event to other sidebar icons to hide profile
    document.querySelectorAll('.menu-options li:not(#profileSidebarIcon)').forEach(item => {
        item.addEventListener('click', function() {
            hideProfile();
        });
    });

    // Also hide profile when clicking on header menu items
    document.querySelectorAll('.header .menu button').forEach(button => {
        button.addEventListener('click', function() {
            hideProfile();
            
            // Update button selection state
            document.querySelectorAll('.header .menu button').forEach(btn => {
                btn.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });

   
    // Add click event to profile icons
    if (profileHeaderIcon) {
        profileHeaderIcon.addEventListener('click', showProfile);
    }
    
    

    

    

    // NEW: Filter dropdown functionality
    const sortDropdown = document.getElementById('sortDropdown');

    

    

    
   

    

    // Search functionality
    function initializeSearch() {
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
                    
                    // Remove animation classes during search
                    item.classList.remove('animate__animated', 'animate__fadeInUp', 'animate__fast');
                });
                
                // Show/hide the "No Results Found" message based on whether we found any results
                if (foundResults || searchTerm === '') {
                    notFound.style.display = 'none';
                } else {
                    notFound.style.display = 'flex';
                }
                
                // Re-run animations after searching - with a small delay
                setTimeout(() => {
                    animateOnScroll();
                }, 50);
            });
        }
    }

// Display toggle functionality
function initializeDisplayToggle() {
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
}

    
    // Call the animation function
    animateOnScroll();

    
});

