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
    public function getInfoDOMMatrSelet($matricule, $codeSociete)
    {
        $SqlDate = "SELECT  Date_Debut, Date_Fin
        FROM Demande_ordre_mission
        WHERE  Matricule = '$matricule'
        AND code_societe = '$codeSociete'
        AND ID_Statut_Demande NOT IN (9, 33, 34, 35, 44)";

        $execSqlDate = $this->connexion->query($SqlDate);

        $DateM = array();
        while ($tab_list = odbc_fetch_array($execSqlDate)) {
            $DateM[] = $tab_list;
        }

        return $DateM;
    }

    public function verifierSiTropPercuBatch(array $numeroDoms, string $codeSociete): array
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
                    AND dom.code_societe='$codeSociete'
                GROUP BY dom.Numero_Ordre_Mission, dom.Nombre_Jour";

        $result = [];
        $rs = $this->connexion->query($sql);
        while ($row = odbc_fetch_array($rs)) {
            $result[$row['Numero_Ordre_Mission']] = (bool) $row['is_trop_percu'];
        }

        return $result;
    }

    public function verifierSiTropPercu(string $numeroDom, string $codeSociete)
    {
        $sql = "SELECT
                    CASE
                        WHEN (dom.Nombre_Jour - COALESCE(SUM(domtp.Nombre_Jour_Tp), 0)) > 0 THEN 'Trop_percu'
                        ELSE ''
                    END AS reponse
                FROM Demande_ordre_mission dom
                LEFT JOIN Demande_ordre_mission_tp domtp
                    ON dom.Numero_Ordre_Mission = domtp.Numero_Ordre_Mission
                WHERE dom.Numero_Ordre_Mission = '$numeroDom' 
                    AND dom.Sous_Type_Document in (2, 3, 10) 
                    AND dom.code_societe='$codeSociete'
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

    /** 
     * Vérifie les conflits de dates entre les DOM et les congés existants pour un matricule sur une période donnée.
     * 
     * @param string             $matricule
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     * @param string             $codeSociete
     * 
     * @return array{dom:list<array<string,string>>,conge:list<array<string,string>>}
     */
    public function verifierConflitDate(string $matricule, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, string $codeSociete)
    {
        $tableParams     = 'Params';
        $dateDebutFormat = $dateDebut->format('Y-m-d');
        $dateFinFormat   = $dateFin->format('Y-m-d');

        $sql = "--sql
            WITH $tableParams AS (
                SELECT 
                    '$matricule'       AS Matricule,
                    '$codeSociete'     AS CodeSociete,
                    '$dateDebutFormat' AS DateDebut,
                    '$dateFinFormat'   AS DateFin
            )
            {$this->getQueryConflitDateDom($tableParams)}
            UNION ALL
            {$this->getQueryConflitDateConge($tableParams)}
            ORDER BY date_debut
        ";

        $stmt = $this->connexion->query($sql);

        $conflits = ['dom' => [], 'conge' => []];

        while ($row = odbc_fetch_array($stmt)) {
            if ($row['type_conflit'] === 'DOM')   $conflits['dom'][] = $row;
            else $conflits['conge'][] = $row;
        }

        return $conflits;
    }

    private function getQueryConflitDateDom(string $tableParams)
    {
        return "SELECT
                    'DOM'                    AS type_conflit,
                    dom.Numero_Ordre_Mission AS numero,
                    dom.Date_Debut           AS date_debut,
                    dom.Date_Fin             AS date_fin
                FROM Demande_ordre_mission dom
                INNER JOIN Statut_demande sd
                    ON sd.ID_Statut_Demande = dom.ID_Statut_Demande
                CROSS JOIN $tableParams p
                WHERE dom.Matricule = p.Matricule
                    AND dom.Code_Societe = p.CodeSociete
                    AND sd.Code_Statut <> 'ANN'
                    AND (
                        dom.Date_Debut <= p.DateFin
                        AND dom.Date_Fin >= p.DateDebut
                    )";
    }

    private function getQueryConflitDateConge(string $tableParams)
    {
        return "SELECT
                    'CONGE'            AS type_conflit,
                    ddc.Numero_Demande AS numero,
                    ddc.Date_Debut     AS date_debut,
                    ddc.Date_Fin       AS date_fin
                FROM demande_de_conge ddc
                CROSS JOIN $tableParams p
                WHERE ddc.Matricule = p.Matricule
                    AND ddc.Statut_Demande NOT LIKE '%Refusé%'
                    AND ddc.Statut_Demande NOT LIKE '%Annulé%'
                    AND (
                        ddc.Date_Debut <= p.DateFin
                        AND ddc.Date_Fin >= p.DateDebut
                    )";
    }
}
