<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService {
    private string $secretKey = "Kx7xLw0VPtZUqjDQwO0jFEKGfHQwdGaprPFFMPvFY6Ih5Ivg6BwfbVehxoWaTLsV4w788PIKIqFrGttftHFJ9fDZS415BQB7vrAAed1EoPqGyX3xXkkdUPyihP9AI7YqRK1kDpKtxB09VV1zX3sor1orv2k83CZosPIicGIOdAjkEArBTokSF9HqQlhu7hgVO8ACxDs";
    private string $issuer = "SIED_API";
    private int $expirationSeconds = 86400;

    public function generateStudentToken($studentId, $email) {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->expirationSeconds;

        $payload = [
            "iss" => $this->issuer,
            "iat" => $issuedAt,
            "exp" => $expiresAt,
            "sub" => $studentId,
            "email" => $email,
            "role" => "student"
        ];

        return JWT::encode($payload, $this->secretKey, "HS256");
    }

    public function validateStudentToken($token) {
        try {
            $decodedToken = JWT::decode($token, new Key($this->secretKey, "HS256"));

            if (!isset($decodedToken->role) || $decodedToken->role !== "student") {
                return null;
            }

            return $decodedToken;

        } catch (Exception $e) {
            error_log("JWT inválido: " . $e->getMessage());
            return null;
        }
    }

    public function getBearerToken() {
        $authHeader = $_SERVER["HTTP_AUTHORIZATION"]
            ?? $_SERVER["REDIRECT_HTTP_AUTHORIZATION"]
            ?? "";

        if ($authHeader === "" && function_exists("getallheaders")) {
            $headers = getallheaders();
            $authHeader = $headers["Authorization"] ?? $headers["authorization"] ?? "";
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}