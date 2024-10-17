<?php

namespace App\Model\dw;

use App\Controller\Traits\ConversionTrait;
use App\Model\Model;

class DossierInterventionAtelierModel extends Model
{

    use ConversionTrait;

    public function findAllDwDit()
    {
        $sql =" SELECT 
            dit.date_creation AS date_creation_intervention,
            dit.numero_dit AS numero_dit_intervention,
            dit.type_reparation AS type_reparation_intervention,
            dit.id_materiel AS id_materiel_intervention,
            dit.numero_parc AS numero_parc_intervention,
            dit.numero_serie AS numero_serie_intervention,
            dit.designation_materiel AS designation_materiel_intervention,
            ord.numero_or AS numero_or_reparation
            FROM DW_Demande_Intervention dit
            LEFT JOIN DW_Ordre_De_Reparation ord 
            ON dit.numero_dit = ord.numero_dit 
            ORDER BY dit.date_creation DESC
        ";

        $exec = $this->connexion->query($sql);
        $tab =[];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        ;
        
        return $this->ConvertirEnUtf_8($tab);
    }

    public function findAll($numDit)
    {
        $sql = " SELECT 
        -- DEMANDE D'INTERVENTION
        dit.numero_dit AS numero_dit_intervention,
        dit.date_creation AS date_creation_intervention,
        dit.date_derniere_modification AS date_modification_intervention,
        dit.extension_fichier As extension_fichier_intervention,
        dit.type_reparation AS type_reparation_intervention,
        dit.id_materiel AS id_materiel_intervention,
        dit.numero_parc AS numero_parc_intervention,
        dit.numero_serie AS numero_serie_intervention,
        dit.designation_materiel AS designation_materiel_intervention,
        dit.total_page AS total_page_intervention,
        dit.taille_fichier AS taille_fichier_intervention,
        dit.path AS path_intervention,
        
        --ORDRE DE REPARATION
        ord.numero_or AS numero_or_reparation,
        ord.date_creation AS date_creation_reparation,
        ord.date_derniere_modification AS date_modification_reparation,
        ord.statut_or AS statut_or_reparation,
        ord.extension_fichier As extension_fichier_reparation,
        ord.total_page AS total_page_reparation,
        ord.taille_fichier AS taille_fichier_reparation,
        ord.path AS path_reparation,
        
        --FACTURE
        fac.numero_fac AS numero_facture,
        fac.date_creation AS date_creation_facture,
        fac.date_derniere_modification AS date_modification_facture,
        fac.extension_fichier As extension_fichier_facture,
        fac.total_page AS total_page_facture,
        fac.taille_fichier AS taille_fichier_facture,
        fac.path AS path_facture,
        
        --RAPORT D'INTERVENTION
        ri.numero_ri AS numero_rapport_intervention,
        ri.date_creation AS date_creation_rapport_intervention,
        ri.date_derniere_modification AS date_modification_rapport_intervention,
        ri.extension_fichier As extension_fichier_rapport_intervention,
        ri.total_page AS total_page_rapport_intervention,
        ri.taille_fichier AS taille_fichier_rapport_intervention,
        ri.path AS path_rapport_intervention,
        
        --COMMANDE
        cde.numero_cde AS numero_commande,
        cde.date_creation AS date_creation_commande,
        cde.date_derniere_modification AS date_modification_commande,
        cde.extension_fichier As extension_fichier_commande,
        cde.total_page AS total_page_commande,
        cde.taille_fichier AS taille_fichier_commande,
        cde.path AS path_commande

            FROM DW_Demande_Intervention dit
            LEFT JOIN DW_Ordre_De_Reparation ord 
            ON dit.numero_dit = ord.numero_dit
			LEFT JOIN DW_Facture fac
			ON fac.numero_or = ord.numero_or
			LEFT JOIN DW_Rapport_Intervention ri
			ON ri.numero_or = ord.numero_or
			LEFT JOIN DW_Commande cde
			ON cde.numero_or = ord.numero_or
			WHERE dit.numero_dit = '".$numDit."'
        ";

        $exec = $this->connexion->query($sql);

     $result = odbc_fetch_array($exec);
        return $this->ConvertirEnUtf_8($result);
    }

    public function findDwDit($numDit)
    {
        $sql =" SELECT 
        -- DEMANDE D'INTERVENTION
        dit.numero_dit AS numero_doc,
        dit.date_creation AS date_creation,
        dit.date_derniere_modification AS date_modification,
        dit.extension_fichier As extension_fichier,
        dit.total_page AS total_page,
        dit.taille_fichier AS taille_fichier,
        dit.path AS chemin

        FROM DW_Demande_Intervention dit
        WHERE dit.numero_dit = '".$numDit."'
        ";

        $exec = $this->connexion->query($sql);
        $tab =[];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        
        return $this->ConvertirEnUtf_8($tab);
    }

    public function findDwOr($numDit)
    {
        $sql =" SELECT 
        --ORDRE DE REPARATION
        ord.numero_or AS numero_doc,
        ord.date_creation AS date_creation,
        ord.date_derniere_modification AS date_modification,
        ord.extension_fichier As extension_fichier,
        ord.total_page AS total_page,
        ord.taille_fichier AS taille_fichier,
        ord.path AS chemin,
        ord.numero_version AS numero_version,
        ord.statut_or AS statut_or

        FROM DW_Ordre_De_Reparation ord
        WHERE ord.numero_dit = '".$numDit."'
        ORDER BY ord.numero_version ASC
        ";

        $exec = $this->connexion->query($sql);
        $tab =[];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return $this->ConvertirEnUtf_8($tab);
    }

    public function findDwFac($numOr)
    {
        $sql =" SELECT 
        --FACTURE
        fac.numero_fac AS numero_doc,
        fac.date_creation AS date_creation,
        fac.date_derniere_modification AS date_modification,
        fac.extension_fichier As extension_fichier,
        fac.total_page AS total_page,
        fac.taille_fichier AS taille_fichier,
        fac.path AS chemin

        FROM DW_Facture fac
        WHERE fac.numero_or = '".$numOr."'
        ";

        $exec = $this->connexion->query($sql);
        $tab =[];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return $this->ConvertirEnUtf_8($tab);
    }

    public function findDwRi($numOr)
    {
        $sql =" SELECT 
            --RAPORT D'INTERVENTION
            ri.numero_ri AS numero_doc,
            ri.date_creation AS date_creation,
            ri.date_derniere_modification AS date_modification,
            ri.extension_fichier As extension_fichier,
            ri.total_page AS total_page,
            ri.taille_fichier AS taille_fichier,
            ri.path AS chemin

            FROM DW_Rapport_Intervention ri
            WHERE ri.numero_or = '".$numOr."'
        ";

        $exec = $this->connexion->query($sql);
        $tab =[];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return $this->ConvertirEnUtf_8($tab);
    }

    public function findDwCde($numOr)
    {
        $sql =" SELECT 
            --COMMANDE
            cde.numero_cde AS numero_doc,
            cde.date_creation AS date_creation,
            cde.date_derniere_modification AS date_modification,
            cde.extension_fichier As extension_fichier,
            cde.total_page AS total_page,
            cde.taille_fichier AS taille_fichier,
            cde.path AS chemin

            FROM DW_Commande cde
            WHERE cde.numero_or = '".$numOr."'
        ";

        $exec = $this->connexion->query($sql);
        $tab =[];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return $this->ConvertirEnUtf_8($tab);
    }

}