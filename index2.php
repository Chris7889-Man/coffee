<?php
session_start();

// Jika sudah login, redirect ke menu
if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true) {
    header("Location: menu.php");
    exit();
}

require_once 'config/database.php';
// require_once 'classes/Pelanggan.php';

$message = '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();

    $pelanggan = new Pelanggan($db);

    $pelanggan->email = $_POST['email'];
    $pelanggan->password = $_POST['password'];

    if ($pelanggan->login()) {
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_nama'] = $pelanggan->nama_pelanggan;
        $_SESSION['customer_email'] = $pelanggan->email;
        $_SESSION['customer_no_tlpn'] = $pelanggan->no_tlpn;
        $_SESSION['customer_alamat'] = $pelanggan->alamat;

        header("Location: menu.php");
        exit();
    } else {
        $message = "Email atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Super Admin Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #4b0000 0%, #100000 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f5d6d6;
        }

        .card {
            background-color: #580000;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 400px;
            padding-bottom: 1rem;
        }

        .card-header {
            background: none;
            color: #f0b2b2;
            border-bottom: none;
            font-weight: 700;
            font-size: 1.7rem;
            text-align: center;
            letter-spacing: 1px;
            padding-top: 1.5rem;
            padding-bottom: 0.5rem;
        }

        .form-label {
            color: #f2c2c2;
            font-weight: 600;
        }

        .form-control {
            background-color: #8c2a2a;
            border: none;
            color: #f7dede;
            font-weight: 600;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            padding: 10px 14px;
        }

        .form-control::placeholder {
            color: #dba3a3;
            font-style: italic;
        }

        .form-control:focus {
            background-color: #a84646;
            color: #fff0f0;
            box-shadow: none;
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #a32a2a 80%, #d13b3b 100%);
            border: none;
            font-weight: 700;
            padding: 12px;
            border-radius: 50px;
            letter-spacing: 1px;
            transition: background 0.3s ease;
            color: #fff0f0;
            width: 100%;
            margin-top: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(209, 59, 59, 0.7);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #7f2020 80%, #9d2727 100%);
            color: #ffe3e3;
            box-shadow: 0 6px 20px rgba(157, 39, 39, 0.9);
        }

        .alert-danger {
            background-color: #b33a3a;
            border: none;
            color: #ffe0e0;
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 15px;
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Tombol kembali */
        .btn-outline-light {
            margin-top: 30px;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            letter-spacing: 0.9px;
            text-transform: none;
            box-shadow: 0 2px 12px rgba(255, 255, 255, 0.2);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-outline-light:hover {
            background-color: #fff0f0;
            color: #9b2222;
            box-shadow: 0 4px 18px rgba(255, 0, 0, 0.4);
            text-decoration: none;
        }

        .card-body {
            padding: 2rem 2.5rem;
        }

        .btn-kembali-wrapper {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <i class="fas fa-coffee"></i> Super Admin Coffee
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" placeholder="Masukkan email" class="form-control" id="email" name="email"
                        required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" placeholder="Masukkan password" class="form-control" id="password"
                        name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="btn-kembali-wrapper">
                <a href="iklan.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>