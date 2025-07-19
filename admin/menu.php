<?php
class Menu {
    private $conn;
    private $table_name = "menu";

    public $kode_menu;
    public $nama_menu;
    public $kategori;
    public $harga;
    public $status;
    public $stok;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method untuk mengambil kode menu terakhir
    public function getLastKodeMenu() {
        $query = "SELECT kode_menu FROM " . $this->table_name . " ORDER BY kode_menu DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['kode_menu'] : null;
    }

    // Method untuk menambahkan menu baru
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
            SET kode_menu = :kode_menu,
                nama_menu = :nama_menu,
                kategori = :kategori,
                harga = :harga,
                status = :status,
                stok = :stok";

        $stmt = $this->conn->prepare($query);

        $harga = is_numeric($this->harga) ? (int)$this->harga : 0;
        $stok = is_numeric($this->stok) ? (int)$this->stok : 0;
        $kategori = substr(trim($this->kategori), 0, 20);

        $stmt->bindParam(":kode_menu", $this->kode_menu);
        $stmt->bindParam(":nama_menu", $this->nama_menu);
        $stmt->bindParam(":kategori", $kategori);
        $stmt->bindParam(":harga", $harga, PDO::PARAM_INT);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":stok", $stok, PDO::PARAM_INT);

        return $stmt->execute();
    }





    // Read all menu
    public function read() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, stok, created_at 
                FROM " . $this->table_name . " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read available menu
    public function readAvailable() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, stok, created_at 
            FROM " . $this->table_name . " WHERE status = 'available' ORDER BY kategori, nama_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read menu by category
    public function readByCategory() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, stok, created_at 
            FROM " . $this->table_name . " WHERE kategori = :kategori AND status = 'available' ORDER BY nama_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kategori", $this->kategori);
        $stmt->execute();

        return $stmt;
    }

    // Mendapatkan menu berdasarkan kode menu, mengembalikan array hasil fetch
    public function getByKode($kode_menu) {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, stok, created_at 
                FROM " . $this->table_name . " WHERE kode_menu = :kode_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_menu", $kode_menu);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update menu
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
            SET nama_menu=:nama_menu, kategori=:kategori, harga=:harga, status=:status, stok=:stok
            WHERE kode_menu=:kode_menu";

        $stmt = $this->conn->prepare($query);

        $harga = is_numeric($this->harga) ? (int)$this->harga : 0;
        $stok = is_numeric($this->stok) ? (int)$this->stok : 0;
        $kategori = substr(trim($this->kategori), 0, 20);

        $stmt->bindParam(":nama_menu", $this->nama_menu);
        $stmt->bindParam(":kategori", $kategori);
        $stmt->bindParam(":harga", $harga, PDO::PARAM_INT);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":stok", $stok, PDO::PARAM_INT);
        $stmt->bindParam(":kode_menu", $this->kode_menu);

        return $stmt->execute();
    }

    // Delete menu
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE kode_menu = :kode_menu";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_menu", $this->kode_menu);

        return $stmt->execute();
    }
}
