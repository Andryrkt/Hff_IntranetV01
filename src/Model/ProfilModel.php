<?php

namespace App\Model;

use App\Model\Model;

class ProfilModel extends Model
{
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
