<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


class DomDetailController extends Controller
{


    /**
     * Afficher les details du Numero_DOM selectionnne dans DetailDOM 
     * @Route("/detailDom/{numDom}/{id}", name="domDetail_detailDom") 
     */
    public function DetailDOM($numDom, $id)
    {
        $this->SessionStart();

        
            // $NumDom = $_GET['NumDom'];
            // $IdDom = $_GET['Id'];

            $detailDom = $this->detailModel->getDetailDOMselect($numDom, (int) $id);

            // var_dump($detailDom);
            // die();

            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);


            self::$twig->display(
                'dom/DetailDOM.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'detailDom' => $detailDom

                ]
            );
        
    }
}
