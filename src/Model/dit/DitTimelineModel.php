<?php

namespace App\Model\dit;

use App\Model\Model;

class DitTimelineModel extends Model
{
    public function fetchTimelineDit(string $numDit)
    {
        $reparationRealisePol = "ATE POL TANA";
        $statutDebutDefaut = "A AFFECTER";
        $statutDebutPol = "A VALIDER RESP RENTAL";

        $statement = "SELECT 
            di.date_demande, 
            CASE 
                WHEN di.reparation_realise = '$reparationRealisePol' THEN '$statutDebutPol'
                ELSE '$statutDebutDefaut'
            END                              AS statut_debut,
            di.numero_or                     AS dit_numero_or,
            dodr.numero_or                   AS dodr_numero_or,
            dodr.date_soumission             AS date_soumission_or,
            dodr.date_demande_modification   AS date_demande_modification,
            dodr.date_validation_devis       AS date_validation_devis,
            dodr.date_fin_validation_ca      AS date_validation_ca,
            dodr.date_fin_validation_dt      AS date_validation_dt,
            dodr.date_fin_validation_ci      AS date_validation_ci,
            dodr.date_fin_validation_fleet_m AS date_validation_fleet_m,
            dodr.date_fin_validation_dg      AS date_validation_dg,
            dodr.date_fin_validation_ser_em  AS date_validation_ser_em,
            dodr.date_fin_validation_ser_des AS date_validation_ser_des,
            dodr.date_fin_validation_compta  AS date_validation_compta,
            dodr.date_fin_validation_info    AS date_validation_info,
            dodr.date_fin_validation_mag     AS date_validation_mag,
            dodr.date_validation_finale_or   AS date_validation_finale_or,
            dodr.date_fin_validation_section AS date_validation_section
        FROM demande_intervention di 
        JOIN DW_Ordre_De_Reparation dodr ON di.numero_demande_dit=dodr.numero_dit
        WHERE di.numero_demande_dit='$numDit'
        ";

        $result = $this->connexion->query($statement);

        return $result;
    }
}
