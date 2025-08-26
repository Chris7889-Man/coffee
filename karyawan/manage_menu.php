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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stok_update'])) {
    // Ambil data dari form
    $kode_menu = $_POST['kode_menu'] ?? '';
    $stok_baru = isset($_POST['stok']) ? (int)$_POST['stok'] : 0;

    if (!$kode_menu) {
        $message = "Kode menu tidak valid.";
    } else {
        // Ambil stok lama saat ini
        $dataMenu = $menu->getByKode($kode_menu);
        $stok_lama = $dataMenu ? (int)$dataMenu['stok'] : 0;

        // Set properti menu
        $menu->kode_menu = $kode_menu;
        $menu->stok = $stok_baru;
        // Agar update stok penuh dilakukan, isi properti lain dg data lama supaya update tidak overwrite nilai lain
        $menu->nama_menu = $dataMenu['nama_menu'] ?? '';
        $menu->kategori = $dataMenu['kategori'] ?? '';
        $menu->harga = $dataMenu['harga'] ?? 0;
        $menu->status = $dataMenu['status'] ?? 'Tersedia';
        $menu->gambar = $dataMenu['gambar'] ?? '';

        

        if ($stok_baru === $stok_lama) {
            $message = "Stok tidak berubah untuk kode menu $kode_menu.";
        } else {
            // Panggil update yang sudah otomatis update stok sekaligus simpan history stok
            if ($menu->update()) {
                $message = "Stok berhasil diperbarui untuk kode menu $kode_menu.";
            } else {
                $message = "Gagal memperbarui stok untuk kode menu $kode_menu.";
            }
        }
    }
}

// Ambil seluruh data menu
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

        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="mb-3 d-flex gap-2">
              <a href="dashboard.php" class="btn btn-warning">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="tambah_menu.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Menu
            </a>
          
            <a href="history_stok.php" class="btn btn-info btn-sm">Lihat Histori</a>
            <a href="view_menu.php" class="btn btn-info btn-sm">Menu Staff</a>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Menu</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th> <!-- Editable stok dengan form per baris -->
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
                            <form method="POST" action="" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="stok_update" value="1" />
                                <input type="hidden" name="kode_menu" value="<?= htmlspecialchars($row['kode_menu']); ?>" />
                                <input type="number" name="stok" value="<?= (int) $row['stok'] ?>" min="0"
                                    class="form-control" style="max-width: 100px;" required>
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                            </form>
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
    </div>
</body>

</html>
