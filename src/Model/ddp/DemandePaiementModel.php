<?php

namespace App\Model\ddp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DemandePaiementModel extends Model
{
    use ConversionModel;

    public function recupInfoFournissseur()
    {
        $statement=" SELECT 
                    FBSE_NUMFOU AS num_fournisseur,
                    UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur,  -- Prend un seul nom fournisseur
                    MIN(fbse_devise) AS devise,                  -- Prend une seule devise
                    MIN(CASE
                        WHEN ffou_modp = 'CB' THEN 'CARTE BANCAIRE'
                        WHEN ffou_modp = 'CD' THEN 'CHEQUE DIFFERE'
                        WHEN ffou_modp = 'CH' THEN 'CHEQUE COMPTANT'
                        WHEN ffou_modp = 'CO' THEN 'ESPECES COMPTANT'
                        WHEN ffou_modp = 'TA' THEN 'TRAITE'
                        WHEN ffou_modp = 'VI' THEN 'VIREMENT'
                        ELSE ffou_modp
                    END) AS mode_paiement,
                    MIN(CASE
                        WHEN fbqe_ciban = '' OR fbqe_ciban = 'MG' THEN fbqe_bqcpte
                        ELSE fbqe_ciban
                    END) AS rib
                FROM 
                    FRN_BSE
                JOIN 
                    FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                JOIN
                    fou_bqe ON fbqe_numfou = fbse_numfou
                WHERE 
                    FFOU_SOC = 'HF'
                GROUP BY 
                    FBSE_NUMFOU
                ORDER BY 
                    nom_fournisseur;

        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

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

    public function finListFacGcot(string $numeroFournisseur, string  $numCdesString): array{
        $sql = " SELECT  
          distinct 
            TRZT_Facture.Numero_Facture
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
            and TRZT_Facture.Numero_Facture like 'PDV_%'
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
           
        ";

        return array_column($this->retournerResultGcot04($sql),'Numero_Facture');
    }

    public function getNumDossierGcot(string $numeroFournisseur, string  $numCdesString, string $numFactString): array
    {
        $sql = " SELECT  

            TRZT_Dossier_Douane.Numero_Dossier_Douane
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
            and TRZT_Facture.Numero_Facture in ({$numFactString})
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            ";
        return $this->retournerResultGcot04($sql);
    }

    

    public function findListeDoc($numeroDossier)
    {
        $sql=" SELECT  Nom_Fichier, Date_Fichier, Numero_PO
            from GCOT_Gestion_Document
            where Numero_PO='{$numeroDossier}'
            and (Nom_Fichier like '%\PDV%' or Nom_Fichier like '%\BOL%' or Nom_Fichier like '%\HAWB%')
        ";

        return $this->retournerResultGcot04($sql);
    }
}