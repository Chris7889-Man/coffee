<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../admin/menu.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$menuObj = new Menu($db);

// Ambil semua menu untuk dropdown
$menu_list = $menuObj->read()->fetchAll(PDO::FETCH_ASSOC);

// Generate kode_pesanan otomatis
function generateKodePesanan($db) {
    $query = "SELECT kode_pesanan FROM pesanan ORDER BY kode_pesanan DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $last = $stmt->fetch(PDO::FETCH_ASSOC)['kode_pesanan'];
        $num = (int)substr($last, 3);
        return 'NPS' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        return 'NPS0001';
    }
}   

$message = '';
$kode_pesanan = generateKodePesanan($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_pesanan = $_POST['kode_pesanan'];
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $kode_menu = $_POST['kode_menu'];
    $jumlah = (int)$_POST['jumlah'];
    $tgl_pesanan = date('Y-m-d');

    // Ambil harga dari menu
    $query = "SELECT harga FROM menu WHERE kode_menu = :kode_menu";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    $harga_satuan = $menu['harga'] ?? 0;
    $total_harga = $harga_satuan * $jumlah;

    // Simpan pesanan
    $query = "INSERT INTO pesanan (
    kode_pesanan, nama_pelanggan, kode_menu, jumlah, total_harga, tgl_pesanan
) VALUES (
    :kode_pesanan, :nama_pelanggan, :kode_menu, :jumlah, :total_harga, :tgl_pesanan
)";
$stmt = $db->prepare($query);
$stmt->bindParam(':kode_pesanan', $kode_pesanan);
$stmt->bindParam(':nama_pelanggan', $nama_pelanggan);
$stmt->bindParam(':kode_menu', $kode_menu);
$stmt->bindParam(':jumlah', $jumlah, PDO::PARAM_INT);
$stmt->bindParam(':total_harga', $total_harga);
$stmt->bindParam(':tgl_pesanan', $tgl_pesanan);

$stmt->execute();


    if ($stmt->execute()) {
        $message = "Pesanan berhasil ditambahkan!";
        $kode_pesanan = generateKodePesanan($db); // refresh kode
    } else {
        $message = "Gagal menambahkan pesanan.";
    }
}
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // ... proses simpan data ...

//     if ($stmt->execute()) {
//         header("Location: dashboard.php");
//         exit();
//     } else {
//         $message = "Gagal menambahkan pesanan.";
//     }
// }

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
            <input type="text" name="kode_pesanan" class="form-control" value="<?= $kode_pesanan ?>" readonly>
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
                    <option value="<?= $menu['kode_menu']; ?>">
                        <?= $menu['nama_menu']; ?> - Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>