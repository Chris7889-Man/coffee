<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/menu.php';

// Pastikan admin sudah login
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

// Ambil data menu berdasarkan kode_menu
$data_menu = $menu->getByKode($kode_menu);

if (!$data_menu) {
    $message = "Data tidak ditemukan.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_menu = $_POST['nama_menu'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga'];
    $status = $_POST['status'];
    $stok_baru = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    // Ambil stok lama dari data menu saat ini
    $stok_lama = isset($data_menu['stok']) ? (int) $data_menu['stok'] : 0;

    // Set properti menu
    $menu->kode_menu = $kode_menu;
    $menu->nama_menu = $nama_menu;
    $menu->kategori = $kategori;
    $menu->harga = $harga;
    $menu->status = $status;
    $menu->stok = $stok_baru;

    // Update menu
   // Setelah update menu
if ($menu->update()) {
    // Ambil stok lama dan baru untuk membandingkan
    if ($stok_baru !== $stok_lama) {
        date_default_timezone_set('Asia/Makassar');
        $now = date('Y-m-d H:i:s');

        // Keterangan yang lebih dinamis, bisa Anda ubah formatnya seperti di bawah
        if ($stok_baru > $stok_lama) {
            $keterangan = "Penambahan stok";
        } else if ($stok_baru < $stok_lama) {
            $keterangan = "Pengurangan stok";
        } else {
            $keterangan = "Tidak ada perubahan stok";
        }

        // Jika Anda ingin tetap menuliskan hari/bulan, silakan masukkan di keterangan ini:
        $days = [
            'Sunday' => 'Minggu','Monday' => 'Senin','Tuesday' => 'Selasa','Wednesday' => 'Rabu','Thursday' => 'Kamis',
            'Friday' => 'Jumat','Saturday' => 'Sabtu'
        ];
        $months = [
            'January' => 'Januari','February' => 'Februari','March' => 'Maret','April' => 'April','May' => 'Mei',
            'June' => 'Juni','July' => 'Juli','August' => 'Agustus','September' => 'September','October' => 'Oktober',
            'November' => 'November','December' => 'Desember'
        ];

        $day = $days[date('l', strtotime($now))] ?? date('l', strtotime($now));
        $month = $months[date('F', strtotime($now))] ?? date('F', strtotime($now));

        // Gabungkan jika ingin
        $keterangan .= " (Update stok pada $day, bulan $month)";

        $queryLog = "INSERT INTO stok_history 
            (kode_menu, stok_lama, stok_baru, tgl_update, keterangan)
            VALUES (:kode_menu, :stok_lama, :stok_baru, :tgl_update, :keterangan)";
        $stmtLog = $db->prepare($queryLog);
        $stmtLog->bindParam(':kode_menu', $kode_menu);
        $stmtLog->bindParam(':stok_lama', $stok_lama, PDO::PARAM_INT);
        $stmtLog->bindParam(':stok_baru', $stok_baru, PDO::PARAM_INT);
        $stmtLog->bindParam(':tgl_update', $now);
        $stmtLog->bindParam(':keterangan', $keterangan);
        $stmtLog->execute();
    }

    $message = "Menu berhasil diperbarui!";
    // Refresh data menu untuk tampilkan data terbaru
    $data_menu = $menu->getByKode($kode_menu);

} else {
    $message = "Gagal memperbarui menu!";
}
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Edit Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h2>Edit Menu</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($data_menu): ?>
            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="kode_menu" class="form-label">Kode Menu</label>
                    <input type="text" class="form-control" name="kode_menu" id="kode_menu"
                        value="<?= htmlspecialchars($data_menu['kode_menu']); ?>" readonly />
                </div>

                <div class="mb-3">
                    <label for="nama_menu" class="form-label">Nama Menu</label>
                    <input type="text" class="form-control" name="nama_menu" id="nama_menu"
                        value="<?= htmlspecialchars($data_menu['nama_menu']); ?>" required />
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select name="kategori" id="kategori" class="form-control" required>
                        <option value="Coffe" <?= $data_menu['kategori'] == 'Coffe' ? 'selected' : '' ?>>Coffe</option>
                        <option value="Non Coffe" <?= $data_menu['kategori'] == 'Non Coffe' ? 'selected' : '' ?>>Non Coffe
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="harga" class="form-label">Harga</label>
                    <input type="number" class="form-control" name="harga" id="harga"
                        value="<?= htmlspecialchars($data_menu['harga']); ?>" min="0" required />
                </div>

                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="number" name="stok" id="stok" class="form-control" min="0" required
                        value="<?= isset($data_menu['stok']) ? (int) $data_menu['stok'] : 0; ?>" />
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="Tersedia" <?= $data_menu['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia
                        </option>
                        <option value="Tidak Tersedia" <?= $data_menu['status'] == 'Tidak Tersedia' ? 'selected' : '' ?>>
                            Tidak Tersedia</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="manage_menu.php" class="btn btn-secondary ms-2">Kembali</a>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>