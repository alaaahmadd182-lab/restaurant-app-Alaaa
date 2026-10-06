<?php

require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../controllers/RestaurantController.php";
require_once __DIR__ . "/../controllers/AuthController.php";
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../controllers/CategoryController.php";
require_once __DIR__ . "/../controllers/MenuController.php";




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
            requireAdmin();
            createRestaurant();

        }

        sendError("Method not allowed.", 405);
    }

    // -----------------------------
    // SINGLE RESTAURANT
    // Example: /restaurants/5
    // -----------------------------
    if (preg_match("#^restaurants/(\d+)$#", $path, $matches)) {

        $restaurantId = (int) $matches[1];

        if ($method === "GET") {//doesnt require admin because any user can read data
            getRestaurantById($restaurantId);
        }

        if ($method === "PUT") {
            requireAdmin();
            updateRestaurant($restaurantId);
        }

        if ($method === "DELETE") {
            requireAdmin();
            deleteRestaurant($restaurantId);
        }

        sendError("Method not allowed.", 405);
    }
// -----------------------------
// AUTHENTICATION
// -----------------------------
    if ($path === "auth/signup") {

        if ($method === "POST") {
            signup();
        }

        sendError("Method not allowed.", 405);
    }
    if ($path === "auth/login") {

        if ($method === "POST") {
            login();
        }

        sendError("Method not allowed.", 405);
    }


// -----------------------------
// CATEGORIES
// -----------------------------
//public endpoint no require admin func
    if ($path === "categories") {

        if ($method === "GET") {
            getCategories();
        }

        sendError("Method not allowed.", 405);
    }


// -----------------------------
// RESTAURANT MENUS
// -----------------------------
if (preg_match("#^restaurants/(\d+)/menus$#", $path, $matches)) {

    $restaurantId = (int) $matches[1];

    if ($method === "GET") {
        getMenusByRestaurant($restaurantId);
    }

    if ($method === "POST") {
        requireAdmin();
        createMenu($restaurantId);
    }

    sendError("Method not allowed.", 405);
}


// -----------------------------
// SINGLE MENU
// -----------------------------
    if (preg_match("#^menus/(\d+)$#", $path, $matches)) {

        $menuId = (int) $matches[1];

        if ($method === "PUT") {
            requireAdmin();
            updateMenu($menuId);
        }

        if ($method === "DELETE") {
            requireAdmin();
            deleteMenu($menuId);
        }

        sendError("Method not allowed.", 405);
    }






    // Nothing matched
    sendError("Route not found.", 404);
}