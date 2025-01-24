<?php

namespace App\Model;

use App\Model\Model;

class ProfilModel extends Model
{


    // public function getProfilUser($Username)
    // {
    //     $Sql_username = "SELECT 
    //                         Utilisateur 
    //                         FROM Profil_user
    //                         WHERE Utilisateur = '" . $Username . "' 
    //                         AND App = 'DOM'
    //                         ";
    //     $exec_sql_username = $this->connexion->query($Sql_username);

    //     return $exec_sql_username ? odbc_fetch_array($exec_sql_username)['Utilisateur'] : false;
    // }

    public function getINfoAllUserCours($Username)
    {
        $sql_infoAllUsercous = "SELECT 
                                Utilisateur,
                                Profil,
                                App
                                FROM Profil_user
                                WHERE Utilisateur = '" . $Username . "'
                                
                                ";
        $exec_InfoAllUserCours = $this->connexion->query($sql_infoAllUsercous);
        $InfoAllUsercours = array();
        while ($row = odbc_fetch_array($exec_InfoAllUserCours)) {
            $InfoAllUsercours[] = $row;
        }
        return $InfoAllUsercours;
    }
}
