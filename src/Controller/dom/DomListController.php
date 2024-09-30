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
        

        
        $this->SessionStart();

        if(!isset($_SESSION['recherche'])){
            $_SESSION['recherche'] = [];
        }

        if(!empty($request->query->all()) && !array_key_exists('page',$request->query->all()) && !array_key_exists('exportExcel',$request->query->all())){
            $_SESSION['recherche'] = $request->query->all();
        }

        $UserConnect = $_SESSION['user'];
        $infoUserCours = $this->profilModel->getINfoAllUserCours($UserConnect);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $limit = 10; // Nombre d'entrées à afficher par page
        $page =  !empty($request->query->get('page')) && $request->query->get('page') > 1 ? (int)$request->query->get('page') : 1;


        $statut = $this->transformEnSeulTableau($this->domList->getListStatut());

        $sousTypeDoc = $this->transformEnSeulTableau($this->domList->recupSousType());
     

        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'Hffintranet/src/Controller/UserAccessAll.txt';



        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            $array_decoded = $this->domList->RechercheModelAll($_SESSION['recherche'], (int)$page, (int)$limit);
            $resultat = $this->domList->getTotalRecordsAll($_SESSION['recherche']);
        } else {
            $array_decoded = $this->domList->RechercheModel($_SESSION['recherche'], (int)$page, (int)$limit, $UserConnect);
            $resultat = $this->domList->getTotalRecords($_SESSION['recherche'], $UserConnect);
        }

        $totalPages = ceil($resultat / $limit);


        self::$twig->display(
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
                'page' => $page,
                'valeur' => $request->query->all(),
                
            ]
        );
    }

   /**
    * @Route("/data-fetch", name="data_fetch")
    *
    * @return void
    */
    public function dataFetch()
    {
        $array_decoded = $this->domList->RechercheModelExcel($_SESSION['recherche'], $_SESSION['user']);
        header("Content-type:application/json");

        echo json_encode($array_decoded);
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
     * @Route("/annuler/{numDom}", name="domList_annulationStatut")
     */
    public function annulationStatutController($numDom)
    {

        $this->domList->annulationCodestatut($numDom);
        header('Location: /Hffintranet/listDomRech');
        exit();
    }
}
