<?php
session_start();
session_unset(); // hapus semua variabel session
session_destroy(); // hancurkan session
header("Location: index.php"); // arahkan ke halaman login
exit();
