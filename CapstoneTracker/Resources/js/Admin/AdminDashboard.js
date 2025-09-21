document.addEventListener('DOMContentLoaded', function() {
    // Profile functionality (existing code)
    
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
    
    const closePreview = document.getElementById('closePreview');
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

    let changesMade = false;
    let roleChanges = {};
    

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
    
    // Initialize sidebar functionality
    function initializeSidebar() {
        const sidebarOptions = document.querySelectorAll('.menu-options li');
        const contentContainers = {
            'dashboard': document.querySelector('.projects-container'),
            'users': document.getElementById('access-container'),
            'accounts': document.getElementById('accounts-container'),
            'logs': document.getElementById('logs-container')
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
            const viewIds = ['dashboard', 'users', 'accounts', 'logs'];
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

        // Update button states
        menuButtons.forEach(button => button.classList.remove('selected'));
        buttonToSelect.classList.add('selected');

        //button states for log buttons
        logMenuButtons.forEach(button => button.classList.remove('selected'));
        buttonToSelect.classList.add('selected');
        
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

    const projectItems = document.querySelectorAll('.project-item');
    projectItems.forEach(item => {
        item.addEventListener('click', function() {
            handleProjectItemClick(this);
        });
    });

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
        if (departmentFilterDropdown) {
            const deptSelectedText = departmentFilterDropdown.querySelector('.selected span');
            const deptOptions = departmentFilterDropdown.querySelectorAll('.options div');
            
            // Toggle dropdown on click
            departmentFilterDropdown.querySelector('.selected').addEventListener('click', function(e) {
                e.stopPropagation();
                departmentFilterDropdown.classList.toggle('active');
            });
            
            // Handle option selection
            deptOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    deptSelectedText.textContent = this.textContent;
                    departmentFilterDropdown.classList.remove('active');
                    
                    // Filter projects based on selected department
                    filterProjectsByDepartment(value);
                });
            });
        }
        
        // Sort Dropdown
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
                    sortSelectedText.textContent = "Sort by: " + this.textContent;
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

    function sortProjects(criteria) {
        const projectsContainer = document.querySelector('.projects');
        const projectItems = Array.from(document.querySelectorAll('.project-item'));
        
        // Sort based on criteria
        switch(criteria) {
            case 'recent':
                // Assuming you have a data-upload-date attribute with timestamp
                projectItems.sort((a, b) => {
                    return new Date(b.getAttribute('data-upload-date')) - new Date(a.getAttribute('data-upload-date'));
                });
                break;
            case 'popular':
                // Assuming you have a data-views attribute
                projectItems.sort((a, b) => {
                    return parseInt(b.getAttribute('data-views')) - parseInt(a.getAttribute('data-views'));
                });
                break;
            case 'title':
                projectItems.sort((a, b) => {
                    const titleA = a.querySelector('h3').textContent.toLowerCase();
                    const titleB = b.querySelector('h3').textContent.toLowerCase();
                    return titleA.localeCompare(titleB);
                });
                break;
            case 'department':
                projectItems.sort((a, b) => {
                    const deptA = a.getAttribute('data-department');
                    const deptB = b.getAttribute('data-department');
                    return deptA.localeCompare(deptB);
                });
                break;
        }
        
        // Clear the container and append sorted items
        projectsContainer.innerHTML = '';
        projectItems.forEach(item => {
            projectsContainer.appendChild(item);
        });
        
        // Re-run animations after sorting
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
    if (recentView && allView && recentButton) {
        switchView(recentView, recentButton);
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

    // Admin Access Management Search Functionality - FIXED
    const adminUserSearch = document.getElementById('adminUserSearch');
    if (adminUserSearch) {
        adminUserSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const userItems = document.querySelectorAll('.admin-user-item');
            const notFound = document.getElementById('adminNotFound');
            
            let foundResults = false;
            
            userItems.forEach(item => {
                const userName = item.querySelector('h4').textContent.toLowerCase();
                
                // Only search by name now (removed email search)
                if (userName.includes(searchTerm)) {
                    item.style.display = 'flex';
                    foundResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide the "No Results Found" message
            if (foundResults || searchTerm === '') {
                notFound.style.display = 'none';
            } else {
                notFound.style.display = 'block';
            }
        });
    }

    // Role Change Handling
    function initializeRoleChangeHandling() {
        const roleCheckboxes = document.querySelectorAll('.role-checkbox input');
        
        roleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const userId = this.getAttribute('data-user-id');
                const role = this.getAttribute('name');
                const isChecked = this.checked;
                
                // Initialize user in roleChanges if not exists
                if (!roleChanges[userId]) {
                    roleChanges[userId] = {};
                }
                
                // Track this change
                roleChanges[userId][role] = isChecked;
                changesMade = true;
                
                // If a role is selected, uncheck others in the same group
                if (isChecked) {
                    const userItem = this.closest('.admin-user-item');
                    const otherCheckboxes = userItem.querySelectorAll(`.role-checkbox input:not([name="${role}"])`);
                    
                    otherCheckboxes.forEach(otherCheckbox => {
                        otherCheckbox.checked = false;
                        
                        // Also track these changes
                        const otherRole = otherCheckbox.getAttribute('name');
                        if (!roleChanges[userId]) {
                            roleChanges[userId] = {};
                        }
                        roleChanges[userId][otherRole] = false;
                    });
                }
            });
        });
        
        // Initialize the checkboxes
        initializeRoleCheckboxes();
    }

    // Initialize role checkboxes to ensure only one is checked per user
    function initializeRoleCheckboxes() {
        const userItems = document.querySelectorAll('.admin-user-item');
        
        userItems.forEach(userItem => {
            const checkboxes = userItem.querySelectorAll('.role-checkbox input');
            let checkedCount = 0;
            
            // Count how many are checked
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    checkedCount++;
                }
            });
            
            // If more than one is checked, keep only the first one
            if (checkedCount > 1) {
                let firstChecked = true;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        if (firstChecked) {
                            firstChecked = false;
                        } else {
                            checkbox.checked = false;
                        }
                    }
                });
            }
            
            // If none are checked, check student by default
            if (checkedCount === 0) {
                const studentCheckbox = userItem.querySelector('.role-checkbox input[name="student"]');
                if (studentCheckbox) {
                    studentCheckbox.checked = true;
                }
            }
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
        if (allAccountsContainer) allAccountsContainer.style.display = 'none';
        if (pendingAccountsContainer) pendingAccountsContainer.style.display = 'none';
        if (approvedAccountsContainer) approvedAccountsContainer.style.display = 'none';
        
        // Remove active class from all buttons
        const accountButtons = document.querySelectorAll('.logMenu button');
        accountButtons.forEach(button => button.classList.remove('selected'));
        
        // Show selected account view and activate button
        if (viewToShow && buttonToSelect) {
            viewToShow.style.display = 'block';
            buttonToSelect.classList.add('selected');
        }
    }

    // Event listeners for account filter buttons
    if (allAccountsButton && pendingButton && approvedButton) {
        allAccountsButton.addEventListener('click', function() {
            switchAccountView(allAccountsContainer, allAccountsButton);
        });
        
        pendingButton.addEventListener('click', function() {
            switchAccountView(pendingAccountsContainer, pendingButton);
        });
        
        approvedButton.addEventListener('click', function() {
            switchAccountView(approvedAccountsContainer, approvedButton);
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
            
            accountRows.forEach(row => {
                const name = row.querySelector('td:first-child').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    

});

//Thesis view abstract
function handleProjectItemClick(projectItem) {
    const title = projectItem.querySelector('h3').textContent;
    const uploadedDate = projectItem.querySelector('.links p').textContent;
    const authors = projectItem.querySelector('.desc-row p').textContent;
    const fileUrl = projectItem.getAttribute('data-file-url'); // Get the file URL
    
    showProjectPreview(title, uploadedDate, authors, fileUrl);
}

function showProjectPreview(title, uploadedDate, authors, fileUrl) {
    // Update modal content with project details
    const modalTitle = document.querySelector('.preview-modal .modal-title');
    modalTitle.textContent = title;
    
    // Create a container for project info
    const projectInfo = document.createElement('div');
    projectInfo.className = 'project-info-preview';
    projectInfo.innerHTML = `
        <div class="project-detail">
            <strong>Uploaded:</strong> ${uploadedDate}
        </div>
        <div class="project-detail">
            <strong>Authors:</strong> ${authors}
        </div>
    `;
    
    // Set up the document preview
    const fileExtension = fileUrl.split('.').pop().toLowerCase();
    const fileUrlEncoded = encodeURIComponent(fileUrl);
    
    // Reset viewer states
    const docViewerIframe = document.getElementById('doc-viewer-iframe');
    const pdfViewer = document.getElementById('pdf-viewer');
    const unsupportedFile = document.getElementById('unsupported-file');
    
    docViewerIframe.style.display = 'none';
    pdfViewer.style.display = 'none';
    unsupportedFile.style.display = 'none';
    
    // Set download link
    const downloadLink = document.getElementById('download-link');
    downloadLink.href = fileUrl;
    downloadLink.download = title;
    
    if (fileExtension === 'pdf') {
        // Use PDF.js for PDF preview
        previewPdf(fileUrl);
        pdfViewer.style.display = 'block';
    } else {
        // Show unsupported message for other file types
        unsupportedFile.style.display = 'block';
    }
    
    // Show preview modal
    const previewModal = document.getElementById('previewModal');
    previewModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// PDF.js functions for PDF preview
function previewPdf(url) {
    // Load PDF document
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    
    pdfjsLib.getDocument(url).promise.then(function(pdfDoc) {
        const pdfViewer = document.getElementById('pdf-viewer');
        let currentPageNum = 1;
        
        // Render the first page
        renderPage(pdfDoc, currentPageNum);
        
        // Add PDF controls
        addPdfControls(pdfDoc, currentPageNum);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        // Fallback to iframe if PDF.js fails
        const docViewerIframe = document.getElementById('doc-viewer-iframe');
        const pdfViewer = document.getElementById('pdf-viewer');
        const previewUrl = `https://docs.google.com/gview?url=${encodeURIComponent(url)}&embedded=true`;
        docViewerIframe.src = previewUrl;
        docViewerIframe.style.display = 'block';
        pdfViewer.style.display = 'none';
    });
}

function renderPage(pdfDoc, pageNum) {
    const pdfViewer = document.getElementById('pdf-viewer');
    
    pdfDoc.getPage(pageNum).then(function(page) {
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
            // Update page info
            document.getElementById('pdf-page-num').textContent = pageNum;
        });
    });
}

function addPdfControls(pdfDoc, currentPageNum) {
    const pdfViewer = document.getElementById('pdf-viewer');
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
        renderPage(pdfDoc, currentPageNum);
        updatePdfControls(pdfDoc, currentPageNum);
    });
    
    document.getElementById('next-page').addEventListener('click', function() {
        if (currentPageNum >= pdfDoc.numPages) return;
        currentPageNum++;
        renderPage(pdfDoc, currentPageNum);
        updatePdfControls(pdfDoc, currentPageNum);
    });
}

function updatePdfControls(pdfDoc, currentPageNum) {
    document.getElementById('prev-page').disabled = currentPageNum <= 1;
    document.getElementById('next-page').disabled = currentPageNum >= pdfDoc.numPages;
    document.getElementById('pdf-page-num').textContent = currentPageNum;
}





