<?php

namespace App\Model;

use PDO;
use PDOException;

abstract class AbstractCrud
{
    protected $table;

    protected $conn;

    public function __construct(PDO $conn, string $table)
    {
        $this->conn = $conn;
        $this->table = $table;
    }

    /**
     * Create a new record in the table.
     * @param array $data Associative array of column => value.
     * @return int Last inserted ID.
     */
    public function create(array $data): int
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($data));

            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Create failed: " . $e->getMessage());
        }
    }

    /**
     * Read records from the table.
     * @param array $criteria Associative array of column => value for WHERE clause.
     * @param array $columns Columns to fetch (default: all).
     * @return array Fetched rows.
     */
    public function read(array $criteria = [], array $columns = ['*']): array
    {
        try {
            $columnsStr = implode(', ', $columns);
            $sql = "SELECT $columnsStr FROM {$this->table}";
            if ($criteria) {
                $where = implode(' AND ', array_map(fn ($col) => "$col = ?", array_keys($criteria)));
                $sql .= " WHERE $where";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($criteria));

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Read failed: " . $e->getMessage());
        }
    }

    /**
     * Update records in the table.
     * @param array $data Associative array of column => value to update.
     * @param array $criteria Associative array of column => value for WHERE clause.
     * @return int Number of affected rows.
     */
    public function update(array $data, array $criteria): int
    {
        try {
            $set = implode(', ', array_map(fn ($col) => "$col = ?", array_keys($data)));
            $where = implode(' AND ', array_map(fn ($col) => "$col = ?", array_keys($criteria)));
            $sql = "UPDATE {$this->table} SET $set WHERE $where";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_merge(array_values($data), array_values($criteria)));

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new \Exception("Update failed: " . $e->getMessage());
        }
    }

    /**
     * Delete records from the table.
     * @param array $criteria Associative array of column => value for WHERE clause.
     * @return int Number of affected rows.
     */
    public function delete(array $criteria): int
    {
        try {
            $where = implode(' AND ', array_map(fn ($col) => "$col = ?", array_keys($criteria)));
            $sql = "DELETE FROM {$this->table} WHERE $where";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($criteria));

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new \Exception("Delete failed: " . $e->getMessage());
        }
    }
}
