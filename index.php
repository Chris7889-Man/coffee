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
            padding: 40px 10px 20px 10px;
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
        }

        .product-card .price {
            color: #f5ad56;
            margin-top: 5px;
            font-size: 1.07em;
            font-weight: bold;
        }

        .logo{
            color: #f5ad56;
            margin-top: 5px;
            font-size: 1.07em;
            font-weight: bold;
            border-radius: 10px;
            width: 60px;
            height: 70px;
            object-fit: cover;
            margin-bottom: 10px;
        }


        footer {
            background: #372013;
            color: #fff;
            text-align: center;
            padding: 10px 0;
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
        <a href="../karyawan/index.php" class="cta">Masuk Staff</a>
        <a href="../admin0/index.php" class="cta">Masuk Admin</a>
        <a href="../admin/index.php" class="cta">Masuk Super Admin</a>
    </section>

    <!-- GALERI PRODUK KOPI -->
    <section class="product-gallery">
        <div class="product-card">
            <img src="assets/kopi1.jpg" alt="Espresso Classic">
            <h3>Espresso Classic</h3>
            <p>Rasa kopi pekat dan kaya aroma, cocok menemani pagi Anda.</p>
            <div class="price">Rp 10.000</div>
        </div>
        <div class="product-card">
            <img src="assets/kopi2.jpg" alt="Cappuccino">
            <h3>Cappuccino</h3>
            <p>Kopi susu creamy dengan taburan bubuk coklat.</p>
            <div class="price">Rp 15.000</div>
        </div>
        <div class="product-card">
            <img src="assets/kopi3.jpg" alt="Ice Latte">
            <h3>Ice Latte</h3>
            <p>Kopi susu dingin, menyegarkan di setiap waktu.</p>
            <div class="price">Rp 13.000</div>
        </div>
        <div class="product-card">
            <img src="assets/kopi4.jpg" alt="Mocha Aren">
            <h3>Mocha Aren</h3>
            <p>Perpaduan kopi, coklat, dan gula aren lokal.</p>
            <div class="price">Rp 17.000</div>
        </div>
        <!-- Tambahkan produk lain sesuai kebutuhan -->
    </section>

    <footer>
        &copy; 2025 Kopi Vibescoffe
    </footer>
</body>

</html>
