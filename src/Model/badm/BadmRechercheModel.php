<?php

namespace App\Model\badm;

use App\Model\Model;

class BadmRechercheModel extends Model
{

    use BadmModelTrait;

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


        //$this->TestCaractereSpeciaux($tab);




        return $this->convertirEnUtf8($tab);
    }

    function convertirEnUtf8($element)
    {
        if (is_array($element)) {
            foreach ($element as $key => $value) {
                $element[$key] = $this->convertirEnUtf8($value);
            }
        } elseif (is_string($element)) {
            // return mb_convert_encoding($element, 'UTF-8', 'ISO-8859-1');
            return iconv('ISO-8859-1', 'UTF-8', $element);
        }
        return $element;
    }

    private function clean_string($string)
    {
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
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
}
