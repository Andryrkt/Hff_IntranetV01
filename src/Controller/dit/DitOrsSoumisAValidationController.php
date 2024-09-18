<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\dit\DitOrsSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitOrsSoumisAValidationController extends Controller
{
    /**
     * @Route("/insertion-or/{numDit}", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr(Request $request, $numDit)
    {
        $ditInsertionOr = new DitOrsSoumisAValidation();
    
        $form = self::$validator->createBuilder(DitOrsSoumisAValidationType::class, $ditInsertionOr)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            dump($ditInsertionOr);    
            dd($form->getData());
        }


        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
