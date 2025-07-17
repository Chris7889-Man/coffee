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
$menuObj = new Menu($db);
$menu_list = $menuObj->read()->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$data = null;

// Pastikan ada parameter kode_pesanan
if (!isset($_GET['kode_pesanan']) || empty($_GET['kode_pesanan'])) {
    header("Location: manage_orders.php");
    exit();
}

$kode_pesanan = $_GET['kode_pesanan'];

// Ambil data pesanan berdasarkan kode_pesanan
$query = "SELECT * FROM pesanan WHERE kode_pesanan = :kode_pesanan";
$stmt = $db->prepare($query);
$stmt->bindParam(':kode_pesanan', $kode_pesanan);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    $message = "Pesanan tidak ditemukan!";
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $kode_menu = $_POST['kode_menu'];
    $jumlah = (int)$_POST['jumlah'];
    $jenis_pesanan = $_POST['jenis_pesanan'];
    $status_pesanan = $_POST['status_pesanan'];
    $catatan = $_POST['catatan_pesanan'] ?? '';

    // Ambil harga dari menu
    $query = "SELECT harga FROM menu WHERE kode_menu = :kode_menu";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    $harga_satuan = $menu['harga'] ?? 0;
    $total_harga = $harga_satuan * $jumlah;

    // Update data pesanan
    $query = "UPDATE pesanan SET 
                nama_pelanggan = :nama_pelanggan,
                kode_menu = :kode_menu,
                total_harga = :total_harga,
                status_pesanan = :status_pesanan,
                jenis_pesanan = :jenis_pesanan,
                catatan_pesanan = :catatan_pesanan
              WHERE kode_pesanan = :kode_pesanan";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->bindParam(':total_harga', $total_harga);
    $stmt->bindParam(':status_pesanan', $status_pesanan);
    $stmt->bindParam(':jenis_pesanan', $jenis_pesanan);
    $stmt->bindParam(':catatan_pesanan', $catatan);
    $stmt->bindParam(':kode_pesanan', $kode_pesanan);

    if ($stmt->execute()) {
        $message = "Pesanan berhasil diperbarui!";
        // Ambil ulang data setelah update
        $stmt = $db->prepare("SELECT * FROM pesanan WHERE kode_pesanan = :kode_pesanan");
        $stmt->bindParam(':kode_pesanan', $kode_pesanan);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = "Gagal memperbarui pesanan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Pesanan</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($data): ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Kode Pesanan</label>
            <input type="text" name="kode_pesanan" class="form-control" value="<?= $data['kode_pesanan'] ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" class="form-control" value="<?= $data['nama_pelanggan'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Menu</label>
            <select name="kode_menu" class="form-control" required>
                <?php foreach ($menu_list as $menu): ?>
                    <option value="<?= $menu['kode_menu']; ?>" <?= $menu['kode_menu'] === $data['kode_menu'] ? 'selected' : '' ?>>
                        <?= $menu['nama_menu']; ?> - Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" value="<?= $data['total_harga'] / ($menu['harga'] ?? 1) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jenis Pesanan</label>
            <select name="jenis_pesanan" class="form-control" required>
                <option value="dine in" <?= $data['jenis_pesanan'] === 'dine in' ? 'selected' : '' ?>>dine in</option>
                <option value="take-away" <?= $data['jenis_pesanan'] === 'take-away' ? 'selected' : '' ?>>take-away</option>
                <option value="delivery" <?= $data['jenis_pesanan'] === 'delivery' ? 'selected' : '' ?>>delivery</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Status Pesanan</label>
            <select name="status_pesanan" class="form-control" required>
                <option value="pending" <?= $data['status_pesanan'] === 'pending' ? 'selected' : '' ?>>pending</option>
                <option value="confirmed" <?= $data['status_pesanan'] === 'confirmed' ? 'selected' : '' ?>>confirmed</option>
                <option value="preparing" <?= $data['status_pesanan'] === 'preparing' ? 'selected' : '' ?>>preparing</option>
                <option value="ready" <?= $data['status_pesanan'] === 'ready' ? 'selected' : '' ?>>ready</option>
                <option value="completed" <?= $data['status_pesanan'] === 'completed' ? 'selected' : '' ?>>completed</option>
                <option value="canceled" <?= $data['status_pesanan'] === 'canceled' ? 'selected' : '' ?>>canceled</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea name="catatan_pesanan" class="form-control" rows="2"><?= $data['catatan_pesanan'] ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="manage_orders.php" class="btn btn-secondary">Kembali</a>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
