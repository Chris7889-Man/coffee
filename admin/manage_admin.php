<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan hanya super admin yang bisa mengakses
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['is_super_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$admin_access_secret_password = 'rahma123';

// Logout / tutup akses form tambah admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_access'])) {
    unset($_SESSION['admin_access_granted']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Proses verifikasi password untuk menampilkan form tambah admin
$password_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_access_password'])) {
    $input_password = $_POST['admin_access_password'];
    if ($input_password === $admin_access_secret_password) {
        $_SESSION['admin_access_granted'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $password_error = "Password salah, coba lagi.";
    }
}

// Cek apakah akses form tambah admin sudah diberikan
$show_add_admin_form = isset($_SESSION['admin_access_granted']) && $_SESSION['admin_access_granted'] === true;

// Proses tambah admin hanya jika akses sudah diberikan
$success_msg = '';
$error_msg = '';
if ($show_add_admin_form && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $nama_admin = trim($_POST['nama_admin'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $is_super_admin = isset($_POST['is_super_admin']) ? 1 : 0;

    if (empty($username) || empty($nama_admin) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Password dan konfirmasi password tidak sama.";
    } else {
        // Cek username/email sudah ada atau belum
        $stmt_check = $db->prepare("SELECT COUNT(*) FROM admin WHERE username = :username OR email = :email");
        $stmt_check->bindParam(':username', $username);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();
        $exists = $stmt_check->fetchColumn();


        // ===== Proses hapus admin (berdasarkan username + konfirmasi password) =====
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
            $username_to_delete = trim($_POST['username_to_delete'] ?? '');
            $delete_password = $_POST['delete_password'] ?? '';

            if ($username_to_delete === '' || $delete_password === '') {
                $delete_msg = "Username dan password konfirmasi wajib diisi.";
            } elseif ($delete_password !== $admin_access_secret_password) {
                $delete_msg = "Password konfirmasi salah.";
            } else {
                // Cek apakah admin ada
                $stmt_check_del = $db->prepare("SELECT username, is_super_admin, foto FROM admin WHERE username = :username LIMIT 1");
                $stmt_check_del->bindParam(':username', $username_to_delete);
                $stmt_check_del->execute();
                $row_del = $stmt_check_del->fetch(PDO::FETCH_ASSOC);

                if (!$row_del) {
                    $delete_msg = "Admin dengan username '$username_to_delete' tidak ditemukan.";
                } else {
                    // (Opsional) Cegah menghapus diri sendiri saat login
                    $current_username = $_SESSION['admin_username'] ?? '';
                    if ($username_to_delete === $current_username) {
                        $delete_msg = "Tidak bisa menghapus akun Anda sendiri saat sedang login.";
                    } else {
                        // Cegah menghapus super admin terakhir
                        if ((int) $row_del['is_super_admin'] === 1) {
                            $count_super = (int) $db->query("SELECT COUNT(*) FROM admin WHERE is_super_admin = 1")->fetchColumn();
                            if ($count_super <= 1) {
                                $delete_msg = "Tidak bisa menghapus super admin terakhir.";
                            }
                        }
                    }

                    // Jika aman, lanjut hapus
                    if ($delete_msg === '') {
                        // Hapus file foto jika bukan default
                        if (!empty($row_del['foto']) && $row_del['foto'] !== 'default.jpg') {
                            $foto_path = __DIR__ . '/../uploads/photos/' . $row_del['foto'];
                            if (is_file($foto_path)) {
                                @unlink($foto_path);
                            }
                        }

                        $stmt_delete = $db->prepare("DELETE FROM admin WHERE username = :username");
                        $stmt_delete->bindParam(':username', $username_to_delete);
                        if ($stmt_delete->execute()) {
                            $success_msg = "Admin dengan username '$username_to_delete' berhasil dihapus.";
                        } else {
                            $delete_msg = "Gagal menghapus admin. Coba lagi.";
                        }
                    }
                }
            }
        }








        // tempat pengaplotan gambar saat mengisi from tambah admin
        if ($exists > 0) {
            $error_msg = "Username atau email sudah terdaftar.";
        } else {
            // Mulai upload foto di sini
            $foto = 'default.jpg'; // nilai default jika tidak ada upload
            if (!empty($_FILES['foto']['name'])) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['foto']['type'], $allowed_types)) {
                    $upload_dir = __DIR__ . '/../uploads/photos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $filename = uniqid() . '_' . basename($_FILES['foto']['name']);
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                        $foto = $filename;
                    } else {
                        $error_msg = "Gagal upload foto.";
                    }
                } else {
                    $error_msg = "Format foto tidak didukung. Gunakan JPG, PNG, atau GIF.";
                }
            }



            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Tambahkan kolom created_at dan foto saat insert admin baru
            $stmt_insert = $db->prepare("INSERT INTO admin (username, nama_admin, email, password, is_super_admin, created_at, foto) VALUES (:username, :nama_admin, :email, :password, :is_super_admin, NOW(), :foto)");
            $stmt_insert->bindParam(':username', $username);
            $stmt_insert->bindParam(':nama_admin', $nama_admin);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':password', $password_hash);
            $stmt_insert->bindParam(':is_super_admin', $is_super_admin);
            $stmt_insert->bindParam(':foto', $foto);

            if ($stmt_insert->execute()) {
                $success_msg = "Admin baru berhasil ditambahkan.";
                // Reset input setelah sukses
                $_POST = [];
            } else {
                $error_msg = "Gagal menambahkan admin baru, coba lagi.";
            }
        }
    }
}




$delete_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin'])) {
    $username_to_delete = $_POST['username_to_delete'] ?? '';
    $delete_password = $_POST['delete_password'] ?? '';

    // Ambil password admin yang sedang login untuk verifikasi
    $stmt = $db->prepare("SELECT password FROM admin WHERE username = :username");
    $stmt->bindParam(':username', $_SESSION['admin_username'], PDO::PARAM_STR);
    $stmt->execute();
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password konfirmasi hapus admin
    if (!$currentAdmin || !password_verify($delete_password, $currentAdmin['password'])) {
        $delete_msg = "Password salah. Gagal menghapus admin.";
    } elseif ($username_to_delete === $_SESSION['admin_username']) {
        $delete_msg = "Anda tidak bisa menghapus akun Anda sendiri.";
    } else {
        // Proses hapus admin
        $stmt = $db->prepare("DELETE FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username_to_delete, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $success_msg = "Admin '$username_to_delete' berhasil dihapus.";
        } else {
            $delete_msg = "Gagal menghapus admin '$username_to_delete'.";
        }
    }
}

// Ambil ulang data admin setelah proses hapus agar data tampil lengkap
$stmt = $db->prepare("SELECT username, nama_admin, email, is_super_admin, created_at, foto FROM admin ORDER BY nama_admin ASC");
$stmt->execute();





?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Admin - Coffee Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            background: #f8f9fa;
        }

        .table thead {
            background-color: #343a40;
            color: #fff;
        }

        .card-header {
            background: linear-gradient(135deg, #6f42c1, #d6336c);
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #6f42c1, #d6336c);
            border: none;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #d6336c, #6f42c1);
            color: #fff;
        }

        .form-control:focus {
            border-color: #d6336c;
            box-shadow: 0 0 0 0.2rem rgba(214, 51, 108, 0.25);
        }

        .modal-header {
            background: #6f42c1;
            color: #fff;
        }

        /* Foto thumbnail di tabel */
        .foto-thumb {
            max-height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="container my-5" style="max-width: 900px;">
        <h2 class="mb-4 text-center text-secondary">Kelola Admin <i class="fas fa-users-cog"></i></h2>

        <!-- Pesan sukses dan error -->
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Tabel Daftar Admin -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> Daftar Admin</span>
                <div>
                    <?php if ($show_add_admin_form): ?>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="logout_access" class="btn btn-outline-warning btn-sm"
                                title="Tutup Form Tambah Admin">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-outline-warning btn-sm" title="Kembali ke Dashboard">
                            <i class="fas fa-home"></i> kembali
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle text-center">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama Admin</th>
                                <th>Foto</th>
                                <th>Email</th>
                                <th>Super Admin</th>
                                <th>Waktu Terdaftar</th>
                                <th>Aksi</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stmt->rowCount() === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Belum ada admin terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['username']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_admin']) ?></td>

                                        <!-- untuk memunculkan foto di tabel -->
                                        <td>
                                            <?php if (!empty($row['foto']) && $row['foto'] !== 'default.jpg'): ?>
                                                <img src="../assets/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Admin"
                                                    class="foto-thumb" />
                                            <?php else: ?>
                                                <img src="../assets/default.jpg" alt="Foto Admin" class="foto-thumb" />
                                            <?php endif; ?>
                                        </td>
                                        <!-- sampai sini -->
                                        
                                        <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"
                                                class="text-decoration-none"><?= htmlspecialchars($row['email']) ?></a></td>
                                        <td>
                                            <?php if ($row['is_super_admin']): ?>
                                                <span class="badge bg-success">Ya</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d', strtotime($row['created_at'])) ?><br>
                                            <small><?= date('H:i:s', strtotime($row['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                data-bs-toggle="modal" data-bs-target="#deleteAdminModal"
                                                data-username="<?= htmlspecialchars($row['username']) ?>">Pecat
                                            </button>
                                        </td>



                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Tambah Admin -->
        <?php if ($show_add_admin_form): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i> Tambah Admin Baru
                </div>
                <div class="card-body">
                    <form method="POST" novalidate enctype="multipart/form-data">
                        <input type="hidden" name="add_admin" value="1" />
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" name="username" id="username" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autocomplete="username" />
                            </div>
                            <div class="col-md-6">
                                <label for="nama_admin" class="form-label">Nama Admin *</label>
                                <input type="text" name="nama_admin" id="nama_admin" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['nama_admin'] ?? '') ?>" autocomplete="name" />
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" name="email" id="email" class="form-control" required
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autocomplete="email" />
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" name="password" id="password" class="form-control" required
                                    minlength="6" autocomplete="new-password" />
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                    required minlength="6" autocomplete="new-password" />
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_super_admin" id="is_super_admin"
                                        class="form-check-input" <?= isset($_POST['is_super_admin']) ? 'checked' : '' ?> />
                                    <label for="is_super_admin" class="form-check-label fw-semibold">Super Admin</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="foto" class="form-label">Foto Admin</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/*" />
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-gradient-primary">
                                    <i class="fas fa-plus"></i> Tambah Admin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Verifikasi Password -->
    <?php if (!$show_add_admin_form): ?>
        <div class="modal fade show" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-modal="true"
            role="dialog" style="display: block; background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" class="modal-content" novalidate autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordModalLabel">
                            <i class="fas fa-lock"></i> Verifikasi Password Admin
                        </h5>
                        <button type="button" class="btn-close" aria-label="Close"
                            onclick="window.location='dashboard.php'"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="admin_access_password" class="form-label">Masukkan password:</label>
                            <input type="password" name="admin_access_password" id="admin_access_password"
                                class="form-control" placeholder="******" required autofocus />
                        </div>
                        <?php if ($password_error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($password_error) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer flex-column gap-2">
                        <button type="submit" class="btn btn-gradient-primary w-100">Verifikasi</button>
                        <a href="dashboard.php" class="btn btn-gradient-primary w-100">Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>



    <!-- Modal Hapus Admin -->
    <div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-labelledby="deleteAdminModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content">
                <input type="hidden" name="delete_admin" value="1">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAdminModalLabel">
                        <i class="fas fa-user-minus"></i> Hapus Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($delete_msg)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($delete_msg) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="username_to_delete_modal" class="form-label">Username Admin Yang akan di
                            Pecat</label>
                        <input type="text" name="username_to_delete" id="username_to_delete_modal"
                            autocomplete="new-password" class="form-control" required readonly>
                    </div>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" name="delete_password" id="delete_password" autocomplete="new-password"
                            class="form-control" placeholder="******" required>
                        <div class="form-text">Masukkan password rahasia untuk Pemecatan admin.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const inputUsername = document.getElementById('username_to_delete_modal');

            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const username = this.getAttribute('data-username');
                    inputUsername.value = username;
                });
            });
        });
    </script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>