<?php

namespace App\Model\magasin;

use App\Controller\Traits\FormatageTrait;
use App\Model\Model;
use App\Model\Traits\ConversionModel;

class MagasinListeOrModel extends Model
{ 
    use ConversionModel;
    use FormatageTrait;


    
    public function recupereListeMaterielValider( $criteria = [])
    {
    
        if(!empty($criteria['designation'])){
            $designation = " and slor_desi like '%" . $criteria['designation'] . "%'";
        } else {
            $designation = null;
        }

        if(!empty($criteria['referencePiece'])){
            $referencePiece = " and slor_refp like '%" . $criteria['referencePiece'] . "%'";
        } else {
            $referencePiece = null;
        }

        if(!empty($criteria['constructeur'])){
            $constructeur = " and slor_constp  ='" . $criteria['constructeur'] . "'";
        } else {
            $constructeur = null;
        }

        if(!empty($criteria['numDit'])){
            $numDit = " and seor_refdem  ='" . $criteria['numDit'] . "'";
        } else {
            $numDit = null;
        }

        if(!empty($criteria['numOr'])){
            $numOr = " and seor_numor  = '" . $criteria['numOr'] . "'";
        } else {
            $numOr = null;
        }

        if(!empty($criteria['numCommande'])){
            $numCommande = " and slornumcf  ='" . $criteria['numCommande'] . "'";
        } else {
            $numCommande = null;
        }

        if(!empty($criteria['dateDebut'])){
            $dateDebut = " and slor_datec >='" . $criteria['dateDebut']->format('m/d/Y') ."'";
        } else {
            $dateDebut = null;
        }

        if(!empty($criteria['dateFin'])){
            $dateFin = " and slor_datec <= '" .$criteria['dateFin']->format('m/d/Y')."'";
        } else {
            $dateFin = null;
        }

        if(!empty($criteria['orATraiter']) && $criteria['orATraiter'] == true){
            $orATraiter = " and slor_qtewait > 0 ";
        } else {
            $orATraiter = null;
        }

        if(!empty($criteria['qteReserve']) && $criteria['qteReserve'] == true){
            $qteReserve = " and slor_qteres > 0 ";
        } else {
            $qteReserve = null;
        }

        if(!empty($criteria['qteLivree']) && $criteria['qteLivree'] == true){
            $qteLivree = " and slor_qterea > 0 ";
        } else {
            $qteLivree = null;
        }

        if(!empty($criteria['qteReliquat']) && $criteria['qteReliquat'] == true){
            $qteReliquat = " and slor_qterel > 0 ";
        } else {
            $qteReliquat = null;
        }

        $statement = "SELECT
            seor_refdem as referenceDIT,
            seor_numor as numeroOr,
            seor_pos as posOr,
            slornumcf as numCommande,
            slor_typcf as typeCf,
            slor_natcm,
            slor_nogrp/100 as numInterv,
            slor_nolign as numeroLigne,
            slor_noligncm as numeroLigneCmde,
            trim(slor_constp) as constructeur,
            trim(slor_refp) as referencePiece,
            trim(slor_desi) as designationi,
            slor_qtewait as quantiteAAllouer,
            slor_qteres as quantiteReserver,
            slor_qterea as quantiteLivree,
            slor_qterel as quantiteReliquat,
            fcde_posc,
            fcde_posl,
            fcde_posf,
            slor_datec as dateCreation
            from sav_lor
            inner join sav_eor on seor_soc = slor_soc
            and seor_succ = slor_succ
            and seor_numor = slor_numor
            -- recuperation info commande --
            left join HFFV_OR_CDEF on
            slorsoc = slor_soc and
            slorsucc = slorsucc and
            slornumor = slor_numor and
            slornolign = slor_nolign and
            slornoligncm = slor_noligncm

            -- information commande fournisseur --
            left join frn_cde on
            fcde_numcde = slornumcf
            and fcde_succ = slorsucc
            and fcde_soc = slorsoc
            where
            slor_soc = 'HF'
            and slor_succ = '01'
            and slor_typlig = 'P'
            --and slor_pos = 'EC'
            and seor_serv in ('SAV')
            -- and seor_serv in ('SAV','VTE') -- a activer pour d'autre agence autre que 92
            --and slor_qtewait > 0
            and slor_constp not like 'Z%'
            and slor_constp not like 'LUB'
            --and slor_numcf = <numero_commande>
            --and slor_numor = <numero_or>
            and year(slor_datel) >= '2024'
            --and slor_datel >= '08/05/2024'
            and seor_refdem like 'DIT%'
            and seor_typeor not in('950', '501')
            $designation
            $referencePiece 
            $constructeur 
            $dateDebut
            $dateFin
            $orATraiter
            $qteReserve
            $qteLivree
            $qteReliquat
            $numDit
            $numOr
            $numCommande
            order by referencedit desc, numeroor desc, numeroligne asc
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


    

    
    
}