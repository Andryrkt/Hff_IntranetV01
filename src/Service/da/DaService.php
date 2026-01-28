<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Entity\da\DemandeApproParent;
use App\Service\autres\VersionService;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\da\DemandeApproParentLine;
use App\Repository\da\DaAfficherRepository;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use DateTime;

class DaService
{
    private EntityManagerInterface $em;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DaAfficherRepository $daAfficherRepository;
    private DaObservationRepository $daObservationRepository;
    private FileUploaderForDAService $daFileUploader;

    public function __construct(EntityManagerInterface $em, FileUploaderForDAService $daFileUploader)
    {
        $this->em                       = $em;
        $this->daAfficherRepository     = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
        $this->daObservationRepository  = $em->getRepository(DaObservation::class);
        $this->daFileUploader           = $daFileUploader;
    }

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
     * @param string         $username    le nom de l'utilisateur
     * @param UploadedFile[] $files       les fichiers à uploader
     * 
     * @return void
     */
    public function insertionObservation(string $numDa, string $observation, string $username, ?array $files = null): void
    {
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);

        $daObservation = new DaObservation();

        $daObservation
            ->setObservation($text)
            ->setNumDa($numDa)
            ->setUtilisateur($username)
        ;

        if ($files) {
            $fileNames = $this->daFileUploader->uploadMultipleDaFiles($files, $numDa, FileUploaderForDAService::FILE_TYPE["OBSERVATION"]);
            $daObservation->setFileNames($fileNames);
        }

        $this->em->persist($daObservation);
        $this->em->flush();
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
    public function getLignesRectifieesDA(string $numeroDA, int $version): array
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
        $demandeAppro->setStatutDal($statut);

        /** @var DemandeApproL $demandeApproL */
        foreach ($demandeAppro->getDAL() as $demandeApproL) {
            $demandeApproL->setStatutDal($statut);
            /** @var DemandeApproLR $demandeApproLR */
            foreach ($demandeApproL->getDemandeApproLR() as $demandeApproLR) {
                $demandeApproLR->setStatutDal($statut);
                $this->em->persist($demandeApproLR);
            }
            $this->em->persist($demandeApproL);
        }

        $this->em->persist($demandeAppro);

        if ($withFlush) $this->em->flush();
    }

    /**
     * Ajoute les données d'une DA
     * dans la table `DaAfficher`, une ligne par DAL.
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->em->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
     *
     * @param DemandeAppro             $demandeAppro  Objet de la demande d'achat à traiter
     * @param DemandeIntervention|null $dit           Optionnellement, la demande d'intervention associée
     */
    public function generateDaAfficherOnCreationDa(DemandeAppro $demandeAppro, ?DemandeIntervention $dit = null): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daAfficherRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        // Parcours chaque ligne DAL de la demande d'achat
        /** @var DemandeApproL $dal */
        foreach ($demandeAppro->getDAL() as $dal) {
            $daAfficher = new DaAfficher();
            if ($dit) $daAfficher->setDit($dit);
            $daAfficher->duplicateDa($demandeAppro);
            $daAfficher->duplicateDal($dal);
            $daAfficher->setNumeroVersion($numeroVersion);

            $this->em->persist($daAfficher);
        }
        $this->em->flush();
    }

    /**
     * Ajoute les données d'une DA Parent dans la table `DaAfficher`, une ligne par DAL.
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->getEntityManager()->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
     *
     * @param DemandeApproParent $demandeApproParent  Objet de la demande d'achat à traiter
     */
    public function generateDaAfficherOnCreationDaParent(DemandeApproParent $demandeApproParent): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $this->daAfficherRepository->getNumeroVersionMax($demandeApproParent->getNumeroDemandeAppro());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        // Parcours chaque ligne DAL de la demande d'achat
        /** @var DemandeApproParentLine $dal */
        foreach ($demandeApproParent->getDemandeApproParentLines() as $demandeApproParentLine) {
            $daAfficher = new DaAfficher();
            $daAfficher->duplicateDaParent($demandeApproParent);
            $daAfficher->duplicateDaParentLine($demandeApproParentLine);
            $daAfficher->setNumeroVersion($numeroVersion);

            $this->em->persist($daAfficher);
        }
        $this->em->flush();
    }
}
