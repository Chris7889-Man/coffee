<?php
class Admin {
    private $conn;
    public $username;
    public $password;

    public $nama_admin;
    public $email;
    public $is_super_admin;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fungsi login: hanya login jika is_super_admin == 1 dan password benar
    public function login() {
        $query = "SELECT * FROM admin WHERE username = :username AND is_super_admin = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($this->password, $data['password'])) {
                // Set properti admin untuk session
                $this->nama_admin = $data['nama_admin'];
                $this->email = $data['email'];
                $this->is_super_admin = $data['is_super_admin'];
                return true;
            }
        }
        return false;
    }
}
