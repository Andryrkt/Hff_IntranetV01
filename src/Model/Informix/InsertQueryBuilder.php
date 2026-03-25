<?php

namespace App\Model\Informix;

class InsertQueryBuilder
{
    private string $table;
    private array $data = [];
    private array $excludeEmpty = [];
    private array $excludeNull = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
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
            throw new \Exception("Aucune donnée à insérer");
        }

        $colonnes = array_keys($filteredData);
        $parametres = array_map(function ($col) {
            return ":" . $col;
        }, $colonnes);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $colonnes),
            implode(', ', $parametres)
        );

        return [
            'sql' => $sql,
            'params' => $filteredData
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
