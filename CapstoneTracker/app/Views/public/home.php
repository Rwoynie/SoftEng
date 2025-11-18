<?php
require_once '../../../Database/config.php';
require_once '../../Controllers/Controller.php';  
require_once '../../Controllers/PublicHomeController.php';  
require_once '../../Models/Thesis.php';  
require_once '../../Models/PublicHomeModel.php';  

$model = new PublicHomeModel();
$announcements = $model->getActiveAnnouncements();
$programs = $model->getPrograms();
$stats = $model->getThesisStats();

$pinnedAnnouncements = array_filter($announcements, function($ann) {
    return isset($ann['pinned']) && $ann['pinned'] == true;  
});

$nonPinnedAnnouncements = array_filter($announcements, function($ann) {
    return !isset($ann['pinned']) || $ann['pinned'] != true;
});

?>

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

  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  

  
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
        <a href="../User/indexLogin.php" class="btn-login">Login</a>
      </nav>
    </header>

    <!-- ================= Landing Page ================= -->
    <section id="landing-page">
      <!-- Search Banner -->
      <section class="search-banner">
        <div id="particles-js" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;"></div>
  <div class="gradient-overlay"></div>
        <div class="banner-content">
          <h2>Discover Academic Excellence</h2>
          <p>Access hundreds of thesis papers from different departments</p>
          <div class="search-container">
            <form id="search-form" method="POST" action="search.php">
              <div class="searchbox">
                <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
                <input type="text" id="search-input" name="query" placeholder="Enter keywords, title, author, or adviser...">
                <button type="submit" class="search-btn" id="search-btn">Search</button>
              </div>
            </form>
          </div>
          <div class="stats">
            <div class="stat-item">
              <span class="stat-number"><?php echo $stats['total_papers'] ?? '0'; ?></span>
              <span class="stat-label">Thesis Papers</span>
            </div>
            <div class="stat-item">
              <span class="stat-number"><?php echo $stats['total_authors'] ?? '0'; ?></span>
              <span class="stat-label">Active Researchers</span>
            </div>
            <div class="stat-item">
              <span class="stat-number"><?php echo $stats['total_departments'] ?? '0'; ?></span>
              <span class="stat-label">Departments</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Announcements Section -->
      <section class="announcements-section">
          <div class="section-header">
              <h2>Announcements</h2>
          </div>

          <?php
          $pinnedAnnouncements = [];
          $nonPinnedAnnouncements = [];

          if (!empty($announcements)) {
              foreach ($announcements as $ann) {
                  $isPinned = false;
                  
                  if (isset($ann['pinned']) && $ann['pinned'] == true) {
                      $isPinned = true;
                  } elseif (isset($ann['is_pinned']) && $ann['is_pinned'] == true) {
                      $isPinned = true;
                  } elseif (isset($ann['pinned']) && $ann['pinned'] == 1) {
                      $isPinned = true;
                  } elseif (isset($ann['is_pinned']) && $ann['is_pinned'] == 1) {
                      $isPinned = true;
                  }
                  
                  if ($isPinned) {
                      $pinnedAnnouncements[] = $ann;
                  } else {
                      $nonPinnedAnnouncements[] = $ann;
                  }
              }
          }
          
          // Debug: Uncomment the lines below to see what's being fetched
          // echo "<!-- Total announcements: " . count($announcements) . " -->";
          // echo "<!-- Pinned: " . count($pinnedAnnouncements) . " -->";
          // echo "<!-- Non-pinned: " . count($nonPinnedAnnouncements) . " -->";
          ?>

          <!-- Pinned Announcements -->
          <?php if (!empty($pinnedAnnouncements)): ?>
          <div class="pinned-section">
              <h3 style="text-align: center; margin-bottom: 1.5rem; color: var(--primary-color);">
                  Pinned Announcements
              </h3>
              <div class="pinned-announcements-carousel">
                  <button class="carousel-control prev"><i class="fas fa-chevron-left"></i></button>

                  <div class="carousel-container">
                      <div class="announcement-cards">
                          <?php foreach ($pinnedAnnouncements as $index => $announcement): ?>
                              <?php 
                              // Use actual ID or create a unique identifier
                              $announcementId = $announcement['id'] ?? 'pinned_' . $index;
                              ?>
                              <div class="announcement-card pinned" 
                                  data-announcement-id="<?php echo $announcementId; ?>">
                                  <div class="pin-indicator" title="Pinned Announcement">
                                      <i class="fas fa-thumbtack"></i>
                                  </div>
                                  <div class="card-badge <?php echo htmlspecialchars($announcement['type'] ?? 'info'); ?>">
                                      <?php 
                                      $badgeTexts = [
                                          'important' => 'Important',
                                          'deadline' => 'Deadline',
                                          'info' => 'Information',
                                          'event' => 'Event',
                                          'information' => 'Information'
                                      ];
                                      echo $badgeTexts[$announcement['type'] ?? 'info'] ?? 'Announcement';
                                      ?>
                                  </div>
                                  <div class="card-image">
                                      <img src="<?php echo htmlspecialchars($announcement['image'] ?? '../../../resources/images/default-announcement.jpg'); ?>" 
                                          alt="<?php echo htmlspecialchars($announcement['title']); ?>" 
                                          class="Anncmnt_pic">
                                  </div>
                                  <div class="card-content">
                                      <div class="card-header">
                                          <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                          <div class="date">
                                              <?php echo date('F j, Y', strtotime($announcement['date'] ?? $announcement['created_at'] ?? 'now')); ?>
                                          </div>
                                      </div>
                                      <p class="announcement-preview"><?php echo htmlspecialchars($announcement['description'] ?? $announcement['content'] ?? ''); ?></p>
                                      <a href="#" class="read-more" data-announcement-id="<?php echo $announcementId; ?>">
                                          Read More <i class="fas fa-arrow-right"></i>
                                      </a>
                                  </div>
                              </div>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <button class="carousel-control next"><i class="fas fa-chevron-right"></i></button>

                  <?php if (count($pinnedAnnouncements) > 1): ?>
                  <div class="carousel-indicators">
                      <?php for ($i = 0; $i < count($pinnedAnnouncements); $i++): ?>
                          <div class="indicator <?php echo $i === 0 ? 'active' : ''; ?>"></div>
                      <?php endfor; ?>
                  </div>
                  <?php endif; ?>
              </div>
          </div>
          <?php endif; ?>

          <!-- Non-Pinned Announcements -->
          <?php if (!empty($nonPinnedAnnouncements)): ?>
              <?php if (!empty($pinnedAnnouncements)): ?>
              <div style="margin-top: 4rem;">
                  <h3 style="text-align: center; margin-bottom: 1.5rem; color: var(--color-dark-grey);">
                      Recent Announcements
                  </h3>
              </div>
              <?php endif; ?>

              <div class="non-pinned-announcements-carousel">
                  <button class="carousel-control prev"><i class="fas fa-chevron-left"></i></button>

                  <div class="carousel-container">
                      <div class="announcement-cards">
                          <?php foreach ($nonPinnedAnnouncements as $index => $announcement): ?>
                              <?php 
                              $announcementId = $announcement['id'] ?? 'nonpinned_' . $index;
                              ?>
                              <div class="announcement-card" 
                                  data-announcement-id="<?php echo $announcementId; ?>">
                                  <div class="card-badge <?php echo htmlspecialchars($announcement['type'] ?? 'info'); ?>">
                                      <?php echo $badgeTexts[$announcement['type'] ?? 'info'] ?? 'Announcement'; ?>
                                  </div>
                                  <div class="card-image">
                                      <img src="<?php echo htmlspecialchars($announcement['image'] ?? '../../../resources/images/default-announcement.jpg'); ?>" 
                                          alt="<?php echo htmlspecialchars($announcement['title']); ?>" 
                                          class="Anncmnt_pic">
                                  </div>
                                  <div class="card-content">
                                      <div class="card-header">
                                          <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                          <div class="date">
                                              <?php echo date('F j, Y', strtotime($announcement['date'] ?? $announcement['created_at'] ?? 'now')); ?>
                                          </div>
                                      </div>
                                      <p class="announcement-preview"><?php echo htmlspecialchars($announcement['description'] ?? $announcement['content'] ?? ''); ?></p>
                                      <a href="#" class="read-more" data-announcement-id="<?php echo $announcementId; ?>">
                                          Read More <i class="fas fa-arrow-right"></i>
                                      </a>
                                  </div>
                              </div>
                          <?php endforeach; ?>
                      </div>
                  </div>

                  <button class="carousel-control next"><i class="fas fa-chevron-right"></i></button>

                  <?php if (count($nonPinnedAnnouncements) > 1): ?>
                  <div class="carousel-indicators">
                      <?php for ($i = 0; $i < count($nonPinnedAnnouncements); $i++): ?>
                          <div class="indicator <?php echo $i === 0 ? 'active' : ''; ?>"></div>
                      <?php endfor; ?>
                  </div>
                  <?php endif; ?>
              </div>
          <?php endif; ?>

          <!-- No Announcements at all -->
          <?php if (empty($pinnedAnnouncements) && empty($nonPinnedAnnouncements)): ?>
          <div class="announcement-card empty-state">
              <div class="card-content">
                  <div class="card-header">
                      <h3>No Current Announcements</h3>
                  </div>
                  <p>Check back later for updates and important information.</p>
              </div>
          </div>
          <?php endif; ?>
      </section>

<!-- MODAL (Fixed $isPinned error) -->
<div id="announcementModal" class="premium-modal">
    <div class="premium-modal-backdrop"></div>
    <div class="premium-modal-container">
        <div class="premium-modal-content">
            <div class="premium-modal-header">
                <div class="premium-modal-badge-container">
                    <span id="modalBadge" class="premium-modal-badge"></span>
                    <div class="premium-pin-indicator" style="display: none;">
                        Pin
                    </div>
                </div>
                <button type="button" class="premium-close-btn" onclick="closeAnnModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="premium-modal-body">
                <div class="premium-modal-image-container">
                    <img id="modalImage" src="" alt="Announcement image" class="premium-modal-image">
                    <div class="premium-modal-image-overlay"></div>
                </div>
                
                <div class="premium-modal-text-content">
                    <div class="premium-modal-meta">
                        <h2 id="modalTitle" class="premium-modal-title"></h2>
                        <div class="premium-modal-date-container">
                            <i class="fas fa-calendar-alt"></i>
                            <span id="modalDate" class="premium-modal-date"></span>
                        </div>
                    </div>
                    <div class="premium-modal-text">
                        <p id="modalContent"></p>
                    </div>
                </div>
            </div>

            <div class="premium-modal-footer">
                <div class="premium-modal-actions">
                    <button type="button" class="premium-btn secondary" onclick="closeAnnModal()">
                        Close
                    </button>
                    <button type="button" class="premium-btn primary" onclick="shareAnnouncement()">
                        Share
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

      <!-- Programs Carousel -->
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
              <?php if (!empty($programs)): ?>
                <?php foreach ($programs as $program): ?>
                  <div class="logo-card">
                    <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['name']); ?>">
                    <div class="card-content">
                      <h3><?php echo htmlspecialchars($program['name']); ?></h3>
                      <p><?php echo htmlspecialchars($program['meaning']); ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="logo-card empty-state">
                  <div class="card-content">
                    <h3>No Programs Available</h3>
                    <p>Check back later.</p>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <button class="carousel-control next">
            <i class="fas fa-chevron-right"></i>
          </button>
          <div class="carousel-indicators">
            <?php if (!empty($programs)): ?>
              <?php for ($i = 0; $i < count($programs); $i++): ?>
                <div class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
              <?php endfor; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Call to Action -->
      <section class="call-to-action-section">
        <div class="action-cards">
          <div class="action-card">
            <div class="action-icon">
              <i class="fas fa-book-open"></i>
            </div>
            <h3>Browse Thesis</h3>
            <p>Explore all available thesis papers</p>
            <a href="search.php" class="read-more">Browse Now <i class="fas fa-arrow-right"></i></a>
          </div>
          <div class="action-card">
            <div class="action-icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <h3>For Researchers</h3>
            <p>Resources and guidelines for your research</p>
            <a href="#" class="read-more">View Resources <i class="fas fa-arrow-right"></i></a>
          </div>
          <div class="action-card">
            <div class="action-icon">
              <i class="fas fa-question-circle"></i>
            </div>
            <h3>Help Center</h3>
            <p>Get assistance with the system</p>
            <a href="#" class="read-more">Get Help <i class="fas fa-arrow-right"></i></a>
          </div>
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
            <li><a href="/browse">Browse</a></li>
            <li><a href="#">Guidelines</a></li>
            <li><a href="../../../app/Views/User/indexLogin.php">Login</a></li>
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
  <script>

particlesJS('particles-js', {
  particles: {
    number: { value: 80, density: { enable: true, value_area: 800 } },
    color: { value: "#ffffff" },
    shape: { type: "circle" },
    opacity: { value: 0.5, random: true },
    size: { value: 3, random: true },
    line_linked: {
      enable: true,
      distance: 200,
      color: "#ffffff",
      opacity: 0.3,
      width: 1
    },
    move: { enable: true, speed: 2, direction: "none", random: true }
  },
  interactivity: {
    detect_on: "canvas",
    events: {
      onhover: { enable: true, mode: "repulse" },
      onclick: { enable: true, mode: "push" }
    }
  }
});
</script>
</body>
</html>