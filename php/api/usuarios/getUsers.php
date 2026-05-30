<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../tokenAuth.php';
require_once __DIR__ . '/../../class/BackendFacade.php';

validarToken();

try {
    $backend = new BackendFacade();

    $usuarios = $backend->obtainUsers();

    echo json_encode([
        "success" => true,
        "data" => $usuarios
    ]);

} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Error al obtener usuarios"
    ]);
}