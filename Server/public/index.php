<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Controllers/MemberController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$db = new Database();

if ($requestUri === '/members') {
    $controller = new MemberController($db);
    $controller->handleRequest($method);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint not found", "path" => $requestUri]);
}
