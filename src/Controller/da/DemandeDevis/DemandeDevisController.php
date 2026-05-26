<?php

namespace App\Controller\da\DemandeDevis;

use App\Controller\Controller;
use App\Controller\Traits\da\DaAfficherTrait;
use App\Controller\Traits\da\demandeDevis\DaDemandeDevisTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class DemandeDevisController extends Controller
{
    use DaAfficherTrait;
    use DaDemandeDevisTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaTrait();
    }

    /**
     * @Route("/demande-devis-en-cours/{id}", name="api_da_demande_devis_en_cours")
     */
    public function demandeDevisEnCours(int $id)
    {
        $demandeAppro = $this->demandeApproRepository->find($id);

        if (!$demandeAppro) {
            /** NOTIFICATION */
            $this->getSessionService()->set('notification', ['type' => 'danger', 'message' => 'La demande d’achat que vous avez sélectionner n’existe pas.']);
            $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 1, 'page' => 1]);
        }

        $this->appliquerStatutDemandeDevisEnCours($demandeAppro, $this->getUserName());

        $this->ajouterDansTableAffichageParNumDa($demandeAppro->getNumeroDemandeAppro()); // enregistrer dans la table Da Afficher

        /** NOTIFICATION */
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => 'Le statut de la demande d’achat a été modifié avec succès.']);
        $this->redirectToRoute("list_da", ['mes_da_a_traiter' => 1, 'page' => 1]);
    }
}
