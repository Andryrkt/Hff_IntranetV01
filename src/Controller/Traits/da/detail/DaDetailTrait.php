<?php

namespace App\Controller\Traits\da\detail;

use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DemandeAppro;

trait DaDetailTrait
{
    use DaTrait;

    /** 
     * Normaliser les chemins des fichiers pour l'affichage
     * 
     * @param mixed $paths
     * @return array
     */
    private function normalizePaths($paths): array
    {
        if ($paths === '-' || empty($paths)) {
            return [];
        }

        if (!is_array($paths)) {
            $paths = [$paths];
        }

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

        if ($doc !== '-' && !empty($doc)) {
            $tabReturn[] = [
                'nom'  => $doc[$numKey],
                'path' => $doc['path']
            ];
        }

        return $tabReturn;
    }

    /** 
     * Normaliser les chemins pour plusieurs fichiers
     * 
     * @param array $allDocs
     * @param string $numKey1
     * @param string $numKey2
     * @return array
     */
    private function normalizePathsForManyFiles($allDocs, string $numKey): array
    {
        if ($allDocs === '-' || empty($allDocs)) {
            return [];
        }

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
        if ($allDocs === '-' || empty($allDocs)) {
            return [];
        }

        return array_map(function ($doc) {

            return [
                'nom'   => $doc['nomFichierScannee'] ?? $doc['idFacBl'],
                'numBC' => $doc['numeroBc'],
                'path'  => $doc['path']
            ];
        }, $allDocs);
    }

    /** 
     * Obtenir l'url du bon d'achat
     */
    private function getBaIntranetPath(DemandeAppro $demandeAppro): string
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        if (in_array($demandeAppro->getStatutDal(), [DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_TERMINER])) {
            return $_ENV['BASE_PATH_FICHIER_COURT'] . "/da/$numDa/$numDa.pdf";
        }
        return "-";
    }

    /** 
     * Obtenir l'url du bon d'achat validé DW
     */
    private function getBaDocuWarePath(DemandeAppro $demandeAppro)
    {
        $numDa    = $demandeAppro->getNumeroDemandeAppro();
        $daTypeId = $demandeAppro->getDaTypeId();
        $allDocs  = [];

        if ($daTypeId === DemandeAppro::TYPE_DA_DIRECT) {
            $allDocs = $this->dwDaDirectRepository->getPathByNumDa($numDa);
        } elseif ($daTypeId === DemandeAppro::TYPE_DA_REAPPRO) {
            $allDocs = $this->dwFacBlRepository->getPathByNumDa($numDa);
        }

        if (!empty($allDocs)) {
            return array_map(function ($doc) use ($numDa) {
                $doc['num']  = "$numDa-" . ($doc['numeroVersion'] ?? '1');
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return "-";
    }

    /** 
     * Obtenir l'url de l'ordre de réparation
     */
    private function getOrPath(DemandeAppro $demandeAppro)
    {
        $dataOR       = $this->dossierInterventionAtelierModel->findCheminOrDernierValide($demandeAppro->getNumeroDemandeDit(), $demandeAppro->getNumeroDemandeAppro());
        if ($dataOR) {
            return [
                'numeroOr' => $dataOR['numero'],
                'path'     => $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $dataOR['chemin']
            ];
        }
        return "-";
    }

    /** 
     * Obtenir l'url du bon de commande
     */
    private function getBcPath(DemandeAppro $demandeAppro)
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $allDocs = $this->dwBcApproRepository->getPathAndNumeroBCByNumDa($numDa);

        if (!empty($allDocs)) {
            return array_map(function ($doc) {
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return "-";
    }

    /** 
     * Obtenir l'url du bon de livraison + facture
     */
    private function getFacBlPath(DemandeAppro $demandeAppro)
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $allDocs = $this->dwFacBlRepository->getPathByNumDa($numDa);

        if (!empty($allDocs)) {
            return array_map(function ($doc) {
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return "-";
    }

    /** 
     * Obtenir l'url des devis et pièces jointes
     */
    private function getDevisPjPath(DemandeAppro $demandeAppro)
    {
        $items = [];

        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $pjDals = $this->demandeApproLRepository->findAttachmentsByNumeroDA($numDa);
        $pjDalrs = $this->demandeApproLRRepository->findAttachmentsByNumeroDA($numDa);

        /** 
         * Fusionner les résultats des deux tables
         * @var array<int, array{numeroDemandeAppro: string, fileNames: array}>
         **/
        $allRows = array_merge($pjDals, $pjDalrs);

        if (!empty($allRows)) {
            foreach ($allRows as $row) {
                $files = $row['fileNames'];
                foreach ($files as $fileName) {
                    $items[] = [
                        'nomPj' => $fileName,
                        'path'  => "{$_ENV['BASE_PATH_FICHIER_COURT']}/da/$numDa/$fileName",
                    ];
                }
            }
            return $items;
        }

        return "-";
    }
}
