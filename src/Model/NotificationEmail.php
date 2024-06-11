<?php
namespace App\Model;
use App\Model\Model;

class NotificationEmail extends Model{

     function getActiveUsermail($Mailuser){
        $destinataires = array();
        $check_users = "SELECT  Mail FROM Profil_User WHERE  Utilisateur = '".$Mailuser."'";
        $exec = $this->connexion->query($check_users);
        while($dest = odbc_fetch_array($exec)){
            $destinataires[] = $dest[0];
        }
        return $destinataires;
     }

     function SendNOtificationEmail($User,$ObjetMail){
        $destination = $this->getActiveUsermail($User);
        $sujet_mail = $ObjetMail;
        $boundary = md5(uniqid(microtime(),true));
       
        $headers = 'From: noreply <mail@server.com>' . "\r\n";
        $headers .= 'Mime-Version: 1.0' . "\r\n";
        $headers .= 'Content-Type: multipart/mixed;boundary=' . $boundary . "\r\n";
        $headers .= "\r\n";
        $msg = 'Texte affiché par des clients mail ne supportant pas le type MIME.' . "\r\n\r\n";
        $msg .= '--' . $boundary . "\r\n";
        $msg .= 'Content-type: text/html; charset=utf-8' . "\r\n\r\n";
        foreach ($destination as $destination) {
            $envoi = mail($destination, $sujet_mail, $msg, $headers);

            // Vérification si l'e-mail a été envoyé avec succès
            if ($envoi == true) {
                echo "<p>Test 1 : La fonction mail() fonctionne. Un e-mail a été envoyé à l'adresse $destination.<br />S'il ne vous parvient pas, il y a probablement un blocage au niveau du serveur SMTP de l'hébergeur.</p>";
            } else {
                echo "<p>Test 1 : L'envoi par la fonction PHP mail() ne fonctionne pas ou est désactivé.</p>";
            }
        }
     }
}
