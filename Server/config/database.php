<?php

class Database {
    private $host = "localhost";
    private $port = "27017";
    private $dbName = "sunburst";
    private $manager = null;

    public function connect() {
        if ($this->manager !== null) {
            return $this->manager;
        }
        try {
            $connectionString = "mongodb://" . $this->host . ":" . $this->port;
            $this->manager = new MongoDB\Driver\Manager($connectionString);
            return $this->manager;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Database Connection Failed: " . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getNamespace($collectionName) {
        return $this->dbName . "." . $collectionName;
    }
}
