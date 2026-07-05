<?php
// src/Models/Member.php

class Member {
    private $db;
    private $collection = "members";

    public function __construct($databaseInstance) {
        $this->db = $databaseInstance;
    }

    /**
     * Retrieve all members from the sunburst.members collection
     */
    public function getAll() {
        $manager = $this->db->connect();
        $namespace = $this->db->getNamespace($this->collection);

        // Empty array filter selects all documents in the collection
        $query = new MongoDB\Driver\Query([]);
        $cursor = $manager->executeQuery($namespace, $query);

        $result = [];
        foreach ($cursor as $document) {
            // Convert BSON documents to readable associative PHP arrays
            $result[] = (array)$document;
        }

        return $result;
    }
}
