<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$kode_pesanan = isset($_GET['kode_pesanan']) ? $_GET['kode_pesanan'] : (isset($_GET['id']) ? $_GET['id'] : '');

if (empty($kode_pesanan)) {
    echo "<p>Kode pesanan tidak ditemukan.</p>";
    exit();
}

// Query hanya mengambil status_pesanan
$query = "SELECT status_pesanan FROM pesanan WHERE kode_pesanan = :kode_pesanan";
$stmt = $db->prepare($query);
$stmt->bindParam(':kode_pesanan', $kode_pesanan);

try {
    $stmt->execute();
} catch (PDOException $e) {
    echo "<p>Terjadi kesalahan dalam pengambilan data: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data || !isset($data['status_pesanan'])) {
    echo "<p>Status pesanan tidak ditemukan.</p>";
    exit();
}

// Tampilkan status pesanan
echo "<h2>Status Pesanan</h2>";
echo "<p>Status: " . htmlspecialchars($data['status_pesanan']) . "</p>";
?>



<!DOCTYPE html>
<html>
<head>
    <title>Detail Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Detail Pesanan</h2>

    <table class="table table-bordered">
        <tr>
            <th>Kode Pesanan</th>
            <td><?= htmlspecialchars($data['kode_pesanan']) ?></td>
        </tr>
        <tr>
            <th>Nama Pelanggan</th>
            <td><?= htmlspecialchars($data['nama_pelanggan']) ?></td>
        </tr>
        <tr>
            <th>Kode Menu</th>
            <td><?= htmlspecialchars($data['kode_menu']) ?></td>
        </tr>
        <tr>
            <th>Total Harga</th>
            <td>Rp<?= number_format($data['total_harga'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <th>Tanggal Pesanan</th>
            <td><?= htmlspecialchars($data['tgl_pesanan']) ?></td>
        </tr>
        <tr>
            <th>Status Pesanan</th>
            <td><?= htmlspecialchars($data['status_pesanan']) ?></td>
        </tr>
        <tr>
            <th>Jenis Pesanan</th>
            <td><?= htmlspecialchars($data['jenis_pesanan']) ?></td>
        </tr>
        <tr>
            <th>Catatan</th>
            <td><?= htmlspecialchars($data['catatan_pesanan']) ?></td>
        </tr>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Kembali ke Daftar Pesanan</a>
</body>
</html>
