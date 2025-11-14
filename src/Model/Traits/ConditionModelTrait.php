<?php

namespace App\Model\Traits;

use App\Service\GlobalVariablesService;

trait ConditionModelTrait
{
    private function conditionLike(string $colonneBase, string $indexCriteria, $criteria)
    {
        if (!empty($criteria[$indexCriteria])) {
            // Échappe les quotes simples pour Informix en les doublant
            $valeur = str_replace("'", "''", (string)$criteria[$indexCriteria]);

            $condition = " AND {$colonneBase} LIKE '%{$valeur}%'";
        } else {
            $condition = "";
        }

        return $condition;
    }

    private function conditionEgal(string $colonneBase, string $indexCriteria, $criteria)
    {
        if (!empty($criteria[$indexCriteria])) {
            $condition = " AND {$colonneBase} = '" . (string)$criteria[$indexCriteria] . "'";
        } else {
            $condition = "";
        }

        return $condition;
    }

    private function conditionDateSigne(string $colonneBase, string $indexCriteria, array $criteria, string $signe)
    {
        if (!empty($criteria[$indexCriteria])) {
            // Vérifie si $criteria['dateDebut'] est un objet DateTime
            if ($criteria[$indexCriteria] instanceof \DateTime) {
                // Formate la date au format SQL (par exemple, 'Y-m-d')
                $formattedDate = $criteria[$indexCriteria]->format('Y-m-d');
            } else {
                // Si ce n'est pas un objet DateTime, le considérer comme une chaîne
                $formattedDate = $criteria[$indexCriteria];
            }

            $condition = " AND {$colonneBase} {$signe} TO_DATE('" . $formattedDate . "', '%Y-%m-%d') ";
        } else {
            $condition = "";
        }
        return $condition;
    }

    private function conditionSigne(string $colonneBase, string $indexCriteria, string $signe, array $criteria)
    {
        if (!empty($criteria[$indexCriteria])) {
            $condition = " AND {$colonneBase} {$signe} '" . $criteria[$indexCriteria] . "'";
        } else {
            $condition = "";
        }

        return $condition;
    }

    private function conditionIn(string $colonneBase, string $indexCriteria, $criteria)
    {
        if (!empty($criteria[$indexCriteria])) {
            $condition = " AND {$colonneBase} IN (" . $criteria[$indexCriteria] . ")";
        } else {
            $condition = "";
        }

        return $condition;
    }

    private function conditionPiece(string $indexCriteria, array $criteria, string $props): ?string
    {
        $piece = null;
        if (!empty($criteria[$indexCriteria])) {
            if ($criteria[$indexCriteria] === "PIECES MAGASIN") {
                $value = GlobalVariablesService::get('pieces_magasin');
                if (!empty($value)) {
                    $piece = " AND $props in ($value) AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
                }
            } elseif ($criteria[$indexCriteria] === "LUB") {
                $value = GlobalVariablesService::get('lub');
                if (!empty($value)) {
                    $piece = " AND $props in ($value)";
                }
            } elseif ($criteria[$indexCriteria] === "ACHATS LOCAUX") {
                $value = GlobalVariablesService::get('achat_locaux');
                if (!empty($value)) {
                    $piece = " AND $props in ($value) ";
                }
            } elseif ($criteria[$indexCriteria] === "TOUTS PIECES") {
                $piece = null;
            }
        } else {
            $value = GlobalVariablesService::get('pieces_magasin');
            if (!empty($value)) {
                $piece = " AND $props in ($value) AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
            }
        }

        return $piece;
    }

    private function conditionPieceLcfng(string $colonneBase, string $indexCriteria, array $criteria): ?string
    {
        if (!empty($criteria[$indexCriteria])) {
            if ($criteria[$indexCriteria] === "PIECES MAGASIN") {
                $piece = " AND {$colonneBase} in (" . GlobalVariablesService::get('pieces_magasin') . ") AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
            } elseif ($criteria[$indexCriteria] === "LUB") {
                $piece = " AND {$colonneBase} in (" . GlobalVariablesService::get('lub') . ")";
            } elseif ($criteria[$indexCriteria] === "ACHATS LOCAUX") {
                $piece = " AND {$colonneBase} in (" . GlobalVariablesService::get('achat_locaux') . ") ";
            } elseif ($criteria[$indexCriteria] === "TOUTS PIECES") {
                $piece = null;
            }
        } else {
            $piece = " AND {$colonneBase} in (" . GlobalVariablesService::get('pieces_magasin') . ") AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
        }

        return $piece;
    }

    private function conditionOrCompletOuNonCis(string $indexCriteria, array $criteria): string
    {
        if (!empty($criteria[$indexCriteria])) {
            if ($criteria[$indexCriteria] === 'ORs COMPLET') {
                $orCompletNom = " HAVING 
                            SUM(nlig_qtecde) = (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0 ";
            } elseif ($criteria[$indexCriteria] === 'ORs INCOMPLETS') {
                $orCompletNom = " HAVING 
                            SUM(nlig_qtecde) > (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0";
            } elseif ($criteria[$indexCriteria] === 'TOUTS LES OR') {
                $orCompletNom = " HAVING 
                            SUM(nlig_qtecde) >= (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0";
            }
        } else {
            $orCompletNom =  " HAVING 
                            SUM(nlig_qtecde) = (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0 ";
        }

        return $orCompletNom;
    }

    private function conditionOrCompletOuNonOrALivrer(string $indexCriteria, array $criteria): string
    {
        if (!empty($criteria[$indexCriteria])) {
            if ($criteria[$indexCriteria] === 'ORs COMPLET') {
                $orCompletNom = " AND T.situation = 'COMPLET' ";
            } elseif ($criteria[$indexCriteria] === 'ORs INCOMPLETS') {
                $orCompletNom = " AND T.situation = 'INCOMPLET' ";
            } else {
                $orCompletNom = "";
            }
        } else {
            $orCompletNom =  " AND T.situation = 'COMPLET'";
        }

        return $orCompletNom;
    }

    private function conditionAgenceUser(string $indexCriteria, array $criteria): string
    {
        if (!empty($criteria[$indexCriteria])) {
            $value = strpos($criteria[$indexCriteria], '-') !== false ? explode('-', $criteria[$indexCriteria])[0] : $criteria[$indexCriteria];
            $agenceUser = $value != "''" ? " AND slor_succ in ($value)" : "";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionAgenceService(string $colonneBase, string $indexCriteria, array $criteria): string
    {
        if (!empty($criteria[$indexCriteria])) {
            $agenceUser = " AND {$colonneBase} = '" . explode('-', $criteria[$indexCriteria])[0] . "'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionAgenceLcfng(string $colonneBase, string $indexCriteria, array $criteria): string
    {
        if (!empty($criteria[$indexCriteria])) {
            $agenceUser = " AND {$colonneBase} LIKE '%" . explode('-', $criteria[$indexCriteria])[0] . "-%'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionServiceLcfng(string $colonneBase, string $indexCriteria, array $criteria): string
    {

        if (!empty($criteria[$indexCriteria])) {
            $agenceUser = " AND {$colonneBase} LIKE '%-" . explode(' ', $criteria[$indexCriteria])[0] . "%'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }
    private function conditionAgenceLcfnp(string $colonneBase, string $indexCriteria, array $criteria): string
    {
        if (!empty($criteria[$indexCriteria])) {
            $agenceUser = " AND {$colonneBase} LIKE '%" . explode('-', $criteria[$indexCriteria])[0] . "-%'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionServiceLcfnp(string $colonneBase, string $indexCriteria, array $criteria): string
    {

        if (!empty($criteria[$indexCriteria])) {
            $agenceUser = " AND {$colonneBase} LIKE '%-" . explode(' ', $criteria[$indexCriteria])[0] . "%'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionOrValide($orValides, $numORItvValides)
    {
        if ($orValides) {
            $orValide = " AND slor_numor||'-'||TRUNC(slor_nogrp/100) IN ($numORItvValides)";
        } else {
            $orValide = '';
        }

        return $orValide;
    }
    //requette condition

    private function requette($criteria, $vinstant)
    {
        // dump($criteria, $criteria['commande']);
        switch ($criteria['commande']) {
            case 'TOUS':
                $vRequest = "SELECT * FROM (select 
                                fcde_numcde as n_commande,
                                fcde_date as date_cmd, 
                                fcde_numfou as  n_frs,
                                (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
                                fcde_ttc as mont_TTC, 
                                fcde_devise as Devis,
                                slor_numor as n_OR, 
                                case  
                                    when slor_natop = 'CES' then 'OR - Cession'
                                    else 'OR - Client'
                                end as commentaire,
                                case  
                                    when slor_natop = 'CES' then (select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb)||' - '||
                                (select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb)
                                    else (select seor_numcli||' - '||trim(seor_nomcli) from sav_eor where seor_soc = slor_soc and seor_numor = slor_numor)
                                end as client ,
                                'OR' as obs

                                from frn_cde, sav_lor
                                where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
                                and fcde_posl = '--'
                                and (slor_soc = fcde_soc and slor_numcf = fcde_numcde)
                                group by 1,2,3,4,5,6,7,8,9,10

                                union 

                                select 
                                fcde_numcde as n_commande ,
                                fcde_date as date_cmd, 
                                fcde_numfou as n_frs, 
                                (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
                                fcde_ttc as mont_TTC, 
                                fcde_devise as Devis,
                                nlig_numcde as n_OR, 
                                case  
                                    when nlig_natop = 'CIS' then 'CIS'||' vers '||(select trim(asuc_lib) from agr_succ where asuc_numsoc = nlig_soc and asuc_num = nlig_succd)
                                    else 'Vente Client'
                                end as commentaire,
                                case  
                                    when nlig_natop = 'CIS' then case when NOM_CLIENT is null then 'REAPPRO' else CLIENT||' - '||NOM_CLIENT end
                                    else (select nent_numcli||' - '||trim(nent_nomcli) from neg_ent where nent_soc = nlig_soc and nent_numcde = nlig_numcde)
                                end as client,
                                case  
                                    when nlig_natop = 'CIS' then case when TYPE is null then 'REAPPRO' else TYPE end
                                    else 'Vente'
                                end as obs

                                from frn_cde, neg_lig, outer hff_ctrmarq_agence_" . $vinstant . "
                                where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
                                and fcde_posl = '--'
                                and (nlig_soc = fcde_soc and nlig_numcf = fcde_numcde)
                                and (ctr_marque = nlig_numcde)
                                group by 1,2,3,4,5,6,7,8,9,10

                                union 

                                select
                                fcde_numcde as n_commande, 
                                fcde_date as date_cmd, 
                                fcde_numfou as n_frs,
                                (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
                                fcde_ttc as mont_TTC, 
                                fcde_devise  as Devis, 
                                0 as n_OR, 
                                'REAPPRO' as commentaire,
                                '' as client,
                                'REAPPRO' as obs

                                from frn_cde
                                where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
                                and fcde_posl = '--'
                                and not exists (select slor_numcf from sav_lor where slor_soc = fcde_soc and slor_numcf = fcde_numcde)
                                and not exists (select nlig_numcf from neg_lig where nlig_soc = fcde_soc and nlig_numcf = fcde_numcde)
                                group by 1,2,3,4,5,6,7,8,9,10
                                ) as  requete_base";
                break;
            case 'ATE':
                $vRequest = " SELECT * FROM ( SELECT  
                            fcde_numcde as n_commande,
                            fcde_date as date_cmd, 
                            fcde_numfou as  n_frs,
                            (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
                            fcde_ttc as mont_TTC, 
                            fcde_devise as Devis,
                            slor_numor as n_OR, 
                            case  
                                when slor_natop = 'CES' then 'OR - Cession'
                                else 'OR - Client'
                            end as commentaire,
                            case  
                                when slor_natop = 'CES' then (select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb)||' - '||
                            (select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb)
                                else (select seor_numcli||' - '||trim(seor_nomcli) from sav_eor where seor_soc = slor_soc and seor_numor = slor_numor)
                            end as client ,
                            'OR' as obs

                            from frn_cde, sav_lor
                            where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
                            and fcde_posl = '--'
                            and (slor_soc = fcde_soc and slor_numcf = fcde_numcde)
                            group by 1,2,3,4,5,6,7,8,9,10 ) as  requete_base ";
                break;
            case 'NEG':
                $vRequest = " SELECT * FROM ( SELECT  
                                fcde_numcde as n_commande ,
                                fcde_date as date_cmd, 
                                fcde_numfou as n_frs, 
                                (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
                                fcde_ttc as mont_TTC, 
                                fcde_devise as Devis,
                                nlig_numcde as n_OR, 
                                case  
                                    when nlig_natop = 'CIS' then 'CIS'||' vers '||(select trim(asuc_lib) from agr_succ where asuc_numsoc = nlig_soc and asuc_num = nlig_succd)
                                    else 'Vente Client'
                                end as commentaire,
                                case  
                                    when nlig_natop = 'CIS' then case when NOM_CLIENT is null then 'REAPPRO' else CLIENT||' - '||NOM_CLIENT end
                                    else (select nent_numcli||' - '||trim(nent_nomcli) from neg_ent where nent_soc = nlig_soc and nent_numcde = nlig_numcde)
                                end as client,
                                case  
                                    when nlig_natop = 'CIS' then case when TYPE is null then 'REAPPRO' else TYPE end
                                    else 'Vente'
                                end as obs

                                from frn_cde, neg_lig, outer hff_ctrmarq_agence_" . $vinstant . "
                                where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
                                and fcde_posl = '--'
                                and (nlig_soc = fcde_soc and nlig_numcf = fcde_numcde)
                                and (ctr_marque = nlig_numcde)
                                group by 1,2,3,4,5,6,7,8,9,10 ) as  requete_base";
                break;
            case 'REAPPRO':
                $vRequest = " SELECT * FROM (SELECT 
                            fcde_numcde as n_commande, 
                            fcde_date as date_cmd, 
                            fcde_numfou as n_frs,
                            (select fbse_nomfou from frn_bse where fcde_numfou = fbse_numfou) as nom_Frs,
                            fcde_ttc as mont_TTC, 
                            fcde_devise  as Devis, 
                            0 as n_OR, 
                            'REAPPRO' as commentaire,
                            '' as client,
                            'REAPPRO' as obs

                            from frn_cde
                            where fcde_soc = 'HF' and fcde_succ = '01' and fcde_serv = 'NEG'
                            and fcde_posl = '--'
                            and not exists (select slor_numcf from sav_lor where slor_soc = fcde_soc and slor_numcf = fcde_numcde)
                            and not exists (select nlig_numcf from neg_lig where nlig_soc = fcde_soc and nlig_numcf = fcde_numcde)
                            group by 1,2,3,4,5,6,7,8,9,10 ) as  requete_base";
                break;
        }
        // dump($vRequest);
        return $vRequest;
    }
}
