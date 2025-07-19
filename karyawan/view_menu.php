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
        return stripos($item['nama_menu'], $search) !== false || 
               stripos($item['deskripsi'] ?? '', $search) !== false;
    });
}

// Sort items by category and name
usort($filtered_items, function($a, $b) {
    if ($a['kategori'] == $b['kategori']) {
        return strcmp($a['nama_menu'], $b['nama_menu']);
    }
    return strcmp($a['kategori'], $b['kategori']);
});

$total_items = count($filtered_items);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lihat Menu - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        .menu-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 100%;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .menu-image {
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .price-tag {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
            font-size: 0.8rem;
        }
        .search-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .results-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .out-of-stock {
            position: relative;
            opacity: 0.7;
        }
        .out-of-stock::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.3);
            border-radius: 0.375rem;
            z-index: 1;
        }
        .stock-overlay {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }
        .description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .card-footer-custom {
            margin-top: auto;
            padding-top: 1rem;
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
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['staff_nama']); ?>
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
                        <li class="breadcrumb-item active" aria-current="page">Lihat Menu</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-container">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Menu</label>
                    <input type="text" class="form-control" name="search" placeholder="Nama menu atau deskripsi..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="kategori">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category); ?>" <?= $selected_category == $category ? 'selected' : ''; ?>>
                                <?= htmlspecialchars(ucfirst($category)); ?>
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

        <!-- Results Info -->
        <div class="results-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-info-circle text-primary"></i>
                    <strong>Menampilkan <?= $total_items; ?> item menu</strong>
                    <?php if ($search || $selected_category): ?>
                        <span class="text-muted">
                            <?php if ($search): ?>
                                untuk pencarian "<?= htmlspecialchars($search); ?>"
                            <?php endif; ?>
                            <?php if ($selected_category): ?>
                                kategori "<?= htmlspecialchars(ucfirst($selected_category)); ?>"
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="add_menu.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Menu
                    </a>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="row">
            <?php if (empty($filtered_items)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Tidak ada menu yang ditemukan</h4>
                        <p>Silakan coba dengan kata kunci atau kategori yang berbeda.</p>
                        <a href="view_menu.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Semua Menu
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($filtered_items as $item): ?>
                    <?php
                    $stok = isset($item['stok']) ? (int)$item['stok'] : 0;
                    $kategori = isset($item['kategori']) ? htmlspecialchars(ucfirst($item['kategori'])) : 'Unknown';
                    $nama_menu = isset($item['nama_menu']) ? htmlspecialchars($item['nama_menu']) : '-';
                    $harga = isset($item['harga']) ? (int)$item['harga'] : 0;
                    $deskripsi = isset($item['deskripsi']) ? htmlspecialchars($item['deskripsi']) : '';
                    $kode_menu = isset($item['kode_menu']) ? htmlspecialchars($item['kode_menu']) : '';
                    ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card menu-card <?= ($stok <= 0) ? 'out-of-stock' : ''; ?>">
                            <div class="position-relative">
                                <div class="menu-image">
                                    <i class="fas fa-utensils fa-3x text-muted"></i>
                                </div>

                                <span class="badge bg-secondary category-badge">
                                    <?= $kategori; ?>
                                </span>

                                <?php if ($stok <= 0): ?>
                                    <div class="stock-overlay">
                                        <span class="badge bg-danger fs-6">STOK HABIS</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title"><?= $nama_menu; ?></h5>

                                <?php if (!empty($deskripsi)): ?>
                                    <p class="description"><?= $deskripsi; ?></p>
                                <?php endif; ?>

                                <div class="card-footer-custom">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="price-tag">
                                            Rp <?= number_format($harga, 0, ',', '.'); ?>
                                        </span>
                                        <span class="badge bg-<?= $stok > 5 ? 'success' : ($stok > 0 ? 'warning' : 'danger'); ?>">
                                            Stok: <?= $stok; ?>
                                        </span>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="edit_menu.php?kode_menu=<?= $kode_menu; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="btn btn-primary position-fixed bottom-0 end-0 m-3" id="backToTop" style="display: none;" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Back to top button
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });

        // Auto-submit form on category change
        document.querySelector('select[name="kategori"]').addEventListener('change', function() {
            this.form.submit();
        });

        // Search on Enter key
        document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
