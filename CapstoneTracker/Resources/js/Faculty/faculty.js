document.addEventListener('DOMContentLoaded', function() {
    const profileSidebarIcon = document.getElementById('profileSidebarIcon');
    const profileContainer = document.getElementById('profileContainer');
    const projectsContainer = document.getElementById('projectsGrid');
    const appContentHeader = document.querySelector('.app-content-header');
    const logoutBtn = document.getElementById('logoutHeaderIcon');
    const approvalIcon = document.getElementById('approvalIcon');
    const dashboardIcon = document.getElementById('dashboardIcon');
    const approvalContainer = document.getElementById('approvalContainer');
    const filterDropdown = document.getElementById('filterDropdown');
    const searchInput = document.getElementById('searchInput');
    const listViewIcon = document.getElementById('listViewIcon');
    const gridViewIcon = document.getElementById('gridViewIcon');
    
    // Approval section elements
    const approvalFilterDropdown = document.getElementById('approvalFilterDropdown');
    const approvalSearchInput = document.getElementById('approvalSearchInput');
    const approvalListViewIcon = document.getElementById('approvalListViewIcon');
    const approvalGridViewIcon = document.getElementById('approvalGridViewIcon');
    const approvalListContainer = document.getElementById('approvalListContainer');
    const approvalItems = document.querySelectorAll('.approval-item');

    // Ensure dashboard is shown by default
    hideProfile();
    hideApprovalSection();

    // Logout functionality
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
                    Swal.fire(
                        'Logged out!',
                        'You have been successfully logged out.',
                        'success'
                    ).then(() => {
                        window.location.href = 'publicView.php';
                    });
                }
            });
        });
    }

    // Function to show profile and hide other sections
    function showProfile() {
        profileContainer.style.display = 'block';
        projectsContainer.style.display = 'none';
        approvalContainer.style.display = 'none';
        appContentHeader.style.display = 'none';
        
        // Hide the main header with "Thesis Repository" title and buttons
        const mainHeader = document.querySelector('.main-content .header');
        if (mainHeader) {
            mainHeader.style.display = 'none';
        }

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
        approvalContainer.style.display = 'none';
        appContentHeader.style.display = 'flex';
        
        // Show the main header with "Thesis Repository" title and buttons
        const mainHeader = document.querySelector('.main-content .header');
        if (mainHeader) {
            mainHeader.style.display = 'flex';
        }

        // Reset active states
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        dashboardIcon.classList.add('selected');
    }
    
    // Function to show approval section
    function showApprovalSection() {
        approvalContainer.style.display = 'block';
        projectsContainer.style.display = 'none';
        profileContainer.style.display = 'none';
        appContentHeader.style.display = 'none';

        // Update active states
        document.querySelectorAll('.menu-options li').forEach(item => {
            item.classList.remove('selected');
        });
        approvalIcon.classList.add('selected');
    }
    
    // Function to hide approval section
    function hideApprovalSection() {
        approvalContainer.style.display = 'none';
    }

    // Add click event to profile icons
    if (profileSidebarIcon) {
        profileSidebarIcon.addEventListener('click', showProfile);
    }
    
    // Add click event to approval icon
    if (approvalIcon) {
        approvalIcon.addEventListener('click', showApprovalSection);
    }

    // Add click event to dashboard icon
    if (dashboardIcon) {
        dashboardIcon.addEventListener('click', hideProfile);
    }

    // Add click event to other sidebar icons to hide profile/approval
    document.querySelectorAll('.menu-options li:not(#profileSidebarIcon):not(#approvalIcon)').forEach(item => {
        item.addEventListener('click', hideProfile);
    });

    // Filter dropdown functionality
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
                    projectItems.forEach(item => {
                        item.style.display = 'flex';
                    });
                } else {
                    projectItems.forEach(item => {
                        const tags = item.getAttribute('data-tags').split(' ');
                        if (tags.includes(value)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }
                
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
            
            animateOnScroll();
        });
    }

    // Display toggle functionality
    if (listViewIcon && gridViewIcon) {
        const displayGroupIcons = document.querySelectorAll('.display-group .icon');

        listViewIcon.addEventListener('click', function() {
            projectsContainer.style.gridTemplateColumns = '1fr';
            displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });

        gridViewIcon.addEventListener('click', function() {
            projectsContainer.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
            displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });
    }
    
    // Approval Filter dropdown functionality
    if (approvalFilterDropdown) {
        const selectedText = approvalFilterDropdown.querySelector('.selected span');
        const options = approvalFilterDropdown.querySelectorAll('.options div');
        
        // Toggle dropdown on click
        approvalFilterDropdown.querySelector('.selected').addEventListener('click', function(e) {
            e.stopPropagation();
            approvalFilterDropdown.classList.toggle('active');
        });
        
        // Handle option selection
        options.forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                selectedText.textContent = this.textContent;
                approvalFilterDropdown.classList.remove('active');
                filterApprovalItems();
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (approvalFilterDropdown && !approvalFilterDropdown.contains(e.target)) {
                approvalFilterDropdown.classList.remove('active');
            }
        });
    }

    // Approval Search functionality
    if (approvalSearchInput) {
        approvalSearchInput.addEventListener('input', function() {
            filterApprovalItems();
        });
    }
    
    // Approval Display toggle functionality
    if (approvalListViewIcon && approvalGridViewIcon) {
        const approvalDisplayGroupIcons = document.querySelectorAll('.approval-list-options .display-group .icon');

        approvalListViewIcon.addEventListener('click', function() {
            approvalListContainer.classList.remove('approval-grid-view');
            approvalListContainer.classList.add('approval-list-view');
            approvalDisplayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });

        approvalGridViewIcon.addEventListener('click', function() {
            approvalListContainer.classList.remove('approval-list-view');
            approvalListContainer.classList.add('approval-grid-view');
            approvalDisplayGroupIcons.forEach(icon => icon.classList.remove('selected'));
            this.classList.add('selected');
        });
    }

    function filterApprovalItems() {
        const filterValue = approvalFilterDropdown.querySelector('.selected span').textContent.toLowerCase();
        const searchTerm = approvalSearchInput.value.toLowerCase();
        
        approvalItems.forEach(item => {
            const status = item.getAttribute('data-status');
            const date = new Date(item.getAttribute('data-date'));
            const today = new Date();
            const textContent = item.textContent.toLowerCase();
            
            let shouldShow = true;
            
            // Apply filter
            if (filterValue.includes('today')) {
                shouldShow = date.toDateString() === today.toDateString();
            } else if (filterValue.includes('week')) {
                const oneWeekAgo = new Date();
                oneWeekAgo.setDate(today.getDate() - 7);
                shouldShow = date >= oneWeekAgo;
            } else if (filterValue.includes('month')) {
                const oneMonthAgo = new Date();
                oneMonthAgo.setMonth(today.getMonth() - 1);
                shouldShow = date >= oneMonthAgo;
            }
            
            // Apply search
            if (shouldShow && searchTerm) {
                shouldShow = textContent.includes(searchTerm);
            }
            
            item.style.display = shouldShow ? 'flex' : 'none';
        });
    }

    // Animation on scroll functionality
    const animateOnScroll = function() {
        const projectItems = document.querySelectorAll('.project-item');
        
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
        
        projectItems.forEach(item => {
            if (window.getComputedStyle(item).display !== 'none') {
                observer.observe(item);
            }
        });
    };
    
    animateOnScroll();
});

// Approval action functions
function viewThesis(thesisId) {
    Swal.fire({
        title: 'Viewing Thesis',
        text: `Would you like to view thesis #${thesisId}?`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Open Thesis',
        cancelButtonText: 'Cancel'
    });
}

function approveThesis(thesisId) {
    Swal.fire({
        title: 'Approve Thesis?',
        text: 'Are you sure you want to approve this thesis?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Approved!',
                'The thesis has been approved successfully.',
                'success'
            );
        }
    });
}

function rejectThesis(thesisId) {
    Swal.fire({
        title: 'Reject Thesis?',
        text: 'Please provide a reason for rejection:',
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter reason for rejection...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Reject Thesis',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please provide a reason for rejection!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Rejected!',
                'The thesis has been rejected.',
                'success'
            );
        }
    });
}

function requestRevision(thesisId) {
    Swal.fire({
        title: 'Request Revision',
        text: 'Please specify what needs to be revised:',
        icon: 'info',
        input: 'textarea',
        inputPlaceholder: 'Enter revision notes...',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Request Revision',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please provide revision notes!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Revision Requested!',
                'The student has been notified to make revisions.',
                'success'
            );
        }
    });
}