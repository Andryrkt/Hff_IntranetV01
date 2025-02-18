<?php

namespace App\Controller\mutation;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MutationController extends Controller
{
    /**
     * @Route("/mutation/new", name="mutation_nouvelle_demande")
     */
    public function firstForm(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
    }
}
