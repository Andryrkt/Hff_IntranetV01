<?php

namespace App\Model\badm;

use Exception;
use App\Model\Model;
use App\Model\Traits\ConversionModel;

class CasierListModel extends Model
{

    use BadmModelTrait;
    use ConversionModel;


    public function recuperToutesCasier($tab, $page, $pageSize): array
    {

        $offset = $pageSize * ($page - 1);
   
        // Début de la requête SQL
        $sql = "SELECT * FROM Casier_Materiels WHERE 1=1";
    
        
        if (!empty($tab['agence'])) {
            $sql .= " AND Agence_Rattacher LIKE '%" . explode(' ', $tab['agence'])[0] . "%'";
        }
        if (!empty($tab['casier'])) {
            $sql .= " AND Casier LIKE '%" . $tab['casier'] . "%'";
        }
    
        // Ajout d'un ordre de tri
        $sql .= " ORDER BY Numero_CAS DESC OFFSET {$offset} ROWS FETCH NEXT {$pageSize} ROWS ONLY";
    
        // Exécution de la requête
        $statement = $this->connexion->query($sql);
    
        
    
        $tab = [];
        // Récupération des données
        while ($donner = odbc_fetch_array($statement)) {
            // Nettoyage des données de chaque colonne
            foreach ($donner as $key => $value) {
                $donner[$key] = $this->clean_string($value);
            }
            $tab[] = $donner;
        }
    
        // Convertir les données en UTF-8 avant de retourner
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


    public function NombreDeLigne($tab)
    {
        $sql = "SELECT COUNT(*) As nbrLigne FROM Casier_Materiels WHERE 1=1";

        if (!empty($tab['agence'])) {
            $sql .= " AND Agence_Rattacher LIKE '%" . explode(' ', $tab['agence'])[0] . "%'";
        }
        if (!empty($tab['casier'])) {
            $sql .= " AND Casier LIKE '%" . $tab['casier'] . "%'";
        }

        $statement = $this->connexion->query($sql);
        return odbc_fetch_array($statement);
    }

    
}
