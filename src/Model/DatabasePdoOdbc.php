<?php

namespace App\Model;

use PDO;
use PDOException;

class DatabasePdoOdbc
{
    // private $dsn;
    // private $user;
    // private $password;
    // public $conn;

    // public function __construct($dsn, $user, $password)
    // {
    //     $this->dsn = $dsn;
    //     $this->user = $user;
    //     $this->password = $password;
    //     $this->connect();
    // }

    // private function connect()
    // {
    //     try {
    //         $this->conn = new PDO($this->dsn, $this->user, $this->password);
    //         $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //         //echo "Connexion réussie.";
    //     } catch (PDOException $e) {
    //         die("Erreur de connexion : " . $e->getMessage());
    //     }
    // }

    // public function create($table, $data)
    // {
    //     $columns = implode(", ", array_keys($data));
    //     $values = implode(", ", array_map(function ($value) {
    //         return "?";
    //     }, $data));
    //     $stmt = $this->conn->prepare("INSERT INTO $table ($columns) VALUES ($values)");
    //     return $stmt->execute(array_values($data));
    // }

    // public function readAll($table)
    // {
    //     $stmt = $this->conn->query("SELECT * FROM $table");
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // public function readByCriteria($table, $criteria)
    // {
    //     $condition = implode(" AND ", array_map(function ($key) {
    //         return "$key = ?";
    //     }, array_keys($criteria)));
    //     $stmt = $this->conn->prepare("SELECT * FROM $table WHERE $condition");
    //     $stmt->execute(array_values($criteria));
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // public function update($table, $data, $criteria)
    // {
    //     $dataKeyValues = implode(", ", array_map(function ($key) {
    //         return "$key = ?";
    //     }, array_keys($data)));
    //     $condition = implode(" AND ", array_map(function ($key) {
    //         return "$key = ?";
    //     }, array_keys($criteria)));
    //     $stmt = $this->conn->prepare("UPDATE $table SET $dataKeyValues WHERE $condition");
    //     return $stmt->execute(array_merge(array_values($data), array_values($criteria)));
    // }

    // public function delete($table, $criteria)
    // {
    //     $condition = implode(" AND ", array_map(function ($key) {
    //         return "$key = ?";
    //     }, array_keys($criteria)));
    //     $stmt = $this->conn->prepare("DELETE FROM $table WHERE $condition");
    //     return $stmt->execute(array_values($criteria));
    // }
}
