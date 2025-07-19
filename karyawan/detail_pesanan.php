<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $kode_pesanan = $_POST['kode_pesanan'] ?? '';
    $status_baru = $_POST['status_baru'] ?? '';

    if ($kode_pesanan && $status_baru) {
        $update_query = "UPDATE pesanan SET status_pesanan = :status WHERE kode_pesanan = :kode";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':status', $status_baru);
        $update_stmt->bindParam(':kode', $kode_pesanan);

        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Status pesanan berhasil diupdate!";
        } else {
            $_SESSION['error_message'] = "Gagal mengupdate status pesanan.";
        }
    } else {
        $_SESSION['error_message'] = "Data yang dikirim tidak lengkap.";
    }
}

// Filter parameters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$query = "SELECT p.*, m.nama_menu 
          FROM pesanan p 
          LEFT JOIN menu m ON p.kode_menu = m.kode_menu 
          WHERE 1=1";

$params = [];

if ($status_filter) {
    $query .= " AND p.status_pesanan = :status";
    $params[':status'] = $status_filter;
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

// Get available statuses
$status_options = [
    'pending' => 'Pending',
    'proses' => 'Diproses',
    'selesai' => 'Selesai',
    'batal' => 'Dibatalkan'
];
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
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 20px;
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
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_menu.php">Lihat Menu</a></li>
                    <li class="nav-item"><a class="nav-link active" href="pesan_minuman.php">Kelola Pesanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporan_harian.php">Laporan Harian</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['staff_nama'] ?? 'Staff'); ?>
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

    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-md-12">
                <h1><i class="fas fa-tasks"></i> Kelola Pesanan</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kelola Pesanan</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="card filter-card">
            <div class="card-body">
                <h5><i class="fas fa-filter"></i> Filter Pesanan</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Cari Pesanan</label>
                        <input type="text" class="form-control" name="search"
                               placeholder="Kode, nama pelanggan, atau menu..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <?php foreach ($status_options as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>" <?php echo $status_filter === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date"
                               value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="col-md-3 d-grid gap-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="handle_orders.php" class="btn btn-outline-light">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Daftar Pesanan (<?php echo count($orders); ?> pesanan)</h5>
                <a href="pesan_minuman.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Tambah Pesanan Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h4>Tidak ada pesanan ditemukan</h4>
                        <p>Silakan ubah filter atau tambah pesanan baru.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
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
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                // Pastikan semua key ada agar aman
                                $kode = isset($order['kode_pesanan']) ? $order['kode_pesanan'] : '';
                                $pelanggan = isset($order['nama_pelanggan']) ? $order['nama_pelanggan'] : '';
                                $menu = isset($order['nama_menu']) ? $order['nama_menu'] : '';
                                $jumlah = isset($order['jumlah']) ? (int)$order['jumlah'] : 0;
                                $total_harga = isset($order['total_harga']) ? (int)$order['total_harga'] : 0;
                                $tgl_pesanan_raw = isset($order['tgl_pesanan']) ? $order['tgl_pesanan'] : '';
                                $tgl_pesanan = $tgl_pesanan_raw ? date('d/m/Y H:i', strtotime($tgl_pesanan_raw)) : '';
                                $status_pesanan = isset($order['status_pesanan']) ? $order['status_pesanan'] : '';

                                // Tentukan kelas badge status
                                $status_class = 'secondary';
                                switch ($status_pesanan) {
                                    case 'pending': $status_class = 'warning'; break;
                                    case 'proses': $status_class = 'info'; break;
                                    case 'selesai': $status_class = 'success'; break;
                                    case 'batal': $status_class = 'danger'; break;
                                }

                                $status_label = $status_options[$status_pesanan] ?? 'Tidak diketahui';
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($kode); ?></strong></td>
                                    <td><i class="fas fa-user"></i> <?php echo htmlspecialchars($pelanggan); ?></td>
                                    <td><i class="fas fa-coffee"></i> <?php echo htmlspecialchars($menu); ?></td>
                                    <td><span class="badge bg-info"><?php echo number_format($jumlah); ?></span></td>
                                    <td><strong>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></strong></td>
                                    <td><small class="text-muted"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($tgl_pesanan); ?></small></td>
                                    <td><span class="badge bg-<?php echo $status_class; ?> status-badge"><?php echo htmlspecialchars($status_label); ?></span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Update Status Button -->
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#updateStatusModal"
                                                    onclick="setUpdateModal('<?php echo addslashes($kode); ?>', '<?php echo addslashes($status_pesanan); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- View Details Button -->
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal"
                                                    onclick="showDetail('<?php echo addslashes($kode); ?>', '<?php echo addslashes(htmlspecialchars($pelanggan)); ?>', '<?php echo addslashes(htmlspecialchars($menu)); ?>', '<?php echo addslashes($jumlah); ?>', '<?php echo addslashes($total_harga); ?>', '<?php echo addslashes($tgl_pesanan); ?>', '<?php echo addslashes(htmlspecialchars($status_label)); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Statistics -->
        <div class="row mt-4">
            <?php
            // Hitung status berdasarkan seluruh data $orders
            $stats = [
                'pending' => 0,
                'proses' => 0,
                'selesai' => 0,
                'batal' => 0
            ];
            foreach ($orders as $o) {
                $st = $o['status_pesanan'] ?? '';
                if (isset($stats[$st])) {
                    $stats[$st]++;
                }
            }
            ?>

            <div class="col-md-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-clock"></i> Pending</h5>
                        <h3><?php echo $stats['pending']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-cog"></i> Diproses</h5>
                        <h3><?php echo $stats['proses']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-check"></i> Selesai</h5>
                        <h3><?php echo $stats['selesai']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-times"></i> Dibatalkan</h5>
                        <h3><?php echo $stats['batal']; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateStatusModalLabel">Update Status Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="kode_pesanan" id="update_kode_pesanan" required>
                        <div class="mb-3">
                            <label class="form-label">Status Baru</label>
                            <select class="form-select" name="status_baru" id="update_status" required>
                                <?php foreach ($status_options as $value => $label): ?>
                                    <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Kode Pesanan:</strong> <span id="detail_kode"></span></p>
                            <p><strong>Nama Pelanggan:</strong> <span id="detail_pelanggan"></span></p>
                            <p><strong>Menu:</strong> <span id="detail_menu"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Jumlah:</strong> <span id="detail_jumlah"></span></p>
                            <p><strong>Total Harga:</strong> <span id="detail_total"></span></p>
                            <p><strong>Tanggal:</strong> <span id="detail_tanggal"></span></p>
                            <p><strong>Status:</strong> <span id="detail_status"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setUpdateModal(kode_pesanan, current_status) {
            document.getElementById('update_kode_pesanan').value = kode_pesanan;
            document.getElementById('update_status').value = current_status;
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

        // Auto-hide alerts after 5 seconds
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
</body>

</html>
