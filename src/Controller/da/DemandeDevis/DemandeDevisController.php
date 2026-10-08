<?php

namespace App\Controller\da\DemandeDevis;

use App\Service\da\DaService;
use App\Controller\Controller;
use App\Service\da\DaAfficherService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DemandeDevisController extends Controller
{
    private DaService $daService;
    private DaAfficherService $daAfficherService;

    public function __construct(DaService $daService, DaAfficherService $daAfficherService)
    {
        $this->daService         = $daService;
        $this->daAfficherService = $daAfficherService;
    }

    /**
     * @Route("/demande-devis-en-cours/{id}", name="api_da_demande_devis_en_cours")
     */
    public function demandeDevisEnCours(int $id)
    {
        $demandeAppro = $this->daService->getDemandeAppro($id);

        if (!$demandeAppro) {
            /** NOTIFICATION */
            $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => 'La demande d’achat que vous avez sélectionner n’existe pas.']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
        }

        $this->daService->appliquerStatutDemandeDevisEnCours($demandeAppro, $this->getUserName());

        $this->daAfficherService->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro()); // enregistrer dans la table Da Afficher

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Le statut de la demande d’achat a été modifié avec succès.']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 0, 'page' => 1]);
    }
}
