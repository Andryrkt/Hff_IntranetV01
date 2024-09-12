<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DitInsertionOr;
use App\Form\dit\DitInsertionOrType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitInsertionOrController extends Controller
{
    /**
     * @Route("/insertion-or/{numDit}", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr(Request $request, $numDit)
    {
        $ditInsertionOr = new DitInsertionOr();
        $ditInsertionOr->setNumeroDit($numDit);
        $form = self::$validator->createBuilder(DitInsertionOrType::class, $ditInsertionOr)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $ditInsertionOr->setNumeroDit($numDit);
            dump($ditInsertionOr);    
            dd($form->getData());

        }


        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
