<?php
session_start();

require_once '../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT nama_staff, jabatan, username, email, no_hp, alamat, password FROM staff WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['staff_logged_in'] = true;
            $_SESSION['staff_nama'] = $row['nama_staff'];
            $_SESSION['staff_jabatan'] = $row['jabatan'];
            $_SESSION['staff_username'] = $row['username'];
            $_SESSION['staff_email'] = $row['email'];
            $_SESSION['staff_no_hp'] = $row['no_hp'];
            $_SESSION['staff_alamat'] = $row['alamat'];

            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Email atau password salah!";
        }
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
    <title>Login - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        /* Background gradasi hijau coklat ke hitam */
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2a3a15 0%, #4b3523 60%, #12110e 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #dfd6c9;
        }

        .card {
            background-color: #3d2f23;
            /* coklat tua */
            max-width: 400px;
            width: 100%;
            border-radius: 18px;
            box-shadow:
                0 10px 15px rgba(0, 0, 0, 0.7),
                inset 0 0 30px rgba(60, 40, 20, 0.6);
            padding-bottom: 1rem;
            border: none;
        }

        .card-header {
            background: none;
            border-bottom: none;
            padding: 1.5rem 1.5rem 0 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            color: #e6d4b8;
            font-weight: 700;
            font-size: 2rem;
            letter-spacing: 1.5px;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
        }

        .card-header i {
            color: #b49e67;
            font-size: 2.3rem;
            margin-bottom: 0.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        .card-body {
            padding: 1.5rem 2rem 2rem 2rem;
        }

        label.form-label {
            color: #bca873;
            font-weight: 600;
            margin-bottom: 0.3rem;
            letter-spacing: 0.5px;
        }

        input.form-control {
            background-color: #2b3b13;
            border: 1.5px solid #736741;
            border-radius: 12px;
            color: #d6d0bd;
            font-weight: 600;
            padding: 0.6rem 1rem;
            transition: 0.3s ease;
            box-shadow: inset 0 0 8px rgba(120, 95, 54, 0.5);
        }

        input.form-control::placeholder {
            color: #91875f;
            font-style: italic;
        }

        input.form-control:focus {
            border-color: #d4bc77;
            outline: none;
            background-color: #414f19;
            color: #f0ecd7;
            box-shadow: 0 0 8px #d4bc77;
        }

        button.btn-primary {
            background: linear-gradient(90deg, #946e36 80%, #6e4f25 100%);
            border: none;
            color: #f4f1e8;
            font-weight: 700;
            letter-spacing: 1.3px;
            padding: 0.7rem 0;
            border-radius: 30px;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 4px 10px rgba(148, 110, 54, 0.7);
            transition: background 0.3s ease;
        }

        button.btn-primary:hover {
            background: linear-gradient(90deg, #7f5e2e 80%, #543c15 100%);
            color: #f5f2db;
            box-shadow: 0 6px 14px rgba(148, 110, 54, 0.9);
        }

        .alert-danger {
            background-color: #7b5c24;
            color: #e6d8a7;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
            box-shadow: inset 0 0 8px #533f0f;
            letter-spacing: 0.5px;
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
    <div class="card shadow">
        <div class="card-header">
            <i class="fas fa-coffee"></i>
            <h2>Staff Coffee</h2>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" placeholder="masukkan email" class="form-control"
                        required autofocus />
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" placeholder="masukkan password"
                        class="form-control" required />
                </div>
                <button type="submit" class="btn btn-primary">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>