<?php

class ApiDatabase {
    private string $host = "localhost";
    private string $dbName = "proyecto";
    private string $user = "root";
    private string $password = "LmP_2k26";
    private ?PDO $connection = null;

    public function connect(): PDO {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

                $this->connection = new PDO($dsn, $this->user, $this->password);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                http_response_code(500);

                echo json_encode([
                    "success" => false,
                    "message" => "Error de conexión con la base de datos"
                ]);

                exit;
            }
        }

        return $this->connection;
    }
}