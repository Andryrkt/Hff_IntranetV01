<?php

namespace App\Model\da;

use App\Model\Model;

class DaSoumissionFacBlModel extends Model
{
    public function getMontantReceptionIpsEtNumFac(string $numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT  SUM(fllf_pxach) as montant_reception_ips, fliv_livext as numero_facture
                        FROM informix.frn_llf 
                        join informix.frn_liv on fliv_numliv = fllf_numliv 
                        WHERE fllf_numliv='$numeroLivraison' and fliv_soc='$codeSociete'
                        group by numero_facture
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }


    /**
     * Récupère les références / désignations des lignes de commande fournisseur
     * concernées par une livraison (frn_llf -> frn_cdl).
     */
    public function getRefDesiFrnCdl($numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT DISTINCT TRIM(fcdl_refp) as reference
                ,TRIM(fcdl_desi) as designation
                ,fllf_numliv as numero_livraison
            from informix.frn_llf
            inner join informix.frn_cdl
                on fcdl_numcde = fllf_numcde
                and fcdl_refp = fllf_refp
                and fcdl_constp = fllf_constp
                and fcdl_succ = fllf_succ
                and fcdl_soc = fllf_soc
                and fcdl_ligne = fllf_ligne
            where fllf_numliv = '$numeroLivraison'
            and fllf_soc = '$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    /**
     * Récupère les références / désignations des lignes d'OR (sav_lor) concernées par une livraison.
     * NB : slor_natcm indique la nature du numéro porté par slor_numcf :
     *  - 'C' : numéro de commande fournisseur (article pas encore réceptionné)
     *  - 'L' : numéro de livraison (IPS le remplace à la réception)
     */
    public function getRefDesiSavLor($numeroCde, $numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT slor_numcf as numero_livraison
                ,TRIM(slor_refp) as reference
                ,TRIM(slor_desi) as designation
                from Informix.sav_lor
                where slor_soc = '$codeSociete'
                and (
                    (slor_natcm = 'C' and slor_numcf = '$numeroCde')
                    or (slor_natcm = 'L' and slor_numcf = '$numeroLivraison')
                )
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    /**
     * Diagnostic d'une livraison quand aucun article livré n'a été retrouvé :
     * nombre de lignes IPS (livraison, commande, lignes d'OR réceptionnées sur la livraison) et références.
     */
    public function getDiagnosticLivraison(string $numeroLivraison, string $numeroCde, string $codeSociete): array
    {
        $statement = " SELECT
                (SELECT COUNT(*) FROM informix.frn_llf WHERE fllf_numliv = '$numeroLivraison') as nb_lignes_livraison,
                (SELECT COUNT(*) FROM informix.frn_llf WHERE fllf_numliv = '$numeroLivraison' AND fllf_soc = '$codeSociete') as nb_lignes_livraison_societe,
                (SELECT COUNT(*) FROM informix.frn_llf WHERE fllf_numliv = '$numeroLivraison' AND fllf_numcde = '$numeroCde') as nb_lignes_livraison_cde,
                (SELECT COUNT(*) FROM informix.frn_cdl WHERE fcdl_numcde = '$numeroCde' AND fcdl_soc = '$codeSociete') as nb_lignes_cde,
                (SELECT COUNT(*) FROM informix.sav_lor WHERE slor_numcf = '$numeroLivraison' AND slor_soc = '$codeSociete') as nb_lignes_or
            FROM systables WHERE tabid = 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data   = $this->convertirEnUtf8($this->connect->fetchResults($result))[0] ?? [];

        $statement = " SELECT DISTINCT TRIM(fllf_constp) || ' ' || TRIM(fllf_refp) as article
            FROM informix.frn_llf WHERE fllf_numliv = '$numeroLivraison'
        ";
        $result = $this->connect->executeQuery($statement);
        $data['articles_livres'] = array_column($this->convertirEnUtf8($this->connect->fetchResults($result)), 'article');

        $statement = " SELECT DISTINCT TRIM(slor_constp) || ' ' || TRIM(slor_refp) as article
            FROM informix.sav_lor WHERE slor_numcf = '$numeroLivraison' AND slor_soc = '$codeSociete'
        ";
        $result = $this->connect->executeQuery($statement);
        $data['articles_or'] = array_column($this->convertirEnUtf8($this->connect->fetchResults($result)), 'article');

        return $data;
    }

    /** ================ DDPL ==========================*/
    /**
     * Retourne les montants HT et TTC d'une commande.
     *
     * @param int $numCde
     *
     * @return array{
     *     montant_total_cde_ht: float,
     *     montant_total_cde_ttc: float
     * }
     */
    public function getMontantCde(int $numCde): array
    {
        $statement = "SELECT 
                    fcde_mtn as montant_total_cde_ht,
                    fcde_ttc as montant_total_cde_ttc
                from informix.frn_cde 
                where fcde_numcde ='$numCde'
            ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        if (empty($data)) {
            return [
                'montant_total_cde_ht'  => 0.00,
                'montant_total_cde_ttc' => 0.00,
            ];
        }

        return [
            'montant_total_cde_ht'  => (float) $data[0]['montant_total_cde_ht'],
            'montant_total_cde_ttc' => (float) $data[0]['montant_total_cde_ttc'],
        ];
    }

    public function getArticleCde(int $numCde)
    {
        $statement = " SELECT 
                TRIM(fcdl_constp) as constructeur 
                ,TRIM(fcdl_ref) as reference
                ,TRIM(fcdl_desi) as designation
                ,ROUND(fcdl_qte) as qte_cde
                ,ROUND(fcdl_solde) as qte_reliquat
                ,ROUND(fcdl_qteli) as  qte_receptionnee
            FROM informix.frn_cdl 
            where fcdl_numcde ='$numCde'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getPosl(int $numCde, string $codeSociete)
    {
        $statement = " SELECT TRIM(fcde_posl) as posl
            from informix.frn_cde 
            where fcde_numcde = $numCde
            AND fcde_soc = '$codeSociete'
            ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'posl')[0] ?? null;
    }

    public function getDevise(int $numCde, string $codeSociete)
    {
        $statement = " SELECT TRIM(fcde_devise) as devise
            from informix.frn_cde 
            where fcde_numcde = $numCde
            AND fcde_soc = '$codeSociete'
            ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'devise')[0] ?? null;
    }

    public function getMontantLivraison(string $numeroLivraison, string $codeSociete)
    {
        $statement = " SELECT SUM(fllf_qteliv * fllf_achnet) as montant_livraison 
                        from informix.frn_llf 
                        WHERE fllf_numliv='$numeroLivraison' and fllf_soc='$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'montant_livraison')[0] ?? 0.0;
    }

    public function getMontantFactureSoumiseAvecTva(string $numeroCommande, string $codeSociete)
    {
        $statement = " SELECT  round(SUM(case 
                        when fcdl_tva = 0 
                        then (fllf_achnet *  fllf_qtefac) 
                        else (fllf_achnet *  fllf_qtefac *1.2)
                        end )
                        , 2) as montant_facture_soumise_tva
               
               from informix.frn_llf
               inner join informix.frn_cdl 
               on fcdl_numcde = fllf_numcde 
               and fcdl_refp = fllf_refp 
               and fcdl_constp = fllf_constp
               and fcdl_succ = fllf_succ
               and fcdl_soc = fllf_soc
               and fcdl_ligne = fllf_ligne 
               where fllf_numcde = '$numeroCommande'
               and fllf_soc = '$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'montant_facture_soumise_tva')[0] ?? 0.0;
    }

    public function getMontantCommandeTtc(string $numeroCommande, string $codeSociete)
    {
        $statement = " SELECT  fcde_ttc as montant_cde_ttc 
                from Informix.frn_cde 
                where fcde_numcde ='$numeroCommande' 
                and fcde_soc ='$codeSociete'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'montant_cde_ttc')[0] ?? 0.0;
    }
}
