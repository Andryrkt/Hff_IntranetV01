<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\admin\exception;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

class UtilisateurExceptionController extends BaseController
{
    /**
     * @Route("/erreur-utilisateur-non-trouver/{message}", name="utilisateur_non_touver")
     *
     * @return void
     */
    public function utilisateurNonTrouver($message)
    {
        return $this->render('admin/exception/utilisateurException.html.twig', 
    [
        'message' => $message,
    ]);
    }
}