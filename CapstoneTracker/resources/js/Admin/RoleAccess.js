
// Admin Access Management
async function fetchAndDisplayUsers() {
    try {
    //    console.log('Fetching users with CSRF token...');
        
        // Get the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
     //   console.log('Using CSRF token:', csrfToken);
        
        // Use FormData for POST request with CSRF token
        const formData = new FormData();
        formData.append('action', 'get_all_users_complete_roles');
        formData.append('csrf_token', csrfToken);
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
     //   console.log('Raw response:', rawText);
        
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            // Try to extract JSON if there's extra output
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                data = JSON.parse(jsonMatch[0]);
                console.log('Successfully extracted JSON from response');
            } else {
                throw new Error('Server returned invalid JSON response');
            }
        }
        
    //    console.log('Parsed data:', data);
        
        if (data.success && data.users) {
      //      console.log(`✅ Successfully loaded ${data.users.length} users with role data`);
            displayUsersInAccessManagement(data.users);
            
            // ADD THIS LINE: Update the user counts in access cards
            updateUserCounts(data.users);
        } else {
            throw new Error(data.message || 'Failed to load user data');
        }
        
    } catch (error) {
        console.error('Error fetching users:', error);
        
        // Show error in UI
        const adminUserList = document.getElementById('adminUserList');
        if (adminUserList) {
            adminUserList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Failed to Load Users</h3>
                    <p>${error.message}</p>
                    <button onclick="fetchAndDisplayUsers()" class="retry-btn">Retry</button>
                </div>
            `;
        }
        
        Swal.fire({
            title: 'Load Error',
            text: error.message,
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
    userItem.setAttribute('data-user-id', user.User_ID);
    
    // Store permission data in data attributes
    userItem.setAttribute('data-sub-admin', user.Sub_Admin || 'No');
    userItem.setAttribute('data-can-edit', user.Can_Edit || 'No');
    userItem.setAttribute('data-manage-access', user.Manage_Access || 'No');
    
    // Get role display text from User_Role (USER_INFORMATION table)
    let userRoleDisplay = '';
    if (user.User_Role === 'faculty') {
        userRoleDisplay = 'Faculty';
    } else if (user.User_Role === 'student') {
        userRoleDisplay = 'Student';
    } else if (user.User_Role === 'superAdmin') {
        userRoleDisplay = 'Admin';
    } else if (user.User_Role === 'SubAdmin') {
        userRoleDisplay = 'Sub-Admin';
    } else {
        userRoleDisplay = user.User_Role || 'User';
    }
    
    // Get actual permission values from ROLES table
    const subAdminValue = user.Sub_Admin || 'No';
    const canEditValue = user.Can_Edit || 'No';
    const manageAccessValue = user.Manage_Access || 'No';
    
    // Concatenate department and course
    const department = user.Department || '';
    const course = user.Course || '';
    let departmentCourse = '';
    
    if (department && course) {
        departmentCourse = `${department} • ${course}`;
    } else if (department) {
        departmentCourse = department;
    } else if (course) {
        departmentCourse = course;
    } else {
        departmentCourse = 'No Department/Course';
    }
    
    // Check if user is super admin - hide rolebox for super admin
    const isSuperAdmin = user.User_Role === 'superAdmin';
    
    // Create user item - hide role button for super admin
    userItem.innerHTML = `
    <div class="access-info">
        <h4>${user.First_Name} ${user.Middle_Name || ''} ${user.Last_Name} ${user.Extension || ''}</h4>
        <p>${user.Email} • ${departmentCourse} • Status: ${user.Acc_Status}</p>
        </div>
        <div class="role-checkbox-container">
            <label class="role-checkbox">
                <p>${userRoleDisplay}</p>
            </label>
            ${!isSuperAdmin ? `
            <button class="role-button" title="Manage Roles">
                <i class="fa-solid fa-circle-plus"></i>
            </button>
            ` : `
            <div class="role-button-disabled" title="Super Admin roles cannot be modified">
                <i class="fa-solid fa-crown" style="color: #ffd700;"></i>
            </div>
            `}
        </div>
    `;
    
    return userItem;
}


function initializeRoleBox() {
    // Create overlay for closing roleBox when clicking outside
    const overlay = document.createElement('div');
    overlay.className = 'roleBox-overlay';
    document.body.appendChild(overlay);
    
    // Create global roleBox container
    const globalRoleBoxContainer = document.getElementById('globalRoleBoxContainer');
    if (!globalRoleBoxContainer) {
        const container = document.createElement('div');
        container.id = 'globalRoleBoxContainer';
        document.body.appendChild(container);
    }
    
    // Close all roleBoxes when clicking overlay
    overlay.addEventListener('click', function() {
        closeAllRoleBoxes();
    });
    
    // Handle role button clicks
    document.addEventListener('click', function(e) {
        const roleButton = e.target.closest('.role-button');
        if (roleButton) {
            e.preventDefault();
            e.stopPropagation();
            
            const accessItem = roleButton.closest('.access-item');
            const userId = accessItem.getAttribute('data-user-id');
            
            // Close all other roleBoxes
            closeAllRoleBoxes();
            
            // Create and show roleBox for this user
            showRoleBoxForUser(roleButton, userId, accessItem);
        }
        
        // Handle roleBox button clicks
        const roleActionBtn = e.target.closest('.role-action-btn');
        if (roleActionBtn && !roleActionBtn.disabled) {
            e.preventDefault();
            e.stopPropagation();
            
            const roleBox = roleActionBtn.closest('.roleBox');
            if (roleBox) {
                const userId = roleBox.getAttribute('data-user-id');
                const userName = roleBox.getAttribute('data-user-name');
                const permissionType = roleActionBtn.getAttribute('data-permission');
                const currentValue = roleActionBtn.getAttribute('data-current-value');
                
                // Get current permission values from rolebox data attributes
                const currentSubAdmin = roleBox.getAttribute('data-sub-admin');
                const currentCanEdit = roleBox.getAttribute('data-can-edit');
                const currentManageAccess = roleBox.getAttribute('data-manage-access');
                
                // Call the function to handle the role action with all current values
                handleRoleAction(userId, userName, permissionType, currentValue, currentSubAdmin, currentCanEdit, currentManageAccess);
                
                // Close the roleBox after action
                closeAllRoleBoxes();
            }
        }
    });
}

function showRoleBoxForUser(roleButton, userId, accessItem) {
    // Get user data from the access item
    const userName = accessItem.querySelector('h4').textContent;
    
    // Get permission values from data attributes
    const subAdminValue = accessItem.getAttribute('data-sub-admin') || 'No';
    const canEditValue = accessItem.getAttribute('data-can-edit') || 'No';
    const manageAccessValue = accessItem.getAttribute('data-manage-access') || 'No';
    
    const subAdminColor = subAdminValue === 'Yes' ? 'red' : 'gray';
    const canEditColor = canEditValue === 'Yes' ? 'red' : 'gray';
    const manageAccessColor = manageAccessValue === 'Yes' ? 'red' : 'gray';
    
    // Create roleBox HTML with ALL necessary data attributes
    const roleBoxHTML = `
        <div class="roleBox active" 
             data-user-id="${userId}" 
             data-user-name="${userName}"
             data-sub-admin="${subAdminValue}"
             data-can-edit="${canEditValue}"
             data-manage-access="${manageAccessValue}">
            <button class="role-action-btn" 
                    data-permission="sub_admin" 
                    data-current-value="${subAdminValue}">
                <i class="fa-solid fa-user-shield" style="color: ${subAdminColor};"></i> Sub-Admin
            </button>
            <button class="role-action-btn ${subAdminValue === 'No' ? 'disabled-role' : ''}" 
                    data-permission="can_edit" 
                    data-current-value="${canEditValue}"
                    ${subAdminValue === 'No' ? 'disabled' : ''}>
                <i class="fa-solid fa-file-pen" style="color: ${canEditColor};"></i> Modify Thesis
                ${subAdminValue === 'No' ? '<span class="role-hint">(Sub-Admin only)</span>' : ''}
            </button>
            <button class="role-action-btn ${subAdminValue === 'No' ? 'disabled-role' : ''}" 
                    data-permission="manage_access" 
                    data-current-value="${manageAccessValue}"
                    ${subAdminValue === 'No' ? 'disabled' : ''}>
                <i class="fa-solid fa-key" style="color: ${manageAccessColor};"></i> Manage Access
                ${subAdminValue === 'No' ? '<span class="role-hint">(Sub-Admin only)</span>' : ''}
            </button>
        </div>
    `;
    
    // Add to global container
    const globalContainer = document.getElementById('globalRoleBoxContainer');
    globalContainer.innerHTML = roleBoxHTML;
    globalContainer.style.display = 'block';
    
    // Position the roleBox relative to the button
    const roleBox = globalContainer.querySelector('.roleBox');
    positionRoleBox(roleBox, roleButton);
    
    // Show overlay
    document.querySelector('.roleBox-overlay').classList.add('active');
}

function positionRoleBox(roleBox, roleButton) {
    const buttonRect = roleButton.getBoundingClientRect();
    const roleBoxRect = roleBox.getBoundingClientRect();
    
    // Position below the button
    let top = buttonRect.bottom + 5;
    let left = buttonRect.right - roleBoxRect.width;
    
    // Adjust if roleBox would go off screen
    if (left + roleBoxRect.width > window.innerWidth) {
        left = window.innerWidth - roleBoxRect.width - 10;
    }
    
    if (top + roleBoxRect.height > window.innerHeight) {
        top = buttonRect.top - roleBoxRect.height - 5;
    }
    
    roleBox.style.top = top + 'px';
    roleBox.style.left = left + 'px';
}



async function handleRoleAction(userId, userName, permissionType, currentValue) {
    try {
        console.log(`Starting role action for user ${userId}, permission ${permissionType}, current value ${currentValue}`);

        // Get the rolebox to access all current permission values
        const roleBox = document.querySelector('#globalRoleBoxContainer .roleBox.active');
        if (!roleBox) {
            throw new Error('Role box not found');
        }

        // Get current permission values from the rolebox data attributes
        const currentSubAdmin = roleBox.getAttribute('data-sub-admin') || 'No';
        const currentCanEdit = roleBox.getAttribute('data-can-edit') || 'No';
        const currentManageAccess = roleBox.getAttribute('data-manage-access') || 'No';

        // First, check if the user's account status is pending
        let userStatus;
        try {
            userStatus = await getUserAccountStatus(userId);
            console.log(`User ${userId} account status: ${userStatus}`);
        } catch (statusError) {
            console.error('Error checking account status:', statusError);
            // Continue anyway but show a warning
            await Swal.fire({
                title: 'Warning',
                text: `Unable to verify account status: ${statusError.message}. Proceeding with caution.`,
                icon: 'warning',
                confirmButtonText: 'Continue'
            });
            userStatus = 'unknown';
        }
        
        if (userStatus === 'pending') {
            await Swal.fire({
                title: 'Account Pending',
                html: `Cannot modify roles for <strong>${userName}</strong> because their account is still pending approval.<br><br>
                      Please approve the account first before assigning roles.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Determine new value (toggle between Yes/No)
        const newValue = currentValue === 'Yes' ? 'No' : 'Yes';
        
        // Map permission types to display names
        const permissionDisplayNames = {
            'sub_admin': 'Sub-Admin',
            'can_edit': 'Modify Thesis', 
            'manage_access': 'Manage Access'
        };
        
        const displayName = permissionDisplayNames[permissionType] || permissionType;
        const action = newValue === 'Yes' ? 'grant' : 'revoke';
        
        // Show confirmation dialog
        let confirmationMessage = `Are you sure you want to ${action} <strong>${displayName}</strong> permission for <strong>${userName}</strong>?`;
        
        // Special message for Sub-Admin revocation
        if (permissionType === 'sub_admin' && newValue === 'No') {
            confirmationMessage = `Are you sure you want to revoke <strong>Sub-Admin</strong> permission for <strong>${userName}</strong>?<br><br>
                                  <small style="color: #666;">This will:
                                  <br>• Change user role back to their original role
                                  <br>• Remove Sub-Admin status
                                  <br>• <strong>Automatically disable all other permissions</strong></small>`;
        }
        
        // Special message for trying to grant permissions without Sub-Admin
        if ((permissionType === 'can_edit' || permissionType === 'manage_access') && newValue === 'Yes') {
            if (currentSubAdmin === 'No') {
                await Swal.fire({
                    title: 'Sub-Admin Required',
                    html: `Cannot grant <strong>${permissionType === 'can_edit' ? 'Modify Thesis' : 'Manage Access'}</strong> permission to <strong>${userName}</strong>.<br><br>
                          <strong>Only Sub-Admin users can have these permissions.</strong><br><br>
                          Please grant Sub-Admin permission first, then you can assign ${permissionType === 'can_edit' ? 'Modify Thesis' : 'Manage Access'}.`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
        }
        
        const result = await Swal.fire({
            title: `${action === 'grant' ? 'Grant' : 'Revoke'} ${displayName}?`,
            html: confirmationMessage,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${action} permission!`,
            cancelButtonText: 'Cancel'
        });
        
        if (result.isConfirmed) {
            // Update the specific permission in the database
            await updateUserPermission(userId, permissionType, newValue, currentSubAdmin, currentCanEdit, currentManageAccess);
            
            let successMessage = `Successfully ${action === 'grant' ? 'granted' : 'revoked'} ${displayName} permission for ${userName}.`;
            
            // Add note about auto-disabling for Sub-Admin revocation
            if (permissionType === 'sub_admin' && newValue === 'No') {
                successMessage += `<br><br><small>All other permissions have been automatically disabled.</small>`;
            }
            
            await Swal.fire({
                title: 'Success!',
                html: successMessage,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            
            // Refresh the user list to show updated permissions
            fetchAndDisplayUsers();
        }
    } catch (error) {
        console.error('Error in handleRoleAction:', error);
        
        await Swal.fire({
            title: 'Error',
            text: `Failed to update permission: ${error.message}`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}


async function updateUserPermission(userId, permissionType, newValue, currentSubAdmin, currentCanEdit, currentManageAccess) {
    let swalInstance = null;
    
    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Double-check account status before proceeding (safety net)
        const userStatus = await getUserAccountStatus(userId);
        if (userStatus === 'pending') {
            throw new Error('Cannot modify roles for pending accounts');
        }

        // Show loading state
        swalInstance = Swal.fire({
            title: 'Updating Permission...',
            text: 'Please wait while we update the user permission.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Prepare the data for the request using the passed current values
        const formData = new FormData();
        formData.append('action', 'update_user_role');
        formData.append('user_id', userId);
        formData.append('csrf_token', csrfToken);
        
        // Set the appropriate permission fields - only change the specific permission
        let subAdminValue = currentSubAdmin;
        let canEditValue = currentCanEdit;
        let manageAccessValue = currentManageAccess;
        let restoreOriginalRole = false;
        let currentUserRole = null;

        // Handle different permission types
        if (permissionType === 'sub_admin') {
            subAdminValue = newValue;
            
            if (newValue === 'Yes') {
                // When granting Sub-Admin, get current role to store as original
                currentUserRole = await getUserCurrentRole(userId);
                formData.append('current_user_role', currentUserRole);
            } else {
                // When revoking Sub-Admin, restore original role
                restoreOriginalRole = true;
                formData.append('restore_original_role', 'true');
            }
        } else if (permissionType === 'can_edit') {
            canEditValue = newValue;
            // When changing can_edit, ensure sub_admin remains as current value
            subAdminValue = currentSubAdmin;
        } else if (permissionType === 'manage_access') {
            manageAccessValue = newValue;
            // When changing manage_access, ensure sub_admin remains as current value
            subAdminValue = currentSubAdmin;
        }
        
        // Set all permission values
        formData.append('sub_admin', subAdminValue);
        formData.append('can_edit', canEditValue);
        formData.append('manage_access', manageAccessValue);
        
        console.log('Sending role update request for user:', userId);
        console.log('Permission type:', permissionType);
        console.log('New value:', newValue);
        console.log('All permissions - Sub_Admin:', subAdminValue, 'Can_Edit:', canEditValue, 'Manage_Access:', manageAccessValue);
        console.log('Restore original role:', restoreOriginalRole);
        console.log('Current user role:', currentUserRole);
        
        // Send the request to update the role
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
        console.log('Raw response from server:', rawText);
        
        let data;
        
        // More robust response parsing
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            
            // Try to extract JSON from the response
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    data = JSON.parse(jsonMatch[0]);
                    console.log('Successfully extracted JSON from response');
                } catch (e) {
                    throw new Error('Server returned invalid JSON format. Raw response: ' + rawText.substring(0, 200));
                }
            } else {
                // Check if it's a PHP error
                if (rawText.includes('Fatal error') || rawText.includes('Parse error') || rawText.includes('Warning') || rawText.includes('Notice')) {
                    throw new Error('PHP error detected: ' + rawText.substring(0, 300));
                } else {
                    throw new Error('Server returned non-JSON response: ' + rawText.substring(0, 200));
                }
            }
        }
        
        console.log('Parsed response data:', data);
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to update user permission');
        }
        
        // Close the loading dialog
        if (swalInstance) {
            Swal.close();
        }
        
        return data;
        
    } catch (error) {
        console.error('Error in updateUserPermission:', error);
        
        // Ensure loading dialog is closed
        if (swalInstance) {
            Swal.close();
        }
        
        throw error;
    }
}


// Function to update user counts in access cards
function updateUserCounts(users) {
    const counts = {
        'admin': 0,
        'faculty': 0,
        'student': 0,
        'all': users.length // Total count for "All" card
    };
    
    // Count users by role
    users.forEach(user => {
        if (user.User_Role === 'SubAdmin' || user.User_Role === 'superAdmin') {
            counts.admin++;
        } else if (user.User_Role === 'faculty') {
            counts.faculty++;
        } else if (user.User_Role === 'student') {
            counts.student++;
        }
    });
    
    // Update the access cards
    const allCountElement = document.querySelector('#allAccessBtn .access-count');
    const adminCountElement = document.querySelector('#adminAccess .access-count');
    const facultyCountElement = document.querySelector('#facultyAccess .access-count');
    const studentCountElement = document.querySelector('#studentAccess .access-count');
    
    if (allCountElement) allCountElement.textContent = `${counts.all} users`;
    if (adminCountElement) adminCountElement.textContent = `${counts.admin} users`;
    if (facultyCountElement) facultyCountElement.textContent = `${counts.faculty} users`;
    if (studentCountElement) studentCountElement.textContent = `${counts.student} users`;
    
    
}


function closeAllRoleBoxes() {
    const globalContainer = document.getElementById('globalRoleBoxContainer');
    if (globalContainer) {
        globalContainer.innerHTML = '';
        globalContainer.style.display = 'none';
    }
    document.querySelector('.roleBox-overlay')?.classList.remove('active');
}


async function revokeAllPermissions(userId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const formData = new FormData();
        formData.append('action', 'update_user_role');
        formData.append('user_id', userId);
        formData.append('csrf_token', csrfToken);
        formData.append('sub_admin', 'No');
        formData.append('can_edit', 'No');
        formData.append('manage_access', 'No');
        formData.append('restore_original_role', 'true');
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
        let data;
        
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                data = JSON.parse(jsonMatch[0]);
            } else {
                throw new Error('Server returned invalid response format');
            }
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to revoke all permissions');
        }
        
        return data;
        
    } catch (error) {
        console.error('Error in revokeAllPermissions:', error);
        throw error;
    }
}

async function getUserAccountStatus(userId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const formData = new FormData();
        formData.append('action', 'get_user_account_status');
        formData.append('user_id', userId);
        formData.append('csrf_token', csrfToken);
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
     //   console.log('Raw response for account status:', rawText); // Debug log
        
        let data;
        
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            console.error('JSON parse error for account status:', parseError);
            console.error('Raw response that failed to parse:', rawText);
            
            // Try to extract JSON if there's extra output
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    data = JSON.parse(jsonMatch[0]);
                    console.log('Successfully extracted JSON from response');
                } catch (e) {
                    throw new Error('Server returned invalid JSON format');
                }
            } else {
                // Check if it's a PHP error
                if (rawText.includes('Fatal error') || rawText.includes('Parse error') || rawText.includes('Warning') || rawText.includes('Notice')) {
                    throw new Error('PHP error detected: ' + rawText.substring(0, 200));
                } else {
                    throw new Error('Server returned non-JSON response');
                }
            }
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to get user account status');
        }
        
        return data.account_status;
        
    } catch (error) {
        console.error('Error getting user account status:', error);
        throw error; // Re-throw to let caller handle it
    }
}

async function getUserCurrentRole(userId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const formData = new FormData();
        formData.append('action', 'get_user_current_role');
        formData.append('user_id', userId);
        formData.append('csrf_token', csrfToken);
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
        console.log('Raw response for current role:', rawText);
        
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                data = JSON.parse(jsonMatch[0]);
            } else {
                throw new Error('Server returned invalid response format');
            }
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to get user role');
        }
        
        return data.user_role;
        
    } catch (error) {
        console.error('Error getting user current role:', error);
        throw error;
    }
}

async function getStoredOriginalRole(userId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const formData = new FormData();
        formData.append('action', 'get_stored_original_role');
        formData.append('user_id', userId);
        formData.append('csrf_token', csrfToken);
        
        const response = await fetch('../../../app/Controllers/RolesController.php', {
            method: 'POST',
            body: formData
        });
        
        const rawText = await response.text();
        console.log('Raw response for stored role:', rawText); // Debug log
        
        let data;
        
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            console.error('JSON parse error for stored role:', parseError);
            console.error('Raw response that failed to parse:', rawText);
            
            // Try to extract JSON if there's extra output
            const jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (jsonMatch) {
                try {
                    data = JSON.parse(jsonMatch[0]);
                    console.log('Successfully extracted JSON from response');
                } catch (e) {
                    throw new Error('Server returned invalid JSON format');
                }
            } else {
                // Check if it's a PHP error
                if (rawText.includes('Fatal error') || rawText.includes('Parse error') || rawText.includes('Warning') || rawText.includes('Notice')) {
                    throw new Error('PHP error detected: ' + rawText.substring(0, 200));
                } else {
                    throw new Error('Server returned non-JSON response');
                }
            }
        }
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to get stored role');
        }
        
        return data.original_role;
        
    } catch (error) {
        console.error('Error getting stored original role:', error);
        throw error;
    }
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
