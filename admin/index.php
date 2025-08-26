<?php
session_start();

// Jika sudah login redirect ke dashboard
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
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

        // Ambil user super admin saja
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = :username AND is_super_admin = 1 LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_nama'] = $user['nama_admin'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['is_super_admin'] = $user['is_super_admin']; // pasti 1

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Password salah!";
            }
        } else {
            $message = "Hanya super admin yang bisa login!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body.bg-light {
            /* Linear gradient gabungan merah, coklat, dan hitam */
            background: linear-gradient(135deg,
                    #7a1212 0%,
                    /* merah gelap */
                    #4e2c22 50%,
                    /* coklat tua */
                    #000000 100%
                    /* hitam */
                );
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f2f2f2;
            /* teks terang agar kontras */
        }

        .card {
            background-color: rgba(85, 30, 30, 0.85);
            /* merah gelap transparan agar background terlihat */
            border: none !important;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 420px;
            padding: 2rem 2.5rem 2.5rem;
            color: #f7e6e6;
        }

        .card-header {
            background: none;
            color: #fddede;
            border-bottom: none;
            padding-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
            font-size: 2rem;
        }

        .card-header h3 {
            color: #f6b8b8;
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
            letter-spacing: 1.2px;
        }

        .form-label {
            color: #e4baba;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background-color: #3d1f1f;
            border: 1.5px solid #7a1212;
            color: #f3d9d9;
            font-weight: 500;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: #ab7d7d;
            font-style: italic;
        }

        .form-control:focus {
            border-color: #d94a4a;
            background: #5a2d2d;
            color: #fff;
            box-shadow: 0 0 0 0.3rem rgba(217, 74, 74, 0.5);
            outline: none !important;
        }

        .btn-primary {
            background: linear-gradient(90deg, #d94a4a 0%, #7a1212 100%);
            border: none;
            font-weight: 700;
            font-size: 1.15em;
            letter-spacing: 1.1px;
            padding: 14px 0;
            border-radius: 35px;
            margin-top: 1.5rem;
            box-shadow: 0 6px 20px rgba(217, 74, 74, 0.7);
            cursor: pointer;
            color: white;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #7a1212 0%, #d94a4a 100%);
            box-shadow: 0 6px 20px rgba(138, 20, 20, 0.8);
            color: white;
        }

        .alert-danger {
            background: rgba(217, 74, 74, 0.85);
            color: #fff;
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 600;
            margin-bottom: 1.25rem;
            box-shadow: inset 0 0 12px #7a1212;
            text-align: center;
        }

        .btn-outline-light {
            margin-top: 1.5rem;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            letter-spacing: 0.9px;
            text-transform: none;
            box-shadow: 0 2px 12px rgba(255, 255, 255, 0.25);
            transition: background-color 0.3s ease, color 0.3s ease;
            color: #fcdede;
            border: 2px solid #f49494;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .btn-outline-light:hover {
            background-color: #d94a4a;
            color: white;
            box-shadow: 0 4px 18px rgba(217, 74, 74, 0.7);
            text-decoration: none;
        }

        .btn-kembali-wrapper {
            text-align: center;
        }
    </style>

</head>

<body class="bg-light">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-coffee"></i>
            <h2>Login Super Admin</h2>
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
                    <input name="username" id="username"  autocomplete="new-password" class="form-control" placeholder="Masukkan username" required
                        autofocus />
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password"  autocomplete="new-password" class="form-control"
                        placeholder="Masukkan password" required />
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
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