<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Proteksi akses staff
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil tanggal dari parameter atau default hari ini
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Query untuk mendapatkan pesanan berdasarkan tanggal
$query = "SELECT p.*, m.nama_menu, m.harga 
        FROM pesanan p 
        LEFT JOIN menu m ON p.kode_menu = m.kode_menu 
        WHERE DATE(p.tgl_pesanan) = :tgl 
        ORDER BY p.tgl_pesanan DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':tgl', $selected_date);
$stmt->execute();
$pesanan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$total_pesanan = count($pesanan_list);
$total_pendapatan = 0;
$status_count = [
    'menunggu' => 0,
    'dikonfirmasi' => 0,
    'diproses' => 0,
    'siap' => 0,
    'selesai' => 0,
    'batal' => 0
];


foreach ($pesanan_list as $row) {
    if ($row['status_pesanan'] !== 'batal') {
        $total_pendapatan += $row['total_harga'];
    }
    $status_count[$row['status_pesanan']]++;
}

// Query untuk mendapatkan menu terlaris
$query_popular = "SELECT m.nama_menu, SUM(p.jumlah) as total_terjual, COUNT(*) as total_order
                FROM pesanan p 
                JOIN menu m ON p.kode_menu = m.kode_menu 
                WHERE DATE(p.tgl_pesanan) = :tgl AND p.status_pesanan != 'batal'
                GROUP BY p.kode_menu, m.nama_menu
                ORDER BY total_terjual DESC 
                LIMIT 5";
$stmt_popular = $db->prepare($query_popular);
$stmt_popular->bindParam(':tgl', $selected_date);
$stmt_popular->execute();
$menu_popular = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);

// Query untuk perbandingan dengan hari sebelumnya
$yesterday = date('Y-m-d', strtotime($selected_date . ' -1 day'));
$query_yesterday = "SELECT COUNT(*) as total_pesanan, SUM(total_harga) as total_pendapatan
                    FROM pesanan 
                    WHERE DATE(tgl_pesanan) = :tgl AND status_pesanan != 'batal'";
$stmt_yesterday = $db->prepare($query_yesterday);
$stmt_yesterday->bindParam(':tgl', $yesterday);
$stmt_yesterday->execute();
$yesterday_data = $stmt_yesterday->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .comparison-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            margin-top: 0.5rem;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
        }

        .table td {
            border: none;
            vertical-align: middle;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
        }

        .popular-menu-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #667eea;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .page-header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-coffee"></i> Coffee Shop Staff
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-chart-line"></i> Laporan Harian</h1>
                        <p class="mb-0">Tanggal: <?php echo date('d F Y', strtotime($selected_date)); ?></p>
                    </div>
                    <div class="col-auto no-print">
                        <a href="dashboard.php" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Date Filter -->
            <div class="filter-section no-print">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5><i class="fas fa-filter"></i> Filter Tanggal</h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>"
                                max="<?php echo date('Y-m-d'); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Lihat
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stat-number text-primary"><?php echo $total_pesanan; ?></div>
                        <div class="stat-label">Total Pesanan</div>
                        <?php
                        $pesanan_diff = $total_pesanan - ($yesterday_data['total_pesanan'] ?? 0);
                        $badge_class = $pesanan_diff >= 0 ? 'bg-success' : 'bg-danger';
                        $icon = $pesanan_diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                        ?>
                        <div class="comparison-badge <?php echo $badge_class; ?> text-white">
                            <i class="fas <?php echo $icon; ?>"></i> <?php echo abs($pesanan_diff); ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon text-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-number text-success">Rp
                            <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Pendapatan</div>
                        <?php
                        $pendapatan_diff = $total_pendapatan - ($yesterday_data['total_pendapatan'] ?? 0);
                        $badge_class = $pendapatan_diff >= 0 ? 'bg-success' : 'bg-danger';
                        $icon = $pendapatan_diff >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                        ?>
                        <div class="comparison-badge <?php echo $badge_class; ?> text-white">
                            <i class="fas <?php echo $icon; ?>"></i> Rp
                            <?php echo number_format(abs($pendapatan_diff), 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number text-warning"><?php echo $status_count['menunggu']; ?></div>
                        <div class="stat-label">Pesanan Menunggu</div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stat-card text-center">
                        <div class="stat-icon text-info">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number text-info"><?php echo $status_count['selesai']; ?></div>
                        <div class="stat-label">Pesanan Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Popular Menu -->
            <?php if (!empty($menu_popular)): ?>
                <div class="chart-container">
                    <h5><i class="fas fa-star"></i> Menu Terlaris</h5>
                    <div class="row">
                        <?php foreach ($menu_popular as $index => $menu): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="popular-menu-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($menu['nama_menu']); ?></h6>
                                            <small class="text-muted"><?php echo $menu['total_order']; ?> pesanan</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-primary fw-bold"><?php echo $menu['total_terjual']; ?></div>
                                            <small class="text-muted">terjual</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar"
                                            style="width: <?php echo ($menu['total_terjual'] / $menu_popular[0]['total_terjual']) * 100; ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status Distribution -->
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5><i class="fas fa-chart-pie"></i> Distribusi Status Pesanan</h5>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <div class="text-warning fs-2"><?php echo $status_count['menunggu']; ?></div>
                                    <div class="text-muted">Menunggu</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <div class="text-info fs-2"><?php echo $status_count['dikonfirmasi']; ?></div>
                                    <div class="text-muted">Dikonfirmasi</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <div class="text-info fs-2"><?php echo $status_count['diproses']; ?></div>
                                    <div class="text-muted">Diproses</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <div class="text-info fs-2"><?php echo $status_count['siap']; ?></div>
                                    <div class="text-muted">Siap</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <div class="text-success fs-2"><?php echo $status_count['selesai']; ?></div>
                                    <div class="text-muted">Selesai</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3">
                                    <div class="text-danger fs-2"><?php echo $status_count['batal']; ?></div>
                                    <div class="text-muted">Batal</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="chart-container">
                        <h5><i class="fas fa-chart-bar"></i> Perbandingan Hari Sebelumnya</h5>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="p-3">
                                    <div class="text-muted mb-2">Kemarin</div>
                                    <div class="fw-bold"><?php echo $yesterday_data['total_pesanan'] ?? 0; ?> pesanan
                                    </div>
                                    <div class="text-success">Rp
                                        <?php echo number_format($yesterday_data['total_pendapatan'] ?? 0, 0, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3">
                                    <div class="text-muted mb-2">Hari ini</div>
                                    <div class="fw-bold"><?php echo $total_pesanan; ?> pesanan</div>
                                    <div class="text-success">Rp
                                        <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Table -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5><i class="fas fa-list"></i> Detail Pesanan</h5>
                    <div class="text-muted">
                        Total: <?php echo $total_pesanan; ?> pesanan
                    </div>
                </div>

                <?php if (!empty($pesanan_list)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Menu</th>
                                    <th>Jumlah</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pesanan_list as $index => $pesanan): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo date('H:i', strtotime($pesanan['tgl_pesanan'])); ?></td>
                                        <td><?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></td>
                                        <td><?php echo htmlspecialchars($pesanan['nama_menu']); ?></td>
                                        <td><?php echo $pesanan['jumlah']; ?></td>
                                        <td>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'menunggu' => 'bg-warning text-dark',   // Kuning
                                                'dikonfirmasi' => 'bg-info text-white',     // Biru muda
                                                'diproses' => 'bg-secondary text-white',// Abu-abu
                                                'siap' => 'bg-primary text-white',  // Biru
                                                'selesai' => 'bg-success text-white',  // Hijau
                                                'batal' => 'bg-danger text-white'    // Merah
                                            ];



                                            $status_class = $status_classes[$pesanan['status_pesanan']] ?? 'bg-secondary text-white';
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <?php echo ucfirst($pesanan['status_pesanan']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">Tidak ada pesanan pada tanggal ini</h5>
                        <p class="text-muted">Pilih tanggal lain untuk melihat data pesanan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>