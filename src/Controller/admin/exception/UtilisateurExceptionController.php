<?php

namespace App\Controller\admin\exception;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurExceptionController extends Controller
{
    /**
     * @Route("/erreur-utilisateur-non-trouver/{message}", name="utilisateur_non_touver")
     *
     * @return void
     */
    public function utilisateurNonTrouver($message)
    {
        self::$twig->display('admin/exception/utilisateurException.html.twig', 
    [
        'message' => $message,
    ]);
    }
}