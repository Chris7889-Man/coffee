<?php
session_start();

require_once '../config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = $_POST['email'] ?? ''; // Bisa username atau email
    $password = $_POST['password'] ?? '';

    if (empty($userInput) || empty($password)) {
        $message = "Username/email dan password harus diisi!";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT nama_staff, jabatan, username, email, no_hp, alamat, foto, password FROM staff WHERE email = :userInput OR username = :userInput LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':userInput', $userInput);
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
                $_SESSION['staff_foto'] = $row['foto'];  // Ambil dari field foto database


                header("Location: view_menu.php");
                exit();
            } else {
                $message = "Username/email atau password salah!";
            }
        } else {
            $message = "Username/email atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Coffee Shop Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2a3a15 0%, #4b3523 60%, #12110e 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #dfd6c9;
            position: relative;
        }

        /* Coffee beans animation */
        .coffee-beans {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .bean {
            position: absolute;
            color: #8b7355;
            font-size: 20px;
            animation: fall 10s linear infinite;
        }

        @keyframes fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .card {
            background-color: rgba(61, 47, 35, 0.95);
            backdrop-filter: blur(10px);
            max-width: 420px;
            width: 90%;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.8),
                inset 0 0 30px rgba(60, 40, 20, 0.6);
            padding-bottom: 1rem;
            border: 1px solid rgba(180, 158, 103, 0.2);
            position: relative;
            z-index: 2;
        }

        .card-header {
            background: none;
            border-bottom: none;
            padding: 2rem 1.5rem 1rem 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            color: #e6d4b8;
            font-weight: 700;
            font-size: 2.2rem;
            letter-spacing: 1.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            margin: 0;
        }

        .card-header i {
            color: #b49e67;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .card-body {
            padding: 1.5rem 2rem 2rem 2rem;
        }

        label.form-label {
            color: #bca873;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group-text {
            background-color: #2b3b13;
            border: 1.5px solid #736741;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #bca873;
        }

        input.form-control {
            background-color: #2b3b13;
            border: 1.5px solid #736741;
            border-left: none;
            border-radius: 0 12px 12px 0;
            color: #d6d0bd;
            font-weight: 600;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
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

        .input-group:focus-within .input-group-text {
            border-color: #d4bc77;
            color: #d4bc77;
        }

        button.btn-primary {
            background: linear-gradient(135deg, #946e36 0%, #6e4f25 100%);
            border: none;
            color: #f4f1e8;
            font-weight: 700;
            letter-spacing: 1.3px;
            padding: 0.8rem 0;
            border-radius: 30px;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(148, 110, 54, 0.7);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        button.btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        button.btn-primary:hover::before {
            left: 100%;
        }

        button.btn-primary:hover {
            background: linear-gradient(135deg, #7f5e2e 0%, #543c15 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(148, 110, 54, 0.9);
        }

        .alert-danger {
            background-color: rgba(139, 69, 19, 0.3);
            color: #ffb3b3;
            border: 1px solid #8b4513;
            border-radius: 10px;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(139, 69, 19, 0.3);
            letter-spacing: 0.5px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .btn-outline-light {
            margin-top: 1.5rem;
            border-radius: 25px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.9px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.8);
            background: transparent;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
        }

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }

        .btn-kembali-wrapper {
            text-align: center;
            margin-top: 1rem;
        }

        .loading {
            display: none;
            text-align: center;
            color: #bca873;
            margin-top: 1rem;
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }

        @media (max-width: 576px) {
            .card {
                margin: 20px;
                width: calc(100% - 40px);
            }

            .card-header h2 {
                font-size: 1.8rem;
            }

            .card-header i {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="coffee-beans"></div>

    <div class="card shadow">
        <div class="card-header">
            <i class="fas fa-coffee"></i>
            <h2>Staff Login</h2>
            <p class="text-muted mb-0">Gajian Sudah Dekat</p>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Username atau Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="email" autocomplete="new-password" name="email"
                            placeholder="Masukkan username atau email Anda" class="form-control" required autofocus
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-key"></i>
                        </span>
                        <input type="password" id="password" autocomplete="new-password" name="password"
                            placeholder="Masukkan password Anda" class="form-control" required />
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>

                <div class="loading">
                    <div class="spinner-border" role="status"></div>
                    <span class="ms-2">Logging in...</span>
                </div>
            </form>

            <div class="btn-kembali-wrapper">
                <a href="../index.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const loading = document.querySelector('.loading');

            submitBtn.style.display = 'none';
            loading.style.display = 'block';
        });

        // Create falling coffee beans animation
        function createCoffeeBean() {
            const bean = document.createElement('div');
            bean.className = 'bean';
            bean.innerHTML = '<i class="fas fa-coffee"></i>';
            bean.style.left = Math.random() * 100 + '%';
            bean.style.animationDelay = Math.random() * 2 + 's';
            bean.style.animationDuration = (Math.random() * 3 + 7) + 's';

            document.querySelector('.coffee-beans').appendChild(bean);

            // Remove bean after animation
            setTimeout(() => {
                bean.remove();
            }, 10000);
        }

        // Create beans periodically
        setInterval(createCoffeeBean, 3000);

        // Create initial beans
        for (let i = 0; i < 5; i++) {
            setTimeout(createCoffeeBean, i * 1000);
        }

        // Auto-hide alert after 5 seconds
        setTimeout(function () {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>

</html>