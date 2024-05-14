<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CasierListController extends Controller
{

    use Transformation;
    
/**
 * @Route("/listCasier/{page?1}", name="liste_affichageListeCasier")
 */
    public function AffichageListeCasier(Request $request , $page)
    {   
        //dd($request->request->all());

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $limit = 10;
        //$page = 1;
        $nombreLigne = $this->casierList->NombreDeLigne($request->request->all());
        $totalPage = ceil((int)$nombreLigne/$limit);

        $agence = $this->transformEnSeulTableau($this->casierList->recupAgence());

        $casier = $this->casierList->recuperToutesCasier($request->request->all(), (int)$page, (int)$limit);



        $this->twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'casier' => $casier,
                'agence' => $agence,
                'nombreLigne' => $nombreLigne,
                'page' => $page,
                'totalPage' => $totalPage
            ]
        );
    }

    /**
     * obtenir les donner de recherhce recupérer par js
     *
     * @return void
     */
    // public function obetuDonneeJson()
    // {
    //     // Assurez-vous que le contenu reçu est de type JSON
    //     $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    //     if ($contentType === "application/json") {
    //         // Recevoir le corps de la requête brute
    //         $content = trim(file_get_contents("php://input"));

    //         // Décoder le JSON reçu
    //         $decoded = json_decode($content, true);

    //         // Vérifier si le décodage a réussi
    //         if (is_array($decoded)) {
    //             //$response = "Vous avez saisi : " . htmlspecialchars($decoded['data1']) . " et " . htmlspecialchars($decoded['data2']);
    //             $tab = [
    //                 "agence" => $decoded['agence'],
    //                 "casier" => $decoded['casier']
    //             ];
    //             $this->SessionStart();
    //             $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    //             $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    //             $text = file_get_contents($fichier);
    //             $boolean = strpos($text, $_SESSION['user']);

    //             $nombreLigne = $this->casierList->NombreDeLigne();
    //             if (!$nombreLigne) {
    //                 $nombreLigne = 0;
    //             }
    //             $agence = $this->transformEnSeulTableau($this->casierList->recupAgence());
    //             $casier = $this->casierList->recuperToutesCasier($tab['agence'], $tab['casier']);
    //             $this->twig->display(
    //                 'badm/casier/listCasier.html.twig',
    //                 [
    //                     'infoUserCours' => $infoUserCours,
    //                     'boolean' => $boolean,
    //                     'casier' => $casier,
    //                     'agence' => $agence,
    //                     'nombreLigne' => $nombreLigne
    //                 ]
    //             );
    //         } else {
    //             // Gestion d'erreur si nécessaire
    //             echo 'Erreur dans le décodage de JSON';
    //         }
    //     } else {
    //         echo 'Content-Type non supporté';
    //     }
    // }
}
