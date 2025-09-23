<!DOCTYPE html>
<html>

<head>
    <title>Kopi Nikmat - Segarkan Harimu!</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #2d2013;
            color: #f5eee6;
        }

        header {
            background: #4e342e;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .logo {
            width: 80px;
        }

        nav a {
            color: #d2b48c;
            text-decoration: none;
            font-weight: bold;
            margin-left: 20px;
        }

        .hero {
            text-align: center;
            padding: 1px 10px 20px 10px;
            background: linear-gradient(#3e2723, #1b0c02);
        }

        .hero h1 {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: #d7b899;
        }

        .cta {
            background: #7b4f2c;
            color: #fff;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background 0.3s;
            margin: 0 7px;
            display: inline-block;
        }

        .cta:hover {
            background: #543f2a;
        }

        .product-gallery {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 40px auto 30px;
            max-width: 1100px;
        }

        .product-card {
            background: #382415;
            border-radius: 12px;
            padding: 22px 18px;
            width: 220px;
            box-shadow: 0 4px 16px rgba(50, 30, 10, 0.15);
            text-align: center;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-10px) scale(1.04);
            box-shadow: 0 8px 24px rgba(80, 50, 30, 0.23);
        }

        .product-card img {
            border-radius: 10px;
            width: 150px;
            height: 170px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .product-card h3 {
            margin: 10px 0 4px;
            color: #e1b181;
            font-size: 1.08em;
        }

        .product-card p {
            margin: 4px 0 0 0;
            font-size: 1em;
            color: #f2e5d3;
            min-height: 40px; /* agar tinggi deskripsi konsisten */
        }

        .product-card .price {
            color: #f5ad56;
            margin-top: 3px;
            font-size: 1.07em;
            font-weight: bold;
        }

        .logo {
            color: #f5ad56;
            margin-top: -5px;
            font-size: 1.07em;
            font-weight: bold;
            border-radius: 10px;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: -3px;
        }

        footer {
            background: #372013;
            color: #fff;
            text-align: center;
            padding: 10px 0;
        }


        /* QR Code Section */
        .payment-qr {
            text-align: center;
            margin: 40px 0 80px;
        }

        .payment-qr h2 {
            color: #d7b899;
            margin-bottom: 15px;
        }

        .payment-qr img {
            width: 250px;
            height: 250px;
            object-fit: contain;
            border: 5px solid #f5ad56;
            border-radius: 15px;
            box-shadow: 0 0 10px #d7b89988;
        }

        @media print {
  body, .container {
    margin: 0;
    padding: 0;
    width: 100%;
    background-color: #2d2013 !important;
    color: #f5eee6 !important;         /* warna teks tema */
    font-family: Arial, sans-serif;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    background-color: #2d2013 !important;
  }
  
  .product-card {
    box-shadow: none !important;
    background-color: #382415 !important;
    color: #f5eee6 !important;
    border: 1px solid #000;
    margin-bottom: 1rem;
    padding: 15px !important;
    page-break-inside: avoid;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  .product-card img {
    width: 150px !important;
    height: 170px !important;
  }
  
  .payment-qr {
    page-break-inside: avoid;
    margin: 2cm auto;
    text-align: center;
  }
  
  .payment-qr img {
    width: 200px !important;
    height: 200px !important;
    border: 5px solid #f5ad56;
    border-radius: 15px;
    box-shadow: 0 0 10px #d7b89988;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  footer {
    page-break-after: avoid;
    background: #372013 !important;
    color: #fff !important;
    text-align: center;
    padding: 10px 0;
  }
  
  .no-print {
    display: none !important;
  }
  
  @page {
    margin: 1.5cm 1.5cm;
  }
}

    </style>
</head>

<body>
    <header>
        <img src="assets/logo.jpg" alt="Logo Kopi Nikmat" class="logo">
        <nav>
            <!-- <a href="login.php">Login</a> -->
        </nav>
    </header>

    <section class="hero">
        <h1>Selamat Datang di Vibescoffe</h1>
        <p>Temukan berbagai pilihan kopi premium dan minuman segar lainnya.<br>
            Pesan sekarang, rasakan kenikmatannya!</p>
        <a href="../karyawan/index.php" class="cta">Staff</a>
        <a href="../admin0/index.php" class="cta">Admin</a>
        <a href="../admin/index.php" class="cta">Super Admin</a>
    
    </section>

    <?php
    $host = 'localhost';
    $dbname = 'coffee';
    $user = 'root';
    $pass = '';

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Koneksi gagal: " . $e->getMessage();
        exit;
    }

    $query = "SELECT gambar, nama_menu, deskripsi, harga FROM menu ORDER BY kode_menu ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="product-gallery">
        <?php foreach ($produk_list as $produk): ?>
            <div class="product-card">
                <img src="assets/<?= htmlspecialchars($produk['gambar'] ?? '') ?>" alt="<?= htmlspecialchars($produk['nama_menu'] ?? '') ?>">
                <h3><?= htmlspecialchars($produk['nama_menu'] ?? '') ?></h3>
                <p><?= htmlspecialchars($produk['deskripsi'] ?? '') ?></p>
                <div class="price">Rp <?= number_format($produk['harga'] ?? 0, 0, ',', '.') ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    

    <section class="payment-qr">
        <h2>Scan untuk Pembayaran</h2>
        <img src="assets/qr-pembayaran.jpg" alt="QR Code Pembayaran">
    </section>

    <footer>
        &copy; 2025 Kopi Vibescoffe
    </footer>
</body>

</html>
