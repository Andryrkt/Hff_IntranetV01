<?php

namespace App\Model\Informix;

use App\Model\Condition\AbstractSelectWhereCondition;

class SelectWhereCondition extends AbstractSelectWhereCondition
{
    /**
     * {@inheritDoc}
     * 
     * @param string $column Nom de la colonne
     * @param ?\DateTimeInterface $d1 Date de début (défaut si null : borne minimale)
     * @param ?\DateTimeInterface $d2 Date de fin (défaut si null : borne maximale)
     * 
     * @return string Fragment SQL ou chaîne vide
     * 
     * Bornes par défaut : 1900-01-01 si $d1 est absent, 3000-12-31 si $d2 est absent.
     */
    public function between(string $column, ?\DateTimeInterface $d1 = null, ?\DateTimeInterface $d2 = null): string
    {
        $d1 = $d1 ? $d1->format('Y-m-d') : '1900-01-01';
        $d2 = $d2 ? $d2->format('Y-m-d') : '3000-12-31';

        return "AND $column BETWEEN datetime(TRIM($d1)) YEAR TO DAY AND datetime(TRIM($d2)) YEAR TO DAY";
    }
}
