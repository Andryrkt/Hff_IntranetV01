<?php

namespace App\Model;

use App\Model\Model;

class AgenceServAutoriserModel extends Model
{


   public function getListAgenceServicetoUserAll()
   {
      $AgServ = "SELECT ID_Agence_Service_Autorise,
                        Session_Utilisateur,
                         Code_AgenceService_IRIUM,
                         Agence_Service_Irium.nom_agence_i100+'-'+Agence_Service_Irium.libelle_service_ips  
                    FROM Agence_service_autorise, Agence_Service_Irium
        Where Agence_service_autorise.Code_AgenceService_IRIUM = Agence_Service_Irium.agence_ips+service_ips";
      $ExecAgeSrv = $this->connexion->query($AgServ);
      $ListAgServ = array();
      while ($List = odbc_fetch_array($ExecAgeSrv)) {
         $ListAgServ[] = $List;
      }
      return $ListAgServ;
   }

   public function deleteAgenceAuto($Id)
   {
      $deleteParam = "DELETE FROM Agence_service_autorise WHERE ID_Agence_Service_Autorise = '" . $Id . "'";
      $execDelete = $this->connexion->query($deleteParam);
   }
}
