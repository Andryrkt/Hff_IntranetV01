<?php

namespace App\Factory\Dom;

use App\Dto\Dom\DomListItemDTO;
use App\Model\dom\DomModel;

class DomListFactory
{
    public function createDtoFromRows(array $row, bool $isTropPercu): DomListItemDTO
    {
        $domListItemDTO = new DomListItemDTO();
        $statut         = trim($row['statutDescription']);

        $domListItemDTO->id                       = $row['id'];
        $domListItemDTO->numeroOrdreMission       = $row['numeroOrdreMission'];
        $domListItemDTO->statutDescription        = $statut;
        $domListItemDTO->codeSousType             = $row['codeSousType'];
        $domListItemDTO->dateDemande              = $row['dateDemande'];
        $domListItemDTO->motifDeplacement         = $row['motifDeplacement'];
        $domListItemDTO->matricule                = $row['matricule'];
        $domListItemDTO->libelleCodeAgenceService = $row['libelleCodeAgenceService'];
        $domListItemDTO->dateDebut                = $row['dateDebut'];
        $domListItemDTO->dateFin                  = $row['dateFin'];
        $domListItemDTO->client                   = $row['client'];
        $domListItemDTO->lieuIntervention         = $row['lieuIntervention'];
        $domListItemDTO->totalGeneralPayer        = $row['totalGeneralPayer'];
        $domListItemDTO->devis                    = $row['devis'];
        $domListItemDTO->classeStatut             = DomListItemDTO::$classeStatutArray[$statut] ?? "";
        $domListItemDTO->styleStatut              = DomListItemDTO::$styleStatutArray[$statut] ?? "";
        $domListItemDTO->showTropPercuAction      = $row['_statutTropPercuOk'] && $isTropPercu;

        return $domListItemDTO;
    }

    public function buildDomDTOs(array $rows): array
    {
        // Étape 1 : calculer statutTropPercuOk sur les données scalaires
        // (remplace statutTropPercuDomList — zéro lazy load, zéro requête)
        $eligibles = [];
        foreach ($rows as &$row) {
            $codeSousType = $row['codeSousType'];
            $statut       = trim($row['statutDescription']);
            $modePaiement = explode(':', $row['modePayement'])[0];

            $statutTropPercuOk = false;
            if (in_array($codeSousType, ['COMPLEMENT', 'MISSION'], true)) {
                $isPaye    = $statut === 'PAYE';
                $isAttente = $statut === 'ATTENTE PAIEMENT';
                $isNotMobileMoney = $modePaiement !== 'MOBILE MONEY';

                if ($isPaye || ($isAttente && $isNotMobileMoney)) {
                    $statutTropPercuOk = true;
                    $eligibles[] = $row['numeroOrdreMission'];
                }
            }
            // On mémorise pour la boucle suivante
            $row['_statutTropPercuOk'] = $statutTropPercuOk;
        }

        // Étape 2 : 1 seule requête ODBC batch pour tous les éligibles
        $tropPercuMap = (new DomModel())->verifierSiTropPercuBatch($eligibles);

        // Étape 3 : construire les DTOs
        $dtos = [];
        foreach ($rows as $row) {
            $numero      = $row['numeroOrdreMission'];
            $isTropPercu = $tropPercuMap[$numero] ?? false;

            $dtos[] = $this->createDtoFromRows($row, $isTropPercu);
        }

        return $dtos;
    }
}
