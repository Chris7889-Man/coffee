<?php
session_start();
// Ganti timezone dari 'Asia/Makassar' (WITA) ke 'Asia/Jayapura' (WIT)
date_default_timezone_set('Asia/Jayapura'); // Set timezone ke WIT

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Pesanan.php';

// Pastikan hanya admin yang sudah login bisa mengakses
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$pesanan = new Pesanan($db);

// Ambil semua pesanan
$stmt = $pesanan->read();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Kelola Pesanan - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4">
        <h2>Kelola Pesanan</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="tambah_orders.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Orders
            </a>
            <a href="dashboard.php" class="btn btn-warning">Kembali</a>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Nama Pelanggan</th>
                    <th>Jumlah Pesanan</th>
                    <th>Total Harga</th>
                    <th>Tanggal & Jam</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['kode_pesanan']); ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['jumlah'] ?? ''); ?></td>
                    <td>Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_pesanan'])); ?></td>
                    <td><?= htmlspecialchars($row['status_pesanan'] ?? ''); ?></td>
                    <td>
                        <a href="edit_orders.php?kode_pesanan=<?= urlencode($row['kode_pesanan']); ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="delete_orders.php?kode_pesanan=<?= urlencode($row['kode_pesanan']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?');">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
