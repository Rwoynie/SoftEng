function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.select');
    
    dropdowns.forEach(dropdown => {
        const selected = dropdown.querySelector('.selected');
        const optionsContainer = dropdown.querySelector('.options');
        
        if (!selected || !optionsContainer) return;

        selected.addEventListener('click', function(e) {
            e.stopPropagation();
            const isActive = dropdown.classList.toggle('active');
            
            dropdowns.forEach(other => {
                if (other !== dropdown) other.classList.remove('active');
            });
        });

        optionsContainer.querySelectorAll('div[data-value]').forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                
                let text = this.childNodes[0].nodeType === 3 
                    ? this.childNodes[0].textContent.trim() 
                    : this.querySelector('span:not(.count)')?.textContent.trim() || this.textContent.trim();
                
                text = text.replace(/\s*\(\d+\)$/, '');
                
                selected.querySelector('span').textContent = text;

                if (dropdown.id === 'filterDropdown') {
                    document.getElementById('hidden-department').value = value;
                } else if (dropdown.id === 'sortDropdown') {
                    document.getElementById('hidden-sort').value = value;
                }

                dropdown.classList.remove('active');

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

    document.addEventListener('click', () => {
        dropdowns.forEach(d => d.classList.remove('active'));
    });
}

function copyCitation(event, thesisId, title, authors, year) {
    event.stopPropagation();
    
    const authorList = formatAuthorsForAPA(authors);
    const citation = `${authorList} (${year}). ${title}. University of Southeastern Philippines.`;
    
    navigator.clipboard.writeText(citation).then(() => {
        const button = event.currentTarget;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.add('copied');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('copied');
        }, 2000);
        
    }).catch(err => {
        console.error('Failed to copy citation: ', err);
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

function formatAuthorsForAPA(authors) {
    const authorArray = authors.split(',').map(author => author.trim());
    
    if (authorArray.length === 0) return '';
    if (authorArray.length === 1) return authorArray[0];
    if (authorArray.length === 2) return `${authorArray[0]} & ${authorArray[1]}`;
    
    return `${authorArray[0]} et al.`;
}

document.addEventListener('DOMContentLoaded', function() {
    initializeDropdowns();
    
    const modal = document.getElementById('thesisModal');
    const closeBtn = document.querySelector('.close');
    const closeModalBtn = document.querySelector('.close-modal');
    const viewFullThesisBtn = document.getElementById('viewFullThesis');
    const listViewIcon = document.getElementById('listViewIcon');
    const gridViewIcon = document.getElementById('gridViewIcon');
    const resultsContainer = document.getElementById('thesis-results');
    const modalCitationBtn = document.getElementById('modalCitationBtn');
    const thesisCards = document.querySelectorAll('.thesis-card[data-thesis-id]');
    
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        modal.style.userSelect = 'auto';
        modal.style.webkitUserSelect = 'auto';
        modal.style.mozUserSelect = 'auto';
        modal.style.msUserSelect = 'auto';
        
        if (window.currentModalPdfUrl) {
            URL.revokeObjectURL(window.currentModalPdfUrl);
            window.currentModalPdfUrl = null;
        }
    }
    
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    if (viewFullThesisBtn) {
        viewFullThesisBtn.addEventListener('click', function() {
            const thesisId = this.getAttribute('data-thesis-id');
            if (thesisId) {
                window.location.href = `../User/indexLogin.php?thesis=${thesisId}`;
            } else {
                window.location.href = '../User/indexLogin.php';
            }
        });
    }
    
    if (modalCitationBtn) {
        modalCitationBtn.addEventListener('click', function() {
            const title = document.getElementById('modalThesisTitle').textContent;
            const authors = document.getElementById('modalThesisAuthors').textContent;
            const date = document.getElementById('modalThesisDate').textContent;
            
            const year = new Date(date).getFullYear() || new Date().getFullYear();
            const authorList = formatAuthorsForAPA(authors);
            const citation = `${authorList} (${year}). ${title}. University of Southeastern Philippines.`;
            
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
    
    if (listViewIcon && gridViewIcon && resultsContainer) {
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
    
    thesisCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a') || e.target.closest('.citation-btn')) {
                return;
            }
            
            const thesisId = this.getAttribute('data-thesis-id');
            openThesisModal(this, thesisId);
        });
    });
    
    function openThesisModal(cardElement, thesisId) {
        const title = cardElement.querySelector('h3').textContent;
        const authors = cardElement.querySelector('.authors span').textContent;
        const adviser = cardElement.querySelector('.adviser span').textContent;
        const department = cardElement.querySelector('.department-badge').textContent;
        const date = cardElement.querySelector('.upload-date span').textContent;
        
        document.getElementById('modalThesisTitle').textContent = title;
        document.getElementById('modalThesisAuthors').textContent = authors;
        document.getElementById('modalThesisAdviser').textContent = adviser;
        document.getElementById('modalThesisDepartment').textContent = department;
        document.getElementById('modalThesisDate').textContent = date;
        
        const viewFullThesisBtn = document.getElementById('viewFullThesis');
        if (viewFullThesisBtn) {
            viewFullThesisBtn.setAttribute('data-thesis-id', thesisId);
        }
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        addModalProtection();
        
        showProtectedAbstractPdf(thesisId, title)
        .catch(error => {
            console.error('Error fetching abstract PDF:', error);
            const abstractContainer = document.getElementById('modalThesisAbstract');
            if (abstractContainer) {
                const abstractPreview = cardElement.querySelector('.abstract-preview')?.textContent || 'Abstract preview not available.';
                abstractContainer.innerHTML = 
                    `<div class="abstract-error">
                        <p>Could not load protected abstract. Showing preview instead.</p>
                        <div class="abstract-text">${abstractPreview}</div>
                    </div>`;
            }
        });
    }

    function addModalProtection() {
        modal.style.userSelect = 'none';
        modal.style.webkitUserSelect = 'none';
        modal.style.mozUserSelect = 'none';
        modal.style.msUserSelect = 'none';
        
        modal.addEventListener('copy', (e) => {
            e.preventDefault();
            showProtectionWarning('Copying content is disabled.');
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'PrintScreen') {
                e.preventDefault();
                showProtectionWarning('Screenshots are discouraged for protected content.');
            }
        });
    }

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
            document.getElementById('modalThesisAbstract').innerHTML = 
                '<div class="loading-preview"><i class="fas fa-spinner fa-spin"></i><p>Loading protected abstract...</p></div>';
            
            await loadPdfJs();
            
            const response = await fetch(`../../../app/Controllers/ThesisController.php?action=downloadAbstract&id=${thesisId}`);
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }
            
            const blob = await response.blob();
            
            if (blob.size === 0) {
                throw new Error('Abstract file is empty');
            }
            
            const arrayBuffer = await blob.arrayBuffer();
            await renderPdfAsImages(arrayBuffer, title);
            
        } catch (error) {
            console.error('Error fetching abstract:', error);
            throw new Error(`Failed to load abstract: ${error.message}`);
        }
    }

    async function renderPdfAsImages(arrayBuffer, title) {
        const abstractContainer = document.getElementById('modalThesisAbstract');
        
        if (!abstractContainer) {
            throw new Error('Abstract container not found in DOM');
        }
        
        try {
            const pdfDoc = await pdfjsLib.getDocument(arrayBuffer).promise;
            const totalPages = pdfDoc.numPages;
            
            abstractContainer.innerHTML = '';
            
            const pagesContainer = document.createElement('div');
            pagesContainer.className = 'pdf-pages-container';
            
            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                const page = await pdfDoc.getPage(pageNum);
                
                const pageContainer = document.createElement('div');
                pageContainer.className = 'pdf-page-container';
                
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                const viewport = page.getViewport({ scale: 1.5 });
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                
                await page.render({
                    canvasContext: ctx,
                    viewport: viewport
                }).promise;
                
                addWatermarkToCanvas(canvas, ctx, `Confidential - ${title}`);
                
                const imageDataUrl = canvas.toDataURL('image/png');
                
                const img = document.createElement('img');
                img.src = imageDataUrl;
                img.className = 'pdf-page-image';
                img.alt = `Abstract page ${pageNum}`;
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '4px';
                img.style.marginBottom = '1rem';
                
                addImageProtection(img);
                
                pageContainer.appendChild(img);
                pagesContainer.appendChild(pageContainer);
            }
            
            abstractContainer.appendChild(pagesContainer);
            
            const notice = document.createElement('div');
            notice.className = 'protection-notice';
            notice.innerHTML = `
                <p><i class="fas fa-shield-alt"></i> <strong>Protected Content:</strong> This abstract is displayed as images to prevent text copying. Downloading is disabled.</p>
            `;
            abstractContainer.appendChild(notice);
            
        } catch (error) {
            if (abstractContainer) {
                abstractContainer.innerHTML = `<div class="abstract-error">
                    <p>Failed to render PDF: ${error.message}</p>
                </div>`;
            }
            throw new Error(`Failed to render PDF: ${error.message}`);
        }
    }

    function addWatermarkToCanvas(canvas, ctx, watermarkText) {
        const width = canvas.width;
        const height = canvas.height;
        
        ctx.save();
        
        ctx.globalAlpha = 0.3;
        ctx.fillStyle = '#d5d5d5';
        ctx.font = 'bold 48px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        ctx.translate(width / 2, height / 2);
        ctx.rotate(-45 * Math.PI / 180);
        
        for (let y = -height; y < height * 2; y += 200) {
            for (let x = -width; x < width * 2; x += 400) {
                ctx.fillText(watermarkText, x, y);
            }
        }
        
        ctx.restore();
    }

    function addImageProtection(img) {
        if (!img || !img.parentNode) return;
        
        img.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            showProtectionWarning('Right-click is disabled to protect content.');
        });
        
        img.addEventListener('dragstart', (e) => {
            e.preventDefault();
        });
        
        img.style.position = 'relative';
        
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

    function showProtectionWarning(message) {
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
        
        setTimeout(() => {
            document.body.removeChild(warning);
        }, 3000);
    }
});

function changePage(page) {
    const form = document.getElementById('search-form');
    const pageInput = document.createElement('input');
    pageInput.type = 'hidden';
    pageInput.name = 'page';
    pageInput.value = page;
    form.appendChild(pageInput);
    form.submit();
}