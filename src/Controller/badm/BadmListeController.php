<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Model\badm\BadmRechercheModel;

class BadmListeController extends Controller
{

    protected $badmRech;

    public function __construct()
    {
        parent::__construct();
        $this->badmRech = new BadmRechercheModel();
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

        $this->twig->display(
            'badm/listBadm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'typeMouvement' => $typeMouvement
            ]
        );
    }

    public function envoiListJsonBadm()
    {


        // Pagination
        $itemsPerPage = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $itemsPerPage;


        $badmJson = $this->badmRech->RechercheBadmModelAll((int)$offset, (int)$itemsPerPage);
        $totalRows = $this->badmRech->recupNombreLigne();

        $response = [
            'page' => $page,
            'per_page' => $itemsPerPage,
            'total' => $totalRows,
            'total_pages' => ceil($totalRows / $itemsPerPage),
            'data' => $badmJson
        ];

        header("Content-type:application/json");

        $jsonData = json_encode($response);


        $this->testJson($jsonData);
    }
}
