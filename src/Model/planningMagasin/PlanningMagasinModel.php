<?php

namespace App\Model\planningMagasin;

use App\Model\Model;
use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use App\Entity\planningMagasin\PlanningMagasinSearch;

class PlanningMagasinModel extends Model
{
    use planningMagasinModelTrait;
    public function recuperationAgenceIrium()
    {
        $statement = " SELECT  trim(asuc_num) as asuc_num ,
                               trim(asuc_lib) as asuc_lib
                      FROM agr_succ
                      WHERE asuc_codsoc = 'HF'
                      AND  (ASUC_NUM like '01' 
                      or ASUC_NUM like '20' 
                      or ASUC_NUM like '30'
                       or ASUC_NUM like '40'
                       or ASUC_NUM like '50'
                       )
                      order by 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return
            array_map(function ($item) {
                return [$item['asuc_num'] . '-' . $item['asuc_lib'] => $item['asuc_num']];
            }, $dataUtf8);
    }

    public function recuperationAgenceDebite()
    {
        $statement = "SELECT  trim(asuc_lib) as asuc_lib,
                            trim(asuc_num) as asuc_num
                    FROM  agr_succ , sav_itv 
                    WHERE asuc_num = sitv_succdeb 
                    AND asuc_codsoc = 'HF'
                    --AND asuc_lib <> 'ANTALAHA'
                    AND asuc_num in ('01', '20', '30', '40')
                    --group by 1,2
                    order by asuc_num";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);

        $result = []; // ex: "01 ANTANANARIVO" => "01"
        foreach ($dataUtf8 as $item) {
            $key = $item['asuc_num'] . ' ' . $item['asuc_lib'];
            $result[$key] = $item['asuc_num'];
        }

        return $result;
    }


    public function recuperationServiceDebite($agence)
    {

        if ($agence === null) {
            $codeAgence = "";
        } else {
            $codeAgence = " AND asuc_num = '" . $agence . "'";
        }

        $statement = " SELECT DISTINCT
                        trim(atab_code) as atab_code ,
                        trim(atab_lib) as atab_lib  
                        FROM agr_succ , agr_tab a 
                        WHERE a.atab_nom = 'SER' 
                        and a.atab_code not in (select b.atab_code from agr_tab b where substr(b.atab_nom,10,2) = asuc_num and b.atab_nom like 'SERBLOSUC%') 
                        AND a.atab_code in ('NEG','FLE','MAP')
                        $codeAgence
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return array_map(function ($item) {
            return [
                "value" => $item['atab_code'],
                "text"  => $item['atab_lib']
            ];
        }, $dataUtf8);
    }


    public function getNumeroDevisValideBcClient()
    {
        $statement = " SELECT DISTINCT bcsn.numero_devis from {$this->dbIrium}:informix.bc_client_soumis_neg bcsn where bcsn.statut_bc like 'Valid%'";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);

        return array_column($resultat, 'numero_devis');
    }

    public function recuperationCommadeplanifier(
        PlanningMagasinSearch $criteria,
        string $back,
        string $condition,
        string $codeAgence,
        string $codeSociete,
        array $numeroDevisValideBcClient
    ) {
        if ($criteria->getOrBackOrder() == true) {
            $numCmd = "AND nent_numcde in (" . $back . ")";
        } else {
            $numCmd = $this->numcommande($criteria);
        }


        switch ($condition) {
            case 'partiel_facture':
                $partFact = $this->bcPartielFacture();
                if (is_array($partFact)) {
                    $factString = TableauEnStringService::orEnString($partFact);
                } else {
                    $factString = '';
                }
                $numCmd = "AND nent_numcde in (" . $factString . ")";
                break;
            case 'partiel_dispo':
                $partDispo = $this->bcPartielDispo();
                if (is_array($partDispo)) {
                    $dispoString = TableauEnStringService::orEnString($partDispo);
                } else {
                    $dispoString = '';
                }
                $numCmd = "AND nent_numcde in (" . $dispoString . ")";
                break;
            case 'complet_non_facture':
                $partcompletnonfac = $this->bcCompletNonFacturer();
                if (is_array($partcompletnonfac)) {
                    $partcompleString = TableauEnStringService::orEnString($partcompletnonfac);
                } else {
                    $partcompleString = '';
                }
                $numCmd = "AND nent_numcde in (" . $partcompleString . ")";
                break;
            case 'back_order':
                $numCmd = "AND nent_numcde in (" . $back . ")";
                break;
            default:
                $numCmd = $this->numcommande($criteria);
                break;
        }
        $agDebit = $this->agenceDebite($criteria, $codeAgence);
        $servDebit = $this->serviceDebite($criteria);
        $codeClient  = $this->codeClient($criteria);
        $commercial = $this->commercial($criteria);
        $refClient = $this->refClient($criteria);
        $numeroDevis = $this->numeroDevis($criteria);
        $orNonValideDW = $this->orNonValiderDW($criteria, $numeroDevisValideBcClient);
        $piecesMagasin = GlobalVariablesService::get('pieces_magasin');
        $statement = "SELECT 
                        trim(nent_succ)                          AS codeSuc,
    trim(asuc_lib)                           AS libSuc,
    trim(nent_servcrt)                       AS codeServ,
    trim(ser.atab_lib)                       AS libServ,
    trim(nent_refcde)                        AS commentaire,
    nent_numcli                              AS idMat,
    trim(cbse_nomcli)                        AS markMat,
    ''                                       AS typeMat,
    ''                                       AS numSerie,
    ''                                       AS numParc,
    ''                                       AS casier,
    year(nent_datexp)                        AS annee,
    month(nent_datexp)                       AS mois,
    nent_numcde                              AS orIntv,
    TRIM((
        SELECT atab_lib 
        FROM {$this->dbIps}:informix.agr_tab 
        WHERE atab_code = nent_codope 
          AND atab_nom  = 'OPE'
    ))                                       AS commercial,

    -- QteCdm
    CASE 
        WHEN (
                SUM(nlig_qteliv) > 0 
                AND SUM(nlig_qteliv) != SUM(nlig_qtecde) 
                AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv))
             )
          OR (
                SUM(nlig_qtecde) != SUM(nlig_qtealiv) 
                AND SUM(nlig_qteliv) = 0 
                AND SUM(nlig_qtealiv) > 0
             )
        THEN SUM(CASE WHEN nlig_constp NOT IN ('ZDI','Nmc') THEN nlig_qtecde ELSE 0 END)
        ELSE SUM(nlig_qtecde) 
    END                                      AS QteCdm,

    -- QteLiv
    CASE 
        WHEN (
                SUM(nlig_qteliv) > 0 
                AND SUM(nlig_qteliv) != SUM(nlig_qtecde) 
                AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv))
             )
          OR (
                SUM(nlig_qtecde) != SUM(nlig_qtealiv) 
                AND SUM(nlig_qteliv) = 0 
                AND SUM(nlig_qtealiv) > 0
             )
        THEN SUM(CASE WHEN nlig_constp NOT IN ('ZDI','Nmc') THEN nlig_qteliv ELSE 0 END)
        ELSE SUM(nlig_qteliv) 
    END                                      AS qtliv,

    -- QteALL
    CASE 
        WHEN (
                SUM(nlig_qteliv) > 0 
                AND SUM(nlig_qteliv) != SUM(nlig_qtecde) 
                AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv))
             )
          OR (
                SUM(nlig_qtecde) != SUM(nlig_qtealiv) 
                AND SUM(nlig_qteliv) = 0 
                AND SUM(nlig_qtealiv) > 0
             )
        THEN SUM(CASE WHEN nlig_constp NOT IN ('ZDI','Nmc') THEN nlig_qtealiv ELSE 0 END)
        ELSE SUM(nlig_qtealiv) 
    END                                      AS QteALL

FROM 
    {$this->dbIps}:informix.neg_ent
    INNER JOIN {$this->dbIps}:informix.neg_lig    ON  nlig_soc    = nent_soc
                                              AND nlig_numcde = nent_numcde
   	INNER JOIN {$this->dbIps}:informix.agr_succ   ON  asuc_numsoc = nent_soc
                                              AND asuc_num    = nent_succ
 	INNER JOIN {$this->dbIps}:informix.agr_tab ser ON  nent_servcrt  = ser.atab_code
                                               AND ser.atab_nom  = 'SER'
  
    INNER JOIN {$this->dbIps}:informix.agr_usr ope ON  ope.ausr_num  = nent_usr  
    INNER JOIN {$this->dbIps}:informix.cli_bse     ON  cbse_numcli   = nent_numcli
    INNER JOIN {$this->dbIps}:informix.cli_soc     ON  csoc_soc      = nent_soc
                                               AND csoc_numcli   = cbse_numcli

WHERE 
    nent_soc     = '$codeSociete'
   AND nent_natop NOT IN ('DEV')
   AND nent_posf  NOT IN ('CP', 'FC')
    AND TO_CHAR(nent_numcli) NOT LIKE '150%'
   AND NOT nent_numcli BETWEEN 1800000 AND 1999999
                        AND trim(nent_succ) in ('01', '20', '30', '40')
                        AND trim(nent_servcrt) <> 'ASS'
                        --AND nlig_constp IN ($piecesMagasin)
                
                        $orNonValideDW
                        $numCmd
                        $agDebit
                        $servDebit
                        $codeClient
                        $commercial
                        $refClient
                        $numeroDevis
                        group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15
                        order by 12 desc, 13 desc";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }

    public function backOrderplanningMagasin(PlanningMagasinSearch $criteria)
    {
        //    if ($criteria->getOrNonValiderDw() == true) {
        //         $value = TableauEnStringService::like($tousLesBCSoumis, 'nent_libcde');
        //        $numCmd = " AND  ($value) ";
        //     }else {
        //         $numCmd = $this->numcommande($criteria);
        //     }
        $statement = "SELECT distinct 
                    nlig_numcde AS intervention
                  FROM neg_lig AS lig
                  INNER JOIN gcot_acknow_cat AS cat
                  ON CAST(lig.nlig_numcf  as varchar(50))= CAST(cat.numero_po as varchar(50))
                  AND (lig.nlig_nolign = cat.line_number OR  lig.nlig_noligncm = cat.line_number)
                  AND lig.nlig_refp = cat.parts_number
                  WHERE (  CAST(cat.libelle_type as varchar(10))= 'Error'  or CAST(cat.libelle_type as varchar(10))= 'Back Order'  ) 
                  AND cat.id_gcot_acknow_cat = (
                                              SELECT MAX(sub.id_gcot_acknow_cat )
                                              FROM gcot_acknow_cat AS sub
                                              WHERE sub.parts_number = cat.parts_number
                                                AND sub.numero_po = cat.numero_po
                                                AND sub.line_number = cat.line_number
                                          )
      ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function bcCompletNonFacturer()
    {
        $statement = "  SELECT    DISTINCT
                        nent_numcde as orIntv
                        from neg_ent, neg_lig, agr_succ, agr_tab ser, agr_usr ope, cli_bse, cli_soc
                        where nent_soc = 'HF'
                        and nlig_soc = nent_soc and nlig_numcde = nent_numcde
                        and asuc_numsoc = nent_soc and asuc_num = nent_succ
                        and csoc_soc = nent_soc and csoc_numcli = cbse_numcli and cbse_numcli = nent_numcli
                        AND (nent_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                        AND (nent_usr = ausr_num)
                        AND nent_natop not in ('DEV')
                        AND nent_posf not in ('CP')
                        AND to_char(nent_numcli) not like '150%'
                        AND nlig_constp not in ('ZDI','Nmc')
                        group by 1
                        HAVING
                            CASE
                                WHEN SUM(nlig_qteliv) > 0
                                    AND SUM(nlig_qteliv) != SUM(nlig_qtecde)
                                    AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv))
                                THEN 'PARTIELLEMENT FACTURE'

                                WHEN SUM(nlig_qtecde) != SUM(nlig_qtealiv)
                                    AND SUM(nlig_qteliv) = 0
                                    AND SUM(nlig_qtealiv) > 0
                                THEN 'PARTIELLEMENT DISPO'

                                WHEN (SUM(nlig_qtecde) = SUM(nlig_qtealiv)
                                    AND SUM(nlig_qteliv) < SUM(nlig_qtecde))
                                    OR (SUM(nlig_qtealiv) > 0
                                        AND SUM(nlig_qtecde) = (SUM(nlig_qtealiv) + SUM(nlig_qteliv)))
                                THEN 'COMPLET NON FACTURE'
                            END = 'COMPLET NON FACTURE' ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function bcPartielDispo()
    {
        $statement = " SELECT    DISTINCT
                        nent_numcde as orIntv
                        from neg_ent, neg_lig, agr_succ, agr_tab ser, agr_usr ope, cli_bse, cli_soc
                        where nent_soc = 'HF'
                        and nlig_soc = nent_soc and nlig_numcde = nent_numcde
                        and asuc_numsoc = nent_soc and asuc_num = nent_succ
                        and csoc_soc = nent_soc and csoc_numcli = cbse_numcli and cbse_numcli = nent_numcli
                        AND (nent_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                        AND (nent_usr = ausr_num)
                        AND nent_natop not in ('DEV')
                        AND nent_posf not in ('CP')
                        AND to_char(nent_numcli) not like '150%'
                        AND nlig_constp not in ('ZDI','Nmc')
                        group by 1
                        HAVING
                            CASE
                                WHEN SUM(nlig_qteliv) > 0
                                    AND SUM(nlig_qteliv) != SUM(nlig_qtecde)
                                    AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv))
                                THEN 'PARTIELLEMENT FACTURE'

                                WHEN SUM(nlig_qtecde) != SUM(nlig_qtealiv)
                                    AND SUM(nlig_qteliv) = 0
                                    AND SUM(nlig_qtealiv) > 0
                                THEN 'PARTIELLEMENT DISPO'

                                WHEN (SUM(nlig_qtecde) = SUM(nlig_qtealiv)
                                    AND SUM(nlig_qteliv) < SUM(nlig_qtecde))
                                    OR (SUM(nlig_qtealiv) > 0
                                        AND SUM(nlig_qtecde) = (SUM(nlig_qtealiv) + SUM(nlig_qteliv)))
                                THEN 'COMPLET NON FACTURE'
                            END = 'PARTIELLEMENT DISPO' 
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function bcPartielFacture()
    {
        $statement = " SELECT    DISTINCT
                        nent_numcde as orIntv
                        from neg_ent, neg_lig, agr_succ, agr_tab ser, agr_usr ope, cli_bse, cli_soc
                        where nent_soc = 'HF'
                        and nlig_soc = nent_soc and nlig_numcde = nent_numcde
                        and asuc_numsoc = nent_soc and asuc_num = nent_succ
                        and csoc_soc = nent_soc and csoc_numcli = cbse_numcli and cbse_numcli = nent_numcli
                        AND (nent_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                        AND (nent_usr = ausr_num)
                        AND nent_natop not in ('DEV')
                        AND nent_posf not in ('CP')
                        AND to_char(nent_numcli) not like '150%'
                        AND nlig_constp not in ('ZDI','Nmc')
                        group by 1
                        HAVING
                            CASE
                                WHEN SUM(nlig_qteliv) > 0
                                    AND SUM(nlig_qteliv) != SUM(nlig_qtecde)
                                    AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv))
                                THEN 'PARTIELLEMENT FACTURE'

                                WHEN SUM(nlig_qtecde) != SUM(nlig_qtealiv)
                                    AND SUM(nlig_qteliv) = 0
                                    AND SUM(nlig_qtealiv) > 0
                                THEN 'PARTIELLEMENT DISPO'

                                WHEN (SUM(nlig_qtecde) = SUM(nlig_qtealiv)
                                    AND SUM(nlig_qteliv) < SUM(nlig_qtecde))
                                    OR (SUM(nlig_qtealiv) > 0
                                        AND SUM(nlig_qtecde) = (SUM(nlig_qtealiv) + SUM(nlig_qteliv)))
                                THEN 'COMPLET NON FACTURE'
                            END = 'PARTIELLEMENT FACTURE' 
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }

    public function recupCommercial(string $codeAgence)
    {
        $statement = " SELECT  TRIM(atab_lib) as nom, 
        TRIM(nent_codope) as value
        from agr_tab, neg_ent
            where nent_soc = 'HF'
            and nent_servcrt in ('NEG','FLE','MAP')
            and atab_nom = 'OPE' and atab_code = nent_codope
                        
        ";
        if ($codeAgence != "-0") {
            $statement .= " AND trim(nent_succ) = $codeAgence";
        }

        $statement .= " group by 1, 2 order by 1";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}
