<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil kode_pesanan dari URL
$kode_pesanan = isset($_GET['kode_pesanan']) ? $_GET['kode_pesanan'] : '';

if (empty($kode_pesanan)) {
    echo "<p>Kode pesanan tidak ditemukan.</p>";
    exit();
}

// Query ambil detail pesanan dan nama menu
$query = "SELECT od.kode_pesanan, od.kode_menu, m.nama_menu, od.jumlah, od.harga_satuan, od.subtotal, od.catatan
        FROM orders_detail od JOIN menu m ON od.kode_menu = m.kode_menuWHERE od.kode_pesanan = :kode_pesanan";

$stmt = $db->prepare($query);
$stmt->bindParam(':kode_pesanan', $kode_pesanan);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Detail Pesanan: <?= htmlspecialchars($kode_pesanan) ?></h2>

    <?php if (count($rows) > 0): ?>
    <table class="table table-bordered table-hover mt-3">
        <thead class="table-dark">
            <tr>
                <th>Kode Menu</th>
                <th>Nama Menu</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['kode_menu']) ?></td>
                <td><?= htmlspecialchars($row['nama_menu']) ?></td>
                <td><?= $row['jumlah'] ?></td>
                <td>Rp<?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                <td>Rp<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['catatan']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="alert alert-warning">Tidak ada data untuk kode pesanan ini.</p>
    <?php endif; ?>

    <a href="orders.php" class="btn btn-secondary">Kembali</a>
</body>
</html>
