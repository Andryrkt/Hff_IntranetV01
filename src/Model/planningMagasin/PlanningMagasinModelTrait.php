<?php

namespace App\Model\planningMagasin;

trait planningMagasinModelTrait
{
    private function numcommande($criteria)
    {
        if (!empty($criteria->getNumOr())) {
            $numCommande = "AND nent_numcde = '" . $criteria->getNumOr() . "' ";
        } else {
            $numCommande = "";
        }
       return $numCommande; 
    }
    private function agenceDebite($criteria)
    {
        if (!empty($criteria->getAgenceDebite())) {
            $agenceDebite = " AND nent_succ = '" . $criteria->getAgenceDebite() . "' ";
        } else {
            $agenceDebite = ""; // AND sitv_succdeb in ('01','02','90','92','40','60','50','40','30','20')
        }
        return $agenceDebite;
    }
    private function serviceDebite($criteria)
    {
        if (!empty($criteria->getServiceDebite())) {
            $serviceDebite = " AND nent_servcrt in ('" . implode("','", $criteria->getServiceDebite()) . "')";
        } else {
            $serviceDebite = "";
        }
        return  $serviceDebite;
    }
    private function codeClient($criteria)
  {
    if (!empty($criteria->getNumParc())) {
      $vconditionNumParc = " AND nent_numcli  = '" . $criteria->getNumParc() . "'";
    } else {
      $vconditionNumParc = "";
    }
    return $vconditionNumParc;
  }
}
