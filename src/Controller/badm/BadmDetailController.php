<?php

namespace App\Controller\badm;

use App\Controller\Controller;


class BadmDetailController extends Controller
{



    public function detailBadm()
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
        $NumBDM = $_GET['NumBDM'];
        $id = $_GET['Id'];

        $badmDetailSqlServer = $this->badmDetail->DetailBadmModelAll($NumBDM, $id);
        $badmDetailInformix = $this->badmDetail->findAll($badmDetailSqlServer[0]['ID_Materiel']);
        $agenceServiceEmetteur = $this->badmDetail->recupeAgenceServiceInformix($badmDetailSqlServer[0]['Agence_Service_Emetteur']);
        $agenceServiceDestinataire = $this->badmDetail->recupeAgenceServiceInformix($badmDetailSqlServer[0]['Agence_Service_Destinataire']);
        $agence = $this->badmDetail->recupAgence();
        $agenceDestinataire = [];
        foreach ($agence as $values) {
            foreach ($values as $value) {
                $agenceDestinataire[] = $value;
            }
        }
        // $agenceDestinataire = $badmDetailSqlServer['agencedestinataire'] .' '.$badmDetailInformix[''];
        // var_dump($badmDetailSqlServer, $badmDetailInformix);

        // die();
        $this->twig->display(
            'badm/formCompleBadm.html.twig',
            [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'Server' => $badmDetailSqlServer,
                'Informix' => $badmDetailInformix,
                'detail' => 'disabled',
                'Emetteur' => $agenceServiceEmetteur,
                'Destinataire' => $agenceServiceDestinataire,
                'agenceDestinataire' => $agenceDestinataire

            ]
        );
    }
}
