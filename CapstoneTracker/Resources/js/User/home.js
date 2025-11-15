let landingPage;
let carouselInstances = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function () {
    initializeDOMElements();
    initializeCarousels();
    initializeEventListeners();
    
    // Initialize search page if we're on search page
    if (document.getElementById('results-page')) {
        initializeSearchPage();
    }
});

function initializeDOMElements() {
    landingPage = document.getElementById('landing-page');
}

// === FLICKITY-LIKE CAROUSEL WITH WRAP-AROUND ===
class FlickityCarousel {
    constructor(element, options = {}) {
        this.element = element;
        this.container = element.querySelector('.carousel-container');
        this.cards = element.querySelector('.announcement-cards') || element.querySelector('.logo-cards');
        this.prevBtn = element.querySelector('.carousel-control.prev');
        this.nextBtn = element.querySelector('.carousel-control.next');
        this.indicators = element.querySelectorAll('.indicator');
        
        this.options = {
            wrapAround: true,
            cellAlign: 'left',
            contain: false,
            ...options
        };
        
        this.currentIndex = 0;
        this.isDragging = false;
        this.startX = 0;
        this.scrollLeft = 0;
        this.autoPlayInterval = null;
        
        this.init();
    }
    
    init() {
        if (!this.cards || this.cards.children.length === 0) return;
        
        this.cardCount = this.cards.children.length;
        this.cardWidth = this.cards.children[0].offsetWidth + 24; // width + gap
        
        this.setupEventListeners();
        this.updateCarousel();
        
        if (this.cardCount <= 1) {
            this.hideControls();
        }
    }
    
    setupEventListeners() {
        // Previous button
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.previous());
        }
        
        // Next button
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.next());
        }
        
        // Indicators
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => this.select(index));
        });
        
        // Touch/Mouse events for dragging
        this.setupDragEvents();
        
        // Keyboard navigation
        this.container.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.previous();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.next();
            }
        });
        
        // Resize handling
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.cardWidth = this.cards.children[0].offsetWidth + 24;
                this.updateCarousel();
            }, 250);
        });
    }
    
    setupDragEvents() {
        const startDrag = (e) => {
            this.isDragging = true;
            this.startX = (e.pageX || e.touches[0].pageX) - this.cards.offsetLeft;
            this.scrollLeft = this.cards.scrollLeft;
            this.cards.style.scrollBehavior = 'auto';
            this.cards.style.cursor = 'grabbing';
        };
        
        const duringDrag = (e) => {
            if (!this.isDragging) return;
            e.preventDefault();
            const x = (e.pageX || e.touches[0].pageX) - this.cards.offsetLeft;
            const walk = (x - this.startX) * 2;
            this.cards.scrollLeft = this.scrollLeft - walk;
        };
        
        const endDrag = () => {
            this.isDragging = false;
            this.cards.style.cursor = 'grab';
            this.cards.style.scrollBehavior = 'smooth';
            
            // Snap to nearest card
            const scrollPos = this.cards.scrollLeft;
            this.currentIndex = Math.round(scrollPos / this.cardWidth);
            this.updateCarousel();
        };
        
        // Mouse events
        this.cards.addEventListener('mousedown', startDrag);
        this.cards.addEventListener('mousemove', duringDrag);
        this.cards.addEventListener('mouseup', endDrag);
        this.cards.addEventListener('mouseleave', endDrag);
        
        // Touch events
        this.cards.addEventListener('touchstart', startDrag);
        this.cards.addEventListener('touchmove', duringDrag);
        this.cards.addEventListener('touchend', endDrag);
        
        // Improve cursor
        this.cards.style.cursor = 'grab';
    }
    
    next() {
        if (this.options.wrapAround) {
            this.currentIndex = (this.currentIndex + 1) % this.cardCount;
        } else {
            this.currentIndex = Math.min(this.currentIndex + 1, this.cardCount - 1);
        }
        this.updateCarousel();
    }
    
    previous() {
        if (this.options.wrapAround) {
            this.currentIndex = (this.currentIndex - 1 + this.cardCount) % this.cardCount;
        } else {
            this.currentIndex = Math.max(this.currentIndex - 1, 0);
        }
        this.updateCarousel();
    }
    
    select(index) {
        this.currentIndex = index;
        this.updateCarousel();
    }
    
    updateCarousel() {
        const scrollPosition = this.currentIndex * this.cardWidth;
        
        this.cards.scrollTo({
            left: scrollPosition,
            behavior: 'smooth'
        });
        
        // Update indicators
        this.indicators.forEach((indicator, idx) => {
            indicator.classList.toggle('active', idx === this.currentIndex);
        });
        
        // Update ARIA attributes for accessibility
        this.cards.querySelectorAll('.announcement-card, .logo-card').forEach((card, idx) => {
            card.setAttribute('aria-hidden', idx !== this.currentIndex);
            if (idx === this.currentIndex) {
                card.setAttribute('tabindex', '0');
            } else {
                card.removeAttribute('tabindex');
            }
        });
    }
    
    hideControls() {
        if (this.prevBtn) this.prevBtn.style.display = 'none';
        if (this.nextBtn) this.nextBtn.style.display = 'none';
        if (this.indicators[0]) {
            this.indicators[0].parentElement.style.display = 'none';
        }
    }
    
    destroy() {
        // Cleanup event listeners if needed
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
        }
    }
}

function initializeCarousels() {
    const announcementCarousels = document.querySelectorAll('.announcements-carousel');
    const programCarousels = document.querySelectorAll('.logo-carousel');
    
    // Initialize announcement carousels
    announcementCarousels.forEach(carousel => {
        const instance = new FlickityCarousel(carousel, {
            wrapAround: true,
            cellAlign: 'center'
        });
        carouselInstances.push(instance);
    });
    
    // Initialize program carousels
    programCarousels.forEach(carousel => {
        const instance = new FlickityCarousel(carousel, {
            wrapAround: true,
            cellAlign: 'center'
        });
        carouselInstances.push(instance);
    });
}

// === SEARCH PAGE FUNCTIONALITY ===
function initializeSearchPage() {
    initializeDropdowns();
    initializeViewToggle();
    initializeSearchForm();
}

function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.select');
    
    dropdowns.forEach(dropdown => {
        const selected = dropdown.querySelector('.selected');
        const options = dropdown.querySelector('.options');
        
        if (!selected || !options) return;
        
        selected.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');
            
            // Close other dropdowns
            dropdowns.forEach(other => {
                if (other !== dropdown) {
                    other.classList.remove('active');
                }
            });
        });
        
        // Option selection
        options.querySelectorAll('div').forEach(option => {
            option.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.textContent.split('|')[0].trim();
                
                selected.querySelector('span').textContent = text;
                
                // Update hidden input
                if (dropdown.id === 'filterDropdown') {
                    document.getElementById('hidden-department').value = value;
                } else if (dropdown.id === 'sortDropdown') {
                    document.getElementById('hidden-sort').value = value;
                }
                
                dropdown.classList.remove('active');
                
                // Auto-submit form if department or sort changes
                setTimeout(() => {
                    const searchForm = document.getElementById('search-form');
                    if (searchForm) {
                        searchForm.submit();
                    }
                }, 300);
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    });
}

function initializeViewToggle() {
    const listViewIcon = document.getElementById('listViewIcon');
    const gridViewIcon = document.getElementById('gridViewIcon');
    const resultsContainer = document.getElementById('thesis-results');
    
    if (!listViewIcon || !gridViewIcon || !resultsContainer) return;
    
    listViewIcon.addEventListener('click', function() {
        this.classList.add('selected');
        gridViewIcon.classList.remove('selected');
        resultsContainer.classList.remove('grid');
        resultsContainer.classList.add('list');
        
        // Save preference to localStorage
        localStorage.setItem('viewPreference', 'list');
    });
    
    gridViewIcon.addEventListener('click', function() {
        this.classList.add('selected');
        listViewIcon.classList.remove('selected');
        resultsContainer.classList.remove('list');
        resultsContainer.classList.add('grid');
        
        // Save preference to localStorage
        localStorage.setItem('viewPreference', 'grid');
    });
    
    // Load saved preference
    const savedView = localStorage.getItem('viewPreference') || 'grid';
    if (savedView === 'list') {
        listViewIcon.click();
    } else {
        gridViewIcon.click();
    }
}

function initializeSearchForm() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('results-search-input');
    
    if (searchForm && searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
}

// === EVENT LISTENERS ===
function initializeEventListeners() {
    const loginBtn = document.querySelector('.btn-login');
    if (loginBtn) {
        loginBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = this.href;
        });
    }
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    carouselInstances.forEach(instance => {
        instance.destroy();
    });
    carouselInstances = [];
});