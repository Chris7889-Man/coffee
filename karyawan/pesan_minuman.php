<?php
session_start();

// Tampilkan error untuk debugging (hapus di production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../admin/menu.php';

// Cek session login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$menuObj = new Menu($db);

// Ambil daftar menu
function getMenuList($menuObj) {
    try {
        $stmt = $menuObj->read();
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) {
        die("Error mengambil data menu: " . $e->getMessage());
    }
}

// Fungsi generate kode pesanan otomatis
function generateKodePesanan($db) {
    $query = "SELECT kode_pesanan FROM pesanan ORDER BY kode_pesanan DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($last && preg_match('/^NPS(\d{4})$/', $last['kode_pesanan'], $matches)) {
        $num = (int)$matches[1];
        return 'NPS' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    } else {
        return 'NPS0001';
    }
}

// Simpan pesanan ke database
function simpanPesanan($db, $data) {
    // Ambil harga dari menu
        $query = "SELECT harga FROM menu WHERE kode_menu = :kode_menu LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $data['kode_menu']);
    $stmt->execute();
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
        return ['success' => false, 'message' => 'Menu tidak ditemukan.'];
    }

    $harga_satuan = $menu['harga'];
    $total_harga = $harga_satuan * $data['jumlah'];

    // --- BAGIAN INI YANG DIUBAH ---
    // Mengambil tanggal dan jam saat ini dari server dalam format YYYY-MM-DD HH:MM:SS
    $tgl_pesanan = date('Y-m-d H:i:s'); 
    // ----------------------------

    $insertQuery = "INSERT INTO pesanan (kode_pesanan, nama_pelanggan, kode_menu, jumlah, total_harga, tgl_pesanan)
                    VALUES (:kode_pesanan, :nama_pelanggan, :kode_menu, :jumlah, :total_harga, :tgl_pesanan)";
    $insertStmt = $db->prepare($insertQuery);

    $insertStmt->bindParam(':kode_pesanan', $data['kode_pesanan']);
    $insertStmt->bindParam(':nama_pelanggan', $data['nama_pelanggan']);
    $insertStmt->bindParam(':kode_menu', $data['kode_menu']);
    $insertStmt->bindParam(':jumlah', $data['jumlah'], PDO::PARAM_INT); // Gunakan PDO::PARAM_INT untuk integer
    $insertStmt->bindParam(':total_harga', $total_harga);
    $insertStmt->bindParam(':tgl_pesanan', $tgl_pesanan);

    try {
        if ($insertStmt->execute()) {
            return ['success' => true, 'message' => 'Pesanan berhasil ditambahkan!'];
        } else {
            // Log error PDO jika perlu untuk debugging
            // error_log("PDO Error: " . implode(" - ", $insertStmt->errorInfo()));
            return ['success' => false, 'message' => 'Gagal menambahkan pesanan. Silakan coba lagi.'];
        }
    } catch (PDOException $e) {
        // Tangkap exception PDO untuk error yang lebih spesifik
        // error_log("PDO Exception: " . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan database: ' . $e->getMessage()];
    }
}

// Ambil menu untuk dropdown
$menu_list = getMenuList($menuObj);

// Siapkan kode pesanan default
$kode_pesanan = generateKodePesanan($db);

$message = '';
$message_type = 'info';

// Proses simpan data jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'kode_pesanan' => $_POST['kode_pesanan'] ?? '',
        'nama_pelanggan' => trim($_POST['nama_pelanggan'] ?? ''),
        'kode_menu' => $_POST['kode_menu'] ?? '',
        'jumlah' => (int)($_POST['jumlah'] ?? 0)
    ];

    // Validasi input sederhana
    if (!$input['kode_pesanan'] || !$input['nama_pelanggan'] || !$input['kode_menu'] || $input['jumlah'] <= 0) {
        $message = "Mohon lengkapi semua data dengan benar.";
        $message_type = 'danger';
    } else {
        $result = simpanPesanan($db, $input);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';

        if ($result['success']) {
            $kode_pesanan = generateKodePesanan($db); // reset kode pesanan baru
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h2>Tambah Pesanan</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type); ?>" role="alert">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label for="kode_pesanan" class="form-label">Kode Pesanan</label>
            <input type="text" id="kode_pesanan" name="kode_pesanan" class="form-control" value="<?= htmlspecialchars($kode_pesanan); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="nama_pelanggan" class="form-label">Nama Pelanggan</label>
            <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="form-control" required value="<?= isset($_POST['nama_pelanggan']) ? htmlspecialchars($_POST['nama_pelanggan']) : ''; ?>">
        </div>

        <div class="mb-3">
            <label for="kode_menu" class="form-label">Menu</label>
            <select id="kode_menu" name="kode_menu" class="form-select" required>
                <option value="" disabled <?= empty($_POST['kode_menu']) ? 'selected' : ''; ?>>- Pilih Menu -</option>
                <?php foreach ($menu_list as $menu): ?>
                    <option value="<?= htmlspecialchars($menu['kode_menu']); ?>" <?= (isset($_POST['kode_menu']) && $_POST['kode_menu'] === $menu['kode_menu']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($menu['nama_menu']); ?> - Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah</label>
            <input type="number" id="jumlah" name="jumlah" class="form-control" min="1" value="<?= isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1; ?>" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
