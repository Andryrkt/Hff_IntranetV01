<?php


use App\Model\Connexion;
use App\Model\LdapModel;
use App\Model\ProfilModel;
use App\Model\StatutModel;
use App\Model\dom\DomModel;
use App\Model\TypeDocModel;
use App\Controller\ProfilControl;

use App\Controller\StatutControl;
use App\Controller\badm\BadmDupli;
use App\Controller\dom\DomControl;
use App\Controller\MainController;
use App\Controller\TypeDocControl;
use App\Controller\badm\BadmController;
use App\Model\AgenceServAutoriserModel;
use App\Controller\badm\CasierController;
use App\Controller\dom\DomListController;
use App\Controller\dom\DomDetailController;
use App\Controller\badm\BadmDupliController;
use App\Controller\badm\BadmListeController;
use App\Controller\badm\BadmDetailController;
use App\Model\admin\personnel\PersonnelModel;
use App\Controller\AgenceServAutoriserControl;
use App\Controller\dom\DomDuplicationController;
use App\Controller\admin\personnel\PersonnelControl;
use App\Controller\badm\CasierListController;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// $routes = require_once __DIR__ . '/config/routes.php';
// //var_dump($routes);

// $dispatcher = FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) use ($routes) {
//     foreach ($routes as $route) {
//         $r->addRoute($route['method'], $route['path'], $route['controller']);
//     }
// });

// // Fetch method and URI from somewhere
// $httpMethod = $_SERVER['REQUEST_METHOD'];
// $uri = $_SERVER['REQUEST_URI'];
// //var_dump($uri);
// // Strip query string (?foo=bar) and decode URI
// if (false !== $pos = strpos($uri, '?')) {
//     $uri = substr($uri, 0, $pos);
// }
// $uri = rawurldecode($uri);
// //var_dump($httpMethod);
// $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
// //var_dump($routeInfo);
// switch ($routeInfo[0]) {
//     case \FastRoute\Dispatcher::FOUND:
//         $explode = explode(':', $routeInfo[1]);

//         call_user_func_array([new $explode[0], $explode[1]], [$routeInfo[2]]);
//         break;
//     case \FastRoute\Dispatcher::NOT_FOUND:
//         //Route not found
//         header("HTTP/1.0 404 Not Found");
//         echo "404 Not Found";
//         break;
// }




// include 'Model/Connexion.php';
// include 'Model/LdapModel.php';
// include 'Controler/LdapControl.php';
// //---Profil---
// include 'Model/ProfilModel.php';
// include 'Controler/ProfilControl.php';
// $Conn_IntranetV01 =  new Connexion();
// $ModelProfil = new ProfilModel();
$ControlProfil = new ProfilControl();

// //----
// //Personnel
// include 'Model/PersonnelModel.php';
// include 'Controler/PersonnelControl.php';
// $ModelPers = new PersonnelModel();
$ControlPers = new PersonnelControl();
//---
// //DOM
// include 'Model/DomModel.php';
// include 'Controler/DomControl.php';
// $ModelDOM = new DomModel();
$ControlDOM = new DomControl();
$DomListeController = new DomListController();
$DomDetailController = new DomDetailController();
$DomDuplicationController = new DomDuplicationController();
// //----
// // TypeDoc
// include 'Model/TypeDocModel.php';
// include 'Controler/TypeDocControl.php';
// $ModelType = new TypeDocModel();
$ControlType = new TypeDocControl();
// //----
// //Statut
// include 'Model/StatutModel.php';
// include 'Controler/StatutControl.php';
// $ModelStatut = new StatutModel();
$ControlStatut = new StatutControl();
// //----
// //Autorisation
// include 'Model/AgenceServAutoriserModel.php';
// include 'Controler/AgenceServAutoriserControl.php';
// $ModelAutorisation = new AgenceServAutoriserModel();
$ControlAutorisation = new AgenceServAutoriserControl();

// include '/Service/GenererPdf.php';
// $genererPdf = new GenererPdf();

$BadmController = new BadmController();
$BadmListeController = new BadmListeController();
$BadmDetailController = new BadmDetailController();
$BadmDupliController = new BadmDupliController();
$CasierController = new CasierController();
$CasierListController = new CasierListController();

//
// $Username = isset($_POST['Username']) ? $_POST['Username'] : '';
// $Password = isset($_POST['Pswd']) ? $_POST['Pswd'] : '';
// $Ldap = new LdapModel();
// $Connexion_Ldap_User = $Ldap->userConnect($Username, $Password);
//$Ldap->searchLdapUser();

$action = isset($_GET['action']) ? $_GET['action'] : 'default';
switch ($action) {
    case 'Acceuil':
        $ControlProfil->showPageAcceuil();
        break;
    case 'Authentification':
        $ControlProfil->showInfoProfilUser();
        // if (!$Connexion_Ldap_User) {
        //     echo '<script type="text/javascript">
        //         alert("Merci de vérifier votre session LDAP");
        //         document.location.href = "/Hffintranet";
        //     </script>';
        // } else {
        //     session_start();
        //     $_SESSION['user'] = $Username;
        // }
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
    case 'updatePersonnel':
        $ControlPers->updatePersonnel();
        break;
    case 'New_DOM':
        $ControlDOM->showFormDOM();
        break;
    case 'ListDom':
        $ControlDOM->ShowListDom();
        break;
    case 'ListDomRech':
        $DomListeController->ShowListDomRecherche();
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
        $DomDetailController->DetailDOM();
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
        $ControlDOM->agenceServiceJson();
        break;
    case 'recherche':
        $DomListeController->rechercheController();
        break;
    case 'listStatut':
        $DomListeController->listStatutController();
        break;
    case 'Dupliquer':
        $DomDuplicationController->duplificationFormJsonController();
        break;
    case 'DuplifierForm':
        $DomDuplicationController->duplificationFormController();
        break;
    case 'LibStatut':
        $ControlDOM->filterStatut();
        break;
    case 'twig':
        $MainController->index();
        break;
    case 'formBadm':
        $BadmController->formBadm();
        break;
    case 'formCompleBadm':
        $BadmController->formBadm();
        break;
    case 'envoiFormCompleBadm':
        $BadmController->formCompleBadm();
        break;
    case 'DetailBADM':
        $BadmDetailController->detailBadm();
        break;
    case 'dupliBADM':
        $BadmDupliController->dupliBadm();
        break;
    case 'serviceDestinataire':
        $BadmController->serviceDestinataire();
        break;
    case 'casierDestinataire':
        $BadmController->casierDestinataire();
        break;
    case 'listBadm':
        $BadmListeController->AffichageListeBadm();
        break;
    case 'listJson':
        $BadmListeController->envoiListJsonBadm();
        break;
    case 'nouveauCasier':
        $CasierController->NouveauCasier();
        break;
    case 'formCasier':
        $CasierController->FormulaireCasier();
        break;
    case 'listCasier':
        $CasierListController->AffichageListeCasier();
        break;

    default:
        include 'Views/SignIn.php';
}








// $dsn = $_ENV['DB_DNS_PDO']; 
// $username = $_ENV['DB_USERNAME_PDO'];
// $password = $_ENV['DB_PASSWORD_PDO']; 

// try {
//     $pdo = new PDO($dsn, $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     echo "Connexion réussie!";
// } catch (PDOException $e) {
//     die("Erreur de connexion : " . $e->getMessage());
// }

// // Exécution d'une requête
// $stmt = $pdo->query("SELECT * FROM Personnel"); // Remplacez 'your_table' par le nom de votre table
// while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//     print_r($row);
// }


















// use App\Model\DatabaseInformix;

// $hostname = 'IPS_HFFPROD';
// $port = '9088';
// $database = 'ol_iriumprod';
// $username = 'informix';
// $password = 'informix';

// // Exemple d'utilisation de la classe
// $informixDB = new DatabaseInformix($hostname, $username, $password);
// $informixDB->connect();

// // Exemple de requête SQL
// $query = "select MMAT_DESI, MMAT_NUMMAT, MMAT_NUMSERIE, MMAT_RECALPH, MMAT_MARQMAT, MMAT_DATENTR, YEAR(MMAT_DATEMSER) As Annee_model, MMAT_TYPMAT, MMAT_NUMPARC, MMAT_NOUO from MAT_MAT ";
//select MIMM_SUCLIEU , MIMM_SERVICE   from MMO_IMM // agence service emetteur
//select MHIR_COMPTEUR, MHIR_CUMCOMP  from MAT_HIR // heures et kilométrage machine
//select SUM(MOFI_MT) AS somme_totale   from MAT_OFI
// // Exécuter la requête
// $result = $informixDB->executeQuery($query);

// // Afficher les résultats
// $rows = $informixDB->fetchResults($result);
// print_r($rows);

// // Fermer la connexion
// $informixDB->close();
