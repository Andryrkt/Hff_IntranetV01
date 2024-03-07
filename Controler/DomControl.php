<?php

class DomControl
{
    public $DomModel;
    private $PersonnelModel;
    public function __construct(DomModel $DomModel)
    {
        $this->DomModel = $DomModel;
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

            $codeServices = $this->DomModel->RecuperationCodeServiceIrium();





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
            //FIN Emetteur

            //DEBITTEUR
            $codeServiceDebitteur = current(explode(' ', strtoupper($_POST['codeService'])));
            $serviceDebitteur = current(explode(' ', strtoupper($_POST['service'])));

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

            // FJ
            $extentsion = array('pdf', 'jpeg', 'jpg', 'png');
            $files01 = $_FILES["file01"];
            $file02 = $_FILES["file02"];

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

            if (strtotime($DateDebut) < strtotime($DateFin) || strtotime($DateDebut) === strtotime($DateFin)) {

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
                    //fiche Atelier
                    /*  $CodeserINT = strtoupper($Code_ServiINT); //codeService ATE
                    $serviceAtelier = 'ATE, CSP , MAS';
                    $valeur_Atelier = array_map('trim', explode(',', $serviceAtelier));
                    if (in_array($CodeserINT, $valeur_Atelier) && !empty($fiche)) {
                    } else {
                        echo '<script type="text/javascript">
                                    alert("Merci de mettre le Numéro de fiche");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                    }*/
                    //numtelMOBILE
                    /*if ($libmodepaie === 'MOBILE MONEY' && !empty($valModemob)) {
                    } else {
                        echo '<script type="text/javascript">
                        alert("Mettre le Numéro Tel");
                        document.location.href = "/Hffintranet/index.php?action=New_DOM";
                        </script>';
                    }*/
                    //echo $libmodepaie;
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
                                    echo '<script type="text/javascript">
                                    alert("Cette Personne a déja une mission enregistrée sur ces dates, vérifier SVP!");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                                } else {
                                    if (!empty($filename01) || !empty($filename02)) {
                                        echo 'avec PJ' . $filename01 . '-' . $filename02;

                                        //echo 'ie ambany 500000';
                                        $this->DomModel->genererPDF(
                                            $Devis,
                                            $Prenoms,
                                            $AllMontant,
                                            $Code_servINT,
                                            $dateS,
                                            $NumDom,
                                            $servINT,
                                            $matr,
                                            $typMiss,

                                            $Nom,
                                            $NbJ,
                                            $dateD,
                                            $heureD,
                                            $dateF,
                                            $heureF,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $numvehicul,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $libmodepaie,
                                            $mode,
                                            $codeAg_servDB,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl,
                                            $MailUser,
                                            $idemnDoit,
                                            $codeServiceDebitteur,
                                            $serviceDebitteur
                                        );
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


                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servDB,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } else {
                                        // echo 'sans PJ';

                                        // echo 'ie ambany 500000';
                                        $this->DomModel->genererPDF(
                                            $Devis,
                                            $Prenoms,
                                            $AllMontant,
                                            $Code_servINT,
                                            $dateS,
                                            $NumDom,
                                            $servINT,
                                            $matr,
                                            $typMiss,

                                            $Nom,
                                            $NbJ,
                                            $dateD,
                                            $heureD,
                                            $dateF,
                                            $heureF,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $numvehicul,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $libmodepaie,
                                            $mode,
                                            $codeAg_servDB,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl,
                                            $MailUser,
                                            $idemnDoit,
                                            $codeServiceDebitteur,
                                            $serviceDebitteur
                                        );
                                        $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servINT,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    }
                                    //
                                }
                            } else {
                                //  echo 'null';
                                // echo 'cette personne est disponnible';

                                //
                                if (!empty($filename01) || !empty($filename02)) {
                                    echo 'avec PJ' . $filename01 . '-' . $filename02;

                                    //echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_servINT,
                                        $dateS,
                                        $NumDom,
                                        $servINT,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
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


                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servDB,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } else {
                                    // echo 'sans PJ';

                                    // echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_servINT,
                                        $dateS,
                                        $NumDom,
                                        $servINT,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
                                    $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servINT,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
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
                                echo '<script type="text/javascript">
                                    alert("Cette Personne a déja une mission enregistrée sur ces dates, vérifier SVP!");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                            } else {
                                if (!empty($filename01) || !empty($filename02)) {
                                    echo 'avec PJ' . $filename01 . '-' . $filename02;

                                    //virement ou especes 
                                    if ($libmodepaie !== 'MOBILE MONEY') {
                                        $this->DomModel->genererPDF(
                                            $Devis,
                                            $Prenoms,
                                            $AllMontant,
                                            $Code_servINT,
                                            $dateS,
                                            $NumDom,
                                            $servINT,
                                            $matr,
                                            $typMiss,

                                            $Nom,
                                            $NbJ,
                                            $dateD,
                                            $heureD,
                                            $dateF,
                                            $heureF,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $numvehicul,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $libmodepaie,
                                            $mode,
                                            $codeAg_servDB,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl,
                                            $MailUser,
                                            $idemnDoit,
                                            $codeServiceDebitteur,
                                            $serviceDebitteur
                                        );
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


                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servDB,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                        //echo 'ie ambany 500000';
                                        $this->DomModel->genererPDF(
                                            $Devis,
                                            $Prenoms,
                                            $AllMontant,
                                            $Code_servINT,
                                            $dateS,
                                            $NumDom,
                                            $servINT,
                                            $matr,
                                            $typMiss,

                                            $Nom,
                                            $NbJ,
                                            $dateD,
                                            $heureD,
                                            $dateF,
                                            $heureF,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $numvehicul,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $libmodepaie,
                                            $mode,
                                            $codeAg_servDB,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl,
                                            $MailUser,
                                            $idemnDoit,
                                            $codeServiceDebitteur,
                                            $serviceDebitteur
                                        );
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


                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servDB,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } //Mobile&allMOnt
                                    else {
                                        echo '<script type="text/javascript">
                                    alert("Assurez vous que le Montant Total est inférieur à 500.000");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                                        var_dump($libmodepaie);
                                        die();
                                    }
                                    //
                                } else {
                                    // echo 'sans PJ';


                                    // sans JP
                                    if ($libmodepaie !== 'MOBILE MONEY') {
                                        echo 'io';
                                        $this->DomModel->genererPDF(
                                            $Devis,
                                            $Prenoms,
                                            $AllMontant,
                                            $Code_servINT,
                                            $dateS,
                                            $NumDom,
                                            $servINT,
                                            $matr,
                                            $typMiss,

                                            $Nom,
                                            $NbJ,
                                            $dateD,
                                            $heureD,
                                            $dateF,
                                            $heureF,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $numvehicul,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $libmodepaie,
                                            $mode,
                                            $codeAg_servDB,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl,
                                            $MailUser,
                                            $idemnDoit,
                                            $codeServiceDebitteur,
                                            $serviceDebitteur
                                        );
                                        $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servINT,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                        // echo 'ie ambany 500000';
                                        $this->DomModel->genererPDF(
                                            $Devis,
                                            $Prenoms,
                                            $AllMontant,
                                            $Code_servINT,
                                            $dateS,
                                            $NumDom,
                                            $servINT,
                                            $matr,
                                            $typMiss,

                                            $Nom,
                                            $NbJ,
                                            $dateD,
                                            $heureD,
                                            $dateF,
                                            $heureF,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $numvehicul,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $libmodepaie,
                                            $mode,
                                            $codeAg_servDB,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl,
                                            $MailUser,
                                            $idemnDoit,
                                            $codeServiceDebitteur,
                                            $serviceDebitteur
                                        );
                                        $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servINT,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } //mobile&allMont 
                                    else {
                                        echo '<script type="text/javascript">
                                alert("Assurez vous que le Montant Total est inférieur à 500.000");
                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
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
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_servINT,
                                        $dateS,
                                        $NumDom,
                                        $servINT,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
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


                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servDB,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    //echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_servINT,
                                        $dateS,
                                        $NumDom,
                                        $servINT,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
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


                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servDB,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } //Mobile&allMOnt
                                else {
                                    var_dump($libmodepaie);
                                    die();
                                    echo '<script type="text/javascript">
                                alert("Assurez vous que le Montant Total est inférieur à 500.000");
                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
                                }
                                //
                            } else {
                                // echo 'sans PJ';

                                //
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_servINT,
                                        $dateS,
                                        $NumDom,
                                        $servINT,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
                                    $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servINT,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    // echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_servINT,
                                        $dateS,
                                        $NumDom,
                                        $servINT,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
                                    $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servINT,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } //mobile&allMont 
                                else {
                                    echo '<script type="text/javascript">
                            alert("Assurez vous que le Montant Total est inférieur à 500.000");
                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
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
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_servINT,
                                    $dateS,
                                    $NumDom,
                                    $servINT,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
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


                                $this->DomModel->InsertDom(
                                    $NumDom,
                                    $dateSystem,
                                    $typMiss,

                                    $matr,
                                    $usersession,
                                    $codeAg_servDB,
                                    $DateDebut,
                                    $heureD,
                                    $DateFin,
                                    $heureF,
                                    $NbJ,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $AllMontant,
                                    $modeDB,
                                    $valModemob,
                                    $Nom,
                                    $Prenoms,
                                    $Devis,
                                    $filename01,
                                    $filename02,
                                    $usersession,
                                    $LibelleCodeAg_ServDB,
                                    $numvehicul,
                                    $idemnDoit,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl
                                );
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                //echo 'ie ambany 500000';
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_servINT,
                                    $dateS,
                                    $NumDom,
                                    $servINT,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
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


                                $this->DomModel->InsertDom(
                                    $NumDom,
                                    $dateSystem,
                                    $typMiss,

                                    $matr,
                                    $usersession,
                                    $codeAg_servDB,
                                    $DateDebut,
                                    $heureD,
                                    $DateFin,
                                    $heureF,
                                    $NbJ,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $AllMontant,
                                    $modeDB,
                                    $valModemob,
                                    $Nom,
                                    $Prenoms,
                                    $Devis,
                                    $filename01,
                                    $filename02,
                                    $usersession,
                                    $LibelleCodeAg_ServDB,
                                    $numvehicul,
                                    $idemnDoit,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl
                                );
                            } //Mobile&allMOnt
                            else {
                                echo '<script type="text/javascript">
                                alert("Assurez vous que le Montant Total est inférieur à 500.000");
                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
                            }
                            //
                        } else {
                            // echo 'sans PJ' test gut; 
                            //
                            if ($libmodepaie !== 'MOBILE MONEY') {
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_servINT,
                                    $dateS,
                                    $NumDom,
                                    $servINT,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
                                $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                $this->DomModel->InsertDom(
                                    $NumDom,
                                    $dateSystem,
                                    $typMiss,

                                    $matr,
                                    $usersession,
                                    $codeAg_servINT,
                                    $DateDebut,
                                    $heureD,
                                    $DateFin,
                                    $heureF,
                                    $NbJ,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $AllMontant,
                                    $modeDB,
                                    $valModemob,
                                    $Nom,
                                    $Prenoms,
                                    $Devis,
                                    $filename01,
                                    $filename02,
                                    $usersession,
                                    $LibelleCodeAg_ServDB,
                                    $numvehicul,
                                    $idemnDoit,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl
                                );
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                // echo 'ie ambany 500000';
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_servINT,
                                    $dateS,
                                    $NumDom,
                                    $servINT,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
                                $this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                                $this->DomModel->InsertDom(
                                    $NumDom,
                                    $dateSystem,
                                    $typMiss,

                                    $matr,
                                    $usersession,
                                    $codeAg_servINT,
                                    $DateDebut,
                                    $heureD,
                                    $DateFin,
                                    $heureF,
                                    $NbJ,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $AllMontant,
                                    $modeDB,
                                    $valModemob,
                                    $Nom,
                                    $Prenoms,
                                    $Devis,
                                    $filename01,
                                    $filename02,
                                    $usersession,
                                    $LibelleCodeAg_ServDB,
                                    $numvehicul,
                                    $idemnDoit,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl
                                );
                            } //mobile&allMont 
                            else {
                                echo '<script type="text/javascript">
                            alert("Assurez vous que le Montant Total est inférieur à 500.000");
                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
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
                    if ($typMiss !== 'COMPLEMENT') {
                        //si frais execption
                        if ($typMiss === 'FRAIS EXCEPTIONNEL' && $Devis !== 'MGA') {

                            $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                            // nvl date 
                            $DDForm = strtotime($DateDebut);
                            $DFForm = strtotime($DateFin);
                            if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                                echo 'non null';
                                //en cours
                                $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                                $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                                if (($DDForm >=  $DD && $DDForm <= $DF) && ($DFForm >= $DD && $DFForm <= $DF)) {
                                    echo '<script type="text/javascript">
                                        alert("Cette personne a déja une mission enregistrée sur ces dates, vérifier SVP!");
                                        document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                        </script>';
                                } else {
                                    //comme d'hab

                                    //echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_serv,
                                        $dateS,
                                        $NumDom,
                                        $serv,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                            $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                            move_uploaded_file($filetemp01, $Upload_file);
                                            $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                            move_uploaded_file($filetemp02, $Upload_file02);
                                            $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                            $this->DomModel->InsertDom(
                                                $NumDom,
                                                $dateSystem,
                                                $typMiss,

                                                $matr,
                                                $usersession,
                                                $codeAg_servDB,
                                                $DateDebut,
                                                $heureD,
                                                $DateFin,
                                                $heureF,
                                                $NbJ,
                                                $motif,
                                                $Client,
                                                $fiche,
                                                $lieu,
                                                $vehicule,
                                                $idemn,
                                                $totalIdemn,
                                                $motifdep01,
                                                $montdep01,
                                                $motifdep02,
                                                $montdep02,
                                                $motifdep03,
                                                $montdep03,
                                                $totaldep,
                                                $AllMontant,
                                                $modeDB,
                                                $valModemob,
                                                $Nom,
                                                $Prenoms,
                                                $Devis,
                                                $filename01,
                                                $filename02,
                                                $usersession,
                                                $LibelleCodeAg_ServDB,
                                                $numvehicul,
                                                $idemnDoit,
                                                $CategoriePers,
                                                $Site,
                                                $Idemn_depl
                                            );
                                        } else {
                                            echo '<script type="text/javascript">
                                                alert("Merci de Mettre les pièce jointes en PDF");
                                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                                </script>';
                                        }
                                    } else {
                                        echo '<script type="text/javascript">
                                    alert("Merci de Mettre les pièce jointes");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                                    }

                                    //
                                }
                            } else {
                                //exce
                                // Mobile& AllMont 

                                //echo 'ie ambany 500000';
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_serv,
                                    $dateS,
                                    $NumDom,
                                    $serv,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                        move_uploaded_file($filetemp01, $Upload_file);
                                        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                        move_uploaded_file($filetemp02, $Upload_file02);
                                        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servDB,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } else {
                                        echo '<script type="text/javascript">
                                            alert("Merci de Mettre les pièce jointes en PDF");
                                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                            </script>';
                                    }
                                } else {
                                    echo '<script type="text/javascript">
                                alert("Merci de Mettre les pièce jointes");
                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
                                }


                                //
                            } //chevauchement
                        } //excep
                        //

                        //fiche Atelier
                        /* $CodeserExt = strtoupper($Code_Servi); //codeService ATE
                        $serviceAtelier = 'ATE, CSP , MAS';
                        $valeur_Atelier = array_map('trim', explode(',', $serviceAtelier));
                        if (in_array($CodeserExt, $valeur_Atelier) && !empty($fiche)) {
                        } else {
                            echo '<script type="text/javascript">
                                    alert("Merci de mettre le Numéro de fiche");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                            
                        }*/
                        /*  if ($libmodepaie === 'MOBILE MONEY' && !empty($valModemob)) {
                        } else {
                            echo '<script type="text/javascript">
                                    alert("Mettre le Numéro Tel");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                        }*/
                        $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                        // nvl date 
                        $DDForm = strtotime($DateDebut);
                        $DFForm = strtotime($DateFin);
                        if ($DomMaxMinDate !== null  && !empty($DomMaxMinDate)) {
                            echo 'non null';
                            //en cours
                            $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                            $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                            if (($DDForm >=  $DD && $DDForm <= $DF) && ($DFForm >= $DD && $DFForm <= $DF)) {
                                echo '<script type="text/javascript">
                                    alert("Cette personne a déja une mission enregistrée sur ces dates, vérifier SVP!");
                                    document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                    </script>';
                            } else {
                                //comme d'hab

                                //
                                if ($libmodepaie !== 'MOBILE MONEY') {
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_serv,
                                        $dateS,
                                        $NumDom,
                                        $serv,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                            $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                            move_uploaded_file($filetemp01, $Upload_file);
                                            $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                            move_uploaded_file($filetemp02, $Upload_file02);
                                            $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                            $this->DomModel->InsertDom(
                                                $NumDom,
                                                $dateSystem,
                                                $typMiss,

                                                $matr,
                                                $usersession,
                                                $codeAg_servDB,
                                                $DateDebut,
                                                $heureD,
                                                $DateFin,
                                                $heureF,
                                                $NbJ,
                                                $motif,
                                                $Client,
                                                $fiche,
                                                $lieu,
                                                $vehicule,
                                                $idemn,
                                                $totalIdemn,
                                                $motifdep01,
                                                $montdep01,
                                                $motifdep02,
                                                $montdep02,
                                                $motifdep03,
                                                $montdep03,
                                                $totaldep,
                                                $AllMontant,
                                                $modeDB,
                                                $valModemob,
                                                $Nom,
                                                $Prenoms,
                                                $Devis,
                                                $filename01,
                                                $filename02,
                                                $usersession,
                                                $LibelleCodeAg_ServDB,
                                                $numvehicul,
                                                $idemnDoit,
                                                $CategoriePers,
                                                $Site,
                                                $Idemn_depl
                                            );
                                        } else {
                                            echo '<script type="text/javascript">
                                            alert("Merci de Mettre les pièce jointes en PDF");
                                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                            </script>';
                                        }
                                    } else {
                                        echo '<script type="text/javascript">
                                alert("Merci de Mettre les pièce jointes");
                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
                                    }
                                } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                    //echo 'ie ambany 500000';
                                    $this->DomModel->genererPDF(
                                        $Devis,
                                        $Prenoms,
                                        $AllMontant,
                                        $Code_serv,
                                        $dateS,
                                        $NumDom,
                                        $serv,
                                        $matr,
                                        $typMiss,

                                        $Nom,
                                        $NbJ,
                                        $dateD,
                                        $heureD,
                                        $dateF,
                                        $heureF,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $numvehicul,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $libmodepaie,
                                        $mode,
                                        $codeAg_servDB,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl,
                                        $MailUser,
                                        $idemnDoit,
                                        $codeServiceDebitteur,
                                        $serviceDebitteur
                                    );
                                    //
                                    if (!empty($filename01) && !empty($filename02)) {
                                        if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                            $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                            move_uploaded_file($filetemp01, $Upload_file);
                                            $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                            move_uploaded_file($filetemp02, $Upload_file02);
                                            $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                            $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                            $this->DomModel->InsertDom(
                                                $NumDom,
                                                $dateSystem,
                                                $typMiss,

                                                $matr,
                                                $usersession,
                                                $codeAg_servDB,
                                                $DateDebut,
                                                $heureD,
                                                $DateFin,
                                                $heureF,
                                                $NbJ,
                                                $motif,
                                                $Client,
                                                $fiche,
                                                $lieu,
                                                $vehicule,
                                                $idemn,
                                                $totalIdemn,
                                                $motifdep01,
                                                $montdep01,
                                                $motifdep02,
                                                $montdep02,
                                                $motifdep03,
                                                $montdep03,
                                                $totaldep,
                                                $AllMontant,
                                                $modeDB,
                                                $valModemob,
                                                $Nom,
                                                $Prenoms,
                                                $Devis,
                                                $filename01,
                                                $filename02,
                                                $usersession,
                                                $LibelleCodeAg_ServDB,
                                                $numvehicul,
                                                $idemnDoit,
                                                $CategoriePers,
                                                $Site,
                                                $Idemn_depl
                                            );
                                        } else {
                                            echo '<script type="text/javascript">
                                            alert("Merci de Mettre les pièce jointes en PDF");
                                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                            </script>';
                                        }
                                    } else {
                                        echo '<script type="text/javascript">
                                alert("Merci de Mettre les pièce jointes");
                                document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
                                    }
                                } //mobile & AllMont 
                                else {
                                    echo '<script type="text/javascript">
                                     alert("Assurez vous que le Montant Total est inférieur à 500.000");
                                     document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                </script>';
                                }
                                //
                                //
                            }
                        } else {
                            //exce
                            // Mobile& AllMont 

                            if ($libmodepaie !== 'MOBILE MONEY') {
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_serv,
                                    $dateS,
                                    $NumDom,
                                    $serv,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                        move_uploaded_file($filetemp01, $Upload_file);
                                        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                        move_uploaded_file($filetemp02, $Upload_file02);
                                        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servDB,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } else {
                                        echo '<script type="text/javascript">
                                        alert("Merci de Mettre les pièce jointes en PDF");
                                        document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                        </script>';
                                    }
                                } else {
                                    echo '<script type="text/javascript">
                            alert("Merci de Mettre les pièce jointes");
                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
                                }
                            } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                                //echo 'ie ambany 500000';
                                $this->DomModel->genererPDF(
                                    $Devis,
                                    $Prenoms,
                                    $AllMontant,
                                    $Code_serv,
                                    $dateS,
                                    $NumDom,
                                    $serv,
                                    $matr,
                                    $typMiss,

                                    $Nom,
                                    $NbJ,
                                    $dateD,
                                    $heureD,
                                    $dateF,
                                    $heureF,
                                    $motif,
                                    $Client,
                                    $fiche,
                                    $lieu,
                                    $vehicule,
                                    $numvehicul,
                                    $idemn,
                                    $totalIdemn,
                                    $motifdep01,
                                    $montdep01,
                                    $motifdep02,
                                    $montdep02,
                                    $motifdep03,
                                    $montdep03,
                                    $totaldep,
                                    $libmodepaie,
                                    $mode,
                                    $codeAg_servDB,
                                    $CategoriePers,
                                    $Site,
                                    $Idemn_depl,
                                    $MailUser,
                                    $idemnDoit,
                                    $codeServiceDebitteur,
                                    $serviceDebitteur
                                );
                                //
                                if (!empty($filename01) && !empty($filename02)) {
                                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                        move_uploaded_file($filetemp01, $Upload_file);
                                        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                        move_uploaded_file($filetemp02, $Upload_file02);
                                        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                        $this->DomModel->InsertDom(
                                            $NumDom,
                                            $dateSystem,
                                            $typMiss,

                                            $matr,
                                            $usersession,
                                            $codeAg_servDB,
                                            $DateDebut,
                                            $heureD,
                                            $DateFin,
                                            $heureF,
                                            $NbJ,
                                            $motif,
                                            $Client,
                                            $fiche,
                                            $lieu,
                                            $vehicule,
                                            $idemn,
                                            $totalIdemn,
                                            $motifdep01,
                                            $montdep01,
                                            $motifdep02,
                                            $montdep02,
                                            $motifdep03,
                                            $montdep03,
                                            $totaldep,
                                            $AllMontant,
                                            $modeDB,
                                            $valModemob,
                                            $Nom,
                                            $Prenoms,
                                            $Devis,
                                            $filename01,
                                            $filename02,
                                            $usersession,
                                            $LibelleCodeAg_ServDB,
                                            $numvehicul,
                                            $idemnDoit,
                                            $CategoriePers,
                                            $Site,
                                            $Idemn_depl
                                        );
                                    } else {
                                        echo '<script type="text/javascript">
                                        alert("Merci de Mettre les pièce jointes en PDF");
                                        document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                        </script>';
                                    }
                                } else {
                                    echo '<script type="text/javascript">
                            alert("Merci de Mettre les pièce jointes");
                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
                                }
                            } //mobile & AllMont 
                            else {
                                echo '<script type="text/javascript">
                                 alert("Assurez vous que le Montant Total est inférieur à 500.000");
                                 document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
                            }
                            //
                        } //chevauchement
                    } else {
                        //si complement sans chevauchement
                        // Mobile& AllMont 

                        if ($libmodepaie !== 'MOBILE MONEY') {
                            $this->DomModel->genererPDF(
                                $Devis,
                                $Prenoms,
                                $AllMontant,
                                $Code_serv,
                                $dateS,
                                $NumDom,
                                $serv,
                                $matr,
                                $typMiss,

                                $Nom,
                                $NbJ,
                                $dateD,
                                $heureD,
                                $dateF,
                                $heureF,
                                $motif,
                                $Client,
                                $fiche,
                                $lieu,
                                $vehicule,
                                $numvehicul,
                                $idemn,
                                $totalIdemn,
                                $motifdep01,
                                $montdep01,
                                $motifdep02,
                                $montdep02,
                                $motifdep03,
                                $montdep03,
                                $totaldep,
                                $libmodepaie,
                                $mode,
                                $codeAg_servDB,
                                $CategoriePers,
                                $Site,
                                $Idemn_depl,
                                $MailUser,
                                $idemnDoit,
                                $codeServiceDebitteur,
                                $serviceDebitteur
                            );
                            //
                            if (!empty($filename01) && !empty($filename02)) {
                                if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                    $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                    move_uploaded_file($filetemp01, $Upload_file);
                                    $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                    move_uploaded_file($filetemp02, $Upload_file02);
                                    $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                    $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servDB,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } else {
                                    echo '<script type="text/javascript">
                                        alert("Merci de Mettre les pièce jointes en PDF");
                                        document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                        </script>';
                                }
                            } else {
                                echo '<script type="text/javascript">
                            alert("Merci de Mettre les pièce jointes");
                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
                            }
                        } elseif ($libmodepaie === 'MOBILE MONEY' && $AllMont <= 500000) {
                            //echo 'ie ambany 500000';
                            $this->DomModel->genererPDF(
                                $Devis,
                                $Prenoms,
                                $AllMontant,
                                $Code_serv,
                                $dateS,
                                $NumDom,
                                $serv,
                                $matr,
                                $typMiss,

                                $Nom,
                                $NbJ,
                                $dateD,
                                $heureD,
                                $dateF,
                                $heureF,
                                $motif,
                                $Client,
                                $fiche,
                                $lieu,
                                $vehicule,
                                $numvehicul,
                                $idemn,
                                $totalIdemn,
                                $motifdep01,
                                $montdep01,
                                $motifdep02,
                                $montdep02,
                                $motifdep03,
                                $montdep03,
                                $totaldep,
                                $libmodepaie,
                                $mode,
                                $codeAg_servDB,
                                $CategoriePers,
                                $Site,
                                $Idemn_depl,
                                $MailUser,
                                $idemnDoit,
                                $codeServiceDebitteur,
                                $serviceDebitteur
                            );
                            //
                            if (!empty($filename01) && !empty($filename02)) {
                                if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                                    $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                                    move_uploaded_file($filetemp01, $Upload_file);
                                    $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                                    move_uploaded_file($filetemp02, $Upload_file02);
                                    $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';

                                    $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                                    $this->DomModel->InsertDom(
                                        $NumDom,
                                        $dateSystem,
                                        $typMiss,

                                        $matr,
                                        $usersession,
                                        $codeAg_servDB,
                                        $DateDebut,
                                        $heureD,
                                        $DateFin,
                                        $heureF,
                                        $NbJ,
                                        $motif,
                                        $Client,
                                        $fiche,
                                        $lieu,
                                        $vehicule,
                                        $idemn,
                                        $totalIdemn,
                                        $motifdep01,
                                        $montdep01,
                                        $motifdep02,
                                        $montdep02,
                                        $motifdep03,
                                        $montdep03,
                                        $totaldep,
                                        $AllMontant,
                                        $modeDB,
                                        $valModemob,
                                        $Nom,
                                        $Prenoms,
                                        $Devis,
                                        $filename01,
                                        $filename02,
                                        $usersession,
                                        $LibelleCodeAg_ServDB,
                                        $numvehicul,
                                        $idemnDoit,
                                        $CategoriePers,
                                        $Site,
                                        $Idemn_depl
                                    );
                                } else {
                                    echo '<script type="text/javascript">
                                        alert("Merci de Mettre les pièce jointes en PDF");
                                        document.location.href = "/Hffintranet/index.php?action=New_DOM";
                                        </script>';
                                }
                            } else {
                                echo '<script type="text/javascript">
                            alert("Merci de Mettre les pièce jointes");
                            document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
                            }
                        } //mobile & AllMont 
                        else {
                            echo '<script type="text/javascript">
                                 alert("Assurer que le Montant Total est supérieur ou égale à 500.000");
                                 document.location.href = "/Hffintranet/index.php?action=New_DOM";
                            </script>';
                        }
                        //
                    } //Type
                }
                //  1date 
            } else {
                echo '<script type="text/javascript">
                alert("Merci de vérifier la date début ");
                 document.location.href = "/Hffintranet/index.php?action=New_DOM";
                </script>';
            }
            echo '<script type="text/javascript">   
                document.location.href = "/Hffintranet/index.php?action=ListDom";
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
        $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
        $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
        include 'Views/Principe.php';
        $FichierAccès = $_SERVER['DOCUMENT_ROOT'] . 'Hffintranet/Controler/UserAccessAll.txt';
        if (strpos(file_get_contents($FichierAccès), $UserConnect) !== false) {
            $ListDomRech = $this->DomModel->getListDomRechALl();
        } else {
            $ListDomRech = $this->DomModel->getListDomRech($UserConnect);
        }
        $Statut = $this->DomModel->getListStatut();
        include 'Views/DOM/ListDomRech.php';
    }

    public function anaranaFonction()
    {
        $codeServiceIrium = $this->DomModel->RecuperationCodeEtServiceIrium();

        if (isset($_GET['option'])) {

            // Récupérer la valeur de l'option sélectionnée
            $selectedOption = $_GET['option'];

            // Vérifier si l'option existe dans le tableau des données simulées
            if (array_key_exists($selectedOption, $codeServiceIrium)) {
                // Afficher le contenu correspondant à l'option sélectionnée
                for ($i = 0; $i < count($codeServiceIrium[$selectedOption]); $i++) {

                    echo ' <option value="' . iconv('Windows-1252', 'UTF-8', $codeServiceIrium[$selectedOption][$i]) . '">' . iconv('Windows-1252', 'UTF-8', $codeServiceIrium[$selectedOption][$i]) . '</option>';
                }
            } else {
                // Gérer le cas où l'option sélectionnée n'existe pas
                echo 'Aucune donnée disponible pour cette option';
            }
        }
    }
}
