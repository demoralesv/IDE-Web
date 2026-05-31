<?php
class Auth {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function login($email, $userPassword) {
        $stmt = $this->conn->prepare("
            SELECT * 
            FROM usuario 
            WHERE correo = :email
        ");

        $stmt->execute([
            ":email" => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($userPassword, $user["password"])) {
            return true;
        }

        return false;
    }
    
    public function registerUser($name, $lastname, $email, $userPassword): array {
        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $userPassword)) {
            return [
                "success" => false,
                "message" => "La contraseña debe contener al menos un número, una mayúscula, una minúscula y mínimo 8 caracteres."
            ];
        }

        $registered = $this->register($name, $lastname, $email, $userPassword);

        return [
            "success" => $registered,
            "message" => $registered
                ? "Usuario registrado exitosamente."
                : "Error al registrar el usuario."
        ];
    }

    public function register($name, $lastname, $email, $userPassword) {
        try {
            $this->conn->beginTransaction();

            $checkStmt = $this->conn->prepare("
                SELECT ID 
                FROM usuario 
                WHERE correo = :correo
            ");

            $checkStmt->execute([
                ":correo" => $email
            ]);

            $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $this->conn->rollBack();
                return false;
            }

            $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

            $stmt = $this->conn->prepare("
                INSERT INTO usuario (nombre, apellido1, correo, password)
                VALUES (:nombre, :apellido1, :correo, :password)
            ");

            $userInserted = $stmt->execute([
                ":nombre" => $name,
                ":apellido1" => $lastname,
                ":correo" => $email,
                ":password" => $hashedPassword
            ]);

            if (!$userInserted) {
                $this->conn->rollBack();
                return false;
            }

            $userId = $this->conn->lastInsertId();

            $teacherStmt = $this->conn->prepare("
                INSERT INTO profesor (ID)
                VALUES (:id)
            ");

            $teacherInserted = $teacherStmt->execute([
                ":id" => $userId
            ]);

            if (!$teacherInserted) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
}