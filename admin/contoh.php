<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

date_default_timezone_set('Asia/Makassar');
$namaAdmin = $_SESSION['admin_nama'] ?? 'Admin';
$fotoAdmin = $_SESSION['admin_foto'] ?? 'default.jpg';

// === Bagian Range Default (Hari, Minggu, Bulan, Tahun) ===
$today = date('Y-m-d');
$year = date('Y');
$month = date('Y-m');
$firstDayMonth = date('Y-m-01');
$lastDayMonth = date('Y-m-t');
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

function getTotalPendapatan($db, $startDate, $endDate)
{
    $query = "SELECT SUM(total_harga) as total FROM pesanan 
            WHERE status_pesanan = 'Selesai' 
            AND DATE(tgl_pesanan) BETWEEN :start_date AND :end_date";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

function getTotalPengeluaran($db, $kategori = null, $startDate = null, $endDate = null)
{
    $query = "SELECT SUM(nominal) as total FROM pengeluaran WHERE 1=1";
    if ($kategori !== null) {
        $query .= " AND kategori = :kategori";
    }
    if ($startDate !== null && $endDate !== null) {
        $query .= " AND DATE(tanggal) BETWEEN :start_date AND :end_date";
    }
    $stmt = $db->prepare($query);
    if ($kategori !== null) {
        $stmt->bindParam(':kategori', $kategori);
    }
    if ($startDate !== null && $endDate !== null) {
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
    }
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

// Hitung pendapatan standar
$pendapatanHariIni = getTotalPendapatan($db, $today, $today);
$pendapatanMingguIni = getTotalPendapatan($db, $startOfWeek, $endOfWeek);
$pendapatanBulanIni = getTotalPendapatan($db, $firstDayMonth, $lastDayMonth);
$pendapatanTahunIni = getTotalPendapatan($db, "$year-01-01", "$year-12-31");

// Pengeluaran per kategori
$kategoriPengeluaran = ['Modal', 'Karyawan', 'Produksi', 'Biaya Lain - Lain'];
$pengeluaranBulan = [];
$pengeluaranTahun = [];
$totalPengeluaranBulan = 0;
$totalPengeluaranTahun = 0;

foreach ($kategoriPengeluaran as $kat) {
    $pengeluaranBulan[$kat] = getTotalPengeluaran($db, $kat, $firstDayMonth, $lastDayMonth);
    $pengeluaranTahun[$kat] = getTotalPengeluaran($db, $kat, "$year-01-01", "$year-12-31");
    $totalPengeluaranBulan += $pengeluaranBulan[$kat];
    $totalPengeluaranTahun += $pengeluaranTahun[$kat];
}

$profitBulanIni = $pendapatanBulanIni - $totalPengeluaranBulan;
$profitTahunIni = $pendapatanTahunIni - $totalPengeluaranTahun;

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

// === Bagian Filter Custom ===
$filter_start = $_GET['start_date'] ?? date('Y-m-01');
$filter_end = $_GET['end_date'] ?? date('Y-m-t');

try {
    $startDate = new DateTime($filter_start);
    $endDate = new DateTime($filter_end);
} catch (Exception $ex) {
    $startDate = new DateTime(date('Y-m-01'));
    $endDate = new DateTime(date('Y-m-t'));
}
$startDateStr = $startDate->format('Y-m-d');
$endDateStr = $endDate->format('Y-m-d');

$pendapatanFiltered = getTotalPendapatan($db, $startDateStr, $endDateStr);
















// Mulai session hanya jika belum aktif


// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

date_default_timezone_set('Asia/Makassar');

// Mapping kode status
$status_full_names = [
    'pending' => 'Menunggu',
    'dikonfirmasi' => 'Dikonfirmasi',
    'proses' => 'Diproses',
    'siap' => 'Siap',
    'selesai' => 'Selesai',
    'batal' => 'Batal'
];

// Update status pesanan jika form update dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $kode_pesanan = $_POST['kode_pesanan'] ?? '';
    $status_baru = $_POST['status_baru'] ?? '';

    $status_baru_full = $status_full_names[$status_baru] ?? null;

    if ($kode_pesanan && $status_baru_full) {
        $update_query = "UPDATE pesanan SET status_pesanan = :status WHERE kode_pesanan = :kode";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':status', $status_baru_full);
        $update_stmt->bindParam(':kode', $kode_pesanan);

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Status pesanan berhasil diupdate!";
        } else {
            $_SESSION['error_message'] = "Gagal mengupdate status pesanan.";
        }
    } else {
        $_SESSION['error_message'] = "Data yang dikirim tidak lengkap atau status tidak valid.";
    }
}

// Ambil filter
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Konversi kode status untuk filter
$filter_status_nama = '';
if ($status_filter && isset($status_full_names[$status_filter])) {
    $filter_status_nama = $status_full_names[$status_filter];
}

// Query pesanan dengan filter dinamis
$query = "SELECT p.*, m.nama_menu 
          FROM pesanan p 
          LEFT JOIN menu m ON p.kode_menu = m.kode_menu 
          WHERE 1=1";
$params = [];

if ($filter_status_nama) {
    $query .= " AND p.status_pesanan = :status";
    $params[':status'] = $filter_status_nama;
}

if ($date_filter) {
    $query .= " AND DATE(p.tgl_pesanan) = :date";
    $params[':date'] = $date_filter;
}

if ($search) {
    $query .= " AND (p.nama_pelanggan LIKE :search OR p.kode_pesanan LIKE :search OR m.nama_menu LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$query .= " ORDER BY p.tgl_pesanan DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_colors = [
    'pending' => 'warning',
    'dikonfirmasi' => 'secondary',
    'proses' => 'info',
    'siap' => 'primary',
    'selesai' => 'success',
    'batal' => 'danger'
];

function getStatusKeyByValue($array, $value) {
    return array_search($value, $array);
}

// Hitung total dan rincian status
$total_jumlah = 0;
$total_harga = 0;
$total_jumlah_batal = 0;
$total_harga_batal = 0;
$total_jumlah_belum_jelas = 0;
$total_harga_belum_jelas = 0;
$total_jumlah_selesai = 0;
$total_harga_selesai = 0;

foreach ($orders as $order) {
    $jumlah = (int)$order['jumlah'];
    $harga = (int)$order['total_harga'];
    $status_db = strtolower($order['status_pesanan'] ?? '');

    $total_jumlah += $jumlah;
    $total_harga += $harga;

    if ($status_db === 'batal') {
        $total_jumlah_batal += $jumlah;
        $total_harga_batal += $harga;
    } elseif ($status_db === 'selesai') {
        $total_jumlah_selesai += $jumlah;
        $total_harga_selesai += $harga;
    } else {
        $total_jumlah_belum_jelas += $jumlah;
        $total_harga_belum_jelas += $harga;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Pesanan - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        .status-badge {
            font-size: 0.8em;
        }
        .table-danger {
            background-color: #f8d7da !important;
        }
        .total-jumlah-batal, .total-harga-batal {
            color: red;
            font-weight: bold;
        }
        .summary-row td {
            font-weight: bold;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<div class="container mt-4">

    <!-- Contoh tombol kembali dan pesan notifikasi -->
    <div class="mb-3">
         <div>
                <img src="/assets/<?= htmlspecialchars($fotoAdmin) ?>" alt="Foto Admin" width="40" height="40"
                    class="rounded-circle border border-primary me-2" style="object-fit: cover;">
                <strong><?= htmlspecialchars($namaAdmin) ?></strong>
            </div><br>
        <a href="dashboard.php" class="btn btn-warning">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Filter Pesanan -->
    <div class="card mb-4">
        <div class="card-body">
            <h5><i class="fas fa-filter"></i> Filter Pesanan</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari Pesanan</label>
                    <input type="text" class="form-control" name="search" placeholder="Kode, nama pelanggan, atau menu..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <?php foreach ($status_full_names as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ($status_filter === $value) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($date_filter); ?>">
                </div>
                <div class="col-md-3 d-grid gap-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-light"><i class="fas fa-search"></i> Cari</button>
                    <a href="detail_pesanan.php" class="btn btn-outline-light"><i class="fas fa-times"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Pesanan -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-list"></i> Daftar Pesanan (<?= count($orders) ?> pesanan)</h5>
            <a href="manage_orders.php" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah Pesanan Baru</a>
        </div>
        <div class="card-body table-responsive-scroll">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>Tidak ada pesanan ditemukan</h4>
                    <p>Silakan ubah filter atau ulangi pencarian.</p>
                </div>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Menu</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Tanggal Pesanan</th>
                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $kode = $order['kode_pesanan'] ?? '';
                            $pelanggan = $order['nama_pelanggan'] ?? '';
                            $menu = $order['nama_menu'] ?? '';
                            $jumlah = (int)$order['jumlah'];
                            $total_harga_order = (int)$order['total_harga'];
                            $tgl_pesanan_raw = $order['tgl_pesanan'] ?? '';
                            $tgl_pesanan = $tgl_pesanan_raw ? date('d/m/Y H:i', strtotime($tgl_pesanan_raw)) : '';
                            $status_pesanan_nama = $order['status_pesanan'] ?? '';

                            $status_key = getStatusKeyByValue($status_full_names, $status_pesanan_nama);
                            $status_class = $status_colors[$status_key] ?? 'secondary';
                            $status_label = $status_full_names[$status_key] ?? 'Tidak diketahui';
                        ?>
                        <tr <?= strtolower($status_label) === 'batal' ? 'class="table-danger"' : '' ?>>
                            <td><strong><?= htmlspecialchars($kode) ?></strong></td>
                            <td><i class="fas fa-user"></i> <?= htmlspecialchars($pelanggan) ?></td>
                            <td><i class="fas fa-coffee"></i> <?= htmlspecialchars($menu) ?></td>
                            <td><span class="badge bg-info"><?= number_format($jumlah) ?></span></td>
                            <td><strong>Rp <?= number_format($total_harga_order, 0, ',', '.') ?></strong></td>
                            <td><small class="text-muted"><i class="fas fa-calendar"></i> <?= htmlspecialchars($tgl_pesanan) ?></small></td>
                            <td><span class="badge bg-<?= $status_class ?> status-badge"><?= htmlspecialchars($status_label) ?></span></td>
                        
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="summary-row">
                            <td colspan="3"><strong>Total Keseluruhan</strong></td>
                            <td><?= number_format($total_jumlah) ?></td>
                            <td>Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="summary-row">
                            <td colspan="3" class="text-end"><strong>Jumlah Pesanan Batal</strong></td>
                            <td class="total-jumlah-batal"><?= number_format($total_jumlah_batal) ?></td>
                            <td class="total-harga-batal">Rp <?= number_format($total_harga_batal, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="summary-row">
                            <td colspan="3" class="text-end"><strong>Jumlah Pesanan Belum Jelas</strong></td>
                            <td><?= number_format($total_jumlah_belum_jelas) ?></td>
                            <td>Rp <?= number_format($total_harga_belum_jelas, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                        <tr class="summary-row">
                            <td colspan="3" class="text-end"><strong>Jumlah Pesanan Selesai</strong></td>
                            <td><?= number_format($total_jumlah_selesai) ?></td>
                            <td>Rp <?= number_format($total_harga_selesai, 0, ',', '.') ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Modal updateStatusModal dan detailModal sesuaikan dari kode Anda sebelumnya -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function setUpdateModal(kodePesanan, currentStatus) {
        document.getElementById('update_kode_pesanan').value = kodePesanan;
        document.getElementById('update_status').value = currentStatus;
    }
    function showDetail(kode, pelanggan, menu, jumlah, total, tanggal, status) {
        document.getElementById('detail_kode').textContent = kode;
        document.getElementById('detail_pelanggan').textContent = pelanggan;
        document.getElementById('detail_menu').textContent = menu;
        document.getElementById('detail_jumlah').textContent = jumlah;
        document.getElementById('detail_total').textContent = 'Rp ' + parseInt(total).toLocaleString('id-ID');
        document.getElementById('detail_tanggal').textContent = tanggal;
        document.getElementById('detail_status').textContent = status;
    }
    setTimeout(function () {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function (alert) {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                alert.classList.add('fade');
            }
        });
    }, 5000);
</script>



<style>
    @media print {

        .btn,
        form,
        .alert {
            display: none !important;
        }
    }
</style>



<head>
    <meta charset="UTF-8" />
    <title>Laporan Keuangan Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-4">



        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
     <br><br>


        <h2 class="mb-4">📊 Laporan Keuangan</h2>

        <!-- Filter Tanggal Custom -->
        <div class="card mb-5">
            <div class="card-header bg-info text-white">Filter Laporan Pendapatan Kotor</div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            value="<?= htmlspecialchars($startDateStr) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                            value="<?= htmlspecialchars($endDateStr) ?>" required>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                    </div>
                </form>

                <h5>Total Pemasukan Kotor</h5>
                <p>Dari <strong><?= $startDate->format('d-m-Y') ?></strong>
                    sampai <strong><?= $endDate->format('d-m-Y') ?></strong></p>
                <h3>Rp <?= number_format($pendapatanFiltered, 0, ',', '.') ?></h3>
            </div>
        </div>

        <!-- Pemasukan -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Pemasukan</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Periode</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Hari Ini (<?= $today ?>)</td>
                            <td>Rp <?= number_format($pendapatanHariIni, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Minggu Ini (<?= $startOfWeek ?> - <?= $endOfWeek ?>)</td>
                            <td>Rp <?= number_format($pendapatanMingguIni, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Bulan Ini (<?= $firstDayMonth ?> - <?= $lastDayMonth ?>)</td>
                            <td>Rp <?= number_format($pendapatanBulanIni, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Tahun <?= $year ?></td>
                            <td>Rp <?= number_format($pendapatanTahunIni, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pengeluaran Bulanan -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">Pengeluaran Bulan Ini</div>
            <div class="card-body">
                <table class="table table-bordered mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategoriPengeluaran as $kat): ?>
                            <tr>
                                <td><?= htmlspecialchars($kat) ?></td>
                                <td>Rp <?= number_format($pengeluaranBulan[$kat], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-success">
                            <td><strong>Total</strong></td>
                            <td><strong>Rp <?= number_format($totalPengeluaranBulan, 0, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <div><strong>Profit Bersih Bulan Ini:</strong> Rp <?= number_format($profitBulanIni, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- Pengeluaran Tahunan -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">Pengeluaran Tahun Ini</div>
            <div class="card-body">
                <table class="table table-bordered mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategoriPengeluaran as $kat): ?>
                            <tr>
                                <td><?= htmlspecialchars($kat) ?></td>
                                <td>Rp <?= number_format($pengeluaranTahun[$kat], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-success">
                            <td><strong>Total</strong></td>
                            <td><strong>Rp <?= number_format($totalPengeluaranTahun, 0, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <div><strong>Profit Bersih Tahun Ini:</strong> Rp <?= number_format($profitTahunIni, 0, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- Input Pengeluaran -->
        <div class="card mb-4">
            <div class="card-header bg-warning">Input Pengeluaran</div>
            <div class="card-body">
                <form method="POST" action="proses_input_pengeluaran.php">
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select name="kategori" id="kategori" class="form-select" required>
                            <?php foreach ($kategoriPengeluaran as $kat): ?>
                                <option value="<?= htmlspecialchars($kat) ?>"><?= htmlspecialchars($kat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nominal" class="form-label">Nominal (Rp)</label>
                            <input type="text" name="nominal" id="nominal" class="form-control" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipe" class="form-label">Jenis Input</label>
                            <select name="tipe" id="tipe" class="form-select" required>
                                <option value="tambah">➕ Tambah</option>
                                <option value="kurangi">➖ Kurangi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" value="<?= date('Y-m-d') ?>" class="form-control"
                            required />
                    </div>

                    <div class="mb-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"> Simpan Pengeluaran</button>
                        <button type="button" class="btn btn-success" onclick="printLaporan()">🖨️ Cetak
                            Laporan</button>
                    </div>


                </form>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printLaporan() {
    // Simpan posisi scroll
    const scrollY = window.scrollY;

    // Panggil print
    window.print();

    // Setelah selesai print (cancel atau lanjut), kembalikan posisi
    window.onafterprint = function() {
        window.scrollTo(0, scrollY);
    };
}
</script>


</body>

</html>