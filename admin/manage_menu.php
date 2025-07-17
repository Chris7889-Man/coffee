<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/menu.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);

// Ambil semua data menu
$stmt = $menu->read();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Menu - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Kelola Menu</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="tambah_menu.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Menu
            </a>
            <a href="dashboard.php" class="btn btn-warning">
                Kembali
            </a>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Menu</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['kode_menu']); ?></td>
                        <td><?= htmlspecialchars($row['nama_menu']); ?></td>
                        <td><?= htmlspecialchars($row['kategori']); ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="edit_menu.php?kode_menu=<?= $row['kode_menu']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                            <a href="delete_menu.php?kode_menu=<?= $row['kode_menu']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
