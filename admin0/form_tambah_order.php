<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/menu.php';

$database = new Database();
$db = $database->getConnection();
$menuObj = new Menu($db);
$menu_list = $menuObj->read()->fetchAll(PDO::FETCH_ASSOC);
$selected_kode_menu = $_GET['kode_menu'] ?? '';

function generateKodePesanan($db)
{
    $query = "SELECT COUNT(*) as total FROM pesanan";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $num = $total + 1;
    return 'PSN' . str_pad($num, 3, '0', STR_PAD_LEFT);
}
$kode_pesanan = generateKodePesanan($db);
?>

<style>

    

    /* Membuat container flex agar form dan kalkulator pisah */
    .modal-body {
        display: flex;
        gap: 10px;
        padding: 10px;
    }

    .form-left {
        flex: 0 0 60%;
        /* 60% dari lebar container */
        border-right: 2px solid #ddd;
        padding-right: 20px;
    }

    .form-right {
        flex: 0 0 38%;
        /* 35% dari lebar container */
        padding-left: 0px;
        border-right: 2px solid #ddd;
        padding-right: 10px;
    }

    .form-label {
        color: #0d0c0cff;
        font-weight: 400;
    }

    input[readonly] {
        background-color: #e9ecef;
    }

    .modal-footer {
        padding-left: 0;
        padding-right: 0;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title" style="color: #0d0c0cff;"><b>Tambah Pesanan</b></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
</div>

<form method="POST" action="tambah_orders.php" id="orderFormModal" class="modal-body">
    <!-- Form Pesanan kiri -->
    <div class="form-left">
        <div class="mb-3">
            <label class="form-label" for="kodePesanan"><b>Kode Pesanan</b></label>
            <input type="text" id="kodePesanan" name="kode_pesanan" class="form-control"
                value="<?= htmlspecialchars($kode_pesanan) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label" for="namaPelanggan"><b>Nama Pelanggan</b></label>
            <input type="text" id="namaPelanggan" name="nama_pelanggan" class="form-control"
                placeholder="Masukkan nama pelanggan" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="selectMenu"><b>Menu</b></label>
            <select name="kode_menu" id="selectMenu" class="form-control" required>
                <option disabled <?= $selected_kode_menu ? '' : 'selected' ?>>- Pilih Menu -</option>
                <?php foreach ($menu_list as $menu): ?>
                    <option value="<?= htmlspecialchars($menu['kode_menu']) ?>" data-harga="<?= (int) $menu['harga'] ?>"
                        <?= ($menu['kode_menu'] == $selected_kode_menu) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($menu['nama_menu']) ?> - Rp <?= number_format($menu['harga'], 0, ',', '.') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="inputJumlah"><b>Jumlah</b></label>
            <input type="number" name="jumlah" id="inputJumlah" class="form-control" min="1" value="1" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="statusPesanan"><b>Status Pesanan</b></label>
            <select name="status_pesanan" id="statusPesanan" class="form-control" required>
                <option value="Menunggu">Menunggu</option>
                <option value="Dikonfirmasi">Dikonfirmasi</option>
                <option value="Diproses">Diproses</option>
                <option value="Siap">Siap</option>
                <option value="Selesai">Selesai</option>
                <option value="Batal">Batal</option>
            </select>
        </div>
        <div class="modal-footer px-0">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Pesanan</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i>
                Batal</button>
        </div>
    </div>

    <!-- Kalkulator Kasir kanan -->
    <div class="form-right">
        <h5 class="modal-title" style="color: #0d0c0cff;"><b>Kalkulator Kasir</b></h5>
        <br>
        <div class="form-group">
            <label class="form-label" for="totalHarga"><b>Total Harga</b></label>
            <input type="text" id="totalHarga" class="form-control" readonly value="Rp 0">
        </div>
        <div class="form-group">
            <label class="form-label" for="jumlahBayar"><b>Jumlah Uang Pembeli</b></label>
           
        
        
        





<input type="text" id="jumlahBayar" class="form-control" placeholder="Masukkan uang pembeli" value="Rp 0">

        <div class="mb-3">
            <label class="form-label"></label>
            <div id="presetAmounts" class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="10000">Rp 10.000</button>      
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="12000">Rp 12.000</button>       
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="13000">Rp 13.000</button>       
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="15000">Rp 15.000</button>       
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="20000">Rp 20.000</button>
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="50000">Rp 50.000</button>
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="75000">Rp 75.000</button>
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="100000">Rp 100.000</button>
                <button type="button" class="btn btn-outline-primary preset-amount" data-amount="500000">Rp 500.000</button>
            </div>
        </div></div>

        
        
<div class="form-group">
    <label class="form-label" for="diskon"><b>Diskon (%)</b></label>
    <input type="number" id="diskon" class="form-control" min="0" max="100" value="0" placeholder="Masukkan diskon dalam persen">
</div>


        <div class="form-group">
            <label class="form-label" for="kembalian"><b>Kembalian</b></label>
            <input type="text" id="kembalian" class="form-control" readonly value="Rp 0">
        </div>
        

</form>


