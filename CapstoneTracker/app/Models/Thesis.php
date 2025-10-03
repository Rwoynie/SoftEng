<?php

require_once 'Model.php';
require_once 'User.php';

class Thesis extends Model {
    protected $tableName = 'THESIS';
    private $error = null;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Upload thesis file and save to database (ADMIN VERSION)
     */
    public function uploadThesis($postData, $files, $adminUserId) {
        try {
            // Validate input
            if (empty($postData['thesistitle'])) {
                $this->error = 'Thesis title is required';
                return false;
            }
            
            if (empty($files['files'])) {
                $this->error = 'Please select at least one file';
                return false;
            }

            // Validate and process author emails
            if (empty($postData['thesisauthor'])) {
                $this->error = 'Author emails are required';
                return false;
            }

            // Get author information from emails
            $authorEmails = array_map('trim', explode(',', $postData['thesisauthor']));
            $authorNames = [];
            $authorIds = [];

            foreach ($authorEmails as $email) {
                if (!empty($email)) {
                    // Find user by email
                    $author = $this->userModel->findByEmail($email);
                    if ($author) {
                        // Build author name
                        $authorName = $author['First_Name'];
                        if (!empty($author['Middle_Name'])) {
                            $authorName .= ' ' . $author['Middle_Name'];
                        }
                        $authorName .= ' ' . $author['Last_Name'];
                        if (!empty($author['Extension'])) {
                            $authorName .= ' ' . $author['Extension'];
                        }
                        
                        $authorNames[] = $authorName;
                        $authorIds[] = $author['ID'];
                    } else {
                        // If user not found, use email as name
                        $authorNames[] = $email;
                        $authorIds[] = null; // No user ID for non-existent users
                    }
                }
            }

            if (empty($authorNames)) {
                $this->error = 'No valid authors found for the provided emails';
                return false;
            }

            // Process file upload
            $uploadedFiles = $this->processFiles($files['files']);
            if (!$uploadedFiles) {
                return false;
            }
            
            // Save to database - use the first author's ID as the main User_ID
            // or the admin's ID if no valid authors found
            $mainUserId = !empty($authorIds[0]) ? $authorIds[0] : $adminUserId;
            $authorString = implode(', ', $authorNames);
            
            foreach ($uploadedFiles as $fileInfo) {
                $success = $this->saveThesisToDatabase([
                    'User_ID' => $mainUserId,
                    'Thesis_Department' => $postData['department'] ?? '',
                    'Thesis_Course' => $postData['course'] ?? '',
                    'Thesis_Email' => $postData['thesisauthor'] ?? '', // Store original email string
                    'Title' => $postData['thesistitle'],
                    'Author' => $authorString,
                    'File_Path' => $fileInfo['file_path'],
                    'File_Size' => $fileInfo['file_size'],
                    'File_Type' => $fileInfo['file_type'],
                    'uploaded_at' => date('Y-m-d H:i:s')
                ]);
                
                if (!$success) {
                    $this->error = 'Failed to save thesis information to database';
                    return false;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->error = 'Upload failed: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Process uploaded files (same as before)
     */
    private function processFiles($files) {
        $uploadedFiles = [];
        $uploadDir = '../../../uploads/theses/';

        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->error = 'Failed to create upload directory';
                return false;
            }
        }
        
        // Check if files array is properly structured
        if (!isset($files['name']) || !is_array($files['name'])) {
            $this->error = 'Invalid file upload structure';
            return false;
        }
        
        // Process each file
        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $this->error = 'File upload error: ' . $this->getUploadError($files['error'][$i]);
                return false;
            }
            
            // Validate file type
            $fileName = $files['name'][$i];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'docx', 'zip'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $this->error = 'Invalid file type. Only PDF, DOCX, and ZIP files are allowed.';
                return false;
            }
            
            // Validate file size (max 50MB)
            $maxFileSize = 50 * 1024 * 1024; // 50MB
            if ($files['size'][$i] > $maxFileSize) {
                $this->error = 'File size exceeds the maximum limit of 50MB.';
                return false;
            }
            
            // Generate unique filename
            $uniqueName = uniqid() . '_' . time() . '_' . $i . '.' . $fileExtension;
            $destination = $uploadDir . $uniqueName;
            
            // Move uploaded file
            if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                $uploadedFiles[] = [
                    'file_path' => $destination,
                    'file_size' => $files['size'][$i],
                    'file_type' => $files['type'][$i],
                    'original_name' => $fileName
                ];
            } else {
                $this->error = 'Failed to move uploaded file.';
                return false;
            }
        }
        
        return $uploadedFiles;
    }

    /**
     * Save thesis information to database
     */
    private function saveThesisToDatabase($data) {
        try {
            $query = "INSERT INTO THESIS (User_ID, Thesis_Department, Thesis_Course, Thesis_Email, Title, Author, File_Path, File_Size, File_Type, uploaded_at) 
                      VALUES (:User_ID, :thesis_department, :thesis_course, :thesis_email, :title, :author, :file_path, :file_size, :file_type, :uploaded_at)";
            
            $this->db->query($query);
            $this->db->bind(':User_ID', $data['User_ID']);
            $this->db->bind(':thesis_department', $data['Thesis_Department']);
            $this->db->bind(':thesis_course', $data['Thesis_Course']);
            $this->db->bind(':thesis_email', $data['Thesis_Email']);
            $this->db->bind(':title', $data['Title']);
            $this->db->bind(':author', $data['Author']);
            $this->db->bind(':file_path', $data['file_path']);
            $this->db->bind(':file_size', $data['file_size']);
            $this->db->bind(':file_type', $data['file_type']);
            $this->db->bind(':uploaded_at', $data['uploaded_at']);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get all theses for admin view
     */
    public function getAllTheses() {
        $query = "SELECT t.*, u.First_Name, u.Middle_Name, u.Last_Name, u.Extension 
                  FROM THESIS t 
                  LEFT JOIN USER_INFORMATION u ON t.User_ID = u.ID 
                  ORDER BY t.uploaded_at DESC";
        
        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Get user's theses
     */
    public function getUserTheses($userId) {
        $query = "SELECT * FROM THESIS WHERE User_ID = :User_ID ORDER BY uploaded_at DESC";
        
        $this->db->query($query);
        $this->db->bind(':User_ID', $userId);
        
        return $this->db->resultSet();
    }

    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }
}
?>