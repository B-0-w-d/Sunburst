<?php

class Member {
    private $db;
    private $collection = "members";

    public function __construct($databaseInstance) {
        $this->db = $databaseInstance;
    }

    // Get all members
    public function getAll() {
        return $this->getWithFilters([]);
    }

    // Sort (Role and Instrument) and filter (Birthdate and Joineddate) function
    public function getWithFilters($filtersPayload, $sortParams = []) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        $filter = [];


        foreach (['role', 'status'] as $field) {
            if (!empty($filtersPayload[$field])) {
                $filter[$field] = $filtersPayload[$field];
            }
        }

        // Check if instruments need to be sorted in members
        if (!empty($filtersPayload['instrument'])) {
            $filter['instrument'] = ['$in' => [$filtersPayload['instrument']]];
        }

        // Sort Logic
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

            // Make sure old and new response data comes back cleanly after filters and sorts
            if (isset($array['instrument']) && !is_array($array['instrument'])) {
                $array['instrument'] = [$array['instrument']];
            }

            $result[] = $array;
        }
        return $result;
    }

    // Add member
    public function create($data) {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        // Access instrument type input, ect: "Vocal,Bass"
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

    // Update member through ID
    public function update($id, $data) {
            $manager = $this->db->connect();
            $namespace = $this->db->getNamespace($this->collection);
            $bulkWrite = new MongoDB\Driver\BulkWrite;

            // Clean up instrument formatting if provided
            if (isset($data['instrument'])) {
                $instruments = $data['instrument'];
                if (!is_array($instruments)) {
                    $instruments = array_filter(explode(',', $instruments));
                }
                $data['instrument'] = $instruments;
            }

            // Handle password hashing securely
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            } else {
                unset($data['password']); // Keep old password if not changing
            }

            // Convert string $id into a real MongoDB ObjectId
            try {
                $mongoId = new MongoDB\BSON\ObjectId($id);
            } catch (Exception $e) {
                return false;
            }

            // Force an update matching exactly by _id, and turn upsert OFF
            $bulkWrite->update(
                ['_id' => $mongoId],          // The exact filter matching target record
                ['$set' => $data],            // The data payload to overwrite fields
                ['multi' => false, 'upsert' => false] //
            );

            $result = $manager->executeBulkWrite($namespace, $bulkWrite);

            // Returns true if modified or if the record matched but data remained identical
            return $result->getModifiedCount() > 0 || $result->getMatchedCount() > 0;
        }

    // Delete member
    public function delete($id) {
            $manager = $this->db->connect();
            $namespace = $this->db->getNamespace($this->collection);

            $bulkWrite = new MongoDB\Driver\BulkWrite;

            $bulkWrite->delete(['_id' => new MongoDB\BSON\ObjectId($id)], ['limit' => 1]);

            $result = $manager->executeBulkWrite($namespace, $bulkWrite);
            return $result->getDeletedCount() > 0;
        }
    }
