<?php

namespace App\Api\ddp;

use App\Controller\Controller;
use App\Form\ddp\FormTypeDemandeType;
use Symfony\Component\Routing\Annotation\Route;

class FormTypeDemandeApi extends Controller
{
    /**
     * @Route("/api/form-type-demande", name="form_type_demande", methods = {"GET", "POST"})
     *
     * @return void
     */
    public function showForm()
    {
        $form = self::$validator->createBuilder(FormTypeDemandeType::class, null)->getForm();
        
        self::$twig->display('ddp/formTypeDemande.html.twig', [
            'form' => $form->createView()
        ]);
    }
}