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

    // Initialize with recent view visible
    switchView(recentView, recentButton);
});
