<?php

class Teacher {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function getTeacherName($email) {
        $stmt = $this->conn->prepare("
            SELECT nombre, apellido1 
            FROM usuario 
            WHERE correo = :email
        ");

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user["nombre"] . " " . $user["apellido1"];
        }

        return null;
    }

    public function getTeacherId($email) {
        $stmt = $this->conn->prepare("
            SELECT ID 
            FROM usuario 
            WHERE correo = :email
        ");

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return $user["ID"];
        }

        return null;
    }    
}