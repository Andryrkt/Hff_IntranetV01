<?php

namespace App\Controller\ddc;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/demande-de-conge")
 */
class AnnulationCongeController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/annulation-conges", name="annulation_conge")
     */
    public function annulationConge()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->checkPageAccess($this->estAdmin());
        /** FIN AUtorisation accès */

        return $this->render('ddc/conge_annulation.html.twig', [
            'url' => "https://hffc.docuware.cloud/DocuWare/Forms/annulation-conges?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
        ]);
    }
}
