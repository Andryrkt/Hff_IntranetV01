<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\UserData\UserDataService;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Entity\da\DemandeApproParent;
use App\Service\autres\VersionService;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\da\DemandeApproParentLine;
use App\Repository\da\DaAfficherRepository;

class DaAfficherService
{
    private EntityManagerInterface $em;
    private DaService $daService;
    private UserDataService $userDataService;
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;

    public function __construct(EntityManagerInterface $em, DaService $daService, UserDataService $userDataService)
    {
        $this->em                      = $em;
        $this->daService               = $daService;
        $this->userDataService         = $userDataService;
        $this->daAfficherRepository    = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository  = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = $em->getRepository(DemandeApproL::class);
    }

    /**
     * Ajoute les données d'une Demande d'Achat dans la table `DaAfficher`,
     * par le numéro de la Demande d'Achat.
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->em->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
     *
     * @param string $numDa         le numéro de la Demande d'Achat à traiter
     * @param bool   $validationDA  indique si l'ajout est effectué dans le cadre d'une validation de la DA
     * @param string $statut        le statut depuis DW (statut OR pour une DA avec DIT)
     *
     * @return void
     */
    public function ajouterDansTableAffichageParNumDa(string $numDa, bool $validationDA = false, string $statut = '', $dateDemande = null): void
    {
        /** @var DemandeAppro $demandeAppro la DA correspondant au numero DA $numDa */
        $demandeAppro = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        /** @var iterable<DaAfficher> $oldDaAffichers collection d'objets d'anciens DaAfficher */
        $oldDaAffichers = $this->daAfficherRepository->getLastDaAfficher($numDa);
        $oldDaAffichersByNumero = [];
        foreach ($oldDaAffichers as $old) {
            $oldDaAffichersByNumero[$old->getNumeroLigne()] = $old;
        }

        $numeroVersionMaxDaAfficher = !empty($oldDaAffichers) ? $oldDaAffichers[0]->getNumeroVersion() : 0;
        $numeroVersionMaxDAL = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        /** @var iterable<DaAfficher> $newDaAffichers collection d'objets des nouveaux DaAfficher */
        $newDaAffichers = $this->daService->getLignesRectifiees($numDa, (int) $numeroVersionMaxDAL); // Récupère les lignes rectifiées de la DA (nouveaux Da afficher)

        $deletedLineNumbers = $this->getDeletedLineNumbers($oldDaAffichers, $newDaAffichers);
        $this->daAfficherRepository->markAsDeletedByNumeroLigne($numDa, $deletedLineNumbers, $this->userDataService->getUserName());

        $dateValidation = new \DateTime('now', new \DateTimeZone('Indian/Antananarivo'));

        foreach ($newDaAffichers as $newDaAfficher) {
            $daAfficher = new DaAfficher();
            if (isset($oldDaAffichersByNumero[$newDaAfficher->getNumeroLigne()])) {
                $ancien = $oldDaAffichersByNumero[$newDaAfficher->getNumeroLigne()];
                $daAfficher->copyFromOld($ancien);
            }
            if ($demandeAppro->getDit()) $daAfficher->setDit($demandeAppro->getDit());

            $daAfficher->duplicateDa($demandeAppro);
            $daAfficher->setNumeroVersion(VersionService::autoIncrement($numeroVersionMaxDaAfficher));

            if ($newDaAfficher instanceof DemandeApproL) $daAfficher->duplicateDal($newDaAfficher); // enregistrement pour DAL
            else if ($newDaAfficher instanceof DemandeApproLR) $daAfficher->duplicateDalr($newDaAfficher); // enregistrement pour DALR

            // Gestion caractères spéciaux
            $daAfficher->setArtDesi($this->daService->normalizeTypographicChars($daAfficher->getArtDesi()));

            if ($validationDA) $daAfficher->setDateValidation($dateValidation);  // Si validation DA
            if ($statut)       $daAfficher->setStatutOr($statut);                // Si le statut OR ou DW est défini
            if ($dateDemande)  $daAfficher->setDateDemande($dateDemande);        // Si la date Demande est défini, écraser celui défini dans `$daAfficher->duplicateDa($demandeAppro);`

            $this->em->persist($daAfficher);
        }
        $this->em->flush();
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

    /**
     * Ajoute les données d'une DA
     * dans la table `DaAfficher`, une ligne par DAL.
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->em->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
     *
     * @param DemandeAppro             $demandeAppro  Objet de la demande d'achat à traiter
     * @param bool                     $firstCreation indique si c'est la première création de la DA
     * @param DemandeIntervention|null $dit           Optionnellement, la demande d'intervention associée
     */
    public function generateDaAfficherOnCreationDa(DemandeAppro $demandeAppro, bool $firstCreation, ?DemandeIntervention $dit = null): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $firstCreation ? 0 : $this->daAfficherRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro(), $demandeAppro->getCodeSociete());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        // Parcours chaque ligne DAL de la demande d'achat
        /** @var DemandeApproL $dal */
        foreach ($demandeAppro->getDAL() as $dal) {
            $daAfficher = new DaAfficher();
            if ($dit) $daAfficher->setDit($dit);
            $daAfficher->duplicateDa($demandeAppro);
            $daAfficher->duplicateDal($dal);
            $daAfficher->setDateDemande($demandeAppro->getDateCreation());
            $daAfficher->setNumeroVersion($numeroVersion);

            // Gestion caractères spéciaux
            $daAfficher->setArtDesi($this->daService->normalizeTypographicChars($daAfficher->getArtDesi()));

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
     * @param bool               $firstCreation      indique si c'est la première création de la DA
     */
    public function generateDaAfficherOnCreationDaParent(DemandeApproParent $demandeApproParent, bool $firstCreation): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $firstCreation ? 0 : $this->daAfficherRepository->getNumeroVersionMax($demandeApproParent->getNumeroDemandeAppro(), $demandeApproParent->getCodeSociete());
        $numeroVersion = VersionService::autoIncrement($numeroVersionMax);

        // Parcours chaque ligne DAL de la demande d'achat
        /** @var DemandeApproParentLine $dal */
        foreach ($demandeApproParent->getDemandeApproParentLines() as $demandeApproParentLine) {
            $daAfficher = new DaAfficher();
            $daAfficher->duplicateDaParent($demandeApproParent);
            $daAfficher->duplicateDaParentLine($demandeApproParentLine);
            $daAfficher->setNumeroVersion($numeroVersion);

            // Gestion caractères spéciaux
            $daAfficher->setArtDesi($this->daService->normalizeTypographicChars($daAfficher->getArtDesi()));

            $this->em->persist($daAfficher);
        }
        $this->em->flush();
    }
}
