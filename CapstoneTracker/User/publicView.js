document.addEventListener('DOMContentLoaded', function() {
    const allButton = document.getElementById('allButton');
    const recentButton = document.getElementById('recentButton');
    const allView = document.getElementById('allView');
    const recentView = document.getElementById('recentView');

    // Changed to select buttons instead of li elements
    const menuButtons = document.querySelectorAll('.header .menu button');

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
        
        
    }

    // Event listeners for buttons
    allButton.addEventListener('click', function() {
        switchView(allView, allButton);
    });

    recentButton.addEventListener('click', function() {
        switchView(recentView, recentButton);
    });

    // Initialize filter dropdown functionality
    const filterDropdown = document.getElementById('filterDropdown');
    const selectedText = filterDropdown.querySelector('.selected span');
    const options = filterDropdown.querySelectorAll('.options div');
    
    // Toggle dropdown on click
    filterDropdown.querySelector('.selected').addEventListener('click', function() {
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
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!filterDropdown.contains(e.target)) {
            filterDropdown.classList.remove('active');
        }
    });

     //Search functionality
     const searchInput = document.querySelector('.search-text');
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
     });

    // Display toggle functionality
const listViewIcon = document.querySelector('.fa-bars').parentElement;
const gridViewIcon = document.querySelector('.fa-th').parentElement;
const displayGroupIcons = document.querySelectorAll('.display-group .icon');

listViewIcon.addEventListener('click', function() {
    // Switch to list view
    const projects = document.querySelectorAll('.projects');
    projects.forEach(project => {
        project.style.gridTemplateColumns = '1fr';
    });
    
    // Update icon states
    displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
    this.classList.add('selected');
});

gridViewIcon.addEventListener('click', function() {
    // Switch to grid view
    const projects = document.querySelectorAll('.projects');
    projects.forEach(project => {
        project.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
    });
    
    // Update icon states
    displayGroupIcons.forEach(icon => icon.classList.remove('selected'));
    this.classList.add('selected');
});

// Sidebar functionality
const sidebarButtons = document.querySelectorAll('.sidebar nav .menu-options li');
const appContent = document.getElementById('app-content');
const loginContent = document.getElementById('login-content');


sidebarButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Remove selected class from all sidebar buttons
        sidebarButtons.forEach(btn => btn.classList.remove('selected'));
        
        // Add selected class to clicked button
        this.classList.add('selected');
        
        // Check if wrench button was clicked
        const isWrenchButton = this.querySelector('.fa-wrench');
        
        if (isWrenchButton) {
            // Show login content, hide main content
            appContent.style.display = 'none';
            loginContent.style.display = 'flex';
            header.style.display = 'none';
        } else {
            // Show main content, hide login content
            appContent.style.display = 'flex';
            loginContent.style.display = 'none';
            header.style.display = 'flex';
        }
    });
});

    // Animation on scroll functionality
    const animateOnScroll = function() {
        const projectItems = document.querySelectorAll('.project-item');
        
        // remove any existing animation classes
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
    switchView(recentView, recentButton);

    

});

function login(){
    window.location.href = "indexLogin.php";
}

