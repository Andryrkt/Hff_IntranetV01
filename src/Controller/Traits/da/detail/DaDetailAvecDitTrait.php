<?php

namespace App\Controller\Traits\da\detail;

use App\Constants\da\StatutDaConstant;
use App\Controller\Traits\da\DaTrait;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Repository\da\DaObservationRepository;

trait DaDetailAvecDitTrait
{
    use DaTrait;

    //==================================================================================================
    private DaObservationRepository $daObservationRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaDetailAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
    }
    //==================================================================================================
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
        $statutDASupprimable = [StatutDaConstant::STATUT_SOUMIS_APPRO, StatutDaConstant::STATUT_SOUMIS_ATE, StatutDaConstant::STATUT_VALIDE];
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
