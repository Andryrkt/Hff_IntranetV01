<?php

namespace App\Service\dom;

use App\Entity\dom\Dom;
use App\Entity\admin\utilisateur\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Service de validation pour les DOM (Dossiers de Mission)
 */
class DomValidationService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * Valide un DOM complet
     */
    public function validateDom(Dom $dom): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Validation des contraintes Symfony
        $violations = $this->validator->validate($dom);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                    'code' => $violation->getCode()
                ];
            }
        }

        // Validation métier spécifique
        $businessErrors = $this->validateBusinessRules($dom);
        $errors = array_merge($errors, $businessErrors);

        // Vérifications de cohérence
        $consistencyWarnings = $this->validateConsistency($dom);
        $warnings = array_merge($warnings, $consistencyWarnings);

        return new ValidationResult(
            empty($errors),
            $errors,
            $warnings
        );
    }

    /**
     * Vérifie les chevauchements de dates pour un matricule
     */
    public function checkDateOverlap(string $matricule, \DateTime $dateDebut, \DateTime $dateFin, ?int $excludeDomId = null): bool
    {
        $qb = $this->entityManager->getRepository(Dom::class)
            ->createQueryBuilder('d')
            ->where('d.matricule = :matricule')
            ->andWhere('d.idStatutDemande NOT IN (:excludedStatuses)')
            ->andWhere('(d.dateDebut <= :dateFin AND d.dateFin >= :dateDebut)')
            ->setParameter('matricule', $matricule)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('excludedStatuses', [9, 33, 34, 35, 44]); // Statuts annulés/rejetés

        if ($excludeDomId) {
            $qb->andWhere('d.id != :excludeId')
                ->setParameter('excludeId', $excludeDomId);
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    /**
     * Valide le montant selon le mode de paiement
     */
    public function validateAmount(string $modePaiement, float $montant): bool
    {
        $mode = explode(':', $modePaiement)[0] ?? $modePaiement;

        // Limite pour Mobile Money
        if ($mode === 'MOBILE MONEY' && $montant > 500000) {
            return false;
        }

        return $montant > 0;
    }

    /**
     * Valide un matricule
     */
    public function validateMatricule(string $matricule): array
    {
        $result = [
            'valid' => false,
            'type' => null,
            'message' => ''
        ];

        if (empty($matricule)) {
            $result['message'] = 'Le matricule est requis';
            return $result;
        }

        // Matricule permanent (4 chiffres)
        if (strlen($matricule) === 4 && ctype_digit($matricule)) {
            $result['valid'] = true;
            $result['type'] = 'PERMANENT';
            $result['message'] = 'Matricule permanent valide';
        }
        // Matricule temporaire (format différent)
        elseif (strlen($matricule) > 4) {
            $result['valid'] = true;
            $result['type'] = 'TEMPORAIRE';
            $result['message'] = 'Matricule temporaire valide';
        } else {
            $result['message'] = 'Format de matricule invalide';
        }

        return $result;
    }

    /**
     * Validation des règles métier
     */
    private function validateBusinessRules(Dom $dom): array
    {
        $errors = [];

        // Vérification des dates
        if ($dom->getDateDebut() && $dom->getDateFin()) {
            if ($dom->getDateDebut() > $dom->getDateFin()) {
                $errors[] = [
                    'field' => 'dateFin',
                    'message' => 'La date de fin doit être postérieure à la date de début',
                    'code' => 'INVALID_DATE_RANGE'
                ];
            }

            // Vérification du chevauchement pour les employés permanents
            if ($dom->getMatricule() && $dom->getSalarier() === 'PERMANENT') {
                if ($this->checkDateOverlap($dom->getMatricule(), $dom->getDateDebut(), $dom->getDateFin())) {
                    $errors[] = [
                        'field' => 'dateDebut',
                        'message' => 'Une mission existe déjà pour ce matricule sur ces dates',
                        'code' => 'DATE_OVERLAP'
                    ];
                }
            }
        }

        // Vérification du montant
        if ($dom->getTotalGeneralPayer()) {
            $montant = (float) str_replace('.', '', $dom->getTotalGeneralPayer());
            if (!$this->validateAmount($dom->getModePayement(), $montant)) {
                $errors[] = [
                    'field' => 'totalGeneralPayer',
                    'message' => 'Le montant dépasse la limite autorisée pour ce mode de paiement',
                    'code' => 'AMOUNT_LIMIT_EXCEEDED'
                ];
            }
        }

        return $errors;
    }

    /**
     * Validation de cohérence (warnings)
     */
    private function validateConsistency(Dom $dom): array
    {
        $warnings = [];

        // Vérification de la cohérence des indemnités
        if ($dom->getNombreJour() && $dom->getNombreJour() > 30) {
            $warnings[] = [
                'field' => 'nombreJour',
                'message' => 'Mission de plus de 30 jours - vérifiez la cohérence',
                'code' => 'LONG_MISSION'
            ];
        }

        // Vérification des pièces justificatives pour les compléments
        if (
            $dom->getSousTypeDocument() &&
            $dom->getSousTypeDocument()->getCodeSousType() === 'COMPLEMENT' &&
            !$dom->getPieceJustificatif()
        ) {
            $warnings[] = [
                'field' => 'pieceJustificatif',
                'message' => 'Pièce justificative recommandée pour un complément',
                'code' => 'MISSING_JUSTIFICATION'
            ];
        }

        return $warnings;
    }
}

/**
 * Classe pour encapsuler le résultat de validation
 */
class ValidationResult
{
    private bool $isValid;
    private array $errors;
    private array $warnings;

    public function __construct(bool $isValid, array $errors = [], array $warnings = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function getFirstError(): ?array
    {
        return $this->errors[0] ?? null;
    }

    public function getErrorMessages(): array
    {
        return array_column($this->errors, 'message');
    }

    public function getWarningMessages(): array
    {
        return array_column($this->warnings, 'message');
    }
}
