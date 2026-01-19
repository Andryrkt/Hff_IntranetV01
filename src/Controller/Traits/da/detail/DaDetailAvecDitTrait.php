<?php

namespace App\Controller\Traits\da\detail;

use App\Entity\da\DaObservation;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Entity\dw\DwBcAppro;
use App\Entity\dw\DwFacBl;
use App\Model\dw\DossierInterventionAtelierModel;
use App\Repository\da\DaObservationRepository;
use App\Repository\dit\DitOrsSoumisAValidationRepository;
use App\Repository\dit\DitRepository;
use App\Repository\dw\DwBcApproRepository;
use App\Repository\dw\DwFactureBonLivraisonRepository;

trait DaDetailAvecDitTrait
{
    use DaDetailTrait;

    //==================================================================================================
    private DitRepository $ditRepository;
    private DwBcApproRepository $dwBcApproRepository;
    private DaObservationRepository $daObservationRepository;
    private DwFactureBonLivraisonRepository $dwFacBlRepository;
    private DitOrsSoumisAValidationRepository $ditOrsSoumisAValidationRepository;
    private DossierInterventionAtelierModel $dossierInterventionAtelierModel;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDetailAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
        $this->dwFacBlRepository = $em->getRepository(DwFacBl::class);
        $this->dwBcApproRepository = $em->getRepository(DwBcAppro::class);
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->ditOrsSoumisAValidationRepository = $em->getRepository(DitOrsSoumisAValidation::class);
        $this->dossierInterventionAtelierModel = new DossierInterventionAtelierModel;
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
                'labelType'  => 'BA',
                'type'       => "Bon d'achat",
                'icon'       => 'fa-solid fa-file-signature',
                'colorClass' => 'border-left-bai',
                'fichiers'   => $this->normalizePaths($tab['baiPath']),
            ],
            [
                'labelType'  => 'OR',
                'type'       => 'Ordre de réparation',
                'icon'       => 'fa-solid fa-wrench',
                'colorClass' => 'border-left-or',
                'fichiers'   => $this->normalizePathsForOneFile($tab['orPath'], 'numeroOr'),
            ],
            [
                'labelType'  => 'DEVPJ-DA',
                'type'       => 'Devis / PJ (émis dans la demande / proposition)',
                'icon'       => 'fa-solid fa-money-bill-wave',
                'colorClass' => 'border-left-devpj',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['devPjPathDal'], 'nomPj'),
            ],
            [
                'labelType'  => 'DEVPJ-OBS',
                'type'       => 'Devis / PJ (émis dans l\'observation)',
                'icon'       => 'fa-solid fa-money-bill-wave',
                'colorClass' => 'border-left-devpj',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['devPjPathObs'], 'nomPj'),
            ],
            [
                'labelType'  => 'BC',
                'type'       => 'Bon de commande',
                'icon'       => 'fa-solid fa-file-circle-check',
                'colorClass' => 'border-left-bc',
                'fichiers'   => $this->normalizePathsForManyFiles($tab['bcPath'], 'numeroBc'),
            ],
            [
                'labelType'  => 'FACBL',
                'type'       => 'Facture / Bon de livraison',
                'icon'       => 'fa-solid fa-file-invoice',
                'colorClass' => 'border-left-facbl',
                'fichiers'   => $this->normalizePathsForFacBl($tab['facblPath'], 'nomFichierScannee', 'idFacBl'),
            ],
        ];
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     * @param iterable<DemandeApproL> $dals lignes demande appro avant affichage twig
     * @param string $statutDal statut de la demande appro
     * 
     * @return iterable
     **/
    private function prepareDataForDisplayDetail(iterable $dals, string $statutDal): iterable
    {
        $datasPrepared = [];
        $statutDASupprimable = [DemandeAppro::STATUT_SOUMIS_APPRO, DemandeAppro::STATUT_SOUMIS_ATE, DemandeAppro::STATUT_VALIDE];
        $supprimable = in_array($statutDal, $statutDASupprimable);

        foreach ($dals as $dal) {
            $datasPrepared[] = [
                "artFams1"           => $dal->getArtFams1() ?? "-",
                "artFams2"           => $dal->getArtFams2() ?? "-",
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
                "supprimable"        => $supprimable,
                "urlDelete"          => $supprimable ? $this->getUrlGenerator()->generate(
                    'da_delete_line_avec_dit',
                    ['numDa' => $dal->getNumeroDemandeAppro(), 'ligne' => $dal->getNumeroLigne()]
                ) : null,
            ];
        }

        return $datasPrepared;
    }
}
