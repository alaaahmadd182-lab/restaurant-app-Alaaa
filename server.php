<?php

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== "/" && file_exists($file) && !is_dir($file)) {
    return false;
}

if (str_starts_with($uri, "/backend/api")) {
    require __DIR__ . "/backend/api/index.php";
    return true;
}

return false;