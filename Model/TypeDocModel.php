<?php

class TypeDocModel
{
    private $Connexion;
     public function __construct(Connexion $Connexion)
     {
        $this->Connexion = $Connexion;
     }

     public function Insert_TypeDoc($TypeDoc,$Soustyp,$DateSys){
      $InsertTypeDoc = "INSERT INTO Sous_type_document(Code_Document, Code_Sous_Type, Date_creation)
                        VALUES('".$TypeDoc."','".$Soustyp."','".$DateSys."')";
      $exceInsertTypDoc = $this->Connexion->query($InsertTypeDoc);
     }
     public function getTypeDocAll(){
        $TypeDoc = "SELECT Code_Document,
                     Code_Sous_Type
                   FROM Sous_type_document  ";
        $execTypeDoc = $this->Connexion->query($TypeDoc);
        $Type = array();
        while($tabType = odbc_fetch_array($execTypeDoc)){
         $Type[] = $tabType;
        }
        return $Type;
     }
}
