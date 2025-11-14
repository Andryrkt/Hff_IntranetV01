<?php

namespace App\Model\magasin\cis;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;
use App\Model\Traits\ConditionModelTrait;

class CisATraiterModel extends Model
{
    use ConversionModel;
    use ConditionModelTrait;

    public function listOrATraiter(array $criteria = [], string $numORItvValides): array
    {
        //condition de recherche
        $orValide = $this->conditionOrValide($criteria['orValide'], $numORItvValides);
        $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'referencePiece', $criteria);
        $constructeur = $this->conditionLike('slor_constp', 'constructeur', $criteria);
        $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
        $numCis = $this->conditionSigne('slor_numcf', 'numCis', '=', $criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $dateDebutCis = $this->conditionDateSigne('nlig_datecde', 'dateDebutCis', $criteria, '>=');
        $dateFinCis = $this->conditionDateSigne('nlig_datecde', 'dateFinCis', $criteria, '<=');
        $dateDebutOr = $this->conditionDateSigne('seor_dateor', 'dateDebutOr', $criteria, '>=');
        $dateFinOr = $this->conditionDateSigne('seor_dateor', 'dateFinOr', $criteria, '<=');
        $piece = $this->conditionPiece('pieces', $criteria, 'slor_constp');
        $agence = $this->conditionAgenceService("(CASE slor_natop 
                        WHEN 'CES' THEN TRIM(slor_succdeb)
                        WHEN 'VTE' THEN TRIM(TO_CHAR(slor_numcli))
                    END)", 'agence', $criteria);

        $service = $this->conditionAgenceService("(CASE slor_natop 
                        WHEN 'CES' THEN TRIM(slor_servdeb)
                        WHEN 'VTE' THEN 
                            (SELECT cbse_nomcli 
                            FROM cli_bse, cli_soc 
                            WHERE csoc_soc = slor_soc 
                            AND cbse_numcli = slor_numcli 
                            AND cbse_numcli = csoc_numcli)
                    END)", 'service', $criteria);
        $agenceUser = $this->conditionAgenceUser('agenceUser', $criteria);

        //requete
        $statement = "SELECT
                    seor_refdem AS NumDit,
                    slor_numcf AS NumCis, 
                    nlig_datecde AS DateCis, 
                    -- Agence service créditeur
                    TRIM(slor_succ) || ' - ' || TRIM(slor_servcrt) AS agenceServiceTravaux,
                    slor_numor AS NumOr, 
                    seor_dateor AS DateOr, 
                    -- Agence service débiteur
                    trim(CASE 
                        WHEN slor_natop = 'CES' THEN TRIM(slor_succdeb) || ' - ' || TRIM(slor_servdeb)
                        WHEN slor_natop = 'VTE' THEN TRIM(TO_CHAR(slor_numcli)) || ' - ' || 
                            (SELECT cbse_nomcli 
                            FROM cli_bse, cli_soc 
                            WHERE csoc_soc = slor_soc 
                            AND cbse_numcli = slor_numcli 
                            AND cbse_numcli = csoc_numcli)
                    END) AS agenceServiceDebiteur,
                    TRUNC(slor_nogrp / 100) AS NItv, 
                    slor_nolign AS NumLigne, 
                    trim(slor_constp) AS Cst, 
                    trim(slor_refp) AS Ref, 
                    trim(slor_desi) AS Designations,
                    -- Quantité demandée
                    TRUNC(CASE 
                        WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                    END) AS Qte_dem,
                    TRUNC(nlig_qtecde - nlig_qtealiv) as Qte_A_TRAITER
                    , mmat_nummat as idMateriel
                    , trim(mmat_numserie) as num_serie
                    , trim(mmat_recalph) as num_parc 
                    , trim(mmat_marqmat) as marque
                    , trim(mmat_numparc) as casie


                FROM 
                    sav_lor  
                INNER JOIN 
                    neg_lig ON nlig_soc = slor_soc 
                    AND nlig_numcde = slor_numcf 
                    AND nlig_nolign = slor_noligncm AND slor_pos = 'EC'
                INNER JOIN 
                    sav_eor ON seor_soc = slor_soc 
                    AND seor_succ = slor_succ 
                    AND seor_numor = slor_numor
                INNER JOIN 
                    mat_mat on mmat_nummat =  seor_nummat
                    
                WHERE
                    slor_soc = 'HF' 
                    AND slor_numcf > 0 -- Ne filtre que les lignes d'OR contremarquées 
                    AND (
                        NVL(nlig_numcf, 0) = 0 -- La CIS n'est pas contremarquée
                        --AND NVL(nlig_qtealiv, 0) = 0 -- Pas encore de quantité à livrer
                        AND nlig_qtealiv < nlig_qtecde -- il ya une qte reliquat qui n'est pas encore commender au fournisseur
                        AND NVL(nlig_qteliv, 0) = 0 -- Pas encore de quantité livrée
                    )
                    AND nlig_natop = 'CIS'
                    --AND slor_constp NOT IN ('LUB', 'SHE', 'JOV')
                    $agenceUser
                    $piece
                    $designation
                    $referencePiece 
                    $constructeur 
                    $dateDebutCis
                    $dateFinCis
                    $dateDebutOr
                    $dateFinOr
                    $numOr
                    $numDit
                    $numCis
                    $agence
                    $service
                -- Ajouter d'autres conditions si nécessaire pour les pièces magasin et les achats locaux
                $orValide
                ORDER BY
                    seor_refdem, 
                    slor_datel, -- Date planning
                    slor_numor";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    // public function getListeCisATraiter(array $criteria = [], string $numORItvValides): array
    // {
    //     //condition de recherche
    //     $orValide = $this->conditionOrValide($criteria['orValide'], $numORItvValides);
    //     $designation = $this->conditionLike('slor_desi', 'designation', $criteria);
    //     $referencePiece = $this->conditionLike('slor_refp', 'referencePiece', $criteria);
    //     $constructeur = $this->conditionLike('slor_constp', 'constructeur', $criteria);
    //     $numDit = $this->conditionLike('seor_refdem', 'numDit', $criteria);
    //     $numCis = $this->conditionSigne('slor_numcf', 'numCis', '=', $criteria);
    //     $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
    //     $dateDebutCis = $this->conditionDateSigne('nlig_datecde', 'dateDebutCis', $criteria, '>=');
    //     $dateFinCis = $this->conditionDateSigne('nlig_datecde', 'dateFinCis', $criteria, '<=');
    //     $dateDebutOr = $this->conditionDateSigne('seor_dateor', 'dateDebutOr', $criteria, '>=');
    //     $dateFinOr = $this->conditionDateSigne('seor_dateor', 'dateFinOr', $criteria, '<=');
    //     $value = GlobalVariablesService::get('pneumatique');
    //     if (!empty($value)) {
    //         $piece = " AND slor_constp in ($value) AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
    //     } else {
    //         $piece = "";
    //     };
    //     $agence = $this->conditionAgenceService("(CASE slor_natop 
    //                     WHEN 'CES' THEN TRIM(slor_succdeb)
    //                     WHEN 'VTE' THEN TRIM(TO_CHAR(slor_numcli))
    //                 END)", 'agence', $criteria);

    //     $service = $this->conditionAgenceService("(CASE slor_natop 
    //                     WHEN 'CES' THEN TRIM(slor_servdeb)
    //                     WHEN 'VTE' THEN 
    //                         (SELECT cbse_nomcli 
    //                         FROM cli_bse, cli_soc 
    //                         WHERE csoc_soc = slor_soc 
    //                         AND cbse_numcli = slor_numcli 
    //                         AND cbse_numcli = csoc_numcli)
    //                 END)", 'service', $criteria);
    //     $agenceUser = $this->conditionAgenceUser('agenceUser', $criteria);

    //     //requete
    //     $statement = "SELECT
    //                 seor_refdem AS NumDit,
    //                 slor_numcf AS NumCis, 
    //                 nlig_datecde AS DateCis, 
    //                 -- Agence service créditeur
    //                 TRIM(slor_succ) || ' - ' || TRIM(slor_servcrt) AS agenceServiceTravaux,
    //                 slor_numor AS NumOr, 
    //                 seor_dateor AS DateOr, 
    //                 -- Agence service débiteur
    //                 trim(CASE 
    //                     WHEN slor_natop = 'CES' THEN TRIM(slor_succdeb) || ' - ' || TRIM(slor_servdeb)
    //                     WHEN slor_natop = 'VTE' THEN TRIM(TO_CHAR(slor_numcli)) || ' - ' || 
    //                         (SELECT cbse_nomcli 
    //                         FROM cli_bse, cli_soc 
    //                         WHERE csoc_soc = slor_soc 
    //                         AND cbse_numcli = slor_numcli 
    //                         AND cbse_numcli = csoc_numcli)
    //                 END) AS agenceServiceDebiteur,
    //                 TRUNC(slor_nogrp / 100) AS NItv, 
    //                 slor_nolign AS NumLigne, 
    //                 trim(slor_constp) AS Cst, 
    //                 trim(slor_refp) AS Ref, 
    //                 trim(slor_desi) AS Designations,
    //                 -- Quantité demandée
    //                 TRUNC(CASE 
    //                     WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
    //                     WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
    //                 END) AS Qte_dem,
    //                 TRUNC(nlig_qtecde - nlig_qtealiv) as Qte_A_TRAITER
    //                 , mmat_nummat as idMateriel
    //                 , trim(mmat_numserie) as num_serie
    //                 , trim(mmat_recalph) as num_parc 
    //                 , trim(mmat_marqmat) as marque
    //                 , trim(mmat_numparc) as casie


    //             FROM 
    //                 sav_lor  
    //             INNER JOIN 
    //                 neg_lig ON nlig_soc = slor_soc 
    //                 AND nlig_numcde = slor_numcf 
    //                 AND nlig_nolign = slor_noligncm AND slor_pos = 'EC'
    //             INNER JOIN 
    //                 sav_eor ON seor_soc = slor_soc 
    //                 AND seor_succ = slor_succ 
    //                 AND seor_numor = slor_numor
    //             INNER JOIN 
    //                 mat_mat on mmat_nummat =  seor_nummat

    //             WHERE
    //                 slor_soc = 'HF' 
    //                 AND slor_numcf > 0 -- Ne filtre que les lignes d'OR contremarquées 
    //                 AND (
    //                     NVL(nlig_numcf, 0) = 0 -- La CIS n'est pas contremarquée
    //                     --AND NVL(nlig_qtealiv, 0) = 0 -- Pas encore de quantité à livrer
    //                     AND nlig_qtealiv < nlig_qtecde -- il ya une qte reliquat qui n'est pas encore commender au fournisseur
    //                     AND NVL(nlig_qteliv, 0) = 0 -- Pas encore de quantité livrée
    //                 )
    //                 AND nlig_natop = 'CIS'
    //                 --AND slor_constp NOT IN ('LUB', 'SHE', 'JOV')
    //                 $agenceUser
    //                 $piece
    //                 $designation
    //                 $referencePiece 
    //                 $constructeur 
    //                 $dateDebutCis
    //                 $dateFinCis
    //                 $dateDebutOr
    //                 $dateFinOr
    //                 $numOr
    //                 $numDit
    //                 $numCis
    //                 $agence
    //                 $service
    //             -- Ajouter d'autres conditions si nécessaire pour les pièces magasin et les achats locaux
    //             $orValide
    //             ORDER BY
    //                 seor_refdem, 
    //                 slor_datel, -- Date planning
    //                 slor_numor";

    //     $result = $this->connect->executeQuery($statement);

    //     $data = $this->connect->fetchResults($result);

    //     return $this->convertirEnUtf8($data);
    // }
}
