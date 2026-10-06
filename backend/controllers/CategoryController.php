<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";

function getCategories()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT
                id,
                name
            FROM categories
            ORDER BY name ASC
        ");

        $stmt->execute();

        $categories = $stmt->fetchAll();

        sendSuccess(
            $categories,
            "Categories retrieved successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to retrieve categories.", 500);
    }
}