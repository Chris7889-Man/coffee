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
// Generate kode menu otomatis hanya untuk method GET (awal form)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $lastKode = $menu->getLastKodeMenu();
    if ($lastKode) {
        $angka = (int) substr($lastKode, 2); // Ambil angka setelah prefix misal "KM"
        $angkaBaru = $angka + 1;
        $kode_menu_terbaru = 'KM' . str_pad($angkaBaru, 3, '0', STR_PAD_LEFT);
    } else {
        $kode_menu_terbaru = 'KM001';
    }
}



// gambar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu->kode_menu = $_POST['kode_menu'] ?? '';
    $menu->nama_menu = $_POST['nama_menu'] ?? '';
    $menu->kategori = isset($_POST['kategori']) ? substr($_POST['kategori'], 0, 20) : '';
    $menu->harga = $_POST['harga'] ?? 0;
    $menu->status = $_POST['status'] ?? 'Tersedia';
    $menu->stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    // Tangani upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['gambar']['tmp_name'];
        $fileName = $_FILES['gambar']['name'];
        $fileSize = $_FILES['gambar']['size'];
        $fileType = $_FILES['gambar']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Ekstensi yang diizinkan
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Buat nama file unik untuk menghindari overwrite file lain
            $newFileName = $menu->kode_menu . '_' . uniqid() . '.' . $fileExtension;

            $uploadFileDir = __DIR__ . '/../assets/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $menu->gambar = $newFileName;
            } else {
                $message = "Terjadi kesalahan saat mengupload gambar.";
            }
        } else {
            $message = "Jenis file gambar tidak diperbolehkan. Gunakan: jpg, jpeg, png, gif.";
        }
    } else {
        // Jika tidak upload file, bisa juga diset gambar kosong/null
        $menu->gambar = null;
    }

    // Simpan data menu beserta nama file gambar ke database
    if (empty($message) && $menu->create()) {
        $message = "Menu berhasil ditambahkan!";
        $kode_menu_terbaru = '';
    } elseif (empty($message)) {
        $message = "Gagal menambah menu!";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Tambah Menu - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">
        <h2>Tambah Menu</h2>


        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>


        <form method="POST" novalidate enctype="multipart/form-data">
                <!-- ... field lain seperti kode_menu, nama_menu, kategori, harga, stok, status ... -->

            <div class="mb-3">
                <label for="kode_menu" class="form-label">Kode Menu</label>
                <input type="text" class="form-control" id="kode_menu" name="kode_menu"
                    value="<?= htmlspecialchars($kode_menu_terbaru ?? '') ?>" readonly required>
            </div>


            <div class="mb-3">
                <label for="nama_menu" class="form-label">Nama Menu</label>
                <input type="text" class="form-control" id="nama_menu" name="nama_menu" required>
            </div>
        
                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Menu</label>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                </div>

                



            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select name="kategori" id="kategori" class="form-control" required>
                    <option disabled selected>- Pilih -</option>
                    <option value="Coffe">Coffe</option>
                    <option value="Non Coffe">Non Coffe</option>
                </select>
            </div>


            <div class="mb-3">
                <label for="harga" class="form-label">Harga</label>
                <input type="number" class="form-control" id="harga" name="harga" min="0" required>
            </div>


            <div class="mb-3">


                <label for="stok" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok" min="0" value="0" required>
            </div>


            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Tersedia" selected>Tersedia</option>
                    <option value="Tidak Tersedia">Tidak Tersedia</option>
                </select>
            </div>


            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="manage_menu.php" class="btn btn-secondary ms-2">Kembali</a>
        </form>
    </div>
</body>

</html>