<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Menu.php';

$database = new Database();
$db = $database->getConnection();
$menu = new Menu($db);

$stmt = $menu->read();
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
foreach ($menu_items as $item) {
    if (!in_array($item['kategori'], $categories)) {
        $categories[] = $item['kategori'];
    }
}

$selected_category = $_GET['kategori'] ?? '';
$search = trim($_GET['search'] ?? '');

$filtered_items = $menu_items;
if ($selected_category) {
    $filtered_items = array_filter($filtered_items, fn($item) => $item['kategori'] == $selected_category);
}
if ($search) {
    $filtered_items = array_filter(
        $filtered_items,
        fn($item) =>
        stripos($item['nama_menu'], $search) !== false ||
        stripos($item['deskripsi'] ?? '', $search) !== false
    );
}

usort($filtered_items, function ($a, $b) {
    if ($a['kategori'] == $b['kategori']) {
        return strcmp($a['nama_menu'], $b['nama_menu']);
    }
    return strcmp($a['kategori'], $b['kategori']);
});

$total_items = count($filtered_items);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lihat Menu - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(#3e2723, #1b0c02); /* gradient coklat gelap */
        color: #f5eee6; /* warna krem */
        position: relative;
        min-height: 100vh;
    }

    body::after {
        content: "";
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: transparent; /* hilangkan overlay hitam */
        z-index: -1;
    }

    #content {
        padding: 20px 15px;
        max-width: 1100px; /* menyesuaikan dengan index.php */
        margin: 0 auto;
        background: transparent;
        color: #f5eee6;
    }

    header#topbar {
        background: #4e342e; /* warna header coklat tua */
        padding: 20px 15px;
        margin-bottom: 30px;
        color: #f5eee6;
        font-size: 1.75rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    #brand {
        color: #f5ad56;
        font-weight: bold;
        display: flex;
        align-items: center;
    }

    #brand i {
        margin-right: 30px;
    }

    #pos-menu {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    #pos-menu a {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #7b4f2c; /* coklat keemasan */
        color: white;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background-color 0.3s ease;
    }

    #pos-menu a:hover {
        background: #543f2a;
        text-decoration: none;
    }

    #pos-menu a i {
        font-size: 1.1rem;
    }

    .search-container {
        background: linear-gradient(#7b4f2c, #543f2a);
        color: #f5eee6;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .search-container label {
        font-weight: 600;
        color: #f5eee6;
    }

    .search-container .form-control,
    .search-container .form-select {
        border-radius: 6px;
        background-color: #3e2723;
        color: #f5eee6;
        border: none;
    }

    .search-container .form-control::placeholder {
        color: #d2b48c;
    }

    .search-container .btn-light {
        font-weight: 600;
        border-radius: 6px;
        background: #d2b48c; /* kuning coklat */
        border: none;
        color: #2d2013;
    }

    .search-container .btn-light:hover {
        background: #c0a573;
        color: #2d2013;
    }

    .menu-card {
        background: #382415; /* warna kartu coklat tua */
        box-shadow: 0 4px 16px rgba(50, 30, 10, 0.15);
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        color: #f5eee6;
        position: relative;
        overflow: hidden;
    }

    .menu-card:hover {
        transform: translateY(-10px) scale(1.04);
        box-shadow: 0 8px 24px rgba(80, 50, 30, 0.23);
    }

    .menu-image {
        height: 192px;
        border-radius: 15px 15px 0 0;
        overflow: hidden;
        background: black;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .menu-image img {
        width: 233px;
        height: 185px;
        object-fit: cover;
        border-radius: 15px 15px 0 0;
        user-select: none;
    }

    .card-title {
        margin: 10px 0 4px;
        font-size: 1.08em;
        color: #e1b181;
        font-weight: bold;
        text-align: center;
    }

    .description {
        font-size: 1em;
        color: #f2e5d3;
        margin-bottom: 5px;
        text-align: center;
        flex-grow: 1;
    }

    .price-tag {
        color: #f5ad56;
        font-size: 1.07em;
        font-weight: bold;
    }

    .category-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.85rem;
        background: #6c757d;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        text-transform: capitalize;
        user-select: none;
    }

    .out-of-stock {
        opacity: 0.6;
        position: relative;
    }

    .stock-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        font-weight: 700;
        color: white;
        font-size: 1.2rem;
        pointer-events: none;
        user-select: none;
        background: rgba(220, 53, 69, 0.85);
        padding: 5px 15px;
        border-radius: 20px;
    }

    .card-footer-custom {
        margin-top: auto;
        padding-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
    }

    .btn-outline-primary {
        color: #ffffffff;
        border-color: #fffffeff;
    }

    .btn-outline-primary:hover {
        background-color: #c43535ff;
        color: #2d2013;
        border-color: #d2b48c;
    }

    /* menagatur bagroun pada kalkulator */
    .form-right {
    flex: 0 0 58%;
    padding-left: 200px;
    /* Tambahan background kuning */
    background-color: #6b6332ff;  /* Ini warna background kalkulator */
    padding: 30px 25px;
    border-radius: 20px;
}


/* mengeser tulisan dan tabel yang terdapat pada kalkulator */
.form-right table,
.form-right p,
.form-right label,
.form-right input,
.form-right select,
.form-right button {
    margin-left: 5px; /* geser sedikit ke kanan */
}



    /* Tetap pertahankan style responsive agar tetap bagus di mobile */
    @media (max-width: 768px) {
        .modal-body {
            flex-direction: column;
        }

        .form-left,
        .form-right {
            flex: 1 0 100%;
            border-right: none;
            padding: 0;
            margin-bottom: 20px;
        }

        /* Atur padding background kuning di mobile agar tetap ada */
        .form-right {
            padding: 20px;
        }
    }

    /* Responsive layout */
    @media (min-width: 992px) {
        .menu-card {
            height: 100%;
        }
    }

    @media (max-width: 991.98px) {
        #content {
            padding: 15px;
        }
    }

    @media (max-width: 767.98px) {
        .menu-card {
            height: auto;
        }

        .search-container .row > div {
            margin-bottom: 1rem;
        }
    }
</style>

</head>

<body>

    <header id="topbar">
        <div id="brand"><i class="fas fa-mug-hot"></i> Vibescoffee</div>
        <small class="text-muted" id="live-time"></small>
        <nav id="pos-menu" aria-label="POS Menu">
            <a href="dashboard.php"><i class="fas fa-coffee"></i> Dashboard</a>
            <a href="detail_pesanan.php"><i class="fas fa-list"></i> Detail Pesanan</a>
            <a href="manage_menu.php"><i class="fas fa-mug-hot"></i> Kelola Menu</a>
            <a href="manage_orders.php"><i class="fas fa-tasks"></i> Kelola Pesanan</a>
            <a href="laporan_harian.php"><i class="fas fa-file-alt"></i> Laporan</a>
            <a href="contoh.php"><i class="fas fa-dollar-sign"></i>Keuangan</a>
        </nav>


        <small class="text-muted" id="live-time"></small>
    </header>


    <div id="content">

        <section class="search-container">
            <form method="GET" class="row g-3" aria-label="Form pencarian dan filter kategori menu">
                <div class="col-md-5 col-sm-12">
                    <label for="search" class="form-label">Cari Menu</label>
                    <input id="search" name="search" type="text" class="form-control"
                        placeholder="Nama menu atau deskripsi" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4 col-sm-12">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select id="kategori" name="kategori" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= $selected_category == $category ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($category)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 col-sm-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-light w-100"><i class="fas fa-search"></i> Cari</button>
                </div>
            </form>
        </section>

        <section class="mb-4 bg-light bg-opacity-10 p-3 rounded">
            <strong>Menampilkan <?= $total_items; ?> item menu
                <?= ($search || $selected_category) ? "untuk pencarian '" . htmlspecialchars($search) . "' dan kategori '" . htmlspecialchars(ucfirst($selected_category)) . "'" : '' ?></strong>
            <a href="view_menu.php" class="btn btn-primary btn-sm float-end" aria-label="Reset filter dan pencarian">
                <i class="fas fa-sync-alt"></i> Reset
            </a>
        </section>

        <section class="row g-4">
            <?php if (empty($filtered_items)): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        Tidak ada menu yang ditemukan.<br>Silakan coba kata kunci atau kategori lain.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($filtered_items as $item):
                    $stok = (int) ($item['stok'] ?? 0);
                    $kategori = htmlspecialchars(ucfirst($item['kategori'] ?? ''));
                    $nama_menu = htmlspecialchars($item['nama_menu'] ?? '');
                    $harga = (int) ($item['harga'] ?? 0);
                    $deskripsi = htmlspecialchars($item['deskripsi'] ?? '');
                    $kode_menu = htmlspecialchars($item['kode_menu'] ?? '');
                    $gambar = htmlspecialchars($item['gambar'] ?? '');
                    ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card menu-card <?= $stok <= 0 ? 'out-of-stock' : '' ?>">
                            <div class="position-relative">
                                <div class="menu-image">
                                    <?php if ($gambar && file_exists(__DIR__ . '/../assets/' . $gambar)): ?>
                                        <img src="/assets/<?= $gambar ?>" alt="Gambar <?= $nama_menu ?>">
                                    <?php else: ?>
                                        <i class="fas fa-utensils"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-secondary category-badge"><?= $kategori ?></span>
                                <?php if ($stok <= 0): ?>
                                    <div class="stock-overlay">
                                        <span>STOK HABIS</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= $nama_menu ?></h5>
                                <?php if ($deskripsi): ?>
                                    <p class="description"><?= $deskripsi ?></p>
                                <?php endif; ?>
                                <div class="card-footer-custom">
                                    <span class="price-tag">Rp <?= number_format($harga, 0, ',', '.') ?></span>
                                    <span class="badge bg-<?= $stok > 5 ? 'success' : ($stok > 0 ? 'warning' : 'danger'); ?>">
                                        Stok: <?= $stok ?>
                                    </span>
                                </div>


                                <div class="d-grid mt-3">



                                    <!-- tombol ini dapat mengarahkan tampilan ke 2 websait -->
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-open-order-modal"
                                        data-bs-toggle="modal" data-bs-target="#dynamicOrderModal"
                                        data-kode-menu="<?= htmlspecialchars($kode_menu) ?>">
                                        <i class="fas fa-edit"></i> Pesan
                                    </button>








                                </div>


                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="modal fade" id="dynamicOrderModal" tabindex="-1" aria-labelledby="dynamicOrderLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content" id="dynamicOrderContent">
                        <!-- Konten form akan dimuat secara dinamis 22222222222222222222222-->
                    </div>
                </div>
            </div>








           




        </section>


        <!-- Tambahkan style ini di <head> atau sebelum modal -->
        <style>
            #orderModal .modal-content {
                background-color: #fff;
                color: #212529;
            }

            #orderModal .form-label b {
                color: #212529;
            }

            #orderModal .form-control,
            #orderModal .form-select {
                background-color: #f8f9fa;
                color: #212529;
                border: 1px solid #ced4da;
            }

            #orderModal .btn-success,
            #orderModal .btn-secondary {
                font-weight: 600;
            }



            #live-time {
                color: white;
                font-size: 0.99rem;
                font-weight: 500;
            }
        </style>




        <!-- Modal Tambah Pesanan
         untuk menambahkan atau mengubah pesanan secara langsung di halaman tanpa harus berpindah ke halaman baru. -->
        <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="tambah_orders.php" id="orderForm" class="modal-content">

                </form>
            </div>
        </div>






        <button id="backToTop" class="btn btn-primary" style="display:none;" title="Kembali ke atas">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) backToTop.style.display = 'block';
            else backToTop.style.display = 'none';
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>




    <span id="live-time"></span>


    

    <script>


        // ini adalah jam kodingan jam yang langsung berjalan

        function updateLiveTime() {
            const now = new Date();
            const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const jam = now.getHours().toString().padStart(2, "0");
            const menit = now.getMinutes().toString().padStart(2, "0");
            const detik = now.getSeconds().toString().padStart(2, "0");
            const tanggal = now.getDate().toString().padStart(2, "0");
            const format = hari[now.getDay()] + ", " + tanggal + " " + bulan[now.getMonth()] + " " + now.getFullYear() + " | " + jam + ":" + menit + ":" + detik;
            document.getElementById('live-time').textContent = format;
        }
        setInterval(updateLiveTime, 1000);
        updateLiveTime();





        const orderModal = document.getElementById('orderModal');
        orderModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Tombol yang memicu modal
            const kodeMenu = button.getAttribute('data-kode');
            const namaMenu = button.getAttribute('data-nama');

            // Isi form modal dengan data menu
            orderModal.querySelector('#modal_kode_menu').value = kodeMenu;
            orderModal.querySelector('#modal_nama_menu').value = namaMenu;

            // Generate kode pesanan secara sederhana (waktu milidetik)
            const kodePesanan = 'PSN' + Date.now().toString().slice(-6);
            orderModal.querySelector('#modal_kode_pesanan').value = kodePesanan;

            // Kosongkan nama pelanggan dan jumlah default 1
            orderModal.querySelector('#modal_nama_pelanggan').value = '';
            orderModal.querySelector('#modal_jumlah').value = 1;

            // Set status default 'Menunggu'
            orderModal.querySelector('#modal_status_pesanan').value = 'Menunggu';
        });




        //  kode ini untuk :
        // Saat tombol .btn-open-order-modal ditekan, mengambil kodeMenu dari data-kode-menu.

        // Menampilkan spinner loading di modal.

        // Melakukan fetch Ajax ke form_tambah_order.php dengan parameter kode menu.

        // Hasil HTML dari fetch ini akan dimasukkan ke dalam #dynamicOrderContent, sehingga form tambah pesanan muncul di modal.

        // Jika gagal, tampil pesan error.-->

        document.querySelectorAll('.btn-open-order-modal').forEach(button => {
            button.addEventListener('click', function () {
                const kodeMenu = this.dataset.kodeMenu;
                const modalContent = document.getElementById('dynamicOrderContent');

                modalContent.innerHTML = `<div class="modal-body text-center p-5">
            <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>`;

                fetch('form_tambah_order.php?kode_menu=' + encodeURIComponent(kodeMenu))
                    .then(response => response.text())
                    .then(html => {
                        modalContent.innerHTML = html;
                    })
                    .catch(err => {
                        modalContent.innerHTML = `<div class="modal-body text-danger p-4">Gagal memuat form pesanan.</div>`;
                    });
            });
        });

        


    //pemangilan kalkulator -->

function initKalkulatorKasir() {
    const selectMenu = document.querySelector("#selectMenu");
    const inputJumlah = document.querySelector("#inputJumlah");
    const diskonInput = document.querySelector("#diskon");
    const jumlahBayarInput = document.querySelector("#jumlahBayar");
    const totalHargaSpan = document.querySelector("#totalHarga");
    const kembalianSpan = document.querySelector("#kembalian");

    function hitung() {
        let harga = parseFloat(selectMenu.value || 0);
        let jumlah = parseInt(inputJumlah.value || 0);
        let diskon = parseFloat(diskonInput.value || 0);
        let total = harga * jumlah;
        let potongan = total * (diskon / 100);
        total -= potongan;
        let bayar = parseFloat(jumlahBayarInput.value || 0);
        let kembalian = bayar - total;

        totalHargaSpan.textContent = total;
        kembalianSpan.textContent = kembalian >= 0 ? kembalian : 0;
    }

    [selectMenu, inputJumlah, diskonInput, jumlahBayarInput].forEach(el => {
        if (el) el.addEventListener("input", hitung);
    });
}

// saat tombol pesan diklik → load form
document.querySelectorAll(".btn-pesan").forEach(btn => {
    btn.addEventListener("click", function() {
        fetch("form_tambah_order.php")
            .then(res => res.text())
            .then(html => {
                document.querySelector("#dynamicOrderContent").innerHTML = html;
                initKalkulatorKasir(); // ✅ aktifkan kalkulator setelah form masuk
            });
    });
});
function initKalkulator() {
    const selectMenu = document.getElementById('selectMenu');
    const inputJumlah = document.getElementById('inputJumlah');
    const diskonInput = document.getElementById('diskon');
    const totalHargaInput = document.getElementById('totalHarga');
    const jumlahBayarInput = document.getElementById('jumlahBayar');
    const kembalianInput = document.getElementById('kembalian');
    const presetButtons = document.querySelectorAll('.preset-amount');

    function formatRupiah(number) {
        return number.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' });
    }
    function parseRupiah(value) {
        if (!value) return 0;
        return parseInt(value.replace(/[^0-9]/g, '')) || 0;
    }
    function formatInputRupiah(value) {
        if (!value) return '';
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function hitungTotalHarga() {
        const selectedOption = selectMenu.options[selectMenu.selectedIndex];
        const harga = parseInt(selectedOption?.dataset?.harga) || 0;
        const jumlah = parseInt(inputJumlah.value) || 0;
        const diskonPersen = diskonInput ? (parseFloat(diskonInput.value) || 0) : 0;
        let total = harga * jumlah;
        let diskonNominal = (total * diskonPersen) / 100;
        let totalSetelahDiskon = total - diskonNominal;
        if (totalSetelahDiskon < 0) totalSetelahDiskon = 0;
        totalHargaInput.value = formatRupiah(totalSetelahDiskon);
        return totalSetelahDiskon;
    }
    function hitungKembalian() {
        const total = hitungTotalHarga();
        const bayar = parseRupiah(jumlahBayarInput.value);
        const kembali = bayar - total;
        kembalianInput.value = kembali >= 0 ? formatRupiah(kembali) : '-';
    }

    jumlahBayarInput.addEventListener('input', function () {
        this.value = formatInputRupiah(parseRupiah(this.value));
        hitungKembalian();
    });
   
    
    presetButtons.forEach(button => {
        button.addEventListener('click', function () {
            const amount = parseInt(this.dataset.amount);
            jumlahBayarInput.value = formatInputRupiah(amount);
            hitungKembalian();
        });
    });
    selectMenu.addEventListener('change', hitungKembalian);
    inputJumlah.addEventListener('input', hitungKembalian);
    if (diskonInput) diskonInput.addEventListener('input', hitungKembalian);

    hitungKembalian();
}

// Event saat tombol Pesan diklik
document.querySelectorAll('.btn-open-order-modal').forEach(button => {
    button.addEventListener('click', function () {
        const kodeMenu = this.dataset.kodeMenu;
        const modalContent = document.getElementById('dynamicOrderContent');
        modalContent.innerHTML = `
            <div class="modal-body text-center p-5">
                <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>`;
        fetch('form_tambah_order.php?kode_menu=' + encodeURIComponent(kodeMenu))
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
                // Inisialisasi kalkulator setelah form dimuat ke modal
                initKalkulator();
                
            })
            .catch(() => {
                modalContent.innerHTML = `<div class="modal-body text-danger p-4"> Gagal memuat form pesanan.</div>`;
            });
    });
});


</script>

</body>

</html>