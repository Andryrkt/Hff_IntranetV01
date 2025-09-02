<?php


namespace App\Model\magasin\devis;

use App\Model\Model;
use App\Service\GlobalVariablesService;

class ListeDevisMagasinModel extends Model
{
    public function getDevis()
    {
        $statement = "SELECT
            -- '' as statut_dw
            nent_numcde as numero_devis
            ,nent_datecde as date_creation
            ,nent_succ || nent_servcrt as emmeteur
            ,nent_numcli || ' - ' || nent_nomcli as client
            ,TRIM(nent_refcde) as reference_client
            ,nent_cdeht as montant
            -- ,'' as Operateur
            -- ,'' as DateEnvoiDevisAuClient
            ,nent_posl as statut_ips
            ,nent_devise as devise

            FROM neg_ent
            WHERE nent_natop = 'DEV'
            AND nent_posL in ('--','AC','DE')
            AND year(Nent_datecde) = '2025'
            order by nent_datecde desc
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getInfoDev(string $numeroDevis)
    {
        $statement = "SELECT nent_devise as devise
                        ,SUM(nlig_qtecde *nlig_pxnreel) as montant_total
                        ,SUM(nlig_nolign) as somme_numero_lignes 
                    from informix.neg_lig 
                    left JOIN informix.neg_ent on nent_numcde = nlig_numcde 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV' 
                    and nlig_constp <> 'Nmc'
                    and nlig_numcde = '$numeroDevis'
                    group by nent_devise
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function constructeurPieceMagasin(string $numeroDevis)
    {
        $statement = "SELECT CASE
                        WHEN COUNT(CASE WHEN nlig_constp = 'CAT' THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp  IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) > 0
                        THEN TRIM('CP')
                    
                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) > 0
                        AND COUNT(CASE WHEN nlig_constp  IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) = 0
                        THEN TRIM('C')

                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp  IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) = 0
                        THEN TRIM('N')

                        WHEN COUNT(CASE WHEN nlig_constp  = 'CAT' THEN 1 END) = 0
                        AND COUNT(CASE WHEN nlig_constp IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) > 0
                        THEN TRIM('P')
                    END AS retour
                    from informix.neg_lig 
                    where nlig_soc='HF' 
                    and nlig_natop='DEV'
                    and nlig_constp <> 'Nmc' 
                    and nlig_numcde = '$numeroDevis'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'retour')[0];
    }

    public function getCodeLibelleClient()
    {
        $statement = "SELECT nent_numcli as code_client, nent_nomcli as nom_client
                        from neg_ent
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}
