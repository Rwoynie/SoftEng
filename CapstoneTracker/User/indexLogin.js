
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
    console.log('ID: ' + profile.getId());
    console.log('Name: ' + profile.getName());
    console.log('Image URL: ' + profile.getImageUrl());
    console.log('Email: ' + profile.getEmail());
    
    // You can send the ID token to your server for verification
    var id_token = googleUser.getAuthResponse().id_token;
    console.log('ID Token: ' + id_token);
    
    // Hide loading after successful sign-in
    hideLoading();
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
});

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