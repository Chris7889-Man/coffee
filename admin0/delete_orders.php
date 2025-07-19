<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Validasi parameter
if (!isset($_GET['kode_pesanan']) || empty($_GET['kode_pesanan'])) {
    $_SESSION['message'] = "Kode pesanan tidak valid.";
    header("Location: manage_orders.php");
    exit();
}

$kode_pesanan = $_GET['kode_pesanan'];

// Inisialisasi database
$database = new Database();
$db = $database->getConnection();

// Hapus pesanan dari database
$query = "DELETE FROM pesanan WHERE kode_pesanan = :kode_pesanan";
$stmt = $db->prepare($query);
$stmt->bindParam(':kode_pesanan', $kode_pesanan);

if ($stmt->execute()) {
    $_SESSION['message'] = "Pesanan berhasil dihapus.";
} else {
    $_SESSION['message'] = "Gagal menghapus pesanan.";
}

// Redirect kembali ke halaman utama
header("Location: manage_orders.php");
exit();
?>
