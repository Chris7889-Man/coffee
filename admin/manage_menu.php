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

$message = '';

// Tangani update stok jika ada submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stok_update'])) {
    date_default_timezone_set('Asia/Makassar');
    $now = date('Y-m-d H:i:s');

    // DEFINISIKAN day dan month DISINI
    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $months = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];

    $day = $days[date('l', strtotime($now))] ?? date('l', strtotime($now));
    $month = $months[date('F', strtotime($now))] ?? date('F', strtotime($now));

    if (isset($_POST['stok']) && is_array($_POST['stok'])) {
        foreach ($_POST['stok'] as $kode_menu => $stok_baru) {
            // ... ambil stok lama dll

            // Buat keterangan memakai $day dan $month
            $keterangan = "Update stok pada hari $day, bulan $month";

            // Insert history stok dengan keterangan tersebut

            // Ambil stok lama dulu
            $queryGet = "SELECT stok FROM menu WHERE kode_menu = :kode_menu";
            $stmtGet = $db->prepare($queryGet);
            $stmtGet->bindParam(':kode_menu', $kode_menu);
            $stmtGet->execute();
            $dataMenu = $stmtGet->fetch(PDO::FETCH_ASSOC);
            $stok_lama = $dataMenu['stok'] ?? 0;

            // Update stok menu
            if ($stok_baru !== $stok_lama) {
                // Simpan history stok hanya jika stok berubah
                $queryLog = "INSERT INTO stok_history (kode_menu, stok_lama, stok_baru, tgl_update, keterangan) 
                VALUES (:kode_menu, :stok_lama, :stok_baru, :tgl_update, :keterangan)";
                $stmtLog = $db->prepare($queryLog);
                $keterangan = "Update stok pada hari $day, bulan $month";
                $stmtLog->bindParam(':kode_menu', $kode_menu);
                $stmtLog->bindParam(':stok_lama', $stok_lama, PDO::PARAM_INT);
                $stmtLog->bindParam(':stok_baru', $stok_baru, PDO::PARAM_INT);
                $stmtLog->bindParam(':tgl_update', $now);
                $stmtLog->bindParam(':keterangan', $keterangan);
                $stmtLog->execute();
            }


            // Simpan history stok
            $queryLog = "INSERT INTO stok_history (kode_menu, stok_lama, stok_baru, tgl_update, keterangan) 
                        VALUES (:kode_menu, :stok_lama, :stok_baru, :tgl_update, :keterangan)";
            $stmtLog = $db->prepare($queryLog);
            $keterangan = "Update stok pada hari $day, bulan $month";
            $stmtLog->bindParam(':kode_menu', $kode_menu);
            $stmtLog->bindParam(':stok_lama', $stok_lama, PDO::PARAM_INT);
            $stmtLog->bindParam(':stok_baru', $stok_baru, PDO::PARAM_INT);
            $stmtLog->bindParam(':tgl_update', $now);
            $stmtLog->bindParam(':keterangan', $keterangan);
            $stmtLog->execute();
        }
        $message = "Stok berhasil diperbarui dan history disimpan.";
    }
}


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

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="stok_update" value="1" />

            <div class="mb-3 d-flex gap-2">
                <a href="tambah_menu.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Menu
                </a>
                <a href="dashboard.php" class="btn btn-warning">
                    Kembali
                </a>
                <a href="history_stok.php" class="btn btn-warning">
                    hystory
                </a>
                <a href="history_stok.php" class="btn btn-warning">History Stok</a>
                <a href="history_stok.php?kode_menu=<?= urlencode($row['kode_menu']) ?>" class="btn btn-info btn-sm">Lihat Histori</a>


                <button type="submit" class="btn btn-success ms-auto">
                    <i class="bi bi-save"></i> Simpan Perubahan Stok
                </button>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode Menu</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th> <!-- Editable stok -->
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
                            <td>
                                <input type="number" name="stok[<?= htmlspecialchars($row['kode_menu']); ?>]"
                                    value="<?= (int) $row['stok'] ?>" min="0" class="form-control" style="max-width: 100px;"
                                    required>
                            </td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td>
                                <a href="edit_menu.php?kode_menu=<?= urlencode($row['kode_menu']); ?>"
                                    class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="delete_menu.php?kode_menu=<?= urlencode($row['kode_menu']); ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>
    </div>
</body>

</html>