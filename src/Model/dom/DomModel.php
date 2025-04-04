<?php

namespace App\Model\dom;

use App\Model\Model;


class DomModel extends Model
{

    //TSY MAHAZO FAFANA
    /**
     * Chevauchement : recuperation la minimum de la date de mission et le maximum de la mission 
     */
    public function getInfoDOMMatrSelet($matricule)
    {
        $SqlDate = "SELECT  Date_Debut, Date_Fin
        FROM Demande_ordre_mission
        WHERE  Matricule = '" . $matricule . "'  
        AND ID_Statut_Demande NOT IN (9, 33, 34, 35, 44)";

        $execSqlDate = $this->connexion->query($SqlDate);

        $DateM = array();
        while ($tab_list = odbc_fetch_array($execSqlDate)) {
            $DateM[] = $tab_list;
        }

        return $DateM;
    }

    public function verifierSiTropPercu(string $numeroDom)
    {
        $sql = "SELECT
                    CASE
                        WHEN (dom.Nombre_Jour - COALESCE(SUM(domtp.Nombre_Jour_Tp), 0)) > 0 THEN 'Trop_percu'
                        ELSE ''
                    END AS reponse
                FROM Demande_ordre_mission dom
                LEFT JOIN Demande_ordre_mission_tp domtp
                    ON dom.Numero_Ordre_Mission = domtp.Numero_Ordre_Mission
                WHERE dom.Numero_Ordre_Mission = '" . $numeroDom . "' AND dom.Sous_Type_Document = 2
                GROUP BY dom.Numero_Ordre_Mission, dom.Nombre_Jour";

        $result = odbc_fetch_array($this->connexion->query($sql));

        return !$result ? $result : $result['reponse'] === 'Trop_percu';
    }

    public function getNombreJourTropPercu(string $numeroDom)
    {
        $sql = "SELECT COALESCE(SUM(domtp.Nombre_Jour_Tp), 0) AS reponse
                FROM Demande_ordre_mission_tp domtp
                WHERE domtp.Numero_Ordre_Mission = '$numeroDom'";

        $result = odbc_fetch_array($this->connexion->query($sql));

        return $result['reponse'];
    }

    /**
     * recuperer le nom et prenoms du matricule 
     */
    // public function getName($Matricule)
    // {
    //     $Queryname  = "SELECT Nom, Prenoms
    //                     FROM Personnel
    //                     WHERE Matricule = '" . $Matricule . "'";
    //     $execCname = $this->connexion->query($Queryname);
    //     $Infopers = array();
    //     while ($tabInfo = odbc_fetch_array($execCname)) {
    //         $Infopers[] = $tabInfo;
    //     }
    //     return $Infopers;
    // }
    // categorie
    /**
     * recuperation des catégories selon le type de mission et code agence 
     */
    // public function CategPers($TypeMiss, $codeAg)
    // {
    //     $SqlTypeMiss = "SELECT DISTINCT
    //                     Catg 
    //                     FROM Idemnity 
    //                     WHERE Type = '" . $TypeMiss . "' 
    //                     AND Rmq  in('STD','" . $codeAg . "')";
    //     $execSqlTypeMiss = $this->connexion->query($SqlTypeMiss);
    //     $ListCatg = array();
    //     while ($TabTYpeMiss = odbc_fetch_array($execSqlTypeMiss)) {
    //         $ListCatg[] = $TabTYpeMiss;
    //     }
    //     return $ListCatg;
    // }
    /**
     * recuperation catégorie Rental 
     * @param default CodeR : 50 
     */
    // public function catgeRental($CodeR)
    // {
    //     $SqlRentacatg = "SELECT DISTINCT Catg FROM Idemnity WHERE Type = 'MUTATION' AND Rmq = '" . $CodeR . "' ";
    //     $exSql = $this->connexion->query($SqlRentacatg);
    //     $ListCatge = array();
    //     while ($tab_list = odbc_fetch_array($exSql)) {
    //         $ListCatge[] = $tab_list;
    //     }
    //     return $ListCatge;
    // }

    /**
     * selection site (region ) 
     * @param  TypeM: type de mission 
     * @param catgPERs:  Catégorie du personnel selectionner 
     */
    // public function SelectSite($TypeM, $catgPERs)
    // {
    //     $Site = "SELECT DISTINCT Destination FROM Idemnity WHERE Type = '" . $TypeM . "' AND Catg='" . $catgPERs . "'  ";
    //     $exSite = $this->connexion->query($Site);
    //     $list = array();
    //     while ($tab = odbc_fetch_array($exSite)) {
    //         $list[] = $tab;
    //     }
    //     return $list;
    // }


    /**
     * recuperation Prix des idemnité
     * @param TypeM: Type de mission
     * @param CategPers: Catgégorie du personnel selectionner 
     * @param Dest : site (region) selectionner
     * @param AgCode: Code agence 
     */
    // public function SelectMUTPrixRental($TypeM, $CategPers, $Dest, $AgCode)
    // {
    //     $PrixRental = "SELECT DISTINCT Montant_idemnite FROM Idemnity WHERE Type = '" . $TypeM . "' 
    //                 AND Catg = '" . $CategPers . "' AND Destination = '" . $Dest . "' AND Rmq = '" . $AgCode . "' ";
    //     $exPrixRental = $this->connexion->query($PrixRental);
    //     $Prix = array();
    //     while ($tab_prix = odbc_fetch_array($exPrixRental)) {
    //         $Prix[] = $tab_prix;
    //     }
    //     return $Prix;
    // }


    //count si 50 catg 
    /**
     * test si le catgérie appartion à l'agence 50
     */
    // public function SiRentalCatg($catg)
    // {
    //     $sqlcount = "SELECT count(*) as nbCount FROM Idemnity WHERE Catg ='" . $catg . "' and Rmq = '50' ";
    //     $exsqlcount = $this->connexion->query($sqlcount);
    //     return $exsqlcount ? odbc_fetch_array($exsqlcount)['nbCount'] : false;
    // }

    /*
    public function agenceDebiteur()
    {
        $statement = " SELECT 
        a.code_agence + ' ' + a.libelle_agence as agenceDebiteur 
        FROM agences a";

        $sql= $this->connexion->query($statement);
        $agences = array();
        while ($tab = odbc_fetch_array($sql)) {
            $agences[] = $tab;
        }
        return $agences;
    }
    
    public function serviceDebiteur($agenceId)
    {
        $statement = "SELECT DISTINCT 
        s.code_service + ' ' + s.libelle_service as serviceDebiteur
        FROM services s
        INNER JOIN agence_service ags ON s.id = ags.service_id
        INNER JOIN agences a ON a.id = ags.agence_id
        WHERE a.code_agence + ' ' + a.libelle_agence = '". $agenceId ."'
        ";
        $sql= $this->connexion->query($statement);
        $services = array();
        while ($tab = odbc_fetch_array($sql)) {
            $services[] = $tab;
        }
        return $services;
    }
        */
}
