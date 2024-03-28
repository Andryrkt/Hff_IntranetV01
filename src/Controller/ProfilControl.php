<?php

namespace App\Controller;

use Exception;
use App\Model\ProfilModel;

class ProfilControl extends Controller
{
    private $ProfilModel;

    public function __construct()
    {
        parent::__construct();
        $this->ProfilModel = new ProfilModel();
    }

    public function showInfoProfilUser()
    {
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }


        $UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
        $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";

        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        //$app = $infoUserCours[0]['App'];

        $this->twig->display(
            'main/Principe.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'UserConnect' => $UserConnect,
                'boolean' => $boolean
            ]
        );
        // $this->twig->display(
        //     'main/accueil.html.twig'
        // );

        //include 'Views/Principe.php';
        //include 'Views/Acceuil.php';

    }

    public function showinfoAllUsercours()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {
            $UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);
            $infoUserCours = $this->ProfilModel->getINfoAllUserCours($_SESSION['user']);


            //include 'Views/Principe.php';

            include 'Views/Propos_page.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function showPageAcceuil()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }


        $UserConnect = $this->ProfilModel->getProfilUser($_SESSION['user']);

        $this->twig->display(
            'main/accueil.html.twig',
            [
                'UserConnect' => $UserConnect,
            ]
        );


        // include 'Views/Principe.php';
        //include 'Views/Acceuil.php';

    }
}
