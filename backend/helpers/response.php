<?php

function sendJsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode($data);
    exit;
}

function sendError($message, $statusCode = 400)
{
    sendJsonResponse([
        "success" => false,
        "message" => $message
    ], $statusCode);
}

function sendSuccess($data = null, $message = "Success", $statusCode = 200)
{
    sendJsonResponse([
        "success" => true,
        "message" => $message,
        "data" => $data
    ], $statusCode);
}