<?php

namespace App\Model\mutation;

use App\Model\Model;

class MutationModel extends Model
{
    public function getNombreOM($dateDebut, $dateFin, $matricule)
    {
        $sql = "SELECT count(*) as nombreOM FROM Demande_ordre_mission
        where ('$dateDebut' between Date_Debut and Date_Fin and Matricule = '$matricule' and ID_Statut_Demande  not in (9,33,34,35,44)) ";

        if ($dateFin !== '') {
            $sql .= "OR('$dateFin' between Date_Debut and Date_Fin and Matricule = '$matricule' and ID_Statut_Demande  not in (9,33,34,35,44))";
        }

        $result = odbc_fetch_array($this->connexion->query($sql));

        return $result['nombreOM'];
    }

    public function getNombreDM($dateDebut, $dateFin, $matricule)
    {
        $sql = "SELECT count(*) as nombreDM FROM Demande_de_mutation
where ('$dateDebut' between Date_Debut and Date_Fin and Matricule = '$matricule' and statut_demande_id  not in (71,72,73,74,75)) ";

        if ($dateFin !== '') {
            $sql .= "OR ('$dateFin' between Date_Debut and Date_Fin and Matricule = '$matricule' and statut_demande_id  not in (71,72,73,74,75))";
        }

        $result = odbc_fetch_array($this->connexion->query($sql));

        return $result['nombreDM'];
    }
}
