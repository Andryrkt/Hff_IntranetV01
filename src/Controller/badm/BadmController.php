<?php


namespace App\Controller\badm;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\ConversionTrait;
use App\Controller\Traits\IncrementationTrait;
use Symfony\Component\Routing\Annotation\Route;

class BadmController extends Controller
{

    use Transformation;
    use ConversionTrait;
    use IncrementationTrait;
    use FormatageTrait;

    private function alertRedirection(string $message, string $chemin = "/Hffintranet/formBadm")
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

    /**
     * @Route("/formBadm", name="badm_formBadm", methods={"GET","POST"})
     */
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



                $agenceEmetteur = $data[0]['agence'] . ' ' . explode('-', $data[0]['service'])[0];
                $serviceEmetteur = trim($data[0]['code_service'] . ' ' . explode('-', $data[0]['service'])[1]);

                $agenceServiceAutoriserbd = $this->badm->recupCodeAgenceServiceAutoriser($_SESSION['user']);

                $agenceServiceAutoriser = $this->transformEnSeulTableau($agenceServiceAutoriserbd);


                $codeAgenceService = $data[0]['agence'] . trim($data[0]['code_service']);

                $coutAcquisition = $data[0]['droits_taxe'];
                $vnc = $coutAcquisition - $data[0]['amortissement'];



                if ($boolean) {

                    self::$twig->display(
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
                        self::$twig->display(
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
                self::$twig->display(
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

    /**
     * agence devient la clé du tableaut et le service devient la valeur
     *
     * @param [type] $tab
     * @param [type] $agences
     * @param [type] $services
     * @return void
     */
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


    /**
     * @Route("/serviceDestinataire", name="badm_serviceDestinataire")
     */
    public function serviceDestinataire()
    {
        $serviceDestinataires = $this->badm->recupeAgenceServiceDestinataire();

        $nouveauTableau = $this->agenceCleServiceValeur($serviceDestinataires, 'agence', 'service');

        //var_dump($nouveauTableau);
        header("Content-type:application/json");

        $jsonData = json_encode($nouveauTableau);

        $this->testJson($jsonData);
    }







    private function imageDansDossier($image, string $imagename, string $chemin)
    {
        $target_dir = $chemin;  // Spécifiez le dossier où l'image sera enregistrée.
        //$image["name"] = $NumBDM . '_' . $agenceService . '.jpg';
        // var_dump($image["name"]);
        // die();
        $target_file = $target_dir . basename($imagename);
        $uploadOk = 1;
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
        $taille = 1.5 * 1024 * 1024;
        // Vérifier la taille du fichier
        if ($image["size"] > $taille) {  // Limite de taille de 300KB
            //echo "";
            $message = "Désolé, votre fichier est trop volumineux (>1,5MB).";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }

        // Autoriser certains formats de fichier
        if (
            $imageFileType != "jpg"  && $imageFileType != "jpeg" && $imageFileType != "png"

        ) {
            // echo "Désolé, seuls les fichiers JPG est autorisés.";
            $message = "Désolé, seuls les fichiers JPG, JEPG et PNG sont autorisés.";
            $this->alertRedirection($message);
            $uploadOk = 0;
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

    public function fichierDansDossier($fichier, string $fichiername, string $chemin)
    {
        $target_dir = $chemin;
        $target_file = $target_dir . basename($fichiername);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));



        // Vérifier si le fichier existe déjà
        if (file_exists($target_file)) {
            //echo "";
            $message = "Désolé, le fichier existe déjà.";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }
        $taille = 1.5 * 1024 * 1024;
        // Vérifier la taille du fichier
        if ($fichier["size"] > $taille) {  // Limite de taille de 300KB
            //echo "";
            $message = "Désolé, votre fichier est trop volumineux (>1,5MB).";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }

        // Autoriser certains formats de fichier
        if (
            $imageFileType != "pdf"  && $imageFileType != "doc" && $imageFileType != "docx"

        ) {
            // echo "Désolé, seuls les fichiers JPG est autorisés.";
            $message = "Désolé, seuls les fichiers PDF, DOC et DOCX sont autorisés.";
            $this->alertRedirection($message);
            $uploadOk = 0;
        }

        // Vérifier si $uploadOk est mis à 0 par une erreur
        if ($uploadOk == 0) {
            //echo "";
            $message = "Désolé, votre fichier n'a pas été téléchargé.";
            $this->alertRedirection($message);
            // si tout est correct, essayer de télécharger le fichier
        } else {
            if (move_uploaded_file($fichier["tmp_name"], $target_file)) {
                //echo "Le fichier " . htmlspecialchars(basename($image["name"])) . " a été téléchargé.";
            } else {
                //echo ;
                $message = "Désolé, il y a eu une erreur lors du téléchargement de votre fichier.";
                $this->alertRedirection($message);
            }
        }
    }




    /**
     *cette function permet de tester s'il y a une valeur dans le $_POST et retourne une valeur selon la condeition
     * 
     * @param string $name c'est le name dans le formulaire
     * @param string $valeurNon si le name n'existe pas, la variable va prendre cette valeur
     * @return void
     */
    private function donneValeurExisteOuNon(string $name, $valeurNon = '')
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        } else {
            return $valeurNon;
        }
    }

    /**
     * @Route("/formCompleBadm", name="badm_formCompleBadm", methods={"GET","POST"})
     */
    public function formCompleBadm()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->SessionStart();
            // var_dump($_POST);
            // die();
            //var_dump($this->badm->findAll());
            $data = $this->badm->findAll($_POST['idMateriel']);
            $codeMouvement = $_POST['codeMouvement'];

            // var_dump($codeMouvement);
            // die();
            // var_dump($data);
            // die();
            $NumBDM = $this->autoINcriment('BDM');
            $heureDemande = $this->getTime();
            $dateDemande = $this->getDatesystem();

            //var_dump('161');
            //echo $dateObject->format('Y-m-d'); // Affiche '2024-04-04'

            $MailUser = $this->badm->getmailUserConnect($_SESSION['user']);


            $numParc = $this->donneValeurExisteOuNon('numParc', $data[0]['num_parc']);



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

            $dateMiseLocation = $this->donneValeurExisteOuNon('dateMiseLocation', $data[0]['date_location']);

            $nomClient = $this->donneValeurExisteOuNon('nomClient');

            $modalitePaiement = $this->donneValeurExisteOuNon('modalitePaiement');

            $prixHt = (float)$this->donneValeurExisteOuNon('prixHt', 0);


            $motifMiseRebut = $this->donneValeurExisteOuNon('motifMiseRebut');


            // var_dump($_FILES);
            //  var_dump(getimagesize($_FILES["imageRebut"]["tmp_name"]));
            //  var_dump(basename($_FILES["imageRebut"]["name"]));
            //  die();


            if (isset($_FILES["imageRebut"]) && $_FILES["imageRebut"]['error'] === 0) {

                $extension = strtolower(pathinfo($_FILES['imageRebut']['name'], PATHINFO_EXTENSION));

                $nomAgenceServiceNonSeparer = $agenceEmetteur . $serviceEmetteur;

                $chemin = $_SERVER['DOCUMENT_ROOT'] . "/Hffintranet/Upload/bdm/images/";
                $imagename = $NumBDM . '_' . $nomAgenceServiceNonSeparer . '.' . $extension;

                $this->imageDansDossier($_FILES['imageRebut'], $imagename, $chemin);
                $image = $imagename;
            } else {

                $image = '';
                $extension = '';
            }

            if (isset($_FILES["fichierRebut"]) && $_FILES["fichierRebut"]['error'] === 0) {
                $extension = strtolower(pathinfo($_FILES['fichierRebut']['name'], PATHINFO_EXTENSION));
                $nomAgenceServiceNonSeparer = $agenceEmetteur . $serviceEmetteur;
                $chemin = $_SERVER['DOCUMENT_ROOT'] . "/Hffintranet/Upload/bdm/fichiers/";
                $fichierName = $NumBDM . '_' . $nomAgenceServiceNonSeparer . '.' . $extension;
                $this->fichierDansDossier($_FILES['fichierRebut'], $fichierName, $chemin);
                $fichier = $fichierName;
            } else {
                $fichier = '';
            }


            $orDb = $this->badm->recupeOr((int)$data[0]['num_matricule']);

            if (empty($orDb)) {
                $OR = 'NON';
            } else {
                $OR = 'OUI';

                // $position = 8; // Couper après le deuxième élément

                // for ($i=0; $i < count($orDb) ; $i++) { 
                //     $or1[] = array_slice($orDb[$i], 0, $position);
                //     $or2[] = array_slice($orDb[$i], $position);
                // }


                foreach ($orDb as $keys => $values) {
                    foreach ($values as $key => $value) {
                        //var_dump($key === 'date');
                        if ($key == "date") {
                            // $or1["Date"] = implode('/', array_reverse(explode("-", $value)));
                            $orDb[$keys]['date'] = implode('/', array_reverse(explode("-", $value)));
                        } elseif ($key == 'agence_service') {
                            $orDb[$keys]['agence_service'] = trim(explode('-', $value)[0]);
                        } elseif ($key === 'montant_total' || $key === 'montant_pieces' || $key === 'montant_pieces_livrees') {
                            $orDb[$keys][$key] = explode(',', $this->formatNumber($value))[0];
                        }
                    }
                }

                // var_dump($orDb);
                // foreach ($or2 as $keys => $values) {
                //     foreach ($values as $key => $value) {
                //         $or2[$keys][$key] = $this->formatNumber($value);
                //     } 
                // }
            }


            if ($codeMouvement === 'CESSION D\'ACTIF') {
                $codeMouvement = 'CESSION D\'\'ACTIF';
            }
            $idTypeMouvement = $this->badm->recupIdtypeMouvemnet($codeMouvement);

            $idMateriel = (int)$data[0]['num_matricule'];

            $idMateriels = $this->transformEnSeulTableau($this->badm->recupeIdMateriel());

            $idStatut = $this->badm->idOuvertStatutDemande();

            /**
             * TODO: eliminer le doublon du changemnet AGENCE/SERVICE, changement de CASIER, ...
             */
            $agenceServDest = $this->transformEnSeulTableau($this->badm->recupAgenceServDest($idMateriel));



            // var_dump($agenceDestinataire === '' && $serviceDestinataire === '' || $agenceServiceEmetteur === $agenceServiceDestinataire);
            // die();
            $conditionAgenceService = $agenceDestinataire === '' && $serviceDestinataire === '' || $agenceServiceEmetteur === $agenceServiceDestinataire;
            $conditionVide = $agenceDestinataire === '' && $serviceDestinataire === '' && $_POST['casierDestinataire'] === '' && $dateMiseLocation === '';
            if (($codeMouvement === 'ENTREE EN PARC' || $codeMouvement === 'CHANGEMENT AGENCE/SERVICE') && $conditionVide) {
                $message = 'compléter tous les champs obligatoires';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'ENTREE EN PARC' && in_array($idMateriel, $idMateriels)) {
                $message = 'ce matériel est déjà en PARC';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'CHANGEMENT AGENCE/SERVICE' && in_array($idMateriel, $idMateriels)) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } elseif ($codeMouvement === 'CHANGEMENT AGENCE/SERVICE' && $conditionAgenceService) {
                $message = 'le choix du type devrait être Changement de Casier';
                $this->alertRedirection($message);
            } else {

                $insertDbBadm = [
                    'Numero_Demande_BADM' => $NumBDM,
                    'Code_Mouvement' => $idTypeMouvement['ID_Type_Mouvement'],
                    'ID_Materiel' => $idMateriel,
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
                    'Num_Parc' => $numParc,
                    'Nom_Image' => $image,
                    'Nom_Fichier' => $fichier,
                    'ID_Statut_Demande' => (int)$idStatut
                ];
                foreach ($insertDbBadm as $cle => $valeur) {
                    $insertDbBadm[$cle] = strtoupper($valeur);
                }
                //var_dump($insertDbBadm);
                // die();
                if ($codeMouvement === 'CESSION D\'\'ACTIF') {
                    $codeMouvement = 'CESSION D\'ACTIF';
                }

                $generPdfBadm = [
                    'typeMouvement' => $codeMouvement,
                    'Num_BDM' => $NumBDM,
                    'Date_Demande' => $this->formatageDate($dateDemande),
                    'Designation' => $data[0]['designation'],
                    'Num_ID' => $data[0]['num_matricule'],
                    'Num_Serie' => $data[0]['num_serie'],
                    'Groupe' => $data[0]['famille'],
                    'Num_Parc' => $numParc,
                    'Affectation' => $data[0]['affectation'],
                    'Constructeur' => $data[0]['constructeur'],
                    'Date_Achat' => $this->formatageDate($data[0]['date_achat']),
                    'Annee_Model' => $data[0]['annee'],
                    'Modele' => $data[0]['modele'],
                    'Agence_Service_Emetteur' => $agenceEmetteur . '-' . $serviceEmetteur,
                    'Casier_Emetteur' => $casierEmetteur,
                    'Agence_Service_Destinataire' => $agenceDestinataire . '-' . $serviceDestinataire,
                    'Casier_Destinataire' => $casierDestinataire,
                    'Motif_Arret_Materiel' => $motifArretMateriel,
                    'Etat_Achat' => $etatAchat,
                    'Date_Mise_Location' => $this->formatageDate($dateMiseLocation),
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
                    'image' => $image,
                    'extension' => $extension,
                    'OR' => $OR
                ];
                // $generPdfBadm = $this->convertirEnUtf8($generPdfBadm);
                // var_dump($this->convertirEnUtf8($insertDbBadm));
                // die();

                $insertDbBadm = $this->convertirEnUtf8($insertDbBadm);
                $this->badm->insererDansBaseDeDonnees($insertDbBadm);
                $this->genererPdf->genererPdfBadm($generPdfBadm, $orDb);
                $this->genererPdf->copyInterneToDOXCUWARE($NumBDM, $agenceEmetteur . $serviceEmetteur);
                header('Location: /Hffintranet/listBadm');
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