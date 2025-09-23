<?php
require_once __DIR__ . '/../config/database.php';

// Pastikan parameter 'username' ada di URL
if (!isset($_GET['username'])) {
    header("Location: data_staff.php"); // Redirect jika parameter tidak ada
    exit();
}

$username = $_GET['username']; // Ambil username dari URL

$database = new Database();
$db = $database->getConnection();

// Query DELETE berdasarkan username
$query = "DELETE FROM staff WHERE username = :username";
$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);

if ($stmt->execute()) {
    header("Location: data_staff.php?deleted=1"); // Redirect dengan pesan sukses
    exit();
} else {
    echo "Gagal menghapus data staff. Error: " . implode(" ", $stmt->errorInfo()); // Tampilkan error PDO jika ada
}

