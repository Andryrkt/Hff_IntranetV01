<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Controller\Traits\ConversionTrait;
use App\Model\dom\DomListModel;

class DomListController extends Controller
{
    private $domList;

    public function __construct()
    {
        parent::__construct();
        $this->domList = new DomListModel();
    }

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
     */
    public function ShowListDomRecherche()
    {
        $this->SessionStart();


        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);


        $statut = $this->domList->getListStatut();

        $sousType = $this->domList->recupSousType();
        $sousTypeDoc = $this->transformEnSeulTableau($sousType);


        $this->twig->display(
            'dom/ListDomRech.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'statut' => $statut,
                'sousTypeDoc' => $sousTypeDoc
            ]
        );
    }



    /**
     * @Andryrkt 
     * cette fonction transforme le tableau statut en json 
     * pour listeDomRecherche
     */
    public function listStatutController()
    {

        $statut = $this->domList->getListStatut();

        header("Content-type:application/json");

        echo json_encode($statut);
    }



    /**
     * @Andryrkt 
     * cette fonction transforme le tableau en json 
     * pour listeDomRecherche
     */
    public function rechercheController()
    {
        $this->SessionStart();

        $UserConnect = $_SESSION['user'];

        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'Hffintranet/src/Controller/UserAccessAll.txt';


        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            $array_decoded = $this->domList->RechercheModelAll();
        } else {
            $array_decoded = $this->domList->RechercheModel($UserConnect);
        }


        header("Content-type:application/json");

        echo json_encode($array_decoded);
    }
}
