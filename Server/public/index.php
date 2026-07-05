<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Member.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$db = new Database();

if ($requestUri === '/members') {
    if ($method === 'GET') {
        $memberModel = new Member($db);
        $membersList = $memberModel->getAll();

        echo json_encode([
            "status" => "success",
            "count" => count($membersList),
            "data" => $membersList
        ]);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint not found"]);
}
