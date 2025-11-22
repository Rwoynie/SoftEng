<?php
session_start();

class LoginSystem {
    private $maxAttempts = 3;
    private $lockoutDuration = 30; // seconds
    
    public function __construct() {
        // Initialize session variables if not set
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        if (!isset($_SESSION['lockout_until'])) {
            $_SESSION['lockout_until'] = null;
        }
    }
    
    public function isLockedOut() {
        if ($_SESSION['lockout_until'] && time() < $_SESSION['lockout_until']) {
            return true;
        } elseif ($_SESSION['lockout_until'] && time() >= $_SESSION['lockout_until']) {
            // Lockout period has ended, reset attempts
            $this->resetAttempts();
            return false;
        }
        return false;
    }
    
    private function authenticate($username, $password) {
        // Replace with your actual authentication logic
        $validUsername = "admin";
        $validPassword = "password123";
        
        return $username === $validUsername && password_verify($password, password_hash($validPassword, PASSWORD_DEFAULT));
    }
    
    public function loginAttempt($username, $password) {
        // Check if account is locked out
        if ($this->isLockedOut()) {
            $remainingTime = $_SESSION['lockout_until'] - time();
            return [
                'success' => false,
                'message' => "Account locked. Try again in {$remainingTime} seconds",
                'remaining_attempts' => 0,
                'locked' => true
            ];
        }
        
        // Attempt authentication
        if ($this->authenticate($username, $password)) {
            // Successful login - reset attempts
            $this->resetAttempts();
            return [
                'success' => true,
                'message' => "Login successful!",
                'remaining_attempts' => $this->maxAttempts,
                'locked' => false
            ];
        } else {
            // Failed login
            $_SESSION['login_attempts']++;
            $remainingAttempts = $this->maxAttempts - $_SESSION['login_attempts'];
            
            if ($_SESSION['login_attempts'] >= $this->maxAttempts) {
                // Lock the account
                $_SESSION['lockout_until'] = time() + $this->lockoutDuration;
                return [
                    'success' => false,
                    'message' => "Too many failed attempts. Account locked for {$this->lockoutDuration} seconds.",
                    'remaining_attempts' => 0,
                    'locked' => true
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Invalid credentials. {$remainingAttempts} attempts remaining.",
                    'remaining_attempts' => $remainingAttempts,
                    'locked' => false
                ];
            }
        }
    }
    
    public function getLoginStatus() {
        if ($this->isLockedOut()) {
            $remainingTime = $_SESSION['lockout_until'] - time();
            return "LOCKED - Try again in {$remainingTime} seconds";
        } else {
            $remainingAttempts = $this->maxAttempts - $_SESSION['login_attempts'];
            return "READY - {$remainingAttempts} attempts remaining";
        }
    }
    
    public function resetAttempts() {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_until'] = null;
    }
    
    public function getRemainingAttempts() {
        return $this->maxAttempts - $_SESSION['login_attempts'];
    }
}

// Demonstration function
function demonstrateLoginSystem() {
    $loginSystem = new LoginSystem();
    
    // Test scenarios
    $testCases = [
        ['username' => 'admin', 'password' => 'wrong1'],
        ['username' => 'admin', 'password' => 'wrong2'],
        ['username' => 'admin', 'password' => 'wrong3'], // This should trigger lockout
        ['username' => 'admin', 'password' => 'password123'], // This should fail due to lockout
    ];
    
    echo "=== Login System Demonstration ===<br><br>";
    
    foreach ($testCases as $i => $credentials) {
        $attemptNumber = $i + 1;
        echo "Attempt {$attemptNumber}:<br>";
        echo "Status: " . $loginSystem->getLoginStatus() . "<br>";
        
        $result = $loginSystem->loginAttempt($credentials['username'], $credentials['password']);
        
        echo "Result: " . $result['message'] . "<br>";
        echo "Success: " . ($result['success'] ? 'true' : 'false') . "<br>";
        echo "Remaining attempts: " . $result['remaining_attempts'] . "<br>";
        
        if ($result['success']) {
            echo "🎉 Login successful!<br>";
            break;
        } else {
            echo "❌ Login failed<br>";
        }
        
        echo str_repeat("-", 50) . "<br>";
        
        // If locked out, show wait message (in real scenario, user would wait)
        if ($result['locked']) {
            echo "<br>⏳ Account is locked. User must wait 30 seconds.<br>";
            echo "In a real application, the user would need to wait before trying again.<br>";
            echo str_repeat("-", 50) . "<br>";
            
            // For demonstration, we'll reset after showing the lockout
            // In real scenario, you wouldn't do this
            if ($i === count($testCases) - 2) { // Reset on the last iteration for demo
                echo "<br>💡 Demo note: Resetting for demonstration purposes<br>";
                $loginSystem->resetAttempts();
            }
        }
    }
}

// Simple function version (file-based for persistence)
function loginWithRetry($username, $password, $maxAttempts = 3, $lockoutTime = 30) {
    $attemptsFile = 'login_attempts.json';
    
    // Read existing attempts data
    if (file_exists($attemptsFile)) {
        $data = json_decode(file_get_contents($attemptsFile), true);
    } else {
        $data = [
            'attempts' => 0,
            'lockout_until' => null,
            'last_attempt' => null
        ];
    }
    
    $currentTime = time();
    
    // Check if locked out
    if ($data['lockout_until'] && $currentTime < $data['lockout_until']) {
        $remaining = $data['lockout_until'] - $currentTime;
        return [
            'success' => false,
            'message' => "Account locked. Try again in {$remaining} seconds",
            'locked' => true
        ];
    } elseif ($data['lockout_until'] && $currentTime >= $data['lockout_until']) {
        // Lockout period ended, reset
        $data['attempts'] = 0;
        $data['lockout_until'] = null;
    }
    
    // Mock authentication function
    function authenticate($u, $p) {
        $validUsername = "admin";
        $validPassword = "password123";
        return $u === $validUsername && $p === $validPassword;
    }
    
    if (authenticate($username, $password)) {
        // Successful login - reset attempts
        $data['attempts'] = 0;
        $data['lockout_until'] = null;
        $data['last_attempt'] = $currentTime;
        file_put_contents($attemptsFile, json_encode($data));
        
        return [
            'success' => true,
            'message' => "Login successful!",
            'locked' => false
        ];
    } else {
        // Failed login
        $data['attempts']++;
        $data['last_attempt'] = $currentTime;
        
        $remainingAttempts = $maxAttempts - $data['attempts'];
        
        if ($data['attempts'] >= $maxAttempts) {
            $data['lockout_until'] = $currentTime + $lockoutTime;
            file_put_contents($attemptsFile, json_encode($data));
            
            return [
                'success' => false,
                'message' => "Too many attempts. Account locked for {$lockoutTime} seconds.",
                'locked' => true
            ];
        } else {
            file_put_contents($attemptsFile, json_encode($data));
            return [
                'success' => false,
                'message' => "Invalid credentials. {$remainingAttempts} attempts remaining.",
                'locked' => false
            ];
        }
    }
}

// Usage example in a web form context
function handleLoginForm() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        $loginSystem = new LoginSystem();
        $result = $loginSystem->loginAttempt($_POST['username'], $_POST['password']);
        
        if ($result['success']) {
            // Login successful - redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            // Show error message
            $errorMessage = $result['message'];
            $remainingAttempts = $result['remaining_attempts'];
            $isLocked = $result['locked'];
            
            // You can use these variables in your HTML template
            return [
                'error' => $errorMessage,
                'remaining_attempts' => $remainingAttempts,
                'locked' => $isLocked
            ];
        }
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System Demo</title>
    <style>
        .container { max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { 
            width: 100%; padding: 8px; border: 1px solid #ddd; 
        }
        button { 
            background: #007bff; color: white; padding: 10px 20px; 
            border: none; cursor: pointer; 
        }
        button:disabled { 
            background: #6c757d; cursor: not-allowed; 
        }
        .error { color: #dc3545; margin-top: 10px; }
        .success { color: #28a745; margin-top: 10px; }
        .info { color: #17a2b8; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Form with Attempt Limiting</h2>
        
        <?php
        // Handle form submission
        $result = handleLoginForm();
        $loginSystem = new LoginSystem();
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" 
                <?php echo $loginSystem->isLockedOut() ? 'disabled' : ''; ?>>
                Login
            </button>
        </form>
        
        <?php if ($result): ?>
            <div class="<?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $result['error']; ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Status:</strong> <?php echo $loginSystem->getLoginStatus(); ?>
        </div>
        
        <div class="info">
            <strong>Remaining attempts:</strong> <?php echo $loginSystem->getRemainingAttempts(); ?>
        </div>
        
        <hr>
        <h3>Demo Credentials:</h3>
        <p>Username: <code>admin</code></p>
        <p>Password: <code>password123</code></p>
    </div>
    
    <?php
    // Run demonstration
    echo "<div style='margin: 50px; padding: 20px; background: #f8f9fa;'>";
    demonstrateLoginSystem();
    echo "</div>";
    ?>
</body>
</html>