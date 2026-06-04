<?php
class Auth {
    private $db;
    private $table = "users";

    public function __construct($db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
    }

    public function register($username, $password, $role = 'patient', $gender = 'Male') {
        try {
            $query = "INSERT INTO " . $this->table . " (username, password, role, gender) VALUES (:username, :password, :role, :gender)";
            $stmt = $this->db->prepare($query);
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":role", $role);
            $stmt->bindParam(":gender", $gender);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { return false; }
            throw $e;
        }
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, role, gender FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['gender'] = $row['gender'];
                return true;
            }
        }
        return false;
    }

    public function isLoggedIn() { return isset($_SESSION['user_id']); }
    public function getRole() { return $_SESSION['role'] ?? null; }
    
    public function logout() {
        session_destroy();
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        unset($_SESSION['gender']);
        return true;
    }
}
?>