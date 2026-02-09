<?php

namespace App\Service\da;

use App\Entity\dw\DwFacBl;
use App\Entity\dw\DwBcAppro;
use App\Entity\dw\DwDaDirect;
use App\Entity\dw\DwDaReappro;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\dw\DwBcApproRepository;
use App\Repository\dw\DwDaDirectRepository;
use App\Repository\dw\DwDaReapproRepository;
use App\Repository\dw\DwFactureBonLivraisonRepository;

class DwDataService
{
    private DwBcApproRepository $dwBcApproRepository;
    private DwDaDirectRepository $dwDaDirectRepository;
    private DwDaReapproRepository $dwDaReapproRepository;
    private DwFactureBonLivraisonRepository $dwFacBlRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->dwFacBlRepository     = $em->getRepository(DwFacBl::class);
        $this->dwBcApproRepository   = $em->getRepository(DwBcAppro::class);
        $this->dwDaDirectRepository  = $em->getRepository(DwDaDirect::class);
        $this->dwDaReapproRepository = $em->getRepository(DwDaReappro::class);
    }

    /** 
     * Obtenir l'url du bon d'achat validé DW
     */
    public function getBaDocuWarePath(DemandeAppro $demandeAppro): array
    {
        $numDa    = $demandeAppro->getNumeroDemandeAppro();
        $daTypeId = $demandeAppro->getDaTypeId();
        $allDocs  = [];
        $result   = [];

        if ($daTypeId === DemandeAppro::TYPE_DA_DIRECT) {
            $allDocs = $this->dwDaDirectRepository->getPathByNumDa($numDa);
        } elseif ($daTypeId === DemandeAppro::TYPE_DA_REAPPRO_MENSUEL || $daTypeId === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL) {
            $allDocs = $this->dwDaReapproRepository->getPathByNumDa($numDa);
        }

        if (!empty($allDocs)) {
            $result = array_map(function ($doc) use ($numDa) {
                $doc['num']  = "$numDa-" . ($doc['numeroVersion'] ?? '1');
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return $result;
    }

    /** 
     * Obtenir l'url du bon de commande
     */
    public function getBcPath(DemandeAppro $demandeAppro): array
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $allDocs = $this->dwBcApproRepository->getPathAndNumeroBCByNumDa($numDa);
        $result  = [];

        if (!empty($allDocs)) {
            $result = array_map(function ($doc) {
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return $result;
    }

    /** 
     * Obtenir l'url du bon de livraison + facture
     */
    public function getFacBlPath(DemandeAppro $demandeAppro): array
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();
        $allDocs = $this->dwFacBlRepository->getPathByNumDa($numDa);
        $result  = [];

        if (!empty($allDocs)) {
            $result = array_map(function ($doc) {
                $doc['path'] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $doc['path'];
                return $doc;
            }, $allDocs);
        }

        return $result;
    }
}
