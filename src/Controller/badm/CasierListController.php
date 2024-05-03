<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;

class CasierListController extends Controller
{

    use Transformation;

    public function AffichageListeCasier()
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $nombreLigne = $this->casierList->NombreDeLigne();
        if (!$nombreLigne) {
            $nombreLigne = 0;
        }
        $agence = $this->transformEnSeulTableau($this->casierList->recupAgence());


        if (isset($_GET) || empty($_GET)) {
            $casier = $this->casierList->recuperToutesCasier();
        } else {
            $agence = $_GET['agence'];
            $casier = $_GET['casier'];
            var_dump($agence, $casier);
            $casier = $this->casierList->recuperToutesCasier($agence, $casier);
        }

        $this->twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'casier' => $casier,
                'agence' => $agence,
                'nombreLigne' => $nombreLigne
            ]
        );
    }
}
