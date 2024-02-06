<?php

class DomControl
{
    public $DomModel;
    private $PersonnelModel;
    public function __construct(DomModel $DomModel)
    {
        $this->DomModel = $DomModel;
    }


    public function showFormDOM()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
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
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }
        if ($_SERVER['REQUEST_METHOD']  === 'POST') {
            $NumDom = $_POST['NumDOM'];
            $code_service = $_POST['Serv'];
            $service = $_POST['LibServ'];
            $typeMission = $_POST['typeMission'];
            $autrtype = $_POST['AutreType'];
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
    }

    public function EnvoieImprimeDom()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
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
            //
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
            $dateSystem = $_POST['datesyst'];
            $dateS = date("d/m/Y", strtotime($_POST['datesyst']));
            $NumDom = $_POST['NumDOM'];
            $Devis = $_POST['Devis'];

            $typMiss = $_POST['typeMission'];
            $autrTyp = $_POST['AutreType'];

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



            if ($checkext === "Interne") {
                $Nom =  $NomINt;
                $Prenoms = $PrenomsINt;
                $matr = $matrInt;


                if ($libmodepaie === "ESPECES") {
                    $mode =  $valModesp;
                    $modeDB = $valModesp;
                }
                if ($libmodepaie === "MOBILE MONEY") {
                    $mode =  "TEL " . $valModemob;
                    $modeDB = $valModemob;
                }
                if ($libmodepaie === "VIREMENT BANCAIRE") {
                    $mode =  "CPT " . $valModecompt;
                    $modeDB = $valModecompt;
                }

                //exce
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
                    $autrTyp,
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
                    $codeAg_serv
                );
                $this->DomModel->copyInterneToDOXCUWARE($NumDom,$codeAg_serv);
                $this->DomModel->InsertDom(
                    $NumDom,
                    $dateSystem,
                    $typMiss,
                    $autrTyp,
                    $matr,
                    $usersession,
                    $codeAg_serv,
                    $DateDebut,
                    $heureD,
                    $DateFin,
                    $heureF,
                    $NbJ,
                    $motif,
                    $Client,
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
                    $LibelleCodeAg_Serv
                );
            } else {

                $Nom = $Nomext;
                $Prenoms = $PrenomExt;
                $matr = "XER00" . $MatrExt . " - TEMPORAIRE";

                if ($libmodepaie === "ESPECES") {
                    $mode =  $valModeExt;
                    $modeDB = $valModeExt;
                }
                if ($libmodepaie === "MOBILE MONEY") {
                    $mode =  "TEL " . $valModeExt;
                    $modeDB = $valModeExt;
                }
                if ($libmodepaie === "VIREMENT BANCAIRE") {
                    $mode =  "CPT " . $valModeExt;
                    $modeDB = $valModeExt;
                }


                //exce
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
                    $autrTyp,
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
                    $codeAg_serv
                );

                if (!empty($filename01) && !empty($filename02)) {
                    if (in_array($file_extension01, $extentsion) && in_array($file_extension02, $extentsion)) {
                        $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hff_IntranetV01/Controler/pdf/' . $filename01;
                        move_uploaded_file($filetemp01, $Upload_file);
                        $Upload_file02 = $_SERVER['DOCUMENT_ROOT'] . '/Hff_IntranetV01/Controler/pdf/' . $filename02;
                        move_uploaded_file($filetemp02, $Upload_file02);
                        $FichierDom = $NumDom . '_' . $codeAg_serv ;

                        $this->DomModel->genererFusion($FichierDom, $filename01, $filename02);
                        $this->DomModel->InsertDom(
                            $NumDom,
                            $dateSystem,
                            $typMiss,
                            $autrTyp,
                            $matr,
                            $usersession,
                            $codeAg_serv,
                            $DateDebut,
                            $heureD,
                            $DateFin,
                            $heureF,
                            $NbJ,
                            $motif,
                            $Client,
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
                            $LibelleCodeAg_Serv
                        );
                    } else {
                        echo '<script type="text/javascript">
                    alert("Merci de Mettre les pièce jointes en PDF");
                    </script>';
                    }
                } else {
                    echo '<script type="text/javascript">
                    alert("Merci de Mettre les pièce jointes");
                
                    </script>';
                }

                /*if (!empty($filename02)) {
                    $Upload_file = $_SERVER['DOCUMENT_ROOT'] . '/Hff_IntranetV01/Controler/pdf/' . $filename02;
                    move_uploaded_file($filetemp02, $Upload_file);
                    $FichierDom = $NumDom . '_' . $matr . '_' . $Code_serv . '.pdf';
                    $this->DomModel->genererFusion($FichierDom,$filename02);
                }*/
            }


            echo '<script type="text/javascript">
                alert("Demande OM Envoyer");
                document.location.href = "/Hff_IntranetV01/index.php?action=ListDom";
                </script>';
        }
    }
    public function ShowListDom()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }

        $UserConnect = $_SESSION['user'];
        $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
        $LibServofCours = $this->DomModel->getLibeleAgence_Service($Servofcours);
        include 'Views/Principe.php';
        $ListDom = $this->DomModel->getListDom($LibServofCours);
        include 'Views/DOM/ListDom.php';
    }
    /* public function copy()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }

        $this->DomModel->copyInterneToDOXCUWARE();
       echo '<script type="text/javascript">
        alert("DOM FUSION");
        document.location.href = "/Hff_IntranetV01/index.php?action=ListDom";
        </script>';
    }*/
}
