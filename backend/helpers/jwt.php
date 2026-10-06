<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/response.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getJwtConfig()
{
    $envPath = __DIR__ . "/../.env";

    if (!file_exists($envPath)) {
        sendError("Server configuration error.", 500);
    }

    $env = parse_ini_file($envPath);

    $secret = $env["JWT_SECRET"] ?? null;
    $expiration = (int) ($env["JWT_EXPIRATION"] ?? 3600);

    if (!$secret) {
        sendError("Server configuration error.", 500);
    }

    return [
        "secret" => $secret,
        "expiration" => $expiration
    ];
}

function generateJwt($user)
{
    $config = getJwtConfig();

    $issuedAt = time();

    $payload = [
        "iat" => $issuedAt,
        "exp" => $issuedAt + $config["expiration"],

        "user" => [
            "id" => (int) $user["id"],
            "username" => $user["username"],
            "role" => $user["role"]
        ]
    ];

    return JWT::encode(
        $payload,
        $config["secret"],
        "HS256"
    );
}