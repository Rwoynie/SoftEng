// Carousel functionality
class Carousel {
  constructor(container, options = {}) {
    this.container = container;
    this.cardsContainer = container.querySelector('.carousel-container > div');
    this.cards = Array.from(this.cardsContainer.children);
    this.prevBtn = container.querySelector('.carousel-control.prev');
    this.nextBtn = container.querySelector('.carousel-control.next');
    this.indicatorsContainer = container.querySelector('.carousel-indicators');
    
    this.currentIndex = 0;
    this.cardsPerView = 3; // Fixed to 3 cards
    this.autoPlay = options.autoPlay || false;
    this.autoPlayInterval = options.autoPlayInterval || 5000;
    this.autoPlayTimer = null;
    this.isTransitioning = false;
    
    this.init();
  }

  init() {
    this.setupCarousel();
    this.createIndicators();
    this.setupEventListeners();
    this.updateCarousel();
    
    if (this.autoPlay) {
      this.startAutoPlay();
    }
  }

  setupCarousel() {
    // Clone cards for infinite loop effect
    const firstCards = Array.from(this.cards.slice(0, this.cardsPerView));
    const lastCards = Array.from(this.cards.slice(-this.cardsPerView));
    
    // Append clones to beginning and end
    lastCards.forEach(card => {
      const clone = card.cloneNode(true);
      clone.classList.add('clone');
      this.cardsContainer.insertBefore(clone, this.cardsContainer.firstChild);
    });
    
    firstCards.forEach(card => {
      const clone = card.cloneNode(true);
      clone.classList.add('clone');
      this.cardsContainer.appendChild(clone);
    });
    
    // Update cards reference
    this.cards = Array.from(this.cardsContainer.children);
    
    // Start at the first original card (after clones)
    this.currentIndex = this.cardsPerView;
    this.cardsContainer.style.transform = `translateX(-${this.currentIndex * this.getCardWidth()}px)`;
  }

  getCardWidth() {
    if (this.cards.length === 0) return 0;
    const card = this.cards[0];
    const cardStyle = getComputedStyle(card);
    const gap = parseInt(getComputedStyle(this.cardsContainer).gap) || 0;
    return card.offsetWidth + gap;
  }

  createIndicators() {
    if (!this.indicatorsContainer) return;
    
    const originalCardsCount = this.cards.length - (2 * this.cardsPerView);
    const totalSlides = Math.ceil(originalCardsCount / this.cardsPerView);
    this.indicatorsContainer.innerHTML = '';
    
    for (let i = 0; i < totalSlides; i++) {
      const indicator = document.createElement('div');
      indicator.className = `indicator ${i === 0 ? 'active' : ''}`;
      indicator.addEventListener('click', () => this.goToSlide(i));
      this.indicatorsContainer.appendChild(indicator);
    }
  }

  setupEventListeners() {
    this.prevBtn?.addEventListener('click', () => this.prev());
    this.nextBtn?.addEventListener('click', () => this.next());
    
    // Touch/swipe support
    let startX = 0;
    let endX = 0;
    
    this.cardsContainer.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX;
    });
    
    this.cardsContainer.addEventListener('touchmove', (e) => {
      endX = e.touches[0].clientX;
    });
    
    this.cardsContainer.addEventListener('touchend', () => {
      const diff = startX - endX;
      if (Math.abs(diff) > 50 && !this.isTransitioning) {
        if (diff > 0) {
          this.next();
        } else {
          this.prev();
        }
      }
    });
    
    // Window resize
    window.addEventListener('resize', () => {
      this.updateCarousel();
    });
    
    // Transition end event for loop handling
    this.cardsContainer.addEventListener('transitionend', () => {
      this.isTransitioning = false;
      this.handleLoop();
    });
    
    // Pause autoplay on hover
    if (this.autoPlay) {
      this.container.addEventListener('mouseenter', () => this.stopAutoPlay());
      this.container.addEventListener('mouseleave', () => this.startAutoPlay());
    }
  }

  updateCarousel() {
    if (this.isTransitioning) return;
    
    const translateX = -this.currentIndex * this.getCardWidth();
    this.cardsContainer.style.transition = 'transform 0.5s ease';
    this.cardsContainer.style.transform = `translateX(${translateX}px)`;
    this.updateIndicators();
  }

  updateIndicators() {
    if (!this.indicatorsContainer) return;
    
    const indicators = this.indicatorsContainer.querySelectorAll('.indicator');
    const originalCardsCount = this.cards.length - (2 * this.cardsPerView);
    const totalSlides = Math.ceil(originalCardsCount / this.cardsPerView);
    
    // Calculate actual slide index (excluding clones)
    let actualIndex = this.currentIndex - this.cardsPerView;
    if (actualIndex < 0) {
      actualIndex = totalSlides - 1;
    } else if (actualIndex >= originalCardsCount) {
      actualIndex = 0;
    } else {
      actualIndex = Math.floor(actualIndex / this.cardsPerView);
    }
    
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === actualIndex);
    });
  }

  next() {
    if (this.isTransitioning) return;
    
    this.isTransitioning = true;
    this.currentIndex++;
    this.updateCarousel();
    this.resetAutoPlay();
  }

  prev() {
    if (this.isTransitioning) return;
    
    this.isTransitioning = true;
    this.currentIndex--;
    this.updateCarousel();
    this.resetAutoPlay();
  }

  handleLoop() {
    const originalCardsCount = this.cards.length - (2 * this.cardsPerView);
    
    // If at the beginning clones, jump to end
    if (this.currentIndex < this.cardsPerView) {
      this.cardsContainer.style.transition = 'none';
      this.currentIndex = this.cardsPerView + originalCardsCount - this.cardsPerView;
      this.cardsContainer.style.transform = `translateX(-${this.currentIndex * this.getCardWidth()}px)`;
      this.isTransitioning = false;
    }
    // If at the end clones, jump to beginning
    else if (this.currentIndex >= this.cardsPerView + originalCardsCount) {
      this.cardsContainer.style.transition = 'none';
      this.currentIndex = this.cardsPerView;
      this.cardsContainer.style.transform = `translateX(-${this.currentIndex * this.getCardWidth()}px)`;
      this.isTransitioning = false;
    }
  }

  goToSlide(slideIndex) {
    if (this.isTransitioning) return;
    
    this.isTransitioning = true;
    this.currentIndex = this.cardsPerView + (slideIndex * this.cardsPerView);
    this.updateCarousel();
    this.resetAutoPlay();
  }

  startAutoPlay() {
    if (this.autoPlay && !this.autoPlayTimer) {
      this.autoPlayTimer = setInterval(() => {
        this.next();
      }, this.autoPlayInterval);
    }
  }

  stopAutoPlay() {
    if (this.autoPlayTimer) {
      clearInterval(this.autoPlayTimer);
      this.autoPlayTimer = null;
    }
  }

  resetAutoPlay() {
    if (this.autoPlay) {
      this.stopAutoPlay();
      this.startAutoPlay();
    }
  }
}

// Dropdown functionality
class Dropdown {
  constructor(container) {
    this.container = container;
    this.selected = container.querySelector('.selected');
    this.options = container.querySelector('.options');
    this.optionsList = container.querySelectorAll('.options div');
    
    this.init();
  }

  init() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    this.selected.addEventListener('click', (e) => {
      e.stopPropagation();
      this.toggle();
    });

    this.optionsList.forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        this.select(option);
      });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', () => {
      this.close();
    });

    // Prevent closing when clicking inside dropdown
    this.options.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }

  toggle() {
    this.container.classList.toggle('active');
  }

  close() {
    this.container.classList.remove('active');
  }

  select(option) {
    const value = option.getAttribute('data-value');
    const text = option.textContent;
    
    this.selected.innerHTML = `
      <span>${text}</span>
      <i class="fa fa-chevron-down" aria-hidden="true"></i>
    `;
    
    this.close();
    
    // Dispatch custom event
    this.container.dispatchEvent(new CustomEvent('change', {
      detail: { value, text }
    }));
  }

  getValue() {
    return this.selected.querySelector('span').textContent;
  }
}

// Main application
class ThesisCompendiumApp {
  constructor() {
    this.currentPage = 'landing';
    this.carousels = [];
    this.dropdowns = [];
    this.init();
  }

  init() {
    this.initializeCarousels();
    this.initializeDropdowns();
    this.setupEventListeners();
    this.loadSampleData();
  }

  initializeCarousels() {
    // Announcements carousel
    const announcementsCarousel = document.querySelector('.announcements-carousel');
    if (announcementsCarousel) {
      this.carousels.push(new Carousel(announcementsCarousel, {
        autoPlay: true,
        autoPlayInterval: 6000
      }));
    }

    // Programs carousel
    const programsCarousel = document.querySelector('.logo-carousel');
    if (programsCarousel) {
      this.carousels.push(new Carousel(programsCarousel, {
        autoPlay: true,
        autoPlayInterval: 5000
      }));
    }
  }

  initializeDropdowns() {
    const dropdownContainers = document.querySelectorAll('.select');
    dropdownContainers.forEach(container => {
      this.dropdowns.push(new Dropdown(container));
    });
  }

  setupEventListeners() {
    // Search functionality
    const searchBtn = document.getElementById('search-btn');
    const searchInput = document.getElementById('search-input');
    const resultsSearchBtn = document.getElementById('results-search-btn');
    const resultsSearchInput = document.getElementById('results-search-input');

    const performSearch = () => {
      const query = searchInput?.value || resultsSearchInput?.value;
      if (query && query.trim()) {
        this.showResultsPage();
        this.performSearch(query);
      }
    };

    searchBtn?.addEventListener('click', performSearch);
    resultsSearchBtn?.addEventListener('click', performSearch);

    searchInput?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') performSearch();
    });

    resultsSearchInput?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') performSearch();
    });

    // View toggle
    const listViewIcon = document.getElementById('listViewIcon');
    const gridViewIcon = document.getElementById('gridViewIcon');
    const resultsContainer = document.getElementById('thesis-results');

    listViewIcon?.addEventListener('click', () => {
      listViewIcon.classList.add('selected');
      gridViewIcon.classList.remove('selected');
      resultsContainer?.classList.remove('grid');
      resultsContainer?.classList.add('list');
    });

    gridViewIcon?.addEventListener('click', () => {
      gridViewIcon.classList.add('selected');
      listViewIcon.classList.remove('selected');
      resultsContainer?.classList.remove('list');
      resultsContainer?.classList.add('grid');
    });

    // Logo click to reload
    document.querySelector('.logo img')?.addEventListener('click', () => {
      window.location.reload();
    });

    // Login button
    document.querySelector('.btn-login')?.addEventListener('click', (e) => {
      e.preventDefault();
      alert('Login functionality would go here');
    });

    // Filter and sort dropdown events
    const filterDropdown = document.getElementById('filterDropdown');
    const sortDropdown = document.getElementById('sortDropdown');

    filterDropdown?.addEventListener('change', (e) => {
      this.filterResults(e.detail.value);
    });

    sortDropdown?.addEventListener('change', (e) => {
      this.sortResults(e.detail.value);
    });
  }

  showResultsPage() {
    document.getElementById('landing-page').classList.add('hidden');
    document.getElementById('results-page').classList.remove('hidden');
  }

  showLandingPage() {
    document.getElementById('landing-page').classList.remove('hidden');
    document.getElementById('results-page').classList.add('hidden');
  }

  performSearch(query) {
    // Simulate search results
    const resultsContainer = document.getElementById('thesis-results');
    const resultsCount = document.getElementById('results-number');
    
    if (resultsContainer && resultsCount) {
      // In a real application, this would be an API call
      const results = this.generateSampleResults(query);
      this.displayResults(results);
      resultsCount.textContent = results.length;
    }
  }

  filterResults(department) {
    const query = document.getElementById('results-search-input')?.value || '';
    let results = this.generateSampleResults(query);
    
    if (department !== 'all') {
      results = results.filter(thesis => 
        thesis.department.toLowerCase().includes(department.toLowerCase())
      );
    }
    
    this.displayResults(results);
    document.getElementById('results-number').textContent = results.length;
  }

  sortResults(criteria) {
    const query = document.getElementById('results-search-input')?.value || '';
    let results = this.generateSampleResults(query);
    
    switch(criteria) {
      case 'recent':
        results.sort((a, b) => new Date(b.date) - new Date(a.date));
        break;
      case 'popular':
        results.sort((a, b) => b.views - a.views);
        break;
      case 'title':
        results.sort((a, b) => a.title.localeCompare(b.title));
        break;
      case 'department':
        results.sort((a, b) => a.department.localeCompare(b.department));
        break;
    }
    
    this.displayResults(results);
  }

  generateSampleResults(query) {
    // Sample data for demonstration
    const allResults = [
      {
        id: 1,
        title: "Machine Learning for Predictive Analysis",
        authors: "John Smith, Sarah Johnson",
        abstract: "This research explores the application of machine learning algorithms for predictive analysis in healthcare data.",
        progress: 85,
        deadline: "2024-06-15",
        department: "Computer Science",
        date: "2024-01-15",
        views: 245
      },
      {
        id: 2,
        title: "Blockchain Implementation for Secure Voting",
        authors: "Michael Brown, Emily Davis",
        abstract: "A comprehensive study on implementing blockchain technology for secure and transparent voting systems.",
        progress: 60,
        deadline: "2024-07-20",
        department: "Information Technology",
        date: "2024-02-10",
        views: 189
      },
      {
        id: 3,
        title: "IoT-based Smart Home Automation",
        authors: "Robert Wilson, Lisa Anderson",
        abstract: "Development of an IoT-based system for smart home automation with energy efficiency optimization.",
        progress: 90,
        deadline: "2024-05-30",
        department: "Computer Engineering",
        date: "2024-01-30",
        views: 312
      },
      {
        id: 4,
        title: "Renewable Energy Monitoring System",
        authors: "David Miller, Jennifer Taylor",
        abstract: "A monitoring system for renewable energy sources with real-time data analysis and reporting.",
        progress: 75,
        deadline: "2024-08-10",
        department: "Electrical Engineering",
        date: "2024-03-05",
        views: 167
      },
      {
        id: 5,
        title: "Automated Manufacturing Process",
        authors: "Christopher Lee, Amanda White",
        abstract: "Automation of manufacturing processes using robotics and AI for improved efficiency.",
        progress: 45,
        deadline: "2024-09-15",
        department: "Mechanical Engineering",
        date: "2024-02-28",
        views: 134
      }
    ];

    if (!query) return allResults;

    const searchTerm = query.toLowerCase();
    return allResults.filter(result => 
      result.title.toLowerCase().includes(searchTerm) ||
      result.abstract.toLowerCase().includes(searchTerm) ||
      result.authors.toLowerCase().includes(searchTerm) ||
      result.department.toLowerCase().includes(searchTerm)
    );
  }

  displayResults(results) {
    const resultsContainer = document.getElementById('thesis-results');
    if (!resultsContainer) return;

    if (results.length === 0) {
      resultsContainer.innerHTML = `
        <div class="no-results">
          <i class="fas fa-search"></i>
          <h3>No results found</h3>
          <p>Try different keywords or check your spelling</p>
        </div>
      `;
      return;
    }

    resultsContainer.innerHTML = results.map(result => `
      <div class="thesis-card" onclick="app.viewThesisDetail(${result.id})">
        <div class="card-header">
          <div class="card-logo">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <div class="card-menu">
            <i class="fas fa-ellipsis-v"></i>
          </div>
        </div>
        <div class="card-content">
          <h3>${result.title}</h3>
          <a href="#">${result.authors}</a>
          <p>${result.abstract}</p>
          <div class="progress-container">
            <div class="progress-info">
              <span>Progress</span>
              <span>${result.progress}%</span>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${result.progress}%"></div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="deadline">
            <i class="fas fa-clock"></i>
            <span>Due: ${new Date(result.deadline).toLocaleDateString()}</span>
          </div>
          <div class="available">
            <i class="fas fa-check-circle"></i>
            <span>Available</span>
          </div>
        </div>
      </div>
    `).join('');
  }

  viewThesisDetail(id) {
    alert(`Viewing thesis details for ID: ${id}\n\nIn a real application, this would navigate to the thesis detail page.`);
  }

  loadSampleData() {
    // Load sample announcements
    this.loadSampleAnnouncements();
    // Load sample programs
    this.loadSamplePrograms();
  }

  loadSampleAnnouncements() {
    const announcementsContainer = document.querySelector('.announcement-cards');
    if (!announcementsContainer) return;

    const announcements = [
      {
        title: "Thesis Submission Deadline",
        date: "June 15, 2024",
        description: "Final submission deadline for all undergraduate thesis papers. Make sure to complete all requirements.",
        badge: "deadline",
        image: "../../../resources/images/Announcement_pic.png"
      },
      {
        title: "Research Methodology Workshop",
        date: "June 20, 2024",
        description: "Join our workshop on advanced research methodologies and statistical analysis techniques.",
        badge: "event",
        image: "../../../resources/images/Announcement_pic.png"
      },
      {
        title: "System Maintenance",
        date: "June 10, 2024",
        description: "The system will be undergoing maintenance from 2:00 AM to 4:00 AM. Please save your work.",
        badge: "info",
        image: "../../../resources/images/Announcement_pic.png"
      },
      {
        title: "New Features Added",
        date: "June 5, 2024",
        description: "We've added new features including advanced search filters and citation tools.",
        badge: "info",
        image: "../../../resources/images/Announcement_pic.png"
      },
      {
        title: "Research Grant Opportunities",
        date: "July 1, 2024",
        description: "Apply for research grants available for innovative projects in computer science.",
        badge: "important",
        image: "../../../resources/images/Announcement_pic.png"
      }
    ];

    announcementsContainer.innerHTML = announcements.map(announcement => `
      <div class="announcement-card">
        <div class="card-badge ${announcement.badge}">${announcement.badge.toUpperCase()}</div>
        <div class="card-image">
          <img src="${announcement.image}" alt="${announcement.title}" class="Anncmnt_pic" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE4MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjdmYWZjIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0jOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+QW5ub3VuY2VtZW50IEltYWdlPC90ZXh0Pjwvc3ZnPg=='">
        </div>
        <div class="card-content">
          <div class="card-header">
            <h3>${announcement.title}</h3>
            <span class="date">${announcement.date}</span>
          </div>
          <p>${announcement.description}</p>
          <a href="#" class="read-more">
            Read More <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    `).join('');

    // Reinitialize carousel with new cards
    const announcementsCarousel = document.querySelector('.announcements-carousel');
    if (announcementsCarousel) {
      this.carousels[0] = new Carousel(announcementsCarousel, {
        autoPlay: true,
        autoPlayInterval: 6000
      });
    }
  }

  loadSamplePrograms() {
    const programsContainer = document.querySelector('.logo-cards');
    if (!programsContainer) return;

    const programs = [
      {
        name: "AECES",
        meaning: "Association of Early Childhood Education",
        image: "../../../resources/images/AECES_LOGO.png"
      },
      {
        name: "AFSET",
        meaning: "Association of Future Secondary Teachers",
        image: "../../../resources/images/AFSET_LOGO.png"
      },
      {
        name: "FTVETS",
        meaning: "Future Technical-Vocational Educators' and Trainers' Society",
        image: "../../../resources/images/FTVETS_LOGO.png"
      },
      {
        name: "OFEE",
        meaning: "Organization of Future Elementary Educators",
        image: "../../../resources/images/OFEE_LOGO.png"
      },
      {
        name: "OFSET",
        meaning: "Organization of Future Special Education Teachers",
        image: "../../../resources/images/OFSET_LOGO.png"
      },
      {
        name: "SABES",
        meaning: "Society of Agricultural and Biosystems Engineering Students",
        image: "../../../resources/images/SABES_LOGO.png"
      },
      {
        name: "SITS",
        meaning: "Society of Information Technology Students",
        image: "../../../resources/images/SITS_LOGO.png"
      }
    ];

    programsContainer.innerHTML = programs.map(program => `
      <div class="logo-card">
        <div class="card-image">
          <img src="${program.image}" alt="${program.name}" class="dept_pic" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjE4MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjdmYWZjIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0jOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UHJvZ3JhbSBMb2dvPC90ZXh0Pjwvc3ZnPg=='">
        </div>
        <div class="card-content">
          <h3>${program.name}</h3>
          <span class="meaning">${program.meaning}</span>
        </div>
      </div>
    `).join('');

    // Reinitialize carousel with new cards
    const programsCarousel = document.querySelector('.logo-carousel');
    if (programsCarousel) {
      this.carousels[1] = new Carousel(programsCarousel, {
        autoPlay: true,
        autoPlayInterval: 5000
      });
    }
  }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  window.app = new ThesisCompendiumApp();
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { Carousel, Dropdown, ThesisCompendiumApp };
}