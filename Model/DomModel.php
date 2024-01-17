<?php
class DomModel
{
    private $connexion;
    public function __construct(Connexion $connexion)
    {
        $this->connexion = $connexion;
    }

    public function DOM_autoINcriment()
    {
        //NumDOM auto
        include('FunctionChaine.php');
        $YearsOfcours = date('y'); //24
        $MonthOfcours = date('m'); //01
        $AnneMoisOfcours = $YearsOfcours . $MonthOfcours; //2401
        // dernier NumDOM dans la base
        $NumDOM_Max = "SELECT  MAX(Numero_Ordre_Mission) FROM Demande_ordre_mission ";
        $exec_NumDOM_Max = $this->connexion->query($NumDOM_Max);
        if ($exec_NumDOM_Max === null) {
            echo "null";
        }
        odbc_fetch_row($exec_NumDOM_Max);
        $Max_NumDOM = odbc_result($exec_NumDOM_Max, 1);
        //num_sequentielless
        $vNumSequential =  substr($Max_NumDOM, -4); // lay 4chiffre msincrimente
        $DateAnneemoisnum = substr($Max_NumDOM, -8);
        $DateYearsMonthOfMax = substr($DateAnneemoisnum, 0, 4);
        if ($DateYearsMonthOfMax == $AnneMoisOfcours) {
            $vNumSequential =  $vNumSequential + 1;
        } else {
            if ($AnneMoisOfcours > $DateYearsMonthOfMax) {
                $vNumSequential = 1;
            }
        }
        strlen($vNumSequential);
        $Result_Num_DOM = "DOM" . $AnneMoisOfcours . CompleteChaineCaractere($vNumSequential, 4, "0", "G");
        return $Result_Num_DOM;
    }

    //personnel
    public function getInfoAgenceUserofCours($Usernames)
    {
        $QueryAgence = "SELECT Utilisateur,
                    Code_AgenceService_Sage,
                    Libelle_AgenceService_Sage
                    FROM Personnel, Profil_User 
                    WHERE Personnel.Matricule = Profil_User.Matricule
                    AND Profil_User.utilisateur = '" . $Usernames . "'";
        $execQueryAgence = $this->connexion->query($QueryAgence);
        $ResAgence = array();
        while ($row_agence = odbc_fetch_array($execQueryAgence)) {
            $ResAgence[] = $row_agence;
        }
        return $ResAgence;
    }
    public function getserviceofcours($Usernames)
    {
        $serviceofcours = "SELECT 
                                Code_AgenceService_Sage
                                FROM Personnel, Profil_User 
                                WHERE Personnel.Matricule = Profil_User.Matricule
                                AND Profil_User.utilisateur = '" . $Usernames . "'";
        $excServofCours = $this->connexion->query($serviceofcours);
        return $excServofCours ? odbc_fetch_array($excServofCours)['Code_AgenceService_Sage'] : false;
    }
    public function getInfoUserMservice($ServiceofCours)
    {
        $QueryService = "SELECT  Matricule,
                        Noms_Prenoms
                        FROM Personnel 
                        WHERE Code_AgenceService_Sage = '" . $ServiceofCours . "' ";
        $execService = $this->connexion->query($QueryService);
        $ResUserAllService = array();
        while ($tab = odbc_fetch_array($execService)) {
            $ResUserAllService[] = $tab;
        }
        return $ResUserAllService;
    }
    public function getInfoTelCompte($userSelect)
    {
        $QueryCompte  = "SELECT Noms_Prenoms,
                            Numero_Telephone,
                            Numero_Compte_Bancaire
                            FROM Personnel
                            WHERE Matricule = '" . $userSelect . "'";
        $execCompte = $this->connexion->query($QueryCompte);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }
    public function getName($Matricule)
    {
        $Queryname  = "SELECT Noms_Prenoms
                        FROM Personnel
                        WHERE Matricule = '" . $Matricule . "'";
        $execCname = $this->connexion->query($Queryname);
        return $execCname ? odbc_fetch_array($execCname)['Noms_Prenoms'] : false;
    }
    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }
}
