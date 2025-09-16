<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/gradcap.png" type="image/x-icon">
    <title>User | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../../resources/css/User/indexLogin.css">
    <script type="text/javascript" src="../../../resources/js/User/indexLogin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://apis.google.com/js/platform.js?onload=onGoogleLoad" async defer></script>
    
</head> 
<body>
    
<div class="container" onclick="onclick">
  <div class="top"></div>
  <div class="bottom"></div>
  <div class="center">
  <div class="container1">
        <div class="container2">
            <img class="sysLogo" src="../../../resources/images/gradcap.png" alt="Thesis Repository Logo - Graduation Cap">
            <h1>Thesis Repository</h1>
            <p class="tagline">A digital library for USeP student research.</p>
            <div class="d-flex justify-content-center gap-2" style="margin-top: 30px;">
                <button id="researcherBtn" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-graduate me-2"></i>
                    Researcher
                </button>   
                <span class="align-self-center text-muted">|</span>
                <button id="facultyBtn" class="btn btn-outline-danger btn-lg">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Faculty
                </button>
            </div>
            <div class="text-center mt-3">
                <a href="../../../app/Views/User/publicView.php" class="text-decoration-none link-secondary" >View as guest</a>
            </div>
        </div>  
    </div>
  </div>
</div>

<!-- LOGIN MODAL (ADDED) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header border-0 text-center w-100 d-block position-relative">
        <img src="../../../resources/images/gradcap.png" class="sysLogo mb-2" alt="Logo" style="width:80px;">
        <h5 class="modal-title" id="modalTitle">Login</h5>
        <button type="button" class="btn btn-link text-muted position-absolute" style="top:8px; right:10px; font-size:24px; text-decoration:none;" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <form>
          <input type="hidden" id="roleField" name="role">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" class="form-control" placeholder="Enter username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" id="password" class="form-control" placeholder="Enter password" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" aria-label="Show password">
                <i class="far fa-eye"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-success w-100 mb-2">Login</button>

          <div class="d-flex justify-content-center mb-2">
            <div id="googleButton"></div>
          </div>
          <button type="button" id="googleModalBtn" class="btn w-100 mb-3" style="background:#db4437; color:white;">
            <i class="fab fa-google me-2"></i> Sign in with USeP Email
          </button>
          <small class="text-muted d-block text-center">Use your USeP (@usep.edu.ph) email only</small>

          <div class="text-center">
            <a href="#" class="btn btn-link">Not yet registered?</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

    <footer class="login-footer">
        <p>&copy; 2025 University of Southeastern Philippines | Thesis Repository</p>
    </footer>

    
</body>
</html>
