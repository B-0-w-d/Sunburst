<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$requestUri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

if ($requestUri === "/api/members") {
    echo json_encode([
        "status" => "success",
        "endpoint" => "Members List",
        "method" => $method,
        "message" => "Successfully hit the members endpoint!",
    ]);
} elseif ($requestUri === "/api/shows") {
    echo json_encode([
        "status" => "success",
        "endpoint" => "Shows List",
        "method" => $method,
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        "error" => "Endpoint not found",
        "your_requested_path" => $requestUri,
    ]);
}
