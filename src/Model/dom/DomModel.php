<?php

namespace App\Model\dom;

use App\Model\Model;
use App\Service\TableauEnStringService;

class DomModel extends Model
{

    //TSY MAHAZO FAFANA
    /**
     * Chevauchement : recuperation la minimum de la date de mission et le maximum de la mission 
     */
    public function getInfoDOMMatrSelet($matricule)
    {
        $SqlDate = "SELECT  Date_Debut, Date_Fin
        FROM Demande_ordre_mission
        WHERE  Matricule = '" . $matricule . "'  
        AND ID_Statut_Demande NOT IN (9, 33, 34, 35, 44)";

        $execSqlDate = $this->connexion->query($SqlDate);

        $DateM = array();
        while ($tab_list = odbc_fetch_array($execSqlDate)) {
            $DateM[] = $tab_list;
        }

        return $DateM;
    }

    public function verifierSiTropPercuBatch(array $numeroDoms): array
    {
        if (empty($numeroDoms)) {
            return [];
        }

        $placeholders = TableauEnStringService::orEnString($numeroDoms);

        $sql = "SELECT
                    dom.Numero_Ordre_Mission,
                    CASE
                        WHEN (dom.Nombre_Jour - COALESCE(SUM(domtp.Nombre_Jour_Tp), 0)) > 0 THEN 1
                        ELSE 0
                    END AS is_trop_percu
                FROM Demande_ordre_mission dom
                LEFT JOIN Demande_ordre_mission_tp domtp
                    ON dom.Numero_Ordre_Mission = domtp.Numero_Ordre_Mission
                WHERE dom.Numero_Ordre_Mission IN ($placeholders)
                AND dom.Sous_Type_Document IN (2, 3)
                GROUP BY dom.Numero_Ordre_Mission, dom.Nombre_Jour";

        $result = [];
        $rs = $this->connexion->query($sql);
        while ($row = odbc_fetch_array($rs)) {
            $result[$row['Numero_Ordre_Mission']] = (bool) $row['is_trop_percu'];
        }

        return $result;
    }

    public function verifierSiTropPercu(string $numeroDom)
    {
        $sql = "SELECT
                    CASE
                        WHEN (dom.Nombre_Jour - COALESCE(SUM(domtp.Nombre_Jour_Tp), 0)) > 0 THEN 'Trop_percu'
                        ELSE ''
                    END AS reponse
                FROM Demande_ordre_mission dom
                LEFT JOIN Demande_ordre_mission_tp domtp
                    ON dom.Numero_Ordre_Mission = domtp.Numero_Ordre_Mission
                WHERE dom.Numero_Ordre_Mission = '" . $numeroDom . "' AND dom.Sous_Type_Document in (2, 3)
                GROUP BY dom.Numero_Ordre_Mission, dom.Nombre_Jour";

        $result = odbc_fetch_array($this->connexion->query($sql));

        return !$result ? $result : $result['reponse'] === 'Trop_percu';
    }

    public function getNombreJourTropPercu(string $numeroDom)
    {
        $sql = "SELECT COALESCE(SUM(domtp.Nombre_Jour_Tp), 0) AS reponse
                FROM Demande_ordre_mission_tp domtp
                WHERE domtp.Numero_Ordre_Mission = '$numeroDom'";

        $result = odbc_fetch_array($this->connexion->query($sql));

        return $result['reponse'];
    }
}
