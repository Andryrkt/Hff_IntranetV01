<?php

namespace App\Traits;

use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;

trait PrepareDataDAP
{
    use DaMailColumnsTrait;

    /** 
     * Préparer les données des Dals à afficher dans le mail
     * 
     * @param iterable<DemandeApproL> $dals données des Dals à préparer
     * 
     * @return array données préparées
     */
    private function prepareDataForMailPropositionDa(iterable $dals): array
    {
        $datasPrepared = [];

        foreach ($dals as $dal) {
            $cst  = $dal->getArtConstp();
            $ref  = $dal->getArtRefp();
            $desi = $dal->getArtDesi();
            $qte  = $dal->getQteDem();
            $datasPrepared[] = [
                'keyId'  => implode('_', array_map('trim', [$cst, $ref, $desi, $qte])),
                'cst'    => $cst,
                'ref'    => $ref,
                'desi'   => $desi,
                'qte'    => $qte,
            ];
        }

        return $datasPrepared;
    }

    /**
     * Construit les lignes de données à partir d'une liste de Dals.
     */
    private function buildRows(iterable $dals, array $columns): array
    {
        $rows = [];
        $methodMapping = $this->getMethodMapping();

        // Préparer à l'avance les méthodes à appeler pour chaque colonne
        $methodsToCall = [];
        foreach ($columns as $key => $label) {
            if (isset($methodMapping[$key])) $methodsToCall[$key] = $methodMapping[$key];
        }

        /** @var DemandeApproL|DemandeApproLR $dal */
        foreach ($dals as $dal) {
            $row = [];
            foreach ($columns as $key => $label) {
                if (!isset($methodsToCall[$key])) {
                    $row[$key] = '-';
                    continue;
                }

                $method = $methodsToCall[$key];

                if (is_array($method)) {
                    // On cherche la bonne méthode selon le type de l'objet
                    $found = false;
                    foreach ($method as $className => $methodName) {
                        if ($dal instanceof $className) {
                            $row[$key] = $dal->$methodName() ?? '-';
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) $row[$key] = '-';
                } else {
                    $row[$key] = $dal->$method() ?? '-';
                }
            }

            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Préparer les données pour un mail de DA.
     *
     * @param int $datypeId
     * @param iterable $dals
     * @param string $context Contexte : 'creation', 'modification', 'validation', etc.
     * @param iterable|null $oldDals Optionnel, pour comparaison dans modification
     */
    private function prepareDataForMailDa(int $datypeId, iterable $dals, string $context, ?iterable $oldDals = null): array
    {
        $columns = $this->getColumnsByType($datypeId, $context);

        // Cas modification avec comparaison ancien / nouveau
        if ($context === 'modification' && $oldDals !== null) {
            return [
                'new' => [
                    'head' => $columns,
                    'body' => $this->buildRows($dals, $columns),
                ],
                'old' => [
                    'head' => $columns,
                    'body' => $this->buildRows($oldDals, $columns),
                ],
            ];
        }

        // Cas création, validation, validationReappro, etc.
        return [
            'head' => $columns,
            'body' => $this->buildRows($dals, $columns),
        ];
    }

    /** Préparer les données pour le mail de création de DA. */
    private function prepareDataForMailCreationDa(int $datypeId, iterable $dals): array
    {
        return $this->prepareDataForMailDa($datypeId, $dals, 'creation');
    }

    /** Préparer les données pour le mail de modification de DA. */
    private function prepareDataForMailModificationDa(int $datypeId, iterable $newDals, iterable $oldDals): array
    {
        return $this->prepareDataForMailDa($datypeId, $newDals, 'modification', $oldDals);
    }

    /** Préparer les données pour le mail de validation de DA. */
    private function prepareDataForMailValidationDa(int $datypeId, iterable $dals): array
    {
        return $this->prepareDataForMailDa($datypeId, $dals, 'validation');
    }

    /** Préparer les données pour le mail de validation/refus de DA Réappro. */
    private function prepareDataForMailValidationDaReappro(int $datypeId, iterable $dals): array
    {
        return $this->prepareDataForMailDa($datypeId, $dals, 'validationReappro');
    }
}
