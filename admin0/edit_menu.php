<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/menu.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);

$message = '';
$data_menu = null;

if (!isset($_GET['kode_menu']) || empty($_GET['kode_menu'])) {
    header("Location: manage_menu.php");
    exit();
}

$kode_menu = $_GET['kode_menu'];

// Menggunakan method baru getByKode yang menerima parameter
$data_menu = $menu->getByKode($kode_menu);

if (!$data_menu) {
    $message = "Data tidak ditemukan.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu->kode_menu = $_POST['kode_menu'];
    $menu->nama_menu = $_POST['nama_menu'];
    $menu->kategori = $_POST['kategori'];
    $menu->harga = $_POST['harga'];
    $menu->status = $_POST['status'];
    $menu->stok = isset($_POST['stok']) ? (int)$_POST['stok'] : 0;

    if ($menu->update()) {
        $message = "Menu berhasil diperbarui!";
        $data_menu = $menu->getByKode($kode_menu);
    } else {
        $message = "Gagal memperbarui menu!";
    }
}
?>
<!-- ... lalu lanjut dengan HTML form seperti sebelumnya ... -->


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Menu</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($data_menu): ?>
        <form method="POST">
            <div class="mb-3">
                <label for="kode_menu" class="form-label">Kode Menu</label>
                <input type="text" class="form-control" name="kode_menu" value="<?= htmlspecialchars($data_menu['kode_menu']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="nama_menu" class="form-label">Nama Menu</label>
                <input type="text" class="form-control" name="nama_menu" value="<?= htmlspecialchars($data_menu['nama_menu']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="Coffe" <?= $data_menu['kategori'] == 'Coffe' ? 'selected' : '' ?>>Coffe</option>
                    <option value="Non Coffe" <?= $data_menu['kategori'] == 'Non Coffe' ? 'selected' : '' ?>>Non Coffe</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="harga" class="form-label">Harga</label>
                <input type="number" class="form-control" name="harga" value="<?= htmlspecialchars($data_menu['harga']); ?>" required>
            </div>
            <div class="mb-3">
    <label for="stok" class="form-label">Stok</label>
    <input type="number" name="stok" id="stok" class="form-control" min="0" required value="<?= isset($data_menu['stok']) ? (int)$data_menu['stok'] : 0; ?>">

</div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="available" <?= $data_menu['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="unavailable" <?= $data_menu['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="manage_menu.php" class="btn btn-secondary">Kembali</a>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
