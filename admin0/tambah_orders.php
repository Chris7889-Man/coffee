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

// Ambil semua menu untuk dropdown
$menu_list = $menuObj->read()->fetchAll(PDO::FETCH_ASSOC);

// Fungsi generate kode pesanan otomatis
function generateKodePesanan($db) {
    $query = "SELECT COUNT(*) as total FROM pesanan";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $num = $total + 1;
    return 'PSN' . str_pad($num, 3, '0', STR_PAD_LEFT);
}

$message = '';
$kode_pesanan = generateKodePesanan($db);

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_pesanan = $_POST['kode_pesanan'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $kode_menu = $_POST['kode_menu'];
    $jumlah = (int)$_POST['jumlah'];
    $status_pesanan = $_POST['status_pesanan'] ?? '';
    date_default_timezone_set('Asia/Makassar');
    $tgl_pesanan = date('Y-m-d H:i:s');

    // Ambil harga dan stok sekarang dari menu
    $query = "SELECT harga, stok FROM menu WHERE kode_menu = :kode_menu";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    $harga_satuan = $menu['harga'] ?? 0;
    $stok_saat_ini = $menu['stok'] ?? 0;

    // Cek stok cukup
    if ($jumlah > $stok_saat_ini) {
        $message = "Stok tidak cukup. Stok saat ini: $stok_saat_ini";
    } else {
        $total_harga = $harga_satuan * $jumlah;

        // Simpan pesanan
        $query = "INSERT INTO pesanan (
                    kode_pesanan, nama_pelanggan, kode_menu, jumlah, total_harga,
                    tgl_pesanan, status_pesanan
                    ) VALUES (
                    :kode_pesanan, :nama_pelanggan, :kode_menu, :jumlah, :total_harga,
                    :tgl_pesanan, :status_pesanan
                    )";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':kode_pesanan', $kode_pesanan);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
        $stmt->bindParam(':kode_menu', $kode_menu);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':total_harga', $total_harga);
        $stmt->bindParam(':tgl_pesanan', $tgl_pesanan);
        $stmt->bindParam(':status_pesanan', $status_pesanan);

        if ($stmt->execute()) {
            // Update stok menu
            $stok_baru = $stok_saat_ini - $jumlah;
            $updateStokQuery = "UPDATE menu SET stok = :stok WHERE kode_menu = :kode_menu";
            $updateStmt = $db->prepare($updateStokQuery);
            $updateStmt->bindParam(':stok', $stok_baru);
            $updateStmt->bindParam(':kode_menu', $kode_menu);
            $updateStmt->execute();

            $message = "Pesanan berhasil ditambahkan! Stok menu diupdate menjadi $stok_baru.";
            $kode_pesanan = generateKodePesanan($db);
        } else {
            $message = "Gagal menambahkan pesanan.";
        }
    }
}

?>




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Tambah Pesanan</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Kode Pesanan</label>
            <input type="text" name="kode_pesanan" class="form-control" value="<?= htmlspecialchars($kode_pesanan) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Pelanggan</label>
            <input type="text" name="nama_pelanggan" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Menu</label>
            <select name="kode_menu" class="form-control" required>
                <option disabled selected>- Pilih Menu -</option>
                <?php foreach ($menu_list as $menu): ?>
                    <option value="<?= htmlspecialchars($menu['kode_menu']); ?>">
                        <?= htmlspecialchars($menu['nama_menu']); ?> - Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Status Pesanan</label>
            <select name="status_pesanan" class="form-control" required>
                <option value="Menunggu">Menunggu</option>
                <option value="Dikonfirmasi">Dikonfirmasi</option>
                <option value="Diproses">Diproses</option>
                <option value="Siap">Siap</option>
                <option value="Selesai">Selesai</option>
                <option value="Batal">Batal</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="manage_orders.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>
