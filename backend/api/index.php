<?php

require_once __DIR__ . "/../helpers/response.php";

$method = $_SERVER["REQUEST_METHOD"];

sendSuccess(
    [
        "method" => $method
    ],
    "Restaurant API is running"
);