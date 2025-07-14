/* Navigation Buttons */
document.addEventListener('DOMContentLoaded', function() {
    const navButtons = document.querySelectorAll('.nav-button');
    const contentContainers = document.querySelectorAll('.content-container');
    
    // Function to hide all content containers
    function hideAllContainers() {
        contentContainers.forEach(container => {
            container.style.display = 'none';
        });
    }
    
    // Function to handle button clicks
    function handleButtonClick(event) {
        const target = this.dataset.target;
        
        // Don't proceed if it's the logout button
        if (this.classList.contains('logout')) {
            window.location.href = "indexLogin.php";
        }
        
        // Hide all containers first
        hideAllContainers();
        
        // Remove active class from all buttons
        navButtons.forEach(button => {
            button.classList.remove('active');
            button.classList.remove('start');
        });
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Show the target container
        if (target) {
            const targetContainer = document.getElementById(target);
            if (targetContainer) {
                targetContainer.style.display = 'flex';
            }
        }
    }
    
    // Add click event to all navigation buttons
    navButtons.forEach(button => {
        button.addEventListener('click', handleButtonClick);
    });
    
    // Initialize the first container as visible
    if (contentContainers.length > 0) {
        contentContainers[0].style.display = 'flex';
    }
});

document.querySelector('.upload-icon').addEventListener('click', function() {
    document.querySelector('.upload-box').style.display = 'flex';
});

document.querySelector('.upload-box').addEventListener('click', function() {
    document.querySelector('.upload-box').style.display = 'none';
});
