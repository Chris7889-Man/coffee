<?php
class Pesanan {
    private $conn;
    public $status_pesanan;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil semua pesanan
    public function read() {
        $query = "SELECT * FROM pesanan ORDER BY tgl_pesanan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ambil pesanan berdasarkan status
    public function readByStatus() {
        $query = "SELECT * FROM pesanan WHERE status_pesanan = :status_pesanan ORDER BY tgl_pesanan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status_pesanan', $this->status_pesanan);
        $stmt->execute();
        return $stmt;
    }
}
?>
