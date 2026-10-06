<?php

$envPath = __DIR__ . "/../.env";

if (!file_exists($envPath)) {
    die("Environment configuration file not found.");
}

$env = parse_ini_file($envPath);

$host = $env["DB_HOST"] ?? null;
$dbname = $env["DB_NAME"] ?? null;
$username = $env["DB_USER"] ?? null;
$password = $env["DB_PASSWORD"] ?? null;

if (!$host || !$dbname || !$username || $password === null) {
    die("Database configuration is incomplete.");
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed.");
}