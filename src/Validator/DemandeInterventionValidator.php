<?php

namespace App\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DemandeInterventionValidator
{
    public static function validateTextarea($object, ExecutionContextInterface $context)
    {
        
        $textareaContent = $object->getYourTextareaField();
        $lines = explode("\n", $textareaContent);

        if (count($lines) > 3) {
            $context->buildViolation('Le champ ne peut pas contenir plus de 3 lignes.')
                ->atPath('detailDemande')
                ->addViolation();
        }

        foreach ($lines as $line) {
            if (strlen($line) > 86) {
                $context->buildViolation('Chaque ligne ne peut pas contenir plus de 86 caractères.')
                    ->atPath('detailDemande')
                    ->addViolation();
            }
        }
    }
}