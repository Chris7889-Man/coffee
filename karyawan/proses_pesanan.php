<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

// Ambil input dari form
$kode_menu       = $_POST['kode_menu'];
$jumlah          = $_POST['jumlah'];
$jenis_pesanan   = $_POST['jenis_pesanan'];
$catatan         = $_POST['catatan'] ?? '';
$nama_pelanggan  = $_SESSION['staff_nama']; // Ambil dari session

// Koneksi DB
$database = new Database();
$db = $database->getConnection();

// Ambil harga menu dari tabel menu
$query = "SELECT harga FROM menu WHERE kode_menu = :kode_menu";
$stmt = $db->prepare($query);
$stmt->bindParam(':kode_menu', $kode_menu);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $harga_satuan = $row['harga'];
    $total_harga = $harga_satuan * $jumlah;

    // Generate kode_pesanan unik
    $kode_pesanan = 'P' . date('YmdHis') . rand(10, 99);

    // Simpan pesanan ke database
    $insert = "INSERT INTO pesanan 
        (kode_pesanan, nama_pelanggan, kode_menu, total_harga, tgl_pesanan, status_pesanan, jenis_pesanan, catatan_pesanan) 
        VALUES 
        (:kode_pesanan, :nama_pelanggan, :kode_menu, :total_harga, NOW(), 'pending', :jenis_pesanan, :catatan)";

    $stmtInsert = $db->prepare($insert);
    $stmtInsert->execute([
        ':kode_pesanan'   => $kode_pesanan,
        ':nama_pelanggan' => $nama_pelanggan,
        ':kode_menu'      => $kode_menu,
        ':total_harga'    => $total_harga,
        ':jenis_pesanan'  => $jenis_pesanan,
        ':catatan'        => $catatan
    ]);

    $_SESSION['pesan_sukses'] = "Pesanan berhasil dikirim!";
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['pesan_error'] = "Menu tidak ditemukan!";
    header("Location: pesan_minuman.php");
    exit();
}
