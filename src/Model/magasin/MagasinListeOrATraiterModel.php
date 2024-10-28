<?php

namespace App\Model\magasin;

use App\Controller\Traits\FormatageTrait;
use App\Model\Model;
use App\Model\Traits\ConditionModelTrait;
use App\Model\Traits\ConversionModel;

class MagasinListeOrATraiterModel extends Model
{ 
    use ConversionModel;
    use FormatageTrait;
    use ConditionModelTrait;


    public function recupNumeroItv($numOr, $stringItv)
    {
        $statement = " SELECT  
                        COUNT(sitv_interv) as nbItv
                        FROM sav_itv 
                        where sitv_numor='".$numOr."'
                        AND sitv_interv NOT IN ('".$stringItv."')";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);

    }

    
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


    public function recupereListeMaterielValider($criteria = [], $lesOrSelonCondition)
    {

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

        $statement = "SELECT 
            trim(seor_refdem) as referenceDIT,
            seor_numor as numeroOr,
            trim(slor_constp) as constructeur, 
            trim(slor_refp) as referencePiece, 
            trim(slor_desi) as designationi, 
            CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea END AS quantiteDemander,
            slor_qteres as quantiteReserver,
            slor_qterea as quantiteLivree,
            slor_qterel as quantiteReliquat,
            slor_datec as dateCreation,
            slor_nogrp/100 as numInterv,
            slor_nolign as numeroLigne,
            slor_datec, 
            --slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence,
            --slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as service,
            slor_succdeb as agence,
            slor_servdeb as service,
            slor_succ as agenceCrediteur,
            slor_servcrt as serviceCrediteur

            from sav_lor 
            inner join sav_eor on seor_soc = slor_soc 
            and seor_succ = slor_succ 
            and seor_numor = slor_numor
            where 
            slor_soc = 'HF'
            and seor_typeor not in('950', '501')
            and slor_succ = '01'
            and seor_numor||'-'||TRUNC(slor_nogrp/100) in ('".$lesOrSelonCondition['numOrValideString']."')
            $agenceUser
            $designation
            $referencePiece 
            $constructeur 
            $dateDebut
            $dateFin
            $numOr
            $numDit
            $piece
            $agence
            $service
            and slor_typlig = 'P'
            and slor_pos = 'EC'
            and seor_serv ='SAV'
            and slor_qteres = 0 and slor_qterel = 0 and slor_qterea = 0
            order by numInterv ASC, seor_dateor DESC, slor_numor DESC, numeroLigne ASC
        ";

        dump($statement);
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
            $niveauUrgence = " AND id_niveau_urgence = '" . $criteria['niveauUrgence']->getId() . "'";
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
                        AND slor_succdeb IN ('01', '20', '50')
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'agence');
    }

}