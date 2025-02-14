<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Form\ddp\DemandePaiementType;
use Symfony\Component\Routing\Annotation\Route;

class DemandePaiementController extends Controller
{

    /**
     * @Route("/demande-paiement/{id}", name="demande_paiement")
     */
    public function afficheForm($id)
    {
        $form = self::$validator->createBuilder(DemandePaiementType::class, null)->getForm();
        self::$twig->display('ddp/demandePaiementNew.html.twig', [
            'id' => $id,
            'form' => $form->createView()
        ]);
    }
    
}