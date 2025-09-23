<?php
session_start();
// Cek login admin biasa
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php"); // redirect ke halaman login admin biasa
    exit();
}

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Mapping kode status pendek ke nama lengkap
$status_full_names = [
    'pending'       => 'Menunggu',
    'dikonfirmasi'  => 'Dikonfirmasi',
    'proses'        => 'Diproses',
    'siap'          => 'Siap',
    'selesai'       => 'Selesai',
    'batal'         => 'Batal'
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

// Ambil parameter filter dari URL
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Konversi kode status pendek jadi nama lengkap untuk filter
$filter_status_nama = '';
if ($status_filter && isset($status_full_names[$status_filter])) {
    $filter_status_nama = $status_full_names[$status_filter];
}

// Query mengambil pesanan dengan filter dinamis
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

// Array status options untuk dropdown (kode pendek => nama lengkap)
$status_options = $status_full_names;

// Warna badge status sesuai kode pendek
$status_colors = [
    'pending'       => 'warning',
    'dikonfirmasi'  => 'secondary',
    'proses'        => 'info',
    'siap'          => 'primary',
    'selesai'       => 'success',
    'batal'         => 'danger'
];

// Fungsi bantu mendapatkan kode pendek dari nama lengkap status
function getStatusKeyByValue($array, $value) {
    return array_search($value, $array);
}

// Hitung total jumlah dan total harga keseluruhan
$total_jumlah = 0;
$total_harga = 0;
$total_jumlah_batal = 0;
foreach ($orders as $order) {
    $jumlah = (int)$order['jumlah'];
    $harga = (int)$order['total_harga'];
    $status_db = strtolower($order['status_pesanan'] ?? '');

    $total_jumlah += $jumlah;
    $total_harga += $harga;

    if ($status_db === 'batal') {
        $total_jumlah_batal += $jumlah;
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
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .table-responsive-scroll {
        max-height: 450px;
        overflow-y: auto;
    }
    .table-danger {
        background-color: #f8d7da !important;
    }
    .total-jumlah-batal {
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
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-coffee"></i> Coffee Shop Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto"></ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?>
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
    <!-- Tombol Kembali ke Dashboard -->
    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-warning">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Alert Messages -->
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

    <!-- Filter -->
    <div class="card filter-card">
        <div class="card-body">
            <h5><i class="fas fa-filter"></i> Filter Pesanan</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari Pesanan</label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Kode, nama pelanggan, atau menu..."
                           value="<?= htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <?php foreach ($status_options as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ($status_filter === $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($date_filter); ?>">
                </div>
                <div class="col-md-3 d-grid gap-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="detail_pesanan.php" class="btn btn-outline-light">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Pesanan -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-list"></i> Daftar Pesanan (<?= count($orders) ?> pesanan)</h5>
            <a href="manage_orders.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tambah Pesanan Baru
            </a>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): 
                        $kode = $order['kode_pesanan'] ?? '';
                        $pelanggan = $order['nama_pelanggan'] ?? '';
                        $menu = $order['nama_menu'] ?? '';
                        $jumlah = (int)($order['jumlah'] ?? 0);
                        $total_harga_order = (int)($order['total_harga'] ?? 0);
                        $tgl_pesanan_raw = $order['tgl_pesanan'] ?? '';
                        $tgl_pesanan = $tgl_pesanan_raw ? date('d/m/Y H:i', strtotime($tgl_pesanan_raw)) : '';
                        $status_pesanan_nama = $order['status_pesanan'] ?? '';

                        // Cari kode pendek dari nama lengkap status
                        $status_pesanan = getStatusKeyByValue($status_options, $status_pesanan_nama);
                        // Default warna badge jika tidak ditemukan status
                        $status_class = $status_colors[$status_pesanan] ?? 'secondary';
                        $status_label = $status_options[$status_pesanan] ?? 'Tidak diketahui';
                        ?>
                        <tr<?= strtolower($status_label) === 'batal' ? ' class="table-danger"' : '' ?>>
                            <td><strong><?= htmlspecialchars($kode) ?></strong></td>
                            <td><i class="fas fa-user"></i> <?= htmlspecialchars($pelanggan) ?></td>
                            <td><i class="fas fa-coffee"></i> <?= htmlspecialchars($menu) ?></td>
                            <td><span class="badge bg-info"><?= number_format($jumlah) ?></span></td>
                            <td><strong>Rp <?= number_format($total_harga_order, 0, ',', '.') ?></strong></td>
                            <td><small class="text-muted"><i class="fas fa-calendar"></i> <?= htmlspecialchars($tgl_pesanan) ?></small></td>
                            <td><span class="badge bg-<?= $status_class ?> status-badge"><?= htmlspecialchars($status_label) ?></span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#updateStatusModal"
                                        onclick="setUpdateModal('<?= addslashes($kode) ?>', '<?= addslashes($status_pesanan) ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                        data-bs-target="#detailModal"
                                        onclick="showDetail('<?= addslashes($kode) ?>', '<?= addslashes(htmlspecialchars($pelanggan)) ?>', '<?= addslashes(htmlspecialchars($menu)) ?>', '<?= addslashes($jumlah) ?>', '<?= addslashes($total_harga_order) ?>', '<?= addslashes($tgl_pesanan) ?>', '<?= addslashes(htmlspecialchars($status_label)) ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total Keseluruhan</strong></td>
                            <td>
                                <?php if ($total_jumlah_batal > 0): ?>
                                    <span class="total-jumlah-batal"><?= number_format($total_jumlah) ?> (Batal: <?= number_format($total_jumlah_batal) ?>)</span>
                                <?php else: ?>
                                    <?= number_format($total_jumlah) ?>
                                <?php endif; ?>
                            </td>
                            <td><strong>Rp <?= number_format($total_harga, 0, ',', '.') ?></strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <a href="contoh.php" class="btn btn-info btn-sm">Keuangan</a>
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
                                <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
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
    // Auto-hide alerts after 5 seconds setTimeout menjalankan fungsi setelah waktu tertentu (5 detik).
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
