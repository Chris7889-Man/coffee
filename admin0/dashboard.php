<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Pesanan.php';
require_once __DIR__ . '/../classes/Menu.php';

$database = new Database();
$db = $database->getConnection();

$pesanan = new Pesanan($db);
$menu = new Menu($db);

$total_pesanan = $pesanan->read()->rowCount();
$total_menu = $menu->read()->rowCount();

// Ambil tanggal hari ini untuk filtering
$today = date('Y-m-d');

// Hitung pembeli unik status 'Selesai' hari ini
$query_pembeli_selesai = "
    SELECT COUNT(DISTINCT nama_pelanggan) AS jumlah_pembeli_selesai 
    FROM pesanan 
    WHERE status_pesanan = 'Selesai' AND DATE(tgl_pesanan) = :today
";
$stmt_pembeli_selesai = $db->prepare($query_pembeli_selesai);
$stmt_pembeli_selesai->bindParam(':today', $today);
$stmt_pembeli_selesai->execute();
$result = $stmt_pembeli_selesai->fetch(PDO::FETCH_ASSOC);
$jumlah_pembeli_selesai = $result['jumlah_pembeli_selesai'] ?? 0;

// Ambil data pesanan terbaru hari ini (max 5)
$query_recent = "
    SELECT * FROM pesanan 
    WHERE DATE(tgl_pesanan) = :today
    ORDER BY tgl_pesanan DESC
    LIMIT 5
";
$stmt = $db->prepare($query_recent);
$stmt->bindParam(':today', $today);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Dashboard - POS | Coffee Shop</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
<style>
    body {
        background: #f5f6fa;
        margin: 0; padding: 0; font-family: Arial, sans-serif;
    }
    #wrapper {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }
    .sidebar {
        width: 250px;
        background: #232a34;
        color: #bbb;
        padding: 20px 15px;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow-y: auto;
        position: sticky;
        top: 0;
    }
    .sidebar h4 {
        color: #fff;
        margin-bottom: 1rem;
    }
    .sidebar a.nav-link {
        display: flex;
        align-items: center;
        color: #bbb;
        padding: 10px 12px;
        border-radius: 5px;
        margin-bottom: 0.25rem;
        text-decoration: none;
    }
    .sidebar a.nav-link.active,
    .sidebar a.nav-link:hover {
        background: #1e90ff;
        color: #fff;
        text-decoration: none;
    }
    .sidebar a.nav-link i {
        margin-right: 8px;
        width: 20px;
        text-align: center;
    }
    .sidebar .user-info {
        margin-top: auto;
        border-top: 1px solid #333;
        padding-top: 12px;
        font-size: 14px;
        color: #ccc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #page-content {
        flex-grow: 1;
        overflow-y: auto;
        height: 100vh;
        padding: 20px 30px;
    }
    .headerbar {
        background: #fff;
        border-bottom: 1px solid #eaeaea;
        padding: 20px 0;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .card-stat {
        border-radius: 12px;
        transition: transform 0.15s ease-in-out;
        cursor: default;
    }
    .card-stat:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(30, 90, 160, 0.12);
    }
    .table-responsive {
        max-height: 55vh;
        overflow-y: auto;
    }
    @media (max-width: 991px) {
        .sidebar {
            width: 70px;
            padding: 10px 8px;
        }
        .sidebar h4,
        .sidebar .user-info span {
            display: none;
        }
        .sidebar a.nav-link {
            justify-content: center;
            padding: 10px 0;
        }
        .sidebar a.nav-link i {
            margin: 0;
            width: auto;
        }
    }

    /* Styling highlight baris selesai */
    .highlight-selesai {
        background-color: #d1e7dd !important; /* hijau muda */
    }

    /* Styling daftar pembeli toggle (optional jika ingin digunakan) */
    #daftarPembeli {
        cursor: default;
        max-height: 200px;
        overflow-y: auto;
        padding-left: 0;
        margin-top: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        background: #fff;
        color: #333;
    }
    #daftarPembeli li {
        padding: 8px 15px;
        border-bottom: 1px solid #eee;
        list-style: none;
        user-select: none;
    }
    #daftarPembeli li.highlight {
        background-color: yellow;
    }
</style>
</head>
<body>
<div id="wrapper">
    <nav class="sidebar">
        <h4><i class="fas fa-cash-register"></i> Point Of Sale</h4>
        
        <a href="dashboard.php" class="nav-link active"><i class="fas fa-home"></i><span class="ms-2 d-none d-lg-inline">Dashboard</span></a>
        
        <a href="view_menu.php" class="nav-link"><i class="fas fa-utensils"></i><span class="ms-2 d-none d-lg-inline">Menu</span></a>
        <a href="manage_menu.php" class="nav-link"><i class="fas fa-mug-hot"></i><span class="ms-2 d-none d-lg-inline">Kelola Menu</span></a>
        <a href="manage_orders.php" class="nav-link"><i class="fas fa-cart-shopping"></i><span class="ms-2 d-none d-lg-inline">Kelola Pesanan</span></a>
        <a href="detail_pesanan.php" class="nav-link"><i class="fas fa-list"></i><span class="ms-2 d-none d-lg-inline">Detail Pesanan</span></a>
         <a href="contoh.php" class="nav-link"><i class="fas fa-dollar-sign"></i><span class="ms-2 d-none d-lg-inline">Keuangan</span></a>
        <a href="laporan_harian.php" class="nav-link"><i class="fas fa-file-lines"></i><span class="ms-2 d-none d-lg-inline">Laporan</span></a>
      
       
          <a href="data_staff.php" class="nav-link"><i class="fas fa-users-cog"></i><span class="ms-2 d-none d-lg-inline">Staff</span></a>

        <?php if (!empty($_SESSION['is_super_admin'])): ?>
            <a href="manage_admin.php" class="nav-link"><i class="fas fa-user-shield"></i><span class="ms-2 d-none d-lg-inline">Kelola Admin</span></a>
        <?php endif; ?>
        <div class="user-info mt-auto d-none d-lg-flex align-items-center justify-content-between">
            <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></span>
            <a href="logout.php" class="btn btn-sm btn-warning">Logout</a>
        </div>
    </nav>
    <div id="page-content">
        <div class="headerbar">
            <div>
                <h2 class="mb-0">Vibescoffee</h2>
                <small class="text-muted" id="live-time"></small>
            </div>
            <div class="d-flex align-items-center">
                <?php $foto_admin = $_SESSION['admin_foto'] ?? 'default.jpg'; ?>
                <img src="/assets/<?=htmlspecialchars($foto_admin)?>" alt="Foto Admin" width="54" height="54" class="rounded-circle border border-primary me-2" style="object-fit: cover;">
                <span class="fw-bold"><?= htmlspecialchars($_SESSION['admin_nama']) ?></span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card card-stat bg-primary text-white shadow-sm text-center p-3">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <h5>Total Pesanan</h5>
                    <p class="display-6 fw-bold mb-0"><?= $total_pesanan ?></p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card card-stat bg-success text-white shadow-sm text-center p-3">
                    <i class="fas fa-coffee fa-2x mb-2"></i>
                    <h5>Total Menu</h5>
                    <p class="display-6 fw-bold mb-0"><?= $total_menu ?></p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card card-stat bg-warning text-dark shadow-sm text-center p-3" style="cursor:pointer" id="jumlahPembeli">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h6>Jumlah Pembeli Selesai</h6>
                    <p class="display-6 fw-bold mb-0"><?= $jumlah_pembeli_selesai ?></p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span>Pesanan Terbaru Hari ini</span>
                <a href="manage_orders.php" class="btn btn-light btn-sm"><i class="fas fa-tasks me-1"></i> Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (count($recent_orders) === 0): ?>
                    <div class="alert alert-info text-center m-3">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Belum ada data pesanan</h4>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Pesanan</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Jumlah</th>
                                    <th>Total Harga</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $row): ?>
                                <tr data-status="<?= htmlspecialchars($row['status_pesanan'] ?? '') ?>">
                                    <td><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pelanggan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['jumlah'] ?? '-') ?></td>
                                    <td>Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tgl_pesanan'])) ?></td>
                                    <td><?= date('H:i', strtotime($row['tgl_pesanan'])) ?></td>
                                    <td><?= htmlspecialchars($row['status_pesanan'] ?? '-') ?></td>
                                    <td>
                                        <a href="detail_pesanan.php?kode_pesanan=<?= urlencode($row['kode_pesanan']) ?>" class="btn btn-sm btn-primary" title="Detail Pesanan">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fungsi menampilkan waktu live
function updateLiveTime() {
    const now = new Date();
    const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const jam = now.getHours().toString().padStart(2, "0");
    const menit = now.getMinutes().toString().padStart(2, "0");
    const detik = now.getSeconds().toString().padStart(2, "0");
    const tanggal = now.getDate().toString().padStart(2, "0");
    const format = hari[now.getDay()] + ", " + tanggal + " " + bulan[now.getMonth()] + " " + now.getFullYear() + " | " + jam + ":" + menit + ":" + detik;
    document.getElementById('live-time').textContent = format;
}
setInterval(updateLiveTime, 1000);
updateLiveTime();

// Toggle highlight pesanan selesai pada tabel saat kartu jumlah pembeli diklik
document.getElementById('jumlahPembeli').addEventListener('click', function() {
    const rows = document.querySelectorAll('.table-responsive table tbody tr');
    rows.forEach(row => {
        if ((row.dataset.status || '').toLowerCase() === 'selesai') {
            row.classList.toggle('highlight-selesai');
        }
    });
});
</script>
</body>
</html>
