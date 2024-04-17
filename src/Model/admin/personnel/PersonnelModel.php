<?php

namespace App\Model\admin\personnel;

use App\Model\Model;

class PersonnelModel extends Model
{

    public function getDatesystem()
    {
        $d = strtotime("now");
        $Date_system = date("Y-m-d", $d);
        return $Date_system;
    }


    public function recupInfoPersonnel()
    {
        $statement = "SELECT ID_Personnel,  Nom, Prenoms, Matricule, Code_AgenceService_Sage, Code_AgenceService_IRIUM, Numero_Fournisseur_IRIUM, Qualification FROM Personnel";
        $execCompte = $this->connexion->query($statement);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }
    /**
     * récupération personnel avec identifiant
     */
    public function recupInfoPersonnelMatricule(int $matricule)
    {
        $statement = "SELECT ID_Personnel,  Nom, Prenoms, Matricule, Code_AgenceService_Sage, Code_AgenceService_IRIUM, Numero_Fournisseur_IRIUM, Qualification, 
        (SELECT DISTINCT service_ips FROM Agence_Service_Irium asi, Personnel p WHERE asi.agence_ips = p. Code_AgenceService_IRIUM and asi.service_sage_paie = p.Code_AgenceService_Sage and Matricule = '" . $matricule . "')
        FROM Personnel
        WHERE Matricule = '" . $matricule . "'";
        $execCompte = $this->connexion->query($statement);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }

    /**
     * Recuperation de code irium
     */
    public function recupAgenceServiceIrium()
    {
        $statement = "SELECT DISTINCT Code_AgenceService_IRIUM FROM Personnel WHERE Code_AgenceService_IRIUM IS NOT NULL ORDER BY Code_AgenceService_IRIUM ASC";
        $execCompte = $this->connexion->query($statement);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }

    /**
     * Recuperation de code sage
     */
    public function recupAgenceServiceSage()
    {
        $statement = "SELECT DISTINCT  Code_AgenceService_Sage FROM Personnel ORDER BY  Code_AgenceService_Sage ASC";
        $execCompte = $this->connexion->query($statement);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }

    /**
     * Récupération de service Irium
     */
    public function recupServiceIrium()
    {
        $statement = "SELECT DISTINCT service_ips FROM Agence_Service_Irium asi, Personnel p WHERE asi.agence_ips = p. Code_AgenceService_IRIUM and asi.service_sage_paie = p.Code_AgenceService_Sage";
        $execCompte = $this->connexion->query($statement);
        $compte = array();
        while ($tab_compt = odbc_fetch_array($execCompte)) {
            $compte[] = $tab_compt;
        }
        return $compte;
    }
}
