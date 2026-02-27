<?php

namespace App\Model\inventaire;

use App\Model\Traits\ConditionModelTrait;
use App\Service\TableauEnStringService;

trait InventaireModelTrait
{
    use ConditionModelTrait;

    private function agence($criteria)
    {
        // dd($criteria);
        if (!empty($criteria->getAgence())) {
            $agence = "AND ainvi_succ IN ('" . implode("','", $criteria->getAgence()) . "')";
        } else {
            $agence = "";
        }
        return $agence;
    }
    private function invenatireDispoligne($criteria)
    {
        // dd(TableauEnStringService::TableauEnString(',',$criteria->getInventaireDispo()));
        if (!empty($criteria->getInventaireDispo())) {
            $agence = "AND ainvi_numinv  IN (" . TableauEnStringService::TableauEnString(',', $criteria->getInventaireDispo()) . ")";
        } else {
            $agence = "AND ainvi_numinv  IN ('')";
        }
        return $agence;
    }

    private function dateDebut($criteria)
    {
        if (!empty($criteria->getDateDebut())) {
            $dateD = "AND ainvi_date >= TO_DATE('" . $criteria->getDateDebut()->format("Y-m-d") . "','%Y-%m-%d')";
        } else {
            $dateD = "";
        }
        return $dateD;
    }
    private function dateFin($criteria)
    {
        if (!empty($criteria->getDateFin())) {
            $dateF = "AND ainvi_date <= TO_DATE('" . $criteria->getDateFin()->format("Y-m-d") . "','%Y-%m-%d')";
        } else {
            $dateF = "";
        }
        return $dateF;
    }

    private function agenceArray(array $criteria)
    {
        if (!empty($criteria['agence'])) {
            $agence = "AND ainvi_succ IN ('" . $criteria['agence'] . "')";
        } else {
            $agence = "";
        }
        return $agence;
    }

    private function dateDebutArray(array $criteria)
    {
        if (!empty($criteria['dateDebut'])) {
            $dateD = "AND ainvi_date >= TO_DATE('" . $criteria['dateDebut']->format("Y-m-d") . "','%Y-%m-%d')";
        } else {
            $dateD = "";
        }
        return $dateD;
    }
    private function dateFinArray(array $criteria)
    {
        if (!empty($criteria['dateFin'])) {
            $dateF = "AND ainvi_date <= TO_DATE('" . $criteria['dateFin']->format("Y-m-d") . "','%Y-%m-%d')";
        } else {
            $dateF = "";
        }
        return $dateF;
    }
}
