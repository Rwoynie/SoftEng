// resources/js/Admin/Backup.js

class BackupManager {
    constructor() {
        this.initEventListeners();
        this.loadBackupInfo();
        this.loadBackupHistory();
    }

    initEventListeners() {
        // Create Backup Button
        const createBackupBtn = document.getElementById('createBackupBtn');
        if (createBackupBtn) {
            createBackupBtn.addEventListener('click', () => {
                this.createBackup();
            });
        }

        // Restore Backup Button
        const restoreBackupBtn = document.getElementById('restoreBackupBtn');
        if (restoreBackupBtn) {
            restoreBackupBtn.addEventListener('click', () => {
                this.showRestoreDialog();
            });
        }

        // View Backup History Button
        const viewHistoryBtn = document.getElementById('viewBackupHistoryBtn');
        if (viewHistoryBtn) {
            viewHistoryBtn.addEventListener('click', () => {
                this.toggleBackupHistory();
            });
        }
    }

    // Add this new method to toggle history visibility
    toggleBackupHistory() {
        const historySection = document.getElementById('backupHistorySection');
        if (historySection) {
            if (historySection.style.display === 'none' || !historySection.style.display) {
                historySection.style.display = 'block';
                this.loadBackupHistory();
            } else {
                historySection.style.display = 'none';
            }
        }
    }

    // Update the renderBackupHistory method to use proper event delegation
    renderBackupHistory(backups) {
        const tbody = document.getElementById('backupHistoryTableBody');

        if (backups.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="no-backups-message">
                        <i class="fas fa-inbox"></i>
                        <h4>No Backup History</h4>
                        <p>No backups have been created yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = backups.map(backup => `
            <tr>
                <td>${this.formatDate(backup.created_at)}</td>
                <td>${backup.file_name}</td>
                <td>${backup.size_formatted}</td>
                <td>Manual Backup</td>
                <td class="text-center">
                    <button class="backup-action download-btn" data-filename="${backup.file_name}" data-action="download" title="Download Backup">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="backup-action restore-btn" data-filename="${backup.file_name}" data-action="restore" title="Restore Backup">
                        <i class="fas fa-upload"></i>
                    </button>
                    <button class="backup-action delete-btn" data-filename="${backup.file_name}" data-action="delete" title="Delete Backup">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        // Add event listeners to the new buttons
        this.initTableEventListeners();
    }

    // Add event delegation for table buttons
    initTableEventListeners() {
        const tbody = document.getElementById('backupHistoryTableBody');
        if (tbody) {
            tbody.addEventListener('click', (e) => {
                const button = e.target.closest('button');
                if (button) {
                    const filename = button.getAttribute('data-filename');
                    const action = button.getAttribute('data-action');
                    
                    switch (action) {
                        case 'download':
                            this.downloadBackup(filename);
                            break;
                        case 'restore':
                            this.confirmRestore(filename);
                            break;
                        case 'delete':
                            this.confirmDelete(filename);
                            break;
                    }
                }
            });
        }
    }

    // Update other methods to remove onclick attributes from the HTML
    async createBackup() {
        const result = await Swal.fire({
            title: 'Create System Backup?',
            text: 'This will create a complete backup of the database. This may take a few moments.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Create Backup!',
            cancelButtonText: 'Cancel',
        });

        if (!result.isConfirmed) {
            return;
        }

        const createBtn = document.getElementById('createBackupBtn');
        const originalText = createBtn.innerHTML;
        
        try {
            createBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
            createBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'create_backup');
            formData.append('csrf_token', this.getCsrfToken());

            const response = await fetch('../../../app/Controllers/BackupController.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Backup created successfully!', 'success');
                this.loadBackupInfo();
                this.loadBackupHistory();
            } else {
                throw new Error(result.error || 'Failed to create backup');
            }

        } catch (error) {
            console.error('Backup creation error:', error);
            this.showNotification('Error creating backup: ' + error.message, 'error');
        } finally {
            createBtn.innerHTML = originalText;
            createBtn.disabled = false;
        }
    }

    // Add helper method to get CSRF token
    getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    showRestoreDialog() {
        // Create file input for backup selection
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = '.gz';
        fileInput.style.display = 'none';
    
        fileInput.addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (file) {
                // Show confirmation before restoring
                const result = await Swal.fire({
                    title: 'Restore Backup?',
                    text: `WARNING: This will restore from ${file.name} and OVERWRITE all current data. This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Restore!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    dangerMode: true
                });
    
                if (result.isConfirmed) {
                    this.restoreBackup(file.name);
                }
            }
        });
    
        document.body.appendChild(fileInput);
        fileInput.click();
        document.body.removeChild(fileInput);
    }

    confirmRestore(backupFileName) {
        Swal.fire({
            title: 'Restore Backup?',
            text: `Are you sure you want to restore from ${backupFileName}? This will overwrite all current data.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, restore!',
            cancelButtonText: 'Cancel',
            
        }).then((result) => {
            if (result.isConfirmed) {
                this.restoreBackup(backupFileName);
            }
        });
    }

    async restoreBackup(backupFileName) {
        // Show confirmation for restore
        const result = await Swal.fire({
            title: 'Restore Backup?',
            text: `WARNING: This will restore from ${backupFileName} and OVERWRITE all current data. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Restore Backup!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            dangerMode: true
        });
    
        if (!result.isConfirmed) {
            return;
        }
    
        try {
            const formData = new FormData();
            formData.append('action', 'restore_backup');
            formData.append('backup_file', backupFileName);
            formData.append('csrf_token', this.getCsrfToken());
    
            // Show loading state
            Swal.fire({
                title: 'Restoring Backup...',
                text: 'This may take a few moments. Do not close this window.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
    
            const response = await fetch('../../../app/Controllers/BackupController.php', {
                method: 'POST',
                body: formData
            });
    
            const result = await response.json();
    
            if (result.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Backup restored successfully! The page will reload.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(result.error || 'Failed to restore backup');
            }
    
        } catch (error) {
            console.error('Backup restore error:', error);
            
            // Show detailed error message
            let errorMessage = error.message;
            if (errorMessage.includes('mysqldump') || errorMessage.includes('mysql')) {
                errorMessage += '\n\nPlease check that MySQL is running and the paths are correct.';
            }
            
            Swal.fire({
                title: 'Restore Failed',
                html: `<div style="text-align: left;">
                        <p>${errorMessage}</p>
                        <details style="margin-top: 10px;">
                            <summary>Technical Details</summary>
                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; margin-top: 10px;">${error.stack}</pre>
                        </details>
                       </div>`,
                icon: 'error',
                confirmButtonText: 'OK',
                width: '600px'
            });
        }
    }

    async loadBackupHistory() {
        try {
            const tbody = document.getElementById('backupHistoryTableBody');
            if (!tbody) {
                console.error('Backup history table body not found');
                return;
            }
    
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="no-backups-message">
                        <i class="fas fa-spinner fa-spin"></i>
                        <h4>Loading backups...</h4>
                    </td>
                </tr>
            `;
    
            const formData = new FormData();
            formData.append('action', 'get_backup_history');
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
            const response = await fetch('../../../app/Controllers/BackupController.php', {
                method: 'POST',
                body: formData
            });

            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            
            // Check if response contains HTML error
            if (responseText.trim().startsWith('<') || responseText.includes('<br />') || responseText.includes('<b>')) {
                console.error('HTML error detected in response:', responseText);
                
                // Try to extract error message from HTML
                const errorMatch = responseText.match(/<b>([^<]+)<\/b>/);
                const errorMessage = errorMatch ? errorMatch[1] : 'Server returned HTML error instead of JSON';
                
                throw new Error(`Server Error: ${errorMessage}`);
            }
    
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Raw response that failed to parse:', responseText);
                throw new Error('Invalid JSON response from server. The server may be experiencing issues.');
            }
    
            if (result.success) {
                this.renderBackupHistory(result.backups);
            } else {
                throw new Error(result.error || 'Failed to load backup history');
            }
    
        } catch (error) {
            console.error('Backup history load error:', error);
            this.renderBackupHistoryError(error.message);
        }
    }
    
    renderBackupHistory(backups) {
        const tbody = document.getElementById('backupHistoryTableBody');

        if (backups.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="no-backups-message">
                        <i class="fas fa-inbox"></i>
                        <h4>No Backup History</h4>
                        <p>No backups have been created yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = backups.map(backup => `
            <tr>
                <td>${this.formatDate(backup.created_at)}</td>
                <td>${backup.file_name}</td>
                <td>${backup.size_formatted}</td>
                <td>Manual Backup</td>
                <td class="text-center">
                    <button class="backup-action download-btn" onclick="backupManager.downloadBackup('${backup.file_name}')" title="Download Backup">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="backup-action restore-btn" onclick="backupManager.confirmRestore('${backup.file_name}')" title="Restore Backup">
                        <i class="fas fa-upload"></i>
                    </button>
                    <button class="backup-action delete" onclick="backupManager.confirmDelete('${backup.file_name}')" title="Delete Backup">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    renderBackupHistoryError(error) {
        const tbody = document.getElementById('backupHistoryTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="no-backups-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Error Loading Backups</h4>
                    <p>${error}</p>
                    <button class="btn-retry" onclick="backupManager.loadBackupHistory()">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </td>
            </tr>
        `;
    }

    async loadBackupInfo() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_backup_info');
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            const response = await fetch('../../../app/Controllers/BackupController.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.updateBackupInfo(result.info);
            } else {
                throw new Error(result.error || 'Failed to load backup info');
            }

        } catch (error) {
            console.error('Backup info load error:', error);
        }
    }

    updateBackupInfo(info) {
        document.getElementById('lastBackupDate').textContent = info.last_backup;
        document.getElementById('backupSize').textContent = info.last_backup_size;
        document.getElementById('autoBackupStatus').textContent = info.auto_backup_status;
        document.getElementById('nextBackupDate').textContent = info.next_backup_date;
        
        // Update backup location if element exists
        const backupPathElement = document.querySelector('.backup-path');
        if (backupPathElement) {
            backupPathElement.textContent = info.backup_location;
        }
    }

    confirmDelete(backupFileName) {
        Swal.fire({
            title: 'Delete Backup?',
            text: `Are you sure you want to delete ${backupFileName}? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            
        }).then((result) => {
            if (result.isConfirmed) {
                this.deleteBackup(backupFileName);
            }
        });
    }

    async deleteBackup(backupFileName) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete_backup');
            formData.append('backup_file', backupFileName);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            const response = await fetch('../../../app/Controllers/BackupController.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Backup deleted successfully!', 'success');
                this.loadBackupInfo();
                this.loadBackupHistory();
            } else {
                throw new Error(result.error || 'Failed to delete backup');
            }

        } catch (error) {
            console.error('Backup deletion error:', error);
            this.showNotification('Error deleting backup: ' + error.message, 'error');
        }
    }

    downloadBackup(backupFileName) {
        // Only download if user explicitly requests it
        if (!confirm(`Download backup file: ${backupFileName}?`)) {
            return;
        }
        
        // Create download link
        const downloadUrl = `../../../app/Controllers/BackupController.php?action=download&file=${encodeURIComponent(backupFileName)}&csrf_token=${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = backupFileName;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.showNotification('Download started for: ' + backupFileName, 'success');
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    showNotification(message, type = 'info') {
        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        toast.fire({
            icon: type,
            title: message
        });
    }

    
}

async function debugBackupRequest() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_backup_history');
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const response = await fetch('../../../app/Controllers/BackupController.php', {
            method: 'POST',
            body: formData
        });
        
        const text = await response.text();
        console.log('Raw response:', text);
        
        // Check what the response actually contains
        if (text.includes('<br />') || text.includes('<b>')) {
            console.log('HTML ERROR DETECTED!');
            // Try to find the actual error message
            const errorMatch = text.match(/<b>([^<]+)<\/b>/);
            if (errorMatch) {
                console.log('PHP Error:', errorMatch[1]);
            }
        }
        
    } catch (error) {
        console.error('Debug request failed:', error);
    }
}

// Initialize Backup Manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.backupManager = new BackupManager();
});

