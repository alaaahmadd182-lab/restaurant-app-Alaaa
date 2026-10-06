<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";

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
}