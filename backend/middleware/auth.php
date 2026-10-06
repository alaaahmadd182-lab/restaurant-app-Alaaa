<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../helpers/jwt.php";
require_once __DIR__ . "/../helpers/response.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function authenticateUser()
{
    $headers = getallheaders();

    $authorization = $headers["Authorization"]
        ?? $headers["authorization"]
        ?? null;

    if (!$authorization) {
        sendError("Authentication required.", 401);
    }

    if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
        sendError("Invalid authorization header.", 401);
    }

    $token = trim($matches[1]);

    try {
        $config = getJwtConfig();

        $decoded = JWT::decode(
            $token,
            new Key($config["secret"], "HS256")
        );

        if (!isset(
            $decoded->user->id,
            $decoded->user->username,
            $decoded->user->role
        )) {
            sendError("Invalid token.", 401);
        }

        return $decoded->user;

    } catch (Exception $e) {
        sendError("Invalid or expired token.", 401);
    }
}

function requireAdmin()
{
    $user = authenticateUser();

    if ($user->role !== "admin") {
        sendError("Admin access required.", 403);
    }

    return $user;
}