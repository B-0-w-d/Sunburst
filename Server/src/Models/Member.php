<?php

class Member {
    private $db;
    private $collection = "members";

    public function __construct($databaseInstance) {
        $this->db = $databaseInstance;
    }

    // Get members from DB
    public function getAll() {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        $query = new MongoDB\Driver\Query([]);
        $cursor = $manager->executeQuery($namespace, $query);

        $result = [];
        foreach ($cursor as $document) {
            $array = (array)$document;
            if (isset($array['_id']) && is_object($array['_id'])) {
                $array['_id'] = (string)$array['_id'];
            }

            $result[] = $array;
        }

        return $result;
    }

    // Insert member Document
    public function create($documentPayload) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        $bulkWrite = new MongoDB\Driver\BulkWrite;
        $bulkWrite->insert($documentPayload);

        $result = $manager->executeBulkWrite($namespace, $bulkWrite);
        return $result->getInsertedCount() > 0;
    }
}
