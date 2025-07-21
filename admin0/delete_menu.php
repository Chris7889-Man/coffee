    <?php
    session_start();
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/menu.php';

    // Pastikan hanya admin yang sudah login bisa mengakses
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    // Validasi parameter kode_menu dari URL
    if (!isset($_GET['kode_menu']) || empty($_GET['kode_menu'])) {
        $_SESSION['message'] = "Kode menu tidak valid.";
        header("Location: manage_menu.php");
        exit();
    }

    $kode_menu = $_GET['kode_menu'];
    $kode_menu = $_GET['nama_menu'];

    // Inisialisasi koneksi dan objek Menu
    $database = new Database();
    $db = $database->getConnection();
    $menu = new Menu($db);

    // Jalankan proses delete
    if ($menu->delete_menu($kode_menu)) {
        $_SESSION['message'] = "Menu berhasil dihapus.";
    } else {
        $_SESSION['message'] = "Gagal menghapus menu.";
    }

    // Redirect kembali ke halaman utama
    header("Location: manage_menu.php");
    exit();

      if ($menu->delete_menu($kode_menu)) {
        $_SESSION['message'] = "Menu berhasil dihapus.";
    } else {
        $_SESSION['message'] = "Gagal menghapus menu.";
    }

    // Redirect kembali ke halaman utama
    header("Location: manage_menu.php");
    exit();
    ?>
