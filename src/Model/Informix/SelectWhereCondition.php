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
        if (empty($values)) return '';
        return $this->createInConditionWithTemp($column, $values);
    }

    public function ni(string $column, array $values): string
    {
        if (empty($values)) return '';
        return $this->createInConditionWithTemp($column, $values, true);
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

    private function createInConditionWithTemp(string $column, array $values, bool $isNotIn = false): string
    {
        if (empty($values)) return $isNotIn ? "" : " AND 1=0";

        $list = "'" . implode("','", array_map([$this, 'escapeString'], $values)) . "'";
        return $isNotIn ? "AND $column NOT IN ($list)" : "AND $column IN ($list)";
    }

    private function escapeString(string $str): string
    {
        return str_replace("'", "''", trim($str));
    }
}
