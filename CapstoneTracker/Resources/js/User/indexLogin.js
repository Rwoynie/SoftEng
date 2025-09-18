// Show error message as SweetAlert and reopen modal if there is an error
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should show the modal
    if (typeof showModal !== 'undefined' && showModal) {
        // Get the role from the hidden field or default to Researcher
        const roleField = document.getElementById('roleField');
        const role = roleField ? roleField.value : 'Researcher';
        openLogin(role);
    }
    
    // Show error message if there is one
    if (typeof errorMessage !== 'undefined' && errorMessage && errorMessage !== '') {
        showErrorAlert(errorMessage);
    }
    
    // Initialize the page functionality
    initializePage();
});

function showErrorAlert(message) {
    Swal.fire({
        title: 'Login Failed',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    }).then((result) => {
        // After user closes the alert, focus on the username field
        if (result.isConfirmed || result.isDismissed) {
            const usernameField = document.getElementById('username');
            if (usernameField) {
                usernameField.focus();
            }
        }
    });
}

// Prevent form submission from closing modal on error
function setupFormHandlers() {
    const loginForm = document.querySelector('#loginModal form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Let the form submit normally - the server will handle validation
            console.log('Form submitted to AuthController');
        });
    }
}


function forgotPassword() {
    Swal.fire({
        title: 'Forgot Password?',
        text: 'Please contact the administrator to reset your password.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function showLoading() {
    const btn = document.getElementById('customGoogleBtn');
    if (btn) {
        btn.classList.add('loading');
    }
}

function hideLoading() {
    const btn = document.getElementById('customGoogleBtn');
    if (btn) {
        btn.classList.remove('loading');
    }
}

function onSignIn(googleUser) {
    var profile = googleUser.getBasicProfile();
    var email = (profile.getEmail() || '').toLowerCase();
    if (!email.endsWith('@usep.edu.ph')) {
        Swal.fire({
            title: 'Invalid Email',
            text: 'Please use your USeP (@usep.edu.ph) account.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        try {
            if (typeof gapi !== 'undefined' && gapi.auth2) {
                var auth2 = gapi.auth2.getAuthInstance();
                if (auth2) auth2.signOut();
            }
        } catch (e) {}
        return;
    }
    var id_token = googleUser.getAuthResponse().id_token;
    sessionStorage.setItem('userEmail', email);
    sessionStorage.setItem('googleIdToken', id_token);
}

// Wait for the Google API to load
function onGoogleLoad() {
    console.log('Google API loaded');
    renderGoogleButton();
}

// Initialize Google Sign-In button
function renderGoogleButton() {
    if (typeof gapi !== 'undefined' && gapi.signin2) {
        gapi.signin2.render('googleButton', {
            'scope': 'profile email',
            'width': 240,
            'height': 40,
            'longtitle': true,
            'theme': 'light',
            'onsuccess': onSignIn,
            'onfailure': function(error) {
                console.log('Google Sign-In failed:', error);
                hideLoading();
            }
        });
        
        // Add event listener to custom button after Google button is rendered
        setTimeout(() => {
            const customBtn = document.getElementById('customGoogleBtn');
            if (customBtn) {
                customBtn.addEventListener('click', function() {
                    showLoading();
                    const googleButton = document.querySelector('#googleButton .abcRioButton');
                    if (googleButton) {
                        googleButton.click();
                    } else {
                        console.log('Google button not found');
                        hideLoading();
                    }
                });
            }
        }, 1000);
    } else {
        console.log('Google API not available yet, retrying...');
        setTimeout(renderGoogleButton, 500);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded');
    
    // Check if Google API is already loaded
    if (typeof gapi !== 'undefined') {
        renderGoogleButton();
    }
    
    // Add a fallback in case the Google API doesn't load properly
    setTimeout(renderGoogleButton, 2000);

    // Modal open buttons
    const researcherBtn = document.getElementById("researcherBtn");
    const facultyBtn = document.getElementById("facultyBtn");
    const modalTitle = document.getElementById("modalTitle");
    const roleField = document.getElementById("roleField");
    const googleModalBtn = document.getElementById("googleModalBtn");
    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const passwordInput = document.getElementById("password");

    function openLogin(role){
      if (modalTitle) modalTitle.innerText = role + " Login";
      if (roleField) roleField.value = role;
      const modalEl = document.getElementById('loginModal');
      if (!modalEl) return;
      const loginModal = new bootstrap.Modal(modalEl);
      loginModal.show();
      // ensure Google button is clickable
      setTimeout(() => {
        if (googleModalBtn) {
          googleModalBtn.onclick = function() {
            const googleButton = document.querySelector('#googleButton .abcRioButton');
            if (googleButton) {
              googleButton.click();
            } else {
              Swal.fire('Google Sign-In not ready', 'Please try again in a moment.', 'info');
            }
          };
        }
      }, 300);
    }

    if (researcherBtn) researcherBtn.addEventListener("click", () => openLogin("Researcher"));
    if (facultyBtn) facultyBtn.addEventListener("click", () => openLogin("Faculty"));

    if (togglePasswordBtn && passwordInput) {
      togglePasswordBtn.addEventListener('click', function() {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        const icon = this.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        }
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
      });
    }
});


      const researcherBtn = document.getElementById("researcherBtn");
      const facultyBtn = document.getElementById("facultyBtn");
      const modalTitle = document.getElementById("modalTitle");
      const roleField = document.getElementById("roleField");

      function openLogin(role){
        modalTitle.innerText = role + " Login";
        roleField.value = role;
        let loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
      }

      researcherBtn.addEventListener("click", () => openLogin("Researcher"));
      facultyBtn.addEventListener("click", () => openLogin("Faculty"));


/*
let slideIndex = 0;
let slideInterval;

function startSlideshow() {
    showSlides();
    // Set up interval for automatic slides
    slideInterval = setInterval(() => {
        plusSlides(1);
    }, 3000); // Change slide every 5 seconds
}

function showSlides() {
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
        slides[i].style.display = "none";
    }
    
    // Remove active class from all dots
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    // Show current slide
    if (slides.length > 0) {
        if (slideIndex >= slides.length) slideIndex = 0;
        if (slideIndex < 0) slideIndex = slides.length - 1;
        
        slides[slideIndex].style.display = "block";
        setTimeout(() => {
            slides[slideIndex].classList.add("active");
        }, 10);
        
        if (dots.length > 0) {
            dots[slideIndex].classList.add("active");
        }
    }
}

// Next/previous controls
function plusSlides(n) {
    clearInterval(slideInterval); // Reset timer when manually changing slides
    slideIndex += n;
    showSlides();
    startSlideshow(); // Restart the timer
}

// Thumbnail image controls
function currentSlide(n) {
    clearInterval(slideInterval); // Reset timer when manually changing slides
    slideIndex = n - 1;
    showSlides();
    startSlideshow(); // Restart the timer
}

// Initialize slideshow when document is ready
document.addEventListener('DOMContentLoaded', function() {
    startSlideshow();
    
    // Pause slideshow when hovering over it
    const slideshow = document.querySelector('.slideshow-container');
    if (slideshow) {
        slideshow.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        slideshow.addEventListener('mouseleave', () => {
            startSlideshow();
        });
    }
});

*/