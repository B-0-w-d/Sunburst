<?php

require_once __DIR__ . '/../Models/memberModel.php';

class MemberController {
    private $memberModel;

    // Constructor: Initializes Database Model connection
    public function __construct($db) {
        $this->memberModel = new Member($db);
    }

    // API Standard Helper: Centralizes JSON formatting & HTTP status codes
    private function jsonResponse($status, $payload, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(["status" => $status], $payload));
        return;
    }

    // Central Request Router:
    public function handleRequest($method) {
        $id = $_GET['id'] ?? null;

        switch ($method) {
            case 'GET':    $this->listMembers(); break;
            case 'POST':   $this->createMember(); break;
            case 'PUT':    $id ? $this->updateMember($id) : $this->jsonResponse("error", ["message" => "Missing ID"], 400); break;
            case 'DELETE': $id ? $this->deleteMember($id) : $this->jsonResponse("error", ["message" => "Missing ID"], 400); break;
            default:       $this->jsonResponse("error", ["message" => "Method not allowed"], 405); break;
        }
    }

    // Query Operation: Handles reading data with optional filtering & sorting
    private function listMembers() {
        $filters = array_filter(['role' => $_GET['role'] ?? null, 'instrument' => $_GET['instrument'] ?? null]);
        $sort = ['sortBy' => $_GET['sortBy'] ?? null, 'sortOrder' => $_GET['sortOrder'] ?? 'asc'];

        $list = $this->memberModel->getWithFilters($filters, $sort);
        $this->jsonResponse("success", ["count" => count($list), "data" => $list]);
    }

    // Mutate Operation: Validates and inserts raw JSON payload into database
    private function createMember() {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data['name']) || empty($data['email'])) {
            return $this->jsonResponse("error", ["message" => "Name and Email are strictly required."], 400);
        }

        $this->memberModel->create($data)
            ? $this->jsonResponse("success", ["message" => "Added successfully!"], 201)
            : $this->jsonResponse("error", ["message" => "Database write failed."], 500);
    }
    private function updateMember($id) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];

            if (empty($data)) {
                return $this->jsonResponse("error", ["message" => "No data provided for update."], 400);
            }

            $this->memberModel->update($id, $data)
                ? $this->jsonResponse("success", ["message" => "Member updated successfully."])
                : $this->jsonResponse("error", ["message" => "Failed to update member or data unchanged."], 500);
        }

        private function deleteMember($id) {
            $this->memberModel->delete($id)
                ? $this->jsonResponse("success", ["message" => "Member deleted successfully."])
                : $this->jsonResponse("error", ["message" => "Member not found or already deleted."], 404);
        }
}
