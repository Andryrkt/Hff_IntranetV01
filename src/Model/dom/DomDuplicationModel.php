<?php


namespace App\Model\dom;

use App\Model\Model;

class DomDuplicationModel extends Model
{


    public function DuplicaftionFormModel($numDom, $IdDom)
    {
        $Sql = "SELECT 
        (select TOP 1 agence_ips from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Code_agence,
        (select TOP 1 nom_agence_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Libelle_agence,
        (select TOP 1 service_ips from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Code_Service,
        (select TOP 1 nom_service_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Libelle_service,
         Date_Demande,
		 Debiteur,
		 Emetteur,
		Sous_type_document,
		Categorie,
        Site,
		Matricule,
        Nom,
        Prenom,
        Date_Debut,
        Date_Fin,
		Heure_Debut,
		Heure_Fin,
		Nombre_Jour,
        Client,
		Motif_Deplacement,
		Fiche,
       Lieu_Intervention,
	    Vehicule_Societe,
        NumVehicule,
		idemnity_depl,
		Devis,
		Doit_indemnite,
		Motif_Autres_depense_1,
        Autres_depense_1,
        Motif_Autres_depense_2,
        Autres_depense_2,
        Motif_Autres_depense_3,
        Autres_depense_3,
		Total_Autres_Depenses,
        Total_General_Payer,
        Mode_Paiement,
		Piece_Jointe_1,
		Piece_Jointe_2,
        Total_Indemnite_Forfaitaire,
        Indemnite_Forfaitaire
        
        FROM Demande_ordre_mission
        WHERE Numero_Ordre_Mission = '" . $numDom . "'
        AND ID_Demande_Ordre_Mission ='" . $IdDom . "' 
        ";
        $excecSelectDom = $this->connexion->query($Sql);
        $ListselectDom = array();
        while ($tablistselectDom = odbc_fetch_array($excecSelectDom)) {
            $ListselectDom[] = $tablistselectDom;
        }
        return $ListselectDom;
    }

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

    // private function clean_string($string)
    // {
    //     return mb_convert_encoding($string, 'ASCII', 'UTF-8');
    // }

    public function DuplicaftionFormJsonModel()
    {
        $Sql = "SELECT 
        (select TOP 1 agence_ips from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Code_agence,
        (select TOP 1 nom_agence_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Libelle_agence,
        (select TOP 1 service_ips from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Code_Service,
        (select TOP 1 nom_service_i100 from Agence_Service_Irium where agence_ips+service_ips = Code_AgenceService_Debiteur) as Libelle_service,		 Debiteur,
		 Emetteur,
        Site,
        Numero_Ordre_Mission,
        ID_Demande_Ordre_Mission       
        FROM Demande_ordre_mission 
        ";
        $excecDuplication = $this->connexion->query($Sql);
        $listDuplication = [];
        while ($tabDuplication = odbc_fetch_array($excecDuplication)) {
            $listDuplication[] = $tabDuplication;
        }

        // Parcourir chaque élément du tableau $tab
        foreach ($listDuplication as $key => &$value) {
            // Parcourir chaque valeur de l'élément et nettoyer les données
            foreach ($value as &$inner_value) {
                $inner_value = $this->clean_string($inner_value);
            }
        }

        return $this->decode_entities_in_array($listDuplication);
    }
}
