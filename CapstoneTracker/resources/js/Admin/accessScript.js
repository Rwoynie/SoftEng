document.addEventListener('DOMContentLoaded', function() {
    // Sample user data
    const users = [
        { id: 1, name: "John Smith", email: "john.smith@example.com", isAdmin: true, lastActive: "2 hours ago" },
        { id: 2, name: "Emma Johnson", email: "emma.j@example.com", isAdmin: false, lastActive: "1 day ago" },
        { id: 3, name: "Michael Brown", email: "m.brown@example.com", isAdmin: true, lastActive: "5 minutes ago" },
        { id: 4, name: "Sarah Davis", email: "sarah.d@example.com", isAdmin: false, lastActive: "3 days ago" },
        { id: 5, name: "Robert Wilson", email: "robert.w@example.com", isAdmin: false, lastActive: "1 week ago" },
        { id: 6, name: "Jennifer Miller", email: "jennifer.m@example.com", isAdmin: true, lastActive: "12 hours ago" },
        { id: 7, name: "David Taylor", email: "david.t@example.com", isAdmin: false, lastActive: "2 days ago" },
        { id: 8, name: "Lisa Anderson", email: "lisa.a@example.com", isAdmin: false, lastActive: "Just now" }
    ];

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
        adminUserList.innerHTML = '';
        
        if (userArray.length === 0) {
            notFound.style.display = 'block';
            return;
        }
        
        notFound.style.display = 'none';
        
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
                        <span class="toggle-label">${user.isAdmin ? 'Admin' : 'User'}</span>
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
                
                // Update the label text
                const label = this.parentElement.querySelector('.toggle-label');
                label.textContent = this.checked ? 'Admin' : 'User';
            });
        });
    }

    // Filter users based on search
    adminUserSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filteredUsers = users.filter(user => 
            user.name.toLowerCase().includes(searchTerm) || 
            user.email.toLowerCase().includes(searchTerm)
        );
        renderAdminUsers(filteredUsers);
    });

    // Save changes with confirmation
    saveAdminChangesBtn.addEventListener('click', function() {
        if (!changesMade) {
            alert('You haven\'t made any changes to save.');
            return;
        }

        if (confirm('Are you sure you want to save these administrator privilege changes?')) {
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
            alert('Admin privileges have been updated successfully!');
            
            // Refresh the view
            renderAdminUsers(users);
        }
    });

    // Back button functionality
    backButton.addEventListener('click', function() {
        alert('Navigating back to roles selection');
        // In a real application, this would navigate back
        // window.history.back() or similar functionality
    });

    // Initial render
    renderAdminUsers(users);
});