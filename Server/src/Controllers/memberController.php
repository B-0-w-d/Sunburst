<?php

require_once __DIR__ . '/../Models/Member.php';

class MemberController {
    private $memberModel;

    public function __construct($db) {
        $this->memberModel = new Member($db);
    }

    public function handleRequest($method) {
        switch ($method) {
            case 'GET':
                $this->listMembers();
                break;
            case 'POST':
                $this->createMember();
                break;
            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(["error" => "HTTP Method dynamic match failed"]);
                break;
        }
    }

    private function listMembers() {
        $membersList = $this->memberModel->getAll();

        echo json_encode([
            "status" => "success",
            "count" => count($membersList),
            "data" => $membersList
        ]);
    }

    private function createMember() {
        // Read input from frontend
        $jsonPayload = file_get_contents('php://input');
        $data = json_decode($jsonPayload, true) ?? [];

        // Structural checking
        if (empty($data['name']) || empty($data['email'])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                "status" => "error",
                "message" => "Name and Email are strictly required fields."
            ]);
            return;
        }

        // Cryptographically password
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        // Add member
        $success = $this->memberModel->create($data);

        if ($success) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "New band member document added successfully!"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to write document into collection"
            ]);
        }
    }
}
