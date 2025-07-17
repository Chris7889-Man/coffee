<?php
require_once __DIR__ . '/../config/database.php';

class Menu {
    private $conn;
    private $table_name = "menu";

    public $kode_menu;
    public $nama_menu;
    public $kategori;
    public $harga;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create menu
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
            SET kode_menu=:kode_menu, nama_menu=:nama_menu, kategori=:kategori, harga=:harga, status=:status";

        $stmt = $this->conn->prepare($query);

        $harga = is_numeric($this->harga) ? (int)$this->harga : 0;
        // Pastikan kategori di-trim dan dibatasi 20 karakter
        $kategori = substr(trim($this->kategori), 0, 20);

        $stmt->bindParam(":kode_menu", $this->kode_menu);
        $stmt->bindParam(":nama_menu", $this->nama_menu);
        $stmt->bindParam(":kategori", $kategori);
        $stmt->bindParam(":harga", $harga, PDO::PARAM_INT);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all menu
    public function read() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, created_at 
                FROM " . $this->table_name . " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read available menu
    public function readAvailable() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, created_at 
            FROM " . $this->table_name . " WHERE status = 'available' ORDER BY kategori, nama_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read menu by category
    public function readByCategory() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, created_at 
            FROM " . $this->table_name . " WHERE kategori = :kategori AND status = 'available' ORDER BY nama_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kategori", $this->kategori);
        $stmt->execute();

        return $stmt;
    }

    // Get menu by code
    public function getByCode() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, created_at 
                FROM " . $this->table_name . " WHERE kode_menu = :kode_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_menu", $this->kode_menu);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nama_menu = $row['nama_menu'];
            $this->kategori = $row['kategori'];
            $this->harga = $row['harga'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Update menu
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
            SET nama_menu=:nama_menu, kategori=:kategori, harga=:harga, status=:status  WHERE kode_menu=:kode_menu";

        $stmt = $this->conn->prepare($query);

        $harga = is_numeric($this->harga) ? (int)$this->harga : 0;
        $kategori = substr(trim($this->kategori), 0, 20);

        $stmt->bindParam(":nama_menu", $this->nama_menu);
        $stmt->bindParam(":kategori", $kategori);
        $stmt->bindParam(":harga", $harga, PDO::PARAM_INT);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":kode_menu", $this->kode_menu);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete menu
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE kode_menu = :kode_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_menu", $this->kode_menu);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Search menu
    public function search($keywords) {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, created_at FROM " . $this->table_name . "  WHERE (nama_menu LIKE :keywords) AND status = 'available' ORDER BY nama_menu";

        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(":keywords", $keywords);
        $stmt->execute();

        return $stmt;
    }

    public function getLastKodeMenu() {
    $query = "SELECT kode_menu FROM menu ORDER BY kode_menu DESC LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['kode_menu'];
    } else {
        return null;
    }
}



public function getByKode($kode_menu) {
    $query = "SELECT * FROM menu WHERE kode_menu = :kode_menu LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



public function delete_menu($kode_menu) {
    $query = "DELETE FROM menu WHERE kode_menu = :kode_menu";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    return $stmt->execute();
}


}



?>