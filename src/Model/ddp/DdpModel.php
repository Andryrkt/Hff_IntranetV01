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
}
