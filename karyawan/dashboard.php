<?php
session_start();

// Check if staff is logged in
if(!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Pesanan.php';
require_once __DIR__ . '/../classes/Menu.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$pesanan = new Pesanan($db);
$menu = new Menu($db);

// Count orders handled by this staff today
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as total FROM pesanan WHERE DATE(tgl_pesanan) = ?");
$stmt->execute([$today]);
$orders_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count total menu items
$stmt = $menu->read();
$total_menu = $stmt->rowCount();

// Recent orders (ambil 5 terbaru)
$recent_stmt = $db->prepare("SELECT * FROM pesanan ORDER BY tgl_pesanan DESC LIMIT 5");
$recent_stmt->execute();
$recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staff - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-stats {
            transition: transform 0.3s ease;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-coffee"></i> Coffee Shop Staff
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_menu.php">Lihat Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesan_minuman.php">Kelola Pesanan</a>
                    </li>
                
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['staff_nama']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Staff</h1>
                <p class="lead">Selamat datang, <?php echo $_SESSION['staff_nama']; ?>! (<?php echo $_SESSION['staff_jabatan']; ?>)</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3 card-stats">
                    <div class="card-header">
                        <i class="fas fa-calendar-day"></i> Pesanan Hari Ini
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo $orders_today; ?></h4>
                        <p class="card-text">Pesanan pada <?php echo date('d/m/Y'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3 card-stats">
                    <div class="card-header">
                        <i class="fas fa-coffee"></i> Total Menu
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo $total_menu; ?></h4>
                        <p class="card-text">Menu yang tersedia</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Pesanan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($recent_orders)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Belum ada pesanan hari ini.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode Pesanan</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_orders as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['kode_pesanan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                            <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['tgl_pesanan'])); ?></td>
                                            <td>
                                                <a href="detail_pesanan.php?kode_pesanan=<?= urlencode($row['kode_pesanan']); ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="handle_orders.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> Lihat Semua Pesanan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2024 Coffee Shop. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh page every 30 seconds to update order counts
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>