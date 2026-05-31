<?php

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Método no permitido.", 405);
}

$input = json_decode(file_get_contents("php://input"), true);

$email = trim($input["correo"] ?? "");
$passwordHashFromIde = $input["password"] ?? "";

if ($email === "" || $passwordHashFromIde === "") {
    ApiResponse::error("Debe ingresar correo y contraseña.");
}

$student = $studentService->loginStudent($email, $passwordHashFromIde);

if (!$student) {
    ApiResponse::error("Correo o contraseña incorrectos, o el usuario no es estudiante.", 401);
}

$token = $jwtService->generateStudentToken((int) $student["ID"], $student["correo"]);

ApiResponse::success([
    "student" => $student,
    "token" => $token
], "Login correcto.");