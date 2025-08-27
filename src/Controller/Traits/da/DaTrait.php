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
use App\Controller\Traits\EntityManagerAwareTrait;

trait DaTrait
{
    use lienGenerique;
    use EntityManagerAwareTrait;

    private bool $daTraitInitialise = false;

    //=====================================================================================
    private DaAfficherRepository $daAfficherRepository;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private EmailDaService $emailDaService;
    private FileUploaderForDAService $daFileUploader;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaTrait(): void
    {
        // Si déjà exécuté → on sort immédiatement
        if ($this->daTraitInitialise) return;

        $em = $this->getEntityManager();
        $this->emailDaService = new EmailDaService;
        $this->daFileUploader = new FileUploaderForDAService($_ENV['BASE_PATH_FICHIER']);
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);

        // On note que l'init a été faite
        $this->daTraitInitialise = true;
    }
    //=====================================================================================

    /**
     * Permet de calculer le nombre de jours disponibles avant la date de fin souhaitée
     *
     * @param DemandeApproL $dal
     * @return int Nombre de jours disponibles (positif si la date n'est pas encore passée, négatif si elle l'est)
     */
    public function getJoursRestants(DemandeApproL $dal): int
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
     * @param string $observation l'Observation à insérer
     * @param DemandeAppro $demandeAppro l'objet DemandeAppro auquel l'observation est liée
     * 
     * @return void
     */
    private function insertionObservation(string $observation, DemandeAppro $demandeAppro): void
    {
        $em = $this->getEntityManager();

        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $observation);

        $daObservation = new DaObservation();

        $daObservation
            ->setObservation($text)
            ->setNumDa($demandeAppro->getNumeroDemandeAppro())
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
        ;

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
     * Ajoute un nombre donné de jours ouvrables (hors samedi et dimanche) à la date actuelle.
     *
     * @param int $nbJoursOuvrables Nombre de jours ouvrables à ajouter.
     * @return DateTime La date résultante après ajout des jours ouvrables.
     */
    private function ajouterJoursOuvrables(int $nbJoursOuvrables): DateTime
    {
        $date = new DateTime();
        $joursAjoutes = 0;

        while ($joursAjoutes < $nbJoursOuvrables) {
            $date->modify('+1 day');

            // 'N' renvoie 1 (lundi) à 7 (dimanche)
            if ($date->format('N') < 6) {
                $joursAjoutes++;
            }
        }

        return $date;
    }

    /**
     * Détermine si une DA doit être verrouillée selon son statut et le profil utilisateur
     * 
     * @param string $statutDa
     * @param string|null $statutBc
     * @param bool $estAdmin
     * @param bool $estAppro
     * @param bool $estAtelier
     * 
     * @return bool True si la DA doit être verrouillée, false sinon
     */
    private function estDaVerrouillee(string $statutDa, ?string $statutBc, bool $estAdmin, bool $estAppro, bool $estAtelier): bool
    {
        // Définition des règles de déverrouillage par profil
        $reglesDeverouillage = [
            'admin' => fn() => true,
            'appro' => fn() => in_array($statutDa, [
                DemandeAppro::STATUT_SOUMIS_APPRO,
                DemandeAppro::STATUT_SOUMIS_ATE
            ]) || ($statutDa === DemandeAppro::STATUT_VALIDE && $statutBc === DaSoumissionBc::STATUT_REFUSE),
            'atelier' => fn() => in_array($statutDa, [
                DemandeAppro::STATUT_SOUMIS_ATE,
                DemandeAppro::STATUT_EN_COURS_CREATION,
                DemandeAppro::STATUT_AUTORISER_MODIF_ATE
            ]),
        ];

        // Par défaut, la DA est verrouillée
        $verrouille = true;

        // Vérifie chaque profil : si l'utilisateur correspond et la règle est vraie, déverrouille
        $profils = [
            'admin'   => $estAdmin,
            'appro'   => $estAppro,
            'atelier' => $estAtelier,
        ];

        foreach ($profils as $profil => $actif) {
            if ($actif && $reglesDeverouillage[$profil]()) {
                $verrouille = false;
                break; // dès qu'une règle déverrouille, on s'arrête
            }
        }

        return $verrouille;
    }
}
