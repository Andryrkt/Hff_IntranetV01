<?php

namespace App\Controller\bdc;

use App\Controller\Controller;
use App\Entity\admin\Application;
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
            'url'       => "https://hffc.docuware.cloud/docuware/forms/bon-de-caisse?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be",
            'pageTitle' => "Nouveau bon de caisse",
            'bgColor'   => "bg-orange-cat",
            'height'    => 1300,
        ]);
    }
}
