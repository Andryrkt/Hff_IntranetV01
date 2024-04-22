<?php

namespace App\Controller\admin\personnel;

use App\Controller\Controller;

use App\Controller\Traits\Transformation;



class PersonnelControl extends Controller
{

    use Transformation;


    public function showPersonnelForm()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo 'okey';
        } else {
            $codeSage = $this->transformEnSeulTableau($this->Person->recupAgenceServiceSage());
            $codeIrium = $this->transformEnSeulTableau($this->Person->recupAgenceServiceIrium());
            $serviceIrium = $this->transformEnSeulTableau($this->Person->recupServiceIrium());


            $this->twig->display(
                'admin/personnel/addPersonnel.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'codeSage' => $codeSage,
                    'codeIrium' => $codeIrium,
                    'serviceIrium' => $serviceIrium
                ]
            );
        }
    }

    public function showListePersonnel()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $infoPersonnel = $this->Person->recupInfoPersonnel();

        // var_dump($infoPersonnel);
        // die();



        $this->twig->display(
            'admin/personnel/listPersonnel.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'infoPersonnel' => $infoPersonnel
            ]
        );
    }

    public function updatePersonnel()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $codeSage = $this->transformEnSeulTableau($this->Person->recupAgenceServiceSage());
        $codeIrium = $this->transformEnSeulTableau($this->Person->recupAgenceServiceIrium());


        $infoPersonnelId = $this->Person->recupInfoPersonnelMatricule($_GET['matricule']);
        $this->twig->display(
            'admin/personnel/addPersonnel.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'codeSage' => $codeSage,
                'codeIrium' => $codeIrium,
                'infoPersonnelId' => $infoPersonnelId
            ]
        );
    }
}
