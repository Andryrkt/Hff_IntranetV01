<?php


namespace App\Model\magasin\devis;

use App\Model\Model;

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
}
