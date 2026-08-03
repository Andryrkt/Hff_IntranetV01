<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Traits\JoursOuvrablesTrait;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Constants\da\StatutBcConstant;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DaAfficherRepository;

class DaTimelineService
{
    use JoursOuvrablesTrait;
    private DaAfficherRepository $daAfficherRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
    }

    /** 
     * @param string $numeroDa
     * @param bool   $isDaViaOR
     * 
     * @return array<string,array<int|string,array{statut:string,dotClass:string,date:string,nbrJours:string}>>
     */
    public function getTimelineData(string $numeroDa, bool $isDaViaOR = false): array
    {
        $allDatas = $this->daAfficherRepository->getTimelineData($numeroDa);
        if (empty($allDatas)) return ['DA' => [], 'OR' => [], 'BC' => []];

        $timelineDa = $this->buildTimelineDA($allDatas);
        $lastDataDA = end($timelineDa);
        [$numeroOr, $timelineOR] = $isDaViaOR ? $this->buildTimelineOR($allDatas, $lastDataDA) : ["", []];
        $lastDataOR = empty($timelineOR) ? $lastDataDA : end($timelineOR);
        $timelineBc = $this->buildTimelineBC($numeroDa, $lastDataOR);

        if (empty($timelineBc)) {
            if ($isDaViaOR && !empty($timelineOR)) $timelineOR = $this->cloturerAvecAujourdhui($timelineOR);
            else                                   $timelineDa = $this->cloturerAvecAujourdhui($timelineDa);
        }

        return [
            'numeroOr' => $numeroOr,
            'DA'       => $timelineDa,
            'OR'       => $timelineOR,
            'BC'       => $timelineBc,
        ];
    }

    /** 
     * @param array<int,array{statutDal:string,dateCreation:\DateTime,dateDemande:\DateTime,numeroOr:string|null,statutOr:string|null,dateMajStatutOr:\DateTime|null}> $allDatas
     * 
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function buildTimelineDA(array $allDatas): array
    {
        $tabTemp = [];

        $statuts      = array_column($allDatas, 'statutDal');
        $skipCloturee = in_array(StatutDaConstant::STATUT_VALIDE, $statuts, true) && in_array(StatutDaConstant::STATUT_CLOTUREE, $statuts, true);

        foreach ($allDatas as $key => $data) {
            // Sauter les entrées CLOTUREE si VALIDE est aussi présent
            if ($skipCloturee && $data['statutDal'] === StatutDaConstant::STATUT_CLOTUREE) continue;

            // Ajouter le statut initial si nécessaire
            if ($key === 0 && $data['statutDal'] !== StatutDaConstant::STATUT_SOUMIS_APPRO) {
                $tabTemp[] = $this->createTimelineEntry(StatutDaConstant::STATUT_SOUMIS_APPRO, $data['dateDemande']);
            }

            // Déterminer le statut final
            $statutFinal = $this->getStatutFinal($data['statutDal'], $data['statutOr']);

            // Ajouter ou mettre à jour le statut
            $lastIndex = count($tabTemp) - 1;
            if ($lastIndex < 0 || $tabTemp[$lastIndex]['statut'] !== $statutFinal) {
                $tabTemp[] = $this->createTimelineEntry($statutFinal, $data['dateDemande'], $data['dateCreation']);
            } else {
                // Mettre à jour avec la date la plus récente
                $tabTemp[$lastIndex]['date'] = $data['dateCreation'];
            }
        }

        return $this->construireEtapesAvecDurees($tabTemp, true);
    }

    /** 
     * @param array<int,array{statutDal:string,dateCreation:\DateTime,dateDemande:\DateTime,numeroOr:string|null,statutOr:string|null,dateMajStatutOr:\DateTime|null}> $allDatas
     * @param array{statut:string,dotClass:string,date:string,nbrJours:string} $lastDataDA
     * 
     * @return array{string,array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>}
     */
    private function buildTimelineOR(array $allDatas, array $lastDataDA): array
    {
        $tabTemp = [];
        $nbrJours = "";
        $dateValidationDA = \DateTime::createFromFormat('d/m/Y', $lastDataDA['date']);

        foreach ($allDatas as $data) {
            $numOr        = $data['numeroOr'];
            $statutOr     = $data['statutOr'];
            $dateStatutOr = $data['dateMajStatutOr'];

            if ($numOr !== null && $statutOr !== null && $dateStatutOr !== null) {
                $tabTemp = [
                    'numeroOr' => $numOr,
                    'statut'   => "OR - " . $statutOr,
                    'dotClass' => StatutOrConstant::getCssClassOr("OR - " . $statutOr),
                    'date'     => $dateStatutOr->format('d/m/Y')
                ];

                $nbrJours = $this->formatDuration($this->differenceJoursOuvrables($dateValidationDA, $dateStatutOr));

                break;
            }
        }

        if (empty($tabTemp)) return ["", []];

        return [
            $tabTemp["numeroOr"],
            [
                [
                    "statut"   => $lastDataDA["statut"],
                    "dotClass" => $lastDataDA["dotClass"],
                    "date"     => $lastDataDA['date'],
                    "nbrJours" => $nbrJours,
                ],
                [
                    "statut"   => $tabTemp["statut"],
                    "dotClass" => $tabTemp["dotClass"],
                    "date"     => $tabTemp["date"],
                    "nbrJours" => "",
                ]
            ]
        ];
    }

    /**
     * @param string $numeroDa
     * @param array{statut:string,dotClass:string,date:string,nbrJours:string} $pointDepart Dernier jalon connu avant le BC (DA ou OR selon le contexte)
     *
     * @return array<string,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function buildTimelineBC(string $numeroDa, array $pointDepart): array
    {
        $allDatas = $this->daAfficherRepository->getAllNumCdeAndVmax($numeroDa);
        $tabTemp = [];
        $dateValidationDA = \DateTime::createFromFormat('d/m/Y', $pointDepart['date']);

        foreach ($allDatas as $data) {
            $numBC = $data['numeroCde'];
            $numeroVersion = $data['numeroVersion'];

            // Récupération de toutes les dates possibles
            $dateCreationBc       = $this->daAfficherRepository->getDateCreationBc($numeroDa, $numeroVersion, $numBC);       // Génération BC
            $dateValidation       = $this->daAfficherRepository->getDateValidationBc($numeroDa, $numeroVersion, $numBC);     // Validation BC
            $dateEnvoi            = $this->daAfficherRepository->getDateEnvoiFournisseur($numeroDa, $numeroVersion, $numBC); // Envoi au fournisseur
            $dateReceptionArticle = $this->daAfficherRepository->getDateReceptionArticle($numeroDa, $numeroVersion, $numBC); // Réception des articles
            $dateLivraisonArticle = $this->daAfficherRepository->getDateLivraisonArticle($numeroDa, $numeroVersion, $numBC); // Livraison des articles

            // Définition de toutes les étapes possibles
            $etapes = [
                $this->creerEtapeBc($pointDepart['statut'], $pointDepart['dotClass'], $dateValidationDA, false),
                $this->creerEtapeBc('Génération BC', StatutBcConstant::STATUT_A_GENERER, $dateCreationBc),
                $this->creerEtapeBc('Validation BC', StatutBcConstant::STATUT_VALIDE, $dateValidation),
                $this->creerEtapeBc('BC envoyé au fournisseur', StatutBcConstant::STATUT_BC_ENVOYE_AU_FOURNISSEUR, $dateEnvoi),
                $this->creerEtapeBc('Réception des articles', StatutBcConstant::STATUT_PARTIELLEMENT_LIVRE, $dateReceptionArticle),
                $this->creerEtapeBc('Livraison des articles', StatutBcConstant::STATUT_TOUS_LIVRES, $dateLivraisonArticle),
            ];

            // Filtrer les étapes qui ont une date
            $etapesValides = array_filter($etapes, fn($etape) => $etape['date'] !== null);

            // S'il n'y a aucune étape valide, passer au suivant
            if (empty($etapesValides)) continue;

            // Trier les étapes par date (du plus ancien au plus récent)
            usort($etapesValides, fn($a, $b) => $a['date'] <=> $b['date']);

            // Construire le tableau avec calcul automatique des durées
            $tabTemp[$numBC] = $this->construireEtapesAvecDurees($etapesValides, (bool) $dateLivraisonArticle);
        }

        return $tabTemp;
    }

    /**
     * @param string $statut Libellé affiché pour cette étape
     * @param string $classKey Clé de statut BC utilisée pour résoudre la classe CSS du point
     * @param \DateTime|null $date
     * @param bool $isStatutBc
     *
     * @return array{statut:string,dotClass:string,date:\DateTime|null}
     */
    private function creerEtapeBc(string $statut, string $classKey, ?\DateTime $date, bool $isStatutBc = true): array
    {
        return [
            'statut'   => $statut,
            'dotClass' => $isStatutBc ? StatutBcConstant::getCssClassBc($classKey) : $classKey,
            'date'     => $date,
        ];
    }

    /**
     * @param string $statutDal
     * @param string|null $statutOr
     *
     * @return string
     */
    private function getStatutFinal(string $statutDal, ?string $statutOr): string
    {
        $estDaValide = ($statutOr === StatutDaConstant::STATUT_DW_A_MODIFIER &&
            $statutDal === StatutDaConstant::STATUT_EN_COURS_CREATION) ||
            $statutDal === StatutDaConstant::STATUT_CLOTUREE;

        return $estDaValide ? StatutDaConstant::STATUT_VALIDE : $statutDal;
    }

    /** 
     * @param string $statut
     * @param \DateTime|null $dateDemande
     * @param \DateTime|null $dateCreation
     * 
     * @return array{statut:string,dotClass:string,date:string,nbrJours:string}
     */
    private function createTimelineEntry(string $statut, ?\DateTime $dateDemande, ?\DateTime $dateCreation = null): array
    {
        return [
            'statut'   => $statut,
            'dotClass' => StatutDaConstant::getCssClassDa($statut),
            'date'     => $statut === StatutDaConstant::STATUT_SOUMIS_APPRO ? $dateDemande : $dateCreation,
            'nbrJours' => 0,
        ];
    }

    /**
     * @return array{statut:string,dotClass:string,date:string,nbrJours:string}
     */
    private function createCurrentDateEntry(): array
    {
        return [
            'statut'   => '',
            'dotClass' => '',
            'date'     => 'Aujourd’hui',
            'nbrJours' => '',
        ];
    }

    /**
     * Clôture une timeline non terminée en calculant la durée jusqu'à aujourd'hui et en ajoutant une entrée "Aujourd'hui".
     *
     * @param array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}> $timeline
     *
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function cloturerAvecAujourdhui(array $timeline): array
    {
        $lastEntry = end($timeline);
        $nbrJours  = $this->formatDuration(
            $this->differenceJoursOuvrables(
                \DateTime::createFromFormat('d/m/Y', $lastEntry['date']),
                new \DateTime()
            )
        );

        $timeline[array_key_last($timeline)]['nbrJours'] = $nbrJours;
        $timeline[] = $this->createCurrentDateEntry();

        return $timeline;
    }

    /**
     * Calcule la durée entre étapes consécutives (jours ouvrables) et formate leur date.
     *
     * @param array<int,array{statut:string,dotClass:string,date:\DateTime}> $etapes Triées par date croissante
     * @param bool $isComplete Si $isComplete est faux, la dernière étape est comptée jusqu'à aujourd'hui et une entrée "Aujourd'hui" est ajoutée.
     *
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function construireEtapesAvecDurees(array $etapes, bool $isComplete): array
    {
        $nbEtapes = count($etapes);
        $timeline = [];

        foreach ($etapes as $index => $etape) {
            $isLastStep = $index === $nbEtapes - 1;
            $dateFin = !$isLastStep ? $etapes[$index + 1]['date'] : ($isComplete ? null : new \DateTime());

            $timeline[] = [
                'statut'   => $etape['statut'],
                'dotClass' => $etape['dotClass'],
                'date'     => $etape['date']->format('d/m/Y'),
                'nbrJours' => $dateFin
                    ? $this->formatDuration($this->differenceJoursOuvrables($etape['date'], $dateFin))
                    : '',
            ];
        }

        if (!$isComplete) $timeline[] = $this->createCurrentDateEntry();

        return $timeline;
    }

    /** 
     * @param int $nbrJours
     * 
     * @return string
     */
    private function formatDuration(int $nbrJours): string
    {
        return $nbrJours === 0 ? "< 1 jour" : $nbrJours . " jour(s)";
    }
}
