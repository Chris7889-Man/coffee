<?php
require_once '../config/database.php';

class Pesanan {
    private $conn;
    private $table_name = "pesanan";

    public $kode_pesanan;
    public $nama_pelanggan;
    public $total_harga;
    public $tgl_pesanan;
    public $status_pesanan;
    public $jenis_pesanan;
    public $catatan_pesanan;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create pesanan
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET kode_pesanan=:kode_pesanan, nama_pelanggan=:nama_pelanggan, total_harga=:total_harga, tgl_pesanan=:tgl_pesanan";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":kode_pesanan", $this->kode_pesanan);
        $stmt->bindParam(":nama_pelanggan", $this->nama_pelanggan);
        $stmt->bindParam(":total_harga", $this->total_harga);
        $stmt->bindParam(":tgl_pesanan", $this->tgl_pesanan);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all pesanan dengan detail menu dan jumlah
    public function read() {
        $query = "SELECT p.kode_pesanan, p.nama_pelanggan, dp.jumlah, m.nama AS nama_menu, p.tgl_pesanan, p.status_pesanan AS status
                  FROM " . $this->table_name . " p
                  LEFT JOIN detail_pesanan dp ON p.kode_pesanan = dp.kode_pesanan
                  LEFT JOIN menu m ON dp.kode_menu = m.kode_menu
                  ORDER BY p.tgl_pesanan DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Read pesanan by customer
    public function readByCustomer() {
        $query = "SELECT kode_pesanan, nama_pelanggan, total_harga, tgl_pesanan 
                  FROM " . $this->table_name . " 
                  WHERE nama_pelanggan = :nama_pelanggan 
                  ORDER BY tgl_pesanan DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nama_pelanggan", $this->nama_pelanggan);
        $stmt->execute();

        return $stmt;
    }

    // Read pesanan by status
    public function readByStatus() {
        $query = "SELECT kode_pesanan, nama_pelanggan, total_harga, tgl_pesanan, 
                  status_pesanan, jenis_pesanan, catatan_pesanan 
                  FROM " . $this->table_name . " 
                  WHERE status_pesanan = :status_pesanan 
                  ORDER BY tgl_pesanan DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status_pesanan", $this->status_pesanan);
        $stmt->execute();

        return $stmt;
    }

    // Get pesanan by code
    public function getByCode() {
        $query = "SELECT kode_pesanan, nama_pelanggan, total_harga, tgl_pesanan, 
                  status_pesanan, jenis_pesanan, catatan_pesanan 
                  FROM " . $this->table_name . " WHERE kode_pesanan = :kode_pesanan";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_pesanan", $this->kode_pesanan);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nama_pelanggan = $row['nama_pelanggan'];
            $this->total_harga = $row['total_harga'];
            $this->tgl_pesanan = $row['tgl_pesanan'];
            $this->status_pesanan = $row['status_pesanan'];
            $this->jenis_pesanan = $row['jenis_pesanan'];
            $this->catatan_pesanan = $row['catatan_pesanan'];
            return true;
        }
        return false;
    }

    // Update status pesanan
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status_pesanan=:status_pesanan WHERE kode_pesanan=:kode_pesanan";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status_pesanan", $this->status_pesanan);
        $stmt->bindParam(":kode_pesanan", $this->kode_pesanan);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update pesanan
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET total_harga=:total_harga, status_pesanan=:status_pesanan, jenis_pesanan=:jenis_pesanan, catatan_pesanan=:catatan_pesanan WHERE kode_pesanan=:kode_pesanan";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":total_harga", $this->total_harga);
        $stmt->bindParam(":status_pesanan", $this->status_pesanan);
        $stmt->bindParam(":jenis_pesanan", $this->jenis_pesanan);
        $stmt->bindParam(":catatan_pesanan", $this->catatan_pesanan);
        $stmt->bindParam(":kode_pesanan", $this->kode_pesanan);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete pesanan
    public function delete() {
        // Delete detail pesanan first
        $query = "DELETE FROM detail_pesanan WHERE kode_pesanan = :kode_pesanan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_pesanan", $this->kode_pesanan);
        $stmt->execute();

        // Delete pesanan
        $query = "DELETE FROM " . $this->table_name . " WHERE kode_pesanan = :kode_pesanan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode_pesanan", $this->kode_pesanan);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Generate kode pesanan
    public function generateKodePesanan() {
        $date = date('Ymd');
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE DATE(tgl_pesanan) = CURDATE()";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $counter = $row['total'] + 1;
        return "ORD" . $date . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }
}
?>
