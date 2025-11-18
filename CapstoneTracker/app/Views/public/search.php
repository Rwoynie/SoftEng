<?php
require_once '../../../Database/config.php';
require_once '../../Controllers/Controller.php';  
require_once '../../Controllers/PublicHomeController.php';  
require_once '../../Models/Thesis.php';  
require_once '../../Models/PublicHomeModel.php'; 

$model = new PublicHomeModel();
$query = trim($_POST['query'] ?? '');
$department = $_POST['department'] ?? 'all';
$sort = $_POST['sort'] ?? 'recent';
$page = max(1, intval($_POST['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

if (!empty($query)) {
    $results = $model->searchThesis($query, $department, $sort, $limit, $offset);
    $total = $model->getSearchCount($query, $department);
    $totalPapers = $total;
    $totalPages = ceil($total / $limit);
    $currentPage = $page;
} else {
    $results = [];
    $totalPapers = 0;
    $totalPages = 1;
    $currentPage = 1;
}

$data = [
    'query' => $query,
    'results' => $results,
    'department' => $department,
    'sort' => $sort,
    'currentPage' => $currentPage,
    'totalPages' => $totalPages,
    'totalPapers' => $totalPapers
];
extract($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results - Thesis Compendium System</title>
  <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
  <link rel="stylesheet" href="../../../resources/css/User/home.css">
  <link rel="stylesheet" href="../../../resources/css/User/search.css">
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
        <a href="../User/indexLogin.php" class="btn-login">Login</a>
      </nav>
    </header>

    <!-- ================= Search Results Page ================= -->
    <section id="results-page">
      <header class="results-header">
        <div class="search-filter">
          <div class="searchbox">
            <form id="search-form" method="POST" action="search.php">
              <div class="icon"> <i class="fa fa-search" aria-hidden="true"></i> </div>
              <input type="text" id="results-search-input" name="query" placeholder="Search thesis..." value="<?php echo htmlspecialchars($query); ?>">
              <input type="hidden" name="department" id="hidden-department" value="<?php echo htmlspecialchars($department); ?>">
              <input type="hidden" name="sort" id="hidden-sort" value="<?php echo htmlspecialchars($sort); ?>">
              <input type="hidden" name="page" value="1">
              <button type="submit" class="search-btn" id="results-search-btn"><i class="fa fa-search" aria-hidden="true"></i></button>
            </form>
          </div>
          
          <div class="filter-controls">
            <div class="select" id="filterDropdown">
              <div class="selected">
                <span><?php echo $department === 'all' ? 'All Departments' : strtoupper($department); ?></span>
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
              </div>
              <div class="options">
                <div data-value="all">All Departments</div>
                <div data-value="BSIT">BSIT | SITS</div>
                <div data-value="BSABE">BSABE | SABES</div>
                <div data-value="BECED">BECED | AECES</div>
                <div data-value="BSNED">BSNED | OFSET</div>
                <div data-value="BTVTED">BTVTED | FTVETS</div>
                <div data-value="BEED">BEED | OFEE</div>
                <div data-value="BSED">BSED | AFSET</div>
              </div>
            </div>
            
            <div class="select" id="sortDropdown">
              <div class="selected">
                <span>Sort by: <?php echo ucfirst($sort); ?></span>
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
        </div>

        <div class="results-title">
          <h2>Search Results</h2>
          <p class="results-count">Showing <span id="results-number"><?php echo $totalPapers; ?></span> results <?php echo !empty($query) ? 'for "' . htmlspecialchars($query) . '"' : ''; ?></p>
        </div>
      </header>

      <section class="results-container">
        <div class="results grid" id="thesis-results">
          <?php if (!empty($results)): ?>
            <?php foreach ($results as $paper): ?>
              <?php 
                $uploadDate = $paper['uploadDate'];
                $year = date('Y', strtotime($uploadDate));
                ?>


              <div class="thesis-card" data-thesis-id="<?php echo $paper['id']; ?>">
                <div class="card-header">
                    <div class="card-logo">
                        <img src="<?php echo htmlspecialchars($paper['logo']); ?>" alt="<?php echo htmlspecialchars($paper['department']); ?>">
                    </div>
                    <button class="citation-btn" onclick="copyCitation(event, <?php echo $paper['id']; ?>, '<?php echo addslashes($paper['title']); ?>', '<?php echo addslashes($paper['authors']); ?>', '<?php echo $year; ?>')">
                        <i class="fas fa-quote-right"></i>
                        Cite
                    </button>
                </div>

                <h3><?php echo htmlspecialchars($paper['title']); ?></h3>
                
                <div class="department-badge">
                    <?php echo htmlspecialchars($paper['department']); ?>
                </div>

                <div class="authors">
                    <i class="fas fa-users"></i>
                    <span><?php echo htmlspecialchars($paper['authors']); ?></span>
                </div>

                <div class="adviser">
                    <i class="fas fa-user-tie"></i>
                    <span><?php echo htmlspecialchars($paper['adviser'] ?? 'Adviser not specified'); ?></span>
                </div>

                <div class="abstract-preview">
                    <?php 
                    $cleanAbstract = strip_tags($paper['abstract']);
                    $cleanAbstract = preg_replace('/[^\x20-\x7E]/', '', $cleanAbstract);
                    $cleanAbstract = trim($cleanAbstract);
                    
                    if (str_starts_with($cleanAbstract, '%PDF')) {
                        $cleanAbstract = 'Abstract available in PDF format';
                    }
                    
                    if (empty($cleanAbstract)) {
                        $cleanAbstract = 'Abstract not available';
                    }
                    
                    echo htmlspecialchars(substr($cleanAbstract, 0, 150));
                    if (strlen($cleanAbstract) > 150) echo '...';
                    ?>
                </div>

                <div class="card-footer">
                    <div class="upload-date">
                        <i class="far fa-calendar-alt"></i>
                        <span><?php echo date('M j, Y', strtotime($paper['uploadDate'])); ?></span>
                    </div>
                    <div class="available">
                        <i class="fas fa-check-circle"></i>
                        <span>Available</span>
                    </div>
                </div>
            </div>



            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-results">
              <i class="fas fa-search fa-3x"></i>
              <h3>No results found</h3>
              <p>Try different keywords or browse all departments</p>
            </div>
          <?php endif; ?>
        </div>
        
        <?php if (!empty($results) && $totalPages > 1): ?>
          <div class="pagination">
              <button class="pagination-btn prev <?php if($currentPage == 1) echo 'disabled'; ?>" 
                <?php if($currentPage > 1): ?>onclick="changePage(<?php echo $currentPage - 1; ?>)"<?php endif; ?>>
                  <i class="fas fa-chevron-left"></i> Previous
              </button>
              
              <div class="page-numbers">
                  <?php 
                  $maxVisible = 5;
                  if ($totalPages <= $maxVisible) {
                      for ($i = 1; $i <= $totalPages; $i++) {
                          echo '<button class="page-btn ' . ($i == $currentPage ? 'active' : '') . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
                      }
                  } else {
                      echo '<button class="page-btn ' . (1 == $currentPage ? 'active' : '') . '" onclick="changePage(1)">1</button>';
                      if ($currentPage > 3) echo '<span class="page-dots">...</span>';
                      for ($i = max(2, $currentPage - 1); $i <= min($totalPages - 1, $currentPage + 1); $i++) {
                          echo '<button class="page-btn ' . ($i == $currentPage ? 'active' : '') . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
                      }
                      if ($currentPage < $totalPages - 2) echo '<span class="page-dots">...</span>';
                      echo '<button class="page-btn ' . ($totalPages == $currentPage ? 'active' : '') . '" onclick="changePage(' . $totalPages . ')">' . $totalPages . '</button>';
                  }
                  ?>
              </div>
              
              <button class="pagination-btn next <?php if($currentPage == $totalPages) echo 'disabled'; ?>" 
                <?php if($currentPage < $totalPages): ?>onclick="changePage(<?php echo $currentPage + 1; ?>)"<?php endif; ?>>
                  Next <i class="fas fa-chevron-right"></i>
              </button>
          </div>
        <?php endif; ?>
      </section>
    </section>

    <!-- Thesis Modal -->
    <div id="thesisModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2 id="modalThesisTitle"></h2>
          <span class="close">&times;</span>
        </div>
        <div class="modal-body">
          <div class="thesis-info">
            <div class="info-item">
              <strong>Authors:</strong>
              <span id="modalThesisAuthors"></span>
            </div>
            <div class="info-item">
              <strong>Adviser:</strong>
              <span id="modalThesisAdviser"></span>
            </div>
            <div class="info-item">
              <strong>Department:</strong>
              <span id="modalThesisDepartment"></span>
            </div>
            <div class="info-item">
              <strong>Upload Date:</strong>
              <span id="modalThesisDate"></span>
            </div>
          </div>
          <div class="abstract-section">
            <h3>Abstract</h3>
            <div id="modalThesisAbstract" class="abstract-content"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="modal-citation-btn" id="modalCitationBtn">
              <i class="fas fa-quote-right"></i>
              Copy APA Citation
          </button>
          <div class="modal-footer-actions">
              <button id="viewFullThesis" class="btn-primary">
                  <i class="fas fa-external-link-alt"></i>
                  View Full Thesis
              </button>
              <button class="btn-secondary close-modal">Close</button>
          </div>
      </div>

      </div>
    </div>

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
            <li><a href="home.php">Home</a></li>
            <li><a href="search.php">Browse</a></li>
            <li><a href="#">Guidelines</a></li>
            <li><a href="../User/indexLogin.php">Login</a></li>
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

  <script>
    function changePage(page) {
      const form = document.getElementById('search-form');
      const pageInput = document.createElement('input');
      pageInput.type = 'hidden';
      pageInput.name = 'page';
      pageInput.value = page;
      form.appendChild(pageInput);
      form.submit();
    }
  </script>
  <script src="../../../resources/js/User/search.js"></script> 
</body>
</html>