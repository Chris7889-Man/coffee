<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
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

// Count total orders
$stmt = $pesanan->read();
$total_pesanan = $stmt->rowCount();

// Count total menu items
$stmt = $menu->read();
$total_menu = $stmt->rowCount();

// Recent orders (ambil 5 terbaru)
$recent_stmt = $pesanan->read();
$recent_orders = [];
$count = 0;
while ($row = $recent_stmt->fetch(PDO::FETCH_ASSOC)) {
    $recent_orders[] = $row;
    $count++;
    if ($count >= 5)
        break;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .img-profile {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease;
        }

        .img-profile:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-coffee"></i> Coffee Shop Admin
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
                        <a class="nav-link" href="manage_menu.php">Kelola Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_orders.php">Kelola Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan_harian.php">Laporan Harian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="data_staff.php">Kelola Staff</a>
                    </li>
                    <?php if ($_SESSION['is_super_admin']): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_admin.php">Kelola Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['admin_nama']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
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
                <h1>Dashboard</h1>
                <p class="lead">Selamat datang, <?php echo $_SESSION['admin_nama']; ?>!</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i> Total Pesanan
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo $total_pesanan; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">
                        <i class="fas fa-coffee"></i> Total Menu
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo $total_menu; ?></h4>
                    </div>
                </div>
                <div>

                </div>
            </div>
        </div>

        <!-- Admin ini unutuk percobaan yang dapat dikenal sebagai gambar -->

        <div class="row mt-4">
            <!-- Card Profil Admin -->
            <div class="col-md-3">
                <div class="card mb-3 text-center">
                    <div class="card-body">
                        <img src="../assets/admincoffe.jpg" alt="Admin Profile" class="rounded-circle mb-2">
                        <h5 class="card-title">Rahmadana</h5>
                        <p class="card-text">Selamat datang Rahmadana selaku Owner</p>
                    </div>
                </div>
            </div>

            <!-- Total Pesanan -->
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i> Total Pesanan
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo $total_pesanan; ?></h4>
                    </div>
                </div>
            </div>

            <!-- Total Menu -->
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">
                        <i class="fas fa-coffee"></i> Total Menu
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo $total_menu; ?></h4>
                    </div>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>