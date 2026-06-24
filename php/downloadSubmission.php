<?php

session_start();

require_once __DIR__ . '/class/BackendFacade.php';

$backend = new BackendFacade();

if (!isset($_SESSION["usuario"], $_SESSION["ID"])) {
    header("Location: /");
    exit;
}

$teacherId = (int) $_SESSION["ID"];

$submissionId = null;

if (isset($ID)) {
    $submissionId = filter_var($ID, FILTER_VALIDATE_INT);
}

if (!$submissionId) {
    http_response_code(400);
    echo "Entrega inválida.";
    exit;
}

$submission = $backend->getSubmissionForDownload((int) $submissionId, $teacherId);

if (!$submission || empty($submission["proyecto"])) {
    http_response_code(404);
    echo "No se encontró la entrega.";
    exit;
}

$fileUrl = $submission["proyecto"];
$filePathFromUrl = parse_url($fileUrl, PHP_URL_PATH);

if (!$filePathFromUrl) {
    http_response_code(404);
    echo "Archivo inválido.";
    exit;
}

$filePathFromUrl = urldecode($filePathFromUrl);

if (!str_starts_with($filePathFromUrl, "/uploads/submissions/")) {
    http_response_code(403);
    echo "Archivo no permitido.";
    exit;
}

$baseDir = realpath(__DIR__ . "/uploads/submissions");
$fullPath = realpath(__DIR__ . $filePathFromUrl);

if (!$baseDir || !$fullPath || !str_starts_with($fullPath, $baseDir)) {
    http_response_code(404);
    echo "Archivo no encontrado.";
    exit;
}

if (!file_exists($fullPath)) {
    http_response_code(404);
    echo "Archivo no encontrado.";
    exit;
}

$fileName = basename($fullPath);

if (ob_get_level()) {
    ob_end_clean();
}

header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($fullPath));
header("Cache-Control: must-revalidate");
header("Pragma: public");

readfile($fullPath);
exit;