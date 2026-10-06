<?php

namespace App\Model\ddp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DdpDossierRegulModel extends Model
{
    use ConversionModel;

    public function findListeGcot(string $numeroFournisseur, string  $numCdesString): array
    {
        $sql = " SELECT  
            TRZT_Dossier_Douane.Code_Fournisseur, 
            TRZT_Dossier_Douane.Libelle_Fournisseur,
            TRZT_Dossier_Douane.Numero_Dossier_Douane, 
            TRZT_Dossier_Douane.Numero_LTA, 
            TRZT_Dossier_Douane.Numero_HAWB,
            TRZT_Facture.Numero_Facture, 
            GCOT_Facture_Ligne.Numero_PO
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
            and TRZT_Facture.Numero_Facture like 'PDV_%'
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            group by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
            order by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
        ";

        return $this->retournerResultGcot04($sql);
    }

    public function findListeDoc(string $numeroDossier)
    {
        $sql=" SELECT  Nom_Fichier, Date_Fichier, Numero_PO
            from GCOT_Gestion_Document
            where Numero_PO='{$numeroDossier}'
            and (Nom_Fichier like '%\PDV%' or Nom_Fichier like '%\BOL%' or Nom_Fichier like '%\HAWB%')
        ";

        return $this->retournerResultGcot04($sql);
    }

    
    public function getListeGcot(array $tab): array
    {
        $numeroFournisseur = $tab['numeroFournisseur'];
        $numCdesString = $tab['numeroCommande'];
        $sql = " SELECT  
            TRZT_Dossier_Douane.Numero_Dossier_Douane 
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
            and TRZT_Facture.Numero_Facture like 'PDV_%'
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            group by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
            order by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
        ";

        return array_column($this->retournerResultGcot04($sql), 'Numero_Dossier_Douane') ;
    }

    public function getListeDoc(string $numeroDossier)
    {
        $sql=" SELECT  Nom_Fichier
            from GCOT_Gestion_Document
            where Numero_PO='{$numeroDossier}'
            and (Nom_Fichier like '%\PDV%' or Nom_Fichier like '%\BOL%' or Nom_Fichier like '%\HAWB%')
        ";

        return array_column($this->retournerResultGcot04($sql),'Nom_Fichier');
    }
}
