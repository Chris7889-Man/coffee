<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Admin.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        $message = "Username dan password wajib diisi!";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $admin = new Admin($db);

        $admin->username = $username;
        $admin->password = $password;

        if ($admin->login()) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin->username;
            $_SESSION['admin_nama'] = $admin->nama_admin;
            $_SESSION['admin_email'] = $admin->email;
            $_SESSION['is_super_admin'] = $admin->is_super_admin;

            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Username atau password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Admin - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body.bg-light {
            background: linear-gradient(135deg, #7E5A3B 0%, #3E2723 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            background-color: #4e342e !important;
            border: none !important;
            border-radius: 16px;
            box-shadow: 0 6px 32px rgba(60, 30, 15, 0.2);
            outline: none !important;
            width: 100%;
            max-width: 420px;
            padding-bottom: 1rem;
        }

        .card:focus,
        .card:active,
        .card:hover {
            outline: none !important;
            box-shadow: 0 6px 32px rgba(60, 30, 15, 0.25);
            border: none !important;
        }

        .card-header {
            background: none;
            color: #efd3b6;
            border-bottom: none;
            padding: 2rem 1.5rem 0 1.5rem;
            text-align: center;
        }

        .card-header h3 {
            color: #D7B899;
            letter-spacing: 1.2px;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
        }

        .form-label {
            color: #e6c8a6;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .form-control {
            background-color: #efd3b6;
            border: 1.5px solid #b88963;
            color: #4e342e;
            font-weight: 500;
            border-radius: 10px;
            box-shadow: none;
            transition: all 0.3s ease;
            padding: 12px 15px;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #bc8b4b;
            background: #fff4e3;
            color: #54391a;
            box-shadow: 0 0 0 0.3rem rgba(215, 184, 153, 0.25);
            outline: none !important;
        }

        .form-control::placeholder {
            color: #a1764b;
            font-style: italic;
        }

        .form-control:focus-visible {
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #7E5A3B 80%, #bc8b4b 100%);
            border: none;
            font-weight: 700;
            font-size: 1.15em;
            letter-spacing: 1.2px;
            transition: background 0.3s ease;
            padding: 14px 0;
            border-radius: 35px;
            margin-top: 1rem;
            box-shadow: 0 5px 15px rgba(124, 84, 44, 0.7);
            cursor: pointer;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #6b4226 80%, #a57333 100%);
            box-shadow: 0 7px 20px rgba(106, 61, 10, 0.85);
        }

        .alert-danger {
            background: #be7c4c;
            color: #fff;
            border: none;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            font-weight: 600;
            padding: 12px 16px;
            text-align: center;
            box-shadow: inset 0 0 12px #75461e;
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

<body class="bg-light">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-coffee"></i> Admin Coffee</h3>
        </div>
        <div class="card-body px-4">
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" novalidate>
                <div class="mb-4">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                        placeholder="Masukkan username" required autofocus />
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Masukkan password" required />
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            <br>
            <div class="btn-kembali-wrapper">
                <a href="../iklan.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</body>

</html>