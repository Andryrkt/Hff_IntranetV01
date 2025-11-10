<?php

namespace App\Controller\ddc;

use App\Controller\Controller;
use App\Entity\admin\Application;
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
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        /** FIN AUtorisation accès */

        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => "https://hffc.docuware.cloud/DocuWare/Forms/annulation-conges?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
            'pageTitle' => "Annulation Demande d’absence",
            'bgColor'   => "bg-orange-cat",
            'height'    => 980,
        ]);
    }

    /**
     * @Route("/annulation-conges-rh", name="annulation_conge_rh")
     */
    public function annulationCongeDedieRH()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        /** FIN AUtorisation accès */

        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => "https://hffc.docuware.cloud/DocuWare/Forms/annulation-conges-rh?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
            'pageTitle' => "Annulation de Congé dédiée RH",
            'bgColor'   => "bg-orange-cat",
            'height'    => 980,
        ]);
    }
}
