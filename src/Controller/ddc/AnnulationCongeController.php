<?php

namespace App\Controller\ddc;

use App\Constants\dw\DwConstant;
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
            'url'       => DwConstant::LINK["annulation-conges-valide"],
            'pageTitle' => "Annulation congés validés",
            'bgColor'   => "bg-orange-cat",
            'height'    => 1050,
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
            'url'       => DwConstant::LINK["annulation-conges-rh"],
            'pageTitle' => "Annulation de Congé dédiée RH",
            'bgColor'   => "bg-orange-cat",
            'height'    => 980,
        ]);
    }
}
