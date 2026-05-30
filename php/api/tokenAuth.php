<?php

function validarToken(): void {
    $apiToken = "Kx7xLw0VPtZUqjDQwO0jFEKGfHQwdGaprPFFMPvFY6Ih5Ivg6BwfbVehxoWaTLsV4w788PIKIqFrGttftHFJ9fDZS415BQB7vrAAed1EoPqGyX3xXkkdUPyihP9AI7YqRK1kDpKtxB09VV1zX3sor1orv2k83CZosPIicGIOdAjkEArBTokSF9HqQlhu7hgVO8ACxDs";

    $headers = getallheaders();
    $authHeader = $headers["Authorization"] ?? "";

    if ($authHeader !== "Bearer " . $apiToken) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "No autorizado"
        ]);

        exit;
    }
}