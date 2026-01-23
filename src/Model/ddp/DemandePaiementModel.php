<?php

namespace App\Model\ddp;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DemandePaiementModel extends Model
{
    use ConversionModel;

    public function recupInfoFournissseur()
    {
        $statement = " SELECT 
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

    public function getFournisseur()
    {
        $statement = "SELECT 
                        FBSE_NUMFOU AS num_fournisseur,
                        UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur
                    FROM 
                        FRN_BSE
                    GROUP BY 
                        FBSE_NUMFOU
                    ORDER BY 
                        nom_fournisseur 
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function findListeGcot(string $numeroFournisseur, string  $numCdesString, string $numFacString): array
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
            and TRZT_Facture.Numero_Facture not in ({$numFacString})
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            group by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
            order by TRZT_Dossier_Douane.Code_Fournisseur, TRZT_Dossier_Douane.Libelle_Fournisseur,TRZT_Dossier_Douane.Numero_Dossier_Douane, TRZT_Dossier_Douane.Numero_LTA, TRZT_Dossier_Douane.Numero_HAWB,TRZT_Facture.Numero_Facture, GCOT_Facture_Ligne.Numero_PO
            ";
        return $this->retournerResultGcot04($sql);
    }

    public function getMontantFacGcot(string $numeroFournisseur, string  $numCdesString, string $numfacture): array
    {
        $sql = "SELECT sum(montant_fob) as montantfacture  from TRZT_Facture where Numero_Facture in ({$numfacture})";

        return array_column($this->retournerResultGcot04($sql), 'montantfacture');
    }



    public function finListFacGcot(string $numeroFournisseur, string  $numCdesString): array
    {
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

        return array_column($this->retournerResultGcot04($sql), 'Numero_Facture');
    }

    public function getNumDossierGcot(string $numeroFournisseur, string  $numCdesString, string $numFactString): array
    {
        $sql = " SELECT  DISTINCT

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

    public function getNumCommande(string $numeroFournisseur, string  $numCdesString, string $numFactString): array
    {
        $sql = " SELECT DISTINCT

            GCOT_Facture_Ligne.Numero_PO as numerocde
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
            and TRZT_Facture.Numero_Facture in ({$numFactString})
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            ";
        return array_column($this->retournerResultGcot04($sql), 'numerocde');
    }


    public function findListeDoc($numeroDossier)
    {
        $sql = " SELECT  Nom_Fichier, Date_Fichier, Numero_PO
            from GCOT_Gestion_Document
            where Numero_PO='{$numeroDossier}'
            and (Nom_Fichier like '%\PDV%' or Nom_Fichier like '%\LTA%' or Nom_Fichier like '%\HAWB%')
        ";

        return $this->retournerResultGcot04($sql);
    }

    public function cdeFacOuNonFac(string  $numCdesString)
    {
        $statement = "SELECT ffac_facext  
                    FROM  frn_fac 
                    WHERE ffac_numfac 
                    IN( SELECT DISTINCT fllf_numfac FROM  frn_llf WHERE fllf_numcde = $numCdesString ) 
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function getNumCdeDw()
    {
        $sql = " SELECT DISTINCT numero_cde as numcde
                FROM DW_Commande where path is not null
        ";
        return array_column($this->retournerResult28($sql), 'numcde');
    }


    public function getPathDwCommande(string $numCde): array
    {
        $sql = " SELECT DISTINCT  path, numero_cde from DW_Commande where numero_cde='{$numCde}' and date_creation = (select max(date_creation) from DW_Commande where numero_cde='{$numCde}' )";

        return $this->retournerResult28($sql);
    }

    public function getMontantCdeAvance(string $numCde)
    {
        $statement = " SELECT  sum(fcdl_pxach * fcdl_qte) as montantCde from frn_cdl where fcdl_numcde in ({$numCde})
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function getModePaiement()
    {
        $statement = " SELECT TRIM(atab_lib) as atablib 
                        from agr_tab 
                        where atab_nom='PAI'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'atablib');
    }

    public function getDevise()
    {
        $statement = " SELECT adev_code as adevcode, 
                            TRIM(adev_lib)as adevlib 
                        from agr_dev
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    /**
     * Recupère le numero de dossier de douane
     *
     * @param string $numeroFournisseur
     * @param string $numCdesString
     * @return array retourne une ou plusieurs valeur
     */
    public function getNumDossierDouane(string $numeroFournisseur, string  $numCdesString, string $numFacture): array
    {
        $sql = " SELECT  DISTINCT

                TRZT_Dossier_Douane.Numero_Dossier_Douane 

                from TRZT_Dossier_Douane
                LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
                LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
                LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
                where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
                and TRZT_Facture.Numero_Facture in ({$numFacture})
                and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
                and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            ";
        return $this->retournerResultGcot04($sql);
    }

    public function getFactureNonReglee(string $numeroFournisseur)
    {
        $statement = " SELECT 'PDV_'||''||trim(tecr_nopiec) as facture_non_lettree
                        FROM trs_ecr 
                        WHERE tecr_nocpp = '{$numeroFournisseur}'
                        and tecr_codjou = 'Achmag'
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'facture_non_lettree');
    }

    public function getCommandeReceptionnee(string $numeroFournisseur)
    {
        $statement = " SELECT distinct fllf_numcde as commande_receptionnee from frn_llf
                    inner join frn_liv on fliv_numliv = fllf_numliv and fliv_soc = fllf_soc and fliv_succ = fllf_succ and fliv_soc = 'HF'
                    where fliv_numfou = '{$numeroFournisseur}'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'commande_receptionnee');
    }

    public function recupInfoPourDa(string $numeroFournisseur, string $numCde)
    {
        $statement = " SELECT 
                    FBSE_NUMFOU AS num_fournisseur,
                    UPPER(MIN(FBSE_NOMFOU)) AS nom_fournisseur,  -- Prend un seul nom fournisseur (Beneficiaire)
                    MIN(fbse_devise) AS devise,                  -- Prend une seule devise
                    TRIM(MIN(CASE
                        WHEN ffou_modp = 'CB' THEN 'CARTE BANCAIRE'
                        WHEN ffou_modp = 'CD' THEN 'CHEQUE DIFFERE'
                        WHEN ffou_modp = 'CH' THEN 'CHEQUE COMPTANT'
                        WHEN ffou_modp = 'CO' THEN 'ESPECES COMPTANT'
                        WHEN ffou_modp = 'TA' THEN 'TRAITE'
                        WHEN ffou_modp = 'VI' THEN 'VIREMENT'
                        ELSE ffou_modp
                    END)) AS mode_paiement,
                    TRIM(MIN(CASE
                        WHEN fbqe_ciban = '' OR fbqe_ciban = 'MG' THEN fbqe_bqcpte
                        ELSE fbqe_ciban
                    END)) AS rib_fournisseur,
                    fcde_succ as code_agence, 
                    fcde_serv as code_service,
                    fcde_numcde as numero_cde
                FROM 
                    informix.FRN_BSE
                JOIN 
                    informix.FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                JOIN
                    informix.fou_bqe ON fbqe_numfou = fbse_numfou
               	JOIN
                    informix.frn_cde ON fcde_numfou = fbse_numfou
                WHERE 
                    FFOU_SOC = 'HF'
                    AND fcde_numcde = '{$numCde}'
                    AND fbse_numfou = '{$numeroFournisseur}'
                GROUP BY 
                    FBSE_NUMFOU, code_agence, code_service, numero_cde
                ORDER BY 
                    nom_fournisseur

        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getCodeAgenceService(int $numOr)
    {
        $statement = " SELECT 
                    seor_succ as code_agence, 
                    seor_servcrt as code_service 
                    from informix.sav_eor 
                    where seor_numor = '$numOr'
        ";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getMontantTotalCde(int $numCde): float
    {
        $statement = " SELECT fcde_mtn as montant_total_cde
                from informix.frn_cde 
                where fcde_numcde ='$numCde'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'montant_total_cde')[0];
    }
}
