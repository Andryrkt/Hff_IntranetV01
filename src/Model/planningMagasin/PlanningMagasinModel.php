<?php

namespace App\Model\planningMagasin;

use App\Model\Model;

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
                    AND asuc_lib <> 'ANTALAHA'
                    AND asuc_num <> '10'
                    group by 1,2
                    order by 1";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return array_combine(
            array_column($dataUtf8, 'asuc_lib'),
            array_map(function ($item) {
                return $item['asuc_num'];
            }, $dataUtf8)
        );
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

    public function recuperationCommadeplanifier($crieria)
    {
        $numCmd = $this->numcommande($crieria);
        $agDebit = $this->agenceDebite($crieria);
        $servDebit = $this->serviceDebite($crieria);

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
                        sum(nlig_qtecde) as QteCdm,
                        sum(nlig_qteliv) as qtliv,
                        sum(nlig_qtealiv) as QteALL

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

                        $numCmd
                        $agDebit
                        $servDebit

                        group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14
                        order by 12 desc, 13 desc";
                    // dump($statement);    
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}
