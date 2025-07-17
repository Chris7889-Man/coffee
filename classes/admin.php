<?php
require_once '../config/database.php';

class Admin {
    private $conn;
    private $table_name = "admin";

    public $username;
    public $password;
    public $nama_admin;
    public $email;
    public $is_super_admin;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create admin
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, nama_admin=:nama_admin, 
                      email=:email, is_super_admin=:is_super_admin";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":nama_admin", $this->nama_admin);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":is_super_admin", $this->is_super_admin);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login admin
    public function login() {
        $query = "SELECT username, password, nama_admin, email, is_super_admin 
                  FROM " . $this->table_name . " 
                  WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password, $row['password'])) {
            $this->nama_admin = $row['nama_admin'];
            $this->email = $row['email'];
            $this->is_super_admin = $row['is_super_admin'];
            return true;
        }
        return false;
    }

    // Read all admins
    public function read() {
        $query = "SELECT username, nama_admin, email, is_super_admin, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Update admin
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET nama_admin=:nama_admin, email=:email, is_super_admin=:is_super_admin";
        
        // Add password to query if it's being updated
        if(!empty($this->password)) {
            $query .= ", password=:password";
        }
        
        $query .= " WHERE username=:username";

        $stmt = $this->conn->prepare($query);

        // Hash password if provided
        if(!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $this->password);
        }

        $stmt->bindParam(":nama_admin", $this->nama_admin);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":is_super_admin", $this->is_super_admin);
        $stmt->bindParam(":username", $this->username);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete admin
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>