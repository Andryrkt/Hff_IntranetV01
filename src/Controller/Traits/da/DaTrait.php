<?php

namespace App\Controller\Traits\da;

use DateTime;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\da\EmailDaService;
use App\Controller\Traits\lienGenerique;
use App\Repository\da\DaAfficherRepository;
use App\Service\da\FileUploaderForDAService;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Service\da\PermissionDaService;

trait DaTrait
{
    use lienGenerique;

    private bool $daTraitInitialise = false;

    //=====================================================================================
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private EmailDaService $emailDaService;
    private PermissionDaService $permissionDaService;
    private FileUploaderForDAService $daFileUploader;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaTrait(): void
    {
        // Si déjà exécuté → on sort immédiatement
        if ($this->daTraitInitialise) return;

        $em = $this->getEntityManager();
        $this->emailDaService           = new EmailDaService($this->getTwig()); // Injection du service Twig depuis Controller
        $this->permissionDaService      = new PermissionDaService;
        $this->daFileUploader           = new FileUploaderForDAService();
        $this->daAfficherRepository     = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);

        // On note que l'init a été faite
        $this->daTraitInitialise = true;
    }
    //=====================================================================================

    /**
     * Permet de calculer le nombre de jours disponibles avant la date de fin souhaitée
     *
     * @return int Nombre de jours disponibles (positif si la date n'est pas encore passée, négatif si elle l'est)
     */
    public function getJoursRestants($dal): int
    {
        // --- 1. Mettre les deux dates à minuit (00:00:00) ---
        $dateFin     = clone $dal->getDateFinSouhaite(); // on clone pour ne pas modifier l'objet de l'entity
        $dateFin->setTime(0, 0, 0);                      // Y-m-d 00:00:00

        $aujourdhui  = new DateTime('today');            // 'today' crée déjà la date du jour à 00:00:00

        // --- 2. Calculer la différence ---
        $interval = $aujourdhui->diff($dateFin);         // toujours positif dans $interval->days
        $days     = $interval->invert ? -$interval->days // invert = 1 si $dateFin est passée
            :  $interval->days;

        // --- 3. Retourner la valeur ---
        return $days;
    }

    /** 
     * Fonction pour l'insertion d'une observation
     * 
     * @param string         $numDa       le numéro de la DA
     * @param string         $observation l'Observation à insérer
     * @param UploadedFile[] $files       les fichiers à uploader
     * 
     * @return void
     */
    private function insertionObservation(string $numDa, string $observation, ?array $files = null): void
    {
        $em = $this->getEntityManager();

        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);

        $daObservation = new DaObservation();

        $daObservation
            ->setObservation($text)
            ->setNumDa($numDa)
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
        ;

        if ($files) {
            $fileNames = $this->daFileUploader->uploadMultipleDaFiles($files, $numDa, FileUploaderForDAService::FILE_TYPE["OBSERVATION"]);
            $daObservation->setFileNames($fileNames);
        }

        $em->persist($daObservation);
        $em->flush();
    }

    /**
     * Récupère les lignes d'une Demande d'Achat en tenant compte des rectifications utilisateur (DALR).
     * Optimisé pour éviter les requêtes en boucle (N+1).
     *
     * @param string $numeroDA le numéro de la Demande d'Achat
     * @param int    $version la version de la Demande d'Achat
     *
     * @return array
     */
    private function getLignesRectifieesDA(string $numeroDA, int $version): array
    {
        // 1. Récupération des lignes DAL (non supprimées)
        /** @var iterable<DemandeApproL> les lignes de DAL non supprimées */
        $lignesDAL = $this->demandeApproLRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
            'numeroVersion'      => $version,
            'deleted'            => false,
        ]);

        // 2. Récupération en une seule requête des DALR associés à la DA
        /** @var iterable<DemandeApproLR> les lignes de DALR correspondant au numéro de la DA */
        $dalrs = $this->demandeApproLRRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
        ]);

        // 3. Indexation des DALR par numéro de ligne, uniquement s'ils sont validés (choix = true)
        $dalrParLigne = [];

        foreach ($dalrs as $dalr) {
            if ($dalr->getChoix()) {
                $dalrParLigne[$dalr->getNumeroLigne()] = $dalr;
            }
        }

        // 4. Construction de la liste finale en remplaçant les DAL par DALR si dispo
        $resultats = [];

        foreach ($lignesDAL as $ligneDAL) {
            $numeroLigne = $ligneDAL->getNumeroLigne(); // numéro de ligne de la DAL
            $resultats[] = $dalrParLigne[$numeroLigne] ?? $ligneDAL;
        }

        return $resultats;
    }

    /**
     * Détecte les lignes supprimées entre deux ensembles de lignes de DA (DaAfficher).
     *
     * Une ligne est considérée comme supprimée si son numéro de ligne existe dans
     * l'ancien jeu de données (`$oldDAs`) mais pas dans le nouveau (`$newDAs`).
     *
     * @param iterable<DaAfficher> $oldDAs Les anciennes lignes de la DA (stockées en base)
     * @param iterable<DaAfficher> $newDAs Les nouvelles lignes de la DA (venant de l'utilisateur ou d'un formulaire)
     *
     * @return string[] Tableau des numéros de ligne à marquer comme supprimés
     */
    public function getDeletedLineNumbers(iterable $oldDAs, iterable $newDAs): array
    {
        if (empty($oldDAs)) return [];

        $oldLineNumbers = [];
        $newLineNumbers = [];

        // Indexer les anciens numéros de ligne
        foreach ($oldDAs as $old) {
            $oldLineNumbers[$old->getNumeroLigne()] = true;
        }

        // Indexer les nouveaux numéros de ligne
        foreach ($newDAs as $new) {
            $newLineNumbers[$new->getNumeroLigne()] = true;
        }

        // Détecter les numéros présents dans l'ancien mais absents dans le nouveau
        $deletedLineNumbers = [];
        foreach ($oldLineNumbers as $numeroLigne => $_) {
            if (!isset($newLineNumbers[$numeroLigne])) $deletedLineNumbers[] = $numeroLigne;
        }

        return $deletedLineNumbers;
    }

    public function appliquerChangementStatut(DemandeAppro $demandeAppro, string $statut, bool $withFlush = true)
    {
        $em = $this->getEntityManager();

        $demandeAppro->setStatutDal($statut);

        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal($statut);
            /** @var DemandeApproLR $demandeApproLR */
            foreach ($demandeApproL->getDemandeApproLR() as $demandeApproLR) {
                $demandeApproLR->setStatutDal($statut);
                $em->persist($demandeApproLR);
            }
            $em->persist($demandeApproL);
        }

        $em->persist($demandeAppro);

        if ($withFlush) $em->flush();
    }
}
