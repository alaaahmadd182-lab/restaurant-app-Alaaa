<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/request.php";


function getMenuItems($menuId)
{
    global $pdo;

    try {
        // Check menu exists
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
            SELECT
                id,
                menu_id,
                name,
                description,
                price,
                image
            FROM menu_items
            WHERE menu_id = :menu_id
            ORDER BY id ASC
        ");

        $stmt->execute([
            "menu_id" => $menuId
        ]);

        $items = $stmt->fetchAll();

        sendSuccess(
            $items,
            "Menu items retrieved successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to retrieve menu items.", 500);
    }
}


function createMenuItem($menuId)
{
    global $pdo;

    $data = getJsonBody();

    $name = trim($data["name"] ?? "");
    $description = trim($data["description"] ?? "");
    $image = trim($data["image"] ?? "");
    $price = $data["price"] ?? null;

    if ($name === "") {
        sendError("Item name is required.", 400);
    }

    if ($price === null || !is_numeric($price) || $price < 0) {
        sendError("A valid non-negative price is required.", 400);
    }

    try {
        // Check menu exists
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
            INSERT INTO menu_items
            (
                menu_id,
                name,
                description,
                price,
                image
            )
            VALUES
            (
                :menu_id,
                :name,
                :description,
                :price,
                :image
            )
        ");

        $stmt->execute([
            "menu_id" => $menuId,
            "name" => $name,
            "description" => $description !== "" ? $description : null,
            "price" => $price,
            "image" => $image !== "" ? $image : null
        ]);

        $itemId = (int) $pdo->lastInsertId();

        sendSuccess(
            [
                "id" => $itemId,
                "menu_id" => $menuId,
                "name" => $name
            ],
            "Menu item created successfully.",
            201
        );

    } catch (PDOException $e) {
        sendError("Unable to create menu item.", 500);
    }
}


function updateMenuItem($itemId)
{
    global $pdo;

    $data = getJsonBody();

    $name = trim($data["name"] ?? "");
    $description = trim($data["description"] ?? "");
    $image = trim($data["image"] ?? "");
    $price = $data["price"] ?? null;

    if ($name === "") {
        sendError("Item name is required.", 400);
    }

    if ($price === null || !is_numeric($price) || $price < 0) {
        sendError("A valid non-negative price is required.", 400);
    }

    try {
        $checkStmt = $pdo->prepare(
            "SELECT id FROM menu_items WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $itemId
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Menu item not found.", 404);
        }

        $stmt = $pdo->prepare("
            UPDATE menu_items
            SET
                name = :name,
                description = :description,
                price = :price,
                image = :image
            WHERE id = :id
        ");

        $stmt->execute([
            "name" => $name,
            "description" => $description !== "" ? $description : null,
            "price" => $price,
            "image" => $image !== "" ? $image : null,
            "id" => $itemId
        ]);

        sendSuccess(
            ["id" => $itemId],
            "Menu item updated successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to update menu item.", 500);
    }
}


function deleteMenuItem($itemId)
{
    global $pdo;

    try {
        $checkStmt = $pdo->prepare(
            "SELECT id FROM menu_items WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $itemId
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Menu item not found.", 404);
        }

        $stmt = $pdo->prepare(
            "DELETE FROM menu_items WHERE id = :id"
        );

        $stmt->execute([
            "id" => $itemId
        ]);

        sendSuccess(
            null,
            "Menu item deleted successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to delete menu item.", 500);
    }
}