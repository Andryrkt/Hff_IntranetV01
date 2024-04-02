<?php

namespace App\Controller;

class BadmController extends Controller
{
    public function formBadm()
    {


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            var_dump($_POST);

            var_dump($this->badm->recuperationCaracterMateriel());
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);


            $this->twig->display(
                'badm/formCompleBadm.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean
                ]
            );
        } else {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
            $this->twig->display(
                'badm/formBadm.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean

                ]
            );
        }
    }

    public function formCompleBadm()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $jsonsata = file_get_contents("php://input");
            $data = json_decode($jsonsata, true);

            if (!empty($data)) {
                $tab = [
                    "message" => $jsonsata
                ];
            } else {
                $tab = [
                    "message" => 'zero données'
                ];
            }


            echo json_encode($tab);
        }
    }
}
