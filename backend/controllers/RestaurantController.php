<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/request.php";
function getRestaurants()
{
    global $pdo;

    try {
        $sql = "
            SELECT
                restaurants.id,
                restaurants.name,
                restaurants.description,
                restaurants.address,
                restaurants.image,
                restaurants.latitude,
                restaurants.longitude,
                categories.id AS category_id,
                categories.name AS category_name
            FROM restaurants
            INNER JOIN categories
                ON restaurants.category_id = categories.id
            ORDER BY restaurants.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $restaurants = $stmt->fetchAll();

        sendSuccess(
            $restaurants,
            "Restaurants retrieved successfully"
        );

    } catch (PDOException $e) {
        sendError("Unable to retrieve restaurants.", 500);
    }
}
function getRestaurantById($id)
{
    global $pdo;

    try {
        $sql = "
            SELECT
                restaurants.id,
                restaurants.name,
                restaurants.description,
                restaurants.address,
                restaurants.image,
                restaurants.latitude,
                restaurants.longitude,
                categories.id AS category_id,
                categories.name AS category_name
            FROM restaurants
            INNER JOIN categories
                ON restaurants.category_id = categories.id
            WHERE restaurants.id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "id" => $id
        ]);

        $restaurant = $stmt->fetch();

        if (!$restaurant) {
            sendError("Restaurant not found.", 404);
        }

        sendSuccess(
            $restaurant,
            "Restaurant retrieved successfully"
        );

    } catch (PDOException $e) {
        sendError("Unable to retrieve restaurant.", 500);
    }
}function createRestaurant()
{
    global $pdo;

    $data = getJsonBody();

    $name = trim($data["name"] ?? "");
    $description = trim($data["description"] ?? "");
    $address = trim($data["address"] ?? "");
    $image = trim($data["image"] ?? "");

    $categoryId = filter_var(
        $data["category_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    $latitude = $data["latitude"] ?? null;
    $longitude = $data["longitude"] ?? null;

    // Required fields
    if ($name === "" || $address === "" || $categoryId === false) {
        sendError(
            "Name, address and a valid category_id are required.",
            400
        );
    }

    // Validate coordinates when provided
    if ($latitude !== null &&
        (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
        sendError("Invalid latitude.", 400);
    }

    if ($longitude !== null &&
        (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
        sendError("Invalid longitude.", 400);
    }

    try {

        // Make sure category exists
        $categoryStmt = $pdo->prepare(
            "SELECT id FROM categories WHERE id = :id"
        );

        $categoryStmt->execute([
            "id" => $categoryId
        ]);

        if (!$categoryStmt->fetch()) {
            sendError("Category not found.", 400);
        }

        // Insert restaurant
        $sql = "
            INSERT INTO restaurants
            (
                category_id,
                name,
                description,
                address,
                image,
                latitude,
                longitude
            )
            VALUES
            (
                :category_id,
                :name,
                :description,
                :address,
                :image,
                :latitude,
                :longitude
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "category_id" => $categoryId,
            "name" => $name,
            "description" => $description !== "" ? $description : null,
            "address" => $address,
            "image" => $image !== "" ? $image : null,
            "latitude" => $latitude,
            "longitude" => $longitude
        ]);

        $restaurantId = (int) $pdo->lastInsertId();

        sendSuccess(
            ["id" => $restaurantId],
            "Restaurant created successfully.",
            201
        );

    } catch (PDOException $e) {
        sendError("Unable to create restaurant.", 500);
    }
}