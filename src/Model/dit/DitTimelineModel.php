<?php

namespace App\Model\dit;

use App\Model\Model;

class DitTimelineModel extends Model
{
    /**
     * Méthode pour retourner les données de la timeline d'un DIT
     *
     * @param string $numDit numéro du DIT
     *
     * @return array<int,array{date_demande:string,statut_debut:string,dit_numero_or:string,dodr_numero_or:string,date_soumission_or:string,date_demande_modification:string,date_validation_devis:string,date_validation_ca:string,date_validation_dt:string,date_validation_ci:string,date_validation_fleet_m:string,date_validation_dg:string,date_validation_ser_em:string,date_validation_ser_des:string,date_validation_compta:string,date_validation_info:string,date_validation_mag:string,date_validation_finale_or:string,date_validation_section:string}> 
     */
    public function fetchTimelineDit(string $numDit): array
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
            dodr.numero_version              AS num_version_or,
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
        LEFT JOIN DW_Ordre_De_Reparation dodr ON di.numero_demande_dit=dodr.numero_dit
        WHERE di.numero_demande_dit='$numDit'
        ";

        $odbcRessource = $this->connexion->query($statement);
        $data = [];
        while ($result = odbc_fetch_array($odbcRessource)) {
            // ? Que se passe-t-il si le numero OR dans DIT et dans DW_Ordre_De_Reparation sont différent ?
            // if ($result["dit_numero_or"] != $result["dodr_numero_or"]) {
            //     throw new \Exception("Numero DIT : " . $numDit . " n'a pas de numéro OR dans DW_Ordre_De_Reparation");
            // }

            $data[] = $result;
        }

        return $data;
    }
}
