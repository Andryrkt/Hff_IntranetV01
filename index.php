<?php
include 'Model/Connexion.php';
include 'Model/LdapModel.php';
include 'Controler/LdapControl.php';
//---Profil---
include 'Model/ProfilModel.php';
include 'Controler/ProfilControl.php';
$Conn_IntranetV01 =  new Connexion();
$ModelProfil = new ProfilModel($Conn_IntranetV01);
$ControlProfil = new ProfilControl($ModelProfil);

//----
//Personnel
include 'Model/PersonnelModel.php';
include 'Controler/PersonnelControl.php';
$ModelPers = new PersonnelModel($Conn_IntranetV01);
$ControlPers = new PersonnelControl($ModelPers);
//---
//DOM
include 'Model/DomModel.php';
include 'Controler/DomControl.php';
$ModelDOM = new DomModel($Conn_IntranetV01);
$ControlDOM = new DomControl($ModelDOM);
//----
// TypeDoc
include 'Model/TypeDocModel.php';
include 'Controler/TypeDocControl.php';
$ModelType = new TypeDocModel($Conn_IntranetV01);
$ControlType = new TypeDocControl($ModelType);
//----
//Statut
include 'Model/StatutModel.php';
include 'Controler/StatutControl.php';
$ModelStatut = new StatutModel($Conn_IntranetV01);
$ControlStatut = new StatutControl($ModelStatut);
//----
//Autorisation
include 'Model/AgenceServAutoriserModel.php';
include 'Controler/AgenceServAutoriserControl.php';
$ModelAutorisation = new AgenceServAutoriserModel($Conn_IntranetV01);
$ControlAutorisation = new AgenceServAutoriserControl($ModelAutorisation);
//
$Username = isset($_POST['Username']) ? $_POST['Username'] : '';
$Password = isset($_POST['Pswd']) ? $_POST['Pswd'] : '';
$Ldap = new LdapConnect();
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
                document.location.href = "/Hff_IntranetV01";
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
        header("Location:/Hff_IntranetV01/");
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
    case 'checkMatricule':
        $ControlDOM->ShowDomPDF();
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
        //    
     case 'Statut':
        $ControlStatut->ShowFormStatut();
        break;   
     case 'MoveStatut':
           $ControlStatut->MoveStatut();
         break;
      case 'AgenceAutoriser':
        $ControlAutorisation->showListAgenceService();
        break;
    default:
        include 'Views/SignIn.php';
}
