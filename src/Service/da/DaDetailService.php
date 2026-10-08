<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DaDetailService
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Prépare les lignes DAL pour l'affichage Twig (détail).
     *
     * @param iterable $dals                lignes demande appro (DemandeApproL)
     * @param string   $statutDal           statut de la demande appro
     * @param string   $routeSuppressionLigne nom de la route de suppression d'une ligne (ex. 'da_delete_line_avec_dit')
     */
    public function prepareDataForDisplayDetail(iterable $dals, string $statutDal, string $routeSuppressionLigne): array
    {
        $datasPrepared = [];
        $supprimable = in_array($statutDal, [StatutDaConstant::STATUT_SOUMIS_APPRO, StatutDaConstant::STATUT_SOUMIS_ATE, StatutDaConstant::STATUT_VALIDE]);

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
                "urlDelete"          => $supprimable ? $this->urlGenerator->generate(
                    $routeSuppressionLigne,
                    ['numDa' => $dal->getNumeroDemandeAppro(), 'ligne' => $dal->getNumeroLigne()]
                ) : null,
            ];
        }

        return $datasPrepared;
    }
}
