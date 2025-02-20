<?php

namespace App\Controller;

use Exception;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Authentification extends Controller
{
    /**
     * @Route("/login/aut", name="security_signin", methods={"GET", "POST"})
     */
    public function affichageSingnin(Request $request)
    {
        $error_msg = null;
        if ($request->isMethod('POST')) {
            $Username = $request->request->get('Username', '');
            $Password = $request->request->get('Pswd', '');

            try {
                $user   = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $Username]);
                $userId = ($user) ? $user->getId() : '-';
                $this->sessionService->set('user_id', $userId);
                if (!$user) {
                    throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $Username);
                }

                if (!$this->ldap->userConnect($Username, $Password)) {
                    $this->logUserVisit('security_signin'); // historisation du page visité par l'utilisateur
                    $error_msg = "Vérifier les informations de connexion, veuillez saisir le nom d'utilisateur et le mot de passe de votre session Windows";
                } else {
                    $this->sessionService->set('user', $Username);
                    $this->sessionService->set('password', $Password);

                    $this->redirectToRoute('profil_acceuil');
                }
            } catch (Exception $e) {
                $this->logUserVisit('security_signin'); // historisation du page visité par l'utilisateur
                $error_msg = $e->getMessage();
            }
        }

        self::$twig->display('signin.html.twig', [
            'error_msg' => $error_msg,
        ]);
    }

    /**
     * @Route("/logout", name="auth_deconnexion")
     *
     * @return void
     */
    public function deconnexion()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->SessionDestroy();
    }
}
