<?php

namespace App\Controller\bdc;

use App\Constants\dw\DwConstant;
use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/compta/demande-de-paiement")
 */
class NewBdcController extends Controller
{
    /**
     * @Route("/bon-de-caisse", name="new_bon_caisse")
     */
    public function newBonCaisse()
    {
        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => DwConstant::LINK["bon-de-caisse"],
            'pageTitle' => "Nouveau bon de caisse",
            'bgColor'   => "bg-orange-cat",
            'height'    => 1300,
        ]);
    }
}
