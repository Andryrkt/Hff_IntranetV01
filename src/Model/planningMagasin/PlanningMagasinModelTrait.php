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

    private function agenceDebite($criteria, string $codeAgence)
    {
        if ($codeAgence !== "-0") {
            $agenceDebite = " AND nent_succ = '$codeAgence' ";
        } elseif (!empty($criteria->getAgenceDebite())) {
            $agenceDebite = " AND nent_succ = '" . $criteria->getAgenceDebite() . "' ";
        } else {
            $agenceDebite = "";
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
    private function commercial($criteria)
    {
        if (!empty($criteria->getCommercial())) {
            $codeCommercial = explode('-', $criteria->getCommercial())[0];
            $condCommercial = " AND TRIM(nent_codope) ='$codeCommercial'  ";
        } else {
            $condCommercial = "";
        }
        return $condCommercial;
    }
    private function refClient($criteria)
    {
        if (!empty($criteria->getRefcde())) {
            $condRefclient = "AND NENT_REFCDE like '%" . $criteria->getRefcde() . "%'  ";
        } else {
            $condRefclient = "";
        }
        return $condRefclient;
    }

    private function numeroDevis($criteria)
    {
        if (!empty($criteria->getNumeroDevis())) {
            $condNumeroDevis = "AND nent_numcde = '" . $criteria->getNumeroDevis() . "'  ";
        } else {
            $condNumeroDevis = "";
        }
        return $condNumeroDevis;
    }
}
