<?php

namespace App\Model\magasin\devis;

use App\Model\Model;
use App\Service\GlobalVariablesService;

class DevisNegModel extends Model
{
    public function getDevisNeg($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure)
    {
        $this->connect->connect();

        try {

            $statement = " SELECT
                    distinct
                    nent.nent_datecde as date_cde_brute
                    ,dneg.statut_dw as statut_dw
                    ,dneg.statut_bc as statut_bc
                    ,nent.nent_numcde as numero_devis
                    ,TO_CHAR(nent.nent_datecde, '%d/%m/%Y') as date_creation
                    ,nent.nent_succ || ' - ' || nent_servcrt as emetteur
                    ,nent.nent_numcli || ' - ' || nent_nomcli as client
                    ,TRIM(nent.nent_refcde) as reference_client
                    ,nent.nent_cdeht as montant_devis
                    ,CASE 
                        WHEN dneg.date_envoye_devis_client IS NOT NULL 
                        THEN TO_CHAR(dneg.date_envoye_devis_client, '%d/%m/%Y')
                        ELSE NULL
                    END as date_envoye_devis_au_client
                    --relance1
                    --relance2
                    --relance3
                    --stop relance
                    ,nent.nent_posl as position_ips
                    --PO/BC client
                    ,TRIM(ausr.ausr_nom)as utilisateur_createur_devis
                    ,dneg.utilisateur as soumis_par
                    ,nent.nent_devise as devise
                    ,nlig.nlig_constp as constructeur
                    ,SUM(nlig.nlig_qtecde *nlig.nlig_pxnreel) as montant_total
                    ,SUM(nlig.nlig_nolign) as somme_numero_lignes
                from ips_hffprod:informix.neg_ent nent
                left JOIN ips_hffprod:informix.neg_lig nlig on nlig.nlig_numcde = nent.nent_numcde
                left join ips_hffprod:informix.agr_usr ausr on ausr.ausr_num = nent.nent_usr and ausr.ausr_soc = nent.nent_soc
                left join ir_prod108:Informix.devis_soumis_a_validation_neg dneg on dneg.numero_devis = nent.nent_numcde
                    WHERE nent.nent_natop = 'DEV'
                    AND nent.nent_soc = 'HF'
                    AND nent.nent_servcrt <> 'ASS'
                    AND (CAST(nent.nent_numcli AS VARCHAR(20)) NOT LIKE '199%' and nent.nent_numcli not in ('1990000'))
                    AND nent_numcde not in ($numDeviAExclure)            
                    AND nent.nent_numcde not in ('19407989','19407991','19408971','19410383','19409906','19409996')
                    AND nent.nent_datecde >= MDY(9, 1, 2025)
                    and nlig_constp <> 'Nmc'
            ";

            if (array_key_exists('statutIps', $criteria) && ($criteria['statutIps'] == 'RE' || $criteria['statutIps'] == 'TR')) {
                $statement .= " AND nent.nent_posl in ('--','AC','DE', 'RE', 'TR')";
            } else {
                $statement .= " AND nent.nent_posl in ('--','AC','DE', 'TR')";
            }

            if ($vignette === 'pneumatique' && !$adminMutli) {
                // entrer par le vignette POL - agence pneumatique
                $piecesPneumatique = GlobalVariablesService::get('pneumatique');
                $statement .= " AND nlig.nlig_constp IN ($piecesPneumatique) AND nent_succ in ($codeAgenceAutoriserString) ";
            } else {
                // entrer par le vignette MAGASIN - agence tana et autres agence
                $piecesMagasin = GlobalVariablesService::get('pieces_magasin');
                $statement .= " AND nlig.nlig_constp IN ($piecesMagasin) AND nent.nent_succ <> '60' ";
            }

            $statement .= " GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15 ";
            $statement .= " ORDER BY date_cde_brute DESC";

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }

    public function getNumeroDevisExclure()
    {
        $sql = " SELECT distinct Numero_Devis_ERP as numDevis
                from GCOT_Devis
                ";

        $statement = $this->connexion04Gcot->query($sql);
        $data = [];
        while ($List = odbc_fetch_array($statement)) {
            $data[] = $List;
        }

        return array_column($data, 'numDevis');
    }
}
