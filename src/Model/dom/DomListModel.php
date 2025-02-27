<?php

namespace App\Model\dom;

use Exception;
use App\Model\Model;

class DomListModel extends Model
{
    /**
     * convertir en UTF_8
     */
    // private function convertirEnUtf8($element)
    // {
    //     if (is_array($element)) {
    //         foreach ($element as $key => $value) {
    //             $element[$key] = $this->convertirEnUtf8($value);
    //         }
    //     } elseif (is_string($element)) {
    //         return mb_convert_encoding($element, 'UTF-8', 'ISO-8859-1');
    //     }
    //     return $element;
    // }

    /**
     * c'est une foncion qui décode les caractères speciaux en html
     */
    // private function decode_entities_in_array($array)
    // {
    //     // Parcourir chaque élément du tableau
    //     foreach ($array as $key => $value) {
    //         // Si la valeur est un tableau, appeler récursivement la fonction
    //         if (is_array($value)) {
    //             $array[$key] = $this->decode_entities_in_array($value);
    //         } else {
    //             // Si la valeur est une chaîne, appliquer la fonction decode_entities()
    //             $array[$key] = html_entity_decode($value);
    //         }
    //     }
    //     return $array;
    // }


    /**
     * récupere le code Statut et libelle statut 
     */
    public function getListStatut()
    {
        $stat = "SELECT   
        Description 
        FROM Statut_demande 
        WHERE Code_Application = 'DOM'
                ";


        $exstat = $this->connexion->query($stat);
        $ListStat = [];
        while ($tabStat = odbc_fetch_array($exstat)) {
            $ListStat[] = $tabStat;
        }
        return $this->decode_entities_in_array($ListStat);
    }

    /**
     * récupere le sous type de document
     */
    public function recupSousType()
    {
        $statement = "SELECT Code_Sous_Type FROM Sous_type_document WHERE Code_Sous_Type <> ''";
        $exstat = $this->connexion->query($statement);
        $sousType = [];
        while ($tabStat = odbc_fetch_array($exstat)) {
            $sousType[] = $tabStat;
        }

        return $sousType;
    }



    /**
     * @Andryrkt 
     * cette fonction récupère les données dans la base de donnée  
     * rectifier les caractère spéciaux et return un tableau
     * pour listeDomRecherhce
     * limiter l'accées des utilisateurs
     */
    public function RechercheModel( $tab, $page, $pageSize, $ConnectUser): array
    {
        $offset = intval(($page - 1) * $pageSize); 
    
        $sql = "SELECT 
            DOM.ID_Demande_Ordre_Mission, 
            SD.Description,
            DOM.Sous_type_document,
            DOM.Numero_Ordre_Mission,
            DOM.Date_Demande,
            DOM.Motif_Deplacement,
            DOM.Matricule,
            DOM.Nom, 
            DOM.Prenom,
            DOM.Mode_Paiement,
            CAST(ASI.nom_agence_i100 AS VARCHAR(MAX)) + ' - ' + CAST(ASI.nom_service_i100 AS VARCHAR(MAX)) AS LibelleCodeAgence_Service, 
            DOM.Date_Debut, 
            DOM.Date_Fin,   
            DOM.Nombre_Jour, 
            DOM.Client,
            DOM.Fiche,
            DOM.Lieu_Intervention,
            DOM.NumVehicule,
            DOM.Total_Autres_Depenses,
            DOM.Total_General_Payer,
            DOM.Devis
        FROM Demande_ordre_mission DOM 
        LEFT JOIN Statut_demande SD ON DOM.ID_Statut_Demande = SD.ID_Statut_Demande
        LEFT JOIN Agence_Service_Irium ASI ON ASI.agence_ips + ASI.service_ips = DOM.Code_AgenceService_Debiteur
        ";
    
        // Build the WHERE clause based on provided filters
       
        $conditions = $this->buildConditions($tab);

    

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
            $sql .= " AND DOM.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
        FROM Agence_service_autorise 
        WHERE Session_Utilisateur = '" . $ConnectUser . "' ) 
        ORDER BY Numero_Ordre_Mission DESC OFFSET {$offset} ROWS FETCH NEXT {$pageSize} ROWS ONLY";
        } else {
            $sql .= " WHERE DOM.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
            FROM Agence_service_autorise 
            WHERE Session_Utilisateur = '" . $ConnectUser . "' ) 
            ORDER BY Numero_Ordre_Mission DESC OFFSET {$offset} ROWS FETCH NEXT {$pageSize} ROWS ONLY";
        }
    

        $statement = $this->connexion->query($sql);
        // Prepare and execute SQL statement
        //$stmt = $this->prepareAndExecute($conn, $sql, $params);
    
        // Fetch and clean results
        return $this->fetchAndCleanResults($statement);
    }

    public function getTotalRecords($tab, $ConnectUser) {
    
        $sql = "SELECT COUNT(*) AS total FROM Demande_ordre_mission DOM
                LEFT JOIN Statut_demande SD ON DOM.ID_Statut_Demande = SD.ID_Statut_Demande
                ";
    
        $params = [];
        $conditions = [];
    
        $conditions = $this->buildConditions($tab);
    
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
            $sql .= " AND DOM.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
        FROM Agence_service_autorise 
        WHERE Session_Utilisateur = '{$ConnectUser}' )";
        } else {
            $sql .= " WHERE DOM.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
        FROM Agence_service_autorise 
        WHERE Session_Utilisateur = '{$ConnectUser}' )";
        }

        
    
        $statement = $this->connexion->query($sql);
     
    
        // Fetch the results
        $result = odbc_fetch_array($statement);
    
        return $result['total'];
    }

    /**
     * @Andryrkt 
     * cette fonction récupère les données dans la base de donnée  
     * rectifier les caractère spéciaux et return un tableau
     * pour listeDomRecherhce
     * limiter l'accées des utilisateurs
     */
    public function RechercheModelAll($tab, $page, $pageSize): array
    {
            
        $offset = intval(($page - 1) * $pageSize); 
    
        $sql = "SELECT 
            DOM.ID_Demande_Ordre_Mission, 
            SD.Description,
            DOM.Sous_type_document,
            DOM.Numero_Ordre_Mission,
            DOM.Date_Demande,
            DOM.Motif_Deplacement,
            DOM.Matricule,
            DOM.Nom, 
            DOM.Prenom,
            DOM.Mode_Paiement,
            CAST(ASI.nom_agence_i100 AS VARCHAR(MAX)) + ' - ' + CAST(ASI.nom_service_i100 AS VARCHAR(MAX)) AS LibelleCodeAgence_Service, 
            DOM.Date_Debut, 
            DOM.Date_Fin,   
            DOM.Nombre_Jour, 
            DOM.Client,
            DOM.Fiche,
            DOM.Lieu_Intervention,
            DOM.NumVehicule,
            DOM.Total_Autres_Depenses,
            DOM.Total_General_Payer,
            DOM.Devis
        FROM Demande_ordre_mission DOM 
        LEFT JOIN Statut_demande SD ON DOM.ID_Statut_Demande = SD.ID_Statut_Demande
        LEFT JOIN Agence_Service_Irium ASI ON ASI.agence_ips + ASI.service_ips = DOM.Code_AgenceService_Debiteur
        ";
    
        // Build the WHERE clause based on provided filters
       
        $conditions = $this->buildConditions($tab);

 

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
    
        $sql .= " ORDER BY Numero_Ordre_Mission DESC OFFSET {$offset} ROWS FETCH NEXT {$pageSize} ROWS ONLY";

        
        $statement = $this->connexion->query($sql);
        // Prepare and execute SQL statement
        //$stmt = $this->prepareAndExecute($conn, $sql, $params);
    
        // Fetch and clean results
        return $this->fetchAndCleanResults($statement);
    }
    


    private function buildConditions(array $tab): array
{
    $conditions = [];
   
    
    if(!empty($tab)){
        if (!empty($tab['dateDemandeDebut'])) {

            $conditions[]= "Date_Demande >=  '{$tab['dateDemandeDebut']}'";
        }
        if(!empty($tab['dateDemandeFin'])) {
            $conditions[]= "Date_Demande <= '{$tab['dateDemandeFin']}'";
        }
        if(!empty($tab['dateMissionDebut'])){
            $conditions[]= "Date_Debut >= '{$tab['dateMissionDebut']}'";
        } 
        if(!empty($tab['dateMissionFin'])){
            $conditions[]= "Date_Fin <= '{$tab['dateMissionFin']}'";
        }

        if(!empty($tab['Description']))
        {
            $conditions[] = "Description LIKE '%{$tab['Description']}%'";
        } 

        if (!empty($tab['Sous_type_document'])) {
            $conditions[] = "Sous_type_document LIKE '%{$tab['Sous_type_document']}%'";
        }

        if(!empty($tab['Matricule'])){
            $conditions[] = "Matricule LIKE '%{$tab['Matricule']}%'";
        }
        
        if(!empty($tab['Numero_Ordre_Mission'])){
            $conditions[] = "Numero_Ordre_Mission LIKE '%{$tab['Numero_Ordre_Mission']}%'";
        }
        
        if(isset($tab['exportExcel'])){
            return $conditions;
        }
    }

    
    
    return $conditions;
}

    
   
    
    private function fetchAndCleanResults($stmt): array
    {
        $result = [];
        while ($row = odbc_fetch_array($stmt)) {
            array_walk_recursive($row, function (&$value, $key) {
                $value = $this->clean_string($value);
            });
            $result[] = $this->decode_entities_in_array($row);
        }
        return $result;
    }
    

public function getTotalRecordsAll($tab) {
    
    $sql = "SELECT COUNT(*) AS total FROM Demande_ordre_mission DOM
            LEFT JOIN Statut_demande SD ON DOM.ID_Statut_Demande = SD.ID_Statut_Demande";

    $params = [];
    $conditions = [];

    $conditions = $this->buildConditions($tab);

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $statement = $this->connexion->query($sql);
 

    // Fetch the results
    $result = odbc_fetch_array($statement);

    return $result['total'];
}


    // private function clean_string($string)
    // {
    //     return mb_convert_encoding($string, 'ASCII', 'UTF-8');
    // }

    // private function TestCaractereSpeciaux(array $tab)
    // {
    //     function contains_special_characters($string)
    //     {
    //         // Expression régulière pour vérifier les caractères spéciaux
    //         return preg_match('/[^\x20-\x7E\t\r\n]/', $string);
    //     }

    //     // Parcours de chaque élément du tableau $tab
    //     foreach ($tab as $key => $value) {
    //         // Parcours de chaque valeur de l'élément
    //         foreach ($value as $inner_value) {
    //             // Vérification de la présence de caractères spéciaux
    //             if (contains_special_characters($value)) {
    //                 echo "Caractère spécial trouvé dans la valeur : $value<br>";
    //             }
    //         }
    //     }
    // }


    public function RechercheModelExcel($tab, $ConnectUser): array
    {
            
        $sql = "SELECT 
            DOM.ID_Demande_Ordre_Mission, 
            SD.Description,
            DOM.Sous_type_document,
            DOM.Numero_Ordre_Mission,
            DOM.Date_Demande,
            DOM.Motif_Deplacement,
            DOM.Matricule,
            DOM.Nom, 
            DOM.Prenom,
            DOM.Mode_Paiement,
            CAST(ASI.nom_agence_i100 AS VARCHAR(MAX)) + ' - ' + CAST(ASI.nom_service_i100 AS VARCHAR(MAX)) AS LibelleCodeAgence_Service, 
            DOM.Date_Debut, 
            DOM.Date_Fin,   
            DOM.Nombre_Jour, 
            DOM.Client,
            DOM.Fiche,
            DOM.Lieu_Intervention,
            DOM.NumVehicule,
            DOM.Total_Autres_Depenses,
            DOM.Total_General_Payer,
            DOM.Devis
        FROM Demande_ordre_mission DOM 
        LEFT JOIN Statut_demande SD ON DOM.ID_Statut_Demande = SD.ID_Statut_Demande
        LEFT JOIN Agence_Service_Irium ASI ON ASI.agence_ips + ASI.service_ips = DOM.Code_AgenceService_Debiteur
        ";
    
        // Build the WHERE clause based on provided filters
       
        $conditions = $this->buildConditions($tab);

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
            $sql .= " AND DOM.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM) 
        
        FROM Agence_service_autorise 
        WHERE Session_Utilisateur = '" . $ConnectUser . "' ) ORDER BY Numero_Ordre_Mission DESC";
        } else {
            $sql .= " WHERE DOM.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM) 
        
        FROM Agence_service_autorise 
        WHERE Session_Utilisateur = '" . $ConnectUser . "' ) ORDER BY Numero_Ordre_Mission DESC";
        }
        
    
        $statement = $this->connexion->query($sql);
        // Prepare and execute SQL statement
        //$stmt = $this->prepareAndExecute($conn, $sql, $params);
    
        // Fetch and clean results
        return $this->fetchAndCleanResults($statement);
    }

    public function annulationCodestatut($numDom)
    {
        $sql = "UPDATE Demande_ordre_mission SET Code_Statut = ?, ID_Statut_Demande = ? WHERE Numero_Ordre_Mission = ?";
        $params = array('ANN', 9,$numDom);

        $this->connexion->prepareAndExecute($sql, $params);
    }
}
