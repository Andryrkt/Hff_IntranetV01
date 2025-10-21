<?php

namespace App\Service\dit;

/**
 * Service de validation des soumissions DIT
 */
class OrSubmissionValidatorService
{
    /**
     * Valider une soumission DIT
     */
    public function validateSubmission(array $data): array
    {
        $errors = [];

        // Validation basique
        if (empty($data['title'])) {
            $errors[] = 'Le titre est requis';
        }

        if (empty($data['description'])) {
            $errors[] = 'La description est requise';
        }

        return $errors;
    }

    /**
     * Vérifier si la soumission est valide
     */
    public function isValid(array $data): bool
    {
        return empty($this->validateSubmission($data));
    }
}
