<?php

namespace App\Model\admin\user;

use App\Model\Model;
use ReflectionClass;
use ReflectionProperty;

class ProfilUserModel extends Model
{
      public function insertData($tableName, $data) {
        // Utiliser la réflexion pour obtenir les propriétés de l'objet
        $reflectionClass = new ReflectionClass($data);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        
        $columns = [];
        $values = [];
        
        foreach ($properties as $property) {
            $property->setAccessible(true); // Rendre la propriété accessible
            $columns[] = $property->getName();
            $values[] = $property->getValue($data);
        }
        
        // Log les colonnes et les valeurs pour déboguer
        error_log("Columns: " . implode(", ", $columns));
        error_log("Values: " . implode(", ", $values));

        // Vérifier si les colonnes et les valeurs ne sont pas vides
        if (empty($columns) || empty($values)) {
            die("Erreur : L'objet de données est vide ou mal formé.");
        }

        // Créer une chaîne pour les noms de colonnes
        $columnString = implode(", ", $columns);

        // Créer une chaîne pour les placeholders
        $placeholders = implode(", ", array_fill(0, count($columns), '?'));

        // Créer la requête SQL d'insertion
        $sql = "INSERT INTO $tableName ($columnString) VALUES ($placeholders)";

        // Log la requête SQL pour déboguer
        error_log("SQL: $sql");

        // Préparer la requête
        $stmt = odbc_prepare($this->connexion->getConnexion(), $sql);

        // Exécuter la requête avec les valeurs
        $result = odbc_execute($stmt, $values);

        // if ($result) {
        //     echo "Data inserted successfully!";
        // } else {
        //     echo "Insertion failed: " . odbc_errormsg($this->connexion->getConnexion());
        // }
    }
}