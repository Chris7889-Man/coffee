<?php
session_start();
require_once '../config/database.php';

// Validasi akses staff
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['pesan_error'] = "Metode akses tidak valid!";
    header("Location: dashboard.php");
    exit();
}

// Validasi input yang diperlukan
$required_fields = ['kode_menu', 'jumlah', 'jenis_pesanan'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $_SESSION['pesan_error'] = "Data yang diperlukan tidak lengkap!";
        header("Location: pesan_minuman.php");
        exit();
    }
}

// Ambil dan validasi input dari form
$kode_menu = trim($_POST['kode_menu']);
$jumlah = (int)$_POST['jumlah'];
$jenis_pesanan = trim($_POST['jenis_pesanan']);
$catatan = trim($_POST['catatan'] ?? '');
$nama_pelanggan = $_SESSION['staff_nama'] ?? 'Staff'; // Ambil dari session

// Validasi nilai input
if ($jumlah <= 0) {
    $_SESSION['pesan_error'] = "Jumlah pesanan tidak valid!";
    header("Location: pesan_minuman.php");
    exit();
}

if (!in_array($jenis_pesanan, ['dine_in', 'takeaway'])) {
    $_SESSION['pesan_error'] = "Jenis pesanan tidak valid!";
    header("Location: pesan_minuman.php");
    exit();
}

try {
    // Koneksi DB
    $database = new Database();
    $db = $database->getConnection();
    
    // Begin transaction
    $db->beginTransaction();

    // Ambil harga menu dari tabel menu
    $query = "SELECT harga, nama_menu FROM menu WHERE kode_menu = :kode_menu";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
        throw new Exception("Menu tidak ditemukan!");
    }

    $harga_satuan = $menu['harga'];
    $total_harga = $harga_satuan * $jumlah;

    // Generate kode_pesanan yang lebih robust
    function generateKodePesanan($db) {
        $query = "SELECT kode_pesanan FROM pesanan ORDER BY kode_pesanan DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $last = $stmt->fetch(PDO::FETCH_ASSOC)['kode_pesanan'];
            // Ambil nomor dari kode pesanan terakhir
            if (preg_match('/NPS(\d+)/', $last, $matches)) {
                $num = (int)$matches[1];
                return 'NPS' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
            }
        }
        return 'NPS0001';
    }

    $kode_pesanan = generateKodePesanan($db);

    // Simpan pesanan ke database
    $insert = "INSERT INTO pesanan 
        (kode_pesanan, nama_pelanggan, kode_menu, jumlah, total_harga, tgl_pesanan, status_pesanan, jenis_pesanan, catatan_pesanan) 
        VALUES 
        (:kode_pesanan, :nama_pelanggan, :kode_menu, :jumlah, :total_harga, NOW(), 'pending', :jenis_pesanan, :catatan)";

    $stmtInsert = $db->prepare($insert);
    $result = $stmtInsert->execute([
        ':kode_pesanan'   => $kode_pesanan,
        ':nama_pelanggan' => $nama_pelanggan,
        ':kode_menu'      => $kode_menu,
        ':jumlah'         => $jumlah,
        ':total_harga'    => $total_harga,
        ':jenis_pesanan'  => $jenis_pesanan,
        ':catatan'        => $catatan
    ]);

    if ($result) {
        // Commit transaction
        $db->commit();
        
        $_SESSION['pesan_sukses'] = "Pesanan berhasil dikirim! Kode: " . $kode_pesanan . " - " . $menu['nama_menu'] . " x" . $jumlah;
        header("Location: dashboard.php");
        exit();
    } else {
        throw new Exception("Gagal menyimpan pesanan ke database!");
    }

} catch (Exception $e) {
    // Rollback transaction jika terjadi error
    if (isset($db)) {
        $db->rollback();
    }
    
    $_SESSION['pesan_error'] = "Error: " . $e->getMessage();
    header("Location: pesan_minuman.php");
    exit();
}
?>