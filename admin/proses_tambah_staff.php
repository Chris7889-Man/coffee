<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_staff.php");
    exit();
}

$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validasi password dan konfirmasi harus sama
if ($password !== $password_confirm) {
    echo "<script>alert('Password dan konfirmasi password tidak sama!'); window.history.back();</script>";
    exit();
}

// Ambil data lain dari form
$nama_staff = $_POST['nama_staff'];
$jabatan = $_POST['jabatan'];
$username = $_POST['username'];
$email = $_POST['email'] ?? null;
$no_hp = $_POST['no_hp'] ?? null;
$alamat = $_POST['alamat'] ?? null;
$kode_gerobak = $_POST['kode_gerobak'];
$lokasi_jualan = $_POST['lokasi_jualan'];

// Mulai upload foto
$foto = 'default.jpg'; // default jika tidak ada file yang diupload
if (!empty($_FILES['foto']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['foto']['type'], $allowed_types)) {
        $upload_dir = __DIR__ . '/../uploads/staff_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = basename($_FILES['foto']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            $foto = $filename;
        } else {
            echo "<script>alert('Gagal upload foto.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Format foto tidak didukung. Gunakan JPG, PNG, atau GIF.'); window.history.back();</script>";
        exit();
    }
}

// Enkripsi password
$password_hashed = password_hash($password, PASSWORD_DEFAULT);

$database = new Database();
$db = $database->getConnection();

$query = "INSERT INTO staff 
    (nama_staff, jabatan, username, password, email, no_hp, alamat, kode_gerobak, lokasi_jualan, foto, tanggal_dibuat)
    VALUES 
    (:nama_staff, :jabatan, :username, :password, :email, :no_hp, :alamat, :kode_gerobak, :lokasi_jualan, :foto, NOW())";

$stmt = $db->prepare($query);
$stmt->bindParam(':nama_staff', $nama_staff);
$stmt->bindParam(':jabatan', $jabatan);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $password_hashed);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':no_hp', $no_hp);
$stmt->bindParam(':alamat', $alamat);
$stmt->bindParam(':kode_gerobak', $kode_gerobak);
$stmt->bindParam(':lokasi_jualan', $lokasi_jualan);
$stmt->bindParam(':foto', $foto);

if ($stmt->execute()) {
    header("Location: data_staff.php?success=1");
    exit();
} else {
    echo "Gagal menambah staff.";
}
?>
