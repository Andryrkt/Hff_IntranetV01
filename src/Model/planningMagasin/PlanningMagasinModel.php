<?php

namespace App\Model\planningMagasin;

use App\Entity\planningMagasin\PlanningMagasinSearch;
use App\Model\Model;
use App\Service\TableauEnStringService;

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


    public function recuperationCommadeplanifier(PlanningMagasinSearch $criteria, string $back, string $condition, array $tousLesBCSoumis, string $codeAgence)
    {
        if ($criteria->getOrBackOrder() == true) {
            $numCmd = "AND nent_numcde in (" . $back . ")";
        } else {
            $numCmd = $this->numcommande($criteria);
        }
        if ($criteria->getOrNonValiderDw() == true) {
            $value = TableauEnStringService::like($tousLesBCSoumis, 'nent_libcde');
            $numDevis = " AND  ($value) ";
        } else {
            $numDevis = "";
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
        $statement = "SELECT 
                        trim(nent_succ) as codeSuc,
                        trim(asuc_lib) as libSuc,
                        trim(nent_servcrt) as codeServ,
                        trim(ser.atab_lib) as libServ,
                        trim(nent_refcde) as commentaire,
                        nent_numcli as idMat,
                        trim(cbse_nomcli) as markMat,
                        '' as typeMat ,
                        '' as numSerie,
                        '' as numParc,
                        '' as casier,
                        year(nent_datexp) as annee,
                        month(nent_datexp) as mois,
                        nent_numcde as orIntv,
                        TRIM((select atab_lib from agr_tab where atab_code = nent_codope and atab_nom = 'OPE' )) as commercial,
                        CASE 
                            WHEN  ( SUM(nlig_qteliv) > 0 AND SUM(nlig_qteliv) != SUM(nlig_qtecde) AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv)) )
                            OR ( SUM(nlig_qtecde) != SUM(nlig_qtealiv) AND SUM(nlig_qteliv) = 0 AND SUM(nlig_qtealiv) > 0 )  THEN 
                            SUM(CASE WHEN nlig_constp NOT IN ('ZDI') THEN nlig_qtecde ELSE 0 END)
                            ELSE sum(nlig_qtecde) 
                            END QteCdm, 
                        CASE 
                            WHEN  ( SUM(nlig_qteliv) > 0 AND SUM(nlig_qteliv) != SUM(nlig_qtecde) AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv)) )
                            OR ( SUM(nlig_qtecde) != SUM(nlig_qtealiv) AND SUM(nlig_qteliv) = 0 AND SUM(nlig_qtealiv) > 0 )  THEN 
                            SUM(CASE WHEN nlig_constp NOT IN ('ZDI') THEN nlig_qteliv ELSE 0 END)
                            ELSE sum(nlig_qteliv) 
                        END qtliv,
                        CASE 
                            WHEN  ( SUM(nlig_qteliv) > 0 AND SUM(nlig_qteliv) != SUM(nlig_qtecde) AND SUM(nlig_qtecde) > (SUM(nlig_qteliv) + SUM(nlig_qtealiv)) )
                            OR ( SUM(nlig_qtecde) != SUM(nlig_qtealiv) AND SUM(nlig_qteliv) = 0 AND SUM(nlig_qtealiv) > 0 )  THEN 
                            SUM(CASE WHEN nlig_constp NOT IN ('ZDI') THEN nlig_qtealiv ELSE 0 END)
                            ELSE sum(nlig_qtealiv) 
                        END QteALL 

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
                        AND not nent_numcli between 1800000 and 1999999
                        AND trim(nent_succ) in ('01', '20', '30', '40')

                        $numDevis
                        $numCmd
                        $agDebit
                        $servDebit
                        $codeClient
                        $commercial
                        $refClient
                        group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15
                        order by 12 desc, 13 desc";
        // dump($statement);
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
                        AND nent_posf not in ('CP','FC')
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
