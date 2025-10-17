

<?php

$announcements = $data['announcements'] ?? [];
$programs = $data['programs'] ?? [];
$stats = $data['stats'] ?? [];
?>

<!-- Update the search form -->
<form id="search-form" method="POST" action="/search">
    <div class="searchbox">
        <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
        <input type="text" id="search-input" name="query" placeholder="Enter keywords, title, author, or adviser...">
        <button type="submit" class="search-btn" id="search-btn">Search</button>
    </div>
</form>

<!-- Update stats section -->
<div class="stats">
    <div class="stat-item">
        <span class="stat-number"><?php echo $stats['total_papers'] ?? '200+'; ?></span>
        <span class="stat-label">Thesis Papers</span>
    </div>
    <div class="stat-item">
        <span class="stat-number"><?php echo $stats['total_authors'] ?? '150+'; ?></span>
        <span class="stat-label">Active Researchers</span>
    </div>
    <div class="stat-item">
        <span class="stat-number"><?php echo $stats['total_departments'] ?? '1'; ?></span>
        <span class="stat-label">Department</span>
    </div>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thesis Compendium System</title>
  <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
  <link rel="stylesheet" href="../../../resources/css/User/home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
</head>
<body>
  <div class="dashboard-container">
    <!-- Header -->
    <header class="main-header">
      <div class="logo">
        <img href="javascript:window.location.reload(true)" src="../../../resources/images/ThesisCompLogo.png" alt="Logo" />
        <div>
          <h1>Thesis Compendium System</h1>
          <h3>University of Southeastern Philippines</h3>

        </div>
        
      </div>
      <nav class="tabs">
        <a href="../../../app/Views/User/indexLogin.php" class="btn-login">Login</a>
      </nav>
    </header>

    <!-- ================= Landing Page ================= -->
    <section id="landing-page">
      <!-- Search Banner -->
      <section class="search-banner">
        <div class="banner-content">
          <h2>Discover Academic Excellence</h2>
          <p>Access hundreds of thesis papers from different departments</p>
          <div class="search-container">
            <div class="searchbox">
              <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
              <input type="text" id="search-input" placeholder="Enter keywords, title, author, or adviser...">
              <button class="search-btn" id="search-btn">Search</button>
            </div>
          </div>
          <div class="stats">
            <div class="stat-item">
              <span class="stat-number">200+</span>
              <span class="stat-label">Thesis Papers</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">150+</span>
              <span class="stat-label">Active Researchers</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">1</span>
              <span class="stat-label">Department</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Announcements Carousel -->
      <section class="announcements-section">
        <div class="section-header">
          <h2>Announcements</h2>
        </div>
        
        <div class="announcements-carousel">
          <button class="carousel-control prev">
            <i class="fas fa-chevron-left"></i>
          </button>
          <div class="carousel-container">
            <div class="announcement-cards">
              
            </div>
          </div>
          <button class="carousel-control next">
            <i class="fas fa-chevron-right"></i>
          </button>
          <div class="carousel-indicators"></div>
        </div>
      </section>

      <!-- Programs Logo Carousel -->
      <section class="program-logos-section">
        <div class="section-header">
          <h2>Programs</h2>
        </div>
        
        <div class="logo-carousel">
          <button class="carousel-control prev">
            <i class="fas fa-chevron-left"></i>
          </button>
          <div class="carousel-container">
            <div class="logo-cards">
              
            </div>
          </div>
          <button class="carousel-control next">
            <i class="fas fa-chevron-right"></i>
          </button>
          <div class="carousel-indicators"></div>
        </div>
      </section>

      <!-- Quick Actions -->
      <section class="quick-actions">
        <div class="section-header">
          <h2>Quick Access</h2>
        </div>
        <div class="action-cards">
          <div class="action-card">
            <div class="action-icon">
              <i class="fas fa-book-open"></i>
            </div>
            <h3>Browse Catalog</h3>
            <p>Explore all available thesis papers</p>
          </div>
          <div class="action-card">
            <div class="action-icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <h3>For Researchers</h3>
            <p>Resources and guidelines for your research</p>
          </div>
          <div class="action-card">
            <div class="action-icon">
              <i class="fas fa-question-circle"></i>
            </div>
            <h3>Help Center</h3>
            <p>Get assistance with the system</p>
          </div>
        </div>
      </section>
    </section>

    <!-- ================= Search Results Page ================= -->
    <section id="results-page" class="hidden">
      <header class="results-header">
        <section class="search-filter">
          <div class="searchbox">
            <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
            <input type="text" id="results-search-input" placeholder="Search thesis...">
            <button class="search-btn" id="results-search-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
          </div>
          
          <div class="filter-controls">
            <div class="select" id="filterDropdown">
              <div class="selected">
                <span>All Departments</span>
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
              </div>
              <div class="options">
                <div data-value="all">All Departments (200)</div>
                <div data-value="cs">BECED | AECES (25)</div>
                <div data-value="it">BSED English | AFSET (30)</div>
                <div data-value="ce">BSED Filipino | AFSET (28)</div>
                <div data-value="ee">BSED Math | AFSET (32)</div>
                <div data-value="me">BTVTED | FTVETS (35)</div>
                <div data-value="ee">BEED | OFEE (20)</div>
                <div data-value="me">BSNED | OFSET (15)</div>
                <div data-value="ce">BSABE | SABES (25)</div>
                <div data-value="it">BSIT | SITS (40)</div>
              </div>
            </div>
            
            <div class="select" id="sortDropdown">
              <div class="selected">
                <span>Sort by: Recent</span>
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
              </div>
              <div class="options">
                <div data-value="recent">Most Recent</div>
                <div data-value="popular">Most Viewed</div>
                <div data-value="title">Title (A-Z)</div>
                <div data-value="department">Department</div>
              </div>
            </div>
            
            <div class="display-group">
              <div class="icon" id="listViewIcon"> <i class="fa fa-bars" aria-hidden="true"></i> </div>
              <div class="icon selected" id="gridViewIcon"> <i class="fa fa-th" aria-hidden="true"></i> </div>
            </div>
          </div>
        </section>
        <div class="results-title">
          <p class="results-count">Showing <span id="results-number">0</span> results...</p>
        </div>
      </header>

      <section class="results-container">
        <div class="results grid" id="thesis-results">
          <!-- Results will be populated by JavaScript -->
        </div>
        <div class="pagination">
          <button class="pagination-btn prev" disabled><i class="fas fa-chevron-left"></i> Previous</button>
          <div class="page-numbers">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <span>...</span>
            <button class="page-btn">10</button>
          </div>
          <button class="pagination-btn next">Next <i class="fas fa-chevron-right"></i></button>
        </div>
      </section>
    </section>

    <!-- ================= Footer ================= -->
    <footer class="main-footer">
      <div class="footer-content">
        <div class="footer-section">
          <div>
            <img class="logo" src="../../../resources/images/ThesisCompLogo.png" alt="Logo" />
           
            <img class="logo" src="../../../resources/images/CTET_LOGO.png" alt="Logo" />
            <h3>Thesis Compendium System</h3>
          </div>
          <p class="footer-description">A comprehensive digital repository for thesis papers and capstone projects.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        
        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="javascript:window.location.reload(true)">Home</a></li>
            <li><a href="#">Browse</a></li>
            <li><a href="#">Guidelines</a></li>
            <li><a href="#">Login</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h4>Resources</h4>
          <ul>
            <li><a href="#">Research Guidelines</a></li>
            <li><a href="#">Formatting Templates</a></li>
            <li><a href="#">Citation Help</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Support Center</a></li>
          </ul>
        </div>
        
        <div class="footer-section">
          <h4>Contact Us</h4>
          <div class="contact-info">
            <p><i class="fas fa-envelope"></i> thesis@usep.edu.ph</p>
            <p><i class="fas fa-phone"></i> 0123 456 7890</p>
            <p><i class="fas fa-map-marker-alt"></i> University of Southeastern Philippines<br>Tagum-Mabini Campus</p>
          </div>
        </div>
      </div>
      
      <div class="footer-bottom">
        <p>&copy; 2025 Thesis Compendium System. All rights reserved.</p>
        <div class="footer-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Accessibility</a>
        </div>
      </div>
    </footer>
  </div>

  <script src="../../../resources/js/User/home.js"></script>
</body>
</html>