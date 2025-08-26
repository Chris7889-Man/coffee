<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori = $_POST['kategori'] ?? '';
    $nominal = $_POST['nominal'] ?? '';
    $tipe = $_POST['tipe'] ?? '';
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

    // Validasi nominal
    $nominal = str_replace(['.', ','], '', $nominal); // hapus titik/ribuan
    if (!is_numeric($nominal) || $nominal <= 0) {
        $_SESSION['message'] = 'Nominal harus berupa angka lebih dari 0';
        header("Location: laporan_keuangan.php");
        exit();
    }

    if ($tipe === 'kurangi') {
        $nominal = -abs($nominal);
    } else {
        $nominal = abs($nominal);
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO pengeluaran (kategori, nominal, tanggal) VALUES (:kategori, :nominal, :tanggal)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kategori', $kategori);
    $stmt->bindParam(':nominal', $nominal, PDO::PARAM_INT);
    $stmt->bindParam(':tanggal', $tanggal);
    $stmt->execute();

    $_SESSION['message'] = 'Pengeluaran berhasil disimpan';
    header("Location: contoh.php");
    exit();
}
?>
