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
            const viewIds = ['dashboard', 'users', 'logs'];
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

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const projectItems = document.querySelectorAll('.project-item');
        const notFound = document.getElementById('notFound');
        
        let foundResults = false;
        
        projectItems.forEach(item => {
            const title = item.querySelector('h3').textContent.toLowerCase();
            const description = item.querySelector('.desc-row p').textContent.toLowerCase();
            const tags = item.getAttribute('data-tags').toLowerCase();
            
            if (title.includes(searchTerm) || description.includes(searchTerm) || tags.includes(searchTerm)) {
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



/* Admin Access Management Script */
function initializeAdminAccessFunctionality() {
    // Sample user data
    const users = [
        { id: 1, name: "John Smith", email: "john.smith@example.com", lastActive: "2 hours ago" },
        { id: 2, name: "Emma Johnson", email: "emma.j@example.com", lastActive: "1 day ago" },
        { id: 3, name: "Michael Brown", email: "m.brown@example.com", lastActive: "5 minutes ago" },
        { id: 4, name: "Sarah Davis", email: "sarah.d@example.com", lastActive: "3 days ago" },
        { id: 5, name: "Robert Wilson", email: "robert.w@example.com", lastActive: "1 week ago" },
        { id: 6, name: "Jennifer Miller", email: "jennifer.m@example.com", lastActive: "12 hours ago" },
        { id: 7, name: "David Taylor", email: "david.t@example.com", lastActive: "2 days ago" },
        { id: 8, name: "Lisa Anderson", email: "lisa.a@example.com", lastActive: "Just now" }
    ];

    // Get elements from the loaded content
    const adminUserList = document.getElementById('adminUserList');
    const adminUserSearch = document.getElementById('adminUserSearch');
    const saveAdminChangesBtn = document.getElementById('saveAdminChangesBtn');
    const notFound = document.getElementById('notFound');
    const backButton = document.getElementById('backToRoles');

    // Track changes
    let changesMade = false;
    const adminStatusChanges = {};

    // Initialize the UI
    function renderAdminUsers(userArray) {
        if (!adminUserList) return;
        
        adminUserList.innerHTML = '';
        
        if (userArray.length === 0) {
            if (notFound) notFound.style.display = 'block';
            return;
        }
        
        if (notFound) notFound.style.display = 'none';
        
        userArray.forEach(user => {
            const userElement = document.createElement('div');
            userElement.className = 'access-item';
            userElement.innerHTML = `
                <div class="access-info">
                    <h4>${user.name}</h4>
                    <p>${user.email} • Last active: ${user.lastActive}</p>
                </div>
                <div class="access-count">
                    <label class="admin-toggle">
                        <input type="checkbox" ${user.isAdmin ? 'checked' : ''} data-user-id="${user.id}">
                        <span class="toggle-slider"></span>
                        
                    </label>
                </div>
            `;
            adminUserList.appendChild(userElement);
        });

        // Add event listeners to checkboxes
        document.querySelectorAll('.admin-toggle input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const userId = parseInt(this.dataset.userId);
                adminStatusChanges[userId] = this.checked;
                changesMade = true;
                
                
            });
        });
    }

    // Filter users based on search
    if (adminUserSearch) {
        adminUserSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const filteredUsers = users.filter(user => 
                user.name.toLowerCase().includes(searchTerm) || 
                user.email.toLowerCase().includes(searchTerm)
            );
            renderAdminUsers(filteredUsers);
        });
    }

    // Save changes with confirmation
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
                text: 'Are you sure you want to save these administrator privilege changes?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--primary-color)',
                cancelButtonColor: 'var(--color-lite-grey)',
                confirmButtonText: 'Yes, save changes!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Apply changes to user data
                    for (const [userId, isAdmin] of Object.entries(adminStatusChanges)) {
                        const user = users.find(u => u.id === parseInt(userId));
                        if (user) {
                            user.isAdmin = isAdmin;
                        }
                    }
                    
                    // Reset changes
                    changesMade = false;
                    Object.keys(adminStatusChanges).forEach(key => delete adminStatusChanges[key]);
                    
                    // Show success message
                    Swal.fire({
                        title: 'Saved!',
                        text: 'Admin privileges have been updated.',
                        icon: 'success',
                        confirmButtonColor: 'var(--primary-color)'
                    });
                    
                    // Refresh the view
                    renderAdminUsers(users);
                }
            });
        });
    }

    // Back button functionality
    if (backButton) {
        backButton.addEventListener('click', function() {
            // Clear admin access content and show role selection
            const adminAccessContent = document.getElementById('admin-access-content');
            const accessCard = document.getElementById('access-card');
            const accessContainer = document.getElementById('access-container');
            const accessHeader2 = document.getElementById('accessHeader2');
            
            if (accessHeader2) accessHeader2.style.display = 'block';
            if (adminAccessContent) adminAccessContent.innerHTML = '';
            if (accessCard) accessCard.style.display = 'block';
            if (accessContainer) accessContainer.style.display = 'block';
        });
    }

    // Initial render
    renderAdminUsers(users);
}

function loadAdminAccessContent() {
    const adminAccessContent = document.getElementById('admin-access-content');
    const projectsContainer = document.querySelector('.projects-container');
    const accessContainer = document.getElementById('access-container');
    const accessCard = document.getElementById('access-card');
    const accessHeader2 = document.getElementById('accessHeader2');
    
    // Hide the projects container and show the access container
    if (projectsContainer) projectsContainer.style.display = 'none';
    if (accessContainer) accessContainer.style.display = 'block';
    if (accessCard) accessCard.style.display = 'none';
    if (accessHeader2) accessHeader2.style.display = 'none';
    
    // Load the admin access content
    fetch('adminAccess.php')
        .then(response => response.text())
        .then(data => {
            if (adminAccessContent) {
                adminAccessContent.innerHTML = data;
                initializeAdminAccessFunctionality(); 
            }
        })
        .catch(error => {
            console.error('Error loading admin access content:', error);
            if (adminAccessContent) {
                adminAccessContent.innerHTML = '<p>Error loading admin access content. Please try again.</p>';
            }
        });
}

// Initialize admin access functionality when the button is clicked
document.addEventListener('DOMContentLoaded', function() {
    const adminAccessBtn = document.getElementById('adminAccess');
    
    if (adminAccessBtn) {
        adminAccessBtn.addEventListener('click', function() {
            loadAdminAccessContent();
        });
    }
});
