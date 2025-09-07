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
    const modalClose = document.querySelector('.modal-close');
    const btnCancel = document.querySelector('.btn-cancel');
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const btnUpload = document.querySelector('.btn-upload');
    const browseBtn = document.querySelector('.browse-btn');

    let uploadedFiles = [];
    
    // Open modal when FAB is clicked
    if (fabIcon) {
        fabIcon.addEventListener('click', function() {
            uploadModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        });
    }
    
    // Close modal functions
    function closeModal() {
        uploadModal.classList.remove('active');
        document.body.style.overflow = ''; // Re-enable scrolling
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    
    if (btnCancel) {
        btnCancel.addEventListener('click', closeModal);
    }
    
    // Close modal when clicking outside
    uploadModal.addEventListener('click', function(e) {
        if (e.target === uploadModal) {
            closeModal();
        }
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
    }
    
    // Display file in the list
    function displayFile(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        // Get appropriate icon based on file type
        let fileIconClass = 'fa-file';
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (fileExtension === 'pdf') {
            fileIconClass = 'fa-file-pdf-o';
        } else if (fileExtension === 'docx') {
            fileIconClass = 'fa-file-word-o';
        } else if (fileExtension === 'zip') {
            fileIconClass = 'fa-file-archive-o';
        }
        
        // Format file size
        const fileSize = formatFileSize(file.size);
        
        fileItem.innerHTML = `
            <div class="file-icon">
                <i class="fa ${fileIconClass}" aria-hidden="true"></i>
            </div>
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${fileSize}</div>
            </div>
            <div class="file-remove" data-filename="${file.name}">
                <i class="fa fa-times" aria-hidden="true"></i>
            </div>
        `;
        
        fileList.appendChild(fileItem);
        
        // Add event listener to remove button
        const removeBtn = fileItem.querySelector('.file-remove');
        removeBtn.addEventListener('click', function() {
            const fileName = this.getAttribute('data-filename');
            removeFile(fileName);
            fileItem.remove();
        });
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
    
    // Upload button functionality
    if (btnUpload) {
        btnUpload.addEventListener('click', function() {
            if (uploadedFiles.length === 0) return;
            
            // Create FormData object to send files
            const formData = new FormData();
            
            for (let i = 0; i < uploadedFiles.length; i++) {
                formData.append('files[]', uploadedFiles[i]);
            }
            
            // Show loading state
            btnUpload.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            btnUpload.disabled = true;
            
            // Simulate upload process (replace with actual AJAX call)
            setTimeout(() => {
                // Success message
                Swal.fire({
                    title: 'Upload Successful!',
                    text: 'Your files have been uploaded successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reset the form
                    uploadedFiles = [];
                    fileList.innerHTML = '';
                    btnUpload.disabled = true;
                    btnUpload.innerHTML = 'Upload';
                    fileInput.value = '';
                    
                    // Close the modal
                    closeModal();
                });
            }, 2000);
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
        projectsContainer.style.display = 'none';
        appContentHeader.style.display = 'none';

        // Update active states
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        profileSidebarIcon.classList.add('selected');
    }

    // Function to hide profile and show projects
    function hideProfile() {
        profileContainer.style.display = 'none';
        projectsContainer.style.display = 'grid';
        appContentHeader.style.display = 'flex';

        // Reset active states
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        document.querySelector('.menu-options li:nth-child(1)').classList.add('selected');
    }

    // Add click event to profile icons
    if (profileHeaderIcon) {
        profileHeaderIcon.addEventListener('click', showProfile);
    }
    
    if (profileSidebarIcon) {
        profileSidebarIcon.addEventListener('click', showProfile);
    }

    // Add click event to other sidebar icons to hide profile
    document.querySelectorAll('.menu-options li:not(#profileSidebarIcon)').forEach(item => {
        item.addEventListener('click', hideProfile);
    });

    // Also hide profile when clicking on header menu items
    document.querySelectorAll('.header .menu li').forEach(item => {
        item.addEventListener('click', hideProfile);
    });

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
            
            projectItems.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const description = item.querySelector('.desc-row p').textContent.toLowerCase();
                const tags = item.getAttribute('data-tags').toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm) || tags.includes(searchTerm)) {
                    item.style.display = 'flex';
                    notFound.style.display = 'none';
                } else {
                    item.style.display = 'none';
                    notFound.style.display = "flex";
                }
            });
            
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
            // Switch to list view
            projectsContainer.style.gridTemplateColumns = '1fr';
            
            // Update icon states
            displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });

        gridViewIcon.addEventListener('click', function() {
            // Switch to grid view
            projectsContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
            
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
});