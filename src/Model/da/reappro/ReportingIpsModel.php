<?php

namespace App\Model\da\reappro;

use App\Model\Model;

class ReportingIpsModel extends Model
{
    public function getReportingData(): array
    {
        $statement = " SELECT 
            slor_succdeb as agence_debiteur
            , slor_servdeb as service_debiteur
            , seor_dateor date_commande
            , slor_numfac as numero_facture
            , TRIM(seor_lib) as client
            , slor_constp as constructeur
            , TRIM(slor_refp) as reference_produit
            , TRIM(slor_desi) as designation_produit
            , ROUND(slor_qterea) as qte_demande
            , slor_pxnreel as prix_unitaire_reel
            , slor_qterea * slor_pxnreel as montant
            FROM informix.sav_lor 
            INNER JOIN informix.sav_eor on seor_soc = slor_soc and seor_succ = slor_succ and seor_numor = slor_numor and seor_soc = 'HF'
            INNER JOIN informix.dpc_fcc on dfcc_numfcc = slor_numfac and dfcc_soc = 'HF'
            WHERE slor_constp in ('ALI','BOI','CEN','FAT','FBU','HAB','INF','MIN','OUT')
            AND seor_dateor >= '01-10-2025'
            AND slor_servcrt = 'APP'
            AND slor_typeor = 600
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $data;
    }
}
