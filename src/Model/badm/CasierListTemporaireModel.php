<?php

namespace App\Model\badm;

use App\Model\Model;
use App\Service\PaginatedQuery;
use App\Model\Traits\ConversionModel;

class CasierListTemporaireModel extends Model
{

    use BadmModelTrait;
    use ConversionModel;


    public function recuperToutesCasier(): array
    {
        $sql = $this->connexion->query("SELECT *
       
        FROM Casier_Materiels_Temporaire
                                                                        
        ORDER BY Numero_CAS DESC

        ");


        // Définir le jeu de caractères source et le jeu de caractères cible

        $tab = [];
        while ($donner = odbc_fetch_array($sql)) {

            $tab[] = $donner;
        }


        // Parcourir chaque élément du tableau $tab
        foreach ($tab as $key => &$value) {
            // Parcourir chaque valeur de l'élément et nettoyer les données
            foreach ($value as &$inner_value) {
                $inner_value = $this->clean_string($inner_value);
            }
        }

        return $this->convertirEnUtf8($tab);
    }

    public function recuperSeulCasier($id): array
    {
        $sql = $this->connexion->query("SELECT *
       
        FROM Casier_Materiels_Temporaire
        WHERE Id = '{$id}'

        ");


        // Définir le jeu de caractères source et le jeu de caractères cible

        $tab = [];
        while ($donner = odbc_fetch_array($sql)) {

            $tab[] = $donner;
        }


        // Parcourir chaque élément du tableau $tab
        foreach ($tab as $key => &$value) {
            // Parcourir chaque valeur de l'élément et nettoyer les données
            foreach ($value as &$inner_value) {
                $inner_value = $this->clean_string($inner_value);
            }
        }

        return $this->convertirEnUtf8($tab);
    }


    public function insererDansBaseDeDonnees($tab)
    {
        $sql = "INSERT INTO Casier_Materiels (
            Agence_Rattacher,
            Casier,
            Nom_Session_Utilisateur,
            Date_Creation,
            Numero_CAS
            
        ) VALUES (?, ?, ?, ?, ?)";

        // Exécution de la requête
        $stmt = odbc_prepare($this->connexion->connect(), $sql);
        if (!$stmt) {
            echo "Erreur de préparation : " . odbc_errormsg($this->connexion->connect());
            return;
        }

        $success = odbc_execute($stmt, array_values($tab));
    }

    public function Delete($id)
    {
        $sql = "DELETE FROM Casier_Materiels_Temporaire WHERE Id = '{$id}'
        ";

        odbc_exec($this->connexion->connect(), $sql);
    }

    /**
     * @return PaginatedQuery
     */
    public function findPaginated(): PaginatedQuery
    {
        return new PaginatedQuery(
            $this->connexion,
            'SELECT * FROM Casier_Materiels ORDER BY Numero_CAS DESC',
            'SELECT COUNT(Id_Materiel) FROM Casier_Materiels'
        );
    }

    public function NombreDeLigne()
    {
        $sql = $this->connexion->query("SELECT COUNT(*) As nbrLigne FROM Casier_Materiels_Temporaire ");
        return odbc_fetch_array($sql);
    }
}
