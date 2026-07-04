<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 3. Simple routing match
if ($requestUri === '/' || $requestUri === '/index.php') {
    echo json_encode([
        "status" => "online",
        "message" => "Sunburst Server Run Succesfully",
        "version" => "1.0.0"
    ]);
} elseif ($requestUri === '/api/health') {
    echo json_encode([
        "status" => "healthy",
        "timestamp" => time()
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        "error" => "Endpoint not found",
        "requested_path" => $requestUri
    ]);
}
