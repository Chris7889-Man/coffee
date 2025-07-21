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
    $stmt_harga_post = $db->prepare("SELECT harga, stok FROM menu WHERE kode_menu = :kode_menu");
    $stmt_harga_post->bindParam(':kode_menu', $kode_menu);
    $stmt_harga_post->execute();
    $menu_post = $stmt_harga_post->fetch(PDO::FETCH_ASSOC);
    $harga_satuan = $menu_post['harga'] ?? 0;
    $stok_lama_menu = $menu_post['stok'] ?? 0;

    // Hitung total harga sesuai jumlah dan harga satuan
    $total_harga = $harga_satuan * $jumlah;

    // Pastikan $jumlah minimal 1
    if ($jumlah < 1) {
        $message = "Jumlah pesanan minimal 1.";
    } else {
        // Ambil informasi pesanan lama agar dapat mengkalkulasi perubahan stok
        $stmt_old = $db->prepare("SELECT kode_menu, jumlah FROM pesanan WHERE kode_pesanan = :kode_pesanan");
        $stmt_old->bindParam(':kode_pesanan', $kode_pesanan);
        $stmt_old->execute();
        $old_pesanan = $stmt_old->fetch(PDO::FETCH_ASSOC);
        if (!$old_pesanan) {
            $message = "Data pesanan tidak ditemukan.";
        } else {
            $old_kode_menu = $old_pesanan['kode_menu'];
            $old_jumlah = (int)$old_pesanan['jumlah'];

            // Hitung stok baru untuk menu lama dan menu baru
            // Logika update stok: stok = stok + jumlah_pesanan_lama - jumlah_pesanan_baru

            // Jika kode_menu berubah, kita harus mengembalikan stok menu lama dan mengurangi stok menu baru
            if ($old_kode_menu !== $kode_menu) {
                // Ambil stok menu lama
                $stmt_old_menu = $db->prepare("SELECT stok FROM menu WHERE kode_menu = :kode_menu");
                $stmt_old_menu->bindParam(':kode_menu', $old_kode_menu);
                $stmt_old_menu->execute();
                $stok_lama_menu_old = $stmt_old_menu->fetchColumn();

                // Hitung stok baru untuk menu lama dan menu baru
                $stok_baru_menu_lama = $stok_lama_menu_old + $old_jumlah; // kembalikan stok menu lama
                $stok_baru_menu_baru = $stok_lama_menu - $jumlah; // kurangi stok menu baru

                if ($stok_baru_menu_baru < 0) {
                    $message = "Stok menu baru tidak cukup untuk pesanan.";
                } else {
                    try {
                        $db->beginTransaction();

                        // Update pesanan
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
                        $stmt->execute();

                        // Update stok menu lama (dikembalikan)
                        $stmt_update_menu_lama = $db->prepare("UPDATE menu SET stok = :stok WHERE kode_menu = :kode_menu");
                        $stmt_update_menu_lama->execute([':stok' => $stok_baru_menu_lama, ':kode_menu' => $old_kode_menu]);

                        // Update stok menu baru (dikurangi)
                        $stmt_update_menu_baru = $db->prepare("UPDATE menu SET stok = :stok WHERE kode_menu = :kode_menu");
                        $stmt_update_menu_baru->execute([':stok' => $stok_baru_menu_baru, ':kode_menu' => $kode_menu]);

                        $now = date('Y-m-d H:i:s');

                        // Insert history untuk menu lama (stok bertambah karena pesanan lama dibatalkan untuk menu lama)
                        $stmt_history_old = $db->prepare("INSERT INTO stok_history 
                            (kode_menu, stok_lama, stok_baru, tgl_update, keterangan)
                            VALUES (:kode_menu, :stok_lama, :stok_baru, :tgl_update, :keterangan)");
                        $stmt_history_old->execute([
                            ':kode_menu' => $old_kode_menu,
                            ':stok_lama' => $stok_lama_menu_old,
                            ':stok_baru' => $stok_baru_menu_lama,
                            ':tgl_update' => $now,
                            ':keterangan' => "Edit pesanan {$kode_pesanan}: kembalikan stok untuk menu lama"
                        ]);

                        // Insert history untuk menu baru (stok berkurang karena pesanan baru)
                        $stmt_history_new = $db->prepare("INSERT INTO stok_history 
                            (kode_menu, stok_lama, stok_baru, tgl_update, keterangan)
                            VALUES (:kode_menu, :stok_lama, :stok_baru, :tgl_update, :keterangan)");
                        $stmt_history_new->execute([
                            ':kode_menu' => $kode_menu,
                            ':stok_lama' => $stok_lama_menu,
                            ':stok_baru' => $stok_baru_menu_baru,
                            ':tgl_update' => $now,
                            ':keterangan' => "Edit pesanan {$kode_pesanan}: kurangi stok untuk menu baru"
                        ]);

                        $db->commit();
                        $message = "Pesanan berhasil diperbarui!";
                    } catch (Exception $e) {
                        $db->rollBack();
                        $message = "Gagal memperbarui pesanan: " . $e->getMessage();
                    }
                }
            } else {  
                // Jika menu tidak berubah, hitung perubahan stok berdasarkan selisih jumlah

                $stok_baru = $stok_lama_menu + $old_jumlah - $jumlah; // kembalikan stok lama, lalu kurangi stok baru

                if ($stok_baru < 0) {
                    $message = "Stok menu tidak cukup untuk pesanan.";
                } else {
                    try {
                        $db->beginTransaction();

                        // Update pesanan
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
                        $stmt->execute();

                        // Update stok menu
                        $stmt_update_menu = $db->prepare("UPDATE menu SET stok = :stok WHERE kode_menu = :kode_menu");
                        $stmt_update_menu->execute([':stok' => $stok_baru, ':kode_menu' => $kode_menu]);

                        $now = date('Y-m-d H:i:s');

                        // Insert ke stok_history
                        $stmt_history = $db->prepare("INSERT INTO stok_history 
                            (kode_menu, stok_lama, stok_baru, tgl_update, keterangan)
                            VALUES (:kode_menu, :stok_lama, :stok_baru, :tgl_update, :keterangan)");
                        $stmt_history->execute([
                            ':kode_menu' => $kode_menu,
                            ':stok_lama' => $stok_lama_menu,
                            ':stok_baru' => $stok_baru,
                            ':tgl_update' => $now,
                            ':keterangan' => "Edit pesanan {$kode_pesanan}"
                        ]);

                        $db->commit();
                        $message = "Pesanan berhasil diperbarui!";
                    } catch (Exception $e) {
                        $db->rollBack();
                        $message = "Gagal memperbarui pesanan: " . $e->getMessage();
                    }
                }
            }

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
                <option value="Menunggu" <?= $data['status_pesanan'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                <option value="Dikonfirmasi" <?= $data['status_pesanan'] === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                <option value="Diproses" <?= $data['status_pesanan'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                <option value="Siap" <?= $data['status_pesanan'] === 'siap' ? 'selected' : '' ?>>Siap</option>
                <option value="Selesai" <?= $data['status_pesanan'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="Batal" <?= $data['status_pesanan'] === 'batal' ? 'selected' : '' ?>>Batal</option>
            </select>
        </div>
    
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="manage_orders.php" class="btn btn-secondary">Kembali</a>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
