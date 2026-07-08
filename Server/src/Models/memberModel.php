<?php

class Member {
    private $db;
    private $collection = "members";

    // Initialization: Establishes database settings
    public function __construct($databaseInstance) {
        $this->db = $databaseInstance;
    }

    // Fetch Operation: Retrieves all data entries unconditionally
    public function getAll() {
        return $this->getWithFilters([]);
    }

    // Query Operation: Processes data filtering, array lookups, and sorting
    public function getWithFilters($filtersPayload, $sortParams = []) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        $filter = [];

        foreach (['role', 'status'] as $field) {
            if (!empty($filtersPayload[$field])) {
                $filter[$field] = $filtersPayload[$field];
            }
        }

        if (!empty($filtersPayload['instrument'])) {
            $filter['instrument'] = ['$in' => [$filtersPayload['instrument']]];
        }

        $options = [];
        if (!empty($sortParams['sortBy'])) {
            $direction = (strtolower($sortParams['sortOrder'] ?? '') === 'desc') ? -1 : 1;
            $options['sort'] = [$sortParams['sortBy'] => $direction];
        }

        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $manager->executeQuery($namespace, $query);

        $result = [];
        foreach ($cursor as $document) {
            $array = (array)$document;

            if (isset($array['_id'])) {
                $array['_id'] = (string)$array['_id'];
            }

            if (isset($array['instrument']) && !is_array($array['instrument'])) {
                $array['instrument'] = [$array['instrument']];
            }

            $result[] = $array;
        }
        return $result;
    }

    // Create Operation: Normalizes payload structure and inserts a new document
    public function create($data) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        $instruments = $data['instrument'] ?? [];
        if (!is_array($instruments)) {
            $instruments = array_filter(explode(',', $instruments));
        }

        $documentPayload = [
            "name"        => $data['name'] ?? '',
            "email"       => $data['email'] ?? '',
            "password"    => password_hash($data['password'] ?? '12345678', PASSWORD_BCRYPT),
            "role"        => $data['role'] ?? 'member',
            "instrument"  => $instruments,
            "birthday"    => $data['birthday'] ?? null,
            "joined_in"   => $data['joined_in'] ?? null
        ];

        $bulkWrite = new MongoDB\Driver\BulkWrite;
        $bulkWrite->insert($documentPayload);

        $result = $manager->executeBulkWrite($namespace, $bulkWrite);
        return $result->getInsertedCount() > 0;
    }

    // Update Operation: Modifies an existing document strictly matching by target ObjectId
    public function update($id, $data) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);
        $bulkWrite = new MongoDB\Driver\BulkWrite;

        if (isset($data['instrument'])) {
            $instruments = $data['instrument'];
            if (!is_array($instruments)) {
                $instruments = array_filter(explode(',', $instruments));
            }
            $data['instrument'] = $instruments;
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        try {
            $mongoId = new MongoDB\BSON\ObjectId($id);
        } catch (Exception $e) {
            return false;
        }

        $bulkWrite->update(
            ['_id' => $mongoId],
            ['$set' => $data],
            ['multi' => false, 'upsert' => false]
        );

        $result = $manager->executeBulkWrite($namespace, $bulkWrite);
        return $result->getModifiedCount() > 0 || $result->getMatchedCount() > 0;
    }

    // Delete Operation: Permanently removes a single target document from the collection
    public function delete($id) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        $bulkWrite = new MongoDB\Driver\BulkWrite;
        $bulkWrite->delete(['_id' => new MongoDB\BSON\ObjectId($id)], ['limit' => 1]);

        $result = $manager->executeBulkWrite($namespace, $bulkWrite);
        return $result->getDeletedCount() > 0;
    }
}
