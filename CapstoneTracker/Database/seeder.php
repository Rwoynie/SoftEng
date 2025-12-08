<?php
// seeder.php
require_once 'Tables.php';

class DatabaseSeeder {
    private $db;
    private $error = null;
    
    public function __construct($database = null) {
        if ($database) {
            require_once __DIR__ . '/../app/Models/Database.php';
            $this->db = $database;
        }
    }
    
    /**
     * Get student data
     */
    public static function getStudentData() {
        $password = 'A12345678';
        $salt = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);
        
        return [
            // Group 1: AHDAP
            [
                'first_name' => 'Shyrell Mae',
                'middle_name' => 'M',
                'last_name' => 'Begonia',
                'email' => 'smbegonia@usep.edu.ph',
                'user_id' => '2021-001',
                'student_id' => '2021-001',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Barky Anne',
                'middle_name' => 'L',
                'last_name' => 'Colas',
                'email' => 'blcolas@usep.edu.ph',
                'user_id' => '2021-002',
                'student_id' => '2021-002',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Regine Joy Dorothy',
                'middle_name' => 'L',
                'last_name' => 'Saliot',
                'email' => 'rlsaliot@usep.edu.ph',
                'user_id' => '2021-003',
                'student_id' => '2021-003',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 2: BANAVISTA
            [
                'first_name' => 'Lee Martin',
                'middle_name' => 'P',
                'last_name' => 'Boja',
                'email' => 'lpboja@usep.edu.ph',
                'user_id' => '2021-004',
                'student_id' => '2021-004',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Michael Lorenz',
                'middle_name' => 'P',
                'last_name' => 'Nesperos',
                'email' => 'mpnesperos@usep.edu.ph',
                'user_id' => '2021-005',
                'student_id' => '2021-005',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Lecil',
                'middle_name' => 'G',
                'last_name' => 'Quibol',
                'email' => 'lgquibol@usep.edu.ph',
                'user_id' => '2021-006',
                'student_id' => '2021-006',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 3: Bubble Scanner
            [
                'first_name' => 'Alumar',
                'middle_name' => 'P',
                'last_name' => 'Bangahon',
                'email' => 'apbangahon@usep.edu.ph',
                'user_id' => '2021-007',
                'student_id' => '2021-007',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'John Joseph',
                'middle_name' => 'N',
                'last_name' => 'Dagondon',
                'email' => 'jndagondon@usep.edu.ph',
                'user_id' => '2021-008',
                'student_id' => '2021-008',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Jaspher Jan',
                'middle_name' => 'M',
                'last_name' => 'Pagas',
                'email' => 'jmpagas@usep.edu.ph',
                'user_id' => '2021-009',
                'student_id' => '2021-009',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 4: GLOMAPP
            [
                'first_name' => 'Jericho',
                'middle_name' => 'E',
                'last_name' => 'Saramosing',
                'email' => 'jesaramosing@usep.edu.ph',
                'user_id' => '2021-010',
                'student_id' => '2021-010',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'James Irnol',
                'middle_name' => 'E',
                'last_name' => 'Sasing',
                'email' => 'jesasing@usep.edu.ph',
                'user_id' => '2021-011',
                'student_id' => '2021-011',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Nal Semper',
                'middle_name' => 'D',
                'last_name' => 'Tiempo',
                'email' => 'ndtiempo@usep.edu.ph',
                'user_id' => '2021-012',
                'student_id' => '2021-012',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 5: M-ACCESS
            [
                'first_name' => 'Eric John',
                'middle_name' => 'A',
                'last_name' => 'Batino',
                'email' => 'eabatino@usep.edu.ph',
                'user_id' => '2021-013',
                'student_id' => '2021-013',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Jill Viemark',
                'middle_name' => '',
                'last_name' => 'Lauronilla',
                'email' => 'jlauronilla@usep.edu.ph',
                'user_id' => '2021-014',
                'student_id' => '2021-014',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Yla Jolina',
                'middle_name' => 'F',
                'last_name' => 'Ypon',
                'email' => 'yfypon@usep.edu.ph',
                'user_id' => '2021-015',
                'student_id' => '2021-015',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 6: SPARKS
            [
                'first_name' => 'Harold Carl',
                'middle_name' => 'Y',
                'last_name' => 'Carcallas',
                'email' => 'hycarcallas@usep.edu.ph',
                'user_id' => '2021-016',
                'student_id' => '2021-016',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Hazel Ross',
                'middle_name' => 'S',
                'last_name' => 'Tomol',
                'email' => 'hstomol@usep.edu.ph',
                'user_id' => '2021-017',
                'student_id' => '2021-017',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Michaella Myr',
                'middle_name' => 'P',
                'last_name' => 'Matratar',
                'email' => 'mpmatratar@usep.edu.ph',
                'user_id' => '2021-018',
                'student_id' => '2021-018',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 7: Telemed
            [
                'first_name' => 'Neil',
                'middle_name' => 'S',
                'last_name' => 'Budanio',
                'email' => 'nsbudanio@usep.edu.ph',
                'user_id' => '2021-019',
                'student_id' => '2021-019',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Vinch Kyle',
                'middle_name' => 'V',
                'last_name' => 'Delgado',
                'email' => 'vvdelgado@usep.edu.ph',
                'user_id' => '2021-020',
                'student_id' => '2021-020',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Arnold Jr.',
                'middle_name' => 'E',
                'last_name' => 'Razonable',
                'email' => 'aerazonable@usep.edu.ph',
                'user_id' => '2021-021',
                'student_id' => '2021-021',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            
            // Group 8: TriGo
            [
                'first_name' => 'Kem',
                'middle_name' => 'B',
                'last_name' => 'Albert',
                'email' => 'kbalbert@usep.edu.ph',
                'user_id' => '2021-022',
                'student_id' => '2021-022',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Earvin Chance',
                'middle_name' => 'A',
                'last_name' => 'Bioco',
                'email' => 'eabioco@usep.edu.ph',
                'user_id' => '2021-023',
                'student_id' => '2021-023',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Roque Jr.',
                'middle_name' => 'A',
                'last_name' => 'Longgakit',
                'email' => 'ralonggakit@usep.edu.ph',
                'user_id' => '2021-024',
                'student_id' => '2021-024',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ]
        ];
    }
    
    /**
     * Get faculty data
     */
    public static function getFacultyData() {
        $password = 'A12345678';
        $salt = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);
        
        return [
            [
                'first_name' => 'Editha',
                'middle_name' => 'L',
                'last_name' => 'Hebron',
                'email' => 'elhebron@usep.edu.ph',
                'user_id' => 'FAC-001',
                'employee_id' => 'FAC-001',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Mishill',
                'middle_name' => 'D',
                'last_name' => 'Cempron',
                'email' => 'mdcempron@usep.edu.ph',
                'user_id' => 'FAC-002',
                'employee_id' => 'FAC-002',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Archie',
                'middle_name' => 'A',
                'last_name' => 'Cenas',
                'email' => 'aacenas@usep.edu.ph',
                'user_id' => 'FAC-003',
                'employee_id' => 'FAC-003',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Luchi',
                'middle_name' => 'A',
                'last_name' => 'Dela Cruz',
                'email' => 'ladelacruz@usep.edu.ph',
                'user_id' => 'FAC-004',
                'employee_id' => 'FAC-004',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ],
            [
                'first_name' => 'Rey',
                'middle_name' => 'M',
                'last_name' => 'De Leon',
                'email' => 'rmdeleon@usep.edu.ph',
                'user_id' => 'FAC-005',
                'employee_id' => 'FAC-005',
                'department' => 'College of Teacher Education and Technology',
                'course' => 'BSIT'
            ]
        ];
    }
    
    /**
     * Seed students
     */
    public function seedStudents() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $students = self::getStudentData();
        $password = 'A12345678';
        
        foreach ($students as $student) {
            try {
                // Check if student already exists
                $this->db->query("SELECT ID FROM USER_INFORMATION WHERE Email = :email");
                $this->db->bind(':email', $student['email']);
                $this->db->execute();
                
                if ($this->db->rowCount() > 0) {
                    continue; 
                }
                
                $salt = bin2hex(random_bytes(16));
                $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);
                
                // Hash identifiers
                $emailHash = hash('sha256', $student['email']);
                $userIdHash = hash('sha256', $student['user_id']);
                $studentIdHash = hash('sha256', $student['student_id']);
                
                // Insert student
                $this->db->query("INSERT INTO USER_INFORMATION 
                    (pswrd, Salt, First_Name, Middle_Name, Last_Name, Extension, Email, User_ID, Student_ID,
                     Email_Hash, User_ID_Hash, Student_ID_Hash, Employee_ID_Hash,
                     User_Role, Acc_Status, Login_Method, Department, Course, Profile_Pic) 
                    VALUES 
                    (:password, :salt, :first_name, :middle_name, :last_name, :extension, 
                     :email, :user_id, :student_id,
                     :email_hash, :user_id_hash, :student_id_hash, :employee_id_hash,
                     :user_role, :acc_status, :login_method, :department, :course, :profile_pic)");
                
                $this->db->bind(':password', $hashedPassword);
                $this->db->bind(':salt', $salt);
                $this->db->bind(':first_name', $student['first_name']);
                $this->db->bind(':middle_name', $student['middle_name']);
                $this->db->bind(':last_name', $student['last_name']);
                $this->db->bind(':extension', '');
                $this->db->bind(':email', $student['email']);
                $this->db->bind(':user_id', $student['user_id']);
                $this->db->bind(':student_id', $student['student_id']);
                $this->db->bind(':email_hash', $emailHash);
                $this->db->bind(':user_id_hash', $userIdHash);
                $this->db->bind(':student_id_hash', $studentIdHash);
                $this->db->bind(':employee_id_hash', null);
                $this->db->bind(':user_role', 'student');
                $this->db->bind(':acc_status', 'approved');
                $this->db->bind(':login_method', 'manual');
                $this->db->bind(':department', $student['department']);
                $this->db->bind(':course', $student['course']);
                $this->db->bind(':profile_pic', null);
                
                $this->db->execute();
                
            } catch (Exception $e) {
                $this->error = "Failed to seed student {$student['email']}: " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Seed faculty
     */
    public function seedFaculty() {
        if (!$this->db || !$this->db->isConnected()) {
            $this->error = "Database connection not established";
            return false;
        }
        
        $faculty = self::getFacultyData();
        $password = 'A12345678';
        
        foreach ($faculty as $facultyMember) {
            try {
                // Check if faculty already exists
                $this->db->query("SELECT ID FROM USER_INFORMATION WHERE Email = :email");
                $this->db->bind(':email', $facultyMember['email']);
                $this->db->execute();
                
                if ($this->db->rowCount() > 0) {
                    continue; 
                }
                
                $salt = bin2hex(random_bytes(16));
                $hashedPassword = password_hash($password . $salt, PASSWORD_DEFAULT);
                
                // Hash identifiers
                $emailHash = hash('sha256', $facultyMember['email']);
                $userIdHash = hash('sha256', $facultyMember['user_id']);
                $employeeIdHash = hash('sha256', $facultyMember['employee_id']);
                
                // Insert faculty
                $this->db->query("INSERT INTO USER_INFORMATION 
                    (pswrd, Salt, First_Name, Middle_Name, Last_Name, Extension, Email, User_ID, Employee_ID,
                     Email_Hash, User_ID_Hash, Student_ID_Hash, Employee_ID_Hash,
                     User_Role, Acc_Status, Login_Method, Department, Course, Profile_Pic) 
                    VALUES 
                    (:password, :salt, :first_name, :middle_name, :last_name, :extension, 
                     :email, :user_id, :employee_id,
                     :email_hash, :user_id_hash, :student_id_hash, :employee_id_hash,
                     :user_role, :acc_status, :login_method, :department, :course, :profile_pic)");
                
                $this->db->bind(':password', $hashedPassword);
                $this->db->bind(':salt', $salt);
                $this->db->bind(':first_name', $facultyMember['first_name']);
                $this->db->bind(':middle_name', $facultyMember['middle_name']);
                $this->db->bind(':last_name', $facultyMember['last_name']);
                $this->db->bind(':extension', '');
                $this->db->bind(':email', $facultyMember['email']);
                $this->db->bind(':user_id', $facultyMember['user_id']);
                $this->db->bind(':employee_id', $facultyMember['employee_id']);
                $this->db->bind(':email_hash', $emailHash);
                $this->db->bind(':user_id_hash', $userIdHash);
                $this->db->bind(':student_id_hash', null);
                $this->db->bind(':employee_id_hash', $employeeIdHash);
                $this->db->bind(':user_role', 'faculty');
                $this->db->bind(':acc_status', 'approved');
                $this->db->bind(':login_method', 'manual');
                $this->db->bind(':department', $facultyMember['department']);
                $this->db->bind(':course', $facultyMember['course']);
                $this->db->bind(':profile_pic', null);
                
                $this->db->execute();
                
            } catch (Exception $e) {
                $this->error = "Failed to seed faculty {$facultyMember['email']}: " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Run full seeding process
     */
    public function runSeeder() {
        echo "Starting database seeding...<br>";
        
        if (!$this->seedStudents()) {
            echo "Error seeding students: " . $this->getError() . "<br>";
            return false;
        }
        echo "Students seeded successfully!<br>";
        
        if (!$this->seedFaculty()) {
            echo "Error seeding faculty: " . $this->getError() . "<br>";
            return false;
        }
        echo "Faculty seeded successfully!<br>";
        
        echo "Database seeding completed successfully!<br>";
        return true;
    }
    
    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Static method to run seeder
     */
    public static function manualSeed() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        require_once __DIR__ . '/../vendor/autoload.php';
        
        $dotenvPath = dirname(__DIR__) . '/.env';
        if (!file_exists($dotenvPath)) {
            die("Error: .env file not found at: " . $dotenvPath);
        }
        
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        require_once __DIR__ . '/../app/Models/Database.php';

        require_once __DIR__ . '/config.php';
        
        try {
            $db = new Database();
            $seeder = new DatabaseSeeder($db);

            if ($seeder->runSeeder()) {
                echo "<h3>Seeder completed successfully!</h3>";
                echo "<p><strong>Login credentials for all accounts:</strong></p>";
                echo "<p>Email: [respective email]</p>";
                echo "<p>Password: A12345678</p>";
                echo "<p><strong>Note:</strong> Users should change their passwords after first login.</p>";
            } else {
                echo "<h3>Seeder completed with errors!</h3>";
            }
            
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}


DatabaseSeeder::manualSeed();
?>