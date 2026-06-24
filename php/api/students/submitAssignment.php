<?php

require_once __DIR__ . '/../bootstrap.php';

function uploadSubmissionFile(int $evaluationId, int $groupId): array {
    if (!isset($_FILES["projectFile"])) {
        return [
            "success" => false,
            "message" => "No se recibió ningún archivo con el nombre projectFile."
        ];
    }

    if ($_FILES["projectFile"]["error"] !== UPLOAD_ERR_OK) {
        return [
            "success" => false,
            "message" => "Error de subida PHP: " . $_FILES["projectFile"]["error"]
        ];
    }

    $maxSize = 50 * 1024 * 1024;

    if ($_FILES["projectFile"]["size"] > $maxSize) {
        return [
            "success" => false,
            "message" => "El archivo supera el tamaño máximo permitido."
        ];
    }

    $originalName = $_FILES["projectFile"]["name"];
    $tmpName = $_FILES["projectFile"]["tmp_name"];

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = [
        "zip", "rar", "pdf", "txt", "doc", "docx",
        "png", "jpg", "jpeg", "java", "py", "cs", "cpp",
        "html", "css", "js", "sql", "json", "xml"
    ];

    if (!in_array($extension, $allowedExtensions, true)) {
        return [
            "success" => false,
            "message" => "Extensión no permitida: " . $extension
        ];
    }

    $uploadDir = __DIR__ . "/../../uploads/submissions/evaluation_" . $evaluationId . "/group_" . $groupId;

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return [
                "success" => false,
                "message" => "No se pudo crear la carpeta de subida."
            ];
        }
    }

    $safeBaseName = preg_replace(
        "/[^a-zA-Z0-9_-]/",
        "_",
        pathinfo($originalName, PATHINFO_FILENAME)
    );

    $newFileName = $safeBaseName . "_" . uniqid() . "." . $extension;
    $destination = $uploadDir . "/" . $newFileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        return [
            "success" => false,
            "message" => "No se pudo mover el archivo a la carpeta final."
        ];
    }

    $scheme = "http";

    if (
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
        ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https"
    ) {
        $scheme = "https";
    }

    $host = $_SERVER["HTTP_HOST"] ?? "sied.me";

    return [
        "success" => true,
        "url" => $scheme . "://" . $host . "/uploads/submissions/evaluation_" . $evaluationId . "/group_" . $groupId . "/" . $newFileName
    ];
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

$uploadResult = uploadSubmissionFile((int) $evaluationId, $groupId);

if (!$uploadResult["success"]) {
    ApiResponse::error($uploadResult["message"]);
}

$projectUrl = $uploadResult["url"];

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