<?php

require_once __DIR__ . "/response.php";
//takes incoming json and turns it into arrayy
function getJsonBody()
{
    $rawBody = file_get_contents("php://input");

    if (empty($rawBody)) {
        sendError("Request body is required.", 400);
    }

    $data = json_decode($rawBody, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        sendError("Invalid JSON body.", 400);
    }

    return $data;
}