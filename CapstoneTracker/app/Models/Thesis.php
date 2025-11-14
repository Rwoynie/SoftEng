<?php


require_once 'Model.php';
require_once 'User.php';

class Thesis extends Model {
    protected $tableName = 'THESIS';
    private $error = null;
    private $userModel;
    

    public function __construct($db = null) {
        parent::__construct();
        if ($db) {
            $this->db = $db;
        }
        $this->userModel = new User();
    }

    /**
     * Upload thesis file and save to database (ADMIN VERSION)
     */
    public function uploadThesis($postData, $files, $adminUserId) {
        try {
            error_log("Starting thesis upload process");
            
            // Validate input
            if (empty($postData['thesistitle'])) {
                $this->error = 'Thesis title is required';
                return false;
            }
            
            // NEW: Check if title already exists
            $title = trim($postData['thesistitle']);
            if ($this->titleExists($title)) {
                $this->error = 'A thesis with this title already exists. Please choose a different title.';
                return false;
            }
            
            
            // Check if both abstract and thesis files are uploaded
            if (empty($files['abstract_file']) || empty($files['thesis_file'])) {
                $this->error = 'Both abstract and thesis files are required';
                return false;
            }

            // Validate and process author emails
            if (empty($postData['thesisauthor'])) {
                $this->error = 'Author emails are required';
                return false;
            }

            if (empty($postData['thesisadviser'])) {
                $this->error = 'Adviser email is required';
                return false;
            }

            if (empty($postData['department'])) {
                $this->error = 'Department is required';
                return false;
            }

            if (empty($postData['course'])) {
                $this->error = 'Course is required';
                return false;
            }

            if (empty($postData['hardbound'])) {
                $this->error = 'Hardbound availability is required';
                return false;
            }

            // Get author information from emails
            $authorEmails = array_map('trim', explode(',', $postData['thesisauthor']));
            $authorNames = [];
            $authorIds = [];
            $facultyAuthors = []; // Track faculty users
            $pendingAuthors = []; // Track pending accounts

            // Get adviser email information from emails
            $adviserEmails = array_map('trim', explode(',', $postData['thesisadviser']));
            $adviserNames = [];
            $adviserIds = [];
            $nonFacultyAdvisers = []; // Track non-faculty advisers
            $pendingAdvisers = []; // Track pending accounts for advisers

            foreach ($authorEmails as $email) {
                if (!empty($email)) {
                    // Find user by email
                    $author = $this->userModel->findByEmail($email);
                    if ($author) {
                        // Check if user has pending status
                        if ($author->Acc_Status === 'pending') {
                            $pendingAuthors[] = $email;
                            continue; // Skip pending users
                        }
                        
                        // Check if user has faculty role
                        if ($author->User_Role === 'faculty') {
                            $facultyAuthors[] = $email;
                            continue; // Skip faculty users as authors
                        }
                        
                        // Build author name
                        $authorName = $author->First_Name;  
                        if (!empty($author->Middle_Name)) { 
                            $authorName .= ' ' . $author->Middle_Name;
                        }
                        $authorName .= ' ' . $author->Last_Name; 
                        if (!empty($author->Extension)) { 
                            $authorName .= ' ' . $author->Extension;
                        }
                        
                        $authorNames[] = $authorName;
                        $authorIds[] = $author->ID; 
                    } else {
                        // If user not found, use email as name
                        $authorNames[] = $email;
                        $authorIds[] = null;
                    }
                }
            }

            // Check if any pending accounts were found in authors
            if (!empty($pendingAuthors)) {
                $pendingEmails = implode(', ', $pendingAuthors);
                $this->error = "Cannot add pending accounts as authors. Please approve the following accounts first: " . $pendingEmails;
                return false;
            }

            // Check if any faculty users were found in authors
            if (!empty($facultyAuthors)) {
                $facultyEmails = implode(', ', $facultyAuthors);
                $this->error = "Faculty users cannot be listed as authors. Please remove the following faculty emails: " . $facultyEmails;
                return false;
            }

            foreach ($adviserEmails as $Aemail) {
                if (!empty($Aemail)) {
                    // Find user by email
                    $adviser = $this->userModel->findByEmail($Aemail);
                    if ($adviser) {
                        // Check if user has pending status
                        if ($adviser->Acc_Status === 'pending') {
                            $pendingAdvisers[] = $Aemail;
                            continue; // Skip pending users
                        }
                        
                        // Check if user has faculty role
                        if ($adviser->User_Role !== 'faculty') {
                            $nonFacultyAdvisers[] = $Aemail;
                            continue; // Skip non-faculty users as advisers
                        }
                        
                        // Build adviser name
                        $adviserName = $adviser->First_Name;  
                        if (!empty($adviser->Middle_Name)) { 
                            $adviserName .= ' ' . $adviser->Middle_Name;
                        }
                        $adviserName .= ' ' . $adviser->Last_Name; 
                        if (!empty($adviser->Extension)) { 
                            $adviserName .= ' ' . $adviser->Extension;
                        }
                        
                        $adviserNames[] = $adviserName;
                        $adviserIds[] = $adviser->ID; 
                    } else {
                        // If user not found, add to non-faculty list
                        $nonFacultyAdvisers[] = $Aemail;
                    }
                }
            }

            // Check if any pending accounts were found in advisers
            if (!empty($pendingAdvisers)) {
                $pendingEmails = implode(', ', $pendingAdvisers);
                $this->error = "Cannot add pending accounts as advisers. Please approve the following accounts first: " . $pendingEmails;
                return false;
            }

            // Check if any non-faculty users were found in advisers
            if (!empty($nonFacultyAdvisers)) {
                $nonFacultyEmails = implode(', ', $nonFacultyAdvisers);
                $this->error = "Only faculty users can be assigned as advisers. Please remove the following non-faculty emails: " . $nonFacultyEmails;
                return false;
            }

            if (empty($authorNames)) {
                $this->error = 'No valid authors found for the provided emails';
                return false;
            }

            if (empty($adviserNames)) {
                $this->error = 'No valid faculty advisers found for the provided emails';
                return false;
            }

            // Process the uploaded abstract file
            $abstractFileData = $this->processUploadedFile($files['abstract_file'], 'abstract');
            if (!$abstractFileData) {
                return false;
            }

            // Process the uploaded thesis file
            $thesisFileData = $this->processUploadedFile($files['thesis_file'], 'thesis');
            if (!$thesisFileData) {
                return false;
            }
            
            // Use the first valid author ID or admin ID as the main User_ID
            $mainUserId = !empty($authorIds[0]) ? $authorIds[0] : $adminUserId;
            $authorString = implode(', ', $authorNames);

            $adviserUserID = !empty($adviserIds[0]) ? $adviserIds[0] : $adminUserId;
            $adviserString = implode(',', $adviserNames);
            
            // Save to database with both abstract and thesis files
            $success = $this->saveThesisToDatabase([
                'User_ID' => $mainUserId,
                'Thesis_Department' => $postData['department'],
                'Thesis_Course' => $postData['course'],
                'Thesis_Email' => $postData['thesisauthor'],
                'Title' => $postData['thesistitle'],
                'Author' => $authorString,
                'Adviser' => $adviserString,
                'HardBound_Available' => $postData['hardbound'] ?? 'Yes',
                'Thesis_AbstractFile' => $abstractFileData,
                'Thesis_File' => $thesisFileData,
                'uploaded_at' => date('Y-m-d H:i:s')
            ]);
            
            if (!$success) {
                error_log("Failed to save thesis to database: " . $this->error);
                return false;
            }
            
            error_log("Thesis uploaded successfully: " . $postData['thesistitle']);
            return true;
            
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            $this->error = 'Upload failed: ' . $e->getMessage();
            return false;
        }
    }

    public function findById($thesisId) {
        try {
            // Custom query with join for thesis-specific needs
            $query = "SELECT t.*, u.First_Name, u.Middle_Name, u.Last_Name, u.Extension 
                      FROM THESIS t 
                      LEFT JOIN USER_INFORMATION u ON t.User_ID = u.ID 
                      WHERE t.ID = :id";
            
            $this->db->query($query);
            $this->db->bind(':id', $thesisId);
            
            $result = $this->db->single();
            
            if (!$result) {
                error_log("No thesis found with ID: " . $thesisId);
                return false;
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error finding thesis by ID: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Update thesis
     */
    public function updateThesis($thesisId, $postData, $files = null) {
        try {
            error_log("Starting thesis update process for ID: " . $thesisId);
            
            // Validate input
            if (empty($postData['thesistitle'])) {
                $this->error = 'Thesis title is required';
                return false;
            }
            
            // Check if title already exists (excluding current thesis)
            $title = trim($postData['thesistitle']);
            if ($this->titleExists($title, $thesisId)) {
                $this->error = 'A thesis with this title already exists. Please choose a different title.';
                return false;
            }
            
            // Validate author emails
            if (empty($postData['thesisauthor'])) {
                $this->error = 'Author emails are required';
                return false;
            }

            if (empty($postData['thesisadviser'])) {
                $this->error = 'Adviser email is required';
                return false;
            }

            if (empty($postData['department'])) {
                $this->error = 'Department is required';
                return false;
            }

            if (empty($postData['course'])) {
                $this->error = 'Course is required';
                return false;
            }

            if (empty($postData['hardbound'])) {
                $this->error = 'Hardbound availability is required';
                return false;
            }

            // Get author information from emails (same logic as upload)
            $authorEmails = array_map('trim', explode(',', $postData['thesisauthor']));
            $authorNames = [];
            $authorIds = [];
            $facultyAuthors = [];
            $pendingAuthors = [];

            // Get adviser information from emails (same logic as upload)
            $adviserEmails = array_map('trim', explode(',', $postData['thesisadviser']));
            $adviserNames = [];
            $adviserIds = [];
            $nonFacultyAdvisers = [];
            $pendingAdvisers = [];

            foreach ($authorEmails as $email) {
                if (!empty($email)) {
                    // Find user by email
                    $author = $this->userModel->findByEmail($email);
                    if ($author) {
                        // Check if user has pending status
                        if ($author->Acc_Status === 'pending') {
                            $pendingAuthors[] = $email;
                            continue; // Skip pending users
                        }
                        
                        // Check if user has faculty role
                        if ($author->User_Role === 'faculty') {
                            $facultyAuthors[] = $email;
                            continue; // Skip faculty users as authors
                        }
                        
                        // Build author name
                        $authorName = $author->First_Name;  
                        if (!empty($author->Middle_Name)) { 
                            $authorName .= ' ' . $author->Middle_Name;
                        }
                        $authorName .= ' ' . $author->Last_Name; 
                        if (!empty($author->Extension)) { 
                            $authorName .= ' ' . $author->Extension;
                        }
                        
                        $authorNames[] = $authorName;
                        $authorIds[] = $author->ID; 
                    } else {
                        // If user not found, use email as name
                        $authorNames[] = $email;
                        $authorIds[] = null;
                    }
                }
            }
            
            // Check if any pending accounts were found in authors
            if (!empty($pendingAuthors)) {
                $pendingEmails = implode(', ', $pendingAuthors);
                $this->error = "Cannot add pending accounts as authors. Please approve the following accounts first: " . $pendingEmails;
                return false;
            }
            
            // Check if any faculty users were found in authors
            if (!empty($facultyAuthors)) {
                $facultyEmails = implode(', ', $facultyAuthors);
                $this->error = "Faculty users cannot be listed as authors. Please remove the following faculty emails: " . $facultyEmails;
                return false;
            }
            
            foreach ($adviserEmails as $Aemail) {
                if (!empty($Aemail)) {
                    // Find user by email
                    $adviser = $this->userModel->findByEmail($Aemail);
                    if ($adviser) {
                        // Check if user has pending status
                        if ($adviser->Acc_Status === 'pending') {
                            $pendingAdvisers[] = $Aemail;
                            continue; // Skip pending users
                        }
                        
                        // Check if user has faculty role
                        if ($adviser->User_Role !== 'faculty') {
                            $nonFacultyAdvisers[] = $Aemail;
                            continue; // Skip non-faculty users as advisers
                        }
                        
                        // Build adviser name
                        $adviserName = $adviser->First_Name;  
                        if (!empty($adviser->Middle_Name)) { 
                            $adviserName .= ' ' . $adviser->Middle_Name;
                        }
                        $adviserName .= ' ' . $adviser->Last_Name; 
                        if (!empty($adviser->Extension)) { 
                            $adviserName .= ' ' . $adviser->Extension;
                        }
                        
                        $adviserNames[] = $adviserName;
                        $adviserIds[] = $adviser->ID; 
                    } else {
                        // If user not found, add to non-faculty list
                        $nonFacultyAdvisers[] = $Aemail;
                    }
                }
            }
            
            // Check if any pending accounts were found in advisers
            if (!empty($pendingAdvisers)) {
                $pendingEmails = implode(', ', $pendingAdvisers);
                $this->error = "Cannot add pending accounts as advisers. Please approve the following accounts first: " . $pendingEmails;
                return false;
            }
            
            // Check if any non-faculty users were found in advisers
            if (!empty($nonFacultyAdvisers)) {
                $nonFacultyEmails = implode(', ', $nonFacultyAdvisers);
                $this->error = "Only faculty users can be assigned as advisers. Please remove the following non-faculty emails: " . $nonFacultyEmails;
                return false;
            }
            
            if (empty($authorNames)) {
                $this->error = 'No valid authors found for the provided emails';
                return false;
            }
            
            if (empty($adviserNames)) {
                $this->error = 'No valid faculty advisers found for the provided emails';
                return false;
            }

            // Process files if provided
            $abstractFileData = null;
            $thesisFileData = null;

            if ($files && !empty($files['abstract_file']['name'])) {
                $abstractFileData = $this->processUploadedFile($files['abstract_file'], 'abstract');
                if (!$abstractFileData) {
                    return false;
                }
            }

            if ($files && !empty($files['thesis_file']['name'])) {
                $thesisFileData = $this->processUploadedFile($files['thesis_file'], 'thesis');
                if (!$thesisFileData) {
                    return false;
                }
            }

            // Update database
            $success = $this->updateThesisInDatabase($thesisId, [
                'Thesis_Department' => $postData['department'],
                'Thesis_Course' => $postData['course'],
                'Thesis_Email' => $postData['thesisauthor'],
                'Title' => $postData['thesistitle'],
                'Author' => implode(', ', $authorNames),
                'Adviser' => implode(',', $adviserNames),
                'HardBound_Available' => $postData['hardbound'] ?? 'Yes',
                'Thesis_AbstractFile' => $abstractFileData,
                'Thesis_File' => $thesisFileData,
                'updated_at' => date('Y-m-d H:i:s')
            ], $abstractFileData !== null, $thesisFileData !== null);
            
            if (!$success) {
                error_log("Failed to update thesis in database: " . $this->error);
                return false;
            }
            
            error_log("Thesis updated successfully: " . $postData['thesistitle']);
            return true;
            
        } catch (Exception $e) {
            error_log("Update error: " . $e->getMessage());
            $this->error = 'Update failed: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Update thesis in database
     */
    private function updateThesisInDatabase($thesisId, $data, $updateAbstract = false, $updateThesis = false) {
        try {
            $query = "UPDATE THESIS SET 
                    Thesis_Department = :thesis_department, 
                    Thesis_Course = :thesis_course, 
                    Thesis_Email = :thesis_email, 
                    Title = :title, 
                    Author = :author, 
                    Adviser = :adviser, 
                    HardBound_Available = :hardbound_available, 
                    updated_at = :updated_at";
            
            // Add file updates if provided
            if ($updateAbstract) {
                $query .= ", Thesis_AbstractFile = :thesis_abstract_file";
            }
            if ($updateThesis) {
                $query .= ", Thesis_File = :thesis_file";
            }
            
            $query .= " WHERE ID = :id";
            
            $this->db->query($query);
            $this->db->bind(':thesis_department', $data['Thesis_Department']);
            $this->db->bind(':thesis_course', $data['Thesis_Course']);
            $this->db->bind(':thesis_email', $data['Thesis_Email']);
            $this->db->bind(':title', $data['Title']);
            $this->db->bind(':author', $data['Author']);
            $this->db->bind(':adviser', $data['Adviser']);
            $this->db->bind(':hardbound_available', $data['HardBound_Available']);
            $this->db->bind(':updated_at', $data['updated_at']);
            $this->db->bind(':id', $thesisId);
            
            if ($updateAbstract) {
                $this->db->bind(':thesis_abstract_file', $data['Thesis_AbstractFile']);
            }
            if ($updateThesis) {
                $this->db->bind(':thesis_file', $data['Thesis_File']);
            }
            
            $result = $this->db->execute();
            
            if (!$result) {
                $errorInfo = $this->db->getError();
                error_log("Database execution failed: " . print_r($errorInfo, true));
                $this->error = "Database error: " . ($errorInfo['message'] ?? 'Unknown database error');
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Database error in updateThesisInDatabase: " . $e->getMessage());
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Delete thesis
     */
    public function deleteThesis($thesisId) {
        try {
            $query = "DELETE FROM THESIS WHERE ID = :id";
            
            $this->db->query($query);
            $this->db->bind(':id', $thesisId);
            
            $result = $this->db->execute();
            
            if (!$result) {
                $errorInfo = $this->db->getError();
                error_log("Database execution failed: " . print_r($errorInfo, true));
                $this->error = "Database error: " . ($errorInfo['message'] ?? 'Unknown database error');
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Database error in deleteThesis: " . $e->getMessage());
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Check if thesis title already exists
     */
    public function titleExists($title, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM THESIS WHERE LOWER(Title) = LOWER(:title)";
            
            if ($excludeId) {
                $query .= " AND ID != :exclude_id";
            }
            
            $this->db->query($query);
            $this->db->bind(':title', trim($title));
            
            if ($excludeId) {
                $this->db->bind(':exclude_id', $excludeId);
            }
            
            $result = $this->db->single();
            return $result->count > 0;
            
        } catch (Exception $e) {
            error_log("Error checking title existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process uploaded file and return BLOB data
     */
    private function processUploadedFile($file, $fileType = 'thesis') {
        // Handle single file upload
        if (!is_array($file['name'])) {
            return $this->validateAndProcessSingleFile($file, $fileType);
        } else {
            // Handle multiple files - take only the first file
            if ($file['error'][0] !== UPLOAD_ERR_OK) {
                $this->error = $fileType . ' file upload error: ' . $this->getUploadError($file['error'][0]);
                return false;
            }
            
            $singleFile = [
                'name' => $file['name'][0],
                'type' => $file['type'][0],
                'tmp_name' => $file['tmp_name'][0],
                'error' => $file['error'][0],
                'size' => $file['size'][0]
            ];
            
            return $this->validateAndProcessSingleFile($singleFile, $fileType);
        }
    }

    /**
     * Validate and process single file
     */
    private function validateAndProcessSingleFile($file, $fileType = 'thesis') {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error = $fileType . ' file upload error: ' . $this->getUploadError($file['error']);
            return false;
        }
        
        $fileName = $file['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $this->error = 'Invalid ' . $fileType . ' file type. Only PDF files are allowed.';
            return false;
        }
        
        // Validate file size (max 50MB)
        $maxFileSize = 50 * 1024 * 1024;
        if ($file['size'] > $maxFileSize) {
            $this->error = $fileType . ' file size exceeds the maximum limit of 50MB.';
            return false;
        }
        
        if ($file['size'] == 0) {
            $this->error = 'The uploaded ' . $fileType . ' file is empty.';
            return false;
        }
        
        // Read file as binary data (just the BLOB, no metadata)
        $fileData = file_get_contents($file['tmp_name']);
        if ($fileData === false) {
            $this->error = 'Failed to read ' . $fileType . ' file data.';
            return false;
        }
        
        return $fileData; // Return just the BLOB data
    }

    /**
     * Save thesis information to database with both abstract and thesis files
     */
    private function saveThesisToDatabase($data) {
        try {
            $query = "INSERT INTO THESIS (User_ID, Thesis_Department, Thesis_Course, Thesis_Email, Title, Author, Adviser, HardBound_Available, Thesis_AbstractFile, Thesis_File, uploaded_at) 
                    VALUES (:User_ID, :thesis_department, :thesis_course, :thesis_email, :title, :author, :adviser, :hardbound_available, :thesis_abstract_file, :thesis_file, :uploaded_at)";
            
            $this->db->query($query);
            $this->db->bind(':User_ID', $data['User_ID']);
            $this->db->bind(':thesis_department', $data['Thesis_Department']);
            $this->db->bind(':thesis_course', $data['Thesis_Course']);
            $this->db->bind(':thesis_email', $data['Thesis_Email']);
            $this->db->bind(':title', $data['Title']);
            $this->db->bind(':author', $data['Author']);
            $this->db->bind(':adviser', $data['Adviser']);
            $this->db->bind(':hardbound_available', $data['HardBound_Available'] ?? 'Yes'); // Default to 'Yes'
            $this->db->bind(':thesis_abstract_file', $data['Thesis_AbstractFile']); // Abstract file BLOB
            $this->db->bind(':thesis_file', $data['Thesis_File']); // Thesis file BLOB
            $this->db->bind(':uploaded_at', $data['uploaded_at']);
            
            $result = $this->db->execute();
            
            if (!$result) {
                $errorInfo = $this->db->getError();
                error_log("Database execution failed: " . print_r($errorInfo, true));
                $this->error = "Database error: " . ($errorInfo['message'] ?? 'Unknown database error');
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Database error in saveThesisToDatabase: " . $e->getMessage());
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get thesis file by ID
     */
    public function getThesisFile($thesisId) {
        $query = "SELECT Thesis_File, Title, Author FROM THESIS WHERE ID = :id";
        
        $this->db->query($query);
        $this->db->bind(':id', $thesisId);
        
        return $this->db->single();
    }

    /**
     * Get abstract file by ID
     */
    public function getAbstractFile($thesisId) {
        $query = "SELECT Thesis_AbstractFile FROM THESIS WHERE ID = :id";
        
        $this->db->query($query);
        $this->db->bind(':id', $thesisId);
        
        return $this->db->single();
    }


    /**
     * Get both abstract and thesis files by ID
     */
    public function getThesisFiles($thesisId) {
        $query = "SELECT Thesis_AbstractFile, Thesis_File FROM THESIS WHERE ID = :id";
        
        $this->db->query($query);
        $this->db->bind(':id', $thesisId);
        
        return $this->db->single();
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

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }
}
