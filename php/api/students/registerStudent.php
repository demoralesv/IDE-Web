<?php

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Método no permitido.", 405);
}

$input = json_decode(file_get_contents("php://input"), true);

$name = trim($input["nombre"] ?? "");
$lastname = trim($input["apellido1"] ?? "");
$email = trim($input["correo"] ?? "");
$passwordHashFromIde = $input["password"] ?? "";

if ($name === "" || $email === "" || $passwordHashFromIde === "") {
    ApiResponse::error("Nombre, correo y contraseña son obligatorios.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ApiResponse::error("El correo no tiene un formato válido.");
}

$student = $studentService->registerStudent(
    $name,
    $lastname,
    $email,
    $passwordHashFromIde
);

if (!$student) {
    ApiResponse::error("No se pudo registrar el estudiante. Puede que el correo ya exista.");
}

$token = $jwtService->generateStudentToken((int) $student["ID"], $student["correo"]);

ApiResponse::success([
    "student" => $student,
    "token" => $token
], "Estudiante registrado correctamente.");