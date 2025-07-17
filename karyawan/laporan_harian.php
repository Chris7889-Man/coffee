<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Proteksi akses staff
if(!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil data pesanan hari ini
$tgl_hari_ini = date('Y-m-d');
$query = "SELECT * FROM pesanan WHERE DATE(tgl_pesanan) = :tgl ORDER BY tgl_pesanan DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':tgl', $tgl_hari_ini);
$stmt->execute();
$pesanan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total pesanan dan total pendapatan
$total_pesanan = count($pesanan_list);
$total_pendapatan = 0;
foreach ($pesanan_list as $row) {
    $total_pendapatan += $row['total_harga'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian - Coffee Shop Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Laporan Harian</h2>
    <br>
    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    <hr>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Pesanan Hari Ini</div>
                <div class="card-body">
                    <h3 class="card-title"><?= $total_pesanan ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Pendapatan Hari Ini</div>
                <div class="card-body">
                    <h3 class="card-title">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <h4>Daftar Pesanan Hari Ini</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Menu</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pesanan_list as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td><?= htmlspecialchars($row['kode_menu']) ?></td>
                    <td><?= htmlspecialchars($row['jumlah'] ) ?></td>
                    <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
            
                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_pesanan'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($pesanan_list) == 0): ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada pesanan hari ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
