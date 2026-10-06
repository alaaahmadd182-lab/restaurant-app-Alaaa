<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/request.php";


function getMenusByRestaurant($restaurantId)
{
    global $pdo;

    try {
        // Check that restaurant exists
        $checkStmt = $pdo->prepare(
            "SELECT id FROM restaurants WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $restaurantId
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Restaurant not found.", 404);
        }

        // Get restaurant menus
        $stmt = $pdo->prepare("
            SELECT
                id,
                restaurant_id,
                name
            FROM menus
            WHERE restaurant_id = :restaurant_id
            ORDER BY id ASC
        ");

        $stmt->execute([
            "restaurant_id" => $restaurantId
        ]);

        $menus = $stmt->fetchAll();

        sendSuccess(
            $menus,
            "Menus retrieved successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to retrieve menus.", 500);
    }
}


function createMenu($restaurantId)
{
    global $pdo;

    $data = getJsonBody();

    $name = trim($data["name"] ?? "");

    if ($name === "") {
        sendError("Menu name is required.", 400);
    }

    try {
        // Check that restaurant exists
        $checkStmt = $pdo->prepare(
            "SELECT id FROM restaurants WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $restaurantId
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Restaurant not found.", 404);
        }

        $stmt = $pdo->prepare("
            INSERT INTO menus
            (
                restaurant_id,
                name
            )
            VALUES
            (
                :restaurant_id,
                :name
            )
        ");

        $stmt->execute([
            "restaurant_id" => $restaurantId,
            "name" => $name
        ]);

        $menuId = (int) $pdo->lastInsertId();

        sendSuccess(
            [
                "id" => $menuId,
                "restaurant_id" => $restaurantId,
                "name" => $name
            ],
            "Menu created successfully.",
            201
        );

    } catch (PDOException $e) {
        sendError("Unable to create menu.", 500);
    }
}


function updateMenu($menuId)
{
    global $pdo;

    $data = getJsonBody();

    $name = trim($data["name"] ?? "");

    if ($name === "") {
        sendError("Menu name is required.", 400);
    }

    try {
        $checkStmt = $pdo->prepare(
            "SELECT id FROM menus WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $menuId
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Menu not found.", 404);
        }

        $stmt = $pdo->prepare("
            UPDATE menus
            SET name = :name
            WHERE id = :id
        ");

        $stmt->execute([
            "name" => $name,
            "id" => $menuId
        ]);

        sendSuccess(
            [
                "id" => $menuId,
                "name" => $name
            ],
            "Menu updated successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to update menu.", 500);
    }
}


function deleteMenu($menuId)
{
    global $pdo;

    try {
        $checkStmt = $pdo->prepare(
            "SELECT id FROM menus WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $menuId
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Menu not found.", 404);
        }

        $stmt = $pdo->prepare(
            "DELETE FROM menus WHERE id = :id"
        );

        $stmt->execute([
            "id" => $menuId
        ]);

        sendSuccess(
            null,
            "Menu deleted successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to delete menu.", 500);
    }
}