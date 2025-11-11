
const programs = [
  {
    name: "SITS",
    meaning: "Society of Information Technology Students",
    image: "../../../resources/images/SITS_LOGO.png"
  },
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
  }
];

// Sample data for announcements
const announcements = [
  {
    title: "Thesis Submission Deadline",
    description: "Final submission for all undergraduate thesis papers is on December 15, 2024. Please ensure all requirements are met.",
    date: "2024-11-20",
    image: "../../../resources/images/Announcement_pic.png",
    type: "deadline"
  },
  {
    title: "Research Methodology Workshop",
    description: "Join our workshop on advanced research methodologies for thesis writing. Open to all students.",
    date: "2024-11-25",
    image: "../../../resources/images/Announcement_pic.png",
    type: "event"
  },
  {
    title: "System Maintenance",
    description: "The Thesis Compendium System will be undergoing maintenance on November 30, 2024 from 10 PM to 2 AM.",
    date: "2024-11-28",
    image: "../../../resources/images/Announcement_pic.png",
    type: "info"
  },
  {
    title: "New Thesis Guidelines",
    description: "Updated thesis formatting guidelines have been released. Please review before submission.",
    date: "2024-12-01",
    image: "../../../resources/images/Announcement_pic.png",
    type: "important"
  },
  {
    title: "Research Grant Applications",
    description: "Applications for research grants are now open. Deadline for submission is January 15, 2025.",
    date: "2024-12-05",
    image: "../../../resources/images/Announcement_pic.png",
    type: "deadline"
  }
];

// Thesis papers data (loaded from API)
let thesisPapers = [];

// DOM Elements
const landingPage = document.getElementById('landing-page');
const resultsPage = document.getElementById('results-page');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const resultsSearchInput = document.getElementById('results-search-input');
const resultsSearchBtn = document.getElementById('results-search-btn');
const thesisResults = document.getElementById('thesis-results');
const resultsNumber = document.getElementById('results-number');
const filterDropdown = document.getElementById('filterDropdown');
const listViewIcon = document.getElementById('listViewIcon');
const gridViewIcon = document.getElementById('gridViewIcon');

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
  initializeAnnouncements();
  initializePrograms();
  // Use inline preloaded theses first (from PHP), then fall back to API
  try {
    if (Array.isArray(window.__THESIS__) && window.__THESIS__.length) {
      thesisPapers = normalizeTheses(window.__THESIS__);
    } else {
      preloadThesis();
    }
  } catch (_) {
    preloadThesis();
  }
  initializeEventListeners();
});

// Initialize announcements carousel
function initializeAnnouncements() {
  const announcementsContainer = document.querySelector('.announcement-cards');
  const indicatorsContainer = document.querySelector('.announcements-carousel .carousel-indicators');
  
  announcements.forEach((announcement, index) => {
    // Create announcement card
    const card = document.createElement('div');
    card.className = 'announcement-card';
    card.innerHTML = `
      <div class="card-badge ${announcement.type}">${getBadgeText(announcement.type)}</div>
      <div class="card-image">
        <img src="${announcement.image}" alt="${announcement.title}" class="Anncmnt_pic">
      </div>
      <div class="card-content">
        <div class="card-header">
          <h3>${announcement.title}</h3>
          <div class="date">${formatDate(announcement.date)}</div>
        </div>
        <p>${announcement.description}</p>
        <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
      </div>
    `;
    announcementsContainer.appendChild(card);
    
    // Create indicator
    const indicator = document.createElement('div');
    indicator.className = `indicator ${index === 0 ? 'active' : ''}`;
    indicator.addEventListener('click', () => scrollToAnnouncement(index));
    indicatorsContainer.appendChild(indicator);
  });
  
  // Initialize carousel controls
  initializeCarouselControls('.announcements-carousel');
}

// Initialize programs carousel
function initializePrograms() {
  const programsContainer = document.querySelector('.logo-cards');
  const indicatorsContainer = document.querySelector('.program-logos-section .carousel-indicators');
  
  programs.forEach((program, index) => {
    // Create program card
    const card = document.createElement('div');
    card.className = 'logo-card';
    card.innerHTML = `
      <div class="card-image">
        <img src="${program.image}" alt="${program.name}" class="dept_pic">
      </div>
      <div class="card-content">
        <div class="card-header">
          <h3>${program.name}</h3>
          <div class="meaning">${program.meaning}</div>
        </div>
        <a href="#" class="read-more">View Department <i class="fas fa-arrow-right"></i></a>
      </div>
    `;
    programsContainer.appendChild(card);
    
    // Create indicator
    const indicator = document.createElement('div');
    indicator.className = `indicator ${index === 0 ? 'active' : ''}`;
    indicator.addEventListener('click', () => scrollToProgram(index));
    indicatorsContainer.appendChild(indicator);
  });
  
  // Initialize carousel controls
  initializeCarouselControls('.program-logos-section');
}

// Initialize carousel controls
function initializeCarouselControls(carouselSelector) {
  const carousel = document.querySelector(carouselSelector);
  const container = carousel.querySelector('.carousel-container');
  const cards = carousel.querySelector('.announcement-cards') || carousel.querySelector('.logo-cards');
  const prevBtn = carousel.querySelector('.carousel-control.prev');
  const nextBtn = carousel.querySelector('.carousel-control.next');
  const indicators = carousel.querySelectorAll('.indicator');
  
  let currentIndex = 0;
  const cardCount = cards.children.length;
  const cardWidth = cards.children[0].offsetWidth + 24; // width + gap
  
  function updateCarousel() {
    const scrollPosition = currentIndex * cardWidth;
    cards.scrollTo({
      left: scrollPosition,
      behavior: 'smooth'
    });
    
    // Update indicators
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === currentIndex);
    });
  }
  
  prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) {
      currentIndex--;
      updateCarousel();
    }
  });
  
  nextBtn.addEventListener('click', () => {
    if (currentIndex < cardCount - 1) {
      currentIndex++;
      updateCarousel();
    }
  });
  
  // Update indicators on scroll
  cards.addEventListener('scroll', () => {
    const scrollPos = cards.scrollLeft;
    currentIndex = Math.round(scrollPos / cardWidth);
    
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === currentIndex);
    });
  });
}

// Scroll to specific announcement
function scrollToAnnouncement(index) {
  const announcementsCarousel = document.querySelector('.announcements-carousel');
  const cards = announcementsCarousel.querySelector('.announcement-cards');
  const cardWidth = cards.children[0].offsetWidth + 24;
  
  cards.scrollTo({
    left: index * cardWidth,
    behavior: 'smooth'
  });
}

// Scroll to specific program
function scrollToProgram(index) {
  const programsCarousel = document.querySelector('.program-logos-section');
  const cards = programsCarousel.querySelector('.logo-cards');
  const cardWidth = cards.children[0].offsetWidth + 24;
  
  cards.scrollTo({
    left: index * cardWidth,
    behavior: 'smooth'
  });
}

// Initialize event listeners
function initializeEventListeners() {
  // Search functionality
  searchBtn.addEventListener('click', performSearch);
  searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') performSearch();
  });
  
  resultsSearchBtn.addEventListener('click', performResultsSearch);
  resultsSearchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') performResultsSearch();
  });
  
  // Filter dropdown
  filterDropdown.addEventListener('click', toggleDropdown);
  
  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!filterDropdown.contains(e.target)) {
      filterDropdown.classList.remove('active');
    }
  });
  
  // View toggle
  listViewIcon.addEventListener('click', () => toggleView('list'));
  gridViewIcon.addEventListener('click', () => toggleView('grid'));
  
  
}

// Perform search from landing page
function performSearch() {
  const query = searchInput.value.trim();
  if (query) {
    showResultsPage();
    searchFromAPI(query);
  }
}

// Perform search from results page
function performResultsSearch() {
  const query = resultsSearchInput.value.trim();
  if (query) {
    searchFromAPI(query);
  }
}

// Show results page
function showResultsPage() {
  landingPage.classList.add('hidden');
  resultsPage.classList.remove('hidden');
}

// Display search results
function displaySearchResults(query) {
  // Filter thesis papers based on query
  const filteredResults = thesisPapers.filter(paper => 
    paper.title.toLowerCase().includes(query.toLowerCase()) ||
    paper.authors.toLowerCase().includes(query.toLowerCase()) ||
    paper.adviser.toLowerCase().includes(query.toLowerCase()) ||
    paper.department.toLowerCase().includes(query.toLowerCase()) ||
    paper.abstract.toLowerCase().includes(query.toLowerCase())
  );
  
  // Update results count
  resultsNumber.textContent = filteredResults.length;
  
  // Clear previous results
  thesisResults.innerHTML = '';
  
  // Display results
  if (filteredResults.length === 0) {
    thesisResults.innerHTML = `
      <div class="no-results">
        <i class="fas fa-search fa-3x"></i>
        <h3>No results found</h3>
        <p>Try different keywords or browse all departments</p>
      </div>
    `;
  } else {
    filteredResults.forEach(paper => {
      const card = createThesisCard(paper);
      thesisResults.appendChild(card);
    });
  }
}

// Load initial theses to enable instant filtering when network is slow
async function preloadThesis() {
  try {
    const resp = await fetch('../../../app/Controllers/ThesisController.php?action=getAllTheses', { cache: 'no-store' });
    const ct = resp.headers.get('content-type') || '';
    const text = await resp.text();
    console.debug('getAllTheses raw:', text?.slice(0, 300));
    let data = null;
    if (resp.ok && ct.includes('application/json') && text.trim().length) {
      try { data = JSON.parse(text); } catch (e) { console.error('JSON parse error getAllTheses:', e); }
    }
    if (data && data.success && Array.isArray(data.theses) && data.theses.length) {
      thesisPapers = normalizeTheses(data.theses);
      return;
    }
    // Fallback: try department endpoint
    const resp2 = await fetch('../../../app/Controllers/ThesisController.php?action=getThesesByDepartment&department=all', { cache: 'no-store' });
    const ct2 = resp2.headers.get('content-type') || '';
    const text2 = await resp2.text();
    console.debug('getThesesByDepartment raw:', text2?.slice(0, 300));
    let data2 = null;
    if (resp2.ok && ct2.includes('application/json') && text2.trim().length) {
      try { data2 = JSON.parse(text2); } catch (_) {}
    }
    if (data2 && data2.success && Array.isArray(data2.theses) && data2.theses.length) {
      thesisPapers = normalizeTheses(data2.theses);
      return;
    }
  } catch (e) {
    console.error('Failed to preload theses:', e);
  }
}

// Search via API (server-side) and render results
async function searchFromAPI(query) {
  try {
    // Fetch all theses then filter client-side to ensure consistent results for guests
    if (!thesisPapers || thesisPapers.length === 0) {
      await preloadThesis();
    }
    displaySearchResults(query);
  } catch (e) {
    console.error('Search error:', e);
    displaySearchResults(query);
  }
}

function normalizeTheses(theses) {
  return theses.map(t => {
    const title = t.Title || t.title || '';
    const authors = t.Author || t.authors || '';
    const adviser = t.Adviser || t.adviser || '';
    const abstractRaw = t.Thesis_AbstractFile || t.abstract || '';
    const abstractText = typeof abstractRaw === 'string' ? abstractRaw : '';
    const course = t.Thesis_Course || t.course || '';
    const dept = t.Thesis_Department || t.department || '';
    const department = course && dept && !String(dept).includes('|') ? `${course} | ${dept}` : (dept || '');
    const uploadDate = t.uploaded_at || t.uploadDate || '';
    return {
      id: t.ID || t.id,
      title,
      authors,
      adviser,
      abstract: abstractText,
      department,
      uploadDate,
      logo: '../../../resources/images/ThesisCompLogo.png'
    };
  });
}

// Create thesis card element
function createThesisCard(paper) {
  const card = document.createElement('div');
  card.className = 'thesis-card';
  card.innerHTML = `
    <div class="card-header">
      <div class="card-logo">
        <img src="${paper.logo}" alt="${paper.department}" style="width: 100%; height: 100%; object-fit: contain;">
      </div>
      <div class="card-menu">
        <i class="fas fa-ellipsis-v"></i>
      </div>
    </div>
    <h3>${paper.title}</h3>
    <a href="#">${paper.authors}</a>
    <p>${paper.abstract.substring(0, 150)}...</p>
    <div class="card-footer">
      <div class="upload-date">
        <i class="far fa-calendar-alt"></i>
        <span>Uploaded: ${formatDate(paper.uploadDate)}</span>
      </div>
      <div class="available">
        <i class="fas fa-check-circle"></i>
        <span>Available</span>
      </div>
    </div>
  `;
  
  card.addEventListener('click', () => {
    alert(`Viewing details for: ${paper.title}`);
    // In a real application, this would navigate to the thesis detail page
  });
  
  return card;
}

// Toggle dropdown
function toggleDropdown() {
  this.classList.toggle('active');
}

// Toggle view between list and grid
function toggleView(view) {
  if (view === 'list') {
    thesisResults.classList.remove('grid');
    thesisResults.classList.add('list');
    listViewIcon.classList.add('selected');
    gridViewIcon.classList.remove('selected');
  } else {
    thesisResults.classList.remove('list');
    thesisResults.classList.add('grid');
    gridViewIcon.classList.add('selected');
    listViewIcon.classList.remove('selected');
  }
}

// Utility function to format date
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

// Utility function to get badge text
function getBadgeText(type) {
  const badgeTexts = {
    'important': 'Important',
    'deadline': 'Deadline',
    'info': 'Information',
    'event': 'Event'
  };
  return badgeTexts[type] || 'Announcement';
}