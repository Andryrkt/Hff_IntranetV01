<?php

class DomControl
{
    public $DomModel;
    private $PersonnelModel;
    public function __construct(DomModel $DomModel)
    {
        $this->DomModel = $DomModel;
    }
    //
    public function selectCatg()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }
        $valeurSelect = $_POST['typeMission'];
        $InforCatge = $this->DomModel->CategPers($valeurSelect);
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
        $RentalCatg .= "<select id='categRental' class='form-select' name='categRental'>";
        foreach ($CatgeRental as $Catg) {
            $categ = $Catg['Catg'];
            $info = iconv('Windows-1252', 'UTF-8', $categ);

            $RentalCatg .= "<option value='$info'>$info</option>";
        }
        $RentalCatg .= "</select>";

        echo $RentalCatg;
    }
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
        $MutSiteRental = $this->DomModel->SelectSiteRental($TypeMiss,$CatgPersSelect);
        $response1 = "<label for='SiteRental' class='label-form' id='labSiteRental'> Site:</label>";
        $response1 .="<select id='SiteRental' class='form-select' name='SiteRental'>";
        foreach ($MutSiteRental as $Site) {
            $Site = $Site['Destination'];
            $info = iconv('Windows-1252', 'UTF-8', $Site);

            $response1 .= "<option value='$info'>$info</option>";
        }
        $response1 .= "</select>";

        echo $response1;
    }
    //

    public function showFormDOM()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hffintranet/index.php?action=Logout");
            session_destroy();
            exit();
        }

        try {

            $NumDOM = $this->DomModel->DOM_autoINcriment();
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
            $NumDom = $_POST['NumDOM'];
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
            //Serv_Ext
            $Code_serv = $_POST['Serv']; //80 Admin
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
            $Code_servINT = $_POST['ServINt'];
            $codeINT = explode(" ", $Code_servINT);
            $code_AgenceINT = strtolower(current($codeINT)); //80 
            $AgenceINT = strtolower(end($codeINT)); // Admin
            $servINT = $_POST['LibServINT']; //INF info 
            $codeservINT = explode(" ", $servINT);
            $Code_ServiINT = strtolower(current($codeservINT)); // INF
            $ServiINT = strtolower(end($codeservINT)); // INfo
            $codeAg_servINT = $code_AgenceINT . $Code_ServiINT; //80-INF
            $LibelleCodeAg_ServINT = $AgenceINT . "-" . $ServiINT;

            $dateSystem = $_POST['datesyst'];
            $dateS = date("d/m/Y", strtotime($_POST['datesyst']));
            $NumDom = $_POST['NumDOM'];
            $Devis = $_POST['Devis'];

            $typMiss = $_POST['typeMission'];
            //$autrTyp = $_POST['AutreType'];

            $DateDebut = $_POST['dateDebut'];
            $dateD = date("d/m/Y", strtotime($DateDebut));
            $heureD = $_POST['heureDebut'];
            $DateFin = $_POST['dateFin'];
            $dateF = date("d/m/Y", strtotime($DateFin));
            $heureF = $_POST['heureFin'];
            $NbJ = $_POST['Nbjour'];
            $motif =  str_replace("'", "''", $_POST['motif']);

            $Client = str_replace("'", "''", $_POST['client']);
            $fiche = $_POST['fiche'];
            $lieu = str_replace("'", "''", $_POST['lieuInterv']);
            $vehicule = $_POST['vehicule'];
            $numvehicul = $_POST['N_vehicule'];
            $idemn = $_POST['idemForfait'];
            $totalIdemn = $_POST['TotalidemForfait'];
            $motifdep01 = str_replace("'", "''", $_POST['MotifAutredep']);
            $montdep01 = $_POST['Autredep1'];
            $motifdep02 = str_replace("'", "''", $_POST['MotifAutredep2']);
            $montdep02 = $_POST['Autredep2'];
            $motifdep03 = str_replace("'", "''", $_POST['MotifAutredep3']);
            $montdep03 = $_POST['Autredep3'];
            $totaldep = $_POST['TotalAutredep'];
            $libmodepaie = $_POST['modepaie'];
            $valModesp = $_POST['valModesp'];
            $valModemob = $_POST['valModemob'];
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

            // 1

            if (strtotime($DateDebut) < strtotime($DateFin)) {

                if ($checkext === "Interne") {
                    $Nom =  $NomINt;
                    $Prenoms = $PrenomsINt;
                    $matr = $matrInt;
                    $codeAg_servDB = $codeAg_servINT;
                    $LibelleCodeAg_ServDB = $LibelleCodeAg_ServINT;

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
                    //
                    /* $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                    // nvl date 
                    $DDForm = strtotime($DateDebut);
                    $DFForm = strtotime($DateFin);
                    if ($DomMaxMinDate !== NULL) {
                        echo 'non null';
                        //en cours
                        $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                        $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                        if ($DDForm <= $DF) {
                            echo '<script type="text/javascript">
                                    alert("Personne en cours de mission, Non disponible");
                                    document.location.href = "/Hffintranet/index.php?action=checkMatricule&NumDomget='
                                . $NumDom . '&code_service=' . $Code_servINT . '&Matricule=' . $matr . '&service='
                                . $servINT . '&check=' . $checkext . '&user=' . $_SESSION['user'] . '&TypeMission=' . $typMiss . ' ";
                                    </script>';
                        }
                    } /*else {*/
                    //echo 'null';
                    // echo 'cette personne est disponnible';

                    //
                    if (!empty($filename01) || !empty($filename02)) {
                        echo 'avec PJ' . $filename01 . '-' . $filename02;
                        /* $this->DomModel->genererPDF(
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
                                $codeAg_servDB
                            );*/
                        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename01;
                        move_uploaded_file($filetemp01, $Upload_file);
                        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Controler/pdf/' . $filename02;
                        move_uploaded_file($filetemp02, $Upload_file02);
                        $FichierDom = $NumDom . '_' . $codeAg_servDB . '.pdf';
                        if (!empty($filename02)) {
                            echo 'fichier02';
                            //$this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                        } else {
                            // $this->DomModel->genererFusion1($FichierDom, $filename01);
                            echo 'echo non';
                        }


                        /* $this->DomModel->InsertDom(
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
                                $numvehicul
                            );*/
                    } else {
                        echo 'sans PJ';
                        /* $this->DomModel->genererPDF(
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
                                $codeAg_servDB
                            );*/
                        //$this->DomModel->copyInterneToDOXCUWARE($NumDom, $codeAg_servDB);

                        /* $this->DomModel->InsertDom(
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
                                $numvehicul
                            );*/
                    }
                    //
                    //}
                } else {
                    $codeAg_servDB = $codeAg_serv;
                    $LibelleCodeAg_ServDB = $LibelleCodeAg_Serv;
                    $Nom = $Nomext;
                    $Prenoms = $PrenomExt;
                    $matr = "XER00 -" . $MatrExt . " - TEMPORAIRE";

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
                    //
                    /* $DomMaxMinDate = $this->DomModel->getInfoDOMMatrSelet($matr);
                    // nvl date 
                    $DDForm = strtotime($DateDebut);
                    $DFForm = strtotime($DateFin);
                    if ($DomMaxMinDate !== NULL) {
                        echo 'non null';
                        //en cours
                        $DD = strtotime($DomMaxMinDate[0]['DateDebutMin']);
                        $DF = strtotime($DomMaxMinDate[0]['DateFinMax']);
                        if ($DDForm <= $DF) {
                            echo '<script type="text/javascript">
                                    alert("Personne en cours de mission, Non disponible");
                                    document.location.href = "/Hffintranet/index.php?action=checkMatricule&NumDomget='
                                . $NumDom . '&code_service=' . $Code_serv . '&Matricule=' . $matr . '&service='
                                . $serv . '&check=' . $checkext . '&user=' . $_SESSION['user'] . '&TypeMission=' . $typMiss . '&nom=' . $Nom . '&prenoms=' . $Prenoms . '&cin=' . $MatrExt .
                                '";
                                    </script>';
                        }
                    }*/ // else {
                    //exce
                    /* $this->DomModel->genererPDF(
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

                    );*/
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
                                $numvehicul
                            );
                        } else {
                            echo '<script type="text/javascript">
                            alert("Merci de Mettre les pièce jointes en PDF");
                            document.location.href = "/Hffintranet/index.php?action=checkMatricule&NumDomget='
                                . $NumDom . '&code_service=' . $Code_serv . '&Matricule=' . $matr . '&service='
                                . $serv . '&check=' . $checkext . '&user=' . $_SESSION['user'] . '&TypeMission=' . $typMiss . '&nom=' . $Nom . '&prenoms=' . $Prenoms . '&cin=' . $MatrExt .
                                '";
                            </script>';
                            /*echo '<script type="text/javascript">
                    alert("Merci de Mettre les pièce jointes en PDF");
                    </script>';*/
                        }
                    } else {
                        echo '<script type="text/javascript">
                            alert("Merci de Mettre les pièce jointes");
                            document.location.href = "/Hffintranet/index.php?action=checkMatricule&NumDomget='
                            . $NumDom . '&code_service=' . $Code_serv . '&Matricule=' . $matr . '&service='
                            . $serv . '&check=' . $checkext . '&user=' . $_SESSION['user'] . '&TypeMission=' . $typMiss . '&nom=' . $Nom . '&prenoms=' . $Prenoms . '&cin=' . $MatrExt .
                            '";
                            </script>';
                        /* echo '<script type="text/javascript">
                    alert("Merci de Mettre les pièce jointes");
                    </script>';*/
                    }
                    //
                    // }//
                }
                //  1
            } else {
                echo '<script type="text/javascript">
                alert("Merci de vérifier la date début ");
                document.location.href = "/Hffintranet/index.php?action=showFormDOM";
                </script>';
            }
            echo '<script type="text/javascript">   
                alert("Demande OM Envoyer");
                document.location.href = "/Hffintranet/index.php?action=ListDom";
                </script>';
        }
    }
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
        $ListDom = $this->DomModel->getListDom($UserConnect);
        include 'Views/DOM/ListDom.php';
    }
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
            $UserConnect = $_SESSION['user'];
            $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
            include 'Views/Principe.php';
            $detailDom = $this->DomModel->getDetailDOMselect($NumDom);
            include 'Views/DOM/DetailDOM.php';
        }
    }
}
