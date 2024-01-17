<?php
class PersonnelModel extends Connexion
{
    private $connexion;
    public function __construct(Connexion $connexion)
    {
        $this->connexion = $connexion;
    }

    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }

    public function getInfoAgenceUserofCours($Usernames)
    {
        $QueryAgence = "SELECT Utilisateur,
                    Code_AgenceService_Sage,
                    Libelle_AgenceService_Sage
                    FROM Personnel, Profil_User 
                    WHERE Personnel.Matricule = Profil_User.Matricule
                    AND Profil_User.utilisateur = '".$Usernames."'";
        $execQueryAgence = $this->connexion->query($QueryAgence);
        $ResAgence = array();
        while($row_agence = odbc_fetch_array($execQueryAgence)){
            $ResAgence[] = $row_agence;
        }
        return $ResAgence;
    }
    public function getInfoUserMservice($ServiceofCours){
        $QueryService = "SELECT  Personnel.Noms_Prenoms 
                        FROM Personnel 
                        WHERE Code_AgenceService_Sage = '".$ServiceofCours."' ";
        $execService = $this->connexion->query($QueryService);
        $ResUserAllService = array();
        while($tab = odbc_fetch_array($execService)){
            $ResUserAllService[] = $tab;
        }
        return $ResUserAllService;
    }
}
