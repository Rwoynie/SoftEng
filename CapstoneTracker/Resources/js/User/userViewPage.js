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
    
    // Format file size to human readable format
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Remove file from the list
    function removeFile(fileName) {
        uploadedFiles = uploadedFiles.filter(file => file.name !== fileName);
        btnUpload.disabled = uploadedFiles.length === 0;
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
        // Load PDF document
        pdfjsLib = pdfjsLib || window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
        
        pdfjsLib.getDocument(url).promise.then(function(pdfDoc) {
            currentPdfDoc = pdfDoc;
            currentPageNum = 1;
            
            // Render the first page
            renderPage(currentPageNum);
            
            // Add PDF controls
            addPdfControls(pdfDoc);
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            // Fallback to iframe if PDF.js fails
            const previewUrl = `https://docs.google.com/gview?url=${encodeURIComponent(url)}&embedded=true`;
            docViewerIframe.src = previewUrl;
            docViewerIframe.style.display = 'block';
            pdfViewer.style.display = 'none';
        });
    }
    
    function renderPage(pageNum) {
        pdfPageRendering = true;
        
        currentPdfDoc.getPage(pageNum).then(function(page) {
            const scale = 1.5;
            const viewport = page.getViewport({ scale });
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            // Clear previous content
            pdfViewer.innerHTML = '';
            pdfViewer.appendChild(canvas);
            
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            const renderTask = page.render(renderContext);
            
            renderTask.promise.then(function() {
                pdfPageRendering = false;
                
                if (pdfPageNumPending !== null) {
                    renderPage(pdfPageNumPending);
                    pdfPageNumPending = null;
                }
                
                // Update page info
                document.getElementById('pdf-page-num').textContent = pageNum;
            });
        });
    }
    
    function queueRenderPage(pageNum) {
        if (pdfPageRendering) {
            pdfPageNumPending = pageNum;
        } else {
            renderPage(pageNum);
        }
    }
    
    function addPdfControls(pdfDoc) {
        const controlsHtml = `
            <div class="pdf-controls">
                <button id="prev-page" ${currentPageNum <= 1 ? 'disabled' : ''}>Previous</button>
                <span class="pdf-page-info">Page <span id="pdf-page-num">${currentPageNum}</span> of ${pdfDoc.numPages}</span>
                <button id="next-page" ${currentPageNum >= pdfDoc.numPages ? 'disabled' : ''}>Next</button>
            </div>
        `;
        
        pdfViewer.insertAdjacentHTML('afterbegin', controlsHtml);
        
        document.getElementById('prev-page').addEventListener('click', function() {
            if (currentPageNum <= 1) return;
            currentPageNum--;
            queueRenderPage(currentPageNum);
            updatePdfControls(pdfDoc);
        });
        
        document.getElementById('next-page').addEventListener('click', function() {
            if (currentPageNum >= pdfDoc.numPages) return;
            currentPageNum++;
            queueRenderPage(currentPageNum);
            updatePdfControls(pdfDoc);
        });
    }
    
    function updatePdfControls(pdfDoc) {
        document.getElementById('prev-page').disabled = currentPageNum <= 1;
        document.getElementById('next-page').disabled = currentPageNum >= pdfDoc.numPages;
        document.getElementById('pdf-page-num').textContent = currentPageNum;
    }

    // Upload button functionality
    if (btnUpload) {
        btnUpload.addEventListener('click', function() {
            if (uploadedFiles.length === 0) return;
            
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
            
            // Create FormData object to send files
            const formData = new FormData();
            
            // Add thesis title to form data
            if (thesisTitleInput) {
                formData.append('thesisTitle', thesisTitleInput.value.trim());
            }
            
            // Add author to form data if exists
            const thesisAuthorInput = document.getElementById('thesisAuthor');
            if (thesisAuthorInput && thesisAuthorInput.value.trim()) {
                formData.append('thesisAuthor', thesisAuthorInput.value.trim());
            }
            
            for (let i = 0; i < uploadedFiles.length; i++) {
                formData.append('files[]', uploadedFiles[i]);
            }
            
            // Show loading state
            const originalText = btnUpload.innerHTML;
            btnUpload.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            btnUpload.disabled = true;
            
            // Show SweetAlert for upload confirmation
            Swal.fire({
                title: 'Confirm Upload',
                html: `Are you sure you want to upload <strong>${thesisTitleInput.value}</strong> with ${uploadedFiles.length} file(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, upload it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulate upload process (replace with actual AJAX call)
                    setTimeout(() => {
                        // Success message
                        Swal.fire({
                            title: 'Upload Successful!',
                            text: 'Your thesis has been uploaded successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reset the form
                            uploadedFiles = [];
                            showEmptyState();
                            btnUpload.disabled = true;
                            btnUpload.innerHTML = 'Upload';
                            fileInput.value = '';
                            
                            // Clear form fields
                            if (thesisTitleInput) thesisTitleInput.value = '';
                            if (thesisAuthorInput) thesisAuthorInput.value = '';
                            
                            // Close the modal
                            closeModal(uploadModal);
                        });
                    }, 2000);
                } else {
                    // Reset button state if cancelled
                    btnUpload.innerHTML = originalText;
                    btnUpload.disabled = false;
                }
            });
        });
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

