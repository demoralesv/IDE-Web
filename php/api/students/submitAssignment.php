<?php

require_once __DIR__ . '/../bootstrap.php';

function uploadSubmissionFile(int $evaluationId, int $groupId): string|false {
    if (
        !isset($_FILES["projectFile"]) ||
        $_FILES["projectFile"]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return false;
    }

    if ($_FILES["projectFile"]["error"] !== UPLOAD_ERR_OK) {
        return false;
    }

    $maxSize = 1000 * 1024 * 1024;

    if ($_FILES["projectFile"]["size"] > $maxSize) {
        return false;
    }

    $originalName = $_FILES["projectFile"]["name"];
    $tmpName = $_FILES["projectFile"]["tmp_name"];

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = [
        "zip", "rar", "pdf", "txt", "doc", "docx",
        "png", "jpg", "jpeg", "java", "py", "cs", "cpp",
        "html", "css", "js", "sql"
    ];

    if (!in_array($extension, $allowedExtensions, true)) {
        return false;
    }

    $uploadDir = __DIR__ . "/../../uploads/submissions/evaluation_" . $evaluationId . "/group_" . $groupId;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeBaseName = preg_replace(
        "/[^a-zA-Z0-9_-]/",
        "_",
        pathinfo($originalName, PATHINFO_FILENAME)
    );

    $newFileName = $safeBaseName . "_" . uniqid() . "." . $extension;
    $destination = $uploadDir . "/" . $newFileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        return false;
    }

    $scheme = "http";

    if (
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
        ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https"
    ) {
        $scheme = "https";
    }

    $host = $_SERVER["HTTP_HOST"] ?? "sied.me";

    return $scheme . "://" . $host . "/uploads/submissions/evaluation_" . $evaluationId . "/group_" . $groupId . "/" . $newFileName;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
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

$projectUrl = uploadSubmissionFile((int) $evaluationId, $groupId);

if ($projectUrl === false) {
    ApiResponse::error("No se pudo subir el archivo de entrega. Verifique el archivo o su tamaño.");
}

$submissionId = $studentService->createGroupSubmission($groupId, $projectUrl);

if ($submissionId === false) {
    ApiResponse::error("No se pudo registrar la entrega.");
}

$members = $studentService->getGroupMembers($groupId);
$submissions = $studentService->getGroupSubmissions($groupId);

ApiResponse::success([
    "submission" => [
        "ID" => $submissionId,
        "projectUrl" => $projectUrl
    ],
    "group" => [
        "ID" => $groupId,
        "numero" => (int) $group["numero"],
        "members" => $members,
        "submissions" => $submissions
    ]
], "Entrega registrada correctamente para todo el grupo.");