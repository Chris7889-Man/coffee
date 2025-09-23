<?php
session_start();

date_default_timezone_set('Asia/Makassar');

// Cek login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Pesanan.php';
require_once __DIR__ . '/../classes/Menu.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();

$pesanan = new Pesanan($db);
$menu = new Menu($db);

// Total data
$total_pesanan = $pesanan->read()->rowCount();
$total_menu = $menu->read()->rowCount();

// OPTIONAL: Contoh hitungan data lain manual, Anda ganti dengan query sesuai tabel
$jumlah_pelanggan = 12; // Ganti hasil query customers
// Hitung jumlah pembeli selesai hari ini
$today = date('Y-m-d');
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

// HITUNG JUMLAH ADMIN DARI DATABASE
$stmtUserCount = $db->prepare("SELECT COUNT(*) AS total_admin FROM admin");
$stmtUserCount->execute();
$rowUserCount = $stmtUserCount->fetch(PDO::FETCH_ASSOC);
$jumlah_user = $rowUserCount['total_admin'] ?? 0;

// Hitung jumlah staff dari tabel staff
$stmtStaffCount = $db->prepare("SELECT COUNT(*) AS total_staff FROM staff");
$stmtStaffCount->execute();
$rowStaffCount = $stmtStaffCount->fetch(PDO::FETCH_ASSOC);
$jumlah_staff = $rowStaffCount['total_staff'] ?? 0;



// Data pesanan terbaru (maksimum 5)
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
My name is<span class="ms-2 fw-normal"><?= htmlspecialchars($_SESSION['admin_nama']) ?></span>, welcome to the dashboard of Vibescoffee

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
        }

        .sidebar {
            background: #232a34;
            color: #fff;
            min-height: 100vh;
            padding: 30px 10px 40px 15px;
            display: flex;
            flex-direction: column;



        }

        .sidebar .nav-link {
            color: #bbb;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link:focus {
            background: #1e90ff;
            color: #fff !important;
        }

        .sidebar .nav-link:hover {
            color: #fff;
        }

        .sidebar .user-info {
            border-top: 2px solid #333;
            padding-top: 14px;
            font-size: 14px;
        }

        .headerbar {
            background: #fff;
            border-bottom: 1px solid #eaeaea;
            padding: 20px 30px 18px 30px;
        }

        .card-stat {
            border-radius: 12px;
            transition: transform 0.15s;
        }

        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(30, 90, 160, 0.07);
        }

        .table thead {
            background: #343a40;
            color: #fff;
        }

        .table-hover tbody tr:hover {
            background-color: #eef2f7;
        }

        @media (max-width: 991px) {
            .sidebar {
                min-width: 60px;
                text-align: center;
                padding: 10px 2px;
            }

            .sidebar h4,
            .sidebar .user-info {
                display: none;
            }

            .sidebar .nav-link {
                font-size: 18px;
                color: #ddd;
            }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- SIDEBAR -->
        <nav class="sidebar flex-shrink-0">
            <h4 class="mb-4"><i class="fas fa-cash-register"></i> Point Of Sale</h4>
            <ul class="nav nav-pills flex-column mb-4">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i
                            class="fas fa-home me-2"></i>Dashboard</a></li>
                <li><a href="view_menu.php" class="nav-link"><i class="fas fa-utensils"></i><span
                            class="ms-2 d-none d-lg-inline">Menu</span></a></li>

                <li><a href="manage_menu.php" class="nav-link"><i class="fas fa-mug-hot me-2"></i>Kelola Menu</a></li>
                <li><a href="manage_orders.php" class="nav-link"><i class="fas fa-cart-shopping me-2"></i>Kelola
                        Pesanan</a></li>
                <li><a href="detail_pesanan.php" class="nav-link"><i class="fas fa-list"></i><span
                            class="ms-2 d-none d-lg-inline">Detail Pesanan</span></a></li>
                <li><a href="contoh.php" class="nav-link"><i class="fas fa-dollar-sign"></i><span
                            class="ms-2 d-none d-lg-inline">Keuangan</span></a></li>
                <li><a href="laporan_harian.php" class="nav-link"><i class="fas fa-file-lines me-2"></i>Laporan</a></li>
                <li><a href="manage_admin.php" class="nav-link"><i class="fas fa-user-shield me-2"></i>Admin</a>
                </li>
                <li><a href="data_staff.php" class="nav-link"><i class="fas fa-users-cog me-2"></i>Staff</a></li>
                <?php if (!empty($_SESSION['is_super_admin'])): ?>

                <?php endif; ?>
                <li><a href="https://github.com/Chris7889-Man/coffee" class="nav-link"><i
                            class="fab fa-github me-2"></i>BackApp</a></li>
            </ul>
            <div class="user-info mt-auto dropdown">
                <a href="" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                    style="cursor:pointer;">
                    <i class="fas fa-user-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?>
                </a>
                <ul class="dropdown-menu" aria-labelledby="userDropdown" style="min-width: 150px;">
                    <li><a class="dropdown-item" href="profil_my.php">Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-warning" href="logout.php">Logout</a></li>
                </ul>
            </div>

            <!-- Jangan lupa sertakan Bootstrap JS Bundle di akhir body -->


        </nav>
        <!-- /SIDEBAR -->

        <div class="flex-grow-1">
            <!-- HEADERBAR -->
            <div class="headerbar d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Vibescoffee</h2>
                    <span class="text-muted"><?= date('l, d F Y | H:i:s'); ?></span>
                </div>
                <div>
                    <?php $foto_admin = $_SESSION['admin_foto'] ?? 'default.jpg'; ?>
                    <img src="/assets/<?= htmlspecialchars($foto_admin) ?>" alt="Foto Admin" width="54" height="54"
                        class="rounded-circle border border-primary me-2" style="object-fit: cover;">
                    <span class="ms-2 fw-bold"><?= htmlspecialchars($_SESSION['admin_nama']) ?></span>
                </div>
            </div>
            <!-- /HEADERBAR -->

            <main class="container-fluid mt-4">
                <!-- INFO CARDS -->
                <div class="row g-4 mb-4">

                    <div class="col-md-3 col-sm-6">
                        <div class="card card-stat bg-primary text-white shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <h5>Total Pesanan</h5>
                                <div class="display-6 fw-bold"><?= $total_pesanan ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="card card-stat bg-success text-white shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-coffee fa-2x mb-2"></i>
                                <h5>Total Menu</h5>
                                <div class="display-6 fw-bold"><?= $total_menu ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="card card-stat bg-warning text-dark shadow-sm text-center p-3"
                            style="cursor:pointer" id="jumlahPembeli">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h6>Jumlah Pembeli Selesai</h6>
                            <p class="display-6 fw-bold mb-0"><?= $jumlah_pembeli_selesai ?></p>
                        </div>
                    </div>

                    <!-- Gabungkan kartu jumlah admin dan staff dalam satu baris yang sama -->
                    <div class="col-md-3 col-sm-6 d-flex gap-3">
                         <a href="data_staff.php" class="text-decoration-none flex-fill">
                            <div class="card card-stat bg-secondary text-white shadow-sm text-center h-10">
                                <div class="card-body">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h6 class="mb-1">Jumlah Staff</h6>
                                    <div class="fw-bold fs-4"><?= $jumlah_staff ?></div>
                                </div>
                            </div>
                        </a>

                        <a href="manage_admin.php" class="text-decoration-none flex-fill">
                            <div class="card card-stat bg-secondary text-white shadow-sm text-center h-10">
                                <div class="card-body">
                                    <i class="fas fa-user-shield fa-2x mb-2"></i>
                                    <h6 class="mb-1">Jumlah Admin</h6>
                                    <div class="fw-bold fs-6"><?= $jumlah_user ?></div>
                                </div>
                            </div>
                        </a>

                    </div>

                </div>
            </main>

            <!-- /INFO CARDS -->

            <!-- TABEL PESANAN TERBARU -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Pesanan Terbaru</span>
                    <a href="manage_orders.php" class="btn btn-light btn-sm"><i class="fas fa-tasks me-1"></i> Lihat
                        Semua</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Kode Pesanan</th>
                                <th>Nama Pelanggan</th>
                                <th>Jumlah Pesanan</th>
                                <th>Total Harga</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_orders) === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data pesanan</td>
                                </tr>
                            <?php else:
                                foreach ($recent_orders as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['kode_pesanan']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelanggan'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($row['jumlah'] ?? '-'); ?></td>
                                        <td>Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.'); ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_pesanan'])); ?></td>
                                        <td><?= date('H:i', strtotime($row['tgl_pesanan'])); ?></td>
                                        <td><?= htmlspecialchars($row['status_pesanan'] ?? '-'); ?></td>
                                        <td>
                                            <a href="edit_orders.php?kode_pesanan=<?= urlencode($row['kode_pesanan']); ?>"
                                                class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="delete_orders.php?kode_pesanan=<?= urlencode($row['kode_pesanan']); ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?')"><i
                                                    class="fas fa-trash-alt"></i></a>
                                            <a href="detail_pesanan.php?kode_pesanan=<?= urlencode($row['kode_pesanan']) ?>"
                                                class="btn btn-sm btn-primary" title="Detail Pesanan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /TABEL PESANAN TERBARU -->
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>