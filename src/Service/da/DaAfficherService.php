<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproParent;
use App\Service\autres\VersionService;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\da\DemandeApproParentLine;
use App\Repository\da\DaAfficherRepository;

class DaAfficherService
{
    private EntityManagerInterface $em;
    private DaAfficherRepository $daAfficherRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
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
        $numeroVersionMax = $firstCreation ? 0 : $this->daAfficherRepository->getNumeroVersionMax($demandeAppro->getNumeroDemandeAppro());
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
     * @param bool               $firstCreation      indique si c'est la première création de la DA
     */
    public function generateDaAfficherOnCreationDaParent(DemandeApproParent $demandeApproParent, bool $firstCreation): void
    {
        // Récupère le dernier numéro de version existant pour cette demande d'achat
        $numeroVersionMax = $firstCreation ? 0 : $this->daAfficherRepository->getNumeroVersionMax($demandeApproParent->getNumeroDemandeAppro());
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
