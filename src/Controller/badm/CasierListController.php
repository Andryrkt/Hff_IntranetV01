<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use App\Entity\Casier;
use App\Entity\CasierValider;
use App\Form\CasierSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CasierListController extends Controller
{

    use Transformation;
    
/**
 * @Route("/listCasier/{page?1}", name="liste_affichageListeCasier")
 */
    public function AffichageListeCasier(Request $request , $page)
    {   

       $data = self::$em->getRepository(CasierValider::class)->findAll();


       $form = self::$validator->createBuilder(CasierSearchType::class)->getForm();

        self::$twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'casier' => $data,
                'form' => $form->createView()
            ]
        );
    }

}
