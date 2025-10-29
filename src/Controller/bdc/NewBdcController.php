<?php

namespace App\Controller\bdc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class NewBdcController extends Controller
{
    use AutorisationTrait;

    /**
     * Affiche la liste des bons de caisse
     * @Route("/bon-de-caisse", name="new_bon_caisse")
     */
    public function newBonCaisse()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DDP);
        /** FIN AUtorisation accès */

        return $this->render('bdc/bon_caisse_new.html.twig', [
            'url' => "https://hffc.docuware.cloud/docuware/forms/bon-de-caisse?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
        ]);
    }
}
