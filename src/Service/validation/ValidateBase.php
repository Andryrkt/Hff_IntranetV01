<?php

namespace App\Service\validation;

class ValidateBase
{
    /**
     * Vérifie si un identifiant est manquant (null ou chaîne vide)
     * 
     * @param string|null $identifier L'identifiant à vérifier
     * @return bool true si l'identifiant est manquant, false sinon
     */
    protected function isIdentifierMissing(?string $identifier): bool
    {
        return empty($identifier);
    }
}