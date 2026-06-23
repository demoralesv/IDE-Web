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
$evaluationId = isset($evaluationID) ? filter_var($evaluationID, FILTER_VALIDATE_INT) : null;

if ($evaluationId === false || $evaluationId === null) {
    ApiResponse::error("No se pudo identificar la evaluación.");
}

$group = $studentService->getStudentGroupForAssignment((int) $evaluationId, $studentId);

if (!$group) {
    ApiResponse::error("El estudiante no pertenece a ningún grupo para esta evaluación.", 404);
}

$groupId = (int) $group["ID"];

$members = $studentService->getGroupMembers($groupId);
$submissions = $studentService->getGroupSubmissions($groupId);

ApiResponse::success([
    "assignment" => [
        "ID" => (int) $group["evaluacionID"],
        "titulo" => $group["titulo"],
        "descripcion" => $group["descripcion"],
        "fechaEntrega" => $group["fechaentrega"],
        "adjunto" => $group["adjunto"]
    ],
    "group" => [
        "ID" => $groupId,
        "numero" => (int) $group["numero"],
        "members" => $members,
        "submissions" => $submissions
    ]
], "Grupo obtenido correctamente.");