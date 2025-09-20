<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thesis Compendium System</title>
  <link rel="stylesheet" href="../../../resources/css/User/home.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand">
  <script defer src="../../../resources/js/User/home.js"></script>
</head>
<body>
  <div class="dashboard-container">
    <!-- Header -->
    <header class="main-header">
      <div class="logo">
        <img src="../../../resources/images/ThesisCompLogo.png" alt="Logo" />
        <h1>Thesis Compendium System</h1>
      </div>
      <nav class="tabs">
        <a href="#" >Login</a>
      </nav>
    </header>

    <!-- ================= Landing Page ================= -->
    <section id="landing-page">
      <!-- Search Banner -->
      <section class="search-banner">
        <h2>Search Thesis Repository</h2>
        <div class="search-container">
          <div class="searchbox">
            <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
            <input type="text" id="search-input" placeholder="Enter keywords, title, author, or adviser...">
            <button class="search-btn" id="search-btn">Search</button>
          </div>
        </div>
      </section>

      <!-- Announcements Carousel -->
      <section class="announcements-section">
        <div class="section-header">
          <h2>Announcements</h2>
          
        </div>
        
        <div class="announcements-carousel">
          <div class="carousel-container">
            <div class="announcement-cards">
              <div class="announcement-card">
                <div class="card-image">
                  <img class="Anncmnt_pic" src="../../../resources/images/Announcement_pic.png" alt="System Launch" />
                </div>
                <div class="card-content">
                  <div class="card-header">
                    <h3>System Launch</h3>
                    <span class="date">September 15, 2025</span>
                  </div>
                  <p>The Thesis Compendium System officially goes live this semester. All students are encouraged to register and explore the new features.</p>
                  <div class="card-footer">
                    <span class="tag important">Important</span>
                  </div>
                </div>
              </div>
              
              <div class="announcement-card">
                <div class="card-image">
                  <img class="Anncmnt_pic" src="../../../resources/images/Announcement_pic.png" alt="Submission Deadline" />
                </div>
                <div class="card-content">
                  <div class="card-header">
                    <h3>Submission Deadline</h3>
                    <span class="date">September 20, 2025</span>
                  </div>
                  <p>Capstone project submissions are due on October 10, 2025. Please ensure all documents are properly formatted and submitted on time.</p>
                  <div class="card-footer">
                    <span class="tag deadline">Deadline</span>
                  </div>
                </div>
              </div>
              
              <div class="announcement-card">
                <div class="card-image">
                  <img class="Anncmnt_pic" src="../../../resources/images/Announcement_pic.png" alt="Maintenance" />
                </div>
                <div class="card-content">
                  <div class="card-header">
                    <h3>System Maintenance</h3>
                    <span class="date">September 25, 2025</span>
                  </div>
                  <p>System maintenance will occur on October 1, 2025, from 12AM–4AM. The system will be unavailable during this period.</p>
                  <div class="card-footer">
                    <span class="tag info">Information</span>
                  </div>
                </div>
              </div>
              
              <div class="announcement-card">
                <div class="card-image">
                  <img class="Anncmnt_pic" src="../../../resources/images/Announcement_pic.png" alt="Workshop" />
                </div>
                <div class="card-content">
                  <div class="card-header">
                    <h3>Thesis Writing Workshop</h3>
                    <span class="date">October 5, 2025</span>
                  </div>
                  <p>Join our thesis writing workshop on October 15th. Learn how to structure your research and use the system effectively.</p>
                  <div class="card-footer">
                    <span class="tag event">Event</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <button class="carousel-control prev" aria-label="Previous announcements">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="carousel-control next" aria-label="Next announcements">
            <i class="fas fa-chevron-right"></i>
          </button>
          
          <div class="carousel-indicators"></div>
        </div>
      </section>
    </section>

    <!-- ================= Search Results Page ================= -->
    <section id="results-page" class="hidden">
      <header class="results-header">
        <h2>Search Results</h2>
        
        <section class="search-filter">
          <div class="searchbox">
            <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
            <input type="text" id="results-search-input" placeholder="Search thesis...">
            <button class="search-btn" id="results-search-btn">🔍</button>
          </div>
          
          <div class="filter-controls">
            <div class="select" id="filterDropdown">
              <div class="selected">
                <span>All Departments</span>
                <i class="fa fa-filter" aria-hidden="true"></i>
              </div>
              <div class="options">
                <div data-value="all">All Departments</div>
                <div data-value="cs">Computer Science</div>
                <div data-value="it">Information Technology</div>
                <div data-value="ce">Computer Engineering</div>
              </div>
            </div>
            
            <div class="display-group">
              <button class="toggle-view list-view"><i class="fa fa-bars" aria-hidden="true"></i></button>
              <button class="toggle-view grid-view active"><i class="fa fa-th" aria-hidden="true"></i></button>
            </div>
          </div>
        </section>
      </header>

      <section class="results-container">
        <div class="results grid" id="thesis-results">
          <!-- Results will be populated by JavaScript -->
        </div>
      </section>
    </section>

    <!-- ================= Footer ================= -->
    <footer class="footer1" id="footer1">
      <p><strong>Developed by Students of University of Southeastern Philippines Tagum-Mabini Campus</strong></p>
      <p>This System was created by students of the University of Southeastern Philippines Tagum-Mabini Campus, showcasing innovation and dedication to secure and reliable thesis management.</p>
    </footer>

    <footer class="footer2">
      <div class="footer-section">
        <p class="footer-title">Quick Link</p>
        <a href="#">Sign In</a>
      </div>
      
      <div class="footer-section">
        <p class="footer-title">Contact</p>
        <div class="contact-info">
          <span>Email: email@usep.edu.ph</span>
          <span>University of Southeastern Philippines</span>
          <span>College of Teachers Education and Technology</span>
        </div>
      </div>

      <div class="footer-divider"></div>

      <p class="copyright">© 2025 Thesis Compendium System. All rights reserved.</p>
    </footer>
  </div>
</body>
</html>