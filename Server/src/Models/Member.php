<?php

class Member {
    private $db;
    private $collection = "members";

    public function __construct($databaseInstance) {
        $this->db = $databaseInstance;
    }

    // 1. Get all members
    public function getAll() {
        return $this->getWithFilters([]);
    }

    // 2. Sort function (roles, instruments)
    public function getWithFilters($filtersPayload) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        // Create empty Filter for Mongo
        $filter = [];

        // Sort through roles
        if (!empty($filtersPayload['role'])) {
            $filter['role'] = $filtersPayload['role'];
        }

        // Sort through Instruments
        if (!empty($filtersPayload['instrument'])) {
            $filter['instrument'] = $filtersPayload['instrument'];
        }

        $query = new MongoDB\Driver\Query($filter);
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

    // 3. Add member
    public function create($data) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        // Check payload before add in
        $documentPayload = [
            "name"        => $data['name'] ?? '',
            "email"       => $data['email'] ?? '',
            "password"    => password_hash($data['password'] ?? '123456', PASSWORD_BCRYPT),
            "role"        => $data['role'] ?? 'member',
            "instrument"  => $data['instrument'] ?? '',
            "birthday"    => $data['birthday'] ?? null,
            "joined_in"   => $data['joined_in'] ?? null,
        ];

        $bulkWrite = new MongoDB\Driver\BulkWrite;
        $bulkWrite->insert($documentPayload);

        $result = $manager->executeBulkWrite($namespace, $bulkWrite);
        return $result->getInsertedCount() > 0;
    }
}
