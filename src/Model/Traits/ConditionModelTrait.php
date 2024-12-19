<?php

namespace App\Model\Traits;

use App\Service\GlobalVariablesService;

trait ConditionModelTrait
{
    private function conditionLike(string $colonneBase, string $indexCriteria, $criteria)
    {
        if(!empty($criteria[$indexCriteria])) {
            $condition = " AND {$colonneBase} LIKE '%".$criteria[$indexCriteria]."%'";
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
        
            $condition = " AND {$colonneBase} {$signe} TO_DATE('" . $formattedDate. "', '%Y-%m-%d') ";
        } else {
            $condition = "";
        }
        return $condition;
    }

    private function conditionSigne(string $colonneBase, string $indexCriteria, string $signe, array $criteria)
    {
        if(!empty($criteria[$indexCriteria])) {
            $condition = " AND {$colonneBase} {$signe} '".$criteria[$indexCriteria]."'";
        } else {
            $condition = "";
        }

        return $condition;
    }

    private function conditionPiece(string $indexCriteria, array $criteria): ?string
    {   
        if (!empty($criteria[$indexCriteria])) {
            if($criteria[$indexCriteria] === "PIECES MAGASIN"){
                $piece = " AND slor_constp in (".GlobalVariablesService::get('pieces_magasin').")";
            } else if($criteria[$indexCriteria] === "LUB") {
                $piece = " AND slor_constp in (".GlobalVariablesService::get('lub').")";
            } else if($criteria[$indexCriteria] === "ACHATS LOCAUX") {
                $piece = " AND slor_constp in (".GlobalVariablesService::get('achat_locaux').") ";
            }else if($criteria[$indexCriteria] === "TOUTS PIECES") {
                $piece = null;
            }
        } else {
            $piece = " AND slor_constp in (".GlobalVariablesService::get('pieces_magasin').")";
        }

        return $piece;
    }

    private function conditionOrCompletOuNonCis(string $indexCriteria, array $criteria): string 
    {
        if(!empty($criteria[$indexCriteria])) {
            if($criteria[$indexCriteria] === 'ORs COMPLET'){
                $orCompletNom = " HAVING 
                            SUM(nlig_qtecde) = (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0 ";
            } else if($criteria[$indexCriteria] === 'ORs INCOMPLETS') {
                $orCompletNom = " HAVING 
                            SUM(nlig_qtecde) > (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0";
            } else if($criteria[$indexCriteria] === 'TOUTS LES OR'){
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

    private function conditionOrCompletOuNonOrALivrer(string $indexCriteria, array $lesOrSelonCondition, array $criteria): string 
    {
        if(!empty($criteria[$indexCriteria])) {
            if($criteria[$indexCriteria] === 'ORs COMPLET'){
                $orCompletNom = " AND slor_numor||'-'||TRUNC(slor_nogrp/100) IN ('".$lesOrSelonCondition['numOrLivrerComplet']."')";
            } else if($criteria[$indexCriteria] === 'ORs INCOMPLETS') {
                $orCompletNom = " AND slor_numor||'-'||TRUNC(slor_nogrp/100) IN ('".$lesOrSelonCondition['numOrLivrerIncomplet']."')";
            } else if($criteria[$indexCriteria] === 'TOUTS LES OR'){
                $orCompletNom = " AND slor_numor||'-'||TRUNC(slor_nogrp/100) IN ('".$lesOrSelonCondition['numOrLivrerTout']."')";
            }
        } else {
            $orCompletNom =  " AND slor_numor||'-'||TRUNC(slor_nogrp/100) IN ('".$lesOrSelonCondition['numOrLivrerComplet']."')";
        }

        return $orCompletNom;
    }

    private function conditionAgenceUser(string $indexCriteria, array $criteria): string 
    {
        if(!empty($criteria[$indexCriteria])){
            $agenceUser = " AND slor_succ = '".explode('-',$criteria[$indexCriteria])[0]."'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionAgenceService(string $colonneBase, string $indexCriteria,array $criteria): string 
    {
        if(!empty($criteria[$indexCriteria])){
            $agenceUser = " AND {$colonneBase} = '".explode('-',$criteria[$indexCriteria])[0]."'";
        } else {
            $agenceUser = "";
        }

        return $agenceUser;
    }

    private function conditionOrValide($orValides, $numORItvValides)
    {
        if($orValides)
        {
            $orValide = " AND slor_numor||'-'||TRUNC(slor_nogrp/100) IN ('".$numORItvValides."')";
        } else {
            $orValide = '';
        }

        return $orValide;
    }

}