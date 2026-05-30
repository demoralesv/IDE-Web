<?php

class UserService {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function obtainUsers(): array {
        $query = "SELECT ID, nombre, correo FROM usuario";

        $statement = $this->conn->prepare($query);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}