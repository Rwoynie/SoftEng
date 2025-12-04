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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  

  
</head>
<body>
  
  <div class="dashboard-container">
    <!-- Header -->
    <header class="main-header">
      <div class="logo">
        <a href="home.php"><img src="../../../resources/images/ThesisCompLogo.png" alt="Logo" /></a>
        <a href="home.php" style="text-decoration: none">
          <h1>Thesis Compendium System</h1>
          <h3>University of Southeastern Philippines</h3>
        </a>
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



      <!-- Privacy Policy Modal -->
      <div id="privacyModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Privacy Policy</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('privacyModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">USeP Data Privacy Statement</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>The right to privacy is a fundamental human right. Acknowledging this, the University of Southeastern Philippines, hereafter referred to as "University", endeavors to safeguard its stakeholders' data privacy by adhering to data privacy principles and employing standard safety measures in the collection, processing, disclosure and retention of personal data in accordance with the Data Privacy Act of 2012 (R.A. 10173), its Implementing Rules and Regulations (IRR) and to issuances of the National Privacy Commission.</p>
                              
                              <p>This University Data Privacy Statement (the "UDPS") contains an outline of the general practices of the University in the context of data collection and processing. All other data privacy statements released or to be released by the University specific to a particular office, function or procedure shall be in congruence with the UDPS. Designed for general knowledge, the UDPS may not include specific information pertaining to the data collection and processing mechanism of a specific office, function or procedure. Thus, whenever applicable, a more specific data privacy statement or notice should be consulted.</p>
                              
                              <p>For a comprehensive and detailed view of the University's data privacy policies, please refer to the <em>University's Data Privacy Manual</em>.</p>
                              
                              <h3>What personal data the University may collect and process?</h3>
                              <p>The University collects and processes only the type and amount of data necessary to perform its core and auxiliary functions. As an institution composed of heterogeneous entities, the University may collect a variety of personal information in different contexts and for different specific purposes.</p>
                              
                              <p>In general, among the common personal data the University may collect include:</p>
                              <ul>
                                  <li>Name</li>
                                  <li>Specimen signatures</li>
                                  <li>Home address</li>
                                  <li>Email address</li>
                                  <li>Biographical information</li>
                                  <li>Academic information</li>
                                  <li>Nationality</li>
                                  <li>Phone number</li>
                                  <li>Government or Non-government Identification Number / Card</li>
                                  <li>Financial information</li>
                                  <li>Employment details</li>
                                  <li>Images via CCTV and other similar recording devices</li>
                                  <li>Internet Protocol (IP) addresses</li>
                                  <li>Session Cookie data</li>
                              </ul>
                              
                              <p>As a premiere research institution, the University may also collect sensitive personal information in the conduct of relevant researches and studies. For instance, a University-affiliated researcher may collect data pertaining to an individual's ethnic origin, political opinions or criminal history to achieve the objectives of a particular study.</p>
                              
                              <p>All personal data collection and processing can only be done when the University acquires the consent of the data subject, either explicitly or implicitly, after the latter has been informed of the nature and extent of data collection and processing.</p>
                              
                              <h3>Why does the University collect and process personal data?</h3>
                              <p>The purpose of personal data collection and processing may vary from one University procedure (e.g. student admission, visitor entry, human resource management, etc.) to another. However, the general principle governing the University's data collection process is legitimacy of purpose.</p>
                              
                              <p>The University shall only collect and process data for legitimate purposes in consonance with its inherent functions and in compliance with legal requirements. These legitimate purposes may include, but may not be limited to, the following:</p>
                              
                              <ul>
                                  <li>To verify students' and employees' identity;</li>
                                  <li>To generate statistics and analytics useful for administrative decisions;</li>
                                  <li>To strengthen security measures and facilitate investigations of reported violations;</li>
                                  <li>To easily generate statutory reports;</li>
                                  <li>For employee and human resources management purposes (as may be required by applicable laws);</li>
                                  <li>For research purposes or endeavors contributing to the body of knowledge;</li>
                                  <li>To comply with legal or regulatory obligations;</li>
                                  <li>To establish, exercise or defend legal claims</li>
                              </ul>
                              
                              <h3>How does the University share or disclose personal data?</h3>
                              <p>Utmost care and due diligence are practiced by the University in handling personal data. The University shall never share or disclose data to third-parties without prior consent from the data subjects. Whenever disclosure of data is necessary and permitted, the University conscientiously reviews the privacy and security policies of the authorized third-party service providers or external partners. The University may also be required to disclose data in compliance with legal or regulatory obligations.</p>
                              
                              <p>Internal disclosure of personal data from one University entity to another shall be subjected to an institutionalized standard data request procedure. This ensures that data is transmitted through official channels and shared for legitimate purposes.</p>
                              
                              <p>Regardless of the context of data disclosure, the University shall always practice the principle of data minimization which means that only the minimum amount of data needed to serve a particular purpose is shared to the requesting entity.</p>
                              
                              <h3>How does the University protect personal data?</h3>
                              <p>The University shall employ necessary or reasonable safeguards in the form of physical, technological, logical and administrative controls. Internal access to stored personal data will be kept to a minimum number of authorized individuals and bounded by confidentiality agreements. These individuals are subjected to regular training for proper handling of information in accordance to the University's data privacy policies and other related laws, regulations or issuances.</p>
                              
                              <h3>How long does the University retain personal data?</h3>
                              <p>Personal data are retained only for as long as necessary to serve its declared purpose or comply with regulatory and legal requirements. Depending on the nature of data and purpose it serves, the retention period could range from days (e.g. CCTV recording) to years (e.g. student academic information). Whenever retention becomes unnecessary, the University shall dispose the personal data properly through a secure and confidential means.</p>
                              
                              <p>For concerns and inquiries relating to the University Data Privacy, please don't hesitate to contact the University Data Privacy Officer, Ms. Dessa L. Caballero of the University Data Privacy Office (UDPO) through this email: <a href="mailto:ola-udpo@usep.edu.ph">ola-udpo@usep.edu.ph</a>.</p>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('privacyModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Terms of Service Modal -->
      <div id="termsModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Terms of Service</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('termsModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Terms of Service</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>Welcome to the Thesis Compendium System of the University of Southeastern Philippines. By accessing or using our system, you agree to comply with and be bound by the following terms and conditions.</p>
                              
                              <h3>1. Acceptance of Terms</h3>
                              <p>By accessing and using the Thesis Compendium System, you accept and agree to be bound by the terms and provisions of this agreement. If you do not agree to abide by these terms, please do not use this system.</p>
                              
                              <h3>2. Use License</h3>
                              <p>Permission is granted to temporarily access the materials (information or software) on the Thesis Compendium System for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title.</p>
                              
                              <h3>3. User Accounts</h3>
                              <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account.</p>
                              
                              <h3>4. Intellectual Property</h3>
                              <p>The content, organization, graphics, design, compilation, and other matters related to the Thesis Compendium System are protected under applicable copyrights, trademarks, and other proprietary rights.</p>
                              
                              <h3>5. User Responsibilities</h3>
                              <p>Users are responsible for maintaining the confidentiality of their account and password and for restricting access to their computer. Users agree to accept responsibility for all activities that occur under their account or password.</p>
                              
                              <h3>6. Prohibited Uses</h3>
                              <p>Users are prohibited from violating or attempting to violate any security features of the system, including accessing content or data not intended for them, or attempting to probe, scan, or test the vulnerability of the system.</p>
                              
                              <h3>7. Termination</h3>
                              <p>We may terminate or suspend access to our system immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
                              
                              <h3>8. Changes to Terms</h3>
                              <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. By continuing to access or use our system after those revisions become effective, you agree to be bound by the revised terms.</p>
                              
                              <h3>9. Contact Information</h3>
                              <p>If you have any questions about these Terms, please contact us at <a href="mailto:thesis@usep.edu.ph">thesis@usep.edu.ph</a>.</p>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('termsModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Accessibility Modal -->
      <div id="accessibilityModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Accessibility</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('accessibilityModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Accessibility Statement</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>The University of Southeastern Philippines is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.</p>
                              
                              <h3>Conformance Status</h3>
                              <p>The Thesis Compendium System aims to conform with the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA. These guidelines explain how to make web content more accessible for people with disabilities.</p>
                              
                              <h3>Accessibility Features</h3>
                              <ul>
                                  <li>Keyboard navigation support</li>
                                  <li>Text alternatives for non-text content</li>
                                  <li>Content that can be presented in different ways without losing information</li>
                                  <li>Content that is easier to see and hear</li>
                                  <li>Clear and consistent navigation</li>
                              </ul>
                              
                              <h3>Feedback</h3>
                              <p>We welcome your feedback on the accessibility of the Thesis Compendium System. Please let us know if you encounter accessibility barriers:</p>
                              <ul>
                                  <li>Email: <a href="mailto:thesis@usep.edu.ph">thesis@usep.edu.ph</a></li>
                                  <li>Phone: 0123 456 7890</li>
                              </ul>
                              
                              <h3>Technical Specifications</h3>
                              <p>Accessibility of the Thesis Compendium System relies on the following technologies to work with the particular combination of web browser and any assistive technologies or plugins installed on your computer:</p>
                              <ul>
                                  <li>HTML</li>
                                  <li>CSS</li>
                                  <li>JavaScript</li>
                              </ul>
                              
                              <h3>Assessment Approach</h3>
                              <p>The University of Southeastern Philippines assesses the accessibility of the Thesis Compendium System by the following approaches:</p>
                              <ul>
                                  <li>Self-evaluation</li>
                                  <li>External evaluation</li>
                                  <li>Continuous monitoring and improvement</li>
                              </ul>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('accessibilityModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Research Guidelines Modal -->
      <div id="researchGuidelinesModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Research Guidelines</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('researchGuidelinesModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Research Guidelines</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>These guidelines are designed to help researchers conduct high-quality, ethical research and prepare their theses according to University standards.</p>
                              
                              <h3>Research Proposal Requirements</h3>
                              <ul>
                                  <li>Clear statement of the research problem</li>
                                  <li>Comprehensive literature review</li>
                                  <li>Well-defined research methodology</li>
                                  <li>Realistic timeline and budget (if applicable)</li>
                                  <li>Expected outcomes and significance</li>
                              </ul>
                              
                              <h3>Ethical Considerations</h3>
                              <p>All research involving human subjects must receive approval from the University's Ethics Review Committee before data collection begins.</p>
                              
                              <h3>Thesis Formatting Requirements</h3>
                              <ul>
                                  <li>Use A4 size paper</li>
                                  <li>1.5 line spacing for body text</li>
                                  <li>Times New Roman, 12-point font</li>
                                  <li>1-inch margins on all sides</li>
                                  <li>Page numbers in the top right corner</li>
                              </ul>
                              
                              <h3>Submission Deadlines</h3>
                              <p>Please refer to the academic calendar for specific submission deadlines for each semester.</p>
                              
                              <h3>Plagiarism Policy</h3>
                              <p>The University maintains a strict policy against plagiarism. All theses will be screened using plagiarism detection software.</p>
                              
                              <h3>Resources Available</h3>
                              <ul>
                                  <li>Research consultation with faculty advisors</li>
                                  <li>Statistical analysis support</li>
                                  <li>Writing center assistance</li>
                                  <li>Library research resources</li>
                              </ul>
                              
                              <h3>Contact Information</h3>
                              <p>For questions about research guidelines, please contact the Research and Development Office at <a href="mailto:research@usep.edu.ph">research@usep.edu.ph</a>.</p>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('researchGuidelinesModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Formatting Templates Modal -->
      <div id="formattingTemplatesModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Formatting Templates</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('formattingTemplatesModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Formatting Templates</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>To ensure consistency and compliance with University standards, please use the following templates for your thesis documents.</p>
                              
                              <h3>Available Templates</h3>
                              
                              <div class="template-list">
                                  <div class="template-item">
                                      <h4>Thesis Template (Word Document)</h4>
                                      <p>Complete template with proper formatting, styles, and sections.</p>
                                      
                                  </div>
                                  
                                  <div class="template-item">
                                      <h4>Thesis Template (LaTeX)</h4>
                                      <p>For researchers preferring LaTeX typesetting.</p>
                                      
                                  </div>
                                  
                                  <div class="template-item">
                                      <h4>Title Page Template</h4>
                                      <p>Standardized title page format.</p>
                                      
                                  </div>
                                  
                                  <div class="template-item">
                                      <h4>Citation Template</h4>
                                      <p>Examples of proper citation formats.</p>
                                      
                                  </div>
                              </div>
                              
                              <h3>Formatting Guidelines</h3>
                              <ul>
                                  <li>Use A4 size paper (8.27" x 11.69")</li>
                                  <li>1.5 line spacing for body text</li>
                                  <li>Single spacing for long quotations, footnotes, and references</li>
                                  <li>Times New Roman, 12-point font for body text</li>
                                  <li>1-inch margins on all sides</li>
                                  <li>Page numbers in the top right corner (excluding title page)</li>
                                  <li>Chapters start on new pages</li>
                              </ul>
                              
                              <h3>Required Sections</h3>
                              <ol>
                                  <li>Title Page</li>
                                  <li>Abstract</li>
                                  <li>Table of Contents</li>
                                  <li>List of Tables/Figures</li>
                                  <li>Introduction</li>
                                  <li>Literature Review</li>
                                  <li>Methodology</li>
                                  <li>Results</li>
                                  <li>Discussion</li>
                                  <li>Conclusion and Recommendations</li>
                                  <li>References</li>
                                  <li>Appendices (if applicable)</li>
                              </ol>
                              
                              <h3>Need Help?</h3>
                              <p>If you encounter issues with the templates or have formatting questions, please contact the Thesis Office at <a href="mailto:thesis@usep.edu.ph">thesis@usep.edu.ph</a>.</p>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('formattingTemplatesModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Citation Help Modal -->
      <div id="citationHelpModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Citation Help</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('citationHelpModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Citation Help</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>Proper citation is essential for academic integrity. The Thesis Compendium System uses APA (American Psychological Association) 7th edition style as the standard citation format.</p>
                              
                              <h3>Copy Citation Button</h3>
                              <p>When viewing a thesis in the system, simply click the "Copy Citation" button to automatically copy a properly formatted citation to your clipboard.</p>
                              
                              <div class="citation-example">
                                  <h4>Example Citation:</h4>
                                  <div class="citation-box">
                                      <p id="citationExample">Dela Cruz, J. M., & Santos, M. P. (2023). <em>Impact of digital learning tools on student engagement in higher education</em>. University of Southeastern Philippines.</p>
                                      
                                  </div>
                              </div>
                              
                              <h3>APA Citation Guidelines</h3>
                              
                              <h4>Books:</h4>
                              <p>Author, A. A. (Year). <em>Title of work</em>. Publisher.</p>
                              
                              <h4>Journal Articles:</h4>
                              <p>Author, A. A., & Author, B. B. (Year). Title of article. <em>Title of Journal, volume</em>(issue), page range.</p>
                              
                              <h4>Thesis/Dissertation:</h4>
                              <p>Author, A. A. (Year). <em>Title of thesis</em> (Publication No.) [Doctoral dissertation/Master's thesis, Name of Institution]. Name of Database.</p>
                              
                              <h4>Websites:</h4>
                              <p>Author, A. A. (Year, Month Day). <em>Title of webpage</em>. Site Name. URL</p>
                              
                              <h3>Citation Management Tools</h3>
                              <p>Consider using these tools to manage your references:</p>
                              <ul>
                                  <li>Zotero (free)</li>
                                  <li>Mendeley (free)</li>
                                  <li>EndNote (subscription)</li>
                              </ul>
                              
                              <h3>Need Additional Help?</h3>
                              <p>For citation questions not covered here, please consult the APA Manual (7th edition) or contact the University Library at <a href="mailto:library@usep.edu.ph">library@usep.edu.ph</a>.</p>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('citationHelpModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- FAQ Modal -->
      <div id="faqModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">FAQ</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('faqModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Frequently Asked Questions</h2>
                          </div>
                          <div class="premium-modal-text">
                              <div class="faq-section">
                                  <h3>General Questions</h3>
                                  
                                  <div class="faq-item">
                                      <h4>What is the Thesis Compendium System?</h4>
                                      <p>The Thesis Compendium System is a digital repository for thesis papers and capstone projects from the University of Southeastern Philippines. It provides access to academic research for students, faculty, and researchers.</p>
                                  </div>
                                  
                                  <div class="faq-item">
                                      <h4>Who can access the system?</h4>
                                      <p>The system is publicly accessible for browsing and searching. Some features may require a University account for full access.</p>
                                  </div>
                                  
                                  <div class="faq-item">
                                      <h4>How do I search for specific theses?</h4>
                                      <p>You can use the search bar on the homepage to search by keywords, title, author, or adviser. Advanced search filters are also available on the search page.</p>
                                  </div>
                              </div>
                              
                              <div class="faq-section">
                                  <h3>For Researchers</h3>
                                  
                                  <div class="faq-item">
                                      <h4>How do I submit my thesis to the system?</h4>
                                      <p>Thesis submission is managed through your department. Please consult with your thesis adviser or department chair for submission procedures.</p>
                                  </div>
                                  
                                  <div class="faq-item">
                                      <h4>What format should my thesis be in for submission?</h4>
                                      <p>Theses should be submitted as PDF files. Please refer to the Formatting Templates section for specific formatting requirements.</p>
                                  </div>
                                  
                                  <div class="faq-item">
                                      <h4>Can I embargo my thesis?</h4>
                                      <p>Yes, embargo requests are considered on a case-by-case basis. Please discuss this with your thesis adviser and department chair.</p>
                                  </div>
                              </div>
                              
                              <div class="faq-section">
                                  <h3>Technical Questions</h3>
                                  
                                  <div class="faq-item">
                                      <h4>What if I can't access a thesis?</h4>
                                      <p>Some theses may have restricted access due to embargo periods or other limitations. If you believe you should have access to a specific thesis, please contact the University Library.</p>
                                  </div>
                                  
                                  <div class="faq-item">
                                      <h4>How do I report a technical issue?</h4>
                                      <p>Please report any technical issues to the Support Center at <a href="mailto:support@usep.edu.ph">support@usep.edu.ph</a>.</p>
                                  </div>
                                  
                                  <div class="faq-item">
                                      <h4>Is the system mobile-friendly?</h4>
                                      <p>Yes, the Thesis Compendium System is designed to be responsive and works on various devices including smartphones and tablets.</p>
                                  </div>
                              </div>
                              
                              <div class="contact-prompt">
                                  <p>Can't find the answer to your question? <a href="#" onclick="closeModal('faqModal'); openModal('supportModal');">Contact our Support Center</a>.</p>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('faqModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Support Center Modal -->
      <div id="supportModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Support Center</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('supportModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Support Center</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>We're here to help! Please use the following resources to get assistance with the Thesis Compendium System.</p>
                              
                              <div class="support-options">
                                  <div class="support-option">
                                      <h3><i class="fas fa-envelope"></i> Email Support</h3>
                                      <p>Send us an email at <a href="mailto:support@usep.edu.ph">support@usep.edu.ph</a> and we'll respond within 24-48 hours.</p>
                                  </div>
                                  
                                  <div class="support-option">
                                      <h3><i class="fas fa-phone"></i> Phone Support</h3>
                                      <p>Call us at 0123 456 7890 during business hours (Monday-Friday, 8:00 AM - 5:00 PM).</p>
                                  </div>
                                  
                                  <div class="support-option">
                                      <h3><i class="fas fa-map-marker-alt"></i> In-Person Support</h3>
                                      <p>Visit the IT Help Desk at the University Library, Room 201.</p>
                                  </div>
                                  
                                  <div class="support-option">
                                      <h3><i class="fas fa-calendar"></i> Appointment</h3>
                                      <p>Schedule a one-on-one consultation with our technical support staff.</p>
                                      
                                  </div>
                              </div>
                              
                              <h3>Common Support Requests</h3>
                              <ul>
                                  <li>Account access issues</li>
                                  <li>Thesis submission problems</li>
                                  <li>Search and navigation assistance</li>
                                  <li>Citation formatting questions</li>
                                  <li>Technical errors or bugs</li>
                              </ul>
                              
                              <h3>Before Contacting Support</h3>
                              <p>To help us resolve your issue more quickly, please have the following information ready:</p>
                              <ul>
                                  <li>Your name and contact information</li>
                                  <li>A detailed description of the issue</li>
                                  <li>Steps to reproduce the problem</li>
                                  <li>Screenshots (if applicable)</li>
                                  <li>Your browser and operating system</li>
                              </ul>
                              
                              <div class="emergency-contact">
                                  <h3>Urgent Issues</h3>
                                  <p>For urgent system-wide issues affecting multiple users, please call the emergency support line at 0123 456 7891.</p>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('supportModal')">
                              Close
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Guidelines Modal -->
      <div id="guidelinesModal" class="premium-modal">
          <div class="premium-modal-backdrop"></div>
          <div class="premium-modal-container">
              <div class="premium-modal-content">
                  <div class="premium-modal-header">
                      <div class="premium-modal-badge-container">
                          <span class="premium-modal-badge">Guidelines</span>
                      </div>
                      <button type="button" class="premium-close-btn" onclick="closeModal('guidelinesModal')">
                          <i class="fas fa-times"></i>
                      </button>
                  </div>

                  <div class="premium1-modal-body">
                      <div class="premium-modal-text-content">
                          <div class="premium-modal-meta">
                              <h2 class="premium-modal-title">Thesis Compendium System Guidelines</h2>
                          </div>
                          <div class="premium-modal-text">
                              <p>These guidelines outline the proper use of the Thesis Compendium System for all users.</p>
                              
                              <h3>User Responsibilities</h3>
                              <ul>
                                  <li>Use the system only for legitimate academic purposes</li>
                                  <li>Respect copyright and intellectual property rights</li>
                                  <li>Properly cite all materials accessed through the system</li>
                                  <li>Do not attempt to circumvent access controls</li>
                                  <li>Report any system vulnerabilities or issues to support staff</li>
                              </ul>
                              
                              <h3>Thesis Submission Guidelines</h3>
                              <ul>
                                  <li>All theses must be in PDF format</li>
                                  <li>File size should not exceed 50MB</li>
                                  <li>Metadata must be complete and accurate</li>
                                  <li>Appropriate embargo periods must be specified if needed</li>
                                  <li>Final approval from the thesis committee is required</li>
                              </ul>
                              
                              <h3>Access and Usage</h3>
                              <ul>
                                  <li>Public users can browse and search theses</li>
                                  <li>University affiliates have additional access privileges</li>
                                  <li>Downloading is permitted for personal academic use</li>
                                  <li>Commercial use of thesis content is prohibited without permission</li>
                              </ul>
                              
                              <h3>Citation Requirements</h3>
                              <p>When using content from the Thesis Compendium System in your own work, you must:</p>
                              <ul>
                                  <li>Cite the original author and thesis</li>
                                  <li>Use the citation format provided by the system</li>
                                  <li>Obtain permission for extensive quotations</li>
                              </ul>
                              
                              <h3>Privacy and Data Protection</h3>
                              <p>The system collects minimal user data for functionality and analytics. Please refer to our <a href="#" onclick="closeModal('guidelinesModal'); openModal('privacyModal');">Privacy Policy</a> for detailed information.</p>
                              
                              <h3>Violations and Consequences</h3>
                              <p>Violation of these guidelines may result in:</p>
                              <ul>
                                  <li>Temporary or permanent suspension of system access</li>
                                  <li>Academic disciplinary action for students</li>
                                  <li>Employment disciplinary action for staff</li>
                                  <li>Legal action for serious violations</li>
                              </ul>
                              
                              <h3>Questions About Guidelines</h3>
                              <p>If you have questions about these guidelines, please contact the Thesis Office at <a href="mailto:thesis@usep.edu.ph">thesis@usep.edu.ph</a>.</p>
                          </div>
                      </div>
                  </div>

                  <div class="premium-modal-footer">
                      <div class="premium-modal-actions">
                          <button type="button" class="premium-btn secondary" onclick="closeModal('guidelinesModal')">
                              Close
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
                                <?php 
                               
                                $programCodeMap = [
                                    'SITS' => 'SITS',
                                    'AECES' => 'AECES', 
                                    'AFSET' => 'AFSET',
                                    'FTVETS' => 'FTVETS',
                                    'OFEE' => 'OFEE',
                                    'OFSET' => 'OFSET',
                                    'SABES' => 'SABES'
                                ];
                                
                                $programName = $program['name'] ?? '';
                                $programCode = $programCodeMap[$programName] ?? $programName;
                                ?>
                                <div class="logo-card" data-program-code="<?php echo htmlspecialchars($programCode); ?>">
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
            <img class="logo" src="../../../resources/images/CTET_LOGO.png" alt="Logo" />
            <img class="logo" src="../../../resources/images/ThesisCompLogo.png" alt="Logo" />
            
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
              <li><a href="search.php">Browse</a></li>
              <li><a href="#" onclick="openModal('guidelinesModal')">Guidelines</a></li>
              <li><a href="../../../app/Views/User/indexLogin.php">Login</a></li>
          </ul>
      </div>

      <div class="footer-section">
          <h4>Resources</h4>
          <ul>
              <li><a href="#" onclick="openModal('researchGuidelinesModal')">Research Guidelines</a></li>
              <li><a href="#" onclick="openModal('formattingTemplatesModal')">Formatting Templates</a></li>
              <li><a href="#" onclick="openModal('citationHelpModal')">Citation Help</a></li>
              <li><a href="#" onclick="openModal('faqModal')">FAQ</a></li>
              <li><a href="#" onclick="openModal('supportModal')">Support Center</a></li>
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
            <a href="#" onclick="openModal('privacyModal')">Privacy Policy</a>
            <a href="#" onclick="openModal('termsModal')">Terms of Service</a>
            <a href="#" onclick="openModal('accessibilityModal')">Accessibility</a>
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