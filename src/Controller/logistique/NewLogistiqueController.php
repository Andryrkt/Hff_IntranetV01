<?php

namespace App\Controller\logistique;

use App\Constants\dw\DwConstant;
use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materiel/logistique")
 */
class NewLogistiqueController extends Controller
{
    /**
     * @Route("/new-logistique", name="new_logistique")
     */
    public function newLogistique()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => DwConstant::LINK["new-logistique"],
            'pageTitle' => "Nouvelle demande logistique",
            'bgColor'   => "bg-bleu-hff",
            'height'    => 1300,
        ]);
    }
}
