<?php
class Pesanan
{
    private $conn;
    public $status_pesanan;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Ambil semua pesanan
    public function read()
    {
        $query = "SELECT * FROM pesanan ORDER BY tgl_pesanan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ambil pesanan berdasarkan status
    public function readByStatus()
    {
        $query = "SELECT * FROM pesanan WHERE status_pesanan = :status_pesanan ORDER BY tgl_pesanan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status_pesanan', $this->status_pesanan);
        $stmt->execute();
        return $stmt;
    }

    // Ambil satu pesanan berdasarkan kode pesanan
    public function getByKodePesanan($kode_pesanan)
    {
        $query = "SELECT * FROM pesanan WHERE kode_pesanan = :kode_pesanan LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode_pesanan', $kode_pesanan);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update pesanan
    public function updatePesanan($kode_pesanan, $nama_pelanggan, $kode_menu, $jumlah, $total_harga, $status_pesanan)
    {
        $query = "UPDATE pesanan SET 
                    nama_pelanggan = :nama_pelanggan,
                    kode_menu = :kode_menu,
                    jumlah = :jumlah,
                    total_harga = :total_harga,
                    status_pesanan = :status_pesanan
                WHERE kode_pesanan = :kode_pesanan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
        $stmt->bindParam(':kode_menu', $kode_menu);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':total_harga', $total_harga);
        $stmt->bindParam(':status_pesanan', $status_pesanan);
        $stmt->bindParam(':kode_pesanan', $kode_pesanan);
        return $stmt->execute();
    }
}
?>