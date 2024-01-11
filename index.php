<?php
include 'Model/LdapModel.php';
include 'Controler/LdapControl.php';
$Username = isset($_POST['Username']) ? $_POST['Username']: '';
$Password = isset($_POST['Pswd']) ? $_POST['Pswd']: ''; 
$Ldap = new LdapConnect();
$Connexion_Ldap_User = $Ldap->userConnect($Username,$Password);

$action = isset($_GET['action']) ? $_GET['action'] : 'default';
switch ($action) {
    case 'Authentification':
            if(!$Connexion_Ldap_User){
                echo "error";
            }else{
                echo 'you are connected';
            }
        break;
    default:
        include 'Views/SignIn.php';
}
