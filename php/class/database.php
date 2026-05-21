<?php

class Database {
    private PDO $conn;

    public function __construct() {
        require __DIR__ . '/db_info.php';

        try {
            $this->conn = new PDO(
                "mysql:host=$servername;port=$dbPort;dbname=$dbname;charset=utf8mb4",
                $username,
                $dbPassword
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->conn;
    }
}