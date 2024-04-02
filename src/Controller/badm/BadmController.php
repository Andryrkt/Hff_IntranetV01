<?php

namespace App\Controller;

class BadmController extends Controller
{



    public function formBadm()
    {


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //var_dump($_POST);

            var_dump($this->badm->recuperationCaracterMateriel(), 'OKey');


            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            // $this->twig->display(
            //     'badm/formCompleBadm.html.twig',
            //     [
            //         'infoUserCours' => $infoUserCours,
            //         'boolean' => $boolean
            //     ]
            // );
        } else {
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);



            $Code_AgenceService_Sage = $this->DomModel->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->DomModel->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
            $CodeServiceofCours = $this->conversionTabCaractere($CodeServiceofCours);

            var_dump($CodeServiceofCours);
            die();

            /*$this->twig->display(
                'badm/formBadm.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'CodeServiceofCours' => $CodeServiceofCours
                ]
            );*/
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
