<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/request.php";


function getRestaurants(){
    global $pdo;
    try {
        // -----------------------------
        // QUERY PARAMETERS
        // -----------------------------
        $search = trim($_GET["search"] ?? "");
        $category = $_GET["category"] ?? null;

        $page = filter_var(
            $_GET["page"] ?? 1,
            FILTER_VALIDATE_INT
        );

        $limit = filter_var(
            $_GET["limit"] ?? 10,
            FILTER_VALIDATE_INT
        );

        // Validate page
        if ($page === false || $page < 1) {
            sendError("Page must be a positive integer.", 400);
        }

        // Validate limit and prevent huge requests
        if ($limit === false || $limit < 1 || $limit > 50) {
            sendError("Limit must be between 1 and 50.", 400);
        }

        // Validate category when provided
        if ($category !== null && $category !== "") {
            $category = filter_var(
                $category,
                FILTER_VALIDATE_INT
            );

            if ($category === false || $category < 1) {
                sendError("Invalid category.", 400);
            }
        } else {
            $category = null;
        }

        $offset = ($page - 1) * $limit;

        // -----------------------------
        // BUILD FILTERS
        // -----------------------------
        $conditions = [];
        $params = [];

        if ($search !== "") {
            $conditions[] = "
                (
                    restaurants.name LIKE :search_name
                    OR restaurants.description LIKE :search_description
                    OR restaurants.address LIKE :search_address
                )
            ";

            $searchValue = "%" . $search . "%";

            $params["search_name"] = $searchValue;
            $params["search_description"] = $searchValue;
            $params["search_address"] = $searchValue;
        }

        if ($category !== null) {
            $conditions[] = "restaurants.category_id = :category";
            $params["category"] = $category;
        }

        $whereClause = "";

        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }

        // -----------------------------
        // COUNT MATCHING RESTAURANTS
        // -----------------------------
        $countSql = "
            SELECT COUNT(*)
            FROM restaurants
            $whereClause
        ";

        $countStmt = $pdo->prepare($countSql);

        foreach ($params as $key => $value) {
            $type = $key === "category"
                ? PDO::PARAM_INT
                : PDO::PARAM_STR;

            $countStmt->bindValue(
                ":" . $key,
                $value,
                $type
            );
        }

        $countStmt->execute();

        $total = (int) $countStmt->fetchColumn();

        // -----------------------------
        // GET RESTAURANTS
        // -----------------------------
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

            $whereClause

            ORDER BY restaurants.id DESC

            LIMIT :limit
            OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $value) {
            if ($key === "category") {
                $stmt->bindValue(
                    ":category",
                    $value,
                    PDO::PARAM_INT
                );
            } else {
                $stmt->bindValue(
                    ":" . $key,
                    $value,
                    PDO::PARAM_STR
                );
            }
        }

        $stmt->bindValue(
            ":limit",
            $limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":offset",
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $restaurants = $stmt->fetchAll();

        $totalPages = $total > 0
            ? (int) ceil($total / $limit)
            : 0;

        sendSuccess(
            [
                "restaurants" => $restaurants,

                "pagination" => [
                    "current_page" => $page,
                    "per_page" => $limit,
                    "total_items" => $total,
                    "total_pages" => $totalPages
                ]
            ],
            "Restaurants retrieved successfully."
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

function createRestaurant()
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

function updateRestaurant($id)
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

    if ($name === "" || $address === "" || $categoryId === false) {
        sendError(
            "Name, address and a valid category_id are required.",
            400
        );
    }

    if ($latitude !== null &&
        (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
        sendError("Invalid latitude.", 400);
    }

    if ($longitude !== null &&
        (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
        sendError("Invalid longitude.", 400);
    }

    try {
        // Check restaurant exists
        $checkStmt = $pdo->prepare(
            "SELECT id FROM restaurants WHERE id = :id"
        );

        $checkStmt->execute(["id" => $id]);

        if (!$checkStmt->fetch()) {
            sendError("Restaurant not found.", 404);
        }

        // Check category exists
        $categoryStmt = $pdo->prepare(
            "SELECT id FROM categories WHERE id = :id"
        );

        $categoryStmt->execute([
            "id" => $categoryId
        ]);

        if (!$categoryStmt->fetch()) {
            sendError("Category not found.", 400);
        }

        $sql = "
            UPDATE restaurants
            SET
                category_id = :category_id,
                name = :name,
                description = :description,
                address = :address,
                image = :image,
                latitude = :latitude,
                longitude = :longitude
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "category_id" => $categoryId,
            "name" => $name,
            "description" => $description !== "" ? $description : null,
            "address" => $address,
            "image" => $image !== "" ? $image : null,
            "latitude" => $latitude,
            "longitude" => $longitude,
            "id" => $id
        ]);

        sendSuccess(
            ["id" => $id],
            "Restaurant updated successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to update restaurant.", 500);
    }
}

function deleteRestaurant($id)
{
    global $pdo;

    try {
        $checkStmt = $pdo->prepare(
            "SELECT id FROM restaurants WHERE id = :id"
        );

        $checkStmt->execute([
            "id" => $id
        ]);

        if (!$checkStmt->fetch()) {
            sendError("Restaurant not found.", 404);
        }

        $stmt = $pdo->prepare(
            "DELETE FROM restaurants WHERE id = :id"
        );

        $stmt->execute([
            "id" => $id
        ]);

        sendSuccess(
            null,
            "Restaurant deleted successfully."
        );

    } catch (PDOException $e) {
        sendError("Unable to delete restaurant.", 500);
    }
}