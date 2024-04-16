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
}
