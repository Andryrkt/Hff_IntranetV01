<?php

namespace App\Model\magasin;

use App\Controller\Traits\FormatageTrait;
use App\Model\Model;
use App\Model\Traits\ConditionModelTrait;
use App\Model\Traits\ConversionModel;


class MagasinListeOrLivrerModel extends Model
{ 
    use ConversionModel;
    use FormatageTrait;
    use ConditionModelTrait;

    public function recupUserCreateNumOr($numOr)
    {
        $statement = " SELECT 
                        seor_usr as idUser, 
                        trim(ausr_nom) as nomUtilisateur,
                        trim(atab_lib) as nomPrenom
                        from sav_eor, agr_usr, agr_tab
                        where seor_usr = ausr_num
                        and ausr_ope = atab_code 
                        and atab_nom = 'OPE'
                        and seor_numor='".$numOr."'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanning1($numOr)
    {
        $statement = " SELECT  
                            min(ska_d_start) as datePlanning1
                        from skw 
                        inner join ska on ska.skw_id = skw.skw_id 
                        where ofh_id ='".$numOr."'
                        group by ofh_id 
                    ";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDatePlanning2($numOr)
    {
        $statement = " SELECT
                            min(sitv_datepla) as datePlanning2 

                        from sav_itv 
                        where sitv_numor = '".$numOr."'
                        group by sitv_numor
                    ";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerComplet($numOrValideItv, $numOrValide)
    {
        $statement = " SELECT slor_numor||'-'||TRUNC(slor_nogrp/100)
                        FROM sav_lor
                        inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                        WHERE
                            slor_typlig = 'P'
                        and slor_soc = 'HF'
                            AND slor_constp NOT LIKE 'Z%'
                            AND slor_constp NOT IN ('LUB')
                            AND slor_succ = '01'
                            AND seor_serv ='SAV'
                        and slor_numor in ('".$numOrValide."')
                        and seor_numor||'-'||TRUNC(slor_nogrp/100) in ('".$numOrValideItv."')
                        GROUP BY 1
                        HAVING 
                            sum(CASE 
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                            END) = sum(slor_qteres) + sum(slor_qterea)
                            and sum(slor_qteres) > 0
                        order by slor_numor||'-'||TRUNC(slor_nogrp/100) asc
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerIncomplet($numOrValideItv, $numOrValide)
    {
        $statement = " SELECT slor_numor||'-'||TRUNC(slor_nogrp/100)
                        FROM sav_lor
                        inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                        WHERE
                            slor_typlig = 'P'
                        and slor_soc = 'HF'
                            AND slor_constp NOT LIKE 'Z%'
                            AND slor_constp NOT IN ('LUB')
                            AND slor_succ = '01'
                            AND seor_serv ='SAV'
                        and slor_numor in ('".$numOrValide."')
                        and seor_numor||'-'||TRUNC(slor_nogrp/100) in ('".$numOrValideItv."')
                        GROUP BY 1
                        HAVING 
                            sum(CASE 
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                            END) > sum(slor_qteres) + sum(slor_qterea)
                            and sum(slor_qteres) > 0
                        order by slor_numor||'-'||TRUNC(slor_nogrp/100) asc
                    ";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerTout($numOrValideItv, $numOrValide)
    {
        $statement = " SELECT slor_numor||'-'||TRUNC(slor_nogrp/100)
                        FROM sav_lor
                        inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                        WHERE
                            slor_typlig = 'P'
                        and slor_soc = 'HF'
                            AND slor_constp NOT LIKE 'Z%'
                            AND slor_constp NOT IN ('LUB')
                            AND slor_succ = '01'
                            AND seor_serv ='SAV'
                        and slor_numor in ('".$numOrValide."')
                        and seor_numor||'-'||TRUNC(slor_nogrp/100) in ('".$numOrValideItv."')
                        GROUP BY 1
                        HAVING 
                            sum(CASE 
                                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                            END) >= sum(slor_qteres) + sum(slor_qterea)
                            and sum(slor_qteres) > 0
                        order by slor_numor||'-'||TRUNC(slor_nogrp/100) asc
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupereListeMaterielValider( array $criteria = [], array $lesOrSelonCondition)
    {
        //les condeition de filtre
        $designation = $this->conditionLike('slor_desi', 'designation',$criteria);
        $referencePiece = $this->conditionLike('slor_refp', 'referencePiece',$criteria);
        $constructeur = $this->conditionLike('slor_constp', 'constructeur',$criteria);
        $dateDebut = $this->conditionDateSigne( 'slor_datec', 'dateDebut', $criteria, '>=');
        $dateFin = $this->conditionDateSigne( 'slor_datec', 'dateFin', $criteria, '<=');
        $numDit = $this->conditionLike('seor_refdem', 'numDit',$criteria);
        $numOr = $this->conditionSigne('slor_numor', 'numOr', '=', $criteria);
        $piece = $this->conditionPiece('pieces', $criteria);
        $agence = $this->conditionAgenceService("slor_succdeb", 'agence',$criteria);
        $service = $this->conditionAgenceService("slor_servdeb", 'service',$criteria);
        $agenceUser = $this->conditionAgenceUser('agenceUser', $criteria);
        $orCompletNom = $this->conditionOrCompletOuNonOrALivrer('orCompletNon',$lesOrSelonCondition, $criteria);

        //requête
        $statement = " SELECT 
                        trim(seor_refdem) as referenceDIT,
                        seor_numor as numeroOr,
                        trim(slor_constp) as constructeur, 
                        trim(slor_refp) as referencePiece, 
                        trim(slor_desi) as designationi, 
                        CASE 
                            WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                            WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea 
                        END  AS quantiteDemander,
                        slor_qteres  as qteALivrer,
                        slor_qterea as quantiteLivree,
                        slor_qterel as quantiteReliquat,
                        slor_datec as dateCreation,
                        slor_nogrp/100 as numInterv,
                        slor_nolign as numeroLigne,
                        --slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence,
                        --slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as service,
                        slor_succdeb as agenceDebiteur,
                        slor_servdeb as serviceDebiteur,
                        slor_succ as agenceCrediteur,
                        slor_servcrt as serviceCrediteur,
                        (SELECT SUM(CASE 
                                        WHEN B.slor_typlig = 'P' THEN (B.slor_qterel + B.slor_qterea + B.slor_qteres + B.slor_qtewait - B.slor_qrec) 
                                        WHEN B.slor_typlig IN ('F','M','U','C') THEN B.slor_qterea 
                                    END) 
                        FROM sav_lor B 
                        WHERE B.slor_numor = A.slor_numor) AS sommeQuantiteDemander,
                        (SELECT SUM(B.slor_qteres) 
                        FROM sav_lor B 
                        WHERE B.slor_numor = A.slor_numor) AS sommeQuantiteALivrer


                        from sav_lor A
                        inner join sav_eor on seor_soc = slor_soc 
                                            and seor_succ = slor_succ 
                                            and seor_numor = slor_numor

                        where slor_soc = 'HF'
                        and slor_typlig = 'P'
                        and slor_succ = '01'
                        and seor_serv ='SAV'
                        and seor_typeor not in('950', '501')
                        and seor_numor||'-'||TRUNC(slor_nogrp/100) in ('".$lesOrSelonCondition['numOrValideString']."')
                        $agenceUser
                        $piece
                        $orCompletNom
                        $designation
                        $referencePiece 
                        $constructeur 
                        $dateDebut
                        $dateFin
                        $numOr
                        $numDit
                        $agence
                        $service

                        order by 
                            seor_refdem desc,
                            slor_nogrp/100 desc, 
                            seor_numor asc, 
                            slor_nolign asc
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperationConstructeur()
    {
        $statement = " SELECT DISTINCT
            trim(slor_constp) as constructeur
           
            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc 
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'
            and slor_succ = '01'
            and slor_typlig = 'P'
    	    and slor_constp <> '---'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_combine(array_column($this->convertirEnUtf8($data), 'constructeur'), array_column($this->convertirEnUtf8($data), 'constructeur'));
    }


    public function recupNumOr($criteria = [])
    {   
        if(!empty($criteria['niveauUrgence'])){
            $niveauUrgence = " and id_niveau_urgence = '" . $criteria['niveauUrgence']->getId() . "'";
        } else {
            $niveauUrgence = null;
        }

        if(!empty($criteria['numDit'])){
            $numDit = " and numero_demande_dit = '" . $criteria['numDit'] ."'";
        } else {
            $numDit = null;
        }

        if(!empty($criteria['numOr'])){
            $numOr = " and numero_or = '" . $criteria['numOr'] . "'";
        } else {
            $numOr = null;
        }

        $statement = "SELECT 
        numero_or 
        FROM demande_intervention
        WHERE date_validation_or is not null
        and date_validation_or <>'' 
        {$niveauUrgence}
        {$numDit}
        {$numOr}
        ";


        $execQueryNumOr = $this->connexion->query($statement);

        $numOr = array();

        while ($row_num_or = odbc_fetch_array($execQueryNumOr)) {
            $numOr[] = $row_num_or;
        }

        return $numOr;
    }


    public function recupereAutocompletionDesignation($designations)
    {      
        $statement = "SELECT DISTINCT
            
            trim(slor_desi) as designationi

            from sav_lor 
            
            where 
            slor_soc = 'HF'

            and slor_succ = '01'
            and slor_desi like '%" . $designations . "%'
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recuperAutocompletionRefPiece($refPiece)
    {
        $statement ="SELECT 
            
            trim(slor_refp) as referencePiece
           
            from sav_lor 
            
            where 
            slor_soc = 'HF'
            and slor_succ = '01'
            and slor_refp like '%" . $refPiece . "%'
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

public function agence()
    {
        $statement = "  SELECT DISTINCT
                            slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }

    public function service($agence)
    {
        $statement = "  SELECT DISTINCT
                            slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as service
                        FROM sav_lor
                        WHERE slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) <> ''
                        AND slor_soc = 'HF'
                        AND slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) = '".$agence."'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        $dataUtf8 = $this->convertirEnUtf8($data);

        return array_map(function($item) {
       
            return [
                "value" => $item['service'], 
                "text"  => $item['service']
            ];
        }, $dataUtf8);
    }
    

    public function agenceUser()
    {
        $statement = "  SELECT DISTINCT
                            slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence
                        FROM sav_lor
                        WHERE slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) <> ''
                        AND slor_soc = 'HF'
                        AND slor_succdeb IN ('01','50')
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }
}