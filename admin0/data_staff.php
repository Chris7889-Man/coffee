<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM staff ORDER BY nama_staff DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Data Staff</h2>
    
<div class="d-flex gap-2 mb-3">
    <a href="tambah_staff.php" class="btn btn-primary">+ Tambah Staff</a>
    <a href="dashboard.php" class="btn btn-warning">Kembali</a>
</div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Staff berhasil ditambahkan.</div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Username</th>
                <th>Email</th>
                <th>No. HP</th>
                <th>Alamat</th>
                <th>Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($staffs as $staff): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($staff['nama_staff']) ?></td>
                <td><?= htmlspecialchars($staff['jabatan']) ?></td>
                <td><?= htmlspecialchars($staff['username']) ?></td>
                <td><?= htmlspecialchars($staff['email']) ?></td>
                <td><?= htmlspecialchars($staff['no_hp']) ?></td>
                <td><?= htmlspecialchars($staff['alamat']) ?></td>
                <td><?= $staff['tanggal_dibuat'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
