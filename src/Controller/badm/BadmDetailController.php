<?php

namespace App\Controller\badm;

use App\Controller\Controller;


class BadmDetailController extends Controller
{


    private function rendreSeultableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as $values) {
            foreach ($values as $value) {
                $tab[] = $value;
            }
        }
        return $tab;
    }

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
        $agenceDestinataire = $this->rendreSeultableau($agence);
        //$codeAgence = substr(trim($agenceServiceDestinataire[0]['agence']), 0, 2);
        // $service = $this->badmDetail->recupService($codeAgence);
        // $serviceDestinataire = $this->rendreSeultableau($service);
        // var_dump($serviceDestinataire);
        // die();
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
                'agenceDestinataire' => $agenceDestinataire,
                'numBdm' => $NumBDM
            ]
        );
    }
}
