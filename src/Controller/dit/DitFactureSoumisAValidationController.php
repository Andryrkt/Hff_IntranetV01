<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DitFactureSoumisAValidation;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\dit\DitFactureSoumisAValidationType;

class DitFactureSoumisAValidationController extends Controller
{
    /**
     * @Route("/insertion-facture/{numDit}", name="dit_insertion_facture")
     *
     * @return void
     */
    public function factureSoumisAValidation($numDit)
    {

        $ditFactureSoumiAValidation = new DitFactureSoumisAValidation();
        $ditFactureSoumiAValidation->setNumeroDit($numDit);

        $form = self::$validator->createBuilder(DitFactureSoumisAValidationType::class, $ditFactureSoumiAValidation)->getForm();

        self::$twig->display('dit/DitFactureSoumisAValidation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}