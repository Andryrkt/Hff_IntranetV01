<?php
class StatutModel
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
    public function InsertStatut($CoedApp, $CodeStatut,  $Descript, $DateSyst)
    {
        $Sql_Statut = "INSERT INTO Statut_demande
                             (Code_Application,
                            Code_Statut,
                             Description,
                             Date_creation)
                    VALUES('" . $CoedApp . "',
                    '" . $CodeStatut . "',
                    '" . $Descript . "',
                    '" . $DateSyst . "')";
        $execStatut = $this->Connexion->query($Sql_Statut);
    }
    public function getListStatut(){
        $Statut = "SELECT Code_Application,
                          Code_Statut,
                          Description
                    FROM Statut_demande";
        $exce_List = $this->Connexion->query($Statut);
        $StatutList = array();
        while($List = odbc_fetch_array($exce_List)){
            $StatutList[] = $List;
        }
        return $StatutList;
    }
}
