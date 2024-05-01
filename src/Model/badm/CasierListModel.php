<?php

namespace App\Model\badm;

use App\Model\Model;
use App\Service\PaginatedQuery;
use App\Model\Traits\ConversionModel;

class CasierListModel extends Model
{

    use BadmModelTrait;
    use ConversionModel;


    public function recuperToutesCasier(): array
    {
        $sql = $this->connexion->query("SELECT *
       
        FROM Casier_Materiels
                                                                        
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
}
