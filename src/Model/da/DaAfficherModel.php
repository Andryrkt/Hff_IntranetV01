<?php

namespace App\Model\da;

use App\Model\Model;
use App\Entity\da\DemandeAppro;
use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Constants\da\StatutActionConstant;

class DaAfficherModel extends Model
{
    /**
     * Récupère le statut fiable (dernière version de DaAfficher) + classe de couleur d'affichage d'une DA,
     * ainsi que l'acteur et l'action à faire correspondants.
     *
     * @return array{action:string,statutDa:string,classStatutDa:string}
     */
    public function getStatutEtActionAffichage(string $numDa, string $demandeur): array
    {
        $action = [];
        $statutDa = $typeDa = $statutOr = $statutBc = null;

        $sql = "SELECT DISTINCT
                    da.da_type_id AS type_da,
                    da.statut_dal AS statut_da,
                    da.statut_or  AS statut_or,
                    da.statut_cde AS statut_bc
                FROM da_afficher da
                JOIN (
                    SELECT numero_demande_appro, max(numero_version) AS max_version
                    FROM da_afficher
                    GROUP BY numero_demande_appro
                ) m  ON da.numero_demande_appro = m.numero_demande_appro
                    AND da.numero_version = m.max_version
                WHERE da.numero_demande_appro = '$numDa'";

        $result = $this->connexion->query($sql);

        $firstRow = $lastRow = $rowPasDansOR = null;

        while ($row = odbc_fetch_array($result)) {
            $row = $this->convertDataSqlServerToUTF8($row);
            $firstRow ??= $row;
            $lastRow = $row;

            if (($row['statut_bc'] ?? null) === StatutBcConstant::STATUT_PAS_DANS_OR) {
                $rowPasDansOR = $row;
            }

            $isCasSimple = (int) $row['type_da'] != DemandeAppro::TYPE_DA_AVEC_DIT
                || $row['statut_da'] !== StatutDaConstant::STATUT_VALIDE
                || $row['statut_or'] !== StatutOrConstant::STATUT_VALIDE;

            if ($isCasSimple) {
                $chosenRow = $row;
                break;
            }
        }

        $chosenRow ??= $rowPasDansOR ?? $lastRow;

        $typeDa   = isset($chosenRow['type_da']) ? (int) $chosenRow['type_da'] : null;
        $statutDa = $chosenRow['statut_da'];
        $statutOr = $chosenRow['statut_or'];
        $statutBc = $chosenRow['statut_bc'];

        $action = StatutActionConstant::getAction($statutDa, $typeDa, $demandeur, $statutOr, $statutBc);

        return [
            'action'        => $action["action"] ?: "-",
            'statutDa'      => $statutDa,
            'classStatutDa' => StatutDaConstant::getCssClassDa($statutDa),
        ];
    }
}
