<?php

namespace App\Service\dom;

use App\Entity\dom\Dom;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service de logique métier pour les DOM (Dossiers de Mission)
 */
class DomBusinessLogicService
{
    private EntityManagerInterface $entityManager;
    private DomValidationService $validationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        DomValidationService $validationService
    ) {
        $this->entityManager = $entityManager;
        $this->validationService = $validationService;
    }

    /**
     * Traite la création d'un DOM
     */
    public function processDomCreation(Dom $dom, User $user): ProcessResult
    {
        try {
            // Génération du numéro DOM
            $dom->setNumeroOrdreMission($this->generateDomNumber());
            $dom->setDateDemande(new \DateTime());
            $dom->setNomSessionUtilisateur($user->getNomUtilisateur() ?? '');

            // Calcul des indemnités
            $this->calculateIndemnities($dom);

            // Validation
            $validationResult = $this->validationService->validateDom($dom);
            if (!$validationResult->isValid()) {
                return new ProcessResult(false, $validationResult->getErrorMessages());
            }

            // Sauvegarde
            $this->entityManager->persist($dom);
            $this->entityManager->flush();

            return new ProcessResult(true, ['DOM créé avec succès'], $dom);
        } catch (\Exception $e) {
            return new ProcessResult(false, ['Erreur lors de la création : ' . $e->getMessage()]);
        }
    }

    /**
     * Calcule les indemnités selon les critères
     */
    public function calculateIndemnities(Dom $dom): array
    {
        $calculations = [];

        if (!$dom->getCategorie() || !$dom->getSite() || !$dom->getSousTypeDocument() || !$dom->getAgenceEmetteur()) {
            return $calculations;
        }

        // Récupération des indemnités depuis la base
        $indemnite = $this->entityManager->getRepository(Indemnite::class)
            ->findOneBy([
                'type' => $dom->getSousTypeDocument()->getCodeSousType(),
                'catg' => $dom->getCategorie()->getCodeCatg(),
                'destination' => $dom->getSite()->getCodeSite(),
                'rmq' => $dom->getAgence()->getCodeAgence()
            ]);

        if ($indemnite) {
            $montantBase = $indemnite->getMontantIdemnite();
            $nombreJours = $dom->getNombreJour() ?? 1;

            // Calcul indemnité forfaitaire
            $totalForfaitaire = $montantBase * $nombreJours;
            $dom->setIndemniteForfaitaire($montantBase);
            $dom->setTotalIndemniteForfaitaire($totalForfaitaire);

            $calculations['indemnite_forfaitaire'] = $montantBase;
            $calculations['total_forfaitaire'] = $totalForfaitaire;
            $calculations['nombre_jours'] = $nombreJours;
        }

        // Calcul du total général
        $this->calculateTotalGeneral($dom);

        return $calculations;
    }

    /**
     * Calcule le total général à payer
     */
    public function calculateTotalGeneral(Dom $dom): float
    {
        $total = 0;

        // Indemnité de déplacement
        $totalIndemniteDeplacement = $dom->getTotalDeplPlusAutres();
        if ($totalIndemniteDeplacement) {
            $total += (float) str_replace('.', '', $totalIndemniteDeplacement);
        }

        // Indemnité forfaitaire
        if ($dom->getTotalIndemniteForfaitaire()) {
            $total += (float) str_replace('.', '', $dom->getTotalIndemniteForfaitaire());
        }

        // Autres dépenses
        if ($dom->getTotalAutresDepenses()) {
            $total += (float) str_replace('.', '', $dom->getTotalAutresDepenses());
        }

        $dom->setTotalGeneralPayer(number_format($total, 0, ',', '.'));

        return $total;
    }

    /**
     * Génère un numéro DOM unique
     */
    public function generateDomNumber(): string
    {
        $year = date('y');
        $month = date('m');

        // Récupération du dernier numéro du mois
        $lastDom = $this->entityManager->getRepository(Dom::class)
            ->createQueryBuilder('d')
            ->where('d.numeroOrdreMission LIKE :pattern')
            ->setParameter('pattern', 'DOM' . $year . $month . '%')
            ->orderBy('d.numeroOrdreMission', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($lastDom) {
            $lastNumber = (int) substr($lastDom->getNumeroOrdreMission(), -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'DOM' . $year . $month . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Duplique un DOM existant
     */
    public function duplicateDom(Dom $originalDom, User $user): Dom
    {
        $newDom = clone $originalDom;

        // Réinitialisation des champs spécifiques
        $newDom->setId(null);
        $newDom->setNumeroOrdreMission($this->generateDomNumber());
        $newDom->setDateDemande(new \DateTime());
        $newDom->setNomSessionUtilisateur($user->getNomUtilisateur() ?? '');
        $newDom->setIdStatutDemande(null); // Nouveau statut

        return $newDom;
    }

    /**
     * Traite un DOM "trop perçu"
     */
    public function processTropPercu(Dom $originalDom, Dom $tropPercuDom, User $user): ProcessResult
    {
        try {
            // Validation que le DOM original peut avoir un trop perçu
            if (!$this->canCreateTropPercu($originalDom)) {
                return new ProcessResult(false, ['Ce DOM ne peut pas avoir de trop perçu']);
            }

            // Configuration du DOM trop perçu
            $tropPercuDom->setNumeroOrdreMission($this->generateDomNumber());
            $tropPercuDom->setDateDemande(new \DateTime());
            $tropPercuDom->setNomSessionUtilisateur($user->getNomUtilisateur() ?? '');
            $tropPercuDom->setSousTypeDocument(
                $this->entityManager->getRepository(\App\Entity\admin\dom\SousTypeDocument::class)
                    ->findOneBy(['codeSousType' => 'TROP PERCU'])
            );

            // Validation
            $validationResult = $this->validationService->validateDom($tropPercuDom);
            if (!$validationResult->isValid()) {
                return new ProcessResult(false, $validationResult->getErrorMessages());
            }

            // Sauvegarde
            $this->entityManager->persist($tropPercuDom);
            $this->entityManager->flush();

            return new ProcessResult(true, ['DOM trop perçu créé avec succès'], $tropPercuDom);
        } catch (\Exception $e) {
            return new ProcessResult(false, ['Erreur lors de la création du trop perçu : ' . $e->getMessage()]);
        }
    }

    /**
     * Vérifie si un DOM peut avoir un trop perçu
     */
    public function canCreateTropPercu(Dom $dom): bool
    {
        // Vérifications métier pour le trop perçu
        if (!$dom->getStatutTropPercuOk()) {
            return false;
        }

        // Vérifier qu'il n'y a pas déjà un trop perçu
        $existingTropPercu = $this->entityManager->getRepository(Dom::class)
            ->createQueryBuilder('d')
            ->where('d.matricule = :matricule')
            ->andWhere('d.sousTypeDocument = :type')
            ->andWhere('d.dateDebut = :dateDebut')
            ->andWhere('d.dateFin = :dateFin')
            ->setParameter('matricule', $dom->getMatricule())
            ->setParameter('type', $this->entityManager->getRepository(\App\Entity\admin\dom\SousTypeDocument::class)
                ->findOneBy(['codeSousType' => 'TROP PERCU']))
            ->setParameter('dateDebut', $dom->getDateDebut())
            ->setParameter('dateFin', $dom->getDateFin())
            ->getQuery()
            ->getOneOrNullResult();

        return $existingTropPercu === null;
    }

    /**
     * Traite l'upload de fichiers
     */
    public function processFileUpload(Dom $dom, array $uploadedFiles): array
    {
        $uploadedPaths = [];
        $uploadDir = 'uploads/dom/' . $dom->getNumeroOrdreMission() . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($uploadedFiles as $index => $file) {
            if ($file instanceof UploadedFile) {
                $filename = 'piece_' . ($index + 1) . '.' . $file->guessExtension();
                $file->move($uploadDir, $filename);
                $uploadedPaths[] = $uploadDir . $filename;
            }
        }

        return $uploadedPaths;
    }

    /**
     * Récupère les statistiques DOM
     */
    public function getDomStatistics(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $qb = $this->entityManager->getRepository(Dom::class)
            ->createQueryBuilder('d')
            ->select('
                COUNT(d.id) as total,
                COUNT(CASE WHEN d.sousTypeDocument = :mission THEN 1 END) as missions,
                COUNT(CASE WHEN d.sousTypeDocument = :complement THEN 1 END) as complements,
                COUNT(CASE WHEN d.sousTypeDocument = :formation THEN 1 END) as formations,
                AVG(d.nombreJour) as moyenne_jours,
                SUM(CAST(REPLACE(d.totalGeneralPayer, \'.\', \'\') AS DECIMAL)) as total_montant
            ')
            ->where('d.dateDemande BETWEEN :dateDebut AND :dateFin')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin);

        // Récupération des types de documents
        $missionType = $this->entityManager->getRepository(\App\Entity\admin\dom\SousTypeDocument::class)
            ->findOneBy(['codeSousType' => 'MISSION']);
        $complementType = $this->entityManager->getRepository(\App\Entity\admin\dom\SousTypeDocument::class)
            ->findOneBy(['codeSousType' => 'COMPLEMENT']);
        $formationType = $this->entityManager->getRepository(\App\Entity\admin\dom\SousTypeDocument::class)
            ->findOneBy(['codeSousType' => 'FORMATION']);

        $qb->setParameter('mission', $missionType)
            ->setParameter('complement', $complementType)
            ->setParameter('formation', $formationType);

        return $qb->getQuery()->getSingleResult();
    }
}

/**
 * Classe pour encapsuler le résultat de traitement
 */
class ProcessResult
{
    private bool $success;
    private array $messages;
    private ?Dom $dom;

    public function __construct(bool $success, array $messages = [], ?Dom $dom = null)
    {
        $this->success = $success;
        $this->messages = $messages;
        $this->dom = $dom;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getDom(): ?Dom
    {
        return $this->dom;
    }

    public function getFirstMessage(): string
    {
        return $this->messages[0] ?? '';
    }
}
