<?php

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Método no permitido.", 405);
}

$token = $jwtService->getBearerToken();

if ($token === null) {
    ApiResponse::error("Token no enviado.", 401);
}

$decodedToken = $jwtService->validateStudentToken($token);

if ($decodedToken === null) {
    ApiResponse::error("Token inválido o expirado.", 401);
}

$studentId = (int) $decodedToken->sub;

$courses = $studentService->getCoursesWithAssignments($studentId);

ApiResponse::success([
    "courses" => $courses
], "Cursos obtenidos correctamente.");