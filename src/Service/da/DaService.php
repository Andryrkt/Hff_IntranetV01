<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Constants\da\StatutDaConstant;
use App\Service\UserData\UserDataService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;

class DaService
{
    private EntityManagerInterface $em;
    private UserDataService $userDataService;
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;

    public function __construct(EntityManagerInterface $em, UserDataService $userDataService)
    {
        $this->em                       = $em;
        $this->userDataService          = $userDataService;
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
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

        $aujourdhui  = new \DateTime('today');            // 'today' crée déjà la date du jour à 00:00:00

        // --- 2. Calculer la différence ---
        $interval = $aujourdhui->diff($dateFin);         // toujours positif dans $interval->days
        $days     = $interval->invert ? -$interval->days // invert = 1 si $dateFin est passée
            :  $interval->days;

        // --- 3. Retourner la valeur ---
        return $days;
    }

    /**
     * Récupère les lignes d'une Demande d'Achat en tenant compte des rectifications utilisateur (DALR).
     * Optimisé pour éviter les requêtes en boucle (N+1).
     *
     * @param string $numeroDA le numéro de la Demande d'Achat
     * @param int    $version  la version de la Demande d'Achat
     *
     * @return array
     */
    public function getLignesRectifiees(string $numeroDA, int $version): array
    {
        // 1. Récupération des lignes DAL (non supprimées)
        /** @var iterable<DemandeApproL> $lignesDAL les lignes de DAL non supprimées */
        $lignesDAL = $this->demandeApproLRepository->findBy([
            'numeroDemandeAppro' => $numeroDA,
            'numeroVersion'      => $version,
            'deleted'            => false,
        ]);

        // 2. Récupération en une seule requête des DALR associés à la DA
        /** @var iterable<DemandeApproLR> $dalrs les lignes de DALR correspondant au numéro de la DA */
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

    /** Applique le statut « demande de devis en cours » sur la DA */
    public function appliquerStatutDemandeDevisEnCours(DemandeAppro $demandeAppro): void
    {
        $demandeAppro
            ->setDevisDemande(true)
            ->setDateDemandeDevis(new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))
            ->setDevisDemandePar($this->userDataService->getUserName())
        ;

        $this->appliquerChangementStatut($demandeAppro, StatutDaConstant::STATUT_DEMANDE_DEVIS);
    }

    /**
     * Normalise les caractères typographiques
     *
     * @param  string $var Texte à normaliser
     * @return string Texte normalisé
     */
    public function normalizeTypographicChars(?string $var): string
    {
        if (empty($var)) return '';

        $map = [
            "\u{2013}" => '-',   // – demi-cadratin
            "\u{2014}" => '-',   // — cadratin
            "\u{2018}" => "'",   // ' apostrophe ouvrante
            "\u{2019}" => "'",   // ' apostrophe fermante
            "\u{201C}" => '"',   // " guillemet ouvrant
            "\u{201D}" => '"',   // " guillemet fermant
            "\u{2026}" => '...', // … points de suspension
        ];

        return strtr($var, $map);
    }

    /** Récupère une demande appro par son id */
    public function getDemandeAppro(int $id): ?DemandeAppro
    {
        return $this->demandeApproRepository->find($id);
    }
}
