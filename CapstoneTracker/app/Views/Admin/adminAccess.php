
    <div class="app-content">
        <div id="adminAccessPanel" class="access-card">
            <button id="backToRoles" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Roles
            </button>
            
            <h3>Administrator Access Management</h3>
            
            <div class="searchbox">
                <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                <input type="text" placeholder="Search users..." class="search-text" id="adminUserSearch">
            </div>
            
            <div class="access-list" id="adminUserList">
                <!-- Users will be populated here by JavaScript -->
                <div class="access-item">
                    <div class="access-info">
                        <h4>John Smith</h4>
                        <p>john.smith@example.com • Last active: 2 hours ago</p>
                    </div>
                    <div class="access-count">
                        <label class="admin-toggle">
                            <input type="checkbox" checked data-user-id="1">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Admin</span>
                        </label>
                    </div>
                </div>
                
                <div class="access-item">
                    <div class="access-info">
                        <h4>Emma Johnson</h4>
                        <p>emma.j@example.com • Last active: 1 day ago</p>
                    </div>
                    <div class="access-count">
                        <label class="admin-toggle">
                            <input type="checkbox" data-user-id="2">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">User</span>
                        </label>
                    </div>
                </div>
                
                <div class="access-item">
                    <div class="access-info">
                        <h4>Michael Brown</h4>
                        <p>m.brown@example.com • Last active: 5 minutes ago</p>
                    </div>
                    <div class="access-count">
                        <label class="admin-toggle">
                            <input type="checkbox" checked data-user-id="3">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">Admin</span>
                        </label>
                    </div>
                </div>
                
                <div class="access-item">
                    <div class="access-info">
                        <h4>Sarah Davis</h4>
                        <p>sarah.d@example.com • Last active: 3 days ago</p>
                    </div>
                    <div class="access-count">
                        <label class="admin-toggle">
                            <input type="checkbox" data-user-id="4">
                            <span class="toggle-slider"></span>
                            <span class="toggle-label">User</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <p class="notFound" id="notFound">No users found matching your search.</p>
            
            <div class="fab-icon" id="saveAdminChangesBtn">
                <i class="fas fa-save"></i>
            </div>
        </div>
    </div>

    