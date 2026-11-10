<?php

namespace App\Api\da;

use App\Controller\Controller;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\da\DaAfficherService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * Suppression d'une ligne de DA (DA avec DIT et DA directe).
 *
 * @Route("/api/demande-appro")
 */
class DaDeleteLigneApi extends Controller
{
    private DaAfficherService $daAfficherService;

    public function __construct(DaAfficherService $daAfficherService)
    {
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * @Route("/delete-line/{numDa}/{ligne}", name="api_da_delete_line", methods={"POST"})
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

            return new JsonResponse([
                'status'  => 'success',
                'title'   => 'Action effectuée',
                'message' => "La ligne de DA a été supprimée avec succès.",
            ]);
        }

        $message = "Echec de la suppression de la ligne: la ligne de DA n'existe pas.";
        $suivi->echecOperation($message . " (ligne $ligne)");

        return new JsonResponse([
            'status'  => 'error',
            'title'   => 'Erreur lors de la suppression',
            'message' => $message,
        ], 404);
    }
}
