<?php
session_start();

// Check if staff is logged in
if(!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Menu.php';

$database = new Database();
$db = $database->getConnection();

$menu = new Menu($db);

// Get all menu items
$stmt = $menu->read();
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get menu categories
$categories = [];
foreach($menu_items as $item) {
    if (!in_array($item['kategori'], $categories)) {
        $categories[] = $item['kategori'];
    }
}

// Filter by category if selected
$selected_category = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$filtered_items = $menu_items;
if ($selected_category) {
    $filtered_items = array_filter($filtered_items, function($item) use ($selected_category) {
        return $item['kategori'] == $selected_category;
    });
}

if ($search) {
    $filtered_items = array_filter($filtered_items, function($item) use ($search) {
        return stripos($item['nama_menu'], $search) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Menu - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .menu-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-image {
            height: 200px;
            object-fit: cover;
        }
        .price-tag {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .search-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="view_menu.php">Lihat Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesan_minuman.php">Kelola Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="laporan_harian.php">Laporan Harian</a>
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
                <h1><i class="fas fa-list"></i> Daftar Menu</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Lihat Menu</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-container">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Menu</label>
                    <input type="text" class="form-control" name="search" placeholder="Nama menu atau deskripsi..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selected_category == $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($category)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <a href="view_menu.php" class="btn btn-outline-light">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        
        <!-- Menu Items -->
        <div class="row">
            <?php if (empty($filtered_items)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Tidak ada menu yang ditemukan</h4>
                        <p>Silakan coba dengan kata kunci atau kategori yang berbeda.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($filtered_items as $item): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card menu-card h-100">
                            <div class="position-relative">
                                
                                
                                <span class="badge bg-secondary category-badge">
                                    <?php echo ucfirst($item['kategori']); ?>
                                </span>
                                
                                <?php if ($item['kode_menu'] <= 0): ?>
                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.7);">
                                        <span class="badge bg-danger fs-6">STOK HABIS</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['nama_menu']); ?></h5>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="price-tag">
                                        Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                    </span>
                                    <span class="badge bg-<?php echo $item['stok'] > 5 ? 'success' : ($item['stok'] > 0 ? 'warning' : 'danger'); ?>">
                                        Stok: <?php echo $item['stok']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>