<?php

namespace App\Controller\Traits\da\detail;

use App\Entity\dw\DwFacBl;
use App\Entity\dw\DwBcAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Repository\dw\DwBcApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\dw\DwFactureBonLivraisonRepository;

trait DaDetailDirectTrait
{
    use DaDetailTrait;

    //==================================================================================================
    private DwBcApproRepository $dwBcApproRepository;
    private DaObservationRepository $daObservationRepository;
    private DwFactureBonLivraisonRepository $dwFacBlRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDetailDirectTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->dwFacBlRepository = $em->getRepository(DwFacBl::class);
        $this->dwBcApproRepository = $em->getRepository(DwBcAppro::class);
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================


    /** 
     * Obtenir tous les fichiers associés à la demande d'approvisionnement
     * 
     * @param array $tab
     */
    private function getAllDAFile($tab): array
    {
        return [
            [
                'labeltype'  => 'BA',
                'type'       => "Bon d'achat",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-ba',
                'fichiers'   => $this->normalizePaths($tab['baPath']),
            ],
            [
                'labeltype'  => 'DEVPJ',
                'type'       => 'Devis / Pièce(s) jointe(s)',
                'icon'       => 'fa-solid fa-money-bill-wave',
                'colorClass' => 'border-left-devpj',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['devPjPath'], 'nomPj'),
            ],
            [
                'labeltype'  => 'BC',
                'type'       => 'Bon de commande',
                'icon'       => 'fa-solid fa-file-circle-check',
                'colorClass' => 'border-left-bc',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['bcPath'], 'numeroBc'),
            ],
            [
                'labeltype'  => 'FACBL',
                'type'       => 'Facture / Bon de livraison',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fichiers'   => $this->normalizePathsForFacBl($tab['facblPath']),
            ],
        ];
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     * @param iterable<DemandeApproL> $dals lignes demande appro avant affichage twig
     * 
     * @return iterable
     **/
    private function prepareDataForDisplayDetail(iterable $dals): iterable
    {
        $datasPrepared = [];

        foreach ($dals as $dal) {
            $datasPrepared[] = [
                "artRefp"            => $dal->getArtRefp() ?? "-",
                "artDesi"            => $dal->getArtDesi(),
                "nomFournisseur"     => $dal->getNomFournisseur(),
                "dateFinSouhaite"    => $dal->getDateFinSouhaite() ? $dal->getDateFinSouhaite()->format('d/m/Y') : '',
                "prixUnitaire"       => $dal->getPrixUnitaire(),
                "qteDem"             => $dal->getQteDem(),
                "commentaire"        => $dal->getCommentaire() == "" ? "-" : $dal->getCommentaire(),
                "fileNames"          => $dal->getFileNames(),
                "nomFicheTechnique"  => $dal->getNomFicheTechnique(),
                "numeroDemandeAppro" => $dal->getNumeroDemandeAppro(),
                "demandeApproLR"     => $dal->getDemandeApproLR(),
                "estFicheTechnique"  => $dal->getEstFicheTechnique(),
                "urlDelete"          => $this->getUrlGenerator()->generate(
                    'da_delete_line_direct',
                    ['numDa' => $dal->getNumeroDemandeAppro(), 'ligne' => $dal->getNumeroLigne()]
                ),
            ];
        }

        return $datasPrepared;
    }
}
