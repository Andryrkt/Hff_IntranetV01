<?php

namespace App\Controller\Traits\da;

use DateTime;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeApproLR;
use App\Service\da\EmailDaService;
use App\Controller\Traits\lienGenerique;
use App\Repository\da\DaAfficherRepository;
use App\Service\da\FileUploaderForDAService;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;

trait DaTrait
{
    use lienGenerique;

    /**
     * Récupère les services et repositories nécessaires
     */
    protected function getEmailDaService(): EmailDaService
    {
        return $this->getService('App\Service\da\EmailDaService');
    }

    protected function getDaFileUploader(): FileUploaderForDAService
    {
        return $this->getService('App\Service\da\FileUploaderForDAService');
    }

    protected function getDaAfficherRepository(): DaAfficherRepository
    {
        return $this->getEntityManager()->getRepository(DaAfficher::class);
    }

    protected function getDemandeApproRepository(): DemandeApproRepository
    {
        return $this->getEntityManager()->getRepository(DemandeAppro::class);
    }

    protected function getDemandeApproLRepository(): DemandeApproLRepository
    {
        return $this->getEntityManager()->getRepository(DemandeApproL::class);
    }

    protected function getDemandeApproLRRepository(): DemandeApproLRRepository
    {
        return $this->getEntityManager()->getRepository(DemandeApproLR::class);
    }

    /**
     * Permet de calculer le nombre de jours disponibles avant la date de fin souhaitée
     *
     * @return int Nombre de jours disponibles (positif si la date n'est pas encore passée, négatif si elle l'est)
     */
    public function getJoursRestants($dal): int
    {
        $dateFin = clone $dal->getDateFinSouhaite();
        $dateFin->setTime(0, 0, 0);
        $aujourdhui = new DateTime('today');
        $interval = $aujourdhui->diff($dateFin);
        return $interval->invert ? -$interval->days : $interval->days;
    }

    /** 
     * Fonction pour l'insertion d'une observation
     */
    private function insertionObservation(string $observation, DemandeAppro $demandeAppro): void
    {
        $em = $this->getEntityManager();
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);
        $daObservation = new DaObservation();
        $daObservation
            ->setObservation($text)
            ->setNumDa($demandeAppro->getNumeroDemandeAppro())
            ->setUtilisateur($this->getUser()->getNomUtilisateur());
        $em->persist($daObservation);
        $em->flush();
    }

    /**
     * Récupère les lignes d'une Demande d'Achat en tenant compte des rectifications utilisateur (DALR).
     */
    private function getLignesRectifieesDA(string $numeroDA, int $version): array
    {
        $lignesDAL = $this->getDemandeApproLRepository()->findBy([
            'numeroDemandeAppro' => $numeroDA,
            'numeroVersion' => $version,
            'deleted' => false,
        ]);

        $dalrs = $this->getDemandeApproLRRepository()->findBy([
            'numeroDemandeAppro' => $numeroDA,
        ]);

        $dalrParLigne = [];
        foreach ($dalrs as $dalr) {
            if ($dalr->getChoix()) {
                $dalrParLigne[$dalr->getNumeroLigne()] = $dalr;
            }
        }

        $resultats = [];
        foreach ($lignesDAL as $ligneDAL) {
            $numeroLigne = $ligneDAL->getNumeroLigne();
            $resultats[] = $dalrParLigne[$numeroLigne] ?? $ligneDAL;
        }

        return $resultats;
    }

    /**
     * Ajoute un nombre donné de jours ouvrables (hors samedi et dimanche) à la date actuelle.
     */
    private function ajouterJoursOuvrables(int $nbJoursOuvrables): DateTime
    {
        $date = new DateTime();
        $joursAjoutes = 0;
        while ($joursAjoutes < $nbJoursOuvrables) {
            $date->modify('+1 day');
            if ($date->format('N') < 6) {
                $joursAjoutes++;
            }
        }
        return $date;
    }

    /**
     * Détermine si une DA doit être verrouillée selon son statut et le profil utilisateur
     */
    private function estDaVerrouillee(string $statutDa, bool $estAdmin, bool $estAppro, bool $estAtelier, bool $estEmetteurDaDirect): bool
    {
        $statutDaCliquable = [
            DemandeAppro::STATUT_EN_COURS_CREATION,
            DemandeAppro::STATUT_DW_A_MODIFIER,
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_VALIDE,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
            DemandeAppro::STATUT_SOUMIS_ATE,
        ];
        $reglesDeverouillage = [
            'admin' => fn() => in_array($statutDa, $statutDaCliquable),
            'appro' => fn() => in_array($statutDa, [
                DemandeAppro::STATUT_VALIDE,
                DemandeAppro::STATUT_SOUMIS_ATE,
                DemandeAppro::STATUT_SOUMIS_APPRO,
            ]),
            'atelier' => fn() => in_array($statutDa, [
                DemandeAppro::STATUT_SOUMIS_ATE,
                DemandeAppro::STATUT_EN_COURS_CREATION,
                DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
            ]),
            'service_emetteur_da_direct' => fn() => in_array($statutDa, [
                DemandeAppro::STATUT_SOUMIS_ATE,
                DemandeAppro::STATUT_DW_A_MODIFIER,
            ]),
        ];

        $verrouille = true;
        $profils = [
            'admin' => $estAdmin,
            'appro' => $estAppro,
            'atelier' => $estAtelier,
            'service_emetteur_da_direct' => $estEmetteurDaDirect,
        ];

        foreach ($profils as $profil => $actif) {
            if ($actif && $reglesDeverouillage[$profil]()) {
                $verrouille = false;
                break;
            }
        }

        return $verrouille;
    }

    /**
     * Détecte les lignes supprimées entre deux ensembles de lignes de DA (DaAfficher).
     */
    function getDeletedLineNumbers(iterable $oldDAs, iterable $newDAs): array
    {
        $oldLineNumbers = [];
        $newLineNumbers = [];

        foreach ($oldDAs as $old) {
            $oldLineNumbers[$old->getNumeroLigne()] = true;
        }

        foreach ($newDAs as $new) {
            $newLineNumbers[$new->getNumeroLigne()] = true;
        }

        $deletedLineNumbers = [];
        foreach ($oldLineNumbers as $numeroLigne => $_) {
            if (!isset($newLineNumbers[$numeroLigne])) {
                $deletedLineNumbers[] = $numeroLigne;
            }
        }

        return $deletedLineNumbers;
    }
}
