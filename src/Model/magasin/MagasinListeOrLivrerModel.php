<?php

namespace App\Model\magasin;

use App\Controller\Traits\FormatageTrait;
use App\Model\Model;
use App\Model\Traits\ConversionModel;

class MagasinListeOrLivrerModel extends Model
{ 
    use ConversionModel;
    use FormatageTrait;

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

    public function recupOrLivrerComplet()
    {
        $statement = " SELECT 
                            seor_numor as numeroOr
                            from sav_lor as A
                            inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                            where slor_soc = 'HF'
                            AND  (Select 
                                    SUM ( CASE 
                                            WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                                            WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea 
                                        END ) 
                                from sav_lor as B where B.slor_numor =A.slor_numor ) 
                            = (Select SUM( slor_qteres ) from  sav_lor as B where B.slor_numor =A.slor_numor )
                            and slor_qteres <> 0
                            and slor_typlig = 'P'
                            and slor_constp not like 'Z%'
                            and slor_constp not in ('LUB')
                            and slor_succ = '01'
                            and seor_serv ='SAV'
                    ";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerIncomplet()
    {
        $statement = " SELECT distinct slor_numor from sav_lor
                        inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ and seor_numor = slor_numor
                        where
                        slor_qteres > 0
                        and slor_typlig = 'P'  
                        and slor_constp not in ('LUB')
                        and slor_typlig = 'P' 
                        and slor_constp not like 'Z%'
                        and seor_serv = 'SAV'
                        and seor_succ = '01'
                        and seor_typeor not in (501,550)
                    ";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupOrLivrerTout()
    {
        $statement = " SELECT 
                            seor_numor as numeroOr
                            from sav_lor as A
                            inner join sav_eor on seor_soc = slor_soc 
                            and seor_succ = slor_succ 
                            and seor_numor = slor_numor
                            where slor_soc = 'HF'
                            AND  (Select 
                                    SUM ( CASE 
                                            WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                                            WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea 
                                        END ) 
                                from sav_lor as B where B.slor_numor =A.slor_numor ) 
                            >= (Select SUM( slor_qteres ) from  sav_lor as B where B.slor_numor =A.slor_numor  )
                            and slor_qteres <> 0
                            and slor_typlig = 'P'
                            and slor_constp not like 'Z%'
                            and slor_constp not in ('LUB')
                            and slor_succ = '01'
                            and seor_serv ='SAV'
                    ";
        
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupereListeMaterielValider( array $criteria = [], array $lesOrSelonCondition)
    {
    
        // if ($numOrValide === "") {
        //     $numOrValide = '0';
        //and slor_numor in (". $numOrValide .")
        // }

        if(!empty($criteria['designation'])){
            $designation = " AND slor_desi like '%" . $criteria['designation'] . "%'";
        } else {
            $designation = null;
        }
        
        if(!empty($criteria['referencePiece'])){
            $referencePiece = " AND slor_refp like '%" . $criteria['referencePiece'] . "%'";
        } else {
            $referencePiece = null;
        }

        if(!empty($criteria['constructeur'])){
            $constructeur = " AND slor_constp  ='" . $criteria['constructeur'] . "'";
        } else {
            $constructeur = null;
        }

        if(!empty($criteria['dateDebut'])){
            $dateDebut = " AND slor_datec >='" . $criteria['dateDebut']->format('m/d/Y') ."'";
        } else {
            $dateDebut = null;
        }

        if(!empty($criteria['dateFin'])){
            $dateFin = " AND slor_datec <= '" .$criteria['dateFin']->format('m/d/Y')."'";
        } else {
            $dateFin = null;
        }

        if(!empty($criteria['numOr'])){
            $numOr = " AND seor_numor  = '" . $criteria['numOr'] . "'";
        } else {
            $numOr = null;
        }

        if(!empty($criteria['numDit'])){
            $numDit = " AND seor_refdem  = '" . $criteria['numDit'] . "'";
        } else {
            $numDit = null;
        }

        if(!empty($criteria['orCompletNon'])) {
            if($criteria['orCompletNon'] === 'ORs COMPLET'){
                $orCompletNom = " AND slor_numor IN ('".$lesOrSelonCondition['numOrLivrerComplet']."')";
            } else if($criteria['orCompletNon'] === 'ORs INCOMPLETS') {
                $orCompletNom = " AND slor_numor IN ('".$lesOrSelonCondition['numOrLivrerIncomplet']."')";
            } else if($criteria['orCompletNon'] === 'TOUTS LES OR'){
                $orCompletNom = " AND slor_numor IN ('".$lesOrSelonCondition['numOrLivrerTout']."')";
            }
        } else {
            $orCompletNom =  " AND slor_numor IN ('".$lesOrSelonCondition['numOrLivrerComplet']."')";
        }

        if (!empty($criteria['pieces'])) {
            if($criteria['pieces'] === "PIECES MAGASIN"){
                $piece = " AND slor_constp not like 'Z%'
                        and slor_constp not in ('LUB')
                    ";
            } else if($criteria['pieces'] === "LUB") {
                $piece = " AND slor_constp in ('LUB') ";

            } else if($criteria['pieces'] === "ACHATS LOCAUX") {
                $piece = " AND slor_constp like 'Z%' ";

            }else if($criteria['pieces'] === "TOUTS PIECES") {
                $piece = null;
            }
        } else {
            $piece = " AND slor_constp not like 'Z%'
                        and slor_constp not in ('LUB')
                    ";
        }
          
        if(!empty($criteria['agence'])){
            $agence = " AND slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) = '".$criteria['agence']."'";
        } else {
            $agence = "";
        }

        if(!empty($criteria['service'])){
            $service = " AND slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) = '".$criteria['service']."'";
        } else {
            $service = "";
        }


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
                        slor_succdeb||'-'||(select trim(asuc_lib) from agr_succ where asuc_numsoc = slor_soc and asuc_num = slor_succdeb) as agence,
                        slor_servdeb||'-'||(select trim(atab_lib) from agr_tab where atab_nom = 'SER' and atab_code = slor_servdeb) as service,
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
                       
                        order by slor_datec desc, 
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


    
}