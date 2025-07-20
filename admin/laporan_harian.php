<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

date_default_timezone_set('Asia/Makassar');

// Ambil filter
$filter_type = $_GET['filter'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$bulan = $_GET['bulan'] ?? date('Y-m');
$tahun = $_GET['tahun'] ?? date('Y');

function getLaporanData($db, $filter_type, $tanggal = null, $bulan = null, $tahun = null)
{
    $data = [
        'summary' => [],
        'detail' => [],
        'menu_terlaris' => [],
        'status_pesanan' => []
    ];

    switch ($filter_type) {
        case 'harian':
            $dateFilter = "DATE(tgl_pesanan) = :tanggal";

            // Summary
            $sql_summary = "SELECT 
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata_pesanan
                FROM pesanan 
                WHERE status_pesanan = 'Selesai' AND $dateFilter";
            $stmt = $db->prepare($sql_summary);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->execute();
            $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Detail
            $sql_detail = "SELECT p.*, m.nama_menu, m.kategori, m.harga as harga_satuan
                FROM pesanan p
                LEFT JOIN menu m ON p.kode_menu = m.kode_menu
                WHERE p.status_pesanan = 'Selesai' AND $dateFilter
                ORDER BY p.tgl_pesanan DESC";
            $stmt_detail = $db->prepare($sql_detail);
            $stmt_detail->bindParam(':tanggal', $tanggal);
            $stmt_detail->execute();
            $data['detail'] = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

            // Menu Terlaris
            $sql_menu = "SELECT p.kode_menu, m.nama_menu, m.kategori,
                COUNT(*) as jumlah_terjual,
                SUM(p.jumlah) as total_porsi,
                SUM(p.total_harga) as total_penjualan
                FROM pesanan p
                LEFT JOIN menu m ON p.kode_menu = m.kode_menu
                WHERE p.status_pesanan = 'Selesai' AND $dateFilter
                GROUP BY p.kode_menu
                ORDER BY jumlah_terjual DESC
                LIMIT 5";
            $stmt_menu = $db->prepare($sql_menu);
            $stmt_menu->bindParam(':tanggal', $tanggal);
            $stmt_menu->execute();
            $data['menu_terlaris'] = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);

            // Status Pesanan
            $sql_status = "SELECT status_pesanan, COUNT(*) as jumlah FROM pesanan WHERE $dateFilter GROUP BY status_pesanan";
            $stmt_status = $db->prepare($sql_status);
            $stmt_status->bindParam(':tanggal', $tanggal);
            $stmt_status->execute();
            $data['status_pesanan'] = $stmt_status->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'mingguan':
            $start_week = date('Y-m-d', strtotime($tanggal . ' -' . date('w', strtotime($tanggal)) . ' days'));
            $end_week = date('Y-m-d', strtotime($start_week . ' +6 days'));

            // Summary mingguan
            $sql_summary = "SELECT 
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata_pesanan
                FROM pesanan 
                WHERE status_pesanan = 'Selesai' 
                AND DATE(tgl_pesanan) BETWEEN :start_date AND :end_date";
            $stmt = $db->prepare($sql_summary);
            $stmt->bindParam(':start_date', $start_week);
            $stmt->bindParam(':end_date', $end_week);
            $stmt->execute();
            $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['summary']['periode'] = "$start_week sampai $end_week";

            // Detail per hari
            $sql_detail = "SELECT DATE(tgl_pesanan) as tanggal,
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan
                FROM pesanan
                WHERE status_pesanan = 'Selesai'
                AND DATE(tgl_pesanan) BETWEEN :start_date AND :end_date
                GROUP BY DATE(tgl_pesanan)
                ORDER BY DATE(tgl_pesanan)";
            $stmt_detail = $db->prepare($sql_detail);
            $stmt_detail->bindParam(':start_date', $start_week);
            $stmt_detail->bindParam(':end_date', $end_week);
            $stmt_detail->execute();
            $data['detail'] = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

            // Menu Terlaris mingguan
            $sql_menu = "SELECT p.kode_menu, m.nama_menu, m.kategori,
                COUNT(*) as jumlah_terjual,
                SUM(p.jumlah) as total_porsi,
                SUM(p.total_harga) as total_penjualan
                FROM pesanan p
                LEFT JOIN menu m ON p.kode_menu = m.kode_menu
                WHERE p.status_pesanan = 'Selesai'
                AND DATE(p.tgl_pesanan) BETWEEN :start_date AND :end_date
                GROUP BY p.kode_menu
                ORDER BY jumlah_terjual DESC
                LIMIT 5";
            $stmt_menu = $db->prepare($sql_menu);
            $stmt_menu->bindParam(':start_date', $start_week);
            $stmt_menu->bindParam(':end_date', $end_week);
            $stmt_menu->execute();
            $data['menu_terlaris'] = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);

            // Status Pesanan mingguan
            $sql_status = "SELECT status_pesanan, COUNT(*) as jumlah FROM pesanan WHERE DATE(tgl_pesanan) BETWEEN :start_date AND :end_date GROUP BY status_pesanan";
            $stmt_status = $db->prepare($sql_status);
            $stmt_status->bindParam(':start_date', $start_week);
            $stmt_status->bindParam(':end_date', $end_week);
            $stmt_status->execute();
            $data['status_pesanan'] = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

            break;

        case 'bulanan':
            $sql_summary = "SELECT 
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata_pesanan
                FROM pesanan 
                WHERE status_pesanan = 'Selesai' 
                AND DATE_FORMAT(tgl_pesanan, '%Y-%m') = :bulan";
            $stmt = $db->prepare($sql_summary);
            $stmt->bindParam(':bulan', $bulan);
            $stmt->execute();
            $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql_detail = "SELECT DATE(tgl_pesanan) as tanggal,
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan
                FROM pesanan 
                WHERE status_pesanan = 'Selesai' 
                AND DATE_FORMAT(tgl_pesanan, '%Y-%m') = :bulan
                GROUP BY DATE(tgl_pesanan)
                ORDER BY DATE(tgl_pesanan)";
            $stmt_detail = $db->prepare($sql_detail);
            $stmt_detail->bindParam(':bulan', $bulan);
            $stmt_detail->execute();
            $data['detail'] = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

            $sql_menu = "SELECT p.kode_menu, m.nama_menu, m.kategori,
                COUNT(*) as jumlah_terjual,
                SUM(p.jumlah) as total_porsi,
                SUM(p.total_harga) as total_penjualan
                FROM pesanan p
                LEFT JOIN menu m ON p.kode_menu = m.kode_menu
                WHERE p.status_pesanan = 'Selesai' 
                AND DATE_FORMAT(p.tgl_pesanan, '%Y-%m') = :bulan
                GROUP BY p.kode_menu
                ORDER BY jumlah_terjual DESC
                LIMIT 5";
            $stmt_menu = $db->prepare($sql_menu);
            $stmt_menu->bindParam(':bulan', $bulan);
            $stmt_menu->execute();
            $data['menu_terlaris'] = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);

            $sql_status = "SELECT status_pesanan, COUNT(*) as jumlah FROM pesanan WHERE DATE_FORMAT(tgl_pesanan, '%Y-%m') = :bulan GROUP BY status_pesanan";
            $stmt_status = $db->prepare($sql_status);
            $stmt_status->bindParam(':bulan', $bulan);
            $stmt_status->execute();
            $data['status_pesanan'] = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

            break;

        case 'tahunan':
            $sql_summary = "SELECT 
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata_pesanan
                FROM pesanan 
                WHERE status_pesanan = 'Selesai' 
                AND YEAR(tgl_pesanan) = :tahun";
            $stmt = $db->prepare($sql_summary);
            $stmt->bindParam(':tahun', $tahun);
            $stmt->execute();
            $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql_detail = "SELECT DATE_FORMAT(tgl_pesanan, '%Y-%m') as bulan,
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_pendapatan
                FROM pesanan 
                WHERE status_pesanan = 'Selesai' 
                AND YEAR(tgl_pesanan) = :tahun
                GROUP BY DATE_FORMAT(tgl_pesanan, '%Y-%m')
                ORDER BY bulan";
            $stmt_detail = $db->prepare($sql_detail);
            $stmt_detail->bindParam(':tahun', $tahun);
            $stmt_detail->execute();
            $data['detail'] = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

            $sql_menu = "SELECT p.kode_menu, m.nama_menu, m.kategori,
                COUNT(*) as jumlah_terjual,
                SUM(p.jumlah) as total_porsi,
                SUM(p.total_harga) as total_penjualan
                FROM pesanan p
                LEFT JOIN menu m ON p.kode_menu = m.kode_menu
                WHERE p.status_pesanan = 'Selesai' 
                AND YEAR(p.tgl_pesanan) = :tahun
                GROUP BY p.kode_menu
                ORDER BY jumlah_terjual DESC
                LIMIT 5";
            $stmt_menu = $db->prepare($sql_menu);
            $stmt_menu->bindParam(':tahun', $tahun);
            $stmt_menu->execute();
            $data['menu_terlaris'] = $stmt_menu->fetchAll(PDO::FETCH_ASSOC);

            $sql_status = "SELECT status_pesanan, COUNT(*) as jumlah FROM pesanan WHERE YEAR(tgl_pesanan)=:tahun GROUP BY status_pesanan";
            $stmt_status = $db->prepare($sql_status);
            $stmt_status->bindParam(':tahun', $tahun);
            $stmt_status->execute();
            $data['status_pesanan'] = $stmt_status->fetchAll(PDO::FETCH_ASSOC);
            break;
    }

    return $data;
}

$laporan_data = getLaporanData($db, $filter_type, $tanggal, $bulan, $tahun);

// Fungsi format tanggal & bulan bahasa Indonesia
function getIndonesianMonth($month_year)
{
    $months = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    $parts = explode('-', $month_year);
    if (count($parts) == 2) {
        return $months[$parts[1]] . ' ' . $parts[0];
    }
    return $month_year;
}

function getIndonesianDate($date)
{
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $day_name = $days[date('w', strtotime($date))];
    $day = date('d', strtotime($date));
    $month = $months[date('n', strtotime($date))];
    $year = date('Y', strtotime($date));

    return "$day_name, $day $month $year";
}

// Prepare data untuk chart stok
$labels = [];
$data_stok = [];
foreach ($laporan_data['menu_terlaris'] as $menu) {
    $labels[] = $menu['nama_menu'];
    $data_stok[] = (int) $menu['total_porsi'];
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Penjualan - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #fff;
            /* putih bersih */
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1100px;
            margin-top: 2rem;
        }

        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #6f42c1;
            /* ungu cerah */
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .summary-card {
            border-radius: 0.75rem;
            padding: 1.5rem;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: default;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .total-pendapatan {
            background: linear-gradient(135deg, #6f42c1, #a061d8);
        }

        .total-pesanan {
            background: linear-gradient(135deg, #20c997, #0dcaf0);
        }

        .rata-rata {
            background: linear-gradient(135deg, #fd7e14, #ffc107);
        }

        .status-chart-card {
            padding: 1rem;
        }

        .table thead {
            background-color: #6f42c1;
            color: white;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .table-hover tbody tr:hover {
            background-color: #f3f0ff;
        }

        .btn-primary {
            background-color: #6f42c1;
            border: none;
        }

        .btn-primary:hover {
            background-color: #572a9d;
        }

        .nav-tabs .nav-link.active {
            border-color: #6f42c1 #6f42c1 transparent;
            color: #6f42c1;
            font-weight: 600;
        }

        /* Responsive card status kecil untuk grafik */
        .card-status-small {
            text-align: center;
            margin-bottom: 1rem;
            color: #555;
        }
    </style>
</head>


<body>
    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3"><i class="bi bi-bar-chart-line-fill text-primary"></i> Laporan Penjualan</h1>
                <small class="text-muted">Coffee Shop Management System</small>
            </div>
            <div class="no-print">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </button>
                <a href="dashboard.php" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <ul class="nav nav-tabs filter-tabs mb-3">
                    <li class="nav-item"><a href="?filter=harian&tanggal=<?= htmlspecialchars($tanggal) ?>"
                            class="nav-link <?= $filter_type == 'harian' ? 'active' : '' ?>"><i
                                class="bi bi-calendar-day"></i> Harian</a></li>
                    <li class="nav-item"><a href="?filter=mingguan&tanggal=<?= htmlspecialchars($tanggal) ?>"
                            class="nav-link <?= $filter_type == 'mingguan' ? 'active' : '' ?>"><i
                                class="bi bi-calendar-week"></i> Mingguan</a></li>
                    <li class="nav-item"><a href="?filter=bulanan&bulan=<?= htmlspecialchars($bulan) ?>"
                            class="nav-link <?= $filter_type == 'bulanan' ? 'active' : '' ?>"><i
                                class="bi bi-calendar-month"></i> Bulanan</a></li>
                    <li class="nav-item"><a href="?filter=tahunan&tahun=<?= htmlspecialchars($tahun) ?>"
                            class="nav-link <?= $filter_type == 'tahunan' ? 'active' : '' ?>"><i
                                class="bi bi-calendar"></i> Tahunan</a></li>
                </ul>
                <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter_type) ?>" />
                    <?php if ($filter_type == 'harian' || $filter_type == 'mingguan'): ?>
                        <div>
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal" class="form-control"
                                value="<?= htmlspecialchars($tanggal) ?>" required />
                        </div>
                    <?php elseif ($filter_type == 'bulanan'): ?>
                        <div>
                            <label for="bulan" class="form-label">Bulan</label>
                            <input type="month" id="bulan" name="bulan" class="form-control"
                                value="<?= htmlspecialchars($bulan) ?>" required />
                        </div>
                    <?php elseif ($filter_type == 'tahunan'): ?>
                        <div>
                            <label for="tahun" class="form-label">Tahun</label>
                            <select id="tahun" name="tahun" class="form-select" required>
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Periode Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>
                    <i class="bi bi-info-circle"></i> Periode Laporan:
                    <?php
                    switch ($filter_type) {
                        case 'harian':
                            echo getIndonesianDate($tanggal);
                            break;
                        case 'mingguan':
                            echo isset($laporan_data['summary']['periode']) ? 'Minggu ' . $laporan_data['summary']['periode'] : 'Minggu ' . date('d M Y', strtotime($tanggal));
                            break;
                        case 'bulanan':
                            echo getIndonesianMonth($bulan);
                            break;
                        case 'tahunan':
                            echo 'Tahun ' . $tahun;
                            break;
                    }
                    ?>
                </h5>
            </div>
        </div>

        <?php if ($laporan_data['summary'] && ($laporan_data['summary']['jumlah_pesanan'] ?? 0) > 0): ?>
            <!-- Status Pesanan Lengkap -->
            <div class="row mb-4">
                <?php
                $colorMap = [
                    'menunggu' => 'warning',
                    'dikonfirmasi' => 'info',
                    'diproses' => 'primary',
                    'siap' => 'secondary',
                    'selesai' => 'success',
                    'batal' => 'danger'
                ];
                foreach ($laporan_data['status_pesanan'] as $status):
                    $st = strtolower($status['status_pesanan']);
                    $badgeColor = $colorMap[$st] ?? 'secondary';
                    ?>
                    <div class="col-md-2 col-6 mb-3">
                        <div class="card text-center bg-<?= $badgeColor ?> text-white p-2">
                            <div class="card-body p-2">
                                <div class="fw-bold text-capitalize status-badge">
                                    <?= htmlspecialchars($status['status_pesanan']) ?>
                                </div>
                                <div class="display-5"><?= $status['jumlah'] ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card summary-card total-pendapatan text-center">
                        <i class="bi bi-currency-dollar fs-1 mb-2"></i>
                        <h6>Total Pendapatan</h6>
                        <h2>Rp <?= number_format($laporan_data['summary']['total_pendapatan'], 0, ',', '.') ?></h2>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card summary-card total-pesanan text-center">
                        <i class="bi bi-bag-check fs-1 mb-2"></i>
                        <h6>Total Pesanan</h6>
                        <h2><?= $laporan_data['summary']['jumlah_pesanan'] ?></h2>
                        <small>Pesanan Selesai</small>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card summary-card rata-rata text-center">
                        <i class="bi bi-graph-up fs-1 mb-2"></i>
                        <h6>Rata-rata per Pesanan</h6>
                        <h2>Rp <?= number_format($laporan_data['summary']['rata_rata_pesanan'], 0, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
         



            <!-- Detail Table -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-table"></i> Detail <?= ucfirst($filter_type) ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($laporan_data['detail'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <?php if ($filter_type == 'harian'): ?>
                                            <th>Kode Pesanan</th>
                                            <th>Nama Pelanggan</th>
                                            <th>Menu</th>
                                            <th>Kategori</th>
                                            <th>Jumlah</th>
                                            <th>Harga Satuan</th>
                                            <th>Total</th>
                                            <th>Waktu</th>
                                        <?php else: ?>
                                            <th><?= $filter_type == 'tahunan' ? 'Bulan' : 'Tanggal' ?></th>
                                            <th>Jumlah Pesanan</th>
                                            <th>Total Pendapatan</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan_data['detail'] as $i => $row): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <?php if ($filter_type == 'harian'): ?>
                                                <td><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['nama_menu'] ?? $row['kode_menu']) ?></strong><br />
                                                    <small class="text-muted"><?= htmlspecialchars($row['kode_menu']) ?></small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?= strtolower($row['kategori']) == 'coffee' ? 'primary' : 'success' ?>">
                                                        <?= htmlspecialchars($row['kategori'] ?? '-') ?>
                                                    </span>
                                                </td>
                                                <td><?= $row['jumlah'] ?></td>
                                                <td>Rp <?= number_format($row['harga_satuan'] ?? 0, 0, ',', '.') ?></td>
                                                <td><strong>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></strong></td>
                                                <td><?= date('H:i:s', strtotime($row['tgl_pesanan'])) ?></td>
                                            <?php else: ?>
                                                <td>
                                                    <?php
                                                    if ($filter_type == 'tahunan') {
                                                        echo getIndonesianMonth($row['bulan']);
                                                    } else {
                                                        echo getIndonesianDate($row['tanggal']);
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= $row['jumlah_pesanan'] ?></td>
                                                <td><strong>Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?></strong></td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if ($filter_type == 'harian'): ?>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="7" class="text-end"><strong>Total Pendapatan:</strong></td>
                                            <td><strong class="text-success">Rp
                                                    <?= number_format($laporan_data['summary']['total_pendapatan'], 0, ',', '.') ?></strong>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">Tidak ada data untuk periode ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart sederhana dengan doughnut -->


        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i> Tidak ada data untuk periode ini.
            </div>
        <?php endif; ?>
        <!-- Chart Status Pesanan -->
            <div class="card status-chart-card">
                <div class="card-header">
                    Status Pesanan
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="150"></canvas>
                </div>
            </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctxStatus = document.getElementById('statusChart').getContext('2d');

        // Data status dan warnanya
        const statusLabels = [
            <?php foreach ($laporan_data['status_pesanan'] as $s)
                echo "'" . htmlspecialchars($s['status_pesanan']) . "',"; ?>
        ];

        const statusData = [
            <?php foreach ($laporan_data['status_pesanan'] as $s)
                echo (int) $s['jumlah'] . ","; ?>
        ];

        const statusColors = [
            '#ffc107',   // Menunggu - kuning
            '#0dcaf0',   // Dikonfirmasi - biru muda
            '#6f42c1',   // Diproses - ungu
            '#6c757d',   // Siap - abu
            '#198754',   // Selesai - hijau
            '#dc3545'    // Batal - merah
        ];

        // Cocokkan warna dengan label secara sederhana
        const datasetColors = statusLabels.map(label => {
            const map = {
                'Menunggu': '#ffc107',
                'Dikonfirmasi': '#0dcaf0',
                'Diproses': '#6f42c1',
                'Siap': '#6c757d',
                'Selesai': '#198754',
                'Batal': '#dc3545'
            };
            return map[label] || '#adb5bd'; // default abu-abu
        });

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: datasetColors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 30
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 14 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                let label = ctx.label || '';
                                let value = ctx.parsed || 0;
                                return label + ': ' + value + ' pesanan';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    </script>
    <script>
        // Inisialisasi chart stok
        const ctxStok = document.getElementById('stokChart').getContext('2d');
        new Chart(ctxStok, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Total Porsi Terjual',
                    data: <?= json_encode($data_stok) ?>,
                    backgroundColor: '#6f42c1',
                    borderColor: '#5a2e9b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Menu Terlaris' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>

</body>

</html>