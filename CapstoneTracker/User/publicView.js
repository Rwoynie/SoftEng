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
         
         projectItems.forEach(item => {
             const title = item.querySelector('h3').textContent.toLowerCase();
             const description = item.querySelector('.desc-row p').textContent.toLowerCase();
             const tags = item.getAttribute('data-tags').toLowerCase();
             
             if (title.includes(searchTerm) || description.includes(searchTerm) || tags.includes(searchTerm)) {
                 item.style.display = 'flex';
             } else {
                 item.style.display = 'none';
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
    
    // Initialize with recent view visible
    switchView(recentView, recentButton);
});