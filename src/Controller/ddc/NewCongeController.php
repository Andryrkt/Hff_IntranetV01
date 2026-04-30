<?php

namespace App\Controller\ddc;

use App\Constants\dw\DwConstant;
use App\Controller\Controller;
use App\Entity\admin\Application;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/demande-de-conge")
 */
class NewCongeController extends Controller
{
    /**
     * @Route("/nouveau-conge", name="new_conge")
     */
    public function nouveauConge()
    {
        return $this->render("dwForm/dwForm.html.twig", [
            'url'       => DwConstant::LINK["new-conge"],
            'pageTitle' => "Nouvelle demande de congé",
            'bgColor'   => "bg-orange-cat",
            'height'    => 1530,
        ]);
    }
}
