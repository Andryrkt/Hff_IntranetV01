<?php

namespace App\Model\Condition;

/**
 * Implémentation commune des conditions WHERE partagées par tous les moteurs.
 *
 * Fournit les opérateurs standards (eq, ne, in, ni, like, nlike) dont le
 * comportement est identique quel que soit le SGBD cible. La méthode
 * `between` reste abstraite car sa syntaxe (format de date, mots-clés)
 * diffère selon les classes filles.
 */
abstract class AbstractSelectWhereCondition implements WhereConditionInterface
{
    /**
     * {@inheritDoc}
     */
    public function eq(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "AND $column = '$value'";
    }

    /**
     * {@inheritDoc}
     */
    public function ne(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "AND $column <> '$value'";
    }

    /**
     * {@inheritDoc}
     */
    public function in(string $column, ?array $values): string
    {
        $values = $values ? implode(',', $values) : null;
        if (!$values) return '';
        return "AND $column IN ('$values')";
    }

    /**
     * {@inheritDoc}
     */
    public function ni(string $column, ?array $values): string
    {
        $values = $values ? implode(',', $values) : null;
        if (!$values) return '';
        return "AND $column NOT IN ('$values')";
    }

    /**
     * {@inheritDoc}
     */
    public function like(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "AND $column LIKE '%$value%'";
    }

    /**
     * {@inheritDoc}
     */
    public function nlike(string $column, ?string $value): string
    {
        $value = $value ? trim($value) : null;
        if (!$value) return '';
        return "AND $column NOT LIKE '%$value%'";
    }

    /**
     * {@inheritDoc}
     *
     * À implémenter par chaque classe fille selon la syntaxe SQL
     * (format de date, mot-clé BETWEEN) attendue par le SGBD ciblé.
     */
    abstract public function between(string $column, ?\DateTimeInterface $d1 = null, ?\DateTimeInterface $d2 = null): string;
}
