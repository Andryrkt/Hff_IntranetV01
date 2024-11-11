<?php

namespace App\Controller\admin\tki;

use App\Controller\Controller;
use App\Form\ticketing\DemandeSupportInformatiqueType;
use Symfony\Component\Routing\Annotation\Route;

class DemandeSupportInformatiqueController extends Controller
{
    /**
     * @Route("/demande_support_informatique", name="demande_support_informatique")
     */
    public function new()
    {
        $form = self::$validator->createBuilder(DemandeSupportInformatiqueType::class)->getForm();
        self::$twig->display('tik/demandeSupportInformatique/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

?>