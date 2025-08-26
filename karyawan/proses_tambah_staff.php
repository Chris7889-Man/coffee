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
    // Jika tidak sama, bisa redirect kembali dengan pesan error (atau tampilkan error)
    echo "<script>alert('Password dan konfirmasi password tidak sama!'); window.history.back();</script>";
    exit();
}

// Jika validasi lolos, lanjutkan proses simpan data

// Ambil data lain dari form
$nama_staff = $_POST['nama_staff'];
$jabatan = $_POST['jabatan'];
$username = $_POST['username'];
$email = $_POST['email'] ?? null;
$no_hp = $_POST['no_hp'] ?? null;
$alamat = $_POST['alamat'] ?? null;
$kode_gerobak = $_POST['kode_gerobak'];
$lokasi_jualan = $_POST['lokasi_jualan'];

// Enkripsi password
$password_hashed = password_hash($password, PASSWORD_DEFAULT);

$database = new Database();
$db = $database->getConnection();

$query = "INSERT INTO staff 
    (nama_staff, jabatan, username, password, email, no_hp, alamat, kode_gerobak, lokasi_jualan, tanggal_dibuat)
    VALUES 
    (:nama_staff, :jabatan, :username, :password, :email, :no_hp, :alamat, :kode_gerobak, :lokasi_jualan, NOW())";

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

if ($stmt->execute()) {
    header("Location: data_staff.php?success=1");
    exit();
} else {
    echo "Gagal menambah staff.";
}
