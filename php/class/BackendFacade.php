<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/UserService.php';

class BackendFacade {
    private Auth $auth;
    private UserService $userService;

    public function __construct() {
        $database = new Database();
        $conn = $database->getConnection();

        $this->auth = new Auth($conn);
        $this->userService = new UserService($conn);
    }

    public function registerUser(
        string $nombre,
        string $apellido,
        string $correo,
        string $password
    ): array {
        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $password)) {
            return [
                "success" => false,
                "message" => "La contraseña debe contener al menos un número, una mayúscula, una minúscula y mínimo 8 caracteres."
            ];
        }

        if ($this->auth->register($nombre, $apellido, $correo, $password)) {
            return [
                "success" => true,
                "message" => "Usuario registrado exitosamente."
            ];
        }

        return [
            "success" => false,
            "message" => "Error al registrar el usuario."
        ];
    }
    public function logSession(string $correo, string $password): bool {
        return $this->auth->login($correo, $password);
    }

    public function obtainUsers(): array {
        return $this->userService->obtainUsers();
    }
}