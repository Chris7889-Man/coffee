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

if (!isset($_GET['kode_pesanan']) || empty($_GET['kode_pesanan'])) {
    header("Location: manage_orders.php");
    exit();
}

$kode_pesanan = $_GET['kode_pesanan'];

$query = "SELECT * FROM pesanan WHERE kode_pesanan = :kode_pesanan";
$stmt = $db->prepare($query);
$stmt->bindParam(':kode_pesanan', $kode_pesanan);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    $message = "Pesanan tidak ditemukan!";
}

// Ambil harga menu terpilih agar bisa hitung default jumlah
$harga_menu_pilih = 0;
foreach ($menu_list as $menu) {
    if ($menu['kode_menu'] === $data['kode_menu']) {
        $harga_menu_pilih = $menu['harga'];
        break;
    }
}

// Hitung jumlah awal yang akan ditampilkan di input jumlah
$jumlah_saat_ini = 1;
if ($harga_menu_pilih > 0) {
    $jumlah_saat_ini = round($data['total_harga'] / $harga_menu_pilih);
    if ($jumlah_saat_ini < 1) {
        $jumlah_saat_ini = 1; // minimal 1
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $kode_menu = $_POST['kode_menu'];
    $jumlah = (int)$_POST['jumlah'];
    $status_pesanan = $_POST['status_pesanan'];

    // Ambil harga satuan menu yang baru
    $stmt_harga_post = $db->prepare("SELECT harga FROM menu WHERE kode_menu = :kode_menu");
    $stmt_harga_post->bindParam(':kode_menu', $kode_menu);
    $stmt_harga_post->execute();
    $menu_post = $stmt_harga_post->fetch(PDO::FETCH_ASSOC);
    $harga_satuan = $menu_post['harga'] ?? 0;

    // Hitung total harga sesuai jumlah dan harga satuan
    $total_harga = $harga_satuan * $jumlah;

    // Pastikan $jumlah minimal 1
    if ($jumlah < 1) {
        $message = "Jumlah pesanan minimal 1.";
    } else {
        $query = "UPDATE pesanan SET 
                    nama_pelanggan = :nama_pelanggan,
                    kode_menu = :kode_menu,
                    jumlah = :jumlah,
                    total_harga = :total_harga,
                    status_pesanan = :status_pesanan
                  WHERE kode_pesanan = :kode_pesanan";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
        $stmt->bindParam(':kode_menu', $kode_menu);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':total_harga', $total_harga);
        $stmt->bindParam(':status_pesanan', $status_pesanan);
        $stmt->bindParam(':kode_pesanan', $kode_pesanan);

        if ($stmt->execute()) {
            $message = "Pesanan berhasil diperbarui!";
            // Ambil ulang data setelah update agar form terisi data terbaru
            $stmt = $db->prepare("SELECT * FROM pesanan WHERE kode_pesanan = :kode_pesanan");
            $stmt->bindParam(':kode_pesanan', $kode_pesanan);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Update harga menu baru dan jumlah awal
            foreach ($menu_list as $menu) {
                if ($menu['kode_menu'] === $data['kode_menu']) {
                    $harga_menu_pilih = $menu['harga'];
                    break;
                }
            }
            $jumlah_saat_ini = $data['jumlah'] ?? 1;
        } else {
            $message = "Gagal memperbarui pesanan!";
        }
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
            <input type="text" name="kode_pesanan" class="form-control" value="<?= htmlspecialchars($data['kode_pesanan']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" class="form-control" value="<?= htmlspecialchars($data['nama_pelanggan']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Menu</label>
            <select name="kode_menu" class="form-control" required>
                <?php foreach ($menu_list as $menu): ?>
                    <option value="<?= htmlspecialchars($menu['kode_menu']); ?>" <?= $menu['kode_menu'] === $data['kode_menu'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($menu['nama_menu']); ?> - Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" value="<?= htmlspecialchars($jumlah_saat_ini) ?>" required>
        </div>
    
        <div class="mb-3">
            <label class="form-label">Status Pesanan</label>
            <select name="status_pesanan" class="form-control" required>
                <option value="Menunggu" <?= $data['status_pesanan'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                <option value="Dikonfirmasi" <?= $data['status_pesanan'] === 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                <option value="Diproses" <?= $data['status_pesanan'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                <option value="Siap" <?= $data['status_pesanan'] === 'Siap' ? 'selected' : '' ?>>Siap</option>
                <option value="Selesai" <?= $data['status_pesanan'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="Batal" <?= $data['status_pesanan'] === 'Batal' ? 'selected' : '' ?>>Batal</option>
            </select>
        </div>
    
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="manage_orders.php" class="btn btn-secondary">Kembali</a>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
