<?php
class Menu {
    private $conn;
    private $table_name = "menu"; // Tabel untuk data menu
    private $history_table_name = "stok_history"; // Tabel untuk history stok


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


    // Method untuk menambahkan menu baru dan mencatat history stok awal
    public function create() {
        try {
            // Memulai transaksi
            $this->conn->beginTransaction();

            // Query untuk insert data menu
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

            // Bind parameter untuk insert menu
            $stmt->bindParam(":kode_menu", $this->kode_menu);
            $stmt->bindParam(":nama_menu", $this->nama_menu);
            $stmt->bindParam(":kategori", $kategori);
            $stmt->bindParam(":harga", $harga, PDO::PARAM_INT);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":stok", $stok, PDO::PARAM_INT);

            // Eksekusi insert menu. Jika gagal, rollback dan kembalikan false.
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // =========================================================
            // Bagian untuk mencatat history stok awal setelah menu dibuat
            // =========================================================
            $queryHistory = "INSERT INTO " . $this->history_table_name . " 
                (kode_menu, stok_lama, stok_baru, tgl_update, keterangan)
                VALUES (:kode_menu, :stok_lama, :stok_baru, NOW(), :keterangan)";
            
            $stmtHistory = $this->conn->prepare($queryHistory);

            $stokLama = 0; // Stok lama adalah 0 karena ini adalah penambahan menu baru
            $stokBaru = (int)$this->stok; // Stok baru adalah nilai stok yang diinput
            $keterangan = "stok yang baru ditambahkan";

            // Bind parameter untuk insert history stok
            $stmtHistory->bindParam(':kode_menu', $this->kode_menu);
            $stmtHistory->bindParam(':stok_lama', $stokLama, PDO::PARAM_INT);
            $stmtHistory->bindParam(':stok_baru', $stokBaru, PDO::PARAM_INT);
            $stmtHistory->bindParam(':keterangan', $keterangan);

            // Eksekusi insert history stok. Jika gagal, rollback dan kembalikan false.
            if (!$stmtHistory->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Jika semua query berhasil, lakukan commit transaksi
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // Tangani error PDO (misalnya duplikasi kode_menu, dll.)
            $this->conn->rollBack();
            error_log("Error pada create menu: " . $e->getMessage()); // Catat error ke log
            return false;
        } catch (Exception $e) {
            // Tangani error umum lainnya
            $this->conn->rollBack();
            error_log("Error tidak terduga pada create menu: " . $e->getMessage());
            return false;
        }
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
            FROM " . $this->table_name . " WHERE status = 'Tersedia' ORDER BY kategori, nama_menu";
            // Perhatikan: status di tabel mungkin "Tersedia" atau "available", sesuaikan dengan DB Anda
            // Di tambah_menu.php Anda menggunakan "Tersedia"
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }


    // Read menu by category
    public function readByCategory() {
        $query = "SELECT kode_menu, nama_menu, kategori, harga, status, stok, created_at 
            FROM " . $this->table_name . " WHERE kategori = :kategori AND status = 'Tersedia' ORDER BY nama_menu";
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
        try {
            // Perlu mendapatkan stok lama sebelum update untuk history
            $currentMenu = $this->getByKode($this->kode_menu);
            $stokLama = $currentMenu ? $currentMenu['stok'] : 0;

            // Memulai transaksi
            $this->conn->beginTransaction();

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

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // =========================================================
            // Bagian untuk mencatat history stok saat update (jika stok berubah)
            // =========================================================
            if ($stokLama !== $stok) { // Hanya catat history jika stok benar-benar berubah
                $queryHistory = "INSERT INTO " . $this->history_table_name . " 
                    (kode_menu, stok_lama, stok_baru, tgl_update, keterangan)
                    VALUES (:kode_menu, :stok_lama, :stok_baru, NOW(), :keterangan)";
                
                $stmtHistory = $this->conn->prepare($queryHistory);

                $keterangan = "Update stok";
                if ($stok > $stokLama) {
                    $keterangan = "Penambahan stok";
                } elseif ($stok < $stokLama) {
                    $keterangan = "Pengurangan stok";
                }

                $stmtHistory->bindParam(':kode_menu', $this->kode_menu);
                $stmtHistory->bindParam(':stok_lama', $stokLama, PDO::PARAM_INT);
                $stmtHistory->bindParam(':stok_baru', $stok, PDO::PARAM_INT);
                $stmtHistory->bindParam(':keterangan', $keterangan);

                if (!$stmtHistory->execute()) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error pada update menu: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error tidak terduga pada update menu: " . $e->getMessage());
            return false;
        }
    }


    // Fungsi untuk menghapus menu berdasarkan kode_menu
    public function delete_menu($kode_menu) {
        try {
            // Memulai transaksi
            $this->conn->beginTransaction();

            // Hapus semua history stok terkait menu ini
            $queryDeleteHistory = "DELETE FROM " . $this->history_table_name . " WHERE kode_menu = :kode_menu";
            $stmtDeleteHistory = $this->conn->prepare($queryDeleteHistory);
            $stmtDeleteHistory->bindParam(':kode_menu', $kode_menu);
            if (!$stmtDeleteHistory->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Hapus menu dari tabel menu
            $query = "DELETE FROM " . $this->table_name . " WHERE kode_menu = :kode_menu";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':kode_menu', $kode_menu);
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            // Commit transaksi jika semua berhasil
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error pada delete menu: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error tidak terduga pada delete menu: " . $e->getMessage());
            return false;
        }
    }
}
