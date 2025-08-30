<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capstone Filter | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../css/Admin/index.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="container" id="container">
        <!-- Login Form -->
        <div class="form-container sign-in-container">

        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <form id="teacherLoginForm">
                <p class="Header">Welcome User!</p>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                    
                </div>
                    <p class="Forg_Pass" onclick="forgotPassword()">Forgot password?</p>
                <button type="submit" class="btn" id="login">Login</button>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="form-container sign-up-container">
    <form id="teacherRegisterForm">
        <p class="Header">User Registration</p>
            <div class="form-group-container">
                <div class="form-group">
                    <input type="text" name="firstName" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="middleName" placeholder="Middle Name (Optional)">
                </div>
                <div class="form-group">
                    <input type="text" name="lastName" placeholder="Last Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
                </div>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To access your account, please login<br> with your credentials</p>
                    <button class="ghost" id="signIn">Login</button>
                </div>
                <div class="overlay-panel overlay-right">
                    
                    <h1>No Account?</h1>
                    <p>Register your account to access the portal</p>
                    <button class="ghost" id="signUp">Register</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle between forms
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>
</body>
</html>

