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
            $CodeServiceofCours = $this->DomModel->getInfoAgenceUserofCours($_SESSION['user']);
            $Servofcours = $this->DomModel->getserviceofcours($_SESSION['user']);
            $PersonelServOfCours = $this->DomModel->getInfoUserMservice($Servofcours);
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
            include 'Views/DOM/FormCompleDOM.php';
        }
    }

    public function EnvoieImprimeDom(){
        session_start();
        if (empty($_SESSION['user'])) {
            header("Location:/Hff_IntranetV01/index.php?action=Logout");
            session_destroy();
            exit();
        }

        if ($_SERVER['REQUEST_METHOD']  === 'POST'){
            $NumDom = $_POST['NumDOM'];
            $serv = $_POST['LibServ'];
            $typMiss = $_POST['typeMission'];
            $autrTyp = $_POST['AutreType'];
            $Nom = $_POST['nomprenom'];
            $matr =$_POST['matricule'];
            $period = $_POST['periode'];
            $dateD = $_POST['dateDebut'];
            $heureD = $_POST['heureDebut'];
            $dateF = $_POST['dateFin'];
            $heureF = $_POST['heureFin'];
            $NbJ = $_POST['Nbjour'];
            $motif =$_POST['motif'];
            $Client = $_POST['client'];
            $fiche = $_POST['fiche'];
            $lieu = $_POST['lieuInterv'];
            $vehicule = $_POST['vehicule'];
            $numvehicul = $_POST['N_vehicule'];
            $idemn = $_POST['idemForfait'];
            $totalIdemn =$_POST['TotalidemForfait'];
            $motifdep01 = $_POST['MotifAutredep'];
            $montdep01 = $_POST['Autredep1'];
            $motifdep02 = $_POST['MotifAutredep2'];
            $montdep02 = $_POST['Autredep2'];
            $motifdep03 = $_POST['MotifAutredep3'];
            $montdep03 = $_POST['Autredep3'];
            $totaldep = $_POST['TotalAutredep'];
            $libmodepaie = $_POST['modepaie'];
            $valModepaie = $_POST['valMode'];
        }

    }
}
