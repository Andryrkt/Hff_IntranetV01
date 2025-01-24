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


    public function findAll($table_name,$order='', $critere ='') {
    
        // Requête pour récupérer toutes les données de la table spécifiée
        $data_query = "SELECT * FROM $table_name";

        if ($critere !== '') {
            $data_query .= " WHERE {$critere}";
        }

        if ($order !== '') {
            $data_query .= " ORDER BY {$order}";
        }
        $data_result = odbc_exec($this->connexion->getConnexion(), $data_query);
        
        if (!$data_result) {
            die("Query failed: " . odbc_errormsg());
        }
    
        // Récupérer les données dans un tableau
        $data = [];
        while ($data_row = odbc_fetch_array($data_result)) {
            $data[] = $data_row;
        }
    
        // Fermer la connexion
        odbc_close($this->connexion->getConnexion());
    
        return $data;
    }

    public function find($table_name, $critere, $entityClass) {
    
        // Requête pour récupérer toutes les données de la table spécifiée
        $data_query = "SELECT * FROM $table_name WHERE {$critere}";
    
        $data_result = odbc_exec($this->connexion->getConnexion(), $data_query);
        
        if (!$data_result) {
            die("Query failed: " . odbc_errormsg());
        }
    
        // Utiliser la réflexion pour créer des instances de l'entité et les remplir avec les données
        $data = [];
        while ($data_row = odbc_fetch_array($data_result)) {
            $reflectionClass = new ReflectionClass($entityClass);
            $entity = $reflectionClass->newInstance();
            
            foreach ($data_row as $key => $value) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($entity, $setter)) {
                    $entity->$setter($value);
                }
            }
    
            $data[] = $entity;
        }
    
        // Fermer la connexion
        // odbc_close($this->connexion->getConnexion());
    
        return $data;
    }

    function update($table_name, $data, $condition) {
        // Vérifier si $data est un objet et le convertir en tableau si nécessaire
        if (is_object($data)) {
            $reflectionClass = new ReflectionClass($data);
            $data_array = [];
            
            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true); // Rendre la propriété accessible
                $data_array[$property->getName()] = $property->getValue($data);
            }
            $data = $data_array;
        }
    
        // Vérifier que $data n'est pas vide
        if (empty($data)) {
            die("No data provided for update");
        }
    
        // Vérifier la connexion à la base de données
        $connexion = $this->connexion;
        if (!$connexion || !is_resource($connexion)) {
            die("Database connection failed: " . odbc_errormsg());
        }
    
        // Créer la chaîne de la requête de mise à jour
        $set_clause = [];
        foreach ($data as $column => $value) {
            $set_clause[] = "$column = ?";
        }
        if (empty($set_clause)) {
            die("No data to update");
        }
        $set_clause = implode(", ", $set_clause);
    
        // Construire la requête de mise à jour
        $update_query = "UPDATE $table_name SET $set_clause WHERE $condition";
    
        // Préparer la requête
        $stmt = odbc_prepare($connexion, $update_query);
        if (!$stmt) {
            die("Statement preparation failed: " . odbc_errormsg($connexion));
        }
    
        // Préparer les valeurs pour l'exécution
        $values = array_values($data);
    
        // Exécuter la requête avec les valeurs
        $result = odbc_execute($stmt, $values);
    
        if (!$result) {
            die("Update query failed: " . odbc_errormsg($connexion));
        }
    
        // Fermer la connexion
        odbc_close($connexion);
    
        return $result;
    }
    
    
    
    
    
    


    function delete($table_name, $condition) 
    {

        // Construire la requête de suppression
        $delete_query = "DELETE FROM $table_name WHERE $condition";
    
        // Exécuter la requête de suppression
        $result = odbc_exec($this->connexion->getConnexion(), $delete_query);
    
        if (!$result) {
            die("Delete query failed: " . odbc_errormsg());
        }
    
        // Fermer la connexion
        odbc_close($this->connexion->getConnexion());
    
        return $result;
    }
}