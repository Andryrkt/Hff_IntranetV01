<?php

namespace App\Model\da\reappro;

use App\Model\Model;

class ReportingIpsModel extends Model
{
    public function getReportingData(array $criterias): array
    {
        if ($criterias['description']) {
            $description = " AND slor_desi LIKE '%{$criterias['description']}%'";
        } else {
            $description = "";
        }

        if ($criterias['numFacture']) {
            $numFacture = " AND slor_numfac = '{$criterias['numFacture']}'";
        } else {
            $numFacture = "";
        }

        if ($criterias['date_debut']) {
            $dateDebut = " AND EXTEND(dfcc_datefac, YEAR TO DAY) >= '{$criterias['date_debut']}'";
        } else {
            $dateDebut = "";
        }

        if ($criterias['date_fin']) {
            $dateFin = " AND EXTEND(dfcc_datefac, YEAR TO DAY) <= '{$criterias['date_fin']}'";
        } else {
            $dateFin = "";
        }

        if (isset($criterias['agenceDebiteur']) && $criterias['agenceDebiteur'] != "''") {
            $codeAgence = $criterias['agenceDebiteur'];
            $agenceDebiteur = " AND slor_succdeb in ({$codeAgence})";
        } else {
            $agenceDebiteur = "AND slor_succdeb in ('')";
        }

        if ($criterias['serviceDebiteur'] != null && $criterias['serviceDebiteur'] != "''") {
            $codeService = $criterias['serviceDebiteur'];
            $serviceDebiteur = " AND slor_servdeb in({$codeService})";
        } else {
            $serviceDebiteur = "";
        }

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
            WHERE slor_servcrt = 'APP'
            AND slor_typeor = 600
            $dateDebut
            $dateFin
            $agenceDebiteur
            $serviceDebiteur
            $numFacture
            $description
            AND slor_constp in ({$criterias['constructeur']})
            ORDER BY seor_dateor DESC, slor_succdeb, slor_servdeb, slor_numfac, seor_lib, slor_constp, slor_refp

        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $data;
    }
}
