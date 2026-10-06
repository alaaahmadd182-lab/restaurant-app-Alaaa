<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/request.php";

function signup()
{
    global $pdo;

    $data = getJsonBody();

    $firstName = trim($data["first_name"] ?? "");
    $lastName = trim($data["last_name"] ?? "");
    $username = trim($data["username"] ?? "");
    $email = trim($data["email"] ?? "");
    $password = $data["password"] ?? "";

    // Required fields
    if (
        $firstName === "" ||
        $lastName === "" ||
        $username === "" ||
        $email === "" ||
        $password === ""
    ) {
        sendError("All fields are required.", 400);
    }

    // Username cannot contain spaces
    if (preg_match("/\s/", $username)) {
        sendError("Username cannot contain spaces.", 400);
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError("Invalid email address.", 400);
    }

    // Password validation
    if (strlen($password) < 8) {
        sendError(
            "Password must be at least 8 characters.",
            400
        );
    }

    try {
        // Check duplicate username or email
        $checkStmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
               OR username = :username
            LIMIT 1
        ");

        $checkStmt->execute([
            "email" => $email,
            "username" => $username
        ]);

        if ($checkStmt->fetch()) {
            sendError(
                "Email or username is already in use.",
                409
            );
        }

        // Never store the plain password
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "
            INSERT INTO users
            (
                first_name,
                last_name,
                username,
                email,
                password,
                role
            )
            VALUES
            (
                :first_name,
                :last_name,
                :username,
                :email,
                :password,
                'user'
            )
        ";
        //here user is hardcoded to avoid any user to make themselves admins

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "first_name" => $firstName,
            "last_name" => $lastName,
            "username" => $username,
            "email" => $email,
            "password" => $hashedPassword
        ]);

        $userId = (int) $pdo->lastInsertId();

        sendSuccess(
            [
                "id" => $userId,
                "username" => $username
            ],
            "Account created successfully.",
            201
        );

    } catch (PDOException $e) {
        sendError("Unable to create account.", 500);
    }
}