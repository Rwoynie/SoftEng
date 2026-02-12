<?php
/**
 * Debug Test File for User Registration Issues
 * Place this in your app/Controllers/ or app/ directory
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Include required files
require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/AuthController.php';

class DebugTest {
    private $userModel;
    private $authController;

    public function __construct() {
        $this->userModel = new User();
        $this->authController = new AuthController();
    }

    /**
     * Test Specific User ID Lookup
     */
    public function testSpecificUser($userId) {
        echo "<h2>🔍 Testing Specific User ID: {$userId}</h2>";
        
        try {
            // Method 1: Using getUserById
            echo "<h3>Method 1: getUserById()</h3>";
            $user = $this->userModel->getUserById($userId);
            
            if ($user) {
                echo "✅ User FOUND<br>";
                $this->displayUserDetails($user);
            } else {
                echo "❌ User NOT FOUND with getUserById()<br>";
            }

            // Method 2: Direct database query
            echo "<h3>Method 2: Direct Database Query</h3>";
            $db = $this->userModel->getDb();
            $db->query("SELECT * FROM USER_INFORMATION WHERE ID = :id");
            $db->bind(':id', $userId);
            $userDirect = $db->single();
            
            if ($userDirect) {
                echo "✅ User FOUND via direct query<br>";
                $this->displayUserDetails($userDirect);
            } else {
                echo "❌ User NOT FOUND via direct query<br>";
            }

        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Test Specific Email Lookup
     */
    public function testSpecificEmail($email) {
        echo "<h2>📧 Testing Specific Email: {$email}</h2>";
        
        try {
            $user = $this->authController->findByEmail($email);
            
            if ($user) {
                echo "✅ User FOUND<br>";
                $this->displayUserDetails($user);
            } else {
                echo "❌ User NOT FOUND<br>";
                
                // Check what emails exist in database
                echo "<h4>Similar emails in database:</h4>";
                $db = $this->userModel->getDb();
                $db->query("SELECT ID, Email FROM USER_INFORMATION WHERE Email LIKE :pattern LIMIT 10");
                $db->bind(':pattern', '%' . $email . '%');
                $similarUsers = $db->resultSet();
                
                if ($similarUsers) {
                    foreach ($similarUsers as $similar) {
                        $similar = (array)$similar;
                        echo "ID: {$similar['ID']} - Email: {$similar['Email']}<br>";
                    }
                } else {
                    echo "No similar emails found<br>";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Display User Details
     */
    private function displayUserDetails($user) {
        $user = (array)$user; // Convert to array if object
        
        echo "<table border='1' cellpadding='5'>";
        foreach ($user as $key => $value) {
            echo "<tr>";
            echo "<td><strong>{$key}</strong></td>";
            echo "<td>";
            
            if ($key === 'Profile_Pic' && !empty($value)) {
                echo "BINARY DATA (" . strlen($value) . " bytes)";
            } elseif ($key === 'pswrd' || $key === 'Salt') {
                echo "HIDDEN (" . strlen($value) . " chars)";
            } elseif (is_array($value)) {
                echo "ARRAY: " . print_r($value, true);
            } elseif (is_object($value)) {
                echo "OBJECT: " . get_class($value);
            } else {
                echo htmlspecialchars($value ?? 'NULL');
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    /**
     * Test User_ID Generation with Specific Data
     */
    public function testUserIdGenerationWithData($testData) {
        echo "<h2>🧪 Testing User_ID Generation with Specific Data</h2>";
        
        foreach ($testData as $test) {
            echo "<h3>Testing:</h3>";
            echo "Role: {$test['role']}<br>";
            echo "Email: {$test['email']}<br>";
            echo "Student ID: {$test['student_id']}<br>";
            echo "Employee ID: {$test['employee_id']}<br>";
            
            try {
                $userId = $this->userModel->generateUserId($test['role'], $test);
                echo "✅ Generated User_ID: <strong>{$userId}</strong><br>";
                echo "Length: " . strlen($userId) . " characters<br>";
                
                // Test hashing
                $hashed = $this->userModel->hashData($userId);
                echo "Hashed: " . substr($hashed, 0, 20) . "...<br>";
                echo "Hashed length: " . strlen($hashed) . " characters<br>";
                
                // Check if exists in database
                $exists = $this->userModel->valueExists('User_ID', $hashed);
                echo "Exists in DB: " . ($exists ? '❌ YES' : '✅ NO') . "<br>";
                
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
            echo "<hr>";
        }
    }

    /**
     * Test Database Registration with Specific Data
     */
    public function testRegistrationWithData($userData) {
        echo "<h2>🚀 Testing Registration with Specific Data</h2>";
        
        echo "<h3>Registration Data:</h3>";
        echo "<pre>" . print_r($userData, true) . "</pre>";
        
        try {
            $result = $this->userModel->registerGoogleUser($userData);

            echo "<h3>Registration Result:</h3>";
            
            if (is_array($result)) {
                echo "✅ Registration returned ARRAY<br>";
                echo "<pre>" . print_r($result, true) . "</pre>";
            } elseif ($result) {
                echo "✅ Registration SUCCESS<br>";
                echo "Returned User ID: " . $result . "<br>";
                echo "Type: " . gettype($result) . "<br>";
            } else {
                echo "❌ Registration FAILED<br>";
                $error = $this->userModel->getError();
                echo "Error: " . ($error ?: 'Unknown error') . "<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Registration Error: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Run Specific Tests Based on Parameters
     */
    public function runSpecificTests() {
        echo "<html><head><title>Debug Tests</title></head><body>";
        echo "<h1>🔧 Specific Debug Tests</h1>";

        // Get parameters from URL
        $action = $_GET['action'] ?? 'help';
        $userId = $_GET['user_id'] ?? '';
        $email = $_GET['email'] ?? '';

        switch ($action) {
            case 'user':
                if ($userId) {
                    $this->testSpecificUser($userId);
                } else {
                    echo "❌ Please provide user_id parameter<br>";
                }
                break;

            case 'email':
                if ($email) {
                    $this->testSpecificEmail($email);
                } else {
                    echo "❌ Please provide email parameter<br>";
                }
                break;

            case 'generate':
                // Test with specific data
                $testData = [[
                    'role' => $_GET['role'] ?? 'student',
                    'email' => $_GET['email'] ?? 'test@usep.edu.ph',
                    'student_id' => $_GET['student_id'] ?? '',
                    'employee_id' => $_GET['employee_id'] ?? ''
                ]];
                $this->testUserIdGenerationWithData($testData);
                break;

            case 'register':
                // Test registration with specific data
                $userData = [
                    'password' => $_GET['password'] ?? 'testpassword123',
                    'first_name' => $_GET['first_name'] ?? 'Test',
                    'last_name' => $_GET['last_name'] ?? 'User',
                    'email' => $_GET['email'] ?? 'test_' . time() . '@usep.edu.ph',
                    'user_role' => $_GET['role'] ?? 'student',
                    'acc_status' => 'approved',
                    'student_id' => $_GET['student_id'] ?? '',
                    'employee_id' => $_GET['employee_id'] ?? ''
                ];
                $this->testRegistrationWithData($userData);
                break;

            default:
                echo "<h2>📖 Usage Examples:</h2>";
                echo "1. Test specific user: <code>?action=user&user_id=1</code><br>";
                echo "2. Test specific email: <code>?action=email&email=test@usep.edu.ph</code><br>";
                echo "3. Test User_ID generation: <code>?action=generate&role=student&email=test@usep.edu.ph</code><br>";
                echo "4. Test registration: <code>?action=register&role=student&email=test@usep.edu.ph&first_name=John&last_name=Doe</code><br>";
                break;
        }

        echo "</body></html>";
    }
}

// Run specific tests based on URL parameters
$debugTest = new DebugTest();
$debugTest->runSpecificTests();

?>