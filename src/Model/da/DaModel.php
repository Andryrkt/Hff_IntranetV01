<?php

namespace App\Model\da;

use App\Model\Model;

class DaModel extends Model
{
    public function getAllFamille()
    {
        $statement = "SELECT distinct 
            trim(atab_code) as code, 
            trim(atab_lib) as libelle
            FROM agr_tab
            INNER JOIN art_bse ON abse_fams1 = atab_code
            WHERE abse_constp = 'ZST' and atab_nom = 'STA'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_combine(array_column($data, 'libelle'), array_column($data, 'code'));
    }

    public function getTheSousFamille($codeFamille)
    {
        $statement = "SELECT DISTINCT 
            trim(abse_fams2) as code, 
            trim(atab_lib) as libelle
            FROM art_bse
            INNER JOIN agr_tab ON atab_nom = 'S/S'
            WHERE abse_constp = 'ZST' AND abse_fams1 = '$codeFamille'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getAllDesignation()
    {
        $statement = "SELECT 
            trim(abse_desi) as designation,
            abse_pxstd as prix,
            trim(fbse_nomfou) as fournisseur
            FROM art_frn
            INNER JOIN art_bse 
                ON abse_refp = afrn_refp 
                AND afrn_constp = abse_constp
            INNER JOIN frn_bse 
                ON fbse_numfou = afrn_numf
            WHERE abse_constp = 'ZST'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
