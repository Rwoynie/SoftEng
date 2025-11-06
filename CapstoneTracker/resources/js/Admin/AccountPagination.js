class AccountPagination {
    constructor() {
        this.currentPage = 1;
        this.accountsPerPage = 8;
        this.currentFilter = 'all'; // 'all', 'pending', 'approved'
        this.currentSearchTerm = '';
        this.currentRoleFilter = 'recent'; // 'recent', 'admin', 'faculty', 'student'
        this.allAccounts = [];
    }

    initialize() {
        this.loadAccountsData();
        this.initializeEventListeners();
        this.setupPaginationControls();
    }

    loadAccountsData() {
        // Get all account rows from the table
        const accountRows = document.querySelectorAll('.accounts-table tbody tr');
        this.allAccounts = Array.from(accountRows);
        
        // Apply initial pagination
        this.applyPagination();
    }

    initializeEventListeners() {
        // Account filter buttons (All, Pending, Approved)
        const allAccountsButton = document.getElementById('allAccountsButton');
        const pendingButton = document.getElementById('pendingButton');
        const approvedButton = document.getElementById('approvedButton');

        if (allAccountsButton) {
            allAccountsButton.addEventListener('click', () => {
                this.currentFilter = 'all';
                this.currentPage = 1;
                this.applyPagination();
            });
        }

        if (pendingButton) {
            pendingButton.addEventListener('click', () => {
                this.currentFilter = 'pending';
                this.currentPage = 1;
                this.applyPagination();
            });
        }

        if (approvedButton) {
            approvedButton.addEventListener('click', () => {
                this.currentFilter = 'approved';
                this.currentPage = 1;
                this.applyPagination();
            });
        }

        // Account search
        const accountSearchInput = document.getElementById('accountSearchInput');
        if (accountSearchInput) {
            accountSearchInput.addEventListener('input', (e) => {
                this.currentSearchTerm = e.target.value.toLowerCase().trim();
                this.currentPage = 1;
                this.applyPagination();
            });
        }

        // Account filter dropdown
        const accountFilterDropdown = document.getElementById('accountFilterDropdown');
        if (accountFilterDropdown) {
            const options = accountFilterDropdown.querySelectorAll('.options div');
            options.forEach(option => {
                option.addEventListener('click', () => {
                    this.currentRoleFilter = option.getAttribute('data-value');
                    this.currentPage = 1;
                    this.applyPagination();
                    
                    // Update selected text
                    const selectedText = accountFilterDropdown.querySelector('.selected span');
                    selectedText.textContent = option.textContent;
                });
            });
        }
    }

    setupPaginationControls() {
        const prevBtn = document.querySelector('.table-pagination .pagination-btn:first-child');
        const nextBtn = document.querySelector('.table-pagination .pagination-btn:last-child');
        const pageInfo = document.querySelector('.pagination-info');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.applyPagination();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const filteredAccounts = this.getFilteredAccounts();
                const totalPages = Math.ceil(filteredAccounts.length / this.accountsPerPage);
                
                if (this.currentPage < totalPages) {
                    this.currentPage++;
                    this.applyPagination();
                }
            });
        }
    }

    getFilteredAccounts() {
        return this.allAccounts.filter(account => {
            // Apply status filter
            const statusMatch = this.applyStatusFilter(account);
            if (!statusMatch) return false;

            // Apply search filter
            const searchMatch = this.applySearchFilter(account);
            if (!searchMatch) return false;

            // Apply role filter
            const roleMatch = this.applyRoleFilter(account);
            if (!roleMatch) return false;

            return true;
        });
    }

    applyStatusFilter(account) {
        const statusBadge = account.querySelector('.status-badge');
        const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
        
        switch(this.currentFilter) {
            case 'pending':
                return status === 'pending';
            case 'approved':
                return status === 'approved';
            default: // 'all'
                return true;
        }
    }

    applySearchFilter(account) {
        if (!this.currentSearchTerm) return true;

        const name = account.querySelector('td:first-child').textContent.toLowerCase();
        const email = account.querySelector('td:nth-child(2)').textContent.toLowerCase();
        
        return name.includes(this.currentSearchTerm) || email.includes(this.currentSearchTerm);
    }

    applyRoleFilter(account) {
        if (this.currentRoleFilter === 'recent') return true;

        const roleBadge = account.querySelector('.role-badge');
        const role = roleBadge ? roleBadge.textContent.toLowerCase() : '';
        
        switch(this.currentRoleFilter) {
            case 'admin':
                return role === 'admin';
            case 'faculty':
                return role === 'faculty';
            case 'student':
                return role === 'student';
            default:
                return true;
        }
    }

    applyPagination() {
        const filteredAccounts = this.getFilteredAccounts();
        const totalPages = Math.ceil(filteredAccounts.length / this.accountsPerPage);
        
        // Calculate start and end indices
        const startIndex = (this.currentPage - 1) * this.accountsPerPage;
        const endIndex = startIndex + this.accountsPerPage;
        const currentPageAccounts = filteredAccounts.slice(startIndex, endIndex);
    
        // Hide all accounts first
        this.allAccounts.forEach(account => {
            account.style.display = 'none';
        });
    
        // Show only accounts for current page
        currentPageAccounts.forEach(account => {
            account.style.display = '';
        });
    
        // Update pagination controls
        this.updatePaginationUI(totalPages);
    
        // Show no results message if needed
        this.showNoResultsMessage(filteredAccounts.length === 0);
    
        // Setup tooltips for long text
        setTimeout(() => {
            this.setupTableTooltips();
        }, 100);
    }

    updatePaginationUI(totalPages) {
        const prevBtn = document.querySelector('.table-pagination .pagination-btn:first-child');
        const nextBtn = document.querySelector('.table-pagination .pagination-btn:last-child');
        const pageInfo = document.querySelector('.pagination-info');

        // Update button states
        if (prevBtn) {
            prevBtn.disabled = this.currentPage === 1;
        }

        if (nextBtn) {
            nextBtn.disabled = this.currentPage === totalPages || totalPages === 0;
        }

        // Update page info
        if (pageInfo) {
            if (totalPages === 0) {
                pageInfo.textContent = 'No accounts';
            } else {
                pageInfo.textContent = `Page ${this.currentPage} of ${totalPages}`;
            }
        }
    }

    showNoResultsMessage(show) {
        // Remove existing no results message
        const existingMessage = document.querySelector('.no-accounts-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        if (show) {
            const tbody = document.querySelector('.accounts-table tbody');
            const messageRow = document.createElement('tr');
            messageRow.className = 'no-accounts-message';
            messageRow.innerHTML = `
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <div class="no-accounts-found">
                        <i class="fas fa-users" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                        <h3>No Accounts Found</h3>
                        <p>No accounts match your current filters.</p>
                    </div>
                </td>
            `;
            tbody.appendChild(messageRow);
        }
    }

    // Method to refresh accounts data (useful when accounts are added/removed)
    refreshAccounts() {
        this.loadAccountsData();
        this.applyPagination();
    }

    setupTableTooltips() {
        const nameCells = document.querySelectorAll('.accounts-table td:nth-child(1)');
        const emailCells = document.querySelectorAll('.accounts-table td:nth-child(2)');
        
        // Setup tooltips for name cells
        nameCells.forEach(cell => {
            const content = cell.textContent.trim();
            // Always add tooltip if content exists and might be long
            if (content && content.length > 0) {
                cell.setAttribute('data-fulltext', content);
                cell.style.cursor = 'help';
                
                // Force ellipsis for long text
                cell.style.overflow = 'hidden';
                cell.style.whiteSpace = 'nowrap';
                cell.style.textOverflow = 'ellipsis';
            }
        });
        
        // Setup tooltips for email cells (emails are often long)
        emailCells.forEach(cell => {
            const content = cell.textContent.trim();
            if (content && content.length > 0) {
                cell.setAttribute('data-fulltext', content);
                cell.style.cursor = 'help';
                
                // Force ellipsis for long text
                cell.style.overflow = 'hidden';
                cell.style.whiteSpace = 'nowrap';
                cell.style.textOverflow = 'ellipsis';
            }
        });
    }
    
    // Helper method to calculate text width
    getTextWidth(text, font) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        context.font = font;
        const metrics = context.measureText(text);
        return metrics.width;
    }
}