<?php

namespace App\Controller;

use Exception;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use Symfony\Component\Routing\Annotation\Route;

class ProfilControl extends Controller
{
    /**
     * @Route("/Hffintranet/Authentification", name="profil_sigin")
     */
    public function showInfoProfilUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $Username = isset($_POST['Username']) ? $_POST['Username'] : '';
            $Password = isset($_POST['Pswd']) ? $_POST['Pswd'] : '';
            $Ldap = new LdapModel();
            $Connexion_Ldap_User = $Ldap->userConnect($Username, $Password);
            if (!$Connexion_Ldap_User) {
                echo '<script type="text/javascript">
                    alert("Merci de vérifier votre session LDAP");
                    document.location.href = "/Hffintranet";
                </script>';
            } else {
                session_start();
                $_SESSION['user'] = $Username;
                //$UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
                $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);


                $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
                $text = file_get_contents($fichier);
                $boolean = strpos($text, $_SESSION['user']);

                $this->twig->display(
                    'main/accueil.html.twig',
                    [
                        'infoUserCours' => $infoUserCours,
                        'boolean' => $boolean
                    ]
                );
            }
        }
    }

    public function showinfoAllUsercours()
    {
        $this->SessionStart();

        try {
            //$UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
            $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);


            //include 'Views/Principe.php';

            include 'Views/Propos_page.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * @Route("/Hffintranet/Acceuil", name="profil_acceuil")
     */
    public function showPageAcceuil()
    {
        $this->SessionStart();


        $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $this->twig->display(
            'main/accueil.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean
            ]
        );
    }
}
