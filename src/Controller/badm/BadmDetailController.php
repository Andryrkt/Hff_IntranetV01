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

    public function envoiDetailBdmJson()
    {


        $badmDetailSqlServer = $this->badmDetail->DetailBadmModelAll();
        var_dump($badmDetailSqlServer);
        die();
        $agenceServiceDestinataire = $this->badmDetail->recupeAgenceServiceInformix($badmDetailSqlServer[0]['Agence_Service_Destinataire']);

        $tab = [
            "service" => $agenceServiceDestinataire[0]['service'],
            "casier" => $badmDetailSqlServer[0]['casier_Destinataire']
        ];

        header("Content-type:application/json");

        $jsonData = json_encode($tab);

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
