<?php

namespace App\Controller\ddc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/demande-de-conge")
 */
class NewCongeController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/nouveau-conge", name="new_conge")
     */
    public function nouveauConge()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        /** FIN AUtorisation accès */

        return $this->render('ddc/conge_new.html.twig', [
            'url' => "https://hffc.docuware.cloud/docuware/formsweb/demande-de-conges-new?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
        ]);
    }
}
