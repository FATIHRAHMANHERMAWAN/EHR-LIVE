<?php
require_once __DIR__ . '/../config.php';

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Added ;port=3307 right into the connection string
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";port=3307;dbname=" . DB_NAME, 
                DB_USER, 
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
        die("Bağlantı hatası detayı: " . $exception->getMessage());
    }
        return $this->conn;
    }
}
?>