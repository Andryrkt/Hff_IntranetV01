<?php

namespace App\Model;

class Model
{
    protected $connexion;
    protected $connect;
    protected $informixDB;

    // $port = '9088';
    // $database = 'ol_iriumprod';


    public function __construct()
    {
        $this->connexion = new Connexion();
        $this->connect = new DatabaseInformix();
    }

    public function RecupereNumDom($colonne)
    {
        $NumDOM_Max = "SELECT  MAX(Numero_Ordre_Mission) FROM Demande_ordre_mission ";
        $exec_NumDOM_Max = $this->connexion->query($NumDOM_Max);
        if ($exec_NumDOM_Max === null) {
            echo "null";
        }
        odbc_fetch_row($exec_NumDOM_Max);
        return  odbc_result($exec_NumDOM_Max, $colonne);
    }

    public function RecupereNumBDM()
    {
        $NumDOM_Max = "SELECT  MAX(Numero_Demande_BADM) FROM Demande_Mouvement_Materiel ";

        $exec_NumDOM_Max = $this->connexion->query($NumDOM_Max);
        if ($exec_NumDOM_Max === null) {
            echo "null";
        }
        odbc_fetch_row($exec_NumDOM_Max);
        return  odbc_result($exec_NumDOM_Max, 1);
    }

    /**
     * recuperation Mail de l'utilisateur connecter
     */
    public function getmailUserConnect($Userconnect)
    {
        $sqlMail = "SELECT Mail FROM Profil_User WHERE Utilisateur = '" . $Userconnect . "'";
        $exSqlMail = $this->connexion->query($sqlMail);
        return $exSqlMail ? odbc_fetch_array($exSqlMail)['Mail'] : false;
    }


    // Agence Sage to Irium
    /**
     *recuperation agenceService(Base PAiE) de l'utilisateur connecter
     */
    public function getAgence_SageofCours($Userconnect)
    {
        $sql_Agence = "SELECT Code_AgenceService_Sage
                            FROM Personnel, Profil_User
                            WHERE Personnel.Matricule = Profil_User.Matricule
                            AND Profil_User.utilisateur = '" . $Userconnect . "'";
        $exec_Sql_Agence = $this->connexion->query($sql_Agence);
        return $exec_Sql_Agence ? odbc_fetch_array($exec_Sql_Agence)['Code_AgenceService_Sage'] : false;
    }
    /**
     * recuperation agence service dans iRium selon agenceService(Base PAIE) de l'utilisateur connecter 
     * @param $CodeAgenceSage : Agence Service dans le BAse PAIE  $Userconnect: Utilisateur Connecter 
     */
    public function getAgenceServiceIriumofcours($CodeAgenceSage, $Userconnect)
    {
        $sqlAgence_Service_Irim = "SELECT  agence_ips, 
                                            nom_agence_i100,
                                            service_ips,
                                             nom_service_i100
                                    FROM Agence_Service_Irium, personnel,Profil_User
                                    WHERE Agence_Service_Irium.service_sage_paie = personnel.Code_AgenceService_Sage
                                    AND personnel.Code_AgenceService_Sage = '" . $CodeAgenceSage . "'
                                    AND Personnel.Matricule = Profil_User.Matricule
                                    AND Profil_User.utilisateur = '" . $Userconnect . "' ";
        $exec_sqlAgence_Service_Irium = $this->connexion->query($sqlAgence_Service_Irim);
        $Tab_AgenceServiceIrium = array();
        while ($row_Irium = odbc_fetch_array($exec_sqlAgence_Service_Irium)) {
            $Tab_AgenceServiceIrium[] = $row_Irium;
        }
        return $Tab_AgenceServiceIrium;
    }
}
