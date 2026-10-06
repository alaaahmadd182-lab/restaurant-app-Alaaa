<?php

require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../controllers/RestaurantController.php";
function routeRequest()
{
    $method = $_SERVER["REQUEST_METHOD"];

    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    // Remove the API base path
    $basePath = "/backend/api";
    $path = str_replace($basePath, "", $uri);

    // Clean trailing slashes
    $path = trim($path, "/");

    // -----------------------------
    // API HOME
    // -----------------------------
    if ($path === "") {
        if ($method !== "GET") {
            sendError("Method not allowed.", 405);
        }

        sendSuccess(null, "Restaurant API is running");
    }

    // -----------------------------
    // RESTAURANTS
    // -----------------------------
    if ($path === "restaurants") {

        if ($method === "GET") {
            getRestaurants();
        }

        if ($method === "POST") {
            sendSuccess(null, "Restaurant creation route reached", 201);
        }

        sendError("Method not allowed.", 405);
    }

    // -----------------------------
    // SINGLE RESTAURANT
    // Example: /restaurants/5
    // -----------------------------
    if (preg_match("#^restaurants/(\d+)$#", $path, $matches)) {

        $restaurantId = (int) $matches[1];

        if ($method === "GET") {
            getRestaurantById($restaurantId);
        }

        if ($method === "PUT") {
            sendSuccess(
                ["id" => $restaurantId],
                "Restaurant update route reached"
            );
        }

        if ($method === "DELETE") {
            sendSuccess(
                ["id" => $restaurantId],
                "Restaurant delete route reached"
            );
        }

        sendError("Method not allowed.", 405);
    }

    // Nothing matched
    sendError("Route not found.", 404);
}