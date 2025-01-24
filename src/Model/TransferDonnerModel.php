<?php

namespace App\Model;

use App\Model\ConnexionDote4;

class TransferDonnerModel extends Model
{
  

    public function transef()
    {
        $statment = "SELECT * from DemandeIntervention where IDStatutInfo = 10 order by IDDemandeIntervention desc";
      $sql = $this->connexion04->query($statment);
      $data = array();
      while ($tabType = odbc_fetch_array($sql)) {
         $data[] = $tabType;
      }
      return $data;
    }

    public function insert($table, $data) {
        // Construire la requête SQL d'insertion
        $columns = implode(", ", array_keys($data));
        $values  = implode(", ", array_map([$this, 'quoteValue'], array_values($data)));

        $sql = "INSERT INTO $table ($columns) VALUES ($values)";

        // Exécuter la requête
        $result = odbc_exec($this->connexion->getConnexion(), $sql);

        if (!$result) {
            // Afficher l'erreur si la requête échoue
            die("Insertion failed: " . odbc_errormsg($this->connexion->getConnexion()));
        }

        return $result;
    }

    private function quoteValue($value) {
        // Échapper les valeurs pour éviter les injections SQL et les erreurs de syntaxe
        if (is_null($value)) {
            return "NULL";
        } elseif (is_string($value)) {
            return "'" . str_replace("'", "''", $value) . "'";
        } else {
            return $value;
        }
    }

    
}