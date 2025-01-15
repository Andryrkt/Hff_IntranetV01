<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Form\dit\AcSoumisType;
use Symfony\Component\Routing\Annotation\Route;

class AcBcSoumisController extends Controller
{
    /**
     * @Route("/dit/ac-bc-soumis", name="dit_ac_bc_soumis")
     */
    public function traitementFormulaire()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(AcSoumisType::class)->getForm();

        self::$twig->display('dit/AcBcSoumis.html.twig', [
            'form' => $form->createView()
        ]);
    }
}