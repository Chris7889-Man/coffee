<?php
session_start();

// If already logged in, redirect to menu
if(isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true) {
    header("Location: menu.php");
    exit();
}

require_once '../config/database.php';
require_once '../classes/Pelanggan.php';

$message = '';
$error = '';

if($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $pelanggan = new Pelanggan($db);
    
    $pelanggan->nama_pelanggan = $_POST['nama_pelanggan'];
    $pelanggan->email = $_POST['email'];
    $pelanggan->no_tlpn = $_POST['no_tlpn'];
    $pelanggan->alamat = $_POST['alamat'];
    $pelanggan->password = $_POST['password'];
    
    // Validate password confirmation
    if($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        if($pelanggan->register()) {
            $message = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Registrasi gagal! Email atau nama pelanggan mungkin sudah terdaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-coffee"></i> Coffee Shop - Registrasi</h3>
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="nama_pelanggan" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" 
                                    value="<?php echo isset($_POST['nama_pelanggan']) ? $_POST['nama_pelanggan'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="no_tlpn" class="form-label">No. Telepon</label>
                                <input type="tel" class="form-control" id="no_tlpn" name="no_tlpn" 
                                    value="<?php echo isset($_POST['no_tlpn']) ? $_POST['no_tlpn'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo isset($_POST['alamat']) ? $_POST['alamat'] : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i> Daftar
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>