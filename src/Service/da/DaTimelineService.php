<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Traits\JoursOuvrablesTrait;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
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

        /* if (empty($timelineBc)) {
            $nbrJours = $this->formatDuration(
                $this->differenceJoursOuvrables(
                    \DateTime::createFromFormat('d/m/Y', $lastData['date']),
                    new \DateTime()
                )
            );
            $timelineDa[array_key_last($timelineDa)]['nbrJours'] = $nbrJours;
            $timelineDa[] = $this->createCurrentDateEntry();
        } */

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

        // Calculer les durées
        return $this->calculateDurations($tabTemp);
    }

    /** 
     * @param array<int,array{statutDal:string,dateCreation:\DateTime,dateDemande:\DateTime,numeroOr:string|null,statutOr:string|null,dateMajStatutOr:\DateTime|null}> $allDatas
     * @param array{statut:string,dotClass:string,date:string,nbrJours:string} $lastDataDA
     * 
     * @return array{string,array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>}
     */
    private function buildTimelineOR(array $allDatas, $lastDataDA): array
    {
        $tabTemp = [];
        $nbrJours = "";

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

                $nbrJours = $this->formatDuration(
                    $this->differenceJoursOuvrables(
                        \DateTime::createFromFormat('d/m/Y', $lastDataDA['date']),
                        $dateStatutOr
                    )
                );

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
     * @param array{statut:string,dotClass:string,date:string,nbrJours:string} $lastDataDA
     * 
     * @return array<string,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function buildTimelineBC(string $numeroDa, array $lastDataDA): array
    {
        $allDatas = $this->daAfficherRepository->getAllNumCdeAndVmax($numeroDa);
        $tabTemp = [];
        $today = new \DateTime();

        foreach ($allDatas as $data) {
            $numBC = $data['numeroCde'];
            $numeroVersion = $data['numeroVersion'];

            // Récupération de toutes les dates possibles
            $dateValidationDA     = \DateTime::createFromFormat('d/m/Y', $lastDataDA['date']);
            $dateCreationBc       = $this->daAfficherRepository->getDateCreationBc($numeroDa, $numeroVersion, $numBC);       // Génération BC
            $dateValidation       = $this->daAfficherRepository->getDateValidationBc($numeroDa, $numeroVersion, $numBC);     // Validation BC
            $dateEnvoi            = $this->daAfficherRepository->getDateEnvoiFournisseur($numeroDa, $numeroVersion, $numBC); // Envoi au fournisseur
            $dateReceptionArticle = $this->daAfficherRepository->getDateReceptionArticle($numeroDa, $numeroVersion, $numBC); // Réception des articles
            $dateLivraisonArticle = $this->daAfficherRepository->getDateLivraisonArticle($numeroDa, $numeroVersion, $numBC); // Livraison des articles

            // Définition de toutes les étapes possibles
            $etapes = [
                [
                    'statut'   => $lastDataDA['statut'],
                    'dotClass' => $lastDataDA['dotClass'],
                    'date'     => $dateValidationDA
                ],
                [
                    'statut'   => 'Génération BC',
                    'dotClass' => 'bg-bc-a-generer',
                    'date'     => $dateCreationBc
                ],
                [
                    'statut'   => 'Validation BC',
                    'dotClass' => 'bg-bc-valide',
                    'date'     => $dateValidation
                ],
                [
                    'statut'   => 'BC envoyé au fournisseur',
                    'dotClass' => 'bg-bc-envoye-au-fournisseur',
                    'date'     => $dateEnvoi
                ],
                [
                    'statut'   => 'Réception des articles',
                    'dotClass' => 'partiellement-livre',
                    'date'     => $dateReceptionArticle
                ],
                [
                    'statut'   => 'Livraison des articles',
                    'dotClass' => 'tout-livre',
                    'date'     => $dateLivraisonArticle
                ]
            ];

            // Filtrer les étapes qui ont une date
            $etapesValides = array_filter($etapes, fn($etape) => $etape['date'] !== null);

            // S'il n'y a aucune étape valide, passer au suivant
            if (empty($etapesValides)) continue;

            // Trier les étapes par date (du plus ancien au plus récent)
            usort($etapesValides, fn($a, $b) => $a['date'] <=> $b['date']);

            // Construire le tableau avec calcul automatique des durées
            $nbEtapes = count($etapesValides);
            foreach ($etapesValides as $index => $etape) {
                // Déterminer la date de fin pour le calcul
                // Si c'est la dernière étape, pas de durée
                // Sinon, la date de fin est la date de l'étape suivante
                $isLastStep = ($index === $nbEtapes - 1);
                $dateFinCalcul = !$isLastStep ? $etapesValides[$index + 1]['date'] : ($dateLivraisonArticle ? NULL : $today);

                $tabTemp[$numBC][] = [
                    'statut'   => $etape['statut'],
                    'dotClass' => $etape['dotClass'],
                    'date'     => $etape['date']->format('d/m/Y'),
                    'nbrJours' => $dateFinCalcul
                        ? $this->formatDuration($this->differenceJoursOuvrables($etape['date'], $dateFinCalcul))
                        : ''
                ];
            }

            // Ajouter la date actuelle si le processus n'est pas terminé
            if (!$dateLivraisonArticle) $tabTemp[$numBC][] = $this->createCurrentDateEntry();
        }

        return $tabTemp;
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
     * @param array<int,array{statutDal:string,statutOr:null,dateCreation:\DateTime,dateDemande:\DateTime}> $timeline
     * 
     * @return array<int,array{statut:string,dotClass:string,date:string,nbrJours:string}>
     */
    private function calculateDurations(array $timeline): array
    {
        for ($i = 0; $i < count($timeline); $i++) {
            if ($i < count($timeline) - 1) {
                $nbrJours = $this->differenceJoursOuvrables(
                    $timeline[$i + 1]['date'],
                    $timeline[$i]['date']
                );
                $timeline[$i]['nbrJours'] = $this->formatDuration($nbrJours);
            } else {
                $timeline[$i]['nbrJours'] = '';
            }
            $timeline[$i]['date'] = $timeline[$i]['date']->format('d/m/Y');
        }

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
