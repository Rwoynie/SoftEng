let landingPage;
let carouselInstances = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function () {
    initializeDOMElements();
    initializeCarousels();
    initializeEventListeners();
    initializeAnnouncementModals();
    initializeProgramClicks(); 

    
    // Initialize search page if we're on search page
    if (document.getElementById('results-page')) {
        initializeSearchPage();
    }
});

function initializeDOMElements() {
    landingPage = document.getElementById('landing-page');
}

// === ENHANCED FLICKITY-LIKE CAROUSEL WITH WRAP-AROUND AND CENTERED DESIGN ===
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
            cellAlign: 'center',
            contain: false,
            autoPlay: element.classList.contains('logo-carousel'), 
            autoPlayDelay: 4000,
            centered: element.classList.contains('logo-carousel'), 
            ...options
        };
        
        this.currentIndex = 0;
        this.isDragging = false;
        this.startX = 0;
        this.scrollLeft = 0;
        this.autoPlayInterval = null;
        this.isProgramCarousel = element.classList.contains('logo-carousel');
        
        this.init();
    }
    
    init() {
        if (!this.cards || this.cards.children.length === 0) return;
        
        this.cardCount = this.cards.children.length;
        this.cardWidth = this.cards.children[0].offsetWidth + 24; 
        
        this.setupEventListeners();
        this.updateCarousel();
        
        if (this.cardCount <= 1) {
            this.hideControls();
        }
        
        // Start auto-play if enabled
        if (this.options.autoPlay) {
            this.startAutoPlay();
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
        
        // Pause auto-play on hover for programs carousel
        if (this.options.autoPlay) {
            this.container.addEventListener('mouseenter', () => this.pauseAutoPlay());
            this.container.addEventListener('mouseleave', () => this.resumeAutoPlay());
        }
        
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
            
            // Pause auto-play during drag
            if (this.options.autoPlay) {
                this.pauseAutoPlay();
            }
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
            
            // Resume auto-play after drag
            if (this.options.autoPlay) {
                this.resumeAutoPlay();
            }
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
        
        // Reset auto-play timer
        if (this.options.autoPlay) {
            this.resetAutoPlay();
        }
    }
    
    previous() {
        if (this.options.wrapAround) {
            this.currentIndex = (this.currentIndex - 1 + this.cardCount) % this.cardCount;
        } else {
            this.currentIndex = Math.max(this.currentIndex - 1, 0);
        }
        this.updateCarousel();
        
        // Reset auto-play timer
        if (this.options.autoPlay) {
            this.resetAutoPlay();
        }
    }
    
    select(index) {
        this.currentIndex = index;
        this.updateCarousel();
        
        // Reset auto-play timer
        if (this.options.autoPlay) {
            this.resetAutoPlay();
        }
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
                card.classList.add('active');
            } else {
                card.removeAttribute('tabindex');
                card.classList.remove('active');
            }
        });
        
        // Apply centered effect for programs carousel
        if (this.isProgramCarousel && this.options.centered) {
            this.applyCenteredEffect();
        }
    }
    
    applyCenteredEffect() {
        const cards = this.cards.querySelectorAll('.logo-card');
        cards.forEach((card, index) => {
            // Reset all cards first
            card.style.transform = 'scale(0.9)';
            card.style.opacity = '0.7';
            card.style.filter = 'blur(2px)';
            card.style.zIndex = '1';
            
            // Apply active state to current card
            if (index === this.currentIndex) {
                card.style.transform = 'scale(1)';
                card.style.opacity = '1';
                card.style.filter = 'blur(0)';
                card.style.zIndex = '2';
            }
        });
    }
    
    startAutoPlay() {
        if (this.cardCount <= 1) return;
        
        this.autoPlayInterval = setInterval(() => {
            this.next();
        }, this.options.autoPlayDelay);
    }
    
    pauseAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
    
    resumeAutoPlay() {
        if (this.options.autoPlay && !this.autoPlayInterval) {
            this.startAutoPlay();
        }
    }
    
    resetAutoPlay() {
        this.pauseAutoPlay();
        this.resumeAutoPlay();
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
    // ---------- PINNED ANNOUNCEMENTS CAROUSEL 
        const pinnedCarousels = document.querySelectorAll('.pinned-announcements-carousel');
        pinnedCarousels.forEach(carousel => {
            const container = carousel.querySelector('.carousel-container');
            const cardsWrap = carousel.querySelector('.announcement-cards');
            const cards = cardsWrap.querySelectorAll('.announcement-card');
            const prevBtn = carousel.querySelector('.carousel-control.prev');
            const nextBtn = carousel.querySelector('.carousel-control.next');
            const indicators = carousel.querySelectorAll('.indicator');

            if (!cards.length) return;

            const CARD_GAP = 24;
            const cardWidth = cards[0].offsetWidth + CARD_GAP;
            const originalCnt = cards.length;

            // If only one pinned card, center it and hide controls
            if (originalCnt <= 1) {
                prevBtn && (prevBtn.style.display = 'none');
                nextBtn && (nextBtn.style.display = 'none');
                indicators[0] && (indicators[0].parentElement.style.display = 'none');
                
                // Center the single card
                cardsWrap.style.display = 'flex';
                cardsWrap.style.justifyContent = 'center';
                cardsWrap.style.width = '100%';
                
                // Make single card active
                cards[0].classList.add('active');
                cards[0].style.transform = 'scale(1)';
                cards[0].style.opacity = '1';
                cards[0].style.filter = 'none';
                cards[0].style.zIndex = '2';
                
                return;
            }

            // For multiple cards, create seamless loop
            const original = Array.from(cards);
            
            // Clone for infinite scroll
            for (let i = originalCnt - 1; i >= 0; i--) {
                cardsWrap.insertBefore(original[i].cloneNode(true), cardsWrap.firstChild);
            }
            original.forEach(c => cardsWrap.appendChild(c.cloneNode(true)));

            const allCards = cardsWrap.querySelectorAll('.announcement-card');
            const total = allCards.length;

            let currentIdx = originalCnt;
            let autoPlayId = null;

            const goTo = (idx, smooth = true) => {
                currentIdx = idx;
                const offset = currentIdx * cardWidth - (container.offsetWidth - cardWidth) / 2;

                cardsWrap.style.transition = smooth ? 'transform 0.4s ease' : 'none';
                cardsWrap.style.transform = `translateX(${-offset}px)`;

                const origIdx = (currentIdx - originalCnt) % originalCnt;
                indicators.forEach((ind, i) => ind.classList.toggle('active', i === origIdx));

                allCards.forEach((c, i) => {
                    const isActive = (i - originalCnt) % originalCnt === origIdx;
                    c.classList.toggle('active', isActive);
                    c.style.transform = isActive ? 'scale(1)' : 'scale(0.9)';
                    c.style.opacity = isActive ? '1' : '0.7';
                    c.style.filter = 'none'; // Remove blur
                    c.style.zIndex = isActive ? '2' : '1';
                });
            };

            cardsWrap.addEventListener('transitionend', () => {
                let shifted = false;

                if (currentIdx < originalCnt) {
                    currentIdx += originalCnt;
                    shifted = true;
                } else if (currentIdx >= originalCnt * 2) {
                    currentIdx -= originalCnt;
                    shifted = true;
                }

                if (shifted) {
                    const offset = currentIdx * cardWidth - (container.offsetWidth - cardWidth) / 2;
                    cardsWrap.style.transition = 'none';
                    cardsWrap.style.transform = `translateX(${-offset}px)`;
                    void cardsWrap.offsetHeight;
                }
            });

            prevBtn?.addEventListener('click', () => { goTo(currentIdx - 1); resetAuto(); });
            nextBtn?.addEventListener('click', () => { goTo(currentIdx + 1); resetAuto(); });

            indicators.forEach((ind, i) => ind.addEventListener('click', () => {
                const targetOriginal = i;
                const currentOriginal = (currentIdx - originalCnt) % originalCnt;
                const diff = targetOriginal - currentOriginal;
                goTo(currentIdx + diff);
                resetAuto();
            }));

            const startAuto = () => {
                autoPlayId = setInterval(() => goTo(currentIdx + 1), 4000);
            };
            const stopAuto = () => clearInterval(autoPlayId);
            const resetAuto = () => { stopAuto(); startAuto(); };

            if (originalCnt > 1) {
                startAuto();
                carousel.addEventListener('mouseenter', stopAuto);
                carousel.addEventListener('mouseleave', startAuto);
            }

            goTo(currentIdx, false);

            carouselInstances.push({
                destroy: () => {
                    stopAuto();
                    while (cardsWrap.children.length > originalCnt) {
                        cardsWrap.removeChild(cardsWrap.firstChild);
                        cardsWrap.removeChild(cardsWrap.lastChild);
                    }
                }
            });
        });

    // ---------- NON-PINNED ANNOUNCEMENTS CAROUSEL (SEAMLESS LOOP, NO CLONING FOR SINGLE ITEMS) ----------
            const nonPinnedCarousels = document.querySelectorAll('.non-pinned-announcements-carousel');
            nonPinnedCarousels.forEach(carousel => {
                const container = carousel.querySelector('.carousel-container');
                const cardsWrap = carousel.querySelector('.announcement-cards');
                const cards = cardsWrap.querySelectorAll('.announcement-card');
                const prevBtn = carousel.querySelector('.carousel-control.prev');
                const nextBtn = carousel.querySelector('.carousel-control.next');
                const indicators = carousel.querySelectorAll('.indicator');

                if (!cards.length) return;

                const CARD_GAP = 24;
                const cardWidth = cards[0].offsetWidth + CARD_GAP;
                const originalCnt = cards.length;

                // If only one card, center it and hide controls
                if (originalCnt <= 1) {
                    prevBtn && (prevBtn.style.display = 'none');
                    nextBtn && (nextBtn.style.display = 'none');
                    indicators[0] && (indicators[0].parentElement.style.display = 'none');
                    
                    // Center the single card
                    cardsWrap.style.display = 'flex';
                    cardsWrap.style.justifyContent = 'center';
                    cardsWrap.style.width = '100%';
                    
                    // Make single card active
                    cards[0].classList.add('active');
                    cards[0].style.transform = 'scale(1)';
                    cards[0].style.opacity = '1';
                    cards[0].style.filter = 'none';
                    cards[0].style.zIndex = '2';
                    
                    return;
                }

                // For multiple cards, create seamless loop
                const original = Array.from(cards);
                
                for (let i = originalCnt - 1; i >= 0; i--) {
                    cardsWrap.insertBefore(original[i].cloneNode(true), cardsWrap.firstChild);
                }
                original.forEach(c => cardsWrap.appendChild(c.cloneNode(true)));

                const allCards = cardsWrap.querySelectorAll('.announcement-card');
                const total = allCards.length;

                let currentIdx = originalCnt;
                let autoPlayId = null;

                const goTo = (idx, smooth = true) => {
                    currentIdx = idx;
                    const offset = currentIdx * cardWidth - (container.offsetWidth - cardWidth) / 2;

                    cardsWrap.style.transition = smooth ? 'transform 0.4s ease' : 'none';
                    cardsWrap.style.transform = `translateX(${-offset}px)`;

                    const origIdx = (currentIdx - originalCnt) % originalCnt;
                    indicators.forEach((ind, i) => ind.classList.toggle('active', i === origIdx));

                    allCards.forEach((c, i) => {
                        const isActive = (i - originalCnt) % originalCnt === origIdx;
                        c.classList.toggle('active', isActive);
                        c.style.transform = isActive ? 'scale(1)' : 'scale(0.9)';
                        c.style.opacity = isActive ? '1' : '0.7';
                        c.style.filter = 'none'; // Remove blur
                        c.style.zIndex = isActive ? '2' : '1';
                    });
                };

                cardsWrap.addEventListener('transitionend', () => {
                    let shifted = false;

                    if (currentIdx < originalCnt) {
                        currentIdx += originalCnt;
                        shifted = true;
                    } else if (currentIdx >= originalCnt * 2) {
                        currentIdx -= originalCnt;
                        shifted = true;
                    }

                    if (shifted) {
                        const offset = currentIdx * cardWidth - (container.offsetWidth - cardWidth) / 2;
                        cardsWrap.style.transition = 'none';
                        cardsWrap.style.transform = `translateX(${-offset}px)`;
                        void cardsWrap.offsetHeight;
                    }
                });

                prevBtn?.addEventListener('click', () => { goTo(currentIdx - 1); resetAuto(); });
                nextBtn?.addEventListener('click', () => { goTo(currentIdx + 1); resetAuto(); });

                indicators.forEach((ind, i) => ind.addEventListener('click', () => {
                    const targetOriginal = i;
                    const currentOriginal = (currentIdx - originalCnt) % originalCnt;
                    const diff = targetOriginal - currentOriginal;
                    goTo(currentIdx + diff);
                    resetAuto();
                }));

                const startAuto = () => {
                    autoPlayId = setInterval(() => goTo(currentIdx + 1), 4000);
                };
                const stopAuto = () => clearInterval(autoPlayId);
                const resetAuto = () => { stopAuto(); startAuto(); };

                if (originalCnt > 1) {
                    startAuto();
                    carousel.addEventListener('mouseenter', stopAuto);
                    carousel.addEventListener('mouseleave', startAuto);
                }

                goTo(currentIdx, false);

                carouselInstances.push({
                    destroy: () => {
                        stopAuto();
                        while (cardsWrap.children.length > originalCnt) {
                            cardsWrap.removeChild(cardsWrap.firstChild);
                            cardsWrap.removeChild(cardsWrap.lastChild);
                        }
                    }
                });
            });

    // ---------- PROGRAMS CAROUSEL (SEAMLESS LOOP) ----------
    const programCarousels = document.querySelectorAll('.logo-carousel');
    programCarousels.forEach(carousel => {
        // ... (keep the existing program carousel code exactly as it was)
        const container = carousel.querySelector('.carousel-container');
        const cardsWrap = carousel.querySelector('.logo-cards');
        const cards = cardsWrap.querySelectorAll('.logo-card');
        const prevBtn = carousel.querySelector('.carousel-control.prev');
        const nextBtn = carousel.querySelector('.carousel-control.next');
        const indicators = carousel.querySelectorAll('.indicator');

        if (!cards.length) return;

        const CARD_GAP = 24;
        const cardWidth = cards[0].offsetWidth + CARD_GAP;
        const original = Array.from(cards);
        const originalCnt = original.length;

        // Clone for infinite scroll
        for (let i = originalCnt - 1; i >= 0; i--) {
            cardsWrap.insertBefore(original[i].cloneNode(true), cardsWrap.firstChild);
        }
        original.forEach(c => cardsWrap.appendChild(c.cloneNode(true)));

        const allCards = cardsWrap.querySelectorAll('.logo-card');
        const total = allCards.length;

        let currentIdx = originalCnt;
        let autoPlayId = null;

        const goTo = (idx, smooth = true) => {
            currentIdx = idx;
            const offset = currentIdx * cardWidth - (container.offsetWidth - cardWidth) / 2;

            cardsWrap.style.transition = smooth ? 'transform 0.4s ease' : 'none';
            cardsWrap.style.transform = `translateX(${-offset}px)`;

            const origIdx = (currentIdx - originalCnt) % originalCnt;
            indicators.forEach((ind, i) => ind.classList.toggle('active', i === origIdx));

            allCards.forEach((c, i) => {
                const isActive = (i - originalCnt) % originalCnt === origIdx;
                c.classList.toggle('active', isActive);
                c.style.transform = isActive ? 'scale(1)' : 'scale(0.9)';
                c.style.opacity = isActive ? '1' : '0.7';
                c.style.filter = isActive ? 'blur(0)' : 'blur(2px)';
                c.style.zIndex = isActive ? '2' : '1';
            });
        };

        cardsWrap.addEventListener('transitionend', () => {
            let shifted = false;

            if (currentIdx < originalCnt) {
                currentIdx += originalCnt;
                shifted = true;
            } else if (currentIdx >= originalCnt * 2) {
                currentIdx -= originalCnt;
                shifted = true;
            }

            if (shifted) {
                const offset = currentIdx * cardWidth - (container.offsetWidth - cardWidth) / 2;
                cardsWrap.style.transition = 'none';
                cardsWrap.style.transform = `translateX(${-offset}px)`;
                void cardsWrap.offsetHeight;
            }
        });

        prevBtn?.addEventListener('click', () => { goTo(currentIdx - 1); resetAuto(); });
        nextBtn?.addEventListener('click', () => { goTo(currentIdx + 1); resetAuto(); });

        indicators.forEach((ind, i) => ind.addEventListener('click', () => {
            const targetOriginal = i;
            const currentOriginal = (currentIdx - originalCnt) % originalCnt;
            const diff = targetOriginal - currentOriginal;
            goTo(currentIdx + diff);
            resetAuto();
        }));

        const startAuto = () => {
            autoPlayId = setInterval(() => goTo(currentIdx + 1), 4000);
        };
        const stopAuto = () => clearInterval(autoPlayId);
        const resetAuto = () => { stopAuto(); startAuto(); };

        if (originalCnt > 1) {
            startAuto();
            carousel.addEventListener('mouseenter', stopAuto);
            carousel.addEventListener('mouseleave', startAuto);
        } else {
            prevBtn && (prevBtn.style.display = 'none');
            nextBtn && (nextBtn.style.display = 'none');
            indicators[0] && (indicators[0].parentElement.style.display = 'none');
        }

        goTo(currentIdx, false);

        carouselInstances.push({
            destroy: () => {
                stopAuto();
                while (cardsWrap.children.length > originalCnt) {
                    cardsWrap.removeChild(cardsWrap.firstChild);
                    cardsWrap.removeChild(cardsWrap.lastChild);
                }
            }
        });
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


function initializeAnnouncementModals() {
    const modal = document.getElementById('announcementModal');
    const readMoreLinks = document.querySelectorAll('.read-more[data-announcement-id]');
    
    // Close modal function with transition
    function closeModal() {
        const modalContent = modal.querySelector('.premium-modal-content');
        const backdrop = modal.querySelector('.premium-modal-backdrop');
        
        // Add closing animations
        modalContent.style.animation = 'premiumModalIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) reverse';
        backdrop.style.animation = 'premiumBackdropIn 0.3s ease reverse';
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset animations
            modalContent.style.animation = '';
            backdrop.style.animation = '';
        }, 250);
    }
    
    // Open modal function with transition
    function openModal(announcementId) {
        const announcementCard = document.querySelector(`[data-announcement-id="${announcementId}"]`);
        if (!announcementCard) return;
        
        // Get announcement data from the card
        const title = announcementCard.querySelector('h3').textContent;
        const date = announcementCard.querySelector('.date').textContent;
        const badge = announcementCard.querySelector('.card-badge').cloneNode(true);
        const imageSrc = announcementCard.querySelector('.Anncmnt_pic').src;
        const content = announcementCard.querySelector('.announcement-preview').textContent;
        const isPinned = announcementCard.classList.contains('pinned');
        
        // Populate modal
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalDate').textContent = date;
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('modalContent').textContent = content;
        
        // Update badge
        const modalBadge = document.getElementById('modalBadge');
        modalBadge.className = 'premium-modal-badge';
        modalBadge.textContent = badge.textContent.trim();
        
        // Show/hide pinned indicator
        const pinIndicator = modal.querySelector('.premium-pin-indicator');
        if (pinIndicator) {
            pinIndicator.style.display = isPinned ? 'flex' : 'none';
        }
        
        // Show modal with animation
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Trigger animations
        setTimeout(() => {
            const modalContent = modal.querySelector('.premium-modal-content');
            const backdrop = modal.querySelector('.premium-modal-backdrop');
            
            modalContent.style.animation = 'premiumModalIn 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            backdrop.style.animation = 'premiumBackdropIn 0.4s ease';
        }, 50);
    }
    
    // Event listeners for read more links
    readMoreLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const announcementId = this.getAttribute('data-announcement-id');
            openModal(announcementId);
        });
    });
    
    // Event listeners for announcement cards
    document.querySelectorAll('.announcement-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.read-more') && !e.target.closest('.pin-indicator')) {
                const announcementId = this.getAttribute('data-announcement-id');
                openModal(announcementId);
            }
        });
    });
    
    // Close modal events
    const closeBtn = modal.querySelector('.premium-close-btn');
    const backdrop = modal.querySelector('.premium-modal-backdrop');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    
    if (backdrop) {
        backdrop.addEventListener('click', closeModal);
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
}

// Share announcement function
function shareAnnouncement() {
    const title = document.getElementById('modalTitle').textContent;
    const text = document.getElementById('modalContent').textContent.slice(0, 100) + '...';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: window.location.href
        }).catch(err => {
            console.log('Error sharing:', err);
        });
    } else {
        // Fallback: copy to clipboard
        const shareText = `${title}\n\n${text}\n\n${window.location.href}`;
        navigator.clipboard.writeText(shareText).then(() => {
            alert('Announcement link copied to clipboard!');
        }).catch(err => {
            console.log('Error copying to clipboard:', err);
        });
    }
}

// Enhanced open modal function for external calls
function openAnnModal(announcementId) {
    const modal = document.getElementById('announcementModal');
    const card = document.querySelector(`[data-announcement-id="${announcementId}"]`);
    
    if (!card) return;

    const title = card.querySelector('h3').textContent.trim();
    const date = card.querySelector('.date').textContent.trim();
    const badge = card.querySelector('.card-badge').cloneNode(true);
    const imgSrc = card.querySelector('.Anncmnt_pic').src;
    const content = card.querySelector('.announcement-preview').textContent.trim();
    const isPinned = card.classList.contains('pinned');

    // Populate modal
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalDate').textContent = date;
    document.getElementById('modalImage').src = imgSrc;
    document.getElementById('modalContent').textContent = content;

    // Update badge
    const modalBadge = document.getElementById('modalBadge');
    modalBadge.className = 'premium-modal-badge';
    modalBadge.textContent = badge.textContent.trim();

    // Show/hide pinned indicator
    const pinIndicator = modal.querySelector('.premium-pin-indicator');
    if (pinIndicator) {
        pinIndicator.style.display = isPinned ? 'flex' : 'none';
    }

    // Show modal with animation
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Trigger animations
    setTimeout(() => {
        const modalContent = modal.querySelector('.premium-modal-content');
        const backdrop = modal.querySelector('.premium-modal-backdrop');
        
        modalContent.style.animation = 'premiumModalIn 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
        backdrop.style.animation = 'premiumBackdropIn 0.4s ease';
    }, 50);
}

// Enhanced close modal function
function closeAnnModal() {
    const modal = document.getElementById('announcementModal');
    const modalContent = modal.querySelector('.premium-modal-content');
    const backdrop = modal.querySelector('.premium-modal-backdrop');
    
    // Add closing animations
    modalContent.style.animation = 'premiumModalIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) reverse';
    backdrop.style.animation = 'premiumBackdropIn 0.3s ease reverse';
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Reset animations
        modalContent.style.animation = '';
        backdrop.style.animation = '';
    }, 250);
}

// Initialize modals when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeAnnouncementModals();
});

// Initialize modals when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeAnnouncementModals();
});

/* ---------- Announcement Modal ---------- */
function openAnnModal(announcementId) {
    const card = document.querySelector(`[data-announcement-id="${announcementId}"]`);
    if (!card) return;

    const title   = card.querySelector('h3').textContent.trim();
    const date    = card.querySelector('.date').textContent.trim();
    const badge   = card.querySelector('.card-badge').cloneNode(true);
    const imgSrc  = card.querySelector('.Anncmnt_pic').src;
    const content = card.querySelector('.announcement-preview').textContent.trim();

    document.getElementById('modalTitle').textContent   = title;
    document.getElementById('modalDate').textContent    = date;
    document.getElementById('modalImage').src           = imgSrc;
    document.getElementById('modalContent').textContent = content;

    const badgeEl = document.getElementById('modalBadge');
    badgeEl.className = 'annc-badge ' + badge.className.replace('card-badge', '').trim();
    badgeEl.textContent = badge.textContent.trim();

    document.getElementById('announcementModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAnnModal() {
    document.getElementById('announcementModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

/* Keep the click-to-open logic (same as before) */
document.querySelectorAll('.announcement-card').forEach(card => {
    card.addEventListener('click', function (e) {
        if (!e.target.closest('.read-more') && !e.target.closest('.pin-indicator')) {
            const id = this.getAttribute('data-announcement-id');
            openAnnModal(id);
        }
    });
});

/* Close with Esc */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('announcementModal').style.display === 'flex') {
        closeAnnModal();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    carouselInstances.forEach(instance => {
        instance.destroy();
    });
    carouselInstances = [];
});


function initializeProgramClicks() {
    const programCards = document.querySelectorAll('.logo-card[data-program-code]');
    
    programCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on carousel controls or indicators
            if (e.target.closest('.carousel-control') || e.target.closest('.carousel-indicators')) {
                return;
            }
            
            const programCode = this.getAttribute('data-program-code').trim();

            const reverseCourseMap = {
                'SITS': 'BSIT',
                'SABES': 'BSABE',
                'AECES': 'BECED',
                'OFSET': 'BSNED',
                'FTVETS': 'BTVTED',
                'OFEE': 'BEED',
                'AFSET': 'BSED'
            };

            const departmentCode = reverseCourseMap[programCode] || programCode || 'all';

            // Create hidden form and submit to search.php with correct department filter
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'search.php';

            const queryInput = document.createElement('input');
            queryInput.type = 'hidden';
            queryInput.name = 'query';
            queryInput.value = '';
            form.appendChild(queryInput);

            const deptInput = document.createElement('input');
            deptInput.type = 'hidden';
            deptInput.name = 'department';
            deptInput.value = departmentCode; 
            form.appendChild(deptInput);

            const sortInput = document.createElement('input');
            sortInput.type = 'hidden';
            sortInput.name = 'sort';
            sortInput.value = 'recent';
            form.appendChild(sortInput);

            const pageInput = document.createElement('input');
            pageInput.type = 'hidden';
            pageInput.name = 'page';
            pageInput.value = '1';
            form.appendChild(pageInput);

            document.body.appendChild(form);
            form.submit();
        });
        
        // Add hover effect and pointer cursor
        card.style.cursor = 'pointer';
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}