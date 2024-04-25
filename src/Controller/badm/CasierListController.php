<?php

namespace App\Controller\badm;

use App\Controller\Controller;

class CasierListController extends Controller
{
    public function AffichageListeCasier()
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
        $casier = $this->casierList->recuperToutesCasier();



        $this->twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'casier' => $casier
            ]
        );
    }
}
