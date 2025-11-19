<?php
session_start();

if (!isset($_SESSION['system_locked']) || $_SESSION['system_locked'] !== true) {
    header('Location: AdminDashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Locked - Admin Dashboard</title>
    <link rel="icon" href="/CapstoneTracker/resources/Images/ThesisCompLogo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, rgb(186, 30, 31) 0%, rgb(90, 4, 5) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lock-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        
        .lock-icon {
            font-size: 4rem;
            color: #e22929ff;
            margin-bottom: 1.5rem;
        }
        
        h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .user-role {
            color: #666;
            font-size: 0.9rem;
        }
        
        .password-form {
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .password-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .password-input:focus {
            outline: none;
            border-color: #08894dff;
        }
        
        .unlock-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #039538ff 0%, #30a648ff 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .unlock-btn:hover {
            transform: translateY(-2px);
        }
        
        .unlock-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            text-align: center;
            display: none;
        }
        
        .footer-text {
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.8rem;
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .unlock-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <div id="particles-js" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>
    <div class="gradient-overlay">
    </div>

    <div class="lock-container" style="z-index: 0;">
        <div class="lock-icon">
            <i class="fas fa-lock"></i>
        </div>
        
        <h1>System Locked</h1>
        <p class="subtitle">Enter your password to unlock the system</p>
        
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Admin'); ?></div>
        </div>
        
        <form id="unlockForm" class="password-form">
            <div class="form-group">
                <label for="password">Admin Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="password-input" 
                       placeholder="Enter your password" 
                       required
                       autofocus>
            </div>
            
            <div id="errorMessage" class="error-message">
                <i class="fas fa-exclamation-circle"></i> <span id="errorText"></span>
            </div>
            
            <button type="submit" class="unlock-btn" id="unlockBtn">
                <i class="fas fa-unlock"></i> Unlock System
            </button>
        </form>
        
        <p class="footer-text">
            <i class="fas fa-shield-alt"></i> System Secured
        </p>
    </div>

    <script>
        document.getElementById('unlockForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const password = document.getElementById('password').value;
        const unlockBtn = document.getElementById('unlockBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        
        console.log('Submit triggered, password length:', password.length);
        
        if (!password) {
            showError('Please enter your password');
            return;
        }
        
        unlockBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        unlockBtn.disabled = true;
        document.body.classList.add('loading');
        
        try {
            const formData = new FormData();
            formData.append('password', password);
            
            console.log('Sending request to verify password...');
            
            const response = await fetch('../../../app/Controllers/AdminController.php?action=verifyAdminPassword', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status, response.statusText);
            
            const responseText = await response.text();
            console.log('Raw response text:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
                console.log('Parsed JSON result:', result);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                if (responseText.includes('error') || responseText.includes('Error')) {
                    throw new Error('Server error: ' + responseText.substring(0, 100));
                } else {
                    throw new Error('Invalid server response format');
                }
            }
            
            if (result.success) {
                console.log('Password verified successfully, redirecting in 1 second...');
                setTimeout(() => {
                    window.location.href = 'AdminDashboard.php';
                }, 100);
            } else {
                console.log('Password verification failed:', result.error);
                showError(result.error || 'Invalid password');
                document.getElementById('password').value = '';
                document.getElementById('password').focus();
            }
            
        } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'Network error. Please try again.');
        } finally {
            unlockBtn.innerHTML = '<i class="fas fa-unlock"></i> Unlock System';
            unlockBtn.disabled = false;
            document.body.classList.remove('loading');
        }
    });
        
        function showError(message) {
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            errorText.textContent = message;
            errorMessage.style.display = 'block';
            
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }
        
        document.getElementById('password').focus();
        
        const passwordInput = document.getElementById('password');
        const unlockBtn = document.getElementById('unlockBtn');
        
        passwordInput.addEventListener('input', function() {
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.style.display = 'none';
            
            if (this.value.length > 0) {
                unlockBtn.style.opacity = '1';
            } else {
                unlockBtn.style.opacity = '0.8';
            }
        });

        passwordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('unlockForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

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
      distance: 150,
      color: "#ffffff",
      opacity: 0.2,
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