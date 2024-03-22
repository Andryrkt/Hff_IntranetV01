<?php

class DomControl
{
    public $DomModel;
    private $PersonnelModel;
    public function __construct(DomModel $DomModel)
    {
        $this->DomModel = $DomModel;
    }


    public function filterStatut()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $Libstatut = $_POST['LibStatut'];
        $Statut = $this->DomModel->filterstatut($Libstatut);
        echo json_encode($Statut);
    }

    /**
     * selection catgégorie dans l'ajax 
     */
    public function selectCatg()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
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
     */
    public function selectCategRental()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
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
     */
    public function selectSiteRental()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
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
     */
    public function SelectPrixRental()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
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
    //
    /**
     * recuperation des variable ci-dessous vers les views (FormDOM) indiquer 
     */
    public function showFormDOM()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {

            //$NumDOM = $this->DomModel->DOM_autoINcriment();
            $UserConnect = $_SESSION['user'];
            $Code_AgenceService_Sage = $this->DomModel->getAgence_SageofCours($_SESSION['user']);
            $CodeServiceofCours = $this->DomModel->getAgenceServiceIriumofcours($Code_AgenceService_Sage, $_SESSION['user']);
            // $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $PersonelServOfCours = $this->DomModel->getInfoUserMservice($_SESSION['user']);
            $TypeDocument = $this->DomModel->getTypeDoc();

            include 'Views/Principe.php';
            include 'Views/DOM/FormDOM.php';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    /**
     * recupere les variable ci-dessous vers le views => FormCompleDOM
     */
    public function ShowDomPDF()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']  === 'POST') {
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




            include 'Views/Principe.php';
            //include 'Views/DOM/FormCompleAutre.php';
            include 'Views/DOM/FormCompleDOM.php';
        }
        if ($_SERVER['REQUEST_METHOD']  === 'GET') {
            $NumDom = $_GET['NumDomget'];
            $code_service = $_GET['code_service'];
            $service = $_GET['service'];
            $Maricule = $_GET['Matricule'];
            $check = $_GET['check'];
            $typeMission = $_GET['TypeMission'];
            $nomExt = $_GET['nom'];
            $prenomExt = $_GET['prenoms'];
            $CINext = $_GET['cin'];
            //$autrTyp = $_GET['autreType'];
            $UserConnect = $_SESSION['user'];
            $datesyst = $this->DomModel->getDatesystem();
            $Noms = $this->DomModel->getName($Maricule);
            $Compte = $this->DomModel->getInfoTelCompte($Maricule);

            include 'Views/Principe.php';
            //include 'Views/DOM/FormCompleAutre.php';
            include 'Views/DOM/FormCompleDOM.php';
        }
    }


    private function alertRedirection(string $message, string $chemin)
    {
        '<script type="text/javascript">
            alert(' . $message . ');
            document.location.href =' . $chemin . '";
        </script>';
    }

    private function changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB)
    {
        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
        move_uploaded_file($filetemp01, $Upload_file);
        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
        move_uploaded_file($filetemp02, $Upload_file02);
        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';
        if (!empty($filename02)) {
            //echo 'fichier02';
            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
        } else {
            $this->DomModel->genererFusion1($FichierDom, $filename01);
            //echo 'echo non';
        }
    }

    private function changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB)
    {
        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
        move_uploaded_file($filetemp01, $Upload_file);
        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
        move_uploaded_file($filetemp02, $Upload_file02);
        return $NumDom . '_' . $codeAg_servDB . '.pdf';
    }

    /**
     * action de bouton dans view => FormCompleDOM
     * recupere tous les variable POSt dans le FormCompleDOM 
     * But: S'il n'y a pas de Pièce Joint => Génerer le PDF , Copie le PDF generer danns le DOCUMENT DOCUWARE Puis Insere dans la base
     *  S'il y en a 1 Pièce joint => Génere le PDF => Fusionne  avec le Piéce Joint => Inserer dans la Base 
     * s'il y en a 2 (type Temporaire (Externe) Obliger 2 pièce joint ) => Fusionne  avec le Piéce Joint => Inserer dans la Base 
     */

    public function EnvoieImprimeDom()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

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
            $Site = $_POST['SiteRental'];
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

            $chemin = "/Hffintranet/index.php?action=New_DOM";
            // 1

            if (strtotime($DateDebut) <= strtotime($DateFin) || strtotime($DateDebut) === strtotime($DateFin)) {

                if ($checkext === "Interne") {
                    $Nom =  $NomINt;
                    $Prenoms = $PrenomsINt;
                    $matr = $matrInt;
                    $codeAg_servDB = strtoupper($codeAg_servINT);

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
                        if ($typMiss === 'FRAIS EXCEPTIONNEL' && $Devis !== 'MGA') {
                            $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                            // nvl date 
                            $DDForm = strtotime($DateDebut);
                            $DFForm = strtotime($DateFin);
                            if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                                // echo 'non null';
                                //en cours
                                $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                                $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                                if (($DDForm >=  $DD && $DDForm <= $DF) && ($DFForm >= $DD && $DFForm <= $DF)) {
                                    $message = "Cette Personne a déja une mission enregistrée sur ces dates, vérifier SVP!";
                                    echo $this->alertRedirection($message, $chemin);
                                } else {
                                    if (!empty($filename01) || !empty($filename02)) {
                                        echo 'avec PJ' . $filename01 . '-' . $filename02;

                                        $this->DomModel->InsertDom($tabInsertionBdInterne);

                                        //echo 'ie ambany 500000';
                                        $this->DomModel->genererPDF($tabInterne);

                                        $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                    } else {
                                        // echo 'sans PJ';
                                        $this->DomModel->InsertDom($tabInsertionBdInterne);
                                        // echo 'ie ambany 500000';
                                        $this->DomModel->genererPDF($tabInterne);

                                        $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                    }
                                    //
                                }
                            } else {
                                //  echo 'null';
                                // echo 'cette personne est disponnible';

                                //
                                if (!empty($filename01) || !empty($filename02)) {
                                    echo 'avec PJ' . $filename01 . '-' . $filename02;
                                    $this->DomModel->InsertDom($tabInsertionBdInterne);
                                    //echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF($tabInterne);

                                    $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                } else {
                                    // echo 'sans PJ';
                                    $this->DomModel->InsertDom($tabInsertionBdInterne);

                                    // echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF($tabInterne);

                                    $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                }
                                //
                            } //chevauchement------------------
                        } //frais excep
                        //
                        $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                        // nvl date 
                        $DDForm = strtotime($DateDebut);
                        $DFForm = strtotime($DateFin);
                        if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                            // echo 'non null';
                            //en cours
                            $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                            $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                            if (($DDForm >=  $DD && $DDForm <= $DF) && ($DFForm >= $DD && $DFForm <= $DF)) {

                                $message = "Cette Personne a déja une mission enregistrée sur ces dates, vérifier SVP!";

                                echo $this->alertRedirection($message, $chemin);
                            } else {
                                if (!empty($filename01) || !empty($filename02)) {
                                    echo 'avec PJ' . $filename01 . '-' . $filename02;

                                    //virement ou especes 
                                    if ($libmodepaie !== 'MOBILE MONEY') {
                                        $this->DomModel->InsertDom($tabInsertionBdInterne);

                                        $this->DomModel->genererPDF($tabInterne);

                                        $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                    } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                        //echo 'ie ambany 500000';
                                        $this->DomModel->InsertDom($tabInsertionBdInterne);

                                        $this->DomModel->genererPDF($tabInterne);

                                        $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                    } //Mobile&allMOnt
                                    else {
                                        $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                        echo $this->alertRedirection($message, $chemin);
                                    }
                                    //
                                } else {
                                    // echo 'sans PJ';


                                    // sans JP
                                    if ($libmodepaie !== 'MOBILE MONEY') {
                                        echo 'io';
                                        $this->DomModel->InsertDom($tabInsertionBdInterne);

                                        $this->DomModel->genererPDF($tabInterne);
                                        $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                    } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                        // echo 'ie ambany 500000';
                                        $this->DomModel->InsertDom($tabInsertionBdInterne);

                                        $this->DomModel->genererPDF($tabInterne);

                                        $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                    } //mobile&allMont 
                                    else {

                                        $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                        echo $this->alertRedirection($message, $chemin);
                                    }
                                    //
                                }
                                //
                            }
                        } else {
                            //  echo 'null';
                            // echo 'cette personne est disponnible';

                            //
                            if (!empty($filename01) || !empty($filename02)) {
                                echo 'avec PJ' . $filename01 . '-' . $filename02;

                                //si mode avec PJ
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    $this->DomModel->InsertDom($tabInsertionBdInterne);

                                    $this->DomModel->genererPDF($tabInterne);

                                    $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    //echo 'ie ambany 500000';
                                    $this->DomModel->InsertDom($tabInsertionBdInterne);

                                    $this->DomModel->genererPDF($tabInterne);

                                    $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                                } //Mobile&allMOnt
                                else {
                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    echo $this->alertRedirection($message, $chemin);
                                    var_dump($libmodepaie);
                                    die();
                                }
                                //
                            } else {
                                // echo 'sans PJ';

                                //
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    $this->DomModel->InsertDom($tabInsertionBdInterne);

                                    $this->DomModel->genererPDF($tabInterne);

                                    $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    // echo 'ie ambany 500000';
                                    $this->DomModel->InsertDom($tabInsertionBdInterne);

                                    $this->DomModel->genererPDF($tabInterne);

                                    $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                                } //mobile&allMont 
                                else {
                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    echo $this->alertRedirection($message, $chemin);
                                    var_dump($libmodepaie);
                                    die();
                                }
                                //
                            }
                            //
                        } //chevauchement------------------
                    } else {
                        // si complement sans chevauche 

                        //
                        if (!empty($filename01) || !empty($filename02)) {
                            echo 'avec PJ' . $filename01 . '-' . $filename02;

                            //
                            if ($libmodepaie !== 'MOBILE MONEY') {
                                $this->DomModel->InsertDom($tabInsertionBdInterne);

                                $this->DomModel->genererPDF($tabInterne);

                                $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                move_uploaded_file($filetemp01, $Upload_file);
                                $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                move_uploaded_file($filetemp02, $Upload_file02);
                                $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';
                                if (!empty($filename02)) {
                                    //echo 'fichier02';
                                    $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                } else {
                                    $this->DomModel->genererFusion1($FichierDom, $filename01);
                                    //echo 'echo non';
                                }
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                //echo 'ie ambany 500000';
                                $this->DomModel->InsertDom($tabInsertionBdInterne);

                                $this->DomModel->genererPDF($tabInterne);

                                $this->changementDossierFichierInterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);
                            } //Mobile&allMOnt
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                echo $this->alertRedirection($message, $chemin);
                            }
                            //
                        } else {
                            // echo 'sans PJ' test gut; 
                            //
                            if ($libmodepaie !== 'MOBILE MONEY') {
                                $this->DomModel->InsertDom($tabInsertionBdInterne);

                                $this->DomModel->genererPDF($tabInterne);

                                $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                // echo 'ie ambany 500000';
                                $this->DomModel->InsertDom($tabInsertionBdInterne);

                                $this->DomModel->genererPDF($tabInterne);
                                $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);
                            } //mobile&allMont 
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                echo $this->alertRedirection($message, $chemin);
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
                        //si frais execption
                        if ($typMiss === 'FRAIS EXCEPTIONNEL' && $Devis !== 'MGA') {

                            $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                            // nvl date 
                            $DDForm = strtotime($DateDebut);
                            $DFForm = strtotime($DateFin);
                            if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                                echo 'non null 1';
                                //en cours
                                $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                                $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                                if (($DDForm >=  $DD && $DDForm <= $DF) && ($DFForm >= $DD && $DFForm <= $DF)) {

                                    $message = "Cette personne a déja une mission enregistrée sur ces dates, vérifier SVP!";

                                    echo $this->alertRedirection($message, $chemin);
                                } else {
                                    //comme d'hab
                                    $this->DomModel->genererPDF($tabExterne);
                                    //echo 'ie ambany 500000';
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                            $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);


                                            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);


                                            $this->DomModel->InsertDom($tabInsertionBdExterne);
                                            var_dump('08');
                                            die();
                                        } else {

                                            $message = "Merci de Mettre les pièce jointes en PDF";

                                            echo $this->alertRedirection($message, $chemin);
                                        }
                                    } else {

                                        $message = "Merci de Mettre les pièce jointes";

                                        echo $this->alertRedirection($message, $chemin);
                                    }



                                    var_dump('09');
                                    die();
                                    //
                                }
                            } else {
                                //exce
                                // Mobile& AllMont 
                                $this->DomModel->genererPDF($tabExterne);
                                //echo 'ie ambany 500000';
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                        $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);

                                        $this->DomModel->InsertDom($tabInsertionBdExterne);

                                        var_dump('07');
                                        die();
                                    } else {
                                        $message = "Merci de Mettre les pièce jointes en PDF";

                                        echo $this->alertRedirection($message, $chemin);
                                    }
                                } else {
                                    $message = "Merci de Mettre les pièce jointes";

                                    echo $this->alertRedirection($message, $chemin);
                                }

                                var_dump('10');
                                die();


                                //
                            } //chevauchement
                        } //excep
                        //


                        $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                        // nvl date 
                        $DDForm = strtotime($DateDebut);
                        $DFForm = strtotime($DateFin);
                        if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                            //echo 'non null 2';
                            //en cours
                            $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                            $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                            if (($DDForm >=  $DD && $DDForm <= $DF) && ($DFForm >= $DD && $DFForm <= $DF)) {

                                $message = "Cette personne a déja une mission enregistrée sur ces dates, vérifier SVP!";

                                echo $this->alertRedirection($message, $chemin);
                            } else {
                                //comme d'hab

                                //
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    // var_dump($_FILES);
                                    // var_dump($filename01, $filename02);
                                    // var_dump($_SERVER['DOCUMENT_ROOT']);
                                    $this->DomModel->genererPDF($tabExterne);
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                            $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);


                                            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);

                                            $this->DomModel->InsertDom($tabInsertionBdExterne);

                                            var_dump('06');
                                            die();
                                        } else {

                                            $message = "Merci de Mettre les pièces jointes en PDF";

                                            echo $this->alertRedirection($message, $chemin);
                                        }
                                    } else {

                                        $message = "Merci de Mettre les pièces jointes";

                                        echo $this->alertRedirection($message, $chemin);
                                    }
                                    var_dump('11');
                                    die();
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    //echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF($tabExterne);
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {


                                            $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                            $this->DomModel->InsertDom($tabInsertionBdExterne);

                                            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);

                                            var_dump('05');
                                            die();
                                        } else {

                                            $message = "Merci de Mettre les pièce jointes en PDF";

                                            echo $this->alertRedirection($message, $chemin);
                                        }
                                    } else {

                                        $message = "Merci de Mettre les pièce jointes";

                                        echo $this->alertRedirection($message, $chemin);
                                    }

                                    var_dump('12');
                                    die();
                                } //mobile & AllMont 
                                else {

                                    $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                    echo $this->alertRedirection($message, $chemin);
                                }
                                //
                                //
                            }
                        } else {
                            //exce
                            // Mobile& AllMont 

                            if ($libmodepaie !== 'MOBILE MONEY') {


                                $this->DomModel->genererPDF($tabExterne);

                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                        $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom($tabInsertionBdExterne);

                                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);

                                        var_dump('04');
                                        die();
                                    } else {

                                        $message = "Merci de Mettre les pièce jointes en PDF";

                                        echo $this->alertRedirection($message, $chemin);
                                    }
                                } else {
                                    $message = "Merci de Mettre les pièce jointes";

                                    echo $this->alertRedirection($message, $chemin);
                                }



                                var_dump('13');
                                die();
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                //echo 'ie ambany 500000';
                                // var_dump($_FILES);
                                // die();
                                $this->DomModel->genererPDF($tabExterne);

                                var_dump($filename01, $filename02);
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                        $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom($tabInsertionBdExterne);

                                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);


                                        var_dump('03');
                                        die();
                                    } else {
                                        $message = "Merci de Mettre les pièce jointes en PDF";

                                        echo $this->alertRedirection($message, $chemin);
                                    }
                                } else {
                                    $message = "Merci de Mettre les pièce jointes";

                                    echo $this->alertRedirection($message, $chemin);
                                }



                                var_dump('14');
                                die();
                            } //mobile & AllMont 
                            else {
                                $message = "Assurez vous que le Montant Total est inférieur à 500.000";

                                echo $this->alertRedirection($message, $chemin);
                            }
                            //
                        } //chevauchement
                    } else {
                        //si complement sans chevauchement
                        // Mobile& AllMont 

                        if ($libmodepaie !== 'MOBILE MONEY') {
                            $this->DomModel->genererPDF($tabExterne);
                            if (!empty($filename01) && !empty($filename02)) {
                                if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {

                                    $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom($tabInsertionBdExterne);

                                    $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);


                                    var_dump('02');
                                    die();
                                } else {

                                    $message = "Merci de Mettre les pièces jointes en PDF";

                                    echo $this->alertRedirection($message, $chemin);
                                }
                            } else {
                                $message = "Merci de Mettre les pièces jointes";

                                echo $this->alertRedirection($message, $chemin);
                            }


                            var_dump('15');
                            die();
                        } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                            //echo 'ie ambany 500000';
                            $this->DomModel->genererPDF($tabExterne);
                            //
                            if (!empty($filename01) && !empty($filename02)) {
                                if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {


                                    $FichierDom = $this->changementDossierFichierExterne($filename01, $filetemp01, $filename02, $filetemp02, $NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom($tabInsertionBdExterne);

                                    $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);


                                    var_dump('01');
                                    die();
                                } else {
                                    $message = "Merci de Mettre les pièce jointes en PDF";

                                    echo $this->alertRedirection($message, $chemin);
                                }
                            } else {
                                $message = "Merci de Mettre les pièce jointes";

                                echo $this->alertRedirection($message, $chemin);
                            }


                            var_dump('16');
                            die();
                        } //mobile & AllMont 
                        else {
                            $message = "Assurer que le Montant Total est supérieur ou égale à 500.000";

                            echo $this->alertRedirection($message, $chemin);
                        }
                        //
                    } //Type
                }
                //  1date 
            } else {
                $message = "Merci de vérifier la date début ";

                echo $this->alertRedirection($message, $chemin);
            }
            echo '<script type="text/javascript">   
                document.location.href = "/Hffintranet/index.php?action=ListDomRech";
                </script>';
        }
    }


    /**
     * Affiche dans ListDom la liste des DOM selon l'autorisation 
     */
    public function ShowListDom()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        $UserConnect = $_SESSION['user'];
        $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
        $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
        include 'Views/Principe.php';
        //Fichier d'accès All Consultat
        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . 'Hffintranet/Controler/UserAccessAll.txt';
        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            $ListDom = $this->DomModel->getListDomAll();
        } else {
            $ListDom = $this->DomModel->getListDom($UserConnect);
        }
        //

        include 'Views/DOM/ListDom.php';
    }


    /**
     * Afficher les details du Numero_DOM selectionnne dans DetailDOM  
     */
    public function DetailDOM()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if (isset($_GET['NumDom'])) {
            $NumDom = $_GET['NumDom'];
            $IdDom = $_GET['Id'];
            $UserConnect = $_SESSION['user'];
            $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
            include 'Views/Principe.php';
            $detailDom = $this->DomModel->getDetailDOMselect($NumDom, $IdDom);

            include 'Views/DOM/DetailDOM.php';
        }
    }


    /**
     * TODO : Recherche à partir d'une date  définie et statut selectionner 
     */
    public function ShowListDomRecherche()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $UserConnect = $_SESSION['user'];
        //$Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
        //$LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
        include 'Views/Principe.php';
        // $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . 'Hffintranet/Controler/UserAccessAll.txt';
        // if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
        //     $ListDomRech = $this->DomModel->getListDomRechALl();
        // } else {
        //     $ListDomRech = $this->DomModel->getListDomRech($UserConnect);
        // }
        $Statut = $this->DomModel->getListStatut();
        include 'Views/DOM/ListDomRech.php';
    }

    /**
     * @Andryrkt 
     * cette fonction transforme le tableau statut en json 
     * pour listeDomRecherche
     */
    public function listStatutController()
    {

        $statut = $this->DomModel->getListStatut();

        header("Content-type:application/json");

        echo json_encode($statut);
    }

    /**
     * @Andryrkt 
     * cette fonction transforme le tableau en json 
     * pour listeDomRecherche
     */
    public function rechercheController()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        $UserConnect = $_SESSION['user'];

        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . 'Hffintranet/Controler/UserAccessAll.txt';
        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            $array_decoded = $this->DomModel->RechercheModelAll();
        } else {
            $array_decoded = $this->DomModel->RechercheModel($UserConnect);
        }


        //var_dump($array_decoded);

        header("Content-type:application/json");

        echo json_encode($array_decoded);
    }

    /**
     * creation du débiteur (code service et service)
     */
    public function anaranaFonction()
    {
        $codeServiceIrium = $this->DomModel->RecuperationCodeEtServiceIrium();

        //var_dump($codeServiceIrium);
        header("Content-type:application/json");

        echo json_encode($codeServiceIrium);


        // if (isset($_GET['option'])) {

        //     // Récupérer la valeur de l'option sélectionnée
        //     $selectedOption = $_GET['option'];

        //     // Vérifier si l'option existe dans le tableau des données simulées
        //     if (array_key_exists($selectedOption, $codeServiceIrium)) {
        //         // Afficher le contenu correspondant à l'option sélectionnée
        //         for ($i = 0; $i < count($codeServiceIrium[$selectedOption]); $i++) {

        //             echo ' <option value="' . iconv('Windows-1252', 'UTF-8', $codeServiceIrium[$selectedOption][$i]) . '">' . iconv('Windows-1252', 'UTF-8', $codeServiceIrium[$selectedOption][$i]) . '</option>';
        //         }
        //     } else {
        //         // Gérer le cas où l'option sélectionnée n'existe pas
        //         echo 'Aucune donnée disponible pour cette option';
        //     }
        // }
    }






    // public function duplificationDOM()
    // {
    //     session_start();
    //     if (empty($_SESSION['user'])) {
    //         header("Location:/Hffintranet/index.php?action=Logout");
    //         session_destroy();
    //         exit();
    //     }

    //     $UserConnect = $_SESSION['user'];
    //     $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
    //     $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
    //     include 'Views/Principe.php';
    //     $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . 'Hffintranet/Controler/UserAccessAll.txt';
    //     // if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
    //     //     $ListDomRech = $this->DomModel->getListDomRechALl();
    //     // } else {
    //     //     $ListDomRech = $this->DomModel->getListDomRech($UserConnect);
    //     // }
    //     $Statut = $this->DomModel->getListStatut();
    //     include 'Views/DOM/ListDom_Duplifier.php';
    // }


    public function duplificationFormController()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']  === 'GET') {
            $numDom = $_GET['NumDOM'];
            $idDom = $_GET['IdDOM'];

            // var_dump($numDom, $idDom, $matricule, $check);
            // die();
            $datesyst = $this->DomModel->getDatesystem();
            $UserConnect = $_SESSION['user'];
            $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
            include 'Views/Principe.php';
            $data = $this->DomModel->DuplicaftionFormModel($numDom, $idDom);

            $matricule = $_GET['check'];
            $pattern = '/^\d{4}/';
            if (preg_match($pattern, $matricule)) {
                $statutSalarier = 'Interne';
            } else {
                $statutSalarier = 'Externe';
                $cin = explode('-', $data[0]['Matricule'])[1];
            }

            if ($data[0]['Debiteur'] === null) {
                $agentDebiteur = '';
                $serviceDebiteur = '';
            } else {
                $agentDebiteur = explode('-', $data[0]['Debiteur'])[0];
                $serviceDebiteur = explode('-', $data[0]['Debiteur'])[1];
            }

            if ($data[0]['Emetteur'] === null) {
                $agentEmetteur = $data[0]['Code_agence'] . ' ' . $data[0]['Libelle_agence'];
                $serviceEmetteur = $data[0]['Code_Service'] . ' ' . $data[0]['Libelle_service'];
            } else {
                $agentEmetteur = explode('-', $data[0]['Emetteur'])[0];
                $serviceEmetteur = explode('-', $data[0]['Emetteur'])[1];
            }



            $dateDemande = $data[0]['Date_Demande'];
            $dateDebut = date("d/m/Y", strtotime($data[0]['Date_Debut']));
            $dateFin = date("d/m/Y", strtotime($data[0]['Date_Debut']));


            if (trim($data[0]['Mode_Paiement']) === 'ESPECES') {
                if (!isset(explode(' ', trim($data[0]['Mode_Paiement']))[1]) || explode(' ', trim($data[0]['Mode_Paiement']))[1] === null || explode(' ', trim($data[0]['Mode_Paiement']))[1] === '') {
                    $modePaiement = explode(' ', trim($data[0]['Mode_Paiement']));
                    $modePaiementNumero = '';
                } else {

                    $modePaiement = explode(' ', trim($data[0]['Mode_Paiement']))[0];
                    $modePaiementNumero = explode(' ', trim($data[0]['Mode_Paiement']))[1];
                }
            } else {
                $modePaiement = explode(':', trim($data[0]['Mode_Paiement']))[0];
                $modePaiementNumero = explode(':', trim($data[0]['Mode_Paiement']))[1];
            }


            $_FILES['file01']['name'] = $data[0]['Piece_Jointe_1'];
            $_FILES['file02']['name'] = $data[0]['Piece_Jointe_1'];

            //var_dump($statutSalarier);
            // var_dump(trim($data[0]['Mode_Paiement']) === 'ESPECES');
            // var_dump($data[0]);
            // die();
            include 'Views/DOM/FormCompleDOM.php';
        }
    }

    public function duplificationFormJsonController()
    {

        $data1 = $this->DomModel->DuplicaftionFormJsonModel();

        header("Content-type:application/json");

        echo json_encode($data1);
    }
}
