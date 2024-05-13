<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Controller\Traits\ConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class DomListController extends Controller
{

    use ConversionTrait;


    private function transformEnSeulTableau($tabs)
    {
        $tab = [];
        foreach ($tabs as  $values) {
            foreach ($values as $value) {
                $tab[] = $value;
            }
        }

        return $tab;
    }

    /**
     * affichage de l'architecture de la liste du DOM
     * @Route("/listDomRech", name="domList_ShowListDomRecherche")
     */
    public function ShowListDomRecherche(Request $request)
    {
        // dd($request);
        //dd($page);
        
        $this->SessionStart();

        $UserConnect = $_SESSION['user'];
        $infoUserCours = $this->profilModel->getINfoAllUserCours($UserConnect);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $limit = 10; // Nombre d'entrées à afficher par page
        $page =  2;


        $statut = $this->transformEnSeulTableau($this->domList->getListStatut());

        $sousTypeDoc = $this->transformEnSeulTableau($this->domList->recupSousType());
     
        $resultat = $this->domList->getTotalRecords($request->query->all());
        $totalPages = ceil($resultat / $limit);
        //dd($totalPages);

        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'Hffintranet/src/Controller/UserAccessAll.txt';


        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            $array_decoded = $this->domList->RechercheModelAll($request->query->all(), (int)$page, (int)$limit);
        } else {
            $array_decoded = $this->domList->RechercheModel($UserConnect);
        }



        $this->twig->display(
            'dom/ListDomRech.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'statut' => $statut,
                'sousTypeDoc' => $sousTypeDoc,
                'dom' => $array_decoded,
                'statut' =>$statut,
                'sousTypeDoc' => $sousTypeDoc,
                'totalPage' => $totalPages,
                'resultat' => $resultat,
                'page' => $page
            ]
        );
    }



    /**
     * 
     * cette fonction transforme le tableau statut en json 
     * pour listeDomRecherche
     * @Route("/listStatut", name="domList_listStatut")
     */
    public function listStatutController()
    {

        $statut = $this->domList->getListStatut();

        header("Content-type:application/json");

        echo json_encode($statut);
    }



    /**
     * 
     * cette fonction transforme le tableau en json 
     * pour listeDomRecherche
     * @Route("/recherche", name="domList_recherche")
     */
    public function rechercheController()
    {
        $this->SessionStart();

        $UserConnect = $_SESSION['user'];

        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'Hffintranet/src/Controller/UserAccessAll.txt';


        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            //$array_decoded = $this->domList->RechercheModelAll();
        } else {
            $array_decoded = $this->domList->RechercheModel($UserConnect);
        }


        header("Content-type:application/json");

        echo json_encode($array_decoded);
    }

    /**
     * boutton annuler pour pour change le code statut et id statut demande
     *
     * @return void
     */
    public function annulationController()
    {

        $this->domList->annulationCodestatut($_GET['NumDOM']);
        header('Location: /Hffintranet/index.php?action=ListDomRech');
        exit();
    }
}
