<?php

namespace App\Controller;

class BadmController extends Controller
{
    public function formBadm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //var_dump($_POST);

            // var_dump($this->badm->findAll());
            // die();



            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
            $data = $this->badm->findAll($_POST['idMateriel'],  $_POST['numeroParc'], $_POST['numeroSerie']);


            if ($_POST['idMateriel'] === '' &&  $_POST['numeroParc'] === '' && $_POST['numeroSerie'] === '') {
                $message = "il faut renseigner l\'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $chemin = "/Hffintranet/index.php?action=formBadm";
                echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
            } elseif (empty($data)) {
                $message = "Ce matériel peut être dejà vendu";
                $chemin = "/Hffintranet/index.php?action=formBadm";
                echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
            } else {

                // var_dump($data);
                // die();
                $dateDemande = $this->getDatesystem();
                $agenceEmetteur = $data[0]['agence'] . ' ' . explode('-', $data[0]['service'])[0];
                // 
                $serviceEmetteur = substr(explode('-', $data[0]['service'])[1], 0, 3) . ' ' . explode('-', $data[0]['service'])[1];
                $coutAcquisition = $data[0]['charge_entretien'];
                $vnc = $coutAcquisition - $data[0]['amortissement'];
                $agences = $this->badm->recupAgence();
                $agenceDestinataire = [];
                foreach ($agences as  $value) {
                    $agenceDestinataire[] = trim($value['asuc_num'] . ' ' . $value['asuc_lib']);
                }
                // var_dump($agenceDestinataire);

                // die();
            }

            //var_dump($data[0]);
            // die();

            //$amortissement = $this->badm->amortissement();
            //var_dump($amortissement);
            // $heurekilometreMachine = $this->badm->recupheureKilomettreMachine();
            // var_dump($heurekilometreMachine);

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
                    'agenceDestinataire' => $agenceDestinataire
                ]
            );
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



            $this->twig->display(
                'badm/formBadm.html.twig',
                [
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'CodeServiceofCours' => $CodeServiceofCours,
                ]
            );
        }
    }

    // public function envoiDonnerFiltrerInformix()
    // {


    //     $data1 = $this->badm->recuperationCaracterMaterielAll();
    //     // Debug: vérifier si $data1 contient des données
    //     error_log(print_r($data1, true)); // ou utilisez var_dump($data1);

    //     header("Content-Type: application/json");
    //     $json = json_encode($data1);

    //     // Debug: vérifier si json_encode renvoie une erreur
    //     error_log(json_last_error_msg());

    //     echo $json;
    // }

    // public function showFormCompleBadm()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         var_dump($_POST);
    //         $dateDemande = $this->getDatesystem();

    //         $this->SessionStart();
    //         $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    //         $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    //         $text = file_get_contents($fichier);
    //         $boolean = strpos($text, $_SESSION['user']);


    //         $this->twig->display(
    //             'badm/formCompleBadm.html.twig',
    //             [
    //                 'codeMouvement' => $_POST['typeMission'],
    //                 'infoUserCours' => $infoUserCours,
    //                 'boolean' => $boolean,
    //                 'dateDemande' => $dateDemande
    //             ]
    //         );
    //     }
    // }

    public function formCompleBadm()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->SessionStart();
            //var_dump($_POST);
            //var_dump($this->badm->findAll());
            $data = $this->badm->findAll($_POST['idMateriel']);

            //var_dump($data);


            $NumBDM = $this->autoINcriment('BDM');
            $heureDemande = $this->getTime();
            $dateDemande = $this->getDatesystem();

            //var_dump('161');
            //echo $dateObject->format('Y-m-d'); // Affiche '2024-04-04'

            $MailUser = $this->DomModel->getmailUserConnect($_SESSION['user']);

            $agenceEmetteur = $data[0]['agence'];

            $serviceEmetteur = substr(explode('-', $data[0]['service'])[1], 0, 3);


            //$serviceEmetteur = str_split(explode('-', $data[0]['service'])[1], 3);
            $agenceServiceEmetteur = $agenceEmetteur . '-' . $serviceEmetteur;

            $agenceDestinataire = explode(' ', $_POST['agenceDestinataire'])[0];
            $serviceDestinataire = explode(' ', $_POST['serviceDestinataire'])[0];
            $agenceServiceDestinataire = $agenceDestinataire . '-' . $serviceDestinataire;

            $casierDestinataireAgence = $_POST['casierDestinataireAgence'];
            $casierDestinataireChantier = $_POST['casierDestinataireChantier'];
            $casierDestinataireStd = $_POST['casierDestinataireStd'];
            $casierDestinataire = $casierDestinataireAgence . ' ' . $casierDestinataireChantier . ' ' . $casierDestinataireStd;


            $coutAcquisition = 0;
            $vnc = $coutAcquisition - $data[0]['amortissement'];
            //var_dump($_POST);

            $insertDbBadm = [
                'Numero_Demande_BADM' => $NumBDM,
                'Code_Mouvement' => $_POST['codeMouvement'],
                'ID_Materiel' => (int)$data[0]['num_matricule'],
                'Nom_Session_Utilisateur' => $_SESSION['user'],
                'Date_Demande' => $dateDemande,
                'Heure_Demande' => $heureDemande,

                'Agence_Service_Emetteur' => $agenceServiceEmetteur,
                'Casier_Emetteur' => $data[0]['casier_emetteur'],
                'Agence_Service_Destinataire' => $agenceServiceDestinataire,
                'Casier_Destinataire' => $casierDestinataire,
                'Motif_Arret_Materiel' => $_POST['motifArretMateriel'],
                'Etat_Achat' => $data[0]['mmat_nouo'],
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
            $this->badm->insererDansBaseDeDonnees($insertDbBadm);

            $generPdfBadm = [
                'Num_BDM' => $NumBDM,
                'Date_Demande' => implode('/', array_reverse(explode('-', $dateDemande))),
                'Designation' => $data[0]['designation'],
                'Num_ID' => $data[0]['num_matricule'],
                'Num_Serie' => $data[0]['num_serie'],
                'Groupe' => $data[0]['groupe2'],
                'Num_Parc' => $data[0]['num_parc'],
                'Constructeur' => $data[0]['constructeur'],
                'Date_Achat' => implode('/', array_reverse(explode('-', $data[0]['date_achat']))),
                'Annee_Model' => $data[0]['annee'],
                'Modele' => $data[0]['modele'],
                'Agence_Service_Emetteur' => $agenceServiceEmetteur,
                'Casier_Emetteur' => $data[0]['casier_emetteur'],
                'Agence_Service_Destinataire' => $agenceServiceDestinataire,
                'Casier_Destinataire' => $casierDestinataire,
                'Motif_Arret_Materiel' => $_POST['motifArretMateriel'],
                'Etat_Achat' => $data[0]['mmat_nouo'],
                'Date_Mise_Location' => implode('/', array_reverse(explode('-', $_POST['dateMiseLocation']))),
                'Cout_Acquisition' => $coutAcquisition,
                'Amort' => $data[0]['amortissement'],
                'VNC' => $vnc,
                'Nom_Client' => $_POST['nomClient'],
                'Modalite_Paiement' => $_POST['modalitePaiement'],
                'Prix_HT' => $_POST['prixHt'],
                'Motif_Mise_Rebut' => $_POST['motifMiseRebut'],
                'Heures_Machine' => $data[0]['heure'],
                'Kilometrage' => $data[0]['km'],
                'Email_Emetteur' => $MailUser,
                'Agence_Service_Emetteur_Non_separer' => $agenceEmetteur . $serviceEmetteur
            ];
            $this->genererPdf->genererPdfBadm($generPdfBadm);
            $this->genererPdf->copyInterneToDOXCUWARE($NumBDM, $agenceEmetteur . $serviceEmetteur);
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