<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_staff = $_POST['nama_staff'];
    $jabatan = $_POST['jabatan'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $query = "INSERT INTO staff (nama_staff, jabatan, username, password, email, no_hp, alamat) 
            VALUES (:nama_staff, :jabatan, :username, :password, :email, :no_hp, :alamat)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nama_staff', $nama_staff);
    $stmt->bindParam(':jabatan', $jabatan);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':no_hp', $no_hp);
    $stmt->bindParam(':alamat', $alamat);

    if ($stmt->execute()) {
        header("Location: data_staff.php?success=1");
    } else {
        echo "Gagal menyimpan data staff.";
    }
}
?>
