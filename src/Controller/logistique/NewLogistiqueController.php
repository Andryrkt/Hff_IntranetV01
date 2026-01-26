<?php

namespace App\Controller\logistique;

use App\Controller\Controller;
use App\Controller\Traits\AutorisationTrait;
use App\Entity\admin\Application;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel/logistique")
 */
class NewLogistiqueController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/new-logistique", name="new_logistique")
     */
    public function newLogistique()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_BADM);
        /** FIN AUtorisation accès */

        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => "https://hffc.docuware.cloud/DocuWare/Forms/transport-logistique?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
            'pageTitle' => "Nouvelle demande logistique",
            'bgColor'   => "bg-bleu-hff",
            'height'    => 1300,
        ]);
    }
}
