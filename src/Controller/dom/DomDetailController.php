<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Model\dom\DomDetailModel;

class DomDetailController extends Controller
{
    private $detailModel;

    public function __construct()
    {
        parent::__construct();
        $this->detailModel = new DomDetailModel();
    }
    /**
     * Afficher les details du Numero_DOM selectionnne dans DetailDOM  
     */
    public function DetailDOM()
    {
        $this->SessionStart();

        if (isset($_GET['NumDom'])) {
            $NumDom = $_GET['NumDom'];
            $IdDom = $_GET['Id'];

            $detailDom = $this->detailModel->getDetailDOMselect($NumDom, (int) $IdDom);

            // var_dump($detailDom);
            // die();

            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);


            $this->twig->display(
                'dom/DetailDOM.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'detailDom' => $detailDom

                ]
            );
        }
    }
}
