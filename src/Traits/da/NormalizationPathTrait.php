<?php

namespace App\Traits\da;

trait NormalizationPathTrait
{
    /** 
     * Normaliser les chemins pour un seul fichier
     * 
     * @param array  $doc
     * @param string $numKey
     * 
     * @return array
     */
    private function normalizePathSingleFile(array $doc, string $numKey): array
    {
        if (empty($doc)) return [];

        return $this->normalizePathsMultipleFiles([$doc], $numKey);
    }

    /** 
     * Normaliser les chemins pour plusieurs fichiers
     * 
     * @param array  $allDocs
     * @param string $numKey
     * 
     * @return array
     */
    private function normalizePathsMultipleFiles(array $allDocs, string $numKey): array
    {
        if (empty($allDocs)) return [];

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
     * 
     * @return array
     */
    private function normalizePathsFacBl(array $allDocs): array
    {
        if (empty($allDocs)) return [];

        return array_map(function ($doc) {
            return [
                'nom'   => $doc['nomFichierScannee'] ?? $doc['idFacBl'],
                'numBC' => $doc['numeroBc'],
                'path'  => $doc['path']
            ];
        }, $allDocs);
    }
}
