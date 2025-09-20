document.addEventListener('DOMContentLoaded', () => {
  // DOM Elements
  const landingPage = document.getElementById('landing-page');
  const footer1 = document.getElementById('footer1');
  const resultsPage = document.getElementById('results-page');
  const searchInput = document.getElementById('search-input');
  const resultsSearchInput = document.getElementById('results-search-input');
  const searchBtn = document.getElementById('search-btn');
  const resultsSearchBtn = document.getElementById('results-search-btn');
  const thesisResults = document.getElementById('thesis-results');
  
  // Carousel Elements
  const carouselContainer = document.querySelector('.carousel-container');
  const announcementCards = document.querySelector('.announcement-cards');
  const prevButton = document.querySelector('.carousel-control.prev');
  const nextButton = document.querySelector('.carousel-control.next');
  const indicatorsContainer = document.querySelector('.carousel-indicators');
  

  const thesisData = [
    {
      id: 1,
      title: "Regular Logistics",
      url: "regularlogistics.com",
      description: "Supply chain management solution for small and medium businesses.",
      progress: 44,
      daysLeft: 9,
      hardboundAvailable: true,
      department: "cs",
      tags: ["logistics", "supply chain", "business"]
    },
    {
      id: 2,
      title: "Capstone Finder",
      url: "capstonefinder.org",
      description: "A digital repository for storing, managing, and searching capstone projects.",
      progress: 75,
      daysLeft: 15,
      hardboundAvailable: true,
      department: "it",
      tags: ["repository", "management", "search"]
    },
    {
      id: 3,
      title: "Smart Campus",
      url: "smartcampus.edu",
      description: "IoT-based solution for campus management and facility optimization.",
      progress: 30,
      daysLeft: 25,
      hardboundAvailable: false,
      department: "ce",
      tags: ["iot", "campus", "management"]
    },
    {
      id: 4,
      title: "E-Learning Platform",
      url: "elearnplatform.com",
      description: "Interactive online learning system with AI-powered recommendations.",
      progress: 90,
      daysLeft: 5,
      hardboundAvailable: true,
      department: "cs",
      tags: ["education", "ai", "learning"]
    }
  ];

  // Carousel state
  let currentSlide = 0;
  let slideInterval;
  const slides = document.querySelectorAll('.announcement-card');
  const totalSlides = slides.length;
  let slidesToShow = calculateSlidesToShow();

  // Initialize the application
  initApp();

  function initApp() {
    // Set up event listeners
    setupEventListeners();
    
    // Initialize carousel
    initCarousel();
    
    // Initialize with empty results container
    thesisResults.innerHTML = `
      <div class="no-results">
        <i class="fas fa-search"></i>
        <h3>Search for theses</h3>
        <p>Enter keywords in the search box above</p>
      </div>
    `;
  }

  function setupEventListeners() {
    // Search functionality
    searchBtn.addEventListener('click', handleSearch);
    resultsSearchBtn.addEventListener('click', handleResultsSearch);
    
    // Allow pressing Enter to search
    searchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleSearch();
    });
    
    resultsSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleResultsSearch();
    });

    // Grid/List toggle
    const gridBtn = document.querySelector('.grid-view');
    const listBtn = document.querySelector('.list-view');
    const results = document.querySelector('.results');

    gridBtn.addEventListener('click', () => {
      results.classList.add('grid');
      results.classList.remove('list');
      gridBtn.classList.add('active');
      listBtn.classList.remove('active');
    });

    listBtn.addEventListener('click', () => {
      results.classList.add('list');
      results.classList.remove('grid');
      listBtn.classList.add('active');
      gridBtn.classList.remove('active');
    });

    // Filter dropdown functionality
    const filterDropdown = document.getElementById('filterDropdown');
    if (filterDropdown) {
      filterDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        this.classList.toggle('active');
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!filterDropdown.contains(e.target)) {
          filterDropdown.classList.remove('active');
        }
      });

      // Handle option selection
      const options = filterDropdown.querySelectorAll('.options div');
      options.forEach(option => {
        option.addEventListener('click', function() {
          const value = this.getAttribute('data-value');
          const text = this.textContent;
          filterDropdown.querySelector('.selected span').textContent = text;
          filterDropdown.classList.remove('active');
          
          // Filter results based on department
          filterResults(value);
        });
      });
    }

    // Window resize for responsive carousel
    window.addEventListener('resize', () => {
      slidesToShow = calculateSlidesToShow();
      updateCarousel();
    });
  }

  function initCarousel() {
    // Create indicators
    createIndicators();
    
    // Set up carousel navigation
    prevButton.addEventListener('click', () => {
      stopAutoSlide();
      prevSlide();
      startAutoSlide();
    });
    
    nextButton.addEventListener('click', () => {
      stopAutoSlide();
      nextSlide();
      startAutoSlide();
    });
    
    // Start auto sliding
    startAutoSlide();
    
    // Pause auto slide on hover
    carouselContainer.addEventListener('mouseenter', stopAutoSlide);
    carouselContainer.addEventListener('mouseleave', startAutoSlide);
  }

  function calculateSlidesToShow() {
    const width = window.innerWidth;
    if (width < 768) return 1;
    if (width < 992) return 2;
    if (width < 1200) return 3;
    return 4;
  }

  function createIndicators() {
    const indicatorCount = Math.ceil(totalSlides / slidesToShow);
    
    for (let i = 0; i < indicatorCount; i++) {
      const indicator = document.createElement('div');
      indicator.classList.add('indicator');
      if (i === 0) indicator.classList.add('active');
      
      indicator.addEventListener('click', () => {
        stopAutoSlide();
        goToSlide(i * slidesToShow);
        startAutoSlide();
      });
      
      indicatorsContainer.appendChild(indicator);
    }
  }

  function updateIndicators() {
    const indicators = document.querySelectorAll('.indicator');
    const activeIndicator = Math.floor(currentSlide / slidesToShow);
    
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === activeIndicator);
    });
  }

  function updateCarousel() {
    const cardWidth = slides[0].offsetWidth + 20; // width + margin
    const translateX = -currentSlide * cardWidth;
    announcementCards.style.transform = `translateX(${translateX}px)`;
    updateIndicators();
  }

  function nextSlide() {
    if (currentSlide >= totalSlides - slidesToShow) {
      currentSlide = 0;
    } else {
      currentSlide += slidesToShow;
    }
    updateCarousel();
  }

  function prevSlide() {
    if (currentSlide <= 0) {
      currentSlide = totalSlides - slidesToShow;
    } else {
      currentSlide -= slidesToShow;
    }
    updateCarousel();
  }

  function goToSlide(slideIndex) {
    currentSlide = slideIndex;
    updateCarousel();
  }

  function startAutoSlide() {
    stopAutoSlide();
    slideInterval = setInterval(nextSlide, 5000);
  }

  function stopAutoSlide() {
    clearInterval(slideInterval);
  }

  function handleSearch() {
    const term = searchInput.value.trim();
    if (term) {
      // Show results page
      landingPage.classList.add('hidden');
      footer1.classList.add('hidden');
      resultsPage.classList.remove('hidden');
      
      // Set the search term in results page
      resultsSearchInput.value = term;
      
      // Perform search
      performSearch(term);
    }
  }

  function handleResultsSearch() {
    const term = resultsSearchInput.value.trim();
    if (term) {
      performSearch(term);
    }
  }

  function performSearch(term) {
    // Show loading state
    thesisResults.innerHTML = `
      <div class="loading">
        <div class="spinner"></div>
      </div>
    `;
    
    // Simulate API call delay
    setTimeout(() => {
      const results = searchTheses(term);
      displayResults(results);
    }, 500);
  }

  function filterResults(department) {
    const term = resultsSearchInput.value.trim();
    let results;
    
    if (department === 'all') {
      results = searchTheses(term);
    } else {
      results = searchTheses(term).filter(thesis => thesis.department === department);
    }
    
    displayResults(results);
  }

  function searchTheses(term) {
    if (!term) return thesisData;
    
    const searchTerm = term.toLowerCase();
    return thesisData.filter(thesis => {
      return (
        thesis.title.toLowerCase().includes(searchTerm) ||
        thesis.description.toLowerCase().includes(searchTerm) ||
        thesis.tags.some(tag => tag.toLowerCase().includes(searchTerm))
      );
    });
  }

  function displayResults(results) {
    if (results.length === 0) {
      thesisResults.innerHTML = `
        <div class="no-results">
          <i class="fas fa-search"></i>
          <h3>No results found</h3>
          <p>Try different keywords or check your spelling</p>
        </div>
      `;
      return;
    }
    
    thesisResults.innerHTML = results.map(thesis => `
      <div class="thesis-card">
        <div class="card-header">
          <div class="card-logo">
            <i class="fas fa-book"></i>
          </div>
          <div class="card-menu">
            <i class="fas fa-ellipsis-v"></i>
          </div>
        </div>
        <div class="card-content">
          <h3>${thesis.title}</h3>
          <a href="http://${thesis.url}" target="_blank">${thesis.url}</a>
          <p>${thesis.description}</p>
          <div class="progress-container">
            <div class="progress-info">
              <span>Progress</span>
              <span>${thesis.progress}%</span>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${thesis.progress}%"></div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="deadline">
            <i class="fas fa-calendar-alt"></i>
            <span>${thesis.daysLeft} days left</span>
          </div>
          <div class="available">
            <i class="fas ${thesis.hardboundAvailable ? 'fa-check-circle' : 'fa-times-circle'}"></i>
            <span>${thesis.hardboundAvailable ? 'Available' : 'Not Available'}</span>
          </div>
        </div>
      </div>
    `).join('');
  }
});