<?php

namespace App\Service\validation;

use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\Interfaces\StatusRepositoryInterface;

abstract class ValidationServiceBase
{
    /**
     * Vérifie si un fichier a été soumis dans un champ de formulaire donné.
     */
    protected function isFileSubmitted(FormInterface $form, string $fieldName): bool
    {
        if (!$form->has($fieldName)) {
            return false;
        }

        $file = $form->get($fieldName)->getData();

        return $file instanceof UploadedFile;
    }

    /**
     * Vérifie si une chaîne de caractères correspond à un pattern regex.
     */
    protected function matchPattern(?string $subject, string $pattern): bool
    {
        if ($subject === null) {
            return false;
        }
        return preg_match($pattern, $subject) === 1;
    }

    /**
     * Extrait un numéro après un underscore (_) dans une chaîne et le compare à une valeur attendue.
     */
    protected function matchNumberAfterUnderscore(string $subject, string $expectedNumber): bool
    {
        // Trouve la première séquence de chiffres qui suit un underscore
        if (preg_match('/_(\d+)/', $subject, $matches)) {
            // $matches[1] contient les chiffres capturés
            $extractedNumber = $matches[1];
            return $extractedNumber === (string) $expectedNumber;
        }

        return false; // Aucun numéro trouvé après un underscore
    }

    /**
     * Vérifie si le statut le plus récent d'une entité est bloquant.
     *
     * @param StatusRepositoryInterface $repository Le repository de l'entité à vérifier.
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis).
     * @param array $blockingStatuses La liste des statuts considérés comme bloquants.
     * @return boolean True si le statut est bloquant, false sinon.
     */
    protected function isStatusBlocking(
        StatusRepositoryInterface $repository,
        string $identifier,
        array $blockingStatuses
    ): bool {
        $currentStatus = $repository->findLatestStatusByIdentifier($identifier);

        if ($currentStatus === null) {
            // Can't be blocking if no status is found.
            return false;
        }

        return in_array($currentStatus, $blockingStatuses, true);
    }


    protected function isSumOfLinesUnchanged(
        LatestSumOfLinesRepositoryInterface $repository,
        string $identifier,
        int $newSumOfLines
    ): bool {
        $oldSumOfLines = $repository->findLatestSumOfLinesByIdentifier($identifier);

        if ($oldSumOfLines === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return false;
        }

        return $oldSumOfLines === $newSumOfLines;
    }

    /**
     * Vérifie si un identifiant est manquant (null).
     */
    protected function isIdentifierMissing(?string $identifier): bool
    {
        return $identifier === null;
    }
}
