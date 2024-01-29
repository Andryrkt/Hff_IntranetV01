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
            $CodeServiceofCours = $this->DomModel->getAgenceServiceIriumofcours($Code_AgenceService_Sage,$_SESSION['user']);
            $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $PersonelServOfCours = $this->DomModel->getInfoUserMservice($Servofcours);
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
            $Code_serv = $_POST['Serv']; //80 Admin
            $dateS = date("d/m/Y", strtotime($_POST['datesyst']));
            $NumDom = $_POST['NumDOM'];
            $Devis = $_POST['Devis'];
            $serv = $_POST['LibServ']; //INF info 
            $typMiss = $_POST['typeMission'];
            $autrTyp = $_POST['AutreType'];
            $Nom = $_POST['nomprenom'];
            $Prenoms = $_POST['prenom'];
            $matr = $_POST['matricule'];
            $DateDebut = $_POST['dateDebut'];
            $dateD = date("d/m/Y", strtotime( $DateDebut));
            $heureD = $_POST['heureDebut'];
            $DateFin = $_POST['dateFin'];
            $dateF = date("d/m/Y", strtotime($DateFin));
            $heureF = $_POST['heureFin'];
            $NbJ = $_POST['Nbjour'];
            $motif = $_POST['motif'];
            $Client = $_POST['client'];
            $fiche = $_POST['fiche'];
            $lieu = $_POST['lieuInterv'];
            $vehicule = $_POST['vehicule'];
            $numvehicul = $_POST['N_vehicule'];
            $idemn = $_POST['idemForfait'];
            $totalIdemn = $_POST['TotalidemForfait'];
            $motifdep01 = $_POST['MotifAutredep'];
            $montdep01 = $_POST['Autredep1'];
            $motifdep02 = $_POST['MotifAutredep2'];
            $montdep02 = $_POST['Autredep2'];
            $motifdep03 = $_POST['MotifAutredep3'];
            $montdep03 = $_POST['Autredep3'];
            $totaldep = $_POST['TotalAutredep'];
            $libmodepaie = $_POST['modepaie'];
            $valModesp = $_POST['valModesp'];
            $valModemob = $_POST['valModemob'];
            $valModecompt = $_POST['valModecompt'];
            if ($libmodepaie === "ESPECES") {
                $mode =  $valModesp;
            }
            if ($libmodepaie === "MOBILE MONEY") {
                $mode =  $valModemob;
            }
            if ($libmodepaie === "VIREMENT BANCAIRE") {
                $mode =  $valModecompt;
            }
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
                $mode
            );
            $insertToDOM = $this->DomModel;
            // echo "ok ";
            echo '<script type="text/javascript">
                alert("Demande OM Envoyer");
                document.location.href = "/Hff_IntranetV01/index.php?action=New_DOM";
                </script>';
        }
    }
}
