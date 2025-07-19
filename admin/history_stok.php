<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$kode_menu = $_GET['kode_menu'] ?? '';

// Fungsi format tanggal (tanggal bulan tahun, tanpa jam)
function formatTanggalOnly($datetime) {
    $tanggal = strtotime($datetime);
    $bulan_array = [
        1 => 'Januari','Februari','Maret','April','Mei','Juni','Juli',
        'Agustus','September','Oktober','November','Desember'
    ];
    $tgl = date('d', $tanggal);
    $bulan = $bulan_array[(int)date('m', $tanggal)] ?? '';
    $tahun = date('Y', $tanggal);
    return "$tgl $bulan $tahun";
}

// Fungsi format jam (jam:menit:detik)
function formatJamOnly($datetime) {
    return date('H:i:s', strtotime($datetime));
}

$message = '';
$error = '';

// Tangani aksi reset history (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_history') {
    $password = $_POST['password'] ?? '';
    $tgl_mulai = $_POST['tgl_mulai'] ?? '';
    $tgl_akhir = $_POST['tgl_akhir'] ?? '';

    // Ganti password di bawah ini sesuai yang diinginkan (harus rahasia!)
    $password_benar = 'admin123';

    if ($password !== $password_benar) {
        $error = "Password reset salah!";
    } elseif (!$tgl_mulai || !$tgl_akhir) {
        $error = "Tanggal mulai dan tanggal akhir harus diisi!";
    } else {
        // Validasi format tanggal dan bentuk datetime untuk DB
        $tgl_mulai_db = date('Y-m-d 00:00:00', strtotime($tgl_mulai));
        $tgl_akhir_db = date('Y-m-d 23:59:59', strtotime($tgl_akhir));

        if ($tgl_mulai_db > $tgl_akhir_db) {
            $error = "Tanggal mulai harus sebelum atau sama dengan tanggal akhir.";
        } else {
            try {
                if ($kode_menu) {
                    $queryDel = "DELETE FROM stok_history WHERE kode_menu = :kode_menu AND tgl_update BETWEEN :tgl_mulai AND :tgl_akhir";
                    $stmtDel = $db->prepare($queryDel);
                    $stmtDel->bindParam(':kode_menu', $kode_menu);
                    $stmtDel->bindParam(':tgl_mulai', $tgl_mulai_db);
                    $stmtDel->bindParam(':tgl_akhir', $tgl_akhir_db);
                } else {
                    $queryDel = "DELETE FROM stok_history WHERE tgl_update BETWEEN :tgl_mulai AND :tgl_akhir";
                    $stmtDel = $db->prepare($queryDel);
                    $stmtDel->bindParam(':tgl_mulai', $tgl_mulai_db);
                    $stmtDel->bindParam(':tgl_akhir', $tgl_akhir_db);
                }
                $stmtDel->execute();
                $message = "Data history berhasil dihapus untuk rentang tanggal yang dipilih.";
            } catch (Exception $e) {
                $error = "Gagal menghapus data: " . $e->getMessage();
            }
        }
    }
}

// Ambil data history stok
if ($kode_menu) {
    $query = "SELECT * FROM stok_history WHERE kode_menu = :kode_menu ORDER BY tgl_update DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
} else {
    $query = "SELECT * FROM stok_history ORDER BY tgl_update DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
}
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Perubahan Stok<?= $kode_menu ? " - Menu: " . htmlspecialchars($kode_menu) : "" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Responsive table scroll jika perlu */
        .table-responsive { overflow-x:auto; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>Riwayat Perubahan Stok<?= $kode_menu ? " Menu: " . htmlspecialchars($kode_menu) : "" ?></h2>
    <a href="manage_menu.php" class="btn btn-warning mb-3">Kembali ke Kelola Menu</a>

    <?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode Menu</th>
                    <th>Stok Lama</th>
                    <th>Stok Baru</th>
                    <th>Tanggal Update</th>
                    <th>Jam Update</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($history)): ?>
                <tr>
                    <td colspan="7" class="text-center">Belum ada riwayat stok.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($history as $idx => $row): ?>
                <tr>
                    <td><?= $idx + 1 ?></td>
                    <td><?= htmlspecialchars($row['kode_menu']) ?></td>
                    <td><?= (int)$row['stok_lama'] ?></td>
                    <td><?= (int)$row['stok_baru'] ?></td>
                    <td><?= formatTanggalOnly($row['tgl_update']) ?></td>
                    <td><?= formatJamOnly($row['tgl_update']) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <hr>

    <h4>Reset Riwayat Perubahan Stok</h4>
    <form method="POST" class="row g-3 mb-5">
        <input type="hidden" name="action" value="reset_history" />
        <div class="col-md-3">
            <label for="tgl_mulai" class="form-label">Tanggal Mulai</label>
            <input type="date" id="tgl_mulai" name="tgl_mulai" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label for="tgl_akhir" class="form-label">Tanggal Akhir</label>
            <input type="date" id="tgl_akhir" name="tgl_akhir" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label for="password" class="form-label">Password Reset</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan password untuk reset">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Apakah Anda yakin ingin menghapus riwayat stok sesuai rentang tanggal? Proses ini tidak bisa dibatalkan!')">Reset Riwayat</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
                    