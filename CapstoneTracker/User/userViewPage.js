document.addEventListener('DOMContentLoaded', function() {
    const profileHeaderIcon = document.getElementById('profileHeaderIcon');
    const profileSidebarIcon = document.getElementById('profileSidebarIcon');
    const profileContainer = document.getElementById('profileContainer');
    const projectsContainer = document.querySelector('.projects');
    const appContentHeader = document.querySelector('.app-content-header');

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
        document.querySelector('.menu-options li:nth-child(2)').classList.add('selected');
    }

    // Add click event to profile icons
    profileHeaderIcon.addEventListener('click', showProfile);
    profileSidebarIcon.addEventListener('click', showProfile);

    // Add click event to other sidebar icons to hide profile
    document.querySelectorAll('.menu-options li:not(#profileSidebarIcon)').forEach(item => {
        item.addEventListener('click', hideProfile);
    });

    // Also hide profile when clicking on header menu items
    document.querySelectorAll('.header .menu li').forEach(item => {
        item.addEventListener('click', hideProfile);
    });
});
