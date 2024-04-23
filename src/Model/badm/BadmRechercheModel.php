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
    public function RechercheBadmModelAll(): array
    {
        $sql = $this->connexion->query("SELECT 
        dmm.ID_Demande_Mouvement_Materiel, 
        sd.Description AS Statut,
        dmm.Numero_Demande_BADM, 
        dmm.Code_Mouvement, 
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
        dmm.KM_machine
        FROM Demande_Mouvement_Materiel dmm,  Statut_demande sd
        WHERE dmm.Code_Statut = sd.Code_Statut
        AND sd.Code_Application = 'BDM'                                                                 
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

    // public function RechercheBadmModelAll(): array
    // {

    //     $sql = "SELECT 
    //     dmm.ID_Demande_Mouvement_Materiel, 
    //     sd.Description AS Statut,
    //     dmm.Numero_Demande_BADM, 
    //     dmm.Code_Mouvement, 
    //     dmm.ID_Materiel,
    //     dmm.Date_Demande,
    //     dmm.Agence_Service_Emetteur, 
    //     dmm.Casier_Emetteur,
    //     dmm.Agence_Service_Destinataire,
    //     dmm.Casier_Destinataire, 
    //     dmm.Motif_Arret_Materiel, 
    //     dmm.Etat_Achat, 
    //     dmm.Date_Mise_Location, 
    //     dmm.Cout_Acquisition, 
    //     dmm.Amortissement, 
    //     dmm.Valeur_Net_Comptable, 
    //     dmm.Nom_Client, 
    //     dmm.Modalite_Paiement, 
    //     dmm.Prix_Vente_HT, 
    //     dmm.Motif_Mise_Rebut, 
    //     dmm.Heure_machine, 
    //     dmm.KM_machine
    // FROM Demande_Mouvement_Materiel dmm
    // JOIN Statut_demande sd ON dmm.Code_Statut = sd.Code_Statut
    // WHERE sd.Code_Application = 'BDM'
    // ORDER BY Numero_Demande_BADM DESC

    // ";


    //     try {
    //         $stmt = $this->sqlServer->conn->prepare($sql);
    //         $stmt->execute();
    //         $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    //     } catch (PDOException $e) {
    //         echo 'PDOException: ' . $e->getMessage();
    //         // Vous pouvez également enregistrer cette erreur dans un fichier de log si nécessaire
    //         file_put_contents('path_to_log_file', $e->getMessage(), FILE_APPEND);
    //         return [];
    //     }

    //     if (!$results) {
    //         return []; // Si aucun résultat n'est récupéré, retournez un tableau vide pour éviter des erreurs plus loin dans le code
    //     }

    //     // Nettoyer les données
    //     foreach ($results as $result) {
    //         foreach ($result as $value) {
    //             $value = $this->clean_string($value);
    //         }
    //     }

    //     return $this->convertirEnUtf8($results);
    // }
}
