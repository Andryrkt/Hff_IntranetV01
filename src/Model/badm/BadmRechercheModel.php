<?php

namespace App\Model\badm;

use PDO;
use PDOException;
use App\Model\Model;
use App\Model\Traits\ConversionModel;

class BadmRechercheModel extends Model
{

    use BadmModelTrait;
    use ConversionModel;


    /**
     * @Andryrkt 
     * cette fonction récupère les données dans la base de donnée  
     * rectifier les caractère spéciaux et return un tableau
     * pour listeDomRecherhce
     * limiter l'accées des utilisateurs
     */
    public function RechercheBadmMode(string $user): array
    {
        $sql = $this->connexion->query("SELECT 
        dmm.ID_Demande_Mouvement_Materiel, 
        sd.Description AS Statut,
        dmm.Numero_Demande_BADM, 
        tm.Description, 
        dmm.ID_Materiel,
        dmm.Date_Demande,
        dmm.Agence_Service_Emetteur, 
        dmm.Casier_Emetteur,
        dmm.Agence_Service_Destinataire ,
        dmm.Casier_Destinataire, 
        dmm.Motif_Arret_Materiel, 
        dmm.Etat_Achat, 
        dmm.Date_Mise_Location, 
        dmm.Cout_Acquisition, 
        dmm.Amortissement, 
        dmm.Valeur_Net_Comptable, 
        dmm.Nom_Client, 
        dmm.Modalite_Paiement, 
        dmm.Prix_Vente_HT, 
        dmm.Motif_Mise_Rebut, 
        dmm.Heure_machine, 
        dmm.KM_machine,
		dmm.ID_Statut_Demande
        FROM Demande_Mouvement_Materiel dmm  
		LEFT JOIN Statut_demande sd ON  sd.ID_Statut_Demande = dmm.ID_Statut_Demande 
        INNER JOIN Type_Mouvement tm ON dmm.Code_Mouvement = tm.ID_Type_Mouvement
		WHERE dmm.Numero_Demande_BADM LIKE 'BDM%' AND dmm.ID_Statut_Demande NOT IN (9, 18, 22, 24, 26, 32, 33, 34, 35)                                                                 
		AND dmm.Nom_Session_Utilisateur = '{$user}'
        ORDER BY Numero_Demande_BADM DESC

    ");


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

        return $this->convertirEnUtf8($tab);
    }


    /**
     * @Andryrkt 
     * cette fonction récupère les données dans la base de donnée  
     * rectifier les caractère spéciaux et return un tableau
     * pour listeDomRecherhce
     */
    public function RechercheBadmModelAll(): array
    {
        $sql = $this->connexion->query("SELECT 
        dmm.ID_Demande_Mouvement_Materiel, 
        sd.Description AS Statut,
        dmm.Numero_Demande_BADM, 
        tm.Description, 
        dmm.ID_Materiel,
        dmm.Date_Demande,
        dmm.Agence_Service_Emetteur, 
        dmm.Casier_Emetteur,
        dmm.Agence_Service_Destinataire ,
        dmm.Casier_Destinataire, 
        dmm.Motif_Arret_Materiel, 
        dmm.Etat_Achat, 
        dmm.Date_Mise_Location, 
        dmm.Cout_Acquisition, 
        dmm.Amortissement, 
        dmm.Valeur_Net_Comptable, 
        dmm.Nom_Client, 
        dmm.Modalite_Paiement, 
        dmm.Prix_Vente_HT, 
        dmm.Motif_Mise_Rebut, 
        dmm.Heure_machine, 
        dmm.KM_machine,
		dmm.ID_Statut_Demande
        FROM Demande_Mouvement_Materiel dmm  
		LEFT JOIN Statut_demande sd ON  sd.ID_Statut_Demande = dmm.ID_Statut_Demande 
        INNER JOIN Type_Mouvement tm ON dmm.Code_Mouvement = tm.ID_Type_Mouvement
		WHERE dmm.Numero_Demande_BADM LIKE 'BDM%'  AND  dmm.ID_Statut_Demande NOT IN (9, 18, 22, 24, 26, 32, 33, 34, 35)                                                                
        ORDER BY Numero_Demande_BADM DESC

    ");


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

        return $this->convertirEnUtf8($tab);
    }

    


    public function findDesiSerieParc($matricule = ''): array
    {
        $statement = "SELECT

        trim(mmat_desi) as designation,
        trim(mmat_numserie) as num_serie,
        trim(mmat_recalph) as num_parc

        from mat_mat
        WHERE mmat_nummat ='" . $matricule . "'
      ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

}
