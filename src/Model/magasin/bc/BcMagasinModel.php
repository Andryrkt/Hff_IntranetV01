<?php

namespace App\Model\magasin\bc;

use App\Model\Model;
use App\Service\GlobalVariablesService;

class BcMagasinModel extends Model
{
    public function getInformaitonDevisMagasin(string $numeroDevis): array
    {
        $statement = " SELECT nlig_nolign as numero_ligne
            , TRIM(nlig_constp) as constructeur
            , TRIM(nlig_refp) as ref
            , TRIM(nlig_desi) as designation
            , round(nlig_qtecde) as qte
            , ROUND(nlig_pxnreel, 2) as prix_ht
            , ROUND((nlig_pxvteht*nlig_qtecde) * (1-(nlig_rem1/100)), 2) as montant_net
            , ROUND(nlig_rem1, 2) as remise1
            , ROUND(nlig_rem2, 2) as remise2
            , nlig_numcde as numero_devis
            from informix.neg_lig 
            inner join informix.neg_ent on nent_soc = nlig_soc and nent_succ = nlig_succ and nent_numcde = nlig_numcde and nlig_soc = 'HF' and nent_soc = 'HF'
            where nent_natop = 'DEV'
            --year(nlig_datecde) = '2025' and month(nlig_datecde) = '10'
            and nent_posl <> 'TR'
            and nent_servcrt = 'NEG'
            and nlig_numcde = '$numeroDevis'
            and nlig_constp in (" . GlobalVariablesService::get('pieces_magasin') . ") -- ne recuperer que les pièces gérées par le magasin
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getMontantDevis(string $numeroDevis): array
    {
        $statement = " SELECT nent_cdeht as montant
            FROM neg_ent
            WHERE nent_natop = 'DEV'
            AND nent_soc = 'HF'
            AND CAST(nent_numcli AS VARCHAR(20)) NOT LIKE '199%'
            AND year(Nent_datecde) = '2025'
            and nent_numcde ='$numeroDevis'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'montant');
    }
}
