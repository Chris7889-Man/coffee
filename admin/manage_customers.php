<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan hanya admin yang sudah login bisa mengakses
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pelanggan - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Kelola Pelanggan</h2>
        <div class="alert alert-warning">
            Data pelanggan tidak tersedia.
        </div>
    </div>
</body>
</html>
    <div class="container mt-4">
        <h2>Kelola Pelanggan</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="tambah_costumers.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Costumers
            </a>
            <a href="dashboard.php" class="btn btn-warning">
                Kembali
            </a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pelanggan</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_pelanggan'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['telepon'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
