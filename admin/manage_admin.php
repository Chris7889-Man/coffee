<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan hanya super admin yang bisa mengakses
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !$_SESSION['is_super_admin']) {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil semua admin
$stmt = $db->prepare("SELECT username, nama_admin, email, is_super_admin FROM admin ORDER BY nama_admin ASC");
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Admin - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Kelola Admin</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nama Admin</th>
                    <th>Email</th>
                    <th>Super Admin</th>
                </tr>
            </thead>
             <div class="mb-3 d-flex gap-2">
            <!-- <a href="tambah_orders.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Orders
            </a> -->
            <a href="dashboard.php" class="btn btn-warning">
                Kembali
            </a>
        </div>
            <tbody>
                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_admin']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['is_super_admin'] ? 'Ya' : 'Tidak'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
