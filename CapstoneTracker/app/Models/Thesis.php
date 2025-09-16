<?php
class Thesis extends Model {
    public function getUserThesis($userId) {
        $this->db->query('SELECT * FROM theses WHERE user_id = :user_id ORDER BY created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    public function uploadThesis($data, $files) {
        // Handle file upload and database insertion
        $uploadDir = APPROOT . '/public/assets/uploads/';
        $fileName = time() . '_' . basename($files['thesis_file']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($files['thesis_file']['tmp_name'], $targetPath)) {
            $this->db->query('INSERT INTO theses (user_id, title, authors, year_level, file_path) 
                             VALUES (:user_id, :title, :authors, :year_level, :file_path)');
            $this->db->bind(':user_id', $_SESSION['user_id']);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':authors', $data['authors']);
            $this->db->bind(':year_level', $data['year_level']);
            $this->db->bind(':file_path', $fileName);
            
            return $this->db->execute();
        }
        
        return false;
    }
    
    public function getAllTheses() {
        $this->db->query('SELECT theses.*, users.name as author_name 
                         FROM theses 
                         INNER JOIN users ON theses.user_id = users.id 
                         ORDER BY created_at DESC');
        return $this->db->resultSet();
    }
}