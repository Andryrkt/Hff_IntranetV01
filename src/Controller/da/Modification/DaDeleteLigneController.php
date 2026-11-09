<?php

namespace App\Controller\da\Modification;

use App\Controller\Controller;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\da\DaAfficherService;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * Suppression d'une ligne de DA (DA avec DIT et DA directe).
 *
 * @Route("/demande-appro")
 */
class DaDeleteLigneController extends Controller
{
    private DaAfficherService $daAfficherService;

    public function __construct(DaAfficherService $daAfficherService)
    {
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * @Route("/delete-line/{numDa}/{ligne}", name="da_delete_line")
     */
    public function deleteLineDa(string $numDa, string $ligne)
    {
        $em = $this->getEntityManager();
        $criteres = ['numeroDemandeAppro' => $numDa, 'numeroLigne' => $ligne];

        $demandeApproLs = $em->getRepository(DemandeApproL::class)->findBy($criteres);

        // suivi étape par étape de la suppression (admin > historique > suivi des opérations)
        $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DA_SUPPRESSION_LIGNE, $numDa);

        if ($demandeApproLs) {
            $suivi->etape('Suppression de la ligne ' . $ligne . ' (base de données)', function () use ($em, $demandeApproLs, $criteres) {
                $demandeApproLRs = $em->getRepository(DemandeApproLR::class)->findBy($criteres);

                foreach (array_merge($demandeApproLs, $demandeApproLRs) as $entite) {
                    $em->remove($entite);
                }

                $em->flush(); // enregistrer les modifications avant l'appel à "ajouterDansTableAffichageParNumDa"
            });
            $suivi->etape('Mise à jour da_afficher', function () use ($numDa) {
                $this->daAfficherService->ajouterDansTableAffichageParNumDa($numDa); // ajout dans la table DaAfficher si le statut a changé
            });

            $notifType = "success";
            $notifMessage = "Réussite de l'opération: la ligne de DA a été supprimée avec succès.";
        } else {
            $notifType = "danger";
            $notifMessage = "Echec de la suppression de la ligne: la ligne de DA n'existe pas.";
            $suivi->echecOperation($notifMessage . " (ligne $ligne)");
        }
        $this->getSessionService()->set('notification', ['type' => $notifType, 'message' => $notifMessage]);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
