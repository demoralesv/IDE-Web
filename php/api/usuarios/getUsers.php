<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../db.php";
require_once "../auth.php";

validarToken();

try {
    $database = new ApiDatabase();
    $connection = $database->connect();

    $query = "SELECT id, nombre, email FROM usuario";
    $statement = $connection->prepare($query);
    $statement->execute();

    $usuarios = $statement->fetchAll();

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