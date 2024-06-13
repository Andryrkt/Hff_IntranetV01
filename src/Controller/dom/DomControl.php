<?php

namespace App\Controller\dom;


use App\Controller\Controller;
use App\Form\AgenceServiceType;
use App\Form\AgenceServicesType;
use App\Controller\Traits\DomTrait;
use App\Controller\Traits\DomAjaxTrait;
use Symfony\Component\Routing\Annotation\Route;


class DomControl extends Controller
{

    use DomTrait;
 

    public function filterStatut()
    {
        $this->SessionStart();

        $Libstatut = $_POST['LibStatut'];
        $Statut = $this->DomModel->filterstatut($Libstatut);
        echo json_encode($Statut);
    }

 
    /**
     * selection catgégorie dans l'ajax 
     * @Route("/selectCateg")
     */
    public function selectCatg()
    {
        $this->SessionStart();


        $valeurSelect = $_POST['typeMission'];
        $codeAg = $_POST['CodeAg'];
        if ($codeAg !== '50') {
            $AgenceCode = 'STD';
        } else {
            $AgenceCode = '50';
        }
        $InforCatge = $this->DomModel->CategPers($valeurSelect, $AgenceCode);
        $response = "<label for='CategPers' class='label-form' id='labCategPers'> Catégorie:</label>";
        $response .= "<select id='categPers' class='form-select' name='categPers'>";
        foreach ($InforCatge as $info) {
            $categ = $info['Catg'];
            $info = iconv('Windows-1252', 'UTF-8', $categ);

            $response .= "<option value='$info'>$info</option>";
        }
        $response .= "</select>";

        echo $response;
    }

    /**
     * selection categorie Rental 
     * @Route("/selectCatgeRental", name="")
     */
    public function selectCategRental()
    {
        $this->SessionStart();

        $ValCodeserv = $_POST['CodeRental'];
        $CatgeRental = $this->DomModel->catgeRental($ValCodeserv);
        $RentalCatg = "<label for='CategRental' class='label-form' id='labCategRental'> Catégorie:</label>";
        $RentalCatg .= "<select id='categRental' class='form-select' name='categRental' >";
        foreach ($CatgeRental as $Catg) {
            $categ = $Catg['Catg'];
            $Catge50 = iconv('Windows-1252', 'UTF-8', $categ);

            $RentalCatg .= "<option value='$Catge50'>$Catge50</option>";
        }
        $RentalCatg .= "</select>";

        echo $RentalCatg;
    }


    /**
     * selection des sites (regions) correspondant aux catégorie selectionner 
     * @Route("/selectIdem", name="dom_selectSiteRental")
     */
    public function selectSiteRental()
    {
        $this->SessionStart();

        $CatgPersSelect = $_POST['CategPers'];
        $TypeMiss = $_POST['TypeMiss'];

        $MutSiteRental = $this->DomModel->SelectSite($TypeMiss, $CatgPersSelect);

        $response1 = "<label for='SiteRental' class='label-form' id='labSiteRental'> Site:</label>";
        $response1 .= "<select id='SiteRental' class='form-select' name='SiteRental'>";
        foreach ($MutSiteRental as $Site) {
            $Site = $Site['Destination'];
            $info = iconv('Windows-1252', 'UTF-8', $Site);

            $response1 .= "<option value='$info'>$info</option>";
        }
        $response1 .= "</select>";

        echo $response1;
    }

    /**
     * afficher Prix selon selection Sites 
     * @Route("/selectPrixRental", name="selectPrixRental")
     */
    public function SelectPrixRental()
    {
        $this->SessionStart();

        $typeMiss = $_POST['typeMiss'];
        $categ = $_POST['categ'];
        $sitesel = $_POST['siteselect'];
        $codeserv = $_POST['codeser'];
        $count = $this->DomModel->SiRentalCatg($categ);
        $nb_count = intval($count);

        if ($nb_count === 0) {
            $agserv = 'STD';
            $Prix = $this->DomModel->SelectMUTPrixRental($typeMiss, $categ, $sitesel, $agserv);
            //echo $agserv;
            echo  $Prix[0]['Montant_idemnite'];

            // print_r($Prix);
        } else {
            $agserv = '50';
            $Prix = $this->DomModel->SelectMUTPrixRental($typeMiss, $categ, $sitesel, $agserv);
            //echo $agserv;
            echo  $Prix[0]['Montant_idemnite'];
        }
    }



    /**
     * recuperation des variable ci-dessous vers les views (FormDOM) indiquer 
     * @Route("/newDom", name="dom_newDom", methods={"GET", "POST"})
     */
    public function newDom()
    {
        $this->SessionStart();

        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $Code_AgenceService_Sage = $this->DomModel->getAgence_SageofCours($_SESSION['user']);
        $CodeServiceofCours = $this->DomModel->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
        // $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
        $PersonelServOfCours = $this->DomModel->getInfoUserMservice($_SESSION['user']);
        $TypeDocument = $this->DomModel->getTypeDoc();


        $CodeServiceofCours = $this->conversionTabCaractere($CodeServiceofCours);
        //$PersonelServOfCours  = $this->conversionTabCaractere($PersonelServOfCours);
        //$TypeDocument = $this->conversionTabCaractere($TypeDocument);

        // var_dump($CodeServiceofCours, $PersonelServOfCours);
        // var_dump($TypeDocument);
        // die();

        $fichier = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/Acces/Agence.txt';
        // var_dump($fichier);
        // die();

        $LibAgence = $CodeServiceofCours['nom_agence_i100'];
        $LibServ = $CodeServiceofCours['service_ips'];

        $Agence = $LibAgence . " " . $LibServ;
        $boolean2 = strpos(file_get_contents($fichier), $Agence);

        self::$twig->display(
            'dom/FormDOM.html.twig',
            [
                'CodeServiceofCours' => $CodeServiceofCours,
                'PersonelServOfCours' => $PersonelServOfCours,
                'TypeDocument' => $TypeDocument,
                'boolean2' => $boolean2,
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean
            ]
        );
    
    }


    /**
     * recupere les variable ci-dessous vers le views => FormCompleDOM
     * @Route("/checkMatricule", name="dom_showDomPdf")
     */
    public function ShowDomPDF()
    {
        $this->SessionStart();

        if ($_SERVER['REQUEST_METHOD']  === 'POST') {

            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);

            $CategPers = $_POST['categPers'];
            //$NumDom = $_POST['NumDOM'];
            $code_service = $_POST['Serv'];
            $service = $_POST['LibServ'];
            $typeMission = $_POST['typeMission'];
            // $autrtype = $_POST['AutreType'];
            $Maricule = $_POST['matricule'];
            $UserConnect = $_SESSION['user'];
            $check = $_POST['radiochek'];
            $nomExt = $_POST['namesExt'];
            $prenomExt = $_POST['firstnamesExt'];
            $CINext = $_POST['cin'];

            $datesyst = $this->DomModel->getDatesystem();
            $Noms = $this->DomModel->getName($Maricule);
            $Compte = $this->DomModel->getInfoTelCompte($Maricule);

            $nom = $Noms[0]['Nom'];
            $prenom = $Noms[0]['Prenoms'];
            $codeServ = $Compte[0]['Code_serv'];
            $servLib = $Compte[0]['Serv_lib'];
            if (isset($Compte[0]['NumeroTel_Recente']) || isset($Compte[0]['Numero_Compte_Bancaire'])) {
                $numTel = $Compte[0]['NumeroTel_Recente'];
                $numCompteBancaire = $Compte[0]['Numero_Compte_Bancaire'];
            } else {
                $numTel = '';
                $numCompteBancaire = '';
            }

            $form = self::$validator->createBuilder(AgenceServiceType::class)->getForm();
            self::$twig->display(
                'dom/FormCompleDOM.html.twig',
                [
                    'CategPers' => $CategPers,
                    'code_service' => $code_service,
                    'service' => $service,
                    'typeMission' => $typeMission,
                    'Maricule' => $Maricule,
                    'UserConnect' => $UserConnect,
                    'check' => $check,
                    'nomExt' => $nomExt,
                    'prenomExt' => $prenomExt,
                    'CINext' => $CINext,
                    'datesyst' => $datesyst,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'codeServ' => $codeServ,
                    'servLib' => $servLib,
                    'numTel' => $numTel,
                    'numCompteBancaire' => $numCompteBancaire,
                    'infoUserCours' => $infoUserCours,
                    'boolean' => $boolean,
                    'form' => $form->createView()
                ]
            );
            
        }
    }


    private function alertRedirection(string $message, string $chemin = "/Hffintranet/newDom")
    {
        echo "<script type=\"text/javascript\"> alert( ' $message ' ); document.location.href ='$chemin';</script>";
    }

   


    /**
     * action de bouton dans view => FormCompleDOM
     * recupere tous les variable POSt dans le FormCompleDOM 
     * But: S'il n'y a pas de Pièce Joint => Génerer le PDF , Copie le PDF generer danns le DOCUMENT DOCUWARE Puis Insere dans la base
     *  S'il y en a 1 Pièce joint => Génere le PDF => Fusionne  avec le Piéce Joint => Inserer dans la Base 
     * s'il y en a 2 (type Temporaire (Externe) Obliger 2 pièce joint ) => Fusionne  avec le Piéce Joint => Inserer dans la Base 
     * 
     * @Route("/envoieImprimeDom", name="dom_envoieImprimeDom")
     */

    public function EnvoieImprimeDom()
    {
        //$this->alertRedirection('coucou');
        //echo '<script> alert("okey") </script>';


        $this->SessionStart();

        if ($_SERVER['REQUEST_METHOD']  === 'POST') {


            // var_dump($_POST);
            // die();
            $AllMontant = $_POST['Alldepense'];
            $AllMont = str_replace('.', '', $AllMontant);

            $checkext = $_POST['radiochek'];
            $usersession = $_SESSION['user'];
            //Interne
            $NomINt = $_POST['nomprenom'];
            $PrenomsINt = $_POST['prenom'];
            $matrInt = $_POST['matricule'];
            //temporaire
            $Nomext = $_POST['namesExt'];
            $PrenomExt = $_POST['firstnamesExt'];
            $MatrExt = $_POST['cin'];

            //EMETTEUR
            //Serv_Ext
            $Code_serv = strtoupper($_POST['Serv']); //80 Admin
            $code = explode(" ", $Code_serv);
            $code_Agence = strtolower(current($code)); //80 
            $Agence = strtolower(end($code)); // Admin
            $serv = $_POST['LibServ']; //INF info 
            $codeserv = explode(" ", $serv);
            $Code_Servi = strtolower(current($codeserv)); // INF
            $Servi = strtolower(end($codeserv)); // INfo
            $codeAg_serv = $code_Agence . $Code_Servi; //80-INF
            $LibelleCodeAg_Serv = $Agence . "-" . $Servi;

            //Serv_INT
            $Code_servINT = strtoupper($_POST['ServINt']);
            $codeINT = explode(" ", $Code_servINT);
            $code_AgenceINT = strtolower(current($codeINT)); //80 
            $AgenceINT = strtolower(end($codeINT)); // Admin
            $servINT = $_POST['LibServINT']; //INF info 
            $codeservINT = explode(" ", $servINT);
            $Code_ServiINT = strtolower(current($codeservINT)); // INF

            $ServiINT = strtolower(end($codeservINT)); // INfo
            $codeAg_servINT = $code_AgenceINT . $Code_ServiINT; //80-INF
            $LibelleCodeAg_ServINT = $AgenceINT . "-" . $ServiINT;
            //$codeServEmeteur = $_POST['ServINt'] . '-' . $_POST['LibServINT'];
            //var_dump($codeServEmeteur); die();
            //FIN Emetteur

            //DEBITTEUR
            $codeServiceDebitteur = current(explode(' ', strtoupper($_POST['codeService'])));
            $serviceDebitteur = current(explode(' ', strtoupper($_POST['service'])));
            $codeServDebiteur = $_POST['codeService'] . '-' . $_POST['service'];
            //FIN debitteur

            $dateSystem = $_POST['datesyst'];
            $dateS = date("d/m/Y", strtotime($_POST['datesyst']));
            $NumDom = $this->DomModel->DOM_autoINcriment(); //$_POST['NumDOM'];

            $Devis = $_POST['Devis'];

            $typMiss = $_POST['typeMission'];

            if ($_POST['typeMission'] === 'FRAIS EXCEPTIONNEL') {
                $Site = 'AUTRES VILLES';
            } else {
                $Site = $_POST['SiteRental'];
            }

            //$CatgeRent =  categ Rental
            if (isset($_POST['categRental'])) {
                $CatgeRent = $_POST['categRental'];
            } else {
                $CatgeRent =  $_POST['catego'];
            }
            $CatgeSTD = $_POST['catego']; //catgeSTD

            // echo 'Rental' . $CatgeRent . 'STD: ->' . $CatgeSTD . '   Site=>' . $Site . '<br>';

            //$autrTyp = $_POST['AutreType'];

            $DateDebut = $_POST['dateDebut'];
            $dateD = date("d/m/Y", strtotime($DateDebut));
            $heureD = $_POST['heureDebut'];
            $DateFin = $_POST['dateFin'];
            $dateF = date("d/m/Y", strtotime($DateFin));
            $heureF = $_POST['heureFin'];
            $NbJ = $_POST['Nbjour'];
            $motif =  str_replace("'", "''", $_POST['motif']);

            $Client = substr(str_replace("'", "''", $_POST['client']), 0, 26);

            $fiche = $_POST['fiche'];

            $lieu = str_replace("'", "''", $_POST['lieuInterv']);
            $vehicule = $_POST['vehicule'];
            $numvehicul = $_POST['N_vehicule'];
            $idemn = $_POST['idemForfait'];
            $idemnDoit = $_POST['idemForfait01'];
            $totalIdemn = $_POST['TotalidemForfait'];
            //
            $Idemn_depl = $_POST['IdemDeplac'];
            //
            $motifdep01 = str_replace("'", "''", $_POST['MotifAutredep']);
            $montdep01 = $_POST['Autredep1'];
            $motifdep02 = str_replace("'", "''", $_POST['MotifAutredep2']);
            $montdep02 = $_POST['Autredep2'];
            $motifdep03 = str_replace("'", "''", $_POST['MotifAutredep3']);
            $montdep03 = $_POST['Autredep3'];
            $totaldep = $_POST['TotalAutredep'];
            $libmodepaie = $_POST['modepaie'];
            $valModesp = $_POST['valModesp'];
            $valModemob = str_replace(" ", "", $_POST['valModemob']);
            $valModecompt = $_POST['valModecompt'];
            $valModeExt = $_POST['valModespExt'];




            // Créer un fichier temporaire avec un nom unique dans le répertoire temporaire spécifié
            // $tmp_file = tempnam(sys_get_temp_dir(), 'php');


            // $_FILES['blabla']['tmp_name'] = $tmp_file;
            // $_FILES['blabla']['name'] = 'doc.pdf';

            // FJ
            $extentsion = array('pdf', 'jpeg', 'jpg', 'png');

            // if (isset($_POST['file01'])) {
            //     $tmp_file1 = tempnam(sys_get_temp_dir(), 'php');
            //     $tmp_file2 = tempnam(sys_get_temp_dir(), 'php');
            //     $_FILES['file01']['name'] = $_POST["file01"];
            //     $_FILES['file01']['tmp_name'] = $tmp_file1;
            //     $_FILES['file02']['name'] = $_POST["file02"];
            //     $_FILES['file02']['tmp_name'] = $tmp_file2;
            //     $files01 = $_FILES["file01"];
            //     $file02 = $_FILES["file02"];
            // } else {
            // var_dump($_FILES);
            // die();
            $files01 = $_FILES["file01"];
            $file02 = $_FILES["file02"];
            //}

            $filename01 = str_replace("'", "''", $files01['name']);
            $filetemp01 = $files01['tmp_name'];
            $filename_separator01 = explode('.', $filename01);
            $file_extension01 = strtolower(end($filename_separator01));

            $filename02 = str_replace("'", "''", $file02['name']);
            $filetemp02 = $file02['tmp_name'];
            $filename_separator02 = explode('.', $filename02);
            $file_extension02 = strtolower(end($filename_separator02));

            // mail 
            $MailUser = $this->DomModel->getmailUserConnect($_SESSION['user']);


            // 1
            // var_dump('100');
            // die();
            if (strtotime($DateDebut) <= strtotime($DateFin) || strtotime($DateDebut) === strtotime($DateFin)) {
                // var_dump('101');
                // die();
                if ($checkext === "Interne") {
                    $Nom =  $NomINt;
                    $Prenoms = $PrenomsINt;
                    $matr = $matrInt;
                    $codeAg_servDB = strtoupper($codeAg_servINT);
                    // var_dump('102');
                    // die();
                    $LibelleCodeAg_ServDB = strtoupper($LibelleCodeAg_ServINT);
                    if ($code_AgenceINT === '50' && $typMiss === 'MUTATION') {
                        $CategoriePers = $CatgeRent;
                    } else {
                        $CategoriePers = $CatgeSTD;
                    }
                    // echo $CategoriePers . '<\br>' . $Site;

                    if ($libmodepaie === "ESPECES") {
                        $mode =  $valModesp;
                        $modeDB = "ESPECES " . $valModesp;
                    }
                    if ($libmodepaie === "MOBILE MONEY") {
                        $mode =  "TEL " . $valModemob;
                        $modeDB = "MOBILE MONEY : " . $valModemob;
                    }
                    if ($libmodepaie === "VIREMENT BANCAIRE") {
                        $mode =  "CPT " . $valModecompt;
                        $modeDB = "VIREMENT BANCAIRE : " . $valModecompt;
                    }


                    $tabInsertionBdInterne = [

                        "NumDom" => $NumDom,
                        "dateS" => $dateSystem,
                        "typMiss" => $typMiss,

                        "matr" => $matr,
                        "usersession" => $usersession,
                        "codeAg_serv" => $codeAg_servDB,
                        "DateDebut" => $DateDebut,
                        "heureD" => $heureD,
                        "DateFin" => $DateFin,
                        "heureF" => $heureF,
                        "NbJ" => $NbJ,
                        "motif" => $motif,
                        "Client" => $Client,
                        "fiche" => $fiche,
                        "lieu" => $lieu,
                        "vehicule" => $vehicule,
                        "idemn" => $idemn,
                        "totalIdemn" => $totalIdemn,
                        "motifdep01" => $motifdep01,
                        "montdep01" => $montdep01,
                        "motifdep02" => $motifdep02,
                        "montdep02" => $montdep02,
                        "motifdep03" => $motifdep03,
                        "montdep03" => $montdep03,
                        "totaldep" => $totaldep,
                        "AllMontant" => $AllMontant,
                        "modeDB" => $modeDB,
                        "valModemob" => $valModemob,
                        "Nom" => $Nom,
                        "Prenoms" => $Prenoms,
                        "Devis" => $Devis,
                        "filename01" => $filename01,
                        "filename02" => $filename02,
                        "usersessionCre" => $usersession,
                        "LibCodeAg_serv" => $LibelleCodeAg_ServDB,
                        "Numvehicule" => $numvehicul,
                        "doitIdemn" => $idemnDoit,
                        "CategoriePers" => $CategoriePers,
                        "Site" => $Site,
                        "Idemn_depl" => $Idemn_depl,
                        "codeServEmeteur" => $_POST['ServINt'] . '-' . $_POST['LibServINT'],
                        "codeServDebiteur" => $codeServDebiteur
                    ];

                    // Convertir chaque élément en majuscules
                    foreach ($tabInsertionBdInterne as $cle => $valeur) {
                        $tabInsertionBdInterne[$cle] = strtoupper($valeur);
                    }

                    $tabInterne = [
                        "Devis" => $Devis,
                        "Prenoms" => $Prenoms,
                        "AllMontant" => $AllMontant,
                        "Code_serv" => $Code_servINT,
                        "dateS" => $dateS,
                        "NumDom" => $NumDom,
                        "serv" => $servINT,
                        "matr" => $matr,
                        "typMiss" => $typMiss,

                        "Nom" => $Nom,
                        "NbJ" => $NbJ,
                        "dateD" => $dateD,
                        "heureD" => $heureD,
                        "dateF" => $dateF,
                        "heureF" => $heureF,
                        "motif" => $motif,
                        "Client" => $Client,
                        "fiche" => $fiche,
                        "lieu" => $lieu,
                        "vehicule" => $vehicule,
                        "numvehicul" => $numvehicul,
                        "idemn" => $idemn,
                        "totalIdemn" => $totalIdemn,
                        "motifdep01" => $motifdep01,
                        "montdep01" => $montdep01,
                        "motifdep02" => $motifdep02,
                        "montdep02" => $montdep02,
                        "motifdep03" => $motifdep03,
                        "montdep03" => $montdep03,
                        "totaldep" => $totaldep,
                        "libmodepaie" => $libmodepaie,
                        "mode" => $mode,
                        "codeAg_serv" => $codeAg_servDB,
                        "CategoriePers" => $CategoriePers,
                        "Site" => $Site,
                        "Idemn_depl" => $Idemn_depl,
                        "MailUser" => $MailUser,
                        "Bonus" => $idemnDoit,
                        "codeServiceDebitteur" => $codeServiceDebitteur,
                        "serviceDebitteur" => $serviceDebitteur
                    ];

                    //Type: 
                    if ($typMiss !== 'COMPLEMENT') {
                        // frais exep
                        // var_dump('103');
                        // die();
                        if ($typMiss === 'FRAIS EXCEPTIONNEL') {
                            // var_dump('104');
                            // die();
                            $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);


                            // nvl date 
                            // $DDForm = $DateDebut;
                            // $DFForm = $DateFin;
                            //var_dump($DDForm, $DFForm);
                            if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                                // echo 'non null';
                                // var_dump('105');
                                // die();
                                //en cours

                                if ($this->verifierSiDateExistant($matr,  $DateDebut, $DateFin)) {
                                    $message = "Cette Personne a déja une mission enregistrée sur ces dates, vérifier SVP!";
                                    $this->alertRedirection($message);
                                } else {
                                    // var_dump('106');
                                    // die();
                                    if (!empty($filename01) || !empty($filename02)) {
                                        // var_dump('107');
                                        // die();
                                        echo 'avec PJ' . $filename01 . '-' . $filename02;

                                        $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                        $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                    } else {
                                        // var_dump('108');
                                        // die();
                                        // echo 'sans PJ';
                                        $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                        $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                    }
                                    //
                                }
                            } else {
                                //  echo 'null';
                                // echo 'cette personne est disponnible';
                                // var_dump('109');
                                // die();
                                //
                                if (!empty($filename01) || !empty($filename02)) {
                                    // var_dump('110');
                                    // die();
                                    echo 'avec PJ' . $filename01 . '-' . $filename02;
                                    $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                    $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                } else {
                                    // var_dump('111');
                                    // die();
                                    // echo 'sans PJ';
                                    $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                    $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                }
                                //
                            } //chevauchement------------------
                        } //frais excep
                        // var_dump('112');
                        // die();
                        $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                        
                        if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                            
                            if ($this->verifierSiDateExistant($matr,  $DateDebut, $DateFin)) {
                                // var_dump('114');
                                // die();
                                $message = "Cette Personne a déja une mission enregistrée sur ces dates, vérifier SVP!";

                                $this->alertRedirection($message);
                            } else {
                                // var_dump('115');
                                // die();
                                if (!empty($filename01) || !empty($filename02)) {
                                    echo 'avec PJ' . $filename01 . '-' . $filename02;
                                    // var_dump('116');
                                    // die();
                                    //virement ou especes 
                                    if ($libmodepaie !== 'MOBILE MONEY') {
                                        // var_dump('117');
                                        // die();
                                        $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                        $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                    } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                        // var_dump('118');
                                        // die();
                                        //echo 'ie ambany 500000';
                                        $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                        $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                    } //Mobile&allMOnt
                                    else {
                                        $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                        $this->alertRedirection($message);
                                    }
                                    //
                                } else {
                                    // echo 'sans PJ';
                                    // var_dump('119');
                                    // die();

                                    // sans JP
                                    if ($libmodepaie !== 'MOBILE MONEY') {
                                        //echo 'io';
                                        // var_dump('120');
                                        // die();
                                        $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                        $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                    } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                        // var_dump('121');
                                        // die();
                                        // echo 'ie ambany 500000';
                                        $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                        $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                    } //mobile&allMont 
                                    else {

                                        $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                        $this->alertRedirection($message);
                                    }
                                    //
                                }
                                //
                            }
                        } else {
                            //  echo 'null';
                            // echo 'cette personne est disponnible';
                            // var_dump('122');
                            // die();
                            //
                            if (!empty($filename01) || !empty($filename02)) {
                                echo 'avec PJ' . $filename01 . '-' . $filename02;
                                // var_dump('123');
                                // die();
                                //si mode avec PJ
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    // var_dump('124');
                                    // die();
                                    $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                    $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {

                                    // var_dump('125');
                                    // die();
                                    //echo 'ie ambany 500000';
                                    $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                    $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                } //Mobile&allMOnt
                                else {
                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    $this->alertRedirection($message);
                                    var_dump($libmodepaie);
                                    die();
                                }
                                //
                            } else {
                                // echo 'sans PJ';
                                // var_dump('126');
                                // die();
                                //
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    // var_dump('127');
                                    // die();
                                    $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                    $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    // var_dump('128');
                                    // die();
                                    // echo 'ie ambany 500000';
                                    $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                    $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                } //mobile&allMont 
                                else {
                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    $this->alertRedirection($message);
                                    var_dump($libmodepaie);
                                    die();
                                }
                                //
                            }
                            //
                        } //chevauchement------------------
                    } else {
                        // si complement sans chevauche 
                        // var_dump('129');
                        // die();
                        //
                        if (!empty($filename01) || !empty($filename02)) {
                            echo 'avec PJ' . $filename01 . '-' . $filename02;
                            // var_dump('130');
                            // die();
                            //
                            if ($libmodepaie !== 'MOBILE MONEY') {
                                // var_dump('131');
                                // die();
                                $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                move_uploaded_file($filetemp01, $Upload_file);
                                $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                move_uploaded_file($filetemp02, $Upload_file02);
                                $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';
                                if (!empty($filename02)) {
                                    //echo 'fichier02';
                                    $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
                                } else {
                                    $this->fusionPdf->genererFusion1($FichierDom, $filename01);
                                    //echo 'echo non';
                                }
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                // var_dump('132');
                                // die();
                                //echo 'ie ambany 500000';
                                $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                            } //Mobile&allMOnt
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                $this->alertRedirection($message);
                            }
                            //
                        } else {
                            // echo 'sans PJ' test gut; 
                            // var_dump('133');
                            // die();
                            if ($libmodepaie !== 'MOBILE MONEY') {
                                // var_dump('134');
                                // die();
                                $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);

                                $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                // var_dump('135');
                                // die();
                                // echo 'ie ambany 500000';
                                $this->insereDbCreePdfInterne($tabInsertionBdInterne, $tabInterne, $NumDom);
                                $this->genererPdf->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                            } //mobile&allMont 
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                $this->alertRedirection($message);
                            }
                            //
                        }
                        //
                    } //Type
                } else {
                    $codeAg_servDB = strtoupper($codeAg_serv);
                    $LibelleCodeAg_ServDB = strtoupper($LibelleCodeAg_Serv);
                    $Nom = $Nomext;
                    $Prenoms = $PrenomExt;
                    $matr = "XER00 -" . $MatrExt . " - TEMPORAIRE";

                    if ($code_Agence === '50' && $typMiss === 'MUTATION') {
                        $CategoriePers = $CatgeRent;
                    } else {
                        $CategoriePers = $CatgeSTD;
                    }

                    if ($libmodepaie === "ESPECES") {
                        $mode =  $valModeExt;
                        $modeDB = "ESPECES " . $valModeExt;
                    }
                    if ($libmodepaie === "MOBILE MONEY") {
                        $mode =  "TEL " . $valModeExt;
                        $modeDB = "MOBILE MONEY : " . $valModeExt;
                    }
                    if ($libmodepaie === "VIREMENT BANCAIRE") {
                        $mode =  "CPT " . $valModeExt;
                        $modeDB = "VIREMENT BANCAIRE : " . $valModeExt;
                    }




                    $tabInsertionBdExterne = [

                        "NumDom" => $NumDom,
                        "dateS" => $dateSystem,
                        "typMiss" => $typMiss,

                        "matr" => $matr,
                        "usersession" => $usersession,
                        "codeAg_serv" => $codeAg_servDB,
                        "DateDebut" => $DateDebut,
                        "heureD" => $heureD,
                        "DateFin" => $DateFin,
                        "heureF" => $heureF,
                        "NbJ" => $NbJ,
                        "motif" => $motif,
                        "Client" => $Client,
                        "fiche" => $fiche,
                        "lieu" => $lieu,
                        "vehicule" => $vehicule,
                        "idemn" => $idemn,
                        "totalIdemn" => $totalIdemn,
                        "motifdep01" => $motifdep01,
                        "montdep01" => $montdep01,
                        "motifdep02" => $motifdep02,
                        "montdep02" => $montdep02,
                        "motifdep03" => $motifdep03,
                        "montdep03" => $montdep03,
                        "totaldep" => $totaldep,
                        "AllMontant" => $AllMontant,
                        "modeDB" => $modeDB,
                        "valModemob" => $valModemob,
                        "Nom" => $Nom,
                        "Prenoms" => $Prenoms,
                        "Devis" => $Devis,
                        "filename01" => $filename01,
                        "filename02" => $filename02,
                        "usersessionCre" => $usersession,
                        "LibCodeAg_serv" => $LibelleCodeAg_ServDB,
                        "Numvehicule" => $numvehicul,
                        "doitIdemn" => $idemnDoit,
                        "CategoriePers" => $CategoriePers,
                        "Site" => $Site,
                        "Idemn_depl" => $Idemn_depl,
                        "codeServEmeteur" => $_POST['Serv'] . '-' . $_POST['LibServ'],
                        "codeServDebiteur" => $codeServDebiteur
                    ];

                    // Convertir chaque élément en majuscules
                    foreach ($tabInsertionBdExterne as $cle => $valeur) {
                        $tabInsertionBdExterne[$cle] = strtoupper($valeur);
                    }

                    $tabExterne = [
                        "Devis" => $Devis,
                        "Prenoms" => $Prenoms,
                        "AllMontant" => $AllMontant,
                        "Code_serv" => $Code_serv,
                        "dateS" => $dateS,
                        "NumDom" => $NumDom,
                        "serv" => $serv,
                        "matr" => $matr,
                        "typMiss" => $typMiss,

                        "Nom" => $Nom,
                        "NbJ" => $NbJ,
                        "dateD" => $dateD,
                        "heureD" => $heureD,
                        "dateF" => $dateF,
                        "heureF" => $heureF,
                        "motif" => $motif,
                        "Client" => $Client,
                        "fiche" => $fiche,
                        "lieu" => $lieu,
                        "vehicule" => $vehicule,
                        "numvehicul" => $numvehicul,
                        "idemn" => $idemn,
                        "totalIdemn" => $totalIdemn,
                        "motifdep01" => $motifdep01,
                        "montdep01" => $montdep01,
                        "motifdep02" => $motifdep02,
                        "montdep02" => $montdep02,
                        "motifdep03" => $motifdep03,
                        "montdep03" => $montdep03,
                        "totaldep" => $totaldep,
                        "libmodepaie" => $libmodepaie,
                        "mode" => $mode,
                        "codeAg_serv" => $codeAg_servDB,
                        "CategoriePers" => $CategoriePers,
                        "Site" => $Site,
                        "Idemn_depl" => $Idemn_depl,
                        "MailUser" => $MailUser,
                        "Bonus" => $idemnDoit,
                        "codeServiceDebitteur" => $codeServiceDebitteur,
                        "serviceDebitteur" => $serviceDebitteur
                    ];




                    if ($typMiss !== 'COMPLEMENT') {
                        // var_dump('01');
                        // die();
                        //si frais execption
                        if ($typMiss === 'FRAIS EXCEPTIONNEL' && $Devis !== 'MGA') {

                            $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                            // nvl date 

                            if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                                // var_dump('02');
                                // die();
                                //echo 'non null 1';
                                //en cours
                                if ($this->verifierSiDateExistant($matr,  $DateDebut, $DateFin)) {

                                    $message = "Cette personne a déja une mission enregistrée sur ces dates, vérifier SVP!";

                                    $this->alertRedirection($message);
                                } else {
                                    // var_dump('03');
                                    // die();
                                    //comme d'hab
                                    $this->genererPdf->genererPDF($tabExterne);
                                    //echo 'ie ambany 500000';
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                            var_dump('04');
                                            die();
                                            $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);


                                            $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);


                                            $this->DomModel->InsertDom($tabInsertionBdExterne);
                                            $this->DomModel->modificationDernierIdApp($NumDom,'DOM');
                                        } else {

                                            $message = "Merci de Mettre les pièce jointes en PDF";

                                            $this->alertRedirection($message);
                                        }
                                    } else {

                                        $message = "Merci de Mettre les pièce jointes";

                                        $this->alertRedirection($message);
                                    }



                                    //
                                }
                            } else {
                                //exce
                                // Mobile& AllMont 
                                // var_dump('05');
                                // die();
                                $this->genererPdf->genererPDF($tabExterne);
                                //echo 'ie ambany 500000';
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                        // var_dump('06');
                                        // die();
                                        $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                        $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);

                                        $this->DomModel->InsertDom($tabInsertionBdExterne);
                                        $this->DomModel->modificationDernierIdApp($NumDom,'DOM');
                                    } else {
                                        $message = "Merci de Mettre les pièce jointes en PDF";

                                        $this->alertRedirection($message);
                                    }
                                } else {
                                    $message = "Merci de Mettre les pièce jointes";

                                    $this->alertRedirection($message);
                                }
                                //
                            } //chevauchement
                        } //excep
                        //
                        // var_dump($DomMaxMinDate);
                        // die();
                        // var_dump('07');
                        // die();
                        $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                        // nvl date 

                        if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                            //echo 'non null 2';
                            //en cours
                            // var_dump('08');
                            // die();

                            if ($this->verifierSiDateExistant($matr,  $DateDebut, $DateFin)) {

                                $message = "Cette personne a déja une mission enregistrée sur ces dates, vérifier SVP!";

                                $this->alertRedirection($message);
                            } else {
                                //comme d'hab
                                // var_dump('09');
                                // die();
                                //
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    // var_dump($_FILES);
                                    //var_dump($filename01, $filename02);
                                    // var_dump($_SERVER['DOCUMENT_ROOT']);
                                    // var_dump('10');
                                    // die();
                                    $this->genererPdf->genererPDF($tabExterne);
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                            // var_dump('11');
                                            // die();
                                            $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);


                                            $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);

                                            $this->DomModel->InsertDom($tabInsertionBdExterne);
                                            $this->DomModel->modificationDernierIdApp($NumDom,'DOM');
                                        } else {

                                            $message = "Merci de Mettre les pièces jointes en PDF";

                                            $this->alertRedirection($message);
                                        }
                                    } else {

                                        $message = "Merci de Mettre les pièces jointes";

                                        $this->alertRedirection($message);
                                    }
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    //echo 'ie ambany 500000';
                                    // var_dump('12');
                                    // die();
                                    $this->genererPdf->genererPDF($tabExterne);
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                            // var_dump('13');
                                            // die();
                                            $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                            $this->DomModel->InsertDom($tabInsertionBdExterne);
                                            $this->DomModel->modificationDernierIdApp($NumDom,'DOM');

                                            $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
                                        } else {

                                            $message = "Merci de Mettre les pièce jointes en PDF";

                                            $this->alertRedirection($message);
                                        }
                                    } else {

                                        $message = "Merci de Mettre les pièce jointes";

                                        $this->alertRedirection($message);
                                    }
                                } //mobile & AllMont 
                                else {

                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    $this->alertRedirection($message);
                                }
                                //
                                //
                            }
                        } else {
                            //exce
                            // Mobile& AllMont 
                            // var_dump('14');
                            // die();

                            if ($libmodepaie !== 'MOBILE MONEY') {
                                // var_dump('15');
                                // var_dump($filename01, $filename02);
                                // die();


                                $this->genererPdf->genererPDF($tabExterne);

                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                        // var_dump('16');
                                        // die();
                                        $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom($tabInsertionBdExterne);
                                        $this->DomModel->modificationDernierIdApp($NumDom,'DOM');

                                        $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
                                    } else {

                                        $message = "Merci de Mettre les pièce jointes en PDF";

                                        $this->alertRedirection($message);
                                    }
                                } else {
                                    $message = "Merci de Mettre les pièce jointes";

                                    $this->alertRedirection($message);
                                }
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                //echo 'ie ambany 500000';
                                // var_dump($_FILES);
                                // die();
                                // var_dump('17');
                                // die();
                                $this->genererPdf->genererPDF($tabExterne);

                                //var_dump($filename01, $filename02);
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                        // var_dump('18');
                                        // die();
                                        $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom($tabInsertionBdExterne);
                                        $this->DomModel->modificationDernierIdApp($NumDom,'DOM');

                                        $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
                                    } else {
                                        $message = "Merci de Mettre les pièce jointes en PDF";

                                        $this->alertRedirection($message);
                                    }
                                } else {
                                    $message = "Merci de Mettre les pièce jointes";

                                    $this->alertRedirection($message);
                                }
                            } //mobile & AllMont 
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                $this->alertRedirection($message);
                            }
                            //
                        } //chevauchement
                    } else {
                        //si complement sans chevauchement
                        // Mobile& AllMont 
                        // var_dump('19');
                        // die();

                        if ($libmodepaie !== 'MOBILE MONEY') {
                            // var_dump('20');
                            // die();
                            $this->genererPdf->genererPDF($tabExterne);
                            if (!empty($filename01) && !empty($filename02)) {
                                if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                    // var_dump('21');
                                    // die();
                                    $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom($tabInsertionBdExterne);
                                    $this->DomModel->modificationDernierIdApp($NumDom,'DOM');

                                    $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
                                } else {

                                    $message = "Merci de Mettre les pièces jointes en PDF";

                                    $this->alertRedirection($message);
                                }
                            } else {
                                $message = "Merci de Mettre les pièces jointes";

                                $this->alertRedirection($message);
                            }
                        } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                            //echo 'ie ambany 500000';
                            // var_dump('22');
                            // die();
                            $this->genererPdf->genererPDF($tabExterne);

                            //
                            if (!empty($filename01) && !empty($filename02)) {
                                if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                    // var_dump('23');
                                    // die();

                                    $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom($tabInsertionBdExterne);
                                    $this->DomModel->modificationDernierIdApp($NumDom,'DOM');

                                    $this->fusionPdf->genererFusion($FichierDom, $filename01, $filename02);
                                } else {
                                    $message = "Merci de Mettre les pièce jointes en PDF";

                                    $this->alertRedirection($message);
                                }
                            } else {
                                $message = "Merci de Mettre les pièce jointes";

                                $this->alertRedirection($message);
                            }
                        } //mobile & AllMont 
                        else {
                            $message = "Assurer que le Montant Total est supérieur ou égale à 500.000";

                            $this->alertRedirection($message);
                        }
                        //
                    } //Type
                }
                //  1date 
            } else {
                $message = "Merci de vérifier la date début ";

                $this->alertRedirection($message);
            }
            echo '<script type="text/javascript">   
                document.location.href = "/Hffintranet/listDomRech";
                </script>';
        }
    }


    
    /**
     * creation du débiteur (code service et service)
     * @Route("/agServDest", name="dom_agenceServiceJson")
     */
    // public function agenceServiceJson()
    // {
    //     $codeServiceIrium = $this->DomModel->RecuperationCodeEtServiceIrium();


    //     header("Content-type:application/json");

    //     echo json_encode($codeServiceIrium);
    // }
}
