<?php

namespace App\Model\inventaire;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;

class InventaireModel extends Model
{
    use ConversionModel;
    use FormatageTrait;
    use InventaireModelTrait;
    public function recuperationAgenceIrium()
    {
        $statement = " SELECT  trim(asuc_num) as asuc_num ,
                               trim(asuc_lib) as asuc_lib
                      FROM agr_succ
                      WHERE asuc_codsoc = 'HF'
                      AND  (ASUC_NUM like '01' 
                      or ASUC_NUM like '02' 
                      or ASUC_NUM like '10'
                       or ASUC_NUM like '20'
                       or ASUC_NUM like '30'
                       or ASUC_NUM like '40'
                       or ASUC_NUM like '50'
                       or ASUC_NUM like '60'
                       or ASUC_NUM like '92'
                       
                       )
                      order by 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return
            array_map(function ($item) {
                return [$item['asuc_num'] . '-' . $item['asuc_lib'] => $item['asuc_num']];
            }, $dataUtf8);
    }

    public function listeInventaire($criteria)
    {
        $agence = $this->agence($criteria);
        $dateD = $this->dateDebut($criteria);
        $dateF = $this->dateFin($criteria);
        $statement = "SELECT  
                ainvi_numinv_mait as numero, 
                ainvi_date as ouvert_le, 
                TRIM(ainvi_comment) as description,
                '' as nbre_casier,
                count(ainvp_refp) as nbre_ref,
                sum(ainvp_stktheo) as qte_comptee,
                '' as statut,
                trunc(sum(ainvp_prix * ainvp_stktheo)) as Montant
                FROM  art_invi 
                INNER  JOIN art_invp ON ainvp_numinv = ainvi_numinv_mait
                WHERE ainvi_soc ='HF'    
                AND ainvi_sequence = 1
                AND (ainvp_stktheo <> 0 or ( ainvp_ecart <> 0 ))
                $agence
                $dateD
                $dateF
                group by 1,2,3,4
        ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
          $data = $this->connect->fetchResults($result);
          $resultat = $this->convertirEnUtf8($data);
          return $resultat;
    }
}
