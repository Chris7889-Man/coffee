<?php
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Dapatkan semua nomor gerobak yang sudah dipakai
$usedGerobak = [];
$stmt = $db->query("SELECT kode_gerobak FROM staff");
if ($stmt) {
    $usedGerobak = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

// Data daftar nomor gerobak lengkap
$allGerobak = [
    "GRB 1",
    "GRB 2",
    "GRB 3",
    "GRB 4",
    "GRB 5",
    "GRB 6",
    "GRB 7",
    "GRB 8",
    "GRB 9",
    "GRB 10",
    "GRB 11",
    "GRB 12",
    "GRB 13",
    "GRB 14",
    "GRB 15",
    "GRB 16",
    "GRB 17",
    "GRB 18",
    "GRB 19",
    "GRB 20"
];

// Hitung penggunaan lokasi jualan, ambil lokasi yang sudah dipakai 2 kali atau lebih
$stmtLoc = $db->query("SELECT lokasi_jualan, COUNT(*) as total FROM staff GROUP BY lokasi_jualan HAVING total >= 2");
$usedLocations = $stmtLoc ? $stmtLoc->fetchAll(PDO::FETCH_COLUMN, 0) : [];
?>


<!DOCTYPE html>
<html>

<head>
    <title>Tambah Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>Tambah Staff</h2>

    <form action="proses_tambah_staff.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Nama Staff</label>
            <input type="text" name="nama_staff" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Foto Staff</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label>Jabatan</label>
            <select name="jabatan" class="form-control" required>

                <option value="Staff">Staff</option>

            </select>
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" autocomplete="new-password" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" autocomplete="new-password" required>
        </div>
        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirm" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>No. HP</label>
            <input type="text" name="no_hp" class="form-control">
        </div>
        <div class="mb-3">
            <label>Alamat Tempat Tinggal Sekarang</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Kode Gerobak</label>
            <select name="kode_gerobak" class="form-control" required>
                <option value="" disabled selected>Pilih Kode Gerobak</option>
                <?php foreach ($allGerobak as $gerobak): ?>
                    <?php if (!in_array($gerobak, $usedGerobak)): // hanya tampilkan yg belum dipakai ?>
                        <option value="<?= htmlspecialchars($gerobak) ?>"><?= htmlspecialchars($gerobak) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>

        </div>

        <div class="mb-3">
            <label>Lokasi Jualan</label>
            <select name="lokasi_jualan" class="form-control" required>
                <option value="" disabled selected>Pilih Lokasi Jualan</option>
                <?php
                $lokasiList = [
                    "Jl. Sultan Hasanuddin",
                    "Jl. Urip Sumoharjo",
                    "Jl. Jenderal Sudirman",
                    "Jl. Dr. Ratulangi",
                    "Jl. A. P. Pettarani",
                    "Jl. Andi Pangerang Pettarani",
                    "Jl. Hertasning",
                    "Jl. Metro Tanjung Bunga",
                    "Jl. Veteran",
                    "Jl. Perintis Kemerdekaan",
                    "Jl. Somba Opu",
                    "Jl. Boulevard",
                    "Jl. Taman Cendana"
                ];
                foreach ($lokasiList as $lokasi):
                    $disabled = in_array($lokasi, $usedLocations) ? 'disabled' : '';
                    ?>
                    <option value="<?= htmlspecialchars($lokasi) ?>" <?= $disabled ?>><?= htmlspecialchars($lokasi) ?>
                        <?= $disabled ? '(Sudah penuh)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="data_staff.php" class="btn btn-secondary">Kembali</a>
    </form>
</body>

</html>