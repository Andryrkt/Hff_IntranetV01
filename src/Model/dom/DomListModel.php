<?php

namespace App\Model\dom;

use App\Model\Model;

class DomListModel extends Model
{
    /**
     * convertir en UTF_8
     */
    private function convertirEnUtf8($element)
    {
        if (is_array($element)) {
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertirEnUtf8($value);
            }
        } elseif (is_string($element)) {
            return mb_convert_encoding($element, 'UTF-8', 'ISO-8859-1');
        }
        return $element;
    }

    /**
     * c'est une foncion qui décode les caractères speciaux en html
     */
    private function decode_entities_in_array($array)
    {
        // Parcourir chaque élément du tableau
        foreach ($array as $key => $value) {
            // Si la valeur est un tableau, appeler récursivement la fonction
            if (is_array($value)) {
                $array[$key] = $this->decode_entities_in_array($value);
            } else {
                // Si la valeur est une chaîne, appliquer la fonction decode_entities()
                $array[$key] = html_entity_decode($value);
            }
        }
        return $array;
    }


    /**
     * récupere le code Statut et libelle statut 
     */
    public function getListStatut()
    {
        $stat = "SELECT DISTINCT Code_Statut, 
                (SELECT Description FROM Statut_demande SD 
                WHERE SD.Code_Statut = DOM.Code_Statut 
                AND SD.Code_Application = 'DOM')  as 'LibStatut'
                FROM Demande_ordre_mission DOM ";


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
    public function RechercheModel($ConnectUser): array
    {
        $sql = $this->connexion->query("SELECT  
        Demande_ordre_mission.ID_Demande_Ordre_Mission,
        Statut_demande.Description AS Statut,
        Demande_ordre_mission.Sous_type_document,
        Demande_ordre_mission.Numero_Ordre_Mission,
        Demande_ordre_mission.Date_Demande,
        Demande_ordre_mission.Motif_Deplacement,
        Demande_ordre_mission.Matricule,
        Demande_ordre_mission.Nom, 
        Demande_ordre_mission.Prenom,
        Demande_ordre_mission.Mode_Paiement,
   ( SELECT Top 1 Agence_Service_Irium.nom_agence_i100 + ' - ' + Agence_Service_Irium.nom_service_i100 FROM Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur)AS LibelleCodeAgence_Service, 
   Demande_ordre_mission.Date_Debut, 
        Demande_ordre_mission.Date_Fin,   
        Demande_ordre_mission.Nombre_Jour, 
        Demande_ordre_mission.Client,
        Demande_ordre_mission.Fiche,
        Demande_ordre_mission.Lieu_Intervention,
        Demande_ordre_mission.NumVehicule,
        Demande_ordre_mission.Total_Autres_Depenses,
        Demande_ordre_mission.Total_General_Payer,
        Demande_ordre_mission.Devis
            FROM Demande_ordre_mission, Statut_demande
            WHERE Demande_ordre_mission.Code_Statut = Statut_demande.Code_Statut
            AND Demande_ordre_mission.Code_AgenceService_Debiteur IN (SELECT LOWER(Code_AgenceService_IRIUM)  
                                                                    FROM Agence_service_autorise 
                                                                    WHERE Session_Utilisateur = '" . $ConnectUser . "' )
                                                                    
            ORDER BY Numero_Ordre_Mission DESC");


        // Définir le jeu de caractères source et le jeu de caractères cible

        $tab = [];
        while ($donner = odbc_fetch_array($sql)) {

            $tab[] = $donner;
        }


        // Parcourir chaque élément du tableau $tab
        foreach ($tab as $key => &$value) {
            // Parcourir chaque valeur de l'élément et nettoyer les données
            foreach ($value as &$inner_value) {
                $inner_value = $this->clean_string($inner_value);
            }
        }


        $this->TestCaractereSpeciaux($tab);


        return $this->decode_entities_in_array($tab);
    }



    /**
     * @Andryrkt 
     * cette fonction récupère les données dans la base de donnée  
     * rectifier les caractère spéciaux et return un tableau
     * pour listeDomRecherhce
     * limiter l'accées des utilisateurs
     */
    public function RechercheModelAll(): array
    {
        $sql = $this->connexion->query("SELECT 
        DOM.ID_Demande_Ordre_Mission, 
        (SELECT TOP 1 SD.Description 
        FROM Statut_demande SD 
        WHERE SD.Code_Application = 'DOM' 
        AND DOM.Code_Statut = SD.Code_Statut) AS Statut,
        DOM.Sous_type_document,
        DOM.Numero_Ordre_Mission,
        DOM.Date_Demande,
        DOM.Motif_Deplacement,
        DOM.Matricule,
        DOM.Nom, 
        DOM.Prenom,
        DOM.Mode_Paiement,
        (SELECT TOP 1 nom_agence_i100 + ' - ' + nom_service_i100 
         FROM Agence_Service_Irium 
         WHERE agence_ips + service_ips = DOM.Code_AgenceService_Debiteur) AS LibelleCodeAgence_Service, 
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
                                                                           
            ORDER BY Numero_Ordre_Mission DESC");


        // Définir le jeu de caractères source et le jeu de caractères cible

        $tab = [];
        while ($donner = odbc_fetch_array($sql)) {

            $tab[] = $donner;
        }


        // Parcourir chaque élément du tableau $tab
        foreach ($tab as $key => &$value) {
            // Parcourir chaque valeur de l'élément et nettoyer les données
            foreach ($value as &$inner_value) {
                $inner_value = $this->clean_string($inner_value);
            }
        }


        $this->TestCaractereSpeciaux($tab);


        return $this->decode_entities_in_array($tab);
    }


    private function clean_string($string)
    {
        return mb_convert_encoding($string, 'ASCII', 'UTF-8');
    }

    private function TestCaractereSpeciaux(array $tab)
    {
        function contains_special_characters($string)
        {
            // Expression régulière pour vérifier les caractères spéciaux
            return preg_match('/[^\x20-\x7E\t\r\n]/', $string);
        }

        // Parcours de chaque élément du tableau $tab
        foreach ($tab as $key => $value) {
            // Parcours de chaque valeur de l'élément
            foreach ($value as $inner_value) {
                // Vérification de la présence de caractères spéciaux
                if (contains_special_characters($inner_value)) {
                    echo "Caractère spécial trouvé dans la valeur : $inner_value<br>";
                }
            }
        }
    }
}
