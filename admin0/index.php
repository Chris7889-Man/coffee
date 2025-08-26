<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = "Username dan password wajib diisi!";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        // Ambil user dengan username dan is_super_admin = 0 (admin biasa)
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = :username AND is_super_admin = 0 LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifikasi password menggunakan password_hash dan password_verify
            if (password_verify($password, $user['password'])) {
                // Login sukses, set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_nama'] = $user['nama_admin'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['is_super_admin'] = $user['is_super_admin']; // pasti 0 untuk admin biasa
                $_SESSION['admin_foto'] = $user['foto']; // tambahkan ini jika ada kolom foto


                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Password salah!";
            }
        } else {
            $message = "Username tidak ditemukan atau bukan admin biasa yang terdaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Admin Biasa - Coffee Shop</title>
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

        .card-header {
            background: none;
            color: #efd3b6;
            border-bottom: none;
            padding: 2rem 1.5rem 0 1.5rem;
            text-align: center;
            font-weight: 700;
            font-size: 2rem;
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
            color: white;
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

        .btn-outline-light {
            margin-top: 30px;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            letter-spacing: 0.9px;
            text-transform: none;
            color: #704214;
            border: 2px solid #b88963;
            box-shadow: 0 2px 12px rgba(255, 255, 255, 0.2);
            transition: background-color 0.3s ease, color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-outline-light:hover {
            background-color: #b88963;
            color: #3e1f00;
            box-shadow: 0 4px 18px rgba(184, 137, 99, 0.8);
            text-decoration: none;
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
            <i class="fas fa-user"></i>
                
            <h3> Login Admin</h3>
        </div>
        <div class="card-body px-4">
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" novalidate autocomplete="off">
                <div class="mb-4">
                    <label for="username" class="form-label">Username</label>
                    <input name="username" id="username" autocomplete="new-password" class="form-control" placeholder="Masukkan username" required autofocus />
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password"  autocomplete="new-password" class="form-control" placeholder="Masukkan password" required />
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="btn-kembali-wrapper">
                <a href="../index.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>

</html>
