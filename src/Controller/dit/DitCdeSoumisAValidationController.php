<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DitCdeSoumisAValidation;
use App\Form\dit\DitCdeSoumisAValidationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DitCdeSoumisAValidationController extends Controller
{
    /**
     * @Route("/insertion-cde", name="dit_insertion_cde")
     */
    public function cdeSoumisAValidation(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $ditCdeSoumisAValidation = new DitCdeSoumisAValidation();

        
        $form = self::$validator->createBuilder(DitCdeSoumisAValidationType::class)->getForm();
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            dd($form->getData());
        }

        self::$twig->display('dit/DitCdeSoumisAValidation.html.twig', [
            'form' => $form->createView()
        ]);
    }

}