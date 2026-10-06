<?php

namespace App\Model\Informix;

class SelectWhereCondition
{
    public function eq(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "and " . $column . " = '" . $value . "'";
    }

    public function ne(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "and " . $column . " <> '" . $value . "'";
    }

    public function in(string $column, ?array $values): string
    {
        $values = $values ? implode(',', $values) : null;
        if (!$values) return '';
        return "and " . $column . " in ('" . $values . "')";
    }

    public function ni(string $column, array $values): string
    {
        $values = $values ? implode(',', $values) : null;
        if (!$values) return '';
        return "and " . $column . " not in ('" . $values . "')";
    }

    public function like(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "and " . $column . " like '%" . $value . "%'";
    }

    public function nlike(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "and " . $column . " not like '%" . $value . "%'";
    }

    /**
     * Undocumented function
     *
     * @param string $column
     * @param \DateTimeImmutable|\DateTime|null $d1
     * @param \DateTimeImmutable|\DateTime|null $d2
     * @return string
     */
    public function between(string $column,  $d1 = null, $d2 = null): string
    {
        $d1 = $d1 ? trim($d1->format('Y-m-d')) : '1900-01-01';
        $d2 = $d2 ? trim($d2->format('Y-m-d')) : '3000-12-31';

        $d1 = "datetime(" . $d1 . ") year to day";
        $d2 = "datetime(" . $d2 . ") year to day";
        return "and " . $column . " between " . $d1 . " and " . $d2;
    }
}
