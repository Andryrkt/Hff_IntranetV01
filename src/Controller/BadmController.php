<?php

namespace App\Controller;

class BadmController extends Controller
{

    private function alertRedirection(string $message, string $chemin = "/Hffintranet/index.php?action=formBadm")
    {
        echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
    }

    private function changeEtatAchat($dataEtatAchat)
    {
        if ($dataEtatAchat === 'N') {
            return 'Neuf';
        } else {
            return 'Occasion';
        }
    }


    public function formBadm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //var_dump($_POST);

            // var_dump($this->badm->findAll());
            //die();
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $data = $this->badm->findAll($_POST['idMateriel'],  $_POST['numeroParc'], $_POST['numeroSerie']);
            $agences = $this->badm->recupAgence();

            $dateDemande = $this->getDatesystem();

            // if (explode('-', $data[0]['service'])[0] === 'COMM ENERGIE') {

            //     $agenceEmetteur = $data[0]['agence'] . ' ' . 'COMM.ENERGIE';
            // } else {
            $agenceEmetteur = $data[0]['agence'] . ' ' . explode('-', $data[0]['service'])[0];
            //}

            $serviceEmetteur = trim($data[0]['code_service'] . ' ' . explode('-', $data[0]['service'])[1]);

            $coutAcquisition = $data[0]['droits_taxe'];
            $vnc = $coutAcquisition - $data[0]['amortissement'];

            $agenceDestinataire = [];
            foreach ($agences as  $value) {

                // if (trim($value['asuc_lib']) == 'PNEUMATIQUE-OUTILLAGE-LUBRIF') {
                //     $value['asuc_lib'] = 'PNEU - OUTIL - LUB';
                // }
                // if (trim($value['asuc_lib']) == 'COMM ENERGIE') {
                //     $value['asuc_lib'] = 'COMM.ENERGIE';
                // }
                $agenceDestinataire[] = trim($value['asuc_num'] . ' ' . $value['asuc_lib']);
            }


            $etatAchat = $this->ChangeEtatAchat($data[0]['mmat_nouo']);

            // var_dump($data);
            // var_dump($_POST);
            // var_dump($_POST['typeMission'] === 'ENTREE EN PARC' && $data[0]['code_affect'] !== 'VTE');
            // die('ENTREE en PARC');


            if ($_POST['idMateriel'] === '' &&  $_POST['numeroParc'] === '' && $_POST['numeroSerie'] === '') {
                $message = " Renseigner l\'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->alertRedirection($message);
            } elseif (empty($data)) {
                $message = "Ce matériel peut être dejà vendu";
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'ENTREE EN PARC' && $data[0]['code_affect'] !== 'VTE') {
                $message = 'ENTREE en PARC';
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'CHANGEMENT AGENCE/SERVICE' && $data[0]['code_affect'] === 'VTE') {
                $message = 'CHANGEMENT AGENCE SERVICE';
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'CESSION D\'ACTIF' && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM') {
                $message = 'CESSION ACTIF';
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'MISE AU REBUT' && $data[0]['code_affect'] === 'CAS') {
                $message = 'MISE AU REBUT';
                $this->alertRedirection($message);
            } else {


                $this->twig->display(
                    'badm/formCompleBadm.html.twig',
                    [
                        'codeMouvement' => $_POST['typeMission'],
                        'infoUserCours' => $infoUserCours,
                        'boolean' => $boolean,
                        'dateDemande' => $dateDemande,
                        'items' => $data,
                        'agenceEmetteur' => $agenceEmetteur,
                        'serviceEmetteur' => $serviceEmetteur,
                        'coutAcquisition' => $coutAcquisition,
                        'vnc' => $vnc,
                        'agenceDestinataire' => $agenceDestinataire,
                        'etatAchat' => $etatAchat

                    ]
                );
            }
        } else {



            $this->SessionStart();
            $UserConnect = $_SESSION['user'];
            $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);


            $Code_AgenceService_Sage = $this->DomModel->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->DomModel->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
            $PersonelServOfCours = $this->DomModel->getInfoUserMservice($_SESSION['user']);

            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $typeMouvements = $this->badm->recupTypeMouvement();

            $typeMouvement = [];
            foreach ($typeMouvements as  $values) {
                foreach ($values as $value) {
                    $typeMouvement[] = $value;
                }
            };


            $this->twig->display(
                'badm/formBadm.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'CodeServiceofCours' => $CodeServiceofCours,
                    'typeMouvement' => $typeMouvement
                ]
            );
        }
    }


    public function serviceDestinataire()
    {
        $serviceDestinataires = $this->badm->recupeServiceDestinataire();
        //var_dump($serviceDestinataires);
        $nouveauTableau = [];
        foreach ($serviceDestinataires as  $serviceDestinataire) {
            $agenceDestinataire = $serviceDestinataire['agence'] . ' ' . explode('-', $serviceDestinataire['service'])[0];
            $serviceDestinataire = trim($serviceDestinataire['code_service'] . ' ' . explode('-', $serviceDestinataire['service'])[1]);
            if (!isset($nouveauTableau[$agenceDestinataire])) {
                $nouveauTableau[$agenceDestinataire] = [];
            }
            $nouveauTableau[$agenceDestinataire][] = $serviceDestinataire;
        }
        //var_dump($nouveauTableau);
        header("Content-type:application/json");

        $jsonData = json_encode($nouveauTableau);



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

    public function casierDestinataire()
    {
        $casierDestinataire = $this->badm->recupeCasierDestinataire();

        $nouveauTableau = [];

        foreach ($casierDestinataire as $element) {

            // if ($element['agence'] === '90 COMM ENERGIE') {
            //     $agence = '90 COMM.ENERGIE';
            // } else {
            $agence = $element['agence'];
            //}
            $casier = $element['casier'];

            if (!isset($nouveauTableau[$agence])) {


                $nouveauTableau[$agence] = array();
            }

            $nouveauTableau[$agence][] = $casier;
        }



        header("Content-type:application/json");

        $jsonData = json_encode($nouveauTableau);



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


    private function formatNumber($number)
    {

        // Convertit le nombre en chaîne de caractères pour manipulation
        $numberStr = (string)$number;
        $numberStr = str_replace('.', ',', $numberStr);
        // Sépare la partie entière et la partie décimale
        if (strpos($numberStr, ',') !== false) {
            list($intPart, $decPart) = explode(',', $numberStr);
        } else {
            $intPart = $numberStr;
            $decPart = '';
        }

        // Convertit la partie entière en float pour éviter l'avertissement
        $intPart = floatval(str_replace('.', '', $intPart));

        // Formate la partie entière avec des points pour les milliers
        $intPartWithDots = number_format($intPart, 0, ',', '.');

        // Réassemble le nombre
        if ($decPart !== '') {
            return $intPartWithDots . ',' . $decPart;
        } else {
            return $intPartWithDots;
        }
    }




    private function convertirEnUtf8($element)
    {
        if (is_array($element)) {
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertirEnUtf8($value);
            }
        } elseif (is_string($element)) {
            return mb_convert_encoding($element, 'UTF-8', 'ISO-8859-1');
        }
        return $element;
    }

    public function formCompleBadm()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->SessionStart();
            //var_dump($_POST);
            //die();
            //var_dump($this->badm->findAll());
            $data = $this->badm->findAll($_POST['idMateriel']);

            var_dump($data);
            die();
            $NumBDM = $this->autoINcriment('BDM');
            $heureDemande = $this->getTime();
            $dateDemande = $this->getDatesystem();

            //var_dump('161');
            //echo $dateObject->format('Y-m-d'); // Affiche '2024-04-04'

            $MailUser = $this->DomModel->getmailUserConnect($_SESSION['user']);

            $agenceEmetteur = $data[0]['agence'];
            $serviceEmetteur = $data[0]['code_service'];
            $agenceServiceEmetteur = $agenceEmetteur . '-' . $serviceEmetteur;

            $agenceDestinataire = explode(' ', $_POST['agenceDestinataire'])[0];
            $serviceDestinataire = explode(' ', $_POST['serviceDestinataire'])[0];
            $agenceServiceDestinataire = $agenceDestinataire . '-' . $serviceDestinataire;

            $casierDestinataire = $_POST['casierDestinataire'];


            $coutAcquisition = $data[0]['droits_taxe'];
            $vnc = $coutAcquisition - $data[0]['amortissement'];
            //var_dump($_POST);

            $etatAchat = $this->ChangeEtatAchat($data[0]['mmat_nouo']);

            $codeMouvement = $_POST['codeMouvement'];


            $insertDbBadm = [
                'Numero_Demande_BADM' => $NumBDM,
                'Code_Mouvement' => $codeMouvement,
                'ID_Materiel' => (int)$data[0]['num_matricule'],
                'Nom_Session_Utilisateur' => $_SESSION['user'],
                'Date_Demande' => $dateDemande,
                'Heure_Demande' => $heureDemande,

                'Agence_Service_Emetteur' => $agenceServiceEmetteur,
                'Casier_Emetteur' => $data[0]['casier_emetteur'],
                'Agence_Service_Destinataire' => $agenceServiceDestinataire,
                'Casier_Destinataire' => $casierDestinataire,
                'Motif_Arret_Materiel' => $_POST['motifArretMateriel'],
                'Etat_Achat' => $etatAchat,
                'Date_Mise_Location' => $_POST['dateMiseLocation'],
                'Cout_Acquisition' => (float)$coutAcquisition,
                'Amortissement' => (float)$data[0]['amortissement'],
                'Valeur_Net_Comptable' => (float)$vnc,
                'Nom_Client'  => $_POST['nomClient'],
                'Modalite_Paiement'  => $_POST['modalitePaiement'],
                'Prix_Vente_HT'  => (float)$_POST['prixHt'],
                'Motif_Mise_Rebut'  => $_POST['motifMiseRebut'],
                'Heure_machine'  => (int)$data[0]['heure'],
                'KM_machine'  => (int)$data[0]['km'],
                'Code_Statut' => 'OUV'
            ];
            foreach ($insertDbBadm as $cle => $valeur) {
                $insertDbBadm[$cle] = strtoupper($valeur);
            }
            //var_dump($insertDbBadm);
            // die();






            $generPdfBadm = [
                'typeMouvement' => $codeMouvement,
                'Num_BDM' => $NumBDM,
                'Date_Demande' => implode('/', array_reverse(explode('-', $dateDemande))),
                'Designation' => $data[0]['designation'],
                'Num_ID' => $data[0]['num_matricule'],
                'Num_Serie' => $data[0]['num_serie'],
                'Groupe' => $data[0]['famille'],
                'Num_Parc' => $data[0]['num_parc'],
                'Affectation' => $data[0]['affectation'],
                'Constructeur' => $data[0]['constructeur'],
                'Date_Achat' => implode('/', array_reverse(explode('-', $data[0]['date_achat']))),
                'Annee_Model' => $data[0]['annee'],
                'Modele' => $data[0]['modele'],
                'Agence_Service_Emetteur' => $agenceServiceEmetteur,
                'Casier_Emetteur' => $data[0]['casier_emetteur'],
                'Agence_Service_Destinataire' => $agenceServiceDestinataire,
                'Casier_Destinataire' => $casierDestinataire,
                'Motif_Arret_Materiel' => $_POST['motifArretMateriel'],
                'Etat_Achat' => $etatAchat,
                'Date_Mise_Location' => implode('/', array_reverse(explode('-', $_POST['dateMiseLocation']))),
                'Cout_Acquisition' => $this->formatNumber($coutAcquisition),
                'Amort' => $this->formatNumber($data[0]['amortissement']),
                'VNC' => $this->formatNumber($vnc),
                'Nom_Client' => $_POST['nomClient'],
                'Modalite_Paiement' => $_POST['modalitePaiement'],
                'Prix_HT' => $this->formatNumber($_POST['prixHt']),
                'Motif_Mise_Rebut' => $_POST['motifMiseRebut'],
                'Heures_Machine' => $this->formatNumber($data[0]['heure']),
                'Kilometrage' => $this->formatNumber($data[0]['km']),
                'Email_Emetteur' => $MailUser,
                'Agence_Service_Emetteur_Non_separer' => $agenceEmetteur . $serviceEmetteur
            ];
            $conditionAgenceService = ($_POST['agenceDestinataire'] === '' && $_POST['serviceDestinataire'] === '') || $agenceEmetteur === $agenceDestinataire || $serviceEmetteur === $serviceDestinataire;
            $conditionVide = $_POST['agenceDestinataire'] === '' && $_POST['serviceDestinataire'] === '' && $_POST['casierDestinataire'] === '' && $_POST['dateMiseLocation'] === '';
            if (($codeMouvement === 'ENTREE EN PARC' || $codeMouvement === 'CHANGEMENT AGENCE/SERVICE') && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'CHANGEMENT AGENCE/SERVICE' && $conditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } else {
                $insertDbBadm = $this->convertirEnUtf8($insertDbBadm);
                $this->badm->insererDansBaseDeDonnees($insertDbBadm);
                $this->genererPdf->genererPdfBadm($generPdfBadm);
                $this->genererPdf->copyInterneToDOXCUWARE($NumBDM, $agenceEmetteur . $serviceEmetteur);
            }
        }
    }


    public function AffichageListeBadm()
    {
        $this->SessionStart();

        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $typeMouvements = $this->badm->recupTypeMouvement();

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
        $badmJson = $this->badm->RechercheBadmModelAll();

        header("Content-type:application/json");

        $jsonData = json_encode($badmJson);



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



// echo json_encode($tab);

            








              // $jsonsata = file_get_contents("php://input");
            // $data = json_decode($jsonsata, true);

            // var_dump($data);

            // if (!empty($data)) {
            //     $tab = [
            //         "message" => $jsonsata
            //     ];
            // } else {
            //     $tab = [
            //         "message" => 'zero données'
            //     ];
            // }
            // echo json_encode($tab);