<?php

namespace App\Model\inventaire;

use App\Model\Traits\ConditionModelTrait;

trait InventaireModelTrait
{
    use ConditionModelTrait;

    private function agence($criteria)
    {
        if (! empty($criteria->getAgence())) {
            $agence = "AND ainvi_succ IN ('".implode("','", $criteria->getAgence())."')";
        } else {
            $agence = "";
        }

        return $agence;
    }

    private function dateDebut($criteria)
    {
        if (! empty($criteria->getDateDebut())) {
            $dateD = "AND ainvi_date >= TO_DATE('".$criteria->getDateDebut()->format("Y-m-d")."','%Y-%m-%d')";
        } else {
            $dateD = "";
        }

        return $dateD;
    }

    private function dateFin($criteria)
    {
        if (! empty($criteria->getDateFin())) {
            $dateF = "AND ainvi_date <= TO_DATE('".$criteria->getDateFin()->format("Y-m-d")."','%Y-%m-%d')";
        } else {
            $dateF = "";
        }

        return $dateF;
    }
}
