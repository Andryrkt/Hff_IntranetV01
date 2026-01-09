<?php

namespace App\Controller\da\Creation;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/demande-appro") */
class DaNewController extends Controller
{
    /**
     * @Route("/da-first-form", name="da_first_form")
     */
    public function firstForm()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        return $this->render('da/first-form.html.twig', [
            'estAte'                 => $this->estUserDansServiceAtelier(),
            'estCreateurDeDADirecte' => $this->estCreateurDeDADirecte(),
            'urls'                   => [
                'avecDit' => $this->getUrlGenerator()->generate('da_list_dit'),
                'direct'  => $this->getUrlGenerator()->generate('da_new_direct', ['id' => 0]),
                'reappro' => $this->getUrlGenerator()->generate('da_new_reappro_mensuel', ['id' => 0]),
            ],
            'estAdmin'               => $this->estAdmin(),
        ]);
    }
}
