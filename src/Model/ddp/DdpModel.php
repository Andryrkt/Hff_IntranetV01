<?php

namespace App\Model\ddp;

use App\Model\Model;

class DdpModel extends Model
{
    /**
     * Récupération les modes de paiement
     * dans la base de donnée Informix ip_hhfprod
     *
     * @return array
     */
    public function getModePaiement(): array
    {
        $statement = " SELECT TRIM(atab_lib) as atablib 
                        from agr_tab 
                        where atab_nom='PAI'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return array_column($this->convertirEnUtf8($data), 'atablib');
    }

    /**
     * Récupération les devises
     * dans la base de donnée Informix ip_hhfprod
     *
     * @return array
     */
    public function getDevise(): array
    {
        $statement = " SELECT adev_code as adevcode, 
                            TRIM(adev_lib)as adevlib 
                        from agr_dev
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
    }

    public function cdeFacOuNonFac(string  $numCde)
    {
        $statement = "SELECT ffac_facext  
                    FROM  frn_fac 
                    WHERE ffac_numfac 
                    IN( SELECT DISTINCT fllf_numfac FROM  frn_llf WHERE fllf_numcde = $numCde ) 
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        return $this->convertirEnUtf8($data);
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

    public function getNumCommande(
        string $numeroFournisseur,
        string $numCdesString,
        ?string $numFactString
    ): string {
        if (!empty($numFactString)) {
            $numFac = "and TRZT_Facture.Numero_Facture in ({$numFactString})";
        } else {
            $numFac = '';
        }

        $sql = " SELECT DISTINCT
        GCOT_Facture_Ligne.Numero_PO as numerocde
        from TRZT_Dossier_Douane
        LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
        LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
        LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
        where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
        $numFac
        and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
        and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
        ";

        $result = array_column($this->retournerResultGcot04($sql), 'numerocde');

        // Retourner la première valeur ou une chaîne vide
        return !empty($result) ? (string) $result[0] : '';
    }

    public function getCommandeReceptionnee(string $numeroFournisseur): array
    {
        $statement = " SELECT distinct fllf_numcde as commande_receptionnee from frn_llf
                inner join frn_liv 
                    on fliv_numliv = fllf_numliv 
                    and fliv_soc = fllf_soc 
                    and fliv_succ = fllf_succ 
                    and fliv_soc = 'HF'
                where fliv_numfou = '{$numeroFournisseur}'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $data = $this->convertirEnUtf8($data);

        return array_column($data, 'commande_receptionnee');
    }

    /**
     * Récupération des numéro de Dossier de Douane dans GCOT
     * Base de donnée 192.168.0.4 (sqlServeur)
     */
    public function getNumDossierGcot(string $numeroFournisseur, string  $numCdesString, ?string $numFactString): array
    {
        if (!empty($numFactString)) {
            $numFac = " and TRZT_Facture.Numero_Facture in ({$numFactString})";
        } else {
            $numFac = '';
        }
        $sql = " SELECT  DISTINCT
            TRZT_Dossier_Douane.Numero_Dossier_Douane
            from TRZT_Dossier_Douane
            LEFT JOIN TRZT_Facture on TRZT_Dossier_Douane.Numero_Dossier_Douane = TRZT_Facture.Numero_Dossier_Douane
            LEFT JOIN GCOT_Facture on TRZT_Facture.Numero_Facture = GCOT_Facture.Numero_Facture
            LEFT JOIN GCOT_Facture_Ligne on GCOT_Facture.ID_GCOT_Facture = GCOT_Facture_Ligne.ID_GCOT_Facture
            where TRZT_Dossier_Douane.Numero_Dossier_Douane like '%' 
            $numFac
            and TRZT_Dossier_Douane.Code_Fournisseur = '{$numeroFournisseur}'
            and GCOT_Facture_Ligne.Numero_PO in ({$numCdesString})
            ";
        return $this->retournerResultGcot04($sql);
    }

    public function findListeDoc(string $numeroDossier)
    {
        $sql = " SELECT  Nom_Fichier, Date_Fichier, Numero_PO
            from GCOT_Gestion_Document
            where Numero_PO='{$numeroDossier}'
            and (Nom_Fichier like '%\PDV%' or Nom_Fichier like '%\LTA%' or Nom_Fichier like '%\HAWB%')
        ";

        return $this->retournerResultGcot04($sql);
    }
}
