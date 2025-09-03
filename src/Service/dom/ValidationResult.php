<?php

namespace App\Service\dom;

/**
 * Classe pour représenter le résultat d'une validation
 */
class ValidationResult
{
    private bool $valid;
    private array $errors;
    private array $warnings;

    public function __construct(bool $valid, array $errors = [], array $warnings = [])
    {
        $this->valid = $valid;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    /**
     * Indique si la validation est réussie
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Retourne les erreurs de validation
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Retourne les avertissements
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Indique s'il y a des erreurs
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Indique s'il y a des avertissements
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Ajoute une erreur
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
        $this->valid = false;
    }

    /**
     * Ajoute un avertissement
     */
    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * Retourne le résultat sous forme de tableau
     */
    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }
}
