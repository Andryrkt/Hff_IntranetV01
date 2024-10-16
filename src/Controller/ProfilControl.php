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
     * @Route("/Authentification", name="profil_authentification")
     */
    public function showInfoProfilUser()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $Username = isset($_POST['Username']) ? $_POST['Username'] : '';
            $Password = isset($_POST['Pswd']) ? $_POST['Pswd'] : '';
            $Connexion_Ldap_User = $this->ldap->userConnect($Username, $Password);


            if (!$Connexion_Ldap_User) {
                echo '<script type="text/javascript">
                    alert("Merci de vérifier votre session LDAP");
                    document.location.href = "/Hffintranet";
                </script>';
            } else {
                
                try {
                        //$session->start();
                        $user = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $Username]);
                        //$user = self::$em->getRepository(User::class)->findOneBy(['nom_utilisateur' => 'lala']);
                        
                        if (isset($user)) {
                            $userId = $user->getId();
                            $this->sessionService->set('user_id', $userId);
                            // session_start();
                            $_SESSION['user'] = $Username;

                            $_SESSION['password'] = $Password;
                        } else {
                            // Gérer le cas où l'utilisateur n'existe pas
                            throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $Username);
                        }
                } catch (\Exception $e) {
               
                    $this->redirectToRoute('utilisateur_non_touver', ["message" => $e->getMessage()]);
                }



                self::$twig->display(
                    'main/home.html.twig'
                );
            }
        }
    }

    // public function showinfoAllUsercours()
    // {
    //     $this->SessionStart();

    //     try {
    //         //$UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
    //         $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);


    //         //include 'Views/Principe.php';

    //         include 'Views/Propos_page.php';
    //     } catch (Exception $e) {
    //         echo "Error: " . $e->getMessage();
    //     }
    // }

    /**
     * @Route("/Acceuil", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {       

        //$okey = $this->ProfilModel->has_permission($_SESSION['user'], 'CREAT_DOM');

        self::$twig->display(
            'main/accueil.html.twig'
        );
    }
}
