<?php

namespace App\Model\magasin\lcfng;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Model\Traits\ConditionModelTrait;

class ListeCdeFrnNonGenererModel extends Model
{
    use ConversionModel;
    use ConditionModelTrait;

    public function getListeCdeFrnNonGenerer(array $criteria = [], string $numOrValide)
    {
        //condition de recherche
        $designation = $this->conditionLike('resultat.Designations', 'designation',$criteria);
        $referencePiece = $this->conditionLike('resultat.referencePiece', 'referencePiece',$criteria);
        $constructeur = $this->conditionLike('resultat.constructeur', 'constructeur',$criteria);
        $numDit = $this->conditionLike('resultat.numDit', 'numDit',$criteria);
        $numDoc = $this->conditionLike('TO_CHAR(resultat.NumDocument)', 'numDoc', $criteria);
        $typeDoc = $this->conditionSigne('resultat.type_document', 'typeDoc', '=', $criteria);
        $dateDebutDoc = $this->conditionDateSigne( 'resultat.DateDocument', 'dateDebutDoc', $criteria, '>=');
        $dateFinDoc = $this->conditionDateSigne( 'resultat.DateDocument', 'dateFinDoc', $criteria, '<=');
        
        $piece = $this->conditionPieceLcfng('resultat.constructeur', 'typeLigne', $criteria); 

        $numCli = $this->conditionLike('resultat.agenceServiceDebiteur', 'numClient', $criteria);
        $agence = $this->conditionAgenceLcfng("resultat.agenceServiceDebiteur", 'agence',$criteria); 
        $service = $this->conditionServiceLcfng("resultat.agenceServiceDebiteur", 'service',$criteria); 

        $agenceEmetteur = $this->conditionAgenceLcfng("resultat.agenceServiceCrediteur", 'agenceEmetteur',$criteria);
        $serviceEmetteur = $this->conditionServiceLcfng("resultat.agenceServiceCrediteur", 'serviceEmetteur',$criteria);


        $statement = " SELECT * from (SELECT
                trim(seor_refdem) as NumDit,
                seor_numor as NumDocument,
                trim(slor_constp) as constructeur,
                trim(slor_refp) as referencePiece,
                trim(slor_desi) AS Designations,
                trunc(slor_nogrp/100) as numInterv,
                slor_nolign as numeroLigne,
                slor_datec as DateDocument,
                slor_succdeb || '-' ||  slor_servdeb as agenceServiceDebiteur,
                slor_succ  || '-' || slor_servcrt  as agenceServiceCrediteur,
                TRUNC(CASE
                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                    WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea
                END) AS quantiteDemander,
                TRUNC(slor_qterel) AS quantiteReliquat,
                'OR' as type_document
                from sav_lor
                inner join sav_eor on seor_soc = slor_soc
                and seor_succ = slor_succ
                and seor_numor = slor_numor
                where slor_soc = 'HF'
                and slor_succ in ('01')
                AND seor_typeor not in('950', '501')
                AND slor_typlig = 'P'
                and slor_constp not like 'Z%'
                AND slor_pos = 'EC'
                AND seor_serv ='SAV'
                and slor_qterel > 0 and nvl(slor_numcf,0) <= 0
                --and slor_numor in ('".$numOrValide."')

                UNION

                -- CIS
                SELECT
                '' AS NumDit,
                nlig_numcde AS NumDocument,
                trim(nlig_constp) as constructeur,
                trim(nlig_refp) as referencePiece,
                trim(nlig_desi) AS Designations,
                1 as numInterv,
                nlig_nolign as numeroLigne,
                nlig_datecde AS DateDocument,
                to_char(nlig_numcli) AS agenceServiceDebiteur,
                TRIM(nlig_succ) || '-' || trim(nent_servcrt) AS agenceServiceCrediteur,
                TRUNC(nlig_qtecde) AS quantiteDemander,
                TRUNC(nlig_qtecde - nlig_qteliv) AS quantiteReliquat,
                CASE
                WHEN nlig_natop = 'CIS' THEN
                'CIS'
                WHEN nlig_natop = 'DIR' THEN
                'VTE NEGOCE'
                ELSE
                ''
                END as type_document
                FROM neg_lig, neg_ent

                WHERE nlig_soc = 'HF' and nlig_succ = '01' and nvl(nlig_numcf,0) = 0
                and nlig_qtecde <> nlig_qteliv and nlig_qtewait =0 and nlig_qtealiv= 0
                and nlig_constp not in ('Nmc','ZDI','ZAR')
                and nent_numcde = nlig_numcde and nent_natop not in ('DEV')
                and nent_soc = nlig_soc and nent_numcde = nlig_numcde

                order by 8 desc, 2, 7) As resultat
            WHERE resultat.numeroLigne > 0
            --and resultat.NumDocument in ('".$numOrValide."')
            $designation
            $referencePiece
            $constructeur
            $numDit
            $numDoc
            $dateDebutDoc
            $dateFinDoc
            $typeDoc
            $numCli
            $piece
            $agence
            $service
            $agenceEmetteur
            $serviceEmetteur
        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}