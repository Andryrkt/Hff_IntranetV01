<?php

namespace App\Traits;

use App\Entity\da\DemandeApproL;

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
        foreach ($dals as $dal) {
            $row = [];
            foreach ($columns as $key => $label) {
                $method = $methodMapping[$key] ?? null;
                $row[$key] = ($method && method_exists($dal, $method))
                    ? ($dal->{$method}() ?? '-')
                    : '-';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Préparer les données pour le mail de création de DA.
     */
    private function prepareDataForMailCreationDa(int $datypeId, iterable $dals): array
    {
        $columns = $this->getColumnsByType($datypeId, 'creation');

        return [
            'head' => $columns,
            'body' => $this->buildRows($dals, $columns),
        ];
    }

    /**
     * Préparer les données pour le mail de modification de DA.
     */
    private function prepareDataForMailModificationDa(int $datypeId, iterable $newDals, iterable $oldDals): array
    {
        $columns = $this->getColumnsByType($datypeId, 'modification');

        return [
            'new' => [
                'head' => $columns,
                'body' => $this->buildRows($newDals, $columns),
            ],
            'old' => [
                'head' => $columns,
                'body' => $this->buildRows($oldDals, $columns),
            ],
        ];
    }

    /**
     * Préparer les données pour le mail de validation de DA.
     */
    private function prepareDataForMailValidationDa(int $datypeId, iterable $dals): array
    {
        $columns = $this->getColumnsByType($datypeId, 'validation');

        return [
            'head' => $columns,
            'body' => $this->buildRows($dals, $columns),
        ];
    }
}
