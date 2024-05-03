<?php

namespace App\Model\badm;

use App\Model\Model;
use App\Service\PaginatedQuery;
use App\Model\Traits\ConversionModel;

class CasierListModel extends Model
{

    use BadmModelTrait;
    use ConversionModel;


    public function recuperToutesCasier($agence = '', $casier = ''): array
    {
        $sql = "SELECT * FROM Casier_Materiels ORDER BY Numero_CAS DESC";

        if (!empty($agence)) {
            $sql .= " AND Agence_Rattacher  = '{$agence}'";
        }
        if (!empty($casier)) {
            $sql .= " AND Casier = '{$casier}'";
        }

        $statement = $this->connexion->query($sql);
        // Définir le jeu de caractères source et le jeu de caractères cible

        $tab = [];
        while ($donner = odbc_fetch_array($statement)) {

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
     * informix
     */
    public function recupAgence(): array
    {
        $statement = "SELECT DISTINCT 
        trim(trim(asuc_num)||' '|| trim(asuc_lib)) as agence 
        from
        agr_succ , agr_tab a
        where asuc_numsoc = 'HF' and a.atab_nom = 'SER'
        and a.atab_code not in (select b.atab_code from agr_tab b where substr(b.atab_nom,10,2) = asuc_num and b.atab_nom like 'SERBLOSUC%')
        and asuc_num in ('01', '40', '50','90','91','92') 
        order by 1";

        $result = $this->connect->executeQuery($statement);


        $services = $this->connect->fetchResults($result);


        return $this->convertirEnUtf8($services);
    }


    public function NombreDeLigne()
    {
        $sql = $this->connexion->query("SELECT COUNT(*) As nbrLigne FROM Casier_Materiels ");
        return odbc_fetch_array($sql);
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
