<?php
class Menu {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil semua menu
    public function read() {
        $query = "SELECT * FROM menu ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
