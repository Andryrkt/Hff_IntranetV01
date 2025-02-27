<?php

namespace App\Controller;

use Exception;
use SplFileObject;

use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


class ProfilControl extends Controller
{
    /**
     * @Route("/Accueil", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        //$okey = $this->ProfilModel->has_permission($_SESSION['user'], 'CREAT_DOM');
        $userId = $this->sessionService->get('user_id');
        if ($userId) {
            $this->logUserVisit('profil_acceuil'); // historisation du page visité par l'utilisateur

            self::$twig->display(
                'main/accueil.html.twig'
            );
        } else {
            $this->redirectToRoute('security_signin');
        }
    }
}
