<?php

namespace App\Model\Informix;

class UpdateQueryBuilder
{
    private string $table;
    private array $data = [];
    private array $conditions = [];
    private array $excludeEmpty = [];
    private array $excludeNull = [];
    private string $conditionOperator = 'AND';

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function where(string $column, $value, string $operator = '='): self
    {
        $this->conditions[] = [
            'column' => $column,
            'value' => $value,
            'operator' => $operator
        ];
        return $this;
    }

    public function whereRaw(string $condition): self
    {
        $this->conditions[] = [
            'raw' => $condition,
            'value' => null
        ];
        return $this;
    }

    public function setConditionOperator(string $operator): self
    {
        $this->conditionOperator = strtoupper($operator);
        return $this;
    }

    public function excludeEmptyColumns(array $columns): self
    {
        $this->excludeEmpty = $columns;
        return $this;
    }

    public function excludeNullColumns(array $columns): self
    {
        $this->excludeNull = $columns;
        return $this;
    }

    public function build(): array
    {
        // Filtrer les données
        $filteredData = $this->filterData();

        if (empty($filteredData)) {
            throw new \Exception("Aucune donnée à mettre à jour");
        }

        if (empty($this->conditions)) {
            throw new \Exception("Aucune condition WHERE spécifiée pour la mise à jour");
        }

        // Construire la partie SET
        $setClauses = [];
        $params = [];

        foreach ($filteredData as $colonne => $valeur) {
            $paramName = ':' . $colonne;
            $setClauses[] = sprintf("%s = %s", $colonne, $paramName);
            $params[$colonne] = $valeur;
        }

        // Construire la partie WHERE
        $whereClauses = [];
        foreach ($this->conditions as $index => $condition) {
            if (isset($condition['raw'])) {
                $whereClauses[] = $condition['raw'];
            } else {
                $paramName = ':where_' . $index . '_' . str_replace('.', '_', $condition['column']);
                $whereClauses[] = sprintf("%s %s %s", $condition['column'], $condition['operator'], $paramName);
                $params[$paramName] = $condition['value'];
            }
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $this->table,
            implode(', ', $setClauses),
            implode(" {$this->conditionOperator} ", $whereClauses)
        );

        return [
            'sql' => $sql,
            'params' => $params
        ];
    }

    private function filterData(): array
    {
        $filtered = [];

        foreach ($this->data as $colonne => $valeur) {
            // Exclure les colonnes avec valeurs vides
            if (in_array($colonne, $this->excludeEmpty) && $valeur === '') {
                continue;
            }

            // Exclure les colonnes avec valeurs null
            if (in_array($colonne, $this->excludeNull) && $valeur === null) {
                continue;
            }

            $filtered[$colonne] = $valeur;
        }

        return $filtered;
    }
}
