<?php
// Mulai session untuk contoh nama user
session_start();
if (!isset($_SESSION['admin_nama'])) {
    $_SESSION['admin_nama'] = "Rahmadana";
}

$nama = $_SESSION['admin_nama'];
$umur = 19;
$sekolah = "Sulawesi Tengah, Palu";
$profesi = "Mahasiswa, Programmer, Owner Vibescoffee";
$lokasi = "Palu, Sulawesi Tengah, Indonesia";

// Ganti path gambar profil di sini, contoh 'images/foto-saya.jpg'
// File gambar harus ada di folder yang sesuai
$foto_profil = "../assets/owner.jpg";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Profil Saya - <?= htmlspecialchars($nama) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: #333;
            max-width: 700px;
            margin: 40px auto;
            padding: 0 15px;
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 0.2em;
        }

        p.subtitle {
            color: #555;
            font-size: 1.1em;
            margin-top: 0;
            margin-bottom: 2em;
        }

        .profile-photo {
            display: block;
            width: 180px;
            height: 180px;
            margin: 0 auto 2em auto;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #ccc;
        }

        .about-me {
            background-color: #5466ac;
            color: white;
            padding: 25px 30px;
            border-radius: 8px;
            line-height: 1.5em;
            margin-bottom: 2em;
            text-align: center;
        }

        .about-me h2 {
            margin-top: 0;
            margin-bottom: 0.5em;
        }

        dl.details {
            max-width: 400px;
            margin: 0 auto 40px auto;
        }

        dl.details dt {
            float: left;
            width: 120px;
            font-weight: bold;
            clear: both;
        }

        dl.details dd {
            margin: 0 0 10px 130px;
        }

        .social-icons {
            text-align: center;
        }

        .social-icons a {
            display: inline-block;
            margin: 0 10px;
            font-size: 1.4em;
            text-decoration: none;
            color: #333;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #5466ac;
        }

        /* Container tombol agar di kanan */
        .btn-container {
            text-align: right;
            margin-bottom: 30px;
        }

        /* Styling tombol */
        .btn-back {
            position: relative;
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 30px;
            padding: 12px 28px 12px 18px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
            transition: background 0.4s ease, box-shadow 0.4s ease;
        }

        /* Ikon panah kiri */
        .btn-back svg {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            fill: white;
            transition: transform 0.3s ease;
        }

        /* Efek hover */
        .btn-back:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
            box-shadow: 0 8px 18px rgba(37, 117, 252, 0.7);
        }

        .btn-back:hover svg {
            transform: translateX(-4px);
        }

        /* Efek ripple sederhana */
        .btn-back::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 100%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
            pointer-events: none;
            z-index: 0;
        }

        .btn-back:active::after {
            width: 200px;
            height: 200px;
            transition: 0s;
        }
    </style>
    <!-- Font Awesome untuk ikon sosial media -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>

<body>

    <h1>Profil</h1>
    <p class="subtitle"><?= htmlspecialchars($profesi) ?></p>

    <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil <?= htmlspecialchars($nama) ?>"
        class="profile-photo" />

    <div class="about-me">
        <h2>Tentang Saya</h2>
        <p>
            Halo, saya <strong><?= htmlspecialchars($nama) ?></strong>, seorang mahasiswa dan programmer yang
            berdomisili di <strong><?= htmlspecialchars($sekolah) ?></strong>.
            Saya adalah pemilik <strong>Vibescoffee</strong>, dan sangat senang mengembangkan teknologi yang inovatif dan
            bermanfaat.
        </p>
    </div>

    <dl class="details">
        <dt>Nama:</dt>
        <dd><?= htmlspecialchars($nama) ?></dd>

        <dt>Umur:</dt>
        <dd><?= $umur ?> tahun</dd>

        <dt>Sekolah:</dt>
        <dd><?= htmlspecialchars($sekolah) ?></dd>

        <dt>Pekerjaan:</dt>
        <dd><?= htmlspecialchars($profesi) ?></dd>

        <dt>Lokasi:</dt>
        <dd><?= htmlspecialchars($lokasi) ?></dd>
    </dl>
    <a href="dashboard.php" class="btn-back" aria-label="Kembali ke halaman pesanan">
        <!-- Ikon panah kiri (SVG) -->
        <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
            <path d="M11 14L5 8l6-6" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        Kembali
    </a>
    </div>

    <div class="social-icons">
        <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="https://twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
    </div>

</body>

</html>