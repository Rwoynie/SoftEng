<?php
// test_profile_display.php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set a test user ID (change this to match your database)
$testUserId = $_SESSION['4'] ?? 4; // Use session user ID or fallback to 1

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Image Display Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .test-section {
            margin: 30px 0;
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid #667eea;
            background: #f8f9fa;
        }
        
        .test-section h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .test-section h3 i {
            color: #667eea;
        }
        
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .info { border-left-color: #17a2b8; background: #d1ecf1; }
        
        .profile-display {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .profile-icon {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3em;
            transition: all 0.3s ease;
            border: 5px solid white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .profile-image:hover, .profile-icon:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        
        .user-info {
            margin-top: 20px;
        }
        
        .user-info h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .user-info p {
            color: #666;
            margin: 5px 0;
        }
        
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .status.online {
            background: #d4edda;
            color: #155724;
        }
        
        .status.offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .debug-info {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .image-comparison {
            display: flex;
            gap: 30px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .image-box {
            text-align: center;
            flex: 1;
            min-width: 200px;
        }
        
        .image-box h4 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧑‍💼 Profile Image Display Test</h1>
        
        <?php
        try {
            // Include required files
            require_once 'Database/config.php';
            require_once 'app/Models/Profile.php';
            
            // Test 1: Database Connection
            echo "<div class='test-section info'>
                    <h3><i class='fas fa-database'></i> Database Connection</h3>";
            
            $db = new Database();
            if ($db->isConnected()) {
                echo "<p class='success'>✓ Database connected successfully</p>";
            } else {
                throw new Exception("Database connection failed");
            }
            echo "</div>";
            
            // Test 2: Get User Profile
            echo "<div class='test-section info'>
                    <h3><i class='fas fa-user'></i> User Profile Data</h3>
                    <p><strong>Testing User ID:</strong> $testUserId</p>";
            
            $profileModel = new Profile($db);
            $userData = $profileModel->getUserProfile($testUserId);
            
            if ($userData) {
                echo "<p class='success'>✓ User profile found</p>";
                $userName = $userData['Full_Name'] ?? 'Unknown User';
                $userEmail = $userData['Email'] ?? 'No email';
                $userRole = $userData['role_display'] ?? 'Unknown role';
                $userStatus = $userData['Acc_Status'] ?? 'unknown';
            } else {
                echo "<p class='error'>✗ User profile NOT found for ID: $testUserId</p>";
                // Fallback data
                $userName = "Test User (ID: $testUserId)";
                $userEmail = "user@example.com";
                $userRole = "Test Role";
                $userStatus = "active";
            }
            echo "</div>";
            
            // Test 3: Get Profile Image
            echo "<div class='test-section info'>
                    <h3><i class='fas fa-image'></i> Profile Image</h3>";
            
            $imageBlob = $profileModel->getProfileImageBlob($testUserId);
            $hasImage = !empty($imageBlob);
            
            if ($hasImage) {
                echo "<p class='success'>✓ Profile image found in database</p>";
                echo "<p><strong>Image Size:</strong> " . strlen($imageBlob) . " bytes</p>";
                
                // Detect MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_buffer($finfo, $imageBlob);
                finfo_close($finfo);
                echo "<p><strong>MIME Type:</strong> $mimeType</p>";
                
                // Create base64 image data
                $base64Image = base64_encode($imageBlob);
                $imageSrc = "data:$mimeType;base64,$base64Image";
            } else {
                echo "<p class='error'>✗ No profile image found in database</p>";
                $imageSrc = null;
            }
            echo "</div>";
            
            // Display Profile Section
            echo "<div class='profile-display'>
                    <h3>Profile Display</h3>
                    <div class='image-comparison'>";
            
            // Default Profile Icon
            echo "<div class='image-box'>
                    <h4>Default Icon</h4>
                    <div class='profile-icon'>
                        <i class='fas fa-user'></i>
                    </div>
                    <p>Default profile icon</p>
                  </div>";
            
            // Retrieved Profile Image (or fallback)
            echo "<div class='image-box'>
                    <h4>" . ($hasImage ? "Retrieved Image" : "No Image Found") . "</h4>";
            
            if ($hasImage) {
                echo "<img src='$imageSrc' alt='Profile Image' class='profile-image' 
                         onload=\"console.log('Image loaded successfully')\"
                         onerror=\"console.error('Image failed to load'); this.style.display='none'; document.getElementById('fallback-icon').style.display='flex';\">";
                echo "<div id='fallback-icon' class='profile-icon' style='display:none;'>
                        <i class='fas fa-user'></i>
                      </div>";
                echo "<p>Image retrieved from database</p>";
            } else {
                echo "<div class='profile-icon'>
                        <i class='fas fa-user-slash'></i>
                      </div>";
                echo "<p>Using fallback icon (no image in database)</p>";
            }
            
            echo "</div></div>"; // Close image-comparison and profile-display
            
            // User Information
            echo "<div class='user-info'>
                    <h2>$userName</h2>
                    <p><strong>Email:</strong> $userEmail</p>
                    <p><strong>Role:</strong> $userRole</p>
                    <p><strong>User ID:</strong> $testUserId</p>
                    <span class='status " . ($userStatus === 'approved' ? 'online' : 'offline') . "'>
                        " . ucfirst($userStatus) . "
                    </span>
                  </div>";
            
            // Controls
            echo "<div class='controls'>
                    <button class='btn btn-primary' onclick='refreshPage()'>
                        <i class='fas fa-sync-alt'></i> Refresh Test
                    </button>
                    <a href='test_profile_image.php' class='btn btn-secondary'>
                        <i class='fas fa-vial'></i> Detailed Test
                    </a>
                    <a href='userViewPage.php' class='btn btn-success'>
                        <i class='fas fa-arrow-left'></i> Back to App
                    </a>
                  </div>";
            
            // Debug Information
            echo "<div class='test-section'>
                    <h3><i class='fas fa-bug'></i> Debug Information</h3>
                    <div class='debug-info'>
                        <p><strong>Session User ID:</strong> " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>
                        <p><strong>Test User ID:</strong> $testUserId</p>
                        <p><strong>Image Found:</strong> " . ($hasImage ? 'YES' : 'NO') . "</p>
                        <p><strong>Image Source:</strong> " . ($hasImage ? 'Base64 Data URL' : 'None') . "</p>
                        <p><strong>User Data:</strong> " . ($userData ? 'Available' : 'Not Available') . "</p>
                    </div>
                  </div>";
            
        } catch (Exception $e) {
            echo "<div class='test-section error'>
                    <h3><i class='fas fa-exclamation-triangle'></i> Error</h3>
                    <p><strong>Message:</strong> " . $e->getMessage() . "</p>
                    <p><strong>File:</strong> " . $e->getFile() . "</p>
                    <p><strong>Line:</strong> " . $e->getLine() . "</p>
                    <pre>" . $e->getTraceAsString() . "</pre>
                  </div>";
        }
        ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        function refreshPage() {
            document.body.style.opacity = '0.7';
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
        
        // Log image loading events
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    console.log('✅ Image loaded successfully:', this.src.substring(0, 50) + '...');
                });
                img.addEventListener('error', function() {
                    console.log('❌ Image failed to load:', this.src.substring(0, 50) + '...');
                });
            });
            
            console.log('Profile Display Test Loaded');
            console.log('User ID:', '<?php echo $testUserId; ?>');
            console.log('Image Found:', '<?php echo $hasImage ? "YES" : "NO"; ?>');
        });
    </script>
</body>
</html>