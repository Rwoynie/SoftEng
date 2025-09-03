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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="indexLogin.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="Line"></div>
    <div class="yellow"></div>
    <div class="cover" id="cover"></div>

    <div class="container1">
        <div class="container2">
            <img class="sysLogo" src="../images/gradcap.png" alt="System Logo">
            <h1>USeP ThesisComp</h1>
        </div>

        <div class="form-container">
            <!-- Login Form -->
            <form class="loginForm active">
                <h2>Welcome!</h2>
                <div class="form-group">
                    <label for="userID">User ID</label>
                    <input type="text" id="userID" placeholder="Enter your User ID" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Enter your password" required>
                </div>
                <p class="Forg_Pass" onclick="forgotPassword()">Forgot password?</p>
                <button type="submit" class="btn-login">Login</button>
                <p class="toggle-form">Don't have an account? <span onclick="toggleForm()">Sign Up</span></p>
            </form>

            <!-- Signup Form -->
            <form class="signupForm">
                <h2>Create Account</h2>
                <div class="name-fields">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" placeholder="Enter first name" required>
                    </div>
                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" placeholder="Enter middle name">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" placeholder="Enter last name" required>
                    </div>
                    <div class="form-group">
                        <label for="extension">Extension</label>
                        <input type="text" id="extension" placeholder="Jr, Sr, III, etc.">
                    </div>
                </div>
                <div class="account-fields">
                    <div class="form-group">
                        <label for="email">USeP Email</label>
                        <input type="email" id="email" placeholder="Enter your USeP email" required>
                    </div>
                    <div class="form-group">
                        <label for="newUserID">Student ID</label>
                        <input type="text" id="newUserID" placeholder="Enter your Student ID" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Password</label>
                        <input type="password" id="newPassword" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" placeholder="Confirm your password" required>
                    </div>
                </div>
                <button type="submit" class="btn-signup">Sign Up</button>
                <p class="toggle-form">Already have an account? <span onclick="toggleForm()">Login</span></p>
            </form>
        </div>
    </div>

    <script type="text/javascript" src="indexLogin.js">

    </script>
</body>
</html>
