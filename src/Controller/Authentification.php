<?php

namespace App\Controller;

use Exception;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Annotation\Route;

class Authentification extends Controller
{
    /**
     * @Route("/", name="security_signin", methods={"GET", "POST"})
     */
    public function affichageSingnin()
    {
        $error_msg = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $Username = isset($_POST['Username']) ? $_POST['Username'] : '';
            $Password = isset($_POST['Pswd']) ? $_POST['Pswd'] : '';
            $Connexion_Ldap_User = $this->ldap->userConnect($Username, $Password);

            if (!$Connexion_Ldap_User) {
                $error_msg = "Vérifier les informations de connexion, veuillez saisir le nom d'utilisateur et le mot de passe de votre session Windows";
            } else {
                try {
                    //$session->start();
                    $user = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $Username]);
                    //$user = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => 'lala']);

                    if (isset($user)) {
                        $userId = $user->getId();
                        $this->sessionService->set('user_id', $userId);
                        // session_start();

                        $this->sessionService->set('user', $Username);
                        //$_SESSION['user'] = $Username;

                        $this->sessionService->set('password', $Password);
                        //$_SESSION['password'] = $Password;
                    } else {
                        // Gérer le cas où l'utilisateur n'existe pas
                        throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $Username);
                    }
                } catch (Exception $e) {

                    $this->redirectToRoute('utilisateur_non_touver', ["message" => $e->getMessage()]);
                }

                $this->redirectToRoute('profil_acceuil');
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
