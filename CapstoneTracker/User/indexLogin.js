let isAnimating = false;

function toggleForm() {
    if (isAnimating) return;
    isAnimating = true;
    
    const loginForm = document.querySelector('.loginForm');
    const signupForm = document.querySelector('.signupForm');
    
    if (loginForm.classList.contains('active')) {
        // Switching to signup
        loginForm.classList.remove('active');
        setTimeout(() => {
            loginForm.style.display = 'none';
            signupForm.style.display = 'flex';
            setTimeout(() => {
                signupForm.classList.add('active');
                isAnimating = false;
            }, 10);
        }, 400);
    } else {
        // Switching to login
        signupForm.classList.remove('active');
        setTimeout(() => {
            signupForm.style.display = 'none';
            loginForm.style.display = 'flex';
            setTimeout(() => {
                loginForm.classList.add('active');
                isAnimating = false;
            }, 10);
        }, 400);
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