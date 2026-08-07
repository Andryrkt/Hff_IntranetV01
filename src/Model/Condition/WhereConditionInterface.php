<?php

namespace App\Model\Condition;

/**
 * Interface définissant les opérations de construction de clauses WHERE
 * pour des requêtes SQL de type SELECT.
 *
 * Chaque méthode retourne un fragment de clause SQL préfixé par "AND ",
 * prêt à être concaténé dans une requête. Retourne une chaîne vide
 * si la valeur fournie est nulle ou vide.
 */
interface WhereConditionInterface
{
    /**
     * Génère une condition d'égalité (colonne = valeur).
     *
     * @param string $column Nom de la colonne
     * @param ?string $value Valeur à comparer
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function eq(string $column, ?string $value): string;

    /**
     * Génère une condition de différence (colonne <> valeur).
     *
     * @param string $column Nom de la colonne
     * @param ?string $value Valeur à exclure
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function ne(string $column, ?string $value): string;

    /**
     * Génère une condition d'appartenance à une liste (colonne IN (...)).
     *
     * @param string $column Nom de la colonne
     * @param array<int, string>|null $values Liste de valeurs autorisées
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function in(string $column, ?array $values): string;

    /**
     * Génère une condition de non-appartenance à une liste (colonne NOT IN (...)).
     *
     * @param string $column Nom de la colonne
     * @param array<int, string>|null $values Liste de valeurs exclues
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function ni(string $column, ?array $values): string;

    /**
     * Génère une condition de recherche approximative (colonne LIKE '%valeur%').
     *
     * @param string $column Nom de la colonne
     * @param ?string $value Sous-chaîne recherchée
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function like(string $column, ?string $value): string;

    /**
     * Génère une condition de recherche approximative négative (colonne NOT LIKE '%valeur%').
     *
     * @param string $column Nom de la colonne
     * @param ?string $value Sous-chaîne à exclure
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function nlike(string $column, ?string $value): string;

    /**
     * Génère une condition d'intervalle de dates (colonne BETWEEN d1 AND d2).
     *
     * L'implémentation (format de date, syntaxe SQL) peut varier selon
     * le moteur de base de données ciblé par la classe concrète.
     *
     * @param string $column Nom de la colonne
     * @param ?\DateTimeInterface $d1 Date de début (défaut si null : borne minimale)
     * @param ?\DateTimeInterface $d2 Date de fin (défaut si null : borne maximale)
     * 
     * @return string Fragment SQL ou chaîne vide
     */
    public function between(string $column, ?\DateTimeInterface $d1 = null, ?\DateTimeInterface $d2 = null): string;
}
