<?php
class Thesis extends Model {
    protected $tableName = 'theses'; // Set table name
    
    // Thesis-specific methods
    public function getUserTheses($userId) {
        // Can use parent's methods
        $this->db->query('SELECT * FROM theses ...');
        // ... thesis-specific logic
    }
    
    // But also gets these for free from parent:
    // - findAll() to get all theses
    // - findById() to get specific thesis
    // - delete() to delete a thesis
}
?>  