<?php

require_once __DIR__ . "/../config/database.php";

function generateOtpForUser($userId)
{
    global $pdo;

    $otp = (string) random_int(100000, 999999);

    // Never store the actual OTP
    $otpHash = password_hash(
        $otp,
        PASSWORD_DEFAULT
    );

    // OTP valid for 5 minutes
    $expiresAt = date(
        "Y-m-d H:i:s",
        time() + 300
    );

    // Invalidate any previous unused OTPs
    $stmt = $pdo->prepare("
        UPDATE user_otps
        SET used_at = NOW()
        WHERE user_id = :user_id
          AND used_at IS NULL
    ");

    $stmt->execute([
        "user_id" => $userId
    ]);

    // Store new OTP
    $stmt = $pdo->prepare("
        INSERT INTO user_otps
        (
            user_id,
            otp_hash,
            expires_at
        )
        VALUES
        (
            :user_id,
            :otp_hash,
            :expires_at
        )
    ");

    $stmt->execute([
        "user_id" => $userId,
        "otp_hash" => $otpHash,
        "expires_at" => $expiresAt
    ]);

    return $otp;
}