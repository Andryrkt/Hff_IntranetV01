<?php

namespace App\Service\da;

use Doctrine\ORM\EntityManagerInterface;

class DocRattacheService
{
    private EntityManagerInterface $em;
    private DaService $daService;

    public function __construct(EntityManagerInterface $em, DaService $daService)
    {
        $this->em = $em;
        $this->daService = $daService;
    }

    /************************* 
     * Fonctions utilitaires *
     *************************/

    /** 
     * Normaliser les chemins des fichiers pour l'affichage
     * 
     * @param mixed $paths
     * @return array
     */
    private function normalizePaths($paths): array
    {
        if ($paths === '-' || empty($paths)) return [];
        if (!is_array($paths)) $paths = [$paths];
        return array_map(function ($path) {
            return [
                'nom'  => pathinfo($path, PATHINFO_FILENAME),
                'path' => $path
            ];
        }, $paths);
    }

    /** 
     * Normaliser les chemins pour un seul fichier
     * 
     * @param mixed $doc
     * @param string $numKey
     * @return array
     */
    private function normalizePathsForOneFile($doc, string $numKey): array
    {
        $tabReturn = [];
        if ($doc !== '-' && !empty($doc)) $tabReturn[] = [
            'nom'  => $doc[$numKey],
            'path' => $doc['path']
        ];
        return $tabReturn;
    }

    /** 
     * Normaliser les chemins pour plusieurs fichiers
     * 
     * @param array $allDocs
     * @param string $numKey
     * @return array
     */
    private function normalizePathsForManyFiles($allDocs, string $numKey): array
    {
        if ($allDocs === '-' || empty($allDocs)) return [];

        return array_map(function ($doc) use ($numKey) {
            return [
                'nom'  => $doc[$numKey],
                'path' => $doc['path']
            ];
        }, $allDocs);
    }

    /** 
     * Normaliser les chemins pour plusieurs fichiers de facture / Bon de Livraison
     * 
     * @param array $allDocs
     * @return array
     */
    private function normalizePathsForFacBl($allDocs): array
    {
        if ($allDocs === '-' || empty($allDocs)) return [];

        return array_map(function ($doc) {
            return [
                'nom'   => $doc['nomFichierScannee'] ?? $doc['idFacBl'],
                'numBC' => $doc['numeroBc'],
                'path'  => $doc['path']
            ];
        }, $allDocs);
    }
}
