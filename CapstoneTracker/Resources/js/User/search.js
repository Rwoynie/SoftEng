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
        // Check if mobile device
        const isMobile = window.innerWidth <= 575;
        
        if (isMobile) {
            // Force grid view on mobile
            resultsContainer.classList.remove('list');
            resultsContainer.classList.add('grid');
            if (gridViewIcon) gridViewIcon.classList.add('selected');
            if (listViewIcon) listViewIcon.classList.remove('selected');
        } else {
            // Load saved preference for desktop
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
        
        // Populate basic modal info first
        document.getElementById('modalThesisTitle').textContent = title;
        document.getElementById('modalThesisAuthors').textContent = authors;
        document.getElementById('modalThesisAdviser').textContent = adviser;
        document.getElementById('modalThesisDepartment').textContent = department;
        document.getElementById('modalThesisDate').textContent = date;
        
        // Store thesis ID for the view full button
        const viewFullThesisBtn = document.getElementById('viewFullThesis');
        if (viewFullThesisBtn) {
            viewFullThesisBtn.setAttribute('data-thesis-id', thesisId);
        }
        
        // Show modal immediately with loading state
        const modal = document.getElementById('thesisModal');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Clean up previous PDF URL if exists
        if (window.currentModalPdfUrl) {
            URL.revokeObjectURL(window.currentModalPdfUrl);
            window.currentModalPdfUrl = null;
        }
        
        // Show PDF abstract in modal
        showAbstractPdfInModal(thesisId, title)
            .catch(error => {
                console.error('Error fetching abstract PDF:', error);
                // Fallback to preview text
                const abstractPreview = cardElement.querySelector('.abstract-preview').textContent;
                document.getElementById('modalThesisAbstract').innerHTML = 
                    `<div class="abstract-error">
                        <p><strong>Note:</strong> Could not load abstract PDF. Showing preview instead.</p>
                        <div class="abstract-text">${abstractPreview}</div>
                    </div>`;
            });
    }

    
    function closeModal() {
        const modal = document.getElementById('thesisModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Re-enable text selection
        modal.style.userSelect = 'auto';
        modal.style.webkitUserSelect = 'auto';
        modal.style.mozUserSelect = 'auto';
        modal.style.msUserSelect = 'auto';
        
        // Remove event listeners
        const newModal = modal.cloneNode(true);
        modal.parentNode.replaceChild(newModal, modal);
    }
    
    
    // Optional: Add smooth scrolling for abstract content
    const abstractContent = document.getElementById('modalThesisAbstract');
    if (abstractContent) {
        abstractContent.addEventListener('touchstart', function() {
            this.style.overflowY = 'auto';
        });
    }

    // PROTECTION PDF FUNCTIONS
    function loadPdfJs() {
        return new Promise((resolve, reject) => {
            if (typeof pdfjsLib !== 'undefined') {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js';
            script.onload = () => {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
                resolve();
            };
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function showProtectedAbstractPdf(thesisId, title) {
        try {
            // Show loading state
            document.getElementById('modalThesisAbstract').innerHTML = 
                '<div class="loading-preview"><i class="fas fa-spinner fa-spin"></i><p>Loading protected abstract...</p></div>';
            
            // Load PDF.js if needed
            await loadPdfJs();
            
            // Fetch the abstract file
            const response = await fetch(`../../../app/Controllers/ThesisController.php?action=downloadAbstract&id=${thesisId}`);
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }
            
            const blob = await response.blob();
            
            if (blob.size === 0) {
                throw new Error('Abstract file is empty');
            }
            
            // Convert blob to array buffer for PDF.js
            const arrayBuffer = await blob.arrayBuffer();
            
            // Render PDF as images with watermark
            await renderPdfAsImages(arrayBuffer, title);
            
        } catch (error) {
            console.error('Error fetching abstract:', error);
            throw new Error(`Failed to load abstract: ${error.message}`);
        }
    }

    async function renderPdfAsImages(arrayBuffer, title) {
        const abstractContainer = document.getElementById('modalThesisAbstract');
        
        // Check if container exists before proceeding
        if (!abstractContainer) {
            throw new Error('Abstract container not found in DOM');
        }
        
        try {
            // Load PDF document
            const pdfDoc = await pdfjsLib.getDocument(arrayBuffer).promise;
            const totalPages = pdfDoc.numPages;
            
            // Clear container
            abstractContainer.innerHTML = '';
            
            // Create container for all pages
            const pagesContainer = document.createElement('div');
            pagesContainer.className = 'pdf-pages-container';
            
            // Render each page as image
            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                const page = await pdfDoc.getPage(pageNum);
                
                // Create page container
                const pageContainer = document.createElement('div');
                pageContainer.className = 'pdf-page-container';
                
                // Create canvas for rendering
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Set canvas size based on PDF page
                const viewport = page.getViewport({ scale: 1.5 });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                
                // Render page to canvas
                await page.render({
                    canvasContext: ctx,
                    viewport: viewport
                }).promise;
                
                // Add watermark to the canvas
                addWatermarkToCanvas(canvas, ctx, `Confidential - ${title}`);
                
                // Convert canvas to image (makes text copying harder)
                const imageDataUrl = canvas.toDataURL('image/png');
                
                // Create image element
                const img = document.createElement('img');
                img.src = imageDataUrl;
                img.className = 'pdf-page-image';
                img.alt = `Abstract page ${pageNum}`;
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '4px';
                img.style.marginBottom = '1rem';
                
                // Add copy protection to image
                addImageProtection(img);
                
                pageContainer.appendChild(img);
                pagesContainer.appendChild(pageContainer);
            }
            
            abstractContainer.appendChild(pagesContainer);
            
            // Add download restrictions notice
            const notice = document.createElement('div');
            notice.className = 'protection-notice';
            notice.innerHTML = `
                <p><i class="fas fa-shield-alt"></i> <strong>Protected Content:</strong> This abstract is displayed as images to prevent text copying. Downloading is disabled.</p>
            `;
            abstractContainer.appendChild(notice);
            
        } catch (error) {
            // Clear container and show error
            if (abstractContainer) {
                abstractContainer.innerHTML = `<div class="abstract-error">
                    <p><strong>Error:</strong> Failed to render PDF: ${error.message}</p>
                </div>`;
            }
            throw new Error(`Failed to render PDF: ${error.message}`);
        }
    }

    // Add watermark to canvas
function addWatermarkToCanvas(canvas, ctx, watermarkText) {
    const width = canvas.width;
    const height = canvas.height;
   
    
    // Save current context state
    ctx.save();
    
    // Set watermark style
    ctx.globalAlpha = 0.3; // Semi-transparent
    ctx.fillStyle = '#d5d5d5'; // Red color
    ctx.font = 'bold 48px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // Rotate watermark
    ctx.translate(width / 2, height / 2);
    ctx.rotate(-45 * Math.PI / 180);
    
    // Add multiple watermarks across the page
    for (let y = -height; y < height * 2; y += 200) {
        for (let x = -width; x < width * 2; x += 400) {
            ctx.fillText(watermarkText, x, y);
        }
    }
    
    // Restore context
    ctx.restore();
}

// Add protection to images
function addImageProtection(img) {
    if (!img || !img.parentNode) return;
    
    // Disable right-click
    img.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        showProtectionWarning('Right-click is disabled to protect content.');
    });
    
    // Disable drag
    img.addEventListener('dragstart', (e) => {
        e.preventDefault();
    });
    
    // Add overlay to prevent easy screenshot cropping
    img.style.position = 'relative';
    
    // Create transparent overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'absolute';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.pointerEvents = 'none';
    overlay.style.background = 'transparent';
    overlay.style.zIndex = '1';
    
    img.parentNode.style.position = 'relative';
    img.parentNode.appendChild(overlay);
}

// Show protection warning
function showProtectionWarning(message) {
    // Create temporary warning message
    const warning = document.createElement('div');
    warning.className = 'protection-warning';
    warning.textContent = message;
    warning.style.position = 'fixed';
    warning.style.top = '20px';
    warning.style.left = '50%';
    warning.style.transform = 'translateX(-50%)';
    warning.style.background = '#e74c3c';
    warning.style.color = 'white';
    warning.style.padding = '10px 20px';
    warning.style.borderRadius = '4px';
    warning.style.zIndex = '10000';
    warning.style.boxShadow = '0 2px 10px rgba(0,0,0,0.3)';
    
    document.body.appendChild(warning);
    
    // Remove after 3 seconds
    setTimeout(() => {
        document.body.removeChild(warning);
    }, 3000);
}

// Updated openThesisModal function
function openThesisModal(cardElement, thesisId) {
    // Get data from the card
    const title = cardElement.querySelector('h3').textContent;
    const authors = cardElement.querySelector('.authors span').textContent;
    const adviser = cardElement.querySelector('.adviser span').textContent;
    const department = cardElement.querySelector('.department-badge').textContent;
    const date = cardElement.querySelector('.upload-date span').textContent;
    
    // Populate basic modal info first
    document.getElementById('modalThesisTitle').textContent = title;
    document.getElementById('modalThesisAuthors').textContent = authors;
    document.getElementById('modalThesisAdviser').textContent = adviser;
    document.getElementById('modalThesisDepartment').textContent = department;
    document.getElementById('modalThesisDate').textContent = date;
    
    // Store thesis ID for the view full button
    const viewFullThesisBtn = document.getElementById('viewFullThesis');
    if (viewFullThesisBtn) {
        viewFullThesisBtn.setAttribute('data-thesis-id', thesisId);
    }
    
    // Show modal immediately with loading state
    const modal = document.getElementById('thesisModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Add copy protection to entire modal
    addModalProtection();
    
    // Show protected PDF abstract in modal
    showProtectedAbstractPdf(thesisId, title)
    .catch(error => {
        console.error('Error fetching abstract PDF:', error);
        const abstractContainer = document.getElementById('modalThesisAbstract');
        if (abstractContainer) {
            const abstractPreview = cardElement.querySelector('.abstract-preview')?.textContent || 'Abstract preview not available.';
            abstractContainer.innerHTML = 
                `<div class="abstract-error">
                    <p><strong>Note:</strong> Could not load protected abstract. Showing preview instead.</p>
                    <div class="abstract-text">${abstractPreview}</div>
                </div>`;
        }
    });
}

// Add protection to entire modal
function addModalProtection() {
    const modal = document.getElementById('thesisModal');
    
    // Disable text selection in modal
    modal.style.userSelect = 'none';
    modal.style.webkitUserSelect = 'none';
    modal.style.mozUserSelect = 'none';
    modal.style.msUserSelect = 'none';
    
    // Disable copy in modal
    modal.addEventListener('copy', (e) => {
        e.preventDefault();
        showProtectionWarning('Copying content is disabled.');
    });
    
    // Disable print screen (limited effectiveness)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'PrintScreen') {
            e.preventDefault();
            showProtectionWarning('Screenshots are discouraged for protected content.');
        }
    });
}
});