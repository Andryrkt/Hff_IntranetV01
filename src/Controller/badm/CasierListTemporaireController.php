<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;

class CasierListTemporaireController extends Controller
{
    use Transformation;

    public function AffichageListeCasier()
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $casier = $this->caiserListTemporaire->recuperToutesCasier();

        $nombreLigne = $this->caiserListTemporaire->NombreDeLigne();
        if (!$nombreLigne) {
            $nombreLigne = 0;
        }


        $this->twig->display(
            'badm/casier/listTemporaireCasier.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'casier' => $casier,
                'nombreLigne' => $nombreLigne
            ]
        );
    }

    public function tratitementBtnValide()
    {
        $CasierSeul = $this->caiserListTemporaire->recuperSeulCasier($_GET['id']);

        $casier = [
            'Agence' => $CasierSeul[0]['Agence_Rattacher'],
            'Casier' => $CasierSeul[0]['Casier'],
            'Nom_Session_Utilisateur' => $CasierSeul[0]['Nom_Session_Utilisateur'],
            'Date_Creation' => $CasierSeul[0]['Date_Creation'],
            'Numero_CAS' => $CasierSeul[0]['Numero_CAS']
        ];
        $this->caiserListTemporaire->insererDansBaseDeDonnees($casier);
        //$this->caiserListTemporaire->Delete($_GET['id']);
        header('Location: /Hffintranet/index.php?action=listTemporaireCasier');
        exit();
    }
}
