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

$kode_menu = $_GET['kode_menu'] ?? '';

// Fungsi format tanggal (tanggal bulan tahun, tanpa jam)
function formatTanggalOnly($datetime)
{
    $tanggal = strtotime($datetime);
    $bulan_array = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('d', $tanggal);
    $bulan = $bulan_array[(int)date('m', $tanggal)] ?? '';
    $tahun = date('Y', $tanggal);
    return "$tgl $bulan $tahun";
}

// Fungsi format jam (jam:menit:detik)
function formatJamOnly($datetime)
{
    return date('H:i:s', strtotime($datetime));
}

$message = '';
$error = '';

// Tangani aksi reset history (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_history') {
    $password = $_POST['password'] ?? '';
    $tgl_mulai = $_POST['tgl_mulai'] ?? '';
    $tgl_akhir = $_POST['tgl_akhir'] ?? '';

    // Ganti password di bawah ini sesuai yang diinginkan (harus rahasia!)
    $password_benar = 'admin123';

    if ($password !== $password_benar) {
        $error = "Password reset salah!";
    } elseif (!$tgl_mulai || !$tgl_akhir) {
        $error = "Tanggal mulai dan tanggal akhir harus diisi!";
    } else {
        // Validasi format tanggal dan bentuk datetime untuk DB
        $tgl_mulai_db = date('Y-m-d 00:00:00', strtotime($tgl_mulai));
        $tgl_akhir_db = date('Y-m-d 23:59:59', strtotime($tgl_akhir));

        if ($tgl_mulai_db > $tgl_akhir_db) {
            $error = "Tanggal mulai harus sebelum atau sama dengan tanggal akhir.";
        } else {
            try {
                if ($kode_menu) {
                    $queryDel = "DELETE FROM stok_history WHERE kode_menu = :kode_menu AND tgl_update BETWEEN :tgl_mulai AND :tgl_akhir";
                    $stmtDel = $db->prepare($queryDel);
                    $stmtDel->bindParam(':kode_menu', $kode_menu);
                    $stmtDel->bindParam(':tgl_mulai', $tgl_mulai_db);
                    $stmtDel->bindParam(':tgl_akhir', $tgl_akhir_db);
                } else {
                    $queryDel = "DELETE FROM stok_history WHERE tgl_update BETWEEN :tgl_mulai AND :tgl_akhir";
                    $stmtDel = $db->prepare($queryDel);
                    $stmtDel->bindParam(':tgl_mulai', $tgl_mulai_db);
                    $stmtDel->bindParam(':tgl_akhir', $tgl_akhir_db);
                }
                $stmtDel->execute();
                $message = "Data history berhasil dihapus untuk rentang tanggal yang dipilih.";
            } catch (Exception $e) {
                $error = "Gagal menghapus data: " . $e->getMessage();
            }
        }
    }
}

// Ambil data history stok
if ($kode_menu) {
    $query = "SELECT * FROM stok_history WHERE kode_menu = :kode_menu ORDER BY tgl_update DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':kode_menu', $kode_menu);
    $stmt->execute();
} else {
    $query = "SELECT * FROM stok_history ORDER BY tgl_update DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
}
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Riwayat Perubahan Stok<?= $kode_menu ? " - Menu: " . htmlspecialchars($kode_menu) : "" ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<tr></tr>
    <style>
        /* Background dan style body */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c2f33, #23272a);
            color: #eaeaea;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Container styling */
        .container {
            max-width: 1100px;
        }

        /* Heading style */
        h2 {
            font-weight: 700;
            color: #f5f6f7;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
            margin-bottom: 1.5rem;
        }

        /* Tombol Kembali */
        a.btn-warning {
            background-color: #ffa600;
            border-color: #ffa600;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        a.btn-warning:hover {
            background-color: #cc8500;
            border-color: #cc8500;
            color: #ffffff;
        }

        /* Table Responsive Wrapper */
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
            background-color: #1e2124;
            padding: 1rem;
        }

        /* Style tabel */
        table.table {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.35);
            background: #fff;
            color: #222;
        }

        /* Header tabel, warna dan teks */
        table.table thead {
            background: #343a40;
            color: #f8f9fa;
            font-size: 0.9rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 8px 8px 0 0;
        }

        /* Baris tabel */
        table.table tbody tr {
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.25s ease;
        }

        /* Baris hover */
        table.table tbody tr:hover {
            background-color: #f2f2f2;
            color: #222;
        }

        /* Cell padding */
        table.table th,
        table.table td {
            padding: 12px 15px;
            vertical-align: middle;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Pesan alert custom */
        .alert-success {
            background-color: #2ecc71;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(46, 204, 113, 0.5);
        }

        .alert-danger {
            background-color: #e74c3c;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(231, 76, 60, 0.5);
        }

        /* Reset History Section */
        .reset-history-section {
            background-color: #8b0000;
            color: #fff;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(255, 0, 0, 0.6);
            margin-top: 4rem;
            position: relative;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .reset-history-section h4 {
            margin-bottom: 1.5rem;
            font-size: 1.7rem;
        }

        /* Warning icon and text container */
        .reset-warning {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            gap: 10px;
        }

        /* Icon style - SVG heart and warning */
        .reset-warning svg {
            fill: #ff4444;
            width: 24px;
            height: 24px;
            animation: pulse 2s infinite;
            filter: drop-shadow(0 0 5px #ff4444);
        }

        /* Animasi pulse untuk icon */
        @keyframes pulse {
            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.15);
                opacity: 0.8;
            }
        }

        /* Style form inputs */
        .reset-history-section form .form-control {
            border-radius: 0.3rem;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            color: #222;
            transition: box-shadow 0.3s ease;
        }

        .reset-history-section form .form-control:focus {
            box-shadow: 0 0 8px #ff4949;
            outline: none;
        }

        /* Tombol Reset */
        .reset-history-section form button.btn-danger {
            background: linear-gradient(45deg, #ff3b3b, #b22222);
            border: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: background 0.4s ease-in-out;
            box-shadow: 0 0 15px rgb(255 59 59 / 0.7);
        }

        .reset-history-section form button.btn-danger:hover {
            background: linear-gradient(45deg, #b22222, #ff3b3b);
            box-shadow: 0 0 20px rgb(255 80 80 / 0.9);
        }

        /* Responsive Form Columns on small devices */
        @media (max-width: 575.98px) {
            .reset-history-section form .col-md-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>Riwayat Perubahan Stok<?= $kode_menu ? " - Menu: " . htmlspecialchars($kode_menu) : "" ?></h2>
        <a href="manage_menu.php" class="btn btn-warning mb-3">Kembali ke Kelola Menu</a>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Menu</th>
                        <th>Stok Lama</th>
                        <th>Stok Baru</th>
                        <th>Tanggal Update</th>
                        <th>Jam Update</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="7" class="text-center fw-semibold text-muted">Belum ada riwayat stok.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $idx => $row): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><?= htmlspecialchars($row['kode_menu']) ?></td>
                                <td><?= (int)$row['stok_lama'] ?></td>
                                <td><?= (int)$row['stok_baru'] ?></td>
                                <td><?= formatTanggalOnly($row['tgl_update']) ?></td>
                                <td><?= formatJamOnly($row['tgl_update']) ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

</body>

</html>
