<?php
class AgenceServAutoriserModel{
     private $Connexion;

     public function __construct(Connexion $Connexion)
     {
        $this->Connexion = $Connexion;
     }

     public function getListAgenceServicetoUserAll(){
        $AgServ = "SELECT ID_Agence_Service_Autorise,
                        Session_Utilisateur,
                         Code_AgenceService_IRIUM,
                         Agence_Service_Irium.nom_agence_i100+'-'+Agence_Service_Irium.libelle_service_ips  
                    FROM Agence_service_autorise, Agence_Service_Irium
        Where Agence_service_autorise.Code_AgenceService_IRIUM = Agence_Service_Irium.agence_ips+service_ips";
        $ExecAgeSrv = $this->Connexion->query($AgServ);
        $ListAgServ = array();
        while($List = odbc_fetch_array($ExecAgeSrv)){
            $ListAgServ[] = $List;
        }
        return $ListAgServ;
     }

     public function deleteAgenceAuto($Id){
        $deleteParam = "DELETE FROM Agence_service_autorise WHERE ID_Agence_Service_Autorise = '".$Id."'";
        $execDelete = $this->Connexion->query($deleteParam);
     }
}