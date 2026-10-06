<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/request.php";
require_once __DIR__ . "/../helpers/jwt.php";
require_once __DIR__ . "/../helpers/otp.php";


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
function login()
{
    global $pdo;

    $data = getJsonBody();

    $email = trim($data["email"] ?? "");
    $password = $data["password"] ?? "";

    if ($email === "" || $password === "") {
        sendError("Email and password are required.", 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError("Invalid email address.", 400);
    }

    try {
        $stmt = $pdo->prepare("
            SELECT
                id,
                first_name,
                last_name,
                username,
                email,
                password,
                role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            "email" => $email
        ]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user["password"])) {
            sendError("Invalid email or password.", 401);
        }
        $otp = generateOtpForUser($user["id"]);

        // DEVELOPMENT ONLY:
        // Until email delivery is implemented, return the OTP
        // so we can test the verification flow in Postman.
        sendSuccess(
            [
                "otp_required" => true,
                "development_otp" => $otp
            ],
            "Password verified. OTP verification required."
        );

    } catch (PDOException $e) {
        sendError("Unable to login.", 500);
    }
}


function verifyOtp()
{
    global $pdo;

    $data = getJsonBody();

    $email = trim($data["email"] ?? "");
    $otp = trim((string) ($data["otp"] ?? ""));

    if ($email === "" || $otp === "") {
        sendError("Email and OTP are required.", 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendError("Invalid email address.", 400);
    }

    if (!preg_match("/^\d{6}$/", $otp)) {
        sendError("OTP must be exactly 6 digits.", 400);
    }

    try {
        // Find user
        $userStmt = $pdo->prepare("
            SELECT
                id,
                first_name,
                last_name,
                username,
                email,
                role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $userStmt->execute([
            "email" => $email
        ]);

        $user = $userStmt->fetch();

        if (!$user) {
            sendError("Invalid or expired OTP.", 401);
        }

        // Get newest unused OTP
        $otpStmt = $pdo->prepare("
            SELECT
                id,
                otp_hash,
                expires_at
            FROM user_otps
            WHERE user_id = :user_id
              AND used_at IS NULL
            ORDER BY id DESC
            LIMIT 1
        ");

        $otpStmt->execute([
            "user_id" => $user["id"]
        ]);

        $otpRecord = $otpStmt->fetch();

        if (!$otpRecord) {
            sendError("Invalid or expired OTP.", 401);
        }

        // Check expiration
        if (strtotime($otpRecord["expires_at"]) < time()) {
            sendError("Invalid or expired OTP.", 401);
        }

        // Check OTP
        if (!password_verify($otp, $otpRecord["otp_hash"])) {
            sendError("Invalid or expired OTP.", 401);
        }

        // Mark OTP as used BEFORE issuing JWT
        $updateStmt = $pdo->prepare("
            UPDATE user_otps
            SET used_at = NOW()
            WHERE id = :id
              AND used_at IS NULL
        ");

        $updateStmt->execute([
            "id" => $otpRecord["id"]
        ]);

        if ($updateStmt->rowCount() !== 1) {
            sendError("Invalid or expired OTP.", 401);
        }

        // OTP passed → NOW generate JWT
        $token = generateJwt($user);

        sendSuccess(
            [
                "token" => $token,

                "user" => [
                    "id" => (int) $user["id"],
                    "first_name" => $user["first_name"],
                    "last_name" => $user["last_name"],
                    "username" => $user["username"],
                    "email" => $user["email"],
                    "role" => $user["role"]
                ]
            ],
            "OTP verified. Login successful."
        );

    } catch (PDOException $e) {
        sendError("Unable to verify OTP.", 500);
    }
}