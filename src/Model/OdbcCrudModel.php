<?php

namespace App\Model;



class OdbcCrudModel extends Model
{

    public function create($tableName, $data)
    {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_map(function ($value) {
            return "'$value'";
        }, array_values($data)));
        $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";
        $result = odbc_exec($this->connexion, $sql);
        if (!$result) {
            echo "Erreur lors de la création de l'enregistrement: " . odbc_errormsg($this->connexion);
        }
        return $result;
    }

    public function read($tableName, $columns = "*", $conditions = "", $orderBy = "", $limit = null)
    {
        if (is_array($columns)) {
            $columns = implode(", ", $columns);
        }

        $sql = "SELECT $columns FROM $tableName";

        if ($conditions) {
            $sql .= " WHERE $conditions";
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if (!is_null($limit)) {
            $sql .= " LIMIT $limit";
        }

        $result = odbc_exec($this->connexion, $sql);
        if (!$result) {
            echo "Erreur lors de la lecture des données: " . odbc_errormsg($this->connexion);
            return false;
        }

        $rows = [];
        while ($row = odbc_fetch_array($result)) {
            $rows[] = $row;
        }

        return $rows;
    }



    public function update($tableName, $data, $conditions)
    {
        $updates = implode(", ", array_map(function ($key, $value) {
            return "$key = '$value'";
        }, array_keys($data), array_values($data)));
        $sql = "UPDATE $tableName SET $updates WHERE $conditions";
        $result = odbc_exec($this->connexion, $sql);
        if (!$result) {
            echo "Erreur lors de la mise à jour de l'enregistrement: " . odbc_errormsg($this->connexion);
        }
        return $result;
    }

    public function delete($tableName, $conditions)
    {
        $sql = "DELETE FROM $tableName WHERE $conditions";
        $result = odbc_exec($this->connexion, $sql);
        if (!$result) {
            echo "Erreur lors de la suppression de l'enregistrement: " . odbc_errormsg($this->connexion);
        }
        return $result;
    }
}
