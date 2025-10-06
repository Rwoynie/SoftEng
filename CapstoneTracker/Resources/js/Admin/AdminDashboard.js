

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

    // Function to filter users by role
    function filterUsersByRole(role) {
        const userItems = document.querySelectorAll('.admin-user-item');
        let foundResults = false;
        
        userItems.forEach(item => {
            const roleCheckbox = item.querySelector(`.role-checkbox input[name="${role}"]`);
            if (roleCheckbox && roleCheckbox.checked) {
                item.style.display = 'flex';
                foundResults = true;
            } else {
                item.style.display = 'none';
            }
        });
        
        const notFound = document.getElementById('adminNotFound');
        if (foundResults) {
            notFound.style.display = 'none';
        } else {
            notFound.style.display = 'block';
        }
    }


    // Event listeners for access cards
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

    
    

    // Admin Access Management Search Functionality - FIXED
    const adminUserSearch = document.getElementById('adminUserSearch');
    if (adminUserSearch) {
        // Store the original event listener function
        const originalSearchHandler = adminUserSearch.oninput;
        
        // Replace with enhanced search that respects filters
        adminUserSearch.oninput = function() {
            const searchTerm = this.value.toLowerCase().trim();
            const userItems = document.querySelectorAll('.admin-user-item');
            const notFound = document.getElementById('adminNotFound');
            const currentFilter = this.getAttribute('data-current-filter');
            
            let foundResults = false;
            
            userItems.forEach(item => {
                const userName = item.querySelector('h4').textContent.toLowerCase();
                const userEmail = item.querySelector('p').textContent.toLowerCase();
                
                // Check if item matches search term
                const matchesSearch = userName.includes(searchTerm) || userEmail.includes(searchTerm);
                
                // Check if item matches current filter (if any)
                let matchesFilter = true;
                if (currentFilter) {
                    const roleCheckbox = item.querySelector(`.role-checkbox input[name="${currentFilter}"]`);
                    matchesFilter = roleCheckbox && roleCheckbox.checked;
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
                notFound.style.display = 'none';
            } else {
                notFound.style.display = 'block';
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

    // NEED FIXING----------------------------------------------------------------------------
    // Enhanced updateUserRole function
    async function updateUserRole(userId, newRole) {
        try {
            const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=updateUserRole', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    role: newRole
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                return result;
            } else {
                throw new Error(result.error || 'Failed to update user role');
            }
        } catch (error) {
            console.error('Error updating user role:', error);
            throw error;
        }
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
                // Check title availability in real-time (debounced)
                clearTimeout(window.titleCheckTimeout);
                window.titleCheckTimeout = setTimeout(() => {
                    checkTitleExists(thesisTitle.value.trim()).then(exists => {
                        if (exists) {
                            thesisTitle.style.borderColor = 'var(--color-danger)';
                            // Show warning tooltip or message
                            showTitleWarning('This title already exists');
                        } else {
                            thesisTitle.style.borderColor = '#51cf66';
                            hideTitleWarning();
                        }
                    });
                }, 500);
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
        
        // Add visual feedback for department
        if (departmentSelect) {
            if (!isDepartmentSelected) {
                departmentSelect.style.borderColor = 'rgb(221, 221, 221)';
            } else {
                departmentSelect.style.borderColor = '#51cf66';
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
                body: JSON.stringify({ emails: emails })
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
            'courseInput'
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
                
                if (uploadedFiles.length === 0) {
                    Swal.fire({
                        title: 'No Files Selected',
                        text: 'Please select at least one file to upload.',
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

    function resetUploadForm() {
        uploadedFiles = [];
        showEmptyState();
        updateUploadButtonState();
        
        // Clear both file inputs
        if (abstractFileInput) abstractFileInput.value = '';
        if (thesisFileInput) thesisFileInput.value = '';
        
        // Clear all form fields
        const formFields = [
            'thesisTitle',
            'thesisAuthor',
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
    }

    if (uploadModal) {
        uploadModal.addEventListener('click', function(e) {
            if (e.target === uploadModal || e.target.classList.contains('modal-close') || e.target.classList.contains('btn-cancel')) {
                initializeUploadFormValidation();
            }
        });
    }

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
    

});

let changesMade = false;
    let roleChanges = {};

    

    

// Update the user item creation function to include proper event listeners
function createUserItem(user) {
    const userItem = document.createElement('div');
    userItem.className = 'access-item admin-user-item';
    userItem.setAttribute('data-user-id', user.ID);
    
    const isAdmin = user.User_Role === 'admin' || user.User_Role === 'superAdmin';
    const isFaculty = user.User_Role === 'faculty';
    const isStudent = user.User_Role === 'student';
    
    userItem.innerHTML = `
        <div class="access-info">
            <h4>${user.First_Name} ${user.Middle_Name || ''} ${user.Last_Name} ${user.Extension || ''}</h4>
            <p>${user.Email} • ${user.Department || 'No Department'} • Status: ${user.Acc_Status}</p>
        </div>
        <div class="access-roles">
            <label class="role-checkbox">
                <input class="checkbox" type="checkbox" name="admin" data-user-id="${user.ID}" ${isAdmin ? 'checked' : ''}>
                <span class="checkmark"></span>
                Admin
            </label>
            <label class="role-checkbox">
                <input class="checkbox" type="checkbox" name="faculty" data-user-id="${user.ID}" ${isFaculty ? 'checked' : ''}>
                <span class="checkmark"></span>
                Faculty
            </label>
            <label class="role-checkbox">
                <input class="checkbox" type="checkbox" name="student" data-user-id="${user.ID}" ${isStudent ? 'checked' : ''}>
                <span class="checkmark"></span>
                Student
            </label>
        </div>
    `;
    
    // Add event listeners to the checkboxes
    const checkboxes = userItem.querySelectorAll('.role-checkbox input');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const role = this.getAttribute('name');
            const isChecked = this.checked;
            
            trackRoleChange(userId, role, isChecked);
        });
    });
    
    return userItem;
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
            <div class="project-detail">
                <strong>Uploaded:</strong> ${uploadedDate}
            </div>
            <div class="project-detail">
                <strong>Authors:</strong> ${authors}
            </div>
            <div class="project-detail">
                <strong>Adviser:</strong> ${adviser}
            </div>
            ${thesisId ? `<div class="project-detail">
                <strong>Thesis ID:</strong> ${thesisId}
            </div>` : ''}
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
        const response = await fetch('../../../app/Controllers/AdminDashboardController.php?action=getUsers');
        const data = await response.json();
        
        if (data.users && data.users.length > 0) {
            displayUsersInAccessManagement(data.users);
            updateUserCounts(data.role_counts);
        }
    } catch (error) {
        console.error('Error fetching users:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to load user data',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
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
    userItem.setAttribute('data-user-id', user.ID);
    
    const isAdmin = user.User_Role === 'admin' || user.User_Role === 'superAdmin';
    const isFaculty = user.User_Role === 'faculty';
    const isStudent = user.User_Role === 'student';
    
    userItem.innerHTML = `
                <div class="access-info">
            <h4>${user.First_Name} ${user.Middle_Name || ''} ${user.Last_Name} ${user.Extension || ''}</h4>
            <p>${user.Email} • ${user.Department || 'No Department'} • Status: ${user.Acc_Status}</p>
                </div>
        <div class="access-roles">
            <label class="role-checkbox">
                <input class="checkbox" type="checkbox" name="admin" data-user-id="${user.ID}" ${isAdmin ? 'checked' : ''}> Admin
            </label>
            <label class="role-checkbox">
                <input class="checkbox" type="checkbox" name="faculty" data-user-id="${user.ID}" ${isFaculty ? 'checked' : ''}> Faculty
            </label>
            <label class="role-checkbox">
                <input class="checkbox" type="checkbox" name="student" data-user-id="${user.ID}" ${isStudent ? 'checked' : ''}> Student
                    </label>
                </div>
            `;
    
    return userItem;
}

// Function to update user counts in access cards
function updateUserCounts(roleCounts) {
    const counts = {
        'admin': 0,
        'faculty': 0,
        'student': 0
    };
    
    // Convert role counts to expected format
    roleCounts.forEach(roleCount => {
        if (roleCount.User_Role === 'admin' || roleCount.User_Role === 'superAdmin') {
            counts.admin = roleCount.count;
        } else if (roleCount.User_Role === 'faculty') {
            counts.faculty = roleCount.count;
        } else if (roleCount.User_Role === 'student') {
            counts.student = roleCount.count;
        }
    });
    
    // Update the access cards
    const adminCountElement = document.querySelector('#adminAccess .access-count');
    const facultyCountElement = document.querySelector('#facultyAccess .access-count');
    const studentCountElement = document.querySelector('#studentAccess .access-count');
    
    if (adminCountElement) adminCountElement.textContent = `${counts.admin} users`;
    if (facultyCountElement) facultyCountElement.textContent = `${counts.faculty} users`;
    if (studentCountElement) studentCountElement.textContent = `${counts.student} users`;
}





