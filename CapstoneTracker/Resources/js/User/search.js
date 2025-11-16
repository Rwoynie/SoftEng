function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.select');
    
    dropdowns.forEach(dropdown => {
        const selected = dropdown.querySelector('.selected');
        const optionsContainer = dropdown.querySelector('.options');
        
        if (!selected || !optionsContainer) return;

        // Toggle dropdown
        selected.addEventListener('click', function(e) {
            e.stopPropagation();
            const isActive = dropdown.classList.toggle('active');
            
            // Close others
            dropdowns.forEach(other => {
                if (other !== dropdown) other.classList.remove('active');
            });
        });

        // Select option
        optionsContainer.querySelectorAll('div[data-value]').forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.childNodes[0].nodeType === 3 
                    ? this.childNodes[0].textContent.trim() 
                    : this.querySelector('span:not(.count)')?.textContent.trim() || this.textContent.trim();

                // Update display
                selected.querySelector('span').textContent = text;

                // Update hidden field
                if (dropdown.id === 'filterDropdown') {
                    document.getElementById('hidden-department').value = value;
                } else if (dropdown.id === 'sortDropdown') {
                    document.getElementById('hidden-sort').value = value;
                }

                dropdown.classList.remove('active');

                // Auto-submit
                setTimeout(() => {
                    const form = document.getElementById('search-form');
                    if (form) {
                        const pageInput = form.querySelector('input[name="page"]');
                        if (pageInput) pageInput.value = '1';
                        form.submit();
                    }
                }, 200);
            });
        });
    });

    // Close on outside click
    document.addEventListener('click', () => {
        dropdowns.forEach(d => d.classList.remove('active'));
    });
}

// Citation functionality
function copyCitation(event, thesisId, title, authors, year) {
    event.stopPropagation(); // Prevent opening modal
    
    // Format authors for APA citation
    const authorList = formatAuthorsForAPA(authors);
    
    // Create APA 7th edition citation
    const citation = `${authorList} (${year}). ${title}. University of Southeastern Philippines.`;
    
    // Copy to clipboard
    navigator.clipboard.writeText(citation).then(() => {
        // Show copied state
        const button = event.currentTarget;
        const originalText = button.innerHTML;
        const originalBackground = button.style.background;
        
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('copied');
        }, 2000);
        
    }).catch(err => {
        console.error('Failed to copy citation: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = citation;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        const button = event.currentTarget;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('copied');
        }, 2000);
    });
}

// Function to format authors for APA citation
function formatAuthorsForAPA(authors) {
    const authorArray = authors.split(',').map(author => author.trim());
    
    if (authorArray.length === 0) return '';
    if (authorArray.length === 1) return authorArray[0];
    if (authorArray.length === 2) return `${authorArray[0]} & ${authorArray[1]}`;
    
    return `${authorArray[0]} et al.`;
}

// Modal citation functionality
document.addEventListener('DOMContentLoaded', function() {
    const modalCitationBtn = document.getElementById('modalCitationBtn');
    
    if (modalCitationBtn) {
        modalCitationBtn.addEventListener('click', function() {
            const title = document.getElementById('modalThesisTitle').textContent;
            const authors = document.getElementById('modalThesisAuthors').textContent;
            const date = document.getElementById('modalThesisDate').textContent;
            
            // Extract year from date
            const year = new Date(date).getFullYear() || new Date().getFullYear();
            
            // Format authors for APA citation
            const authorList = formatAuthorsForAPA(authors);
            
            // Create APA 7th edition citation
            const citation = `${authorList} (${year}). ${title}. University of Southeastern Philippines.`;
            
            // Copy to clipboard
            navigator.clipboard.writeText(citation).then(() => {
                const originalText = modalCitationBtn.innerHTML;
                
                modalCitationBtn.innerHTML = '<i class="fas fa-check"></i> Citation Copied!';
                modalCitationBtn.classList.add('copied');
                
                setTimeout(() => {
                    modalCitationBtn.innerHTML = originalText;
                    modalCitationBtn.classList.remove('copied');
                }, 2000);
                
            }).catch(err => {
                console.error('Failed to copy citation: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = citation;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                const originalText = modalCitationBtn.innerHTML;
                
                modalCitationBtn.innerHTML = '<i class="fas fa-check"></i> Citation Copied!';
                modalCitationBtn.classList.add('copied');
                
                setTimeout(() => {
                    modalCitationBtn.innerHTML = originalText;
                    modalCitationBtn.classList.remove('copied');
                }, 2000);
            });
        });
    }
});

// View toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeDropdowns();
    
    const listViewIcon = document.getElementById('listViewIcon');
    const gridViewIcon = document.getElementById('gridViewIcon');
    const resultsContainer = document.getElementById('thesis-results');
    
    if (listViewIcon && gridViewIcon && resultsContainer) {
        // Load saved preference
        const savedView = localStorage.getItem('viewPreference') || 'grid';
        if (savedView === 'list') {
            resultsContainer.classList.remove('grid');
            resultsContainer.classList.add('list');
            listViewIcon.classList.add('selected');
            gridViewIcon.classList.remove('selected');
        } else {
            resultsContainer.classList.remove('list');
            resultsContainer.classList.add('grid');
            gridViewIcon.classList.add('selected');
            listViewIcon.classList.remove('selected');
        }
        
        listViewIcon.addEventListener('click', function() {
            resultsContainer.classList.remove('grid');
            resultsContainer.classList.add('list');
            listViewIcon.classList.add('selected');
            gridViewIcon.classList.remove('selected');
            localStorage.setItem('viewPreference', 'list');
        });
        
        gridViewIcon.addEventListener('click', function() {
            resultsContainer.classList.remove('list');
            resultsContainer.classList.add('grid');
            gridViewIcon.classList.add('selected');
            listViewIcon.classList.remove('selected');
            localStorage.setItem('viewPreference', 'grid');
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const modal = document.getElementById('thesisModal');
    const closeBtn = document.querySelector('.close');
    const closeModalBtn = document.querySelector('.close-modal');
    const viewFullThesisBtn = document.getElementById('viewFullThesis');
    
    // Thesis cards
    const thesisCards = document.querySelectorAll('.thesis-card[data-thesis-id]');
    
    // Open modal when thesis card is clicked
    thesisCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't open modal if clicking on links or buttons
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }
            
            const thesisId = this.getAttribute('data-thesis-id');
            openThesisModal(this, thesisId);
        });
    });
    
    // Close modal functions
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    // View full thesis button
    if (viewFullThesisBtn) {
        viewFullThesisBtn.addEventListener('click', function() {
            const thesisId = this.getAttribute('data-thesis-id');
            if (thesisId) {
                // Redirect to login page for full thesis access
                window.location.href = `../User/indexLogin.php?thesis=${thesisId}`;
            } else {
                window.location.href = '../User/indexLogin.php';
            }
        });
    }
    
    function openThesisModal(cardElement, thesisId) {
        // Get data from the card
        const title = cardElement.querySelector('h3').textContent;
        const authors = cardElement.querySelector('.authors span').textContent;
        const adviser = cardElement.querySelector('.adviser span').textContent;
        const department = cardElement.querySelector('.department-badge').textContent;
        const date = cardElement.querySelector('.upload-date span').textContent;
        
        // Get the full abstract from the database via AJAX
        fetchFullAbstract(thesisId).then(fullAbstract => {
            // Populate modal
            document.getElementById('modalThesisTitle').textContent = title;
            document.getElementById('modalThesisAuthors').textContent = authors;
            document.getElementById('modalThesisAdviser').textContent = adviser;
            document.getElementById('modalThesisDepartment').textContent = department;
            document.getElementById('modalThesisDate').textContent = date;
            document.getElementById('modalThesisAbstract').textContent = fullAbstract;
            
            // Store thesis ID for the view full button
            if (viewFullThesisBtn) {
                viewFullThesisBtn.setAttribute('data-thesis-id', thesisId);
            }
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }).catch(error => {
            console.error('Error fetching abstract:', error);
            // Fallback to preview text
            const abstractPreview = cardElement.querySelector('.abstract-preview').textContent;
            document.getElementById('modalThesisTitle').textContent = title;
            document.getElementById('modalThesisAuthors').textContent = authors;
            document.getElementById('modalThesisAdviser').textContent = adviser;
            document.getElementById('modalThesisDepartment').textContent = department;
            document.getElementById('modalThesisDate').textContent = date;
            document.getElementById('modalThesisAbstract').textContent = abstractPreview;
            
            if (viewFullThesisBtn) {
                viewFullThesisBtn.setAttribute('data-thesis-id', thesisId);
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
    
    function fetchFullAbstract(thesisId) {
        return new Promise((resolve, reject) => {
            // Create a simple AJAX request to fetch the full abstract
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_abstract.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            resolve(response.abstract);
                        } else {
                            reject(new Error(response.error || 'Failed to fetch abstract'));
                        }
                    } catch (e) {
                        reject(new Error('Invalid response format'));
                    }
                } else {
                    reject(new Error('Request failed'));
                }
            };
            
            xhr.onerror = function() {
                reject(new Error('Network error'));
            };
            
            xhr.send(`thesis_id=${encodeURIComponent(thesisId)}`);
        });
    }
    
    // Optional: Add smooth scrolling for abstract content
    const abstractContent = document.getElementById('modalThesisAbstract');
    if (abstractContent) {
        abstractContent.addEventListener('touchstart', function() {
            this.style.overflowY = 'auto';
        });
    }
});