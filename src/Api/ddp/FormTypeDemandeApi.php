<?php

namespace App\Api\ddp;

use App\Controller\Controller;
use App\Form\ddp\FormTypeDemandeType;
use Symfony\Component\Routing\Annotation\Route;

class FormTypeDemandeApi extends Controller
{
    /**
     * @Route("/api/form-type-demande", name="api_form_type_demande", methods = {"GET", "POST"})
     *
     * @return void
     */
    public function showForm()
    {
        $form = $this->getFormFactory()->createBuilder(FormTypeDemandeType::class, null)->getForm();

        $this->getTwig()->display('ddp/formTypeDemande.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
