<?php

use App\Model\DomModel;
use App\Model\Connexion;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Model\StatutModel;
use App\Model\TypeDocModel;
use App\Model\PersonnelModel;
use App\Controller\DomControl;
use App\Controller\ProfilControl;
use App\Controller\StatutControl;
use App\Controller\MainController;
use App\Controller\TypeDocControl;
use App\Controller\PersonnelControl;
use App\Model\AgenceServAutoriserModel;
use App\Controller\AgenceServAutoriserControl;



require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// Configuration de Twig avec le chemin vers vos fichiers de template




// include 'Model/Connexion.php';
// include 'Model/LdapModel.php';
// include 'Controler/LdapControl.php';
// //---Profil---
// include 'Model/ProfilModel.php';
// include 'Controler/ProfilControl.php';
$Conn_IntranetV01 =  new Connexion();
$ModelProfil = new ProfilModel();
$ControlProfil = new ProfilControl();

// //----
// //Personnel
// include 'Model/PersonnelModel.php';
// include 'Controler/PersonnelControl.php';
$ModelPers = new PersonnelModel();
$ControlPers = new PersonnelControl();
//---
// //DOM
// include 'Model/DomModel.php';
// include 'Controler/DomControl.php';
$ModelDOM = new DomModel();
$ControlDOM = new DomControl();
// //----
// // TypeDoc
// include 'Model/TypeDocModel.php';
// include 'Controler/TypeDocControl.php';
$ModelType = new TypeDocModel();
$ControlType = new TypeDocControl();
// //----
// //Statut
// include 'Model/StatutModel.php';
// include 'Controler/StatutControl.php';
$ModelStatut = new StatutModel();
$ControlStatut = new StatutControl();
// //----
// //Autorisation
// include 'Model/AgenceServAutoriserModel.php';
// include 'Controler/AgenceServAutoriserControl.php';
$ModelAutorisation = new AgenceServAutoriserModel();
$ControlAutorisation = new AgenceServAutoriserControl();

// include '/Service/GenererPdf.php';
// $genererPdf = new GenererPdf();
$MainController = new MainController();


//
$Username = isset($_POST['Username']) ? $_POST['Username'] : '';
$Password = isset($_POST['Pswd']) ? $_POST['Pswd'] : '';
$Ldap = new LdapModel();
$Connexion_Ldap_User = $Ldap->userConnect($Username, $Password);
//$Ldap->searchLdapUser();

$action = isset($_GET['action']) ? $_GET['action'] : 'default';
switch ($action) {
    case 'Acceuil':
        $ControlProfil->showPageAcceuil();
        break;
    case 'Authentification':

        if (!$Connexion_Ldap_User) {
            echo '<script type="text/javascript">
                alert("Merci de vérifier votre session LDAP");
                document.location.href = "/Hffintranet";
            </script>';
        } else {
            session_start();
            $_SESSION['user'] = $Username;
            $ControlProfil->showInfoProfilUser();
        }
        break;

    case 'Logout':
        session_start();
        $_SESSION['user'] = $Username;
        unset($_SESSION['user']);
        session_destroy();
        session_unset();
        header("Location:/Hffintranet/");
        exit();
        session_write_close();
        break;

    case 'Propos':
        $ControlProfil->showinfoAllUsercours();
        break;
    case 'Personnels':
        $ControlPers->showPersonnelForm();
        break;
    case 'PersonnelList':
        $ControlPers->showListePersonnel();
        break;
    case 'New_DOM':
        $ControlDOM->showFormDOM();
        break;
    case 'ListDom':
        $ControlDOM->ShowListDom();
        break;
    case 'ListDomRech':
        $ControlDOM->ShowListDomRecherche();
        break;
    case 'checkMatricule':
        $ControlDOM->ShowDomPDF();
        break;
    case 'SelectCateg':
        $ControlDOM->selectCatg();
        break;
    case 'SelectCatgeRental':
        $ControlDOM->selectCategRental();
        break;
    case 'selectIdem':
        $ControlDOM->selectSiteRental();
        break;

    case 'SelectPrixRental':
        $ControlDOM->SelectPrixRental();
        break;
    case 'DetailDOM':
        $ControlDOM->DetailDOM();
        break;
    case 'EnvoyerImprime':
        $ControlDOM->EnvoieImprimeDom();
        break;
    case 'TypeDoc':
        $ControlType->showTypeDocForm();
        break;
    case 'MoveTypeDoc':
        $ControlType->MoveTypeDocForm();
        break;
    case 'AgenceServiceAutoAll':
        $ControlType->showListServiceAgenceAll();
        break;
    case 'CodeAgenceServiceAuto':
        $ControlType->showListCodeagence();
        break;
    case 'Statut':
        $ControlStatut->ShowFormStatut();
        break;
    case 'MoveStatut':
        $ControlStatut->MoveStatut();
        break;
    case 'AgenceAutoriser':
        $ControlAutorisation->showListAgenceService();
        break;
    case 'DelAgAuto':
        $ControlAutorisation->deleteAgenceAuto();
        break;
    case 'anaranaaction':
        $ControlDOM->anaranaFonction();
        break;
    case 'recherche':
        $ControlDOM->rechercheController();
        break;
    case 'listStatut':
        $ControlDOM->listStatutController();
        break;
    case 'Dupliquer':
        $ControlDOM->duplificationFormJsonController();
        break;
    case 'DuplifierForm':
        $ControlDOM->duplificationFormController();
        break;
    case 'LibStatut':
        $ControlDOM->filterStatut();
        break;
    case 'twig':
        $MainController->index();
        break;
    default:
        include 'Views/SignIn.php';
}



use App\Model\DatabaseInformix;

$hostname = 'IPS_HFFPROD';
$port = '9088';
$database = 'ol_iriumprod';
$username = 'informix';
$password = 'informix';

// Créer une instance de la classe Database
$database = new DatabaseInformix($hostname, $port, $database, $username, $password);

// Exemple de requête
$query = "SELECT * FROM MAT_MAT ";
$result = $database->query($query);

// Manipuler les résultats de la requête
if ($result) {
    foreach ($result as $row) {
        print_r($row);
    }
} else {
    echo "Erreur lors de l'exécution de la requête";
}

// Fermer la connexion à la base de données
$database->close();
