<?php

namespace App\Service\validation;

use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Repository\Interfaces\LatestSumOfMontantRepositoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\Interfaces\StatusRepositoryInterface;

/**
 * Classe de base abstraite pour les services de validation
 * 
 * Cette classe fournit des méthodes utilitaires communes pour la validation
 * des formulaires, fichiers, statuts et autres données dans l'application.
 */
abstract class ValidationServiceBase
{
    /**
     * Vérifie si un fichier a été soumis dans un champ de formulaire donné
     * 
     * @param FormInterface $form Le formulaire à vérifier
     * @param string $fieldName Le nom du champ de fichier à vérifier
     * @return bool true si un fichier a été soumis, false sinon
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
     * Vérifie si une chaîne de caractères correspond à un pattern regex
     * 
     * @param string|null $subject La chaîne de caractères à tester
     * @param string $pattern Le pattern regex à utiliser pour la correspondance
     * @return bool true si la chaîne correspond au pattern, false sinon
     */
    protected function matchPattern(?string $subject, string $pattern): bool
    {
        if ($subject === null) {
            return false;
        }
        return preg_match($pattern, $subject) === 1;
    }

    /**
     * Extrait un numéro après un underscore (_) dans une chaîne et le compare à une valeur attendue
     * 
     * Cette méthode est utilisée pour valider que le numéro dans un nom de fichier
     * correspond au numéro attendu (ex: "DEVIS_123_456.pdf" avec expectedNumber "123")
     * 
     * @param string $subject La chaîne de caractères contenant le numéro à extraire
     * @param string $expectedNumber Le numéro attendu pour la comparaison
     * @return bool true si le numéro extrait correspond au numéro attendu, false sinon
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
     * Vérifie si le statut le plus récent d'une entité est bloquant
     * 
     * Cette méthode effectue une correspondance exacte entre le statut actuel
     * et la liste des statuts bloquants fournis
     *
     * @param StatusRepositoryInterface $repository Le repository de l'entité à vérifier
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis)
     * @param array $blockingStatuses La liste des statuts considérés comme bloquants
     * @return bool true si le statut est bloquant, false sinon
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

    /**
     * Vérifie si le statut le plus récent d'une entité est bloquant avec recherche partielle
     * 
     * Cette méthode effectue une correspondance partielle (insensible à la casse) entre
     * le statut actuel et les mots-clés bloquants fournis
     *
     * @param StatusRepositoryInterface $repository Le repository de l'entité à vérifier
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis)
     * @param array $blockingStatuses La liste des mots-clés considérés comme bloquants
     * @return bool true si le statut contient un mot-clé bloquant, false sinon
     */
    protected function isStatusBlockingPartial(
        StatusRepositoryInterface $repository,
        string $identifier,
        array $blockingStatuses
    ): bool {
        $currentStatus = $repository->findLatestStatusByIdentifier($identifier);

        if ($currentStatus === null) {
            // Can't be blocking if no status is found.
            return false;
        }

        // Vérifier si le statut actuel contient une partie des mots bloquants
        foreach ($blockingStatuses as $blockingStatus) {
            if (stripos($currentStatus, $blockingStatus) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si le statut le plus récent d'une entité est bloquant avec recherche partielle en commençant par
     * 
     * Cette méthode effectue une correspondance partielle (insensible à la casse) entre
     * le statut actuel et les mots-clés bloquants fournis
     *
     * @param StatusRepositoryInterface $repository Le repository de l'entité à vérifier
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis)
     * @param array $blockingStatuses La liste des mots-clés considérés comme bloquants
     * @return bool true si le statut contient un mot-clé bloquant, false sinon
     */
    protected function isStatusBlockingPartialBeginWith(
        StatusRepositoryInterface $repository,
        string $identifier,
        array $blockingStatuses
    ): bool {
        $currentStatus = $repository->findLatestStatusByIdentifier($identifier);

        if ($currentStatus === null) {
            // Can't be blocking if no status is found.
            return false;
        }

        // Vérifier si le statut actuel contient une partie des mots bloquants
        foreach ($blockingStatuses as $blockingStatus) {
            if (strpos($currentStatus, $blockingStatus) === 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * Vérifie si le nombre de lignes d'une entité est inchangé
     * 
     * Cette méthode compare le nombre de lignes actuel avec le nombre de lignes
     * précédent pour détecter les modifications
     * 
     * @param LatestSumOfLinesRepositoryInterface $repository Le repository pour accéder aux données de lignes
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis)
     * @param int $newSumOfLines Le nouveau nombre de lignes à comparer
     * @return bool true si le nombre de lignes est identique, false sinon
     */
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
     * Vérifie si le montant total d'une entité est inchangé
     * 
     * Cette méthode compare le montant total actuel avec le montant total
     * précédent pour détecter les modifications
     * 
     * @param LatestSumOfMontantRepositoryInterface $repository Le repository pour accéder aux données de montant
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis)
     * @param float $newSumOfMontant Le nouveau montant total à comparer
     * @return bool true si le montant total est identique, false sinon
     */
    protected function isSumOfMontantUnchanged(
        LatestSumOfMontantRepositoryInterface $repository,
        string $identifier,
        float $newSumOfMontant
    ): bool {
        $oldSumOfMontant = $repository->findLatestSumOfMontantByIdentifier($identifier);

        if ($oldSumOfMontant === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return false;
        }

        return $oldSumOfMontant === $newSumOfMontant;
    }


    /**
     * Vérifie si un identifiant est manquant (null)
     * 
     * Cette méthode simple vérifie si l'identifiant fourni est null
     * 
     * @param string|null $identifier L'identifiant à vérifier
     * @return bool true si l'identifiant est manquant (null), false sinon
     */
    protected function isIdentifierMissing(?string $identifier): bool
    {
        return $identifier === null;
    }
}
