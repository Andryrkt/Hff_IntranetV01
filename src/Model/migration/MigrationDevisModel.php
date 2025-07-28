<?php

namespace App\Model\migration;

use App\Model\Model;

class MigrationDevisModel extends Model
{
    private $tableName;

    private $tab;

    public function __construct($tableName, $data = [])
    {
        parent::__construct();
        $this->tableName = $tableName;
        $this->tab = $data;
    }

    // public function insertDevisMigration()
    // {
    //     $query = $this->requestCreate();

    //     $this->connexion->query($query);
    // }

    public function insertDevisMigration()
    {
        $query = $this->requestCreate();

        // Préparation de la requête
        $stmt = odbc_prepare($this->connexion->getConnexion(), $query);

        // Correction : Convertir et échapper les valeurs
        $values = array_map(function ($value) {
            if ($value instanceof \DateTime) {
                return $value->format('Y-m-d H:i:s'); // Conversion DateTime -> string
            } elseif ($value === '') {
                return null; // Éviter les chaînes vides dans SQL Server
            } elseif (is_string($value)) {
                return utf8_encode($value); // Éviter les problèmes d'encodage
            }

            return $value; // Retourner tel quel pour les entiers et décimaux
        }, array_values($this->tab));

        // Exécuter la requête
        odbc_execute($stmt, $values);
    }

    private function requestCreate()
    {
        $tableName = $this->tableName;
        $champTable = array_keys($this->tab);

        // Création des placeholders `?`
        $placeholders = implode(',', array_fill(0, count($champTable), '?'));

        // Génération de la requête SQL avec des `?`
        return "INSERT INTO $tableName (" . implode(',', $champTable) . ") VALUES ($placeholders)";
    }


    // private function requestCreate()
    // {
    //     $tableName    = $this->tableName;
    //     $champTable   = array_keys($this->tab);
    //     $values = array_values($this->tab);
    //     $columns      = implode(',', $champTable);
    //     $placeholders = implode(',', array_map(fn ($value) => $value, $values));
    //     return  "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
    // }

    public function selectDevisMIgration($conditions = [], $champ = "*")
    {
        $query = $this->executeSelect($conditions, $champ);
        $sql = $this->connexion->query($query);
        $result = [];
        while ($tab = odbc_fetch_array($sql)) {
            $result[] = $tab;
        }

        return $result;
    }

    public function executeSelect($conditions = [], $champ = "*")
    {
        $whereCondition = "";

        if (! is_array($champ)) {
            $champ = [$champ]; // Convertir en tableau si une seule colonne est donnée
        }
        $columns = implode(',', $champ);

        $conditionClauses = [];

        foreach ($conditions as $key => $valeur) {
            $conditionClauses[] = "$key = $valeur";
        }

        if (! empty($conditions)) {
            $whereCondition = " WHERE " . implode(' AND ', $conditionClauses);
        }

        return  "SELECT $columns FROM $this->tableName $whereCondition";
    }

    /**
     * UPDATE
     */
    public function updateGeneralise(string $table, array $tabUpdate, array $condition)
    {
        if (empty($tabUpdate) || empty($condition)) {
            throw new \Exception("Les données ou la condition de mise à jour ne peuvent pas être vides.");
        }

        // Génération dynamique des colonnes et des valeurs
        $setClause = implode(", ", array_map(fn ($key) => "$key = ?", array_keys($tabUpdate)));
        $whereClause = implode(" AND ", array_map(fn ($key) => "$key = ?", array_keys($condition)));

        // Construction de la requête SQL
        $query = "UPDATE $table SET $setClause WHERE $whereClause";

        // Préparation de la requête
        $stmt = odbc_prepare($this->connexion->getConnexion(), $query);

        // Conversion et sécurisation des valeurs pour l'exécution
        $values = array_map(function ($value) {
            if ($value instanceof \DateTime) {
                return $value->format('Y-m-d H:i:s'); // Conversion DateTime -> string
            } elseif ($value === '') {
                return null; // Éviter les chaînes vides
            } elseif (is_string($value)) {
                return utf8_encode($value); // Gérer l'encodage
            }

            return $value; // Retourner les autres types tels quels
        }, array_merge(array_values($tabUpdate), array_values($condition))); // Fusion des valeurs SET et WHERE

        // Exécution de la requête
        return odbc_execute($stmt, $values);
    }
}
