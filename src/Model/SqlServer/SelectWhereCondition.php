<?php

namespace App\Model\SqlServer;

use App\Model\Condition\AbstractSelectWhereCondition;

class SelectWhereCondition extends AbstractSelectWhereCondition
{
    /**
     * {@inheritDoc}
     */
    public function between(string $column, ?mixed $d1 = null, ?mixed $d2 = null): string
    {
        if ($d1 !== null && $d1 instanceof \DateTimeInterface) $d1 = $d1->format('Y-m-d');
        if ($d2 !== null && $d2 instanceof \DateTimeInterface) $d2 = $d2->format('Y-m-d');

        $sql = "";
        if ($d1) $sql .= " AND $column >= '$d1'";
        if ($d2) $sql .= " AND $column <= '$d2'";

        return $sql;
    }
}
