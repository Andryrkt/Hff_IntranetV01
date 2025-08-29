<?php

namespace App\Service\magasin\devis;

use App\Service\validation\ValidationServiceBase;
use Symfony\Component\Form\FormInterface;
use App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService;

class DevisMagasinValidationService extends ValidationServiceBase
{
    private const FILE_FIELD_NAME = 'pieceJoint01';
    private const FILENAME_PATTERN = '/^(DEVIS MAGASIN|CONTROLE DEVIS)_(\d+)_(\d+)_(\d+)\\.pdf$/';

    private HistoriqueOperationDevisMagasinService $historiqueService;
    private string $expectedNumeroDevis;

    public function __construct(HistoriqueOperationDevisMagasinService $historiqueService, string $expectedNumeroDevis)
    {
        $this->historiqueService = $historiqueService;
        $this->expectedNumeroDevis = $expectedNumeroDevis;
    }

    public function checkMissingIdentifier(?string $numeroDevis): bool
    {
        if ($this->isIdentifierMissing($numeroDevis)) {
            $message = "Le numero de devis est obligatoire pour la soumission.";
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false; // Validation failed
        }
        return true; // Validation passed
    }

    public function validateSubmittedFile(FormInterface $form): bool
    {
        // Vérifie si un fichier a été soumis
        if (!$this->isFileSubmitted($form, self::FILE_FIELD_NAME)) {
            $message = "Aucun fichier n'a été soumis.";
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false;
        }

        $file = $form->get(self::FILE_FIELD_NAME)->getData();
        $fileName = $file->getClientOriginalName();

        // Vérifie si le nom du fichier correspond au pattern attendu (S'assurer que c'est bien un devis qui soit soumis)
        if (!$this->matchPattern($fileName, self::FILENAME_PATTERN)) {
            $message = "Le nom du fichier soumis n'est pas conforme au format attendu. Reçu: " . $fileName;
            $this->historiqueService->sendNotificationSoumission($message, '', 'devis_magasin_liste', false);
            return false;
        }

        // Vérifie si le numéro de devis dans le nom du fichier correspond au numéro de devis attendu (S'assurer que le devis envoyé corresponde à la ligne de devis utilisé pour la soumission dans l'intranet)
        if (!$this->matchNumberAfterUnderscore($fileName, $this->expectedNumeroDevis)) {
            $message = "Le numéro de devis dans le nom du fichier ($fileName) ne correspond pas au devis du formulaire ( $this->expectedNumeroDevis )";
            $this->historiqueService->sendNotificationSoumission($message, $this->expectedNumeroDevis, 'devis_magasin_liste', false);
            return false;
        }

        return true;
    }

    public function checkBlockingStatusOnSubmission(
        \App\Repository\Interfaces\StatusRepositoryInterface $repository,
        string $numeroDevis
    ): bool {
        $blockingStatuses = [
            'prix à confirmer',
            'prix validé magasin',
            'prix refusé magasin',
            'Soumis à validation'
        ];

        if ($this->isStatusBlocking($repository, $numeroDevis, $blockingStatuses)) {
            $message = "Le devis ne peut pas être soumis car son statut est bloquant.";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return false; // Validation failed
        }

        return true; // Validation passed
    }

    public function estSommeDeLigneChanger(
        \App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface $repository,
        string $numeroDevis,
        int $newSumOfLines
    ): bool {
        $oldSumOfLines = $repository->findLatestSumOfLinesByIdentifier($numeroDevis);

        if ($oldSumOfLines === null) {
            // No previous version to compare against, so it's not a blocking issue.
            return false;
        }

        if ($this->isSumOfLinesUnchanged($repository, $numeroDevis, $newSumOfLines)) {
            $message = "Le prix a été déjà vérifié ... Veuillez soumettre à validation";
            $this->historiqueService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
            return true; // Is blocking
        }

        return false; // Is not blocking
    }
}
