<?php

class TypeDocModel
{
   private $Connexion;
   public function __construct(Connexion $Connexion)
   {
      $this->Connexion = $Connexion;
   }
   public function getDatesystem()
   {
      $d = strtotime("now");
      $Date_system = date("Y-m-d", $d);
      return $Date_system;
   }

   public function Insert_TypeDoc($TypeDoc, $Soustyp, $DateSys)
   {
      $InsertTypeDoc = "INSERT INTO Sous_type_document(Code_Document, Code_Sous_Type, Date_creation)
                        VALUES('" . $TypeDoc . "','" . $Soustyp . "','" . $DateSys . "')";
      $exceInsertTypDoc = $this->Connexion->query($InsertTypeDoc);
   }
   public function getTypeDocAll()
   {
      $TypeDoc = "SELECT Code_Document,
                     Code_Sous_Type
                   FROM Sous_type_document  ";
      $execTypeDoc = $this->Connexion->query($TypeDoc);
      $Type = array();
      while ($tabType = odbc_fetch_array($execTypeDoc)) {
         $Type[] = $tabType;
      }
      return $Type;
   }
   public function getListeServiceAgenceAll()
   {
      $ServAg = "SELECT  nom_agence_i100 +'-'+nom_service_i100 as Agence from Agence_Service_Irium";
      $exServAg = $this->Connexion->query($ServAg);
      $Serv = array();
      while ($tabServ = odbc_fetch_array($exServAg)) {
         $Serv[]= $tabServ;
      }
      return $Serv;
   }

   public function getCodeAgServ($LibAgServ){
      $Libserv = "SELECT agence_ips +service_ips as CodeAgence FROM Agence_Service_Irium
       WHERE nom_agence_i100+'-'+nom_service_i100 = '".$LibAgServ."'";
      $exLibserv = $this->Connexion->query($Libserv);
      return $exLibserv ? odbc_fetch_array($exLibserv)['CodeAgence']: false;
   }
   public function InsertAgenceServiceAutorise($User,$CodeAgence,$DateSyst){
      $Insert = "INSERT INTO Agence_Service_Irium(Session_Utilisateur, Code_AgenceService_IRIUM, Date_creation)
      VALUES('".$User."', '".$CodeAgence."', '".$DateSyst."')";
      $excec = $this->Connexion->query($Insert);
   }
}
