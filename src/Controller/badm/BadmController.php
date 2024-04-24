<?php


namespace App\Controller\badm;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




use App\Controller\Controller;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\Transformation;

class BadmController extends Controller
{

    use Transformation;
    use ConversionTrait;

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
            // var_dump($_POST);
            // die();

            // var_dump($this->badm->findAll());
            //die();
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $data = $this->badm->findAll($_POST['idMateriel'],  $_POST['numeroParc'], $_POST['numeroSerie']);

            //var_dump($data);
            // $agences = $this->badm->recupAgence();

            $dateDemande = $this->getDatesystem();


            $agence = $this->badm->recupAgence();
            $agenceDestinataire = [];
            foreach ($agence as $values) {
                foreach ($values as $value) {
                    $agenceDestinataire[] = $value;
                }
            }
            // var_dump($data);
            // die();


            $etatAchat = $this->ChangeEtatAchat($data[0]['mmat_nouo']);

            if ($data[0]['code_affect'] === 'LCD') {
                $dateMiseLocation = $data[0]['date_location'];
            } else {
                $dateMiseLocation = '';
            }



            // var_dump($data);
            // var_dump($_POST);
            // var_dump($_POST['typeMission'] === 'ENTREE EN PARC' && $data[0]['code_affect'] !== 'VTE');
            // die('ENTREE en PARC');


            if ($_POST['idMateriel'] === '' &&  $_POST['numeroParc'] === '' && $_POST['numeroSerie'] === '') {
                $message = " Renseigner l\'un des champs (Id Matériel, numéro Série et numéro Parc)";
                $this->alertRedirection($message);
            } elseif (empty($data)) {
                $message = "Matériel déjà vendu";
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'ENTREE EN PARC' && $data[0]['code_affect'] !== 'VTE') {
                $message = 'Ce matériel est déjà en PARC';
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'CHANGEMENT AGENCE/SERVICE' && $data[0]['code_affect'] === 'VTE') {
                $message = "L\'agence et le service associés à ce matériel ne peuvent pas être modifiés.";
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'CHANGEMENT AGENCE/SERVICE' && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM') {
                $message = " l\'affectation matériel ne permet pas cette opération";
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'CESSION D\'ACTIF' && $data[0]['code_affect'] !== 'LCD' && $data[0]['code_affect'] !== 'IMM') {
                $message = "Cession d\'actif ";
                $this->alertRedirection($message);
            } elseif ($_POST['typeMission'] === 'MISE AU REBUT' && $data[0]['code_affect'] === 'CAS') {
                $message = 'Ce matériel ne peut pas être mis au rebut';
                $this->alertRedirection($message);
            } else {

                // if(!in_array($codeAgenceService, $agenceServiceAutoriser)){

                //         $message = "vous n\'êtes pas autoriser à consulter ce matériel";
                //         $this->alertRedirection($message);
                // }

                $agenceEmetteur = $data[0]['agence'] . ' ' . explode('-', $data[0]['service'])[0];
                $serviceEmetteur = trim($data[0]['code_service'] . ' ' . explode('-', $data[0]['service'])[1]);

                $agenceServiceAutoriserbd = $this->badm->recupCodeAgenceServiceAutoriser($_SESSION['user']);

                $agenceServiceAutoriser = $this->transformEnSeulTableau($agenceServiceAutoriserbd);


                $codeAgenceService = $data[0]['agence'] . trim($data[0]['code_service']);


                $coutAcquisition = $data[0]['droits_taxe'];
                $vnc = $coutAcquisition - $data[0]['amortissement'];



                if ($boolean) {

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
                            'etatAchat' => $etatAchat,
                            'dateMiseLocation' => $dateMiseLocation
                        ]
                    );
                } else {


                    if (!in_array($codeAgenceService, $agenceServiceAutoriser)) {

                        $message = "vous n\'êtes pas autoriser à consulter ce matériel";
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
                                'etatAchat' => $etatAchat,
                                'dateMiseLocation' => $dateMiseLocation
                            ]
                        );
                    }
                }
            }
        } else {



            $this->SessionStart();

            $Code_AgenceService_Sage = $this->badm->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->badm->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);


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

            $agenceAutoriser = $this->badm->recupeSessionAutoriser($_SESSION['user']);
            if (empty($agenceAutoriser)) {
                $message = "verifiez votre Autorisation";
                $this->alertRedirection($message);
            } else {
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

    private function agenceCleServiceValeur($tab, $agences, $services)
    {
        $nouveauTableau = [];

        foreach ($tab as $element) {

            $agence = $element[$agences];

            $casier = $element[$services];

            if (!isset($nouveauTableau[$agence])) {

                $nouveauTableau[$agence] = array();
            }

            $nouveauTableau[$agence][] = $casier;
        }

        return $nouveauTableau;
    }

    public function serviceDestinataire()
    {
        $serviceDestinataires = $this->badm->recupeAgenceServiceDestinataire();

        $nouveauTableau = $this->agenceCleServiceValeur($serviceDestinataires, 'agence', 'service');

        //var_dump($nouveauTableau);
        header("Content-type:application/json");

        $jsonData = json_encode($nouveauTableau);

        $this->testJson($jsonData);
    }

    public function casierDestinataire()
    {
        $casierDestinataire = $this->badm->recupeCasierDestinataire();

        $nouveauTableau = $this->agenceCleServiceValeur($casierDestinataire, 'agence', 'casier');


        header("Content-type:application/json");

        $jsonData = json_encode($nouveauTableau);

        $this->testJson($jsonData);
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




    private function imageDansDossier($image, $imagename, string $chemin)
    {
        $target_dir = $chemin;  // Spécifiez le dossier où l'image sera enregistrée.
        //$image["name"] = $NumBDM . '_' . $agenceService . '.jpg';
        // var_dump($image["name"]);
        // die();
        $target_file = $target_dir . basename($imagename);
        $uploadOk = 1;
        $quality = 75;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Vérifier si le fichier image est une image réelle ou une fausse image
        if (isset($_POST["submit"])) {
            $check = getimagesize($image["tmp_name"]);
            if ($check !== false) {
                //echo "Le fichier est une image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                //echo "";
                $message = "Le fichier n'est pas une image.";
                $this->alertRedirection($message);
                $uploadOk = 0;
            }
        }

        // Vérifier si le fichier existe déjà
        if (file_exists($target_file)) {
            //echo "";
            $message = "Désolé, le fichier existe déjà.";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }

        // Vérifier la taille du fichier
        $taille = 1 * 1024 * 1024;
        if ($image["size"] > $taille) {  // Limite de taille de 300KB
            //echo "";
            $message = "Désolé, votre fichier est trop volumineux (>1MB).";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }

        // Autoriser certains formats de fichier
        if (
            $imageFileType != "jpg"  && $imageFileType != "jpeg" && $imageFileType != "png"

        ) {
            // echo "Désolé, seuls les fichiers JPG est autorisés.";
            $message = "Désolé, seuls les fichiers JPG, JPEG, et PNG sont autorisés.";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }

        // Vérifier si le fichier est une image
        $check = getimagesize($image['tmp_name']);
        if ($check !== false) {
            // Traiter selon le type de l'image
            switch ($imageFileType) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($image['tmp_name']);
                    imagejpeg($image, $target_file, $quality);
                    break;
                case 'png':
                    $image = imagecreatefrompng($image['tmp_name']);
                    // Convertir PNG en JPEG
                    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
                    imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                    imagealphablending($bg, TRUE);
                    imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                    imagedestroy($image);
                    imagejpeg($bg,  $target_file, $quality);
                    imagedestroy($bg);
                    break;
                default:
                    echo "Seuls les fichiers JPG, JPEG, et PNG sont autorisés.";
                    exit;
            }

            // Libérer la mémoire
            if (isset($image)) {
                imagedestroy($image);
            }

            echo 'Image compressée et sauvegardée avec succès.';
        } else {
            echo "Le fichier n'est pas une image.";
        }


        // Vérifier si $uploadOk est mis à 0 par une erreur
        if ($uploadOk == 0) {
            //echo "";
            $message = "Désolé, votre fichier n'a pas été téléchargé.";
            $this->alertRedirection($message);
            // si tout est correct, essayer de télécharger le fichier
        } else {
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                //echo "Le fichier " . htmlspecialchars(basename($image["name"])) . " a été téléchargé.";
            } else {
                //echo ;
                $message = "Désolé, il y a eu une erreur lors du téléchargement de votre fichier.";
                $this->alertRedirection($message);
            }
        }
    }

    public function formCompleBadm()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->SessionStart();
            // var_dump($_POST);
            // die();
            //var_dump($this->badm->findAll());
            $data = $this->badm->findAll($_POST['idMateriel']);
            $codeMouvement = $_POST['codeMouvement'];
            // var_dump($data);
            // die();
            $NumBDM = $this->autoINcriment('BDM');
            $heureDemande = $this->getTime();
            $dateDemande = $this->getDatesystem();

            //var_dump('161');
            //echo $dateObject->format('Y-m-d'); // Affiche '2024-04-04'

            $MailUser = $this->badm->getmailUserConnect($_SESSION['user']);

            if (isset($_POST['numParc'])) {
                $numParc = $_POST['numParc'];
            } else {
                $numParc = $data[0]['num_parc'];
            }


            $agenceEmetteur = $data[0]['agence'];
            $serviceEmetteur = $data[0]['code_service'];
            $agenceServiceEmetteur = $agenceEmetteur . $serviceEmetteur;
            $casierEmetteur = $data[0]['casier_emetteur'];


            if (isset($_POST['agenceDestinataire']) && isset($_POST['serviceDestinataire']) && isset($_POST['motifArretMateriel'])) {
                $agenceDestinataire = explode(' ', $_POST['agenceDestinataire'])[0];
                $serviceDestinataire = explode(' ', $_POST['serviceDestinataire'])[0];
                $motifArretMateriel = $_POST['motifArretMateriel'];
            } else if ($codeMouvement === 'CHANGEMENT DE CASIER' || $codeMouvement === 'MISE AU REBUT') {
                $agenceDestinataire = $agenceEmetteur;
                $serviceDestinataire = $serviceEmetteur;
                $motifArretMateriel = '';
            } else if ($codeMouvement === 'CESSION D\'ACTIF') {
                if ($agenceEmetteur === '90' || $agenceEmetteur === '91' || $agenceEmetteur === '92') {
                    $agenceDestinataire = '90';
                    $serviceDestinataire = 'COM';
                } else {
                    $agenceDestinataire = '01';
                    $serviceDestinataire = 'COM';
                }
                $motifArretMateriel = '';
            }
            $agenceServiceDestinataire = $agenceDestinataire . $serviceDestinataire;

            if (isset($_POST['casierDestinataire'])) {
                $casierDestinataire = $_POST['casierDestinataire'];
            } elseif ($codeMouvement === 'CESSION D\'ACTIF') {
                $casierDestinataire = "";
            } elseif ($codeMouvement === 'MISE AU REBUT') {
                $casierDestinataire = $casierEmetteur;
            }



            $coutAcquisition = $data[0]['droits_taxe'];
            $vnc = $coutAcquisition - $data[0]['amortissement'];
            //var_dump($_POST);

            $etatAchat = $this->ChangeEtatAchat($data[0]['mmat_nouo']);

            if (isset($_POST['dateMiseLocation'])) {
                $dateMiseLocation = $_POST['dateMiseLocation'];
            } else {
                $dateMiseLocation = $data[0]['date_location'];
            }

            if (isset($_POST['nomClient'])) {
                $nomClient = $_POST['nomClient'];
            } else {
                $nomClient = '';
            }

            if (isset($_POST['modalitePaiement'])) {
                $modalitePaiement = $_POST['modalitePaiement'];
            } else {
                $modalitePaiement = '';
            }

            if (isset($_POST['prixHt'])) {
                $prixHt = (float)$_POST['prixHt'];
            } else {
                $prixHt = 0;
            }


            if (isset($_POST['motifMiseRebut'])) {
                $motifMiseRebut =  $_POST['motifMiseRebut'];
            } else {
                $motifMiseRebut = '';
            }

            if (isset($_FILES["imageRebut"])) {

                $nomAgenceServiceNonSeparer = $agenceEmetteur . $serviceEmetteur;
                $chemin = $_SERVER['DOCUMENT_ROOT'] . "/Hffintranet/Views/images/";
                $imagename = $NumBDM . '_' . $nomAgenceServiceNonSeparer . '.jpg';
                $this->imageDansDossier($_FILES['imageRebut'], $imagename, $chemin);
                $image = $_FILES['imageRebut']['name'];
            } else {
                $image = '';
            }

            $idTypeMouvement = $this->badm->recupIdtypeMouvemnet($codeMouvement);



            // var_dump($_FILES);
            // die();

            // var_dump($agenceDestinataire === '' && $serviceDestinataire === '' || $agenceServiceEmetteur === $agenceServiceDestinataire);
            // die();
            $conditionAgenceService = $agenceDestinataire === '' && $serviceDestinataire === '' || $agenceServiceEmetteur === $agenceServiceDestinataire;
            $conditionVide = $agenceDestinataire === '' && $serviceDestinataire === '' && $_POST['casierDestinataire'] === '' && $dateMiseLocation === '';
            if (($codeMouvement === 'ENTREE EN PARC' || $codeMouvement === 'CHANGEMENT AGENCE/SERVICE') && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'CHANGEMENT AGENCE/SERVICE' && $conditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } else {

                $insertDbBadm = [
                    'Numero_Demande_BADM' => $NumBDM,
                    'Code_Mouvement' => $idTypeMouvement['ID_Type_Mouvement'],
                    'ID_Materiel' => (int)$data[0]['num_matricule'],
                    'Nom_Session_Utilisateur' => $_SESSION['user'],
                    'Date_Demande' => $dateDemande,
                    'Heure_Demande' => $heureDemande,
                    'Agence_Service_Emetteur' => $agenceServiceEmetteur,
                    'Casier_Emetteur' => $casierEmetteur,
                    'Agence_Service_Destinataire' => $agenceServiceDestinataire,
                    'Casier_Destinataire' => $casierDestinataire,
                    'Motif_Arret_Materiel' => $motifArretMateriel,
                    'Etat_Achat' => $etatAchat,
                    'Date_Mise_Location' => $dateMiseLocation,
                    'Cout_Acquisition' => (float)$coutAcquisition,
                    'Amortissement' => (float)$data[0]['amortissement'],
                    'Valeur_Net_Comptable' => (float)$vnc,
                    'Nom_Client'  => $nomClient,
                    'Modalite_Paiement'  => $modalitePaiement,
                    'Prix_Vente_HT'  => (float)$prixHt,
                    'Motif_Mise_Rebut'  => $motifMiseRebut,
                    'Heure_machine'  => (int)$data[0]['heure'],
                    'KM_machine'  => (int)$data[0]['km'],
                    'Code_Statut' => 'OUV',
                    'Num_Parc' => $numParc
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
                    'Num_Parc' => $numParc,
                    'Affectation' => $data[0]['affectation'],
                    'Constructeur' => $data[0]['constructeur'],
                    'Date_Achat' => implode('/', array_reverse(explode('-', $data[0]['date_achat']))),
                    'Annee_Model' => $data[0]['annee'],
                    'Modele' => $data[0]['modele'],
                    'Agence_Service_Emetteur' => $agenceEmetteur . '-' . $serviceEmetteur,
                    'Casier_Emetteur' => $casierEmetteur,
                    'Agence_Service_Destinataire' => $agenceDestinataire . '-' . $serviceDestinataire,
                    'Casier_Destinataire' => $casierDestinataire,
                    'Motif_Arret_Materiel' => $motifArretMateriel,
                    'Etat_Achat' => $etatAchat,
                    'Date_Mise_Location' => $dateMiseLocation,
                    'Cout_Acquisition' => $this->formatNumber($coutAcquisition),
                    'Amort' => $this->formatNumber($data[0]['amortissement']),
                    'VNC' => $this->formatNumber($vnc),
                    'Nom_Client' => $nomClient,
                    'Modalite_Paiement' => $modalitePaiement,
                    'Prix_HT' => $this->formatNumber($prixHt),
                    'Motif_Mise_Rebut' => $motifMiseRebut,
                    'Heures_Machine' => $this->formatNumber($data[0]['heure']),
                    'Kilometrage' => $this->formatNumber($data[0]['km']),
                    'Email_Emetteur' => $MailUser,
                    'Agence_Service_Emetteur_Non_separer' => $agenceEmetteur . $serviceEmetteur,
                    'image' => $image
                ];
                // $generPdfBadm = $this->convertirEnUtf8($generPdfBadm);
                // var_dump($this->convertirEnUtf8($insertDbBadm));
                // die();

                $insertDbBadm = $this->convertirEnUtf8($insertDbBadm);
                $this->badm->insererDansBaseDeDonnees($insertDbBadm);
                $this->genererPdf->genererPdfBadm($generPdfBadm);
                $this->genererPdf->copyInterneToDOXCUWARE($NumBDM, $agenceEmetteur . $serviceEmetteur);
                header('Location: /Hffintranet/index.php?action=listBadm');
                exit();
            }
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