<?php

namespace App\Controller\Traits\da\detail;

trait DaDetailAvecDitTrait
{
    use DaDetailTrait;

    /** 
     * Obtenir tous les fichiers associés à la demande d'approvisionnement
     * 
     * @param array $tab
     */
    private function getAllDAFile($tab): array
    {
        return [
            'BA'    => [
                'type'       => "Bon d'achat",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-ba',
                'fichiers'   => $this->normalizePaths($tab['baPath']),
            ],
            'OR'    => [
                'type'       => 'Ordre de réparation',
                'icon'       => 'fa-solid fa-wrench',
                'colorClass' => 'border-left-or',
                'fichiers'   => $this->normalizePathsForOneFile($tab['orPath'], 'numeroOr'),
            ],
            'BC'    => [
                'type'       => 'Bon de commande',
                'icon'       => 'fa-solid fa-file-circle-check',
                'colorClass' => 'border-left-bc',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['bcPath'], 'numeroBc'),
            ],
            'FACBL' => [
                'type'       => 'Facture / Bon de livraison',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['facblPath'], 'idFacBl'),
            ],
        ];
    }
}
