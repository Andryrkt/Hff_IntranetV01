<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


class BadmListeController extends Controller
{
    /**
     * @Route("/listBadm", name="badmListe_AffichageListeBadm")
     */
    public function AffichageListeBadm()
    {
        $this->SessionStart();

        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $typeMouvements = $this->badmRech->recupTypeMouvement();

        $typeMouvement = [];
        foreach ($typeMouvements as  $values) {
            foreach ($values as $value) {
                $typeMouvement[] = $value;
            }
        };

        self::$twig->display(
            'badm/listBadm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'typeMouvement' => $typeMouvement
            ]
        );
    }

    /**
     * @Route("/ListJsonBadm")
     */
    public function envoiListJsonBadm()
    {
        $this->SessionStart();

        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        if ($boolean) {
            $badmJson = $this->badmRech->RechercheBadmModelAll();
        } else {
            $badmJson = $this->badmRech->RechercheBadmMode($_SESSION['user']);
        }


        header("Content-type:application/json");

        $jsonData = json_encode($badmJson);


        $this->testJson($jsonData);
    }


    private function testJson($jsonData)
    {
        if ($jsonData === false) {
            // L'encodage a échoué, vérifions pourquoi
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    echo 'Aucune erreur';
                    break;
                case JSON_ERROR_DEPTH:
                    echo 'Profondeur maximale atteinte';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo 'Inadéquation des états ou mode invalide';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    echo 'Caractère de contrôle inattendu trouvé';
                    break;
                case JSON_ERROR_SYNTAX:
                    echo 'Erreur de syntaxe, JSON malformé';
                    break;
                case JSON_ERROR_UTF8:
                    echo 'Caractères UTF-8 malformés, possiblement mal encodés';
                    break;
                default:
                    echo 'Erreur inconnue';
                    break;
            }
        } else {
            // L'encodage a réussi
            echo $jsonData;
        }
    }
}
