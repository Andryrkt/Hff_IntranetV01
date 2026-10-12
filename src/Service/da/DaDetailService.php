<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Constants\da\StatutDaConstant;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Orchestre le détail d'une DA : préparation de l'affichage et envoi d'une observation.
 */
class DaDetailService
{
    private DaService $daService;
    private EmailDaService $emailDaService;
    private UrlGeneratorInterface $urlGenerator;
    private DaObservationService $daObservationService;
    private DaAfficherService $daAfficherService;

    public function __construct(
        DaService $daService,
        EmailDaService $emailDaService,
        UrlGeneratorInterface $urlGenerator,
        DaObservationService $daObservationService,
        DaAfficherService $daAfficherService
    ) {
        $this->daService = $daService;
        $this->emailDaService = $emailDaService;
        $this->urlGenerator = $urlGenerator;
        $this->daObservationService = $daObservationService;
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * Enregistre une observation depuis le détail : insertion, autorisation de l'émetteur (APPRO, si coché),
     * mise à jour de DaAfficher, mail. Renvoie la notification, la redirection reste au contrôleur.
     * Pour un Reappro, le formulaire n'a pas de case « statutChange » (toujours false) : aucun changement de statut.
     */
    public function envoyerObservation(DemandeAppro $demandeAppro, DaObservation $daObservation, string $username, User $connectedUser, bool $estAppro): array
    {
        $numDa = $demandeAppro->getNumeroDemandeAppro();

        $this->daObservationService->insertionObservation($numDa, $daObservation->getObservation(), $username, $daObservation->getFileNames());

        if ($estAppro && $daObservation->getStatutChange()) {
            $this->daService->appliquerChangementStatut($demandeAppro, StatutDaConstant::STATUT_AUTORISER_EMETTEUR);

            $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa);
        }

        $this->emailDaService->envoyerMailObservationDa($demandeAppro, $daObservation->getObservation(), $connectedUser, $estAppro);

        return [
            'type'    => 'success',
            'message' => 'Votre observation a été enregistré avec succès.',
        ];
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
