<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DaAfficherRepository;
use App\Traits\JoursOuvrablesTrait;

class DaTimelineService
{
    use JoursOuvrablesTrait;
    private DaAfficherRepository $daAfficherRepository;
    private $styleStatutDA = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
    }

    public function initStyleStatuts()
    {
        $this->styleStatutDA = [
            DemandeAppro::STATUT_VALIDE               => 'bg-bon-achat-valide',
            DemandeAppro::STATUT_CLOTUREE             => 'bg-bon-achat-valide',
            DemandeAppro::STATUT_TERMINER             => 'bg-primary text-white',
            DemandeAppro::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
            DemandeAppro::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
            DemandeAppro::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
            DemandeAppro::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
            DemandeAppro::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
            DemandeAppro::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
            DemandeAppro::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
            DemandeAppro::STATUT_AUTORISER_EMETTEUR   => 'bg-creation-demande-initiale',
            DemandeAppro::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
        ];
    }

    public function getTimelineData(string $numeroDa): array
    {
        $allDatas = $this->daAfficherRepository->getTimelineData($numeroDa);
        if (empty($allDatas)) return [];

        $result = [];
        $this->initStyleStatuts();

        foreach ($allDatas as $key => $data) {
            $statutDal = $data['statutDal'];

            // Ajouter le statut initial si nécessaire
            if ($key === 0 && $statutDal !== DemandeAppro::STATUT_SOUMIS_APPRO) {
                $result[] = [
                    'statut'   => DemandeAppro::STATUT_SOUMIS_APPRO,
                    'dotClass' => $this->styleStatutDA[DemandeAppro::STATUT_SOUMIS_APPRO],
                    'date'     => $data['dateDemande'],
                    'nbrJours' => 0,
                ];
            }

            // Déterminer le statut final
            $statutOr = $data['statutOr'];
            $estDaValide = ($statutOr && $statutOr === DemandeAppro::STATUT_DW_A_MODIFIER &&
                $statutDal === DemandeAppro::STATUT_EN_COURS_CREATION) ||
                $statutDal === DemandeAppro::STATUT_CLOTUREE;
            $statutFinal = $estDaValide ? DemandeAppro::STATUT_VALIDE : $statutDal;

            // Ajouter uniquement si le statut est différent du précédent
            $lastIndex = count($result) - 1;
            if ($lastIndex < 0 || $result[$lastIndex]['statut'] !== $statutFinal) {
                $result[] = [
                    'statut'   => $statutFinal,
                    'dotClass' => $this->styleStatutDA[$statutFinal],
                    'date'     => $data['dateCreation'],
                    'nbrJours' => 0,
                ];
            } else {
                // Mettre à jour la date du statut existant (prendre la plus récente)
                $result[$lastIndex]['date'] = $data['dateCreation'];
            }
        }

        // Calculer les différences de jours ouvrables
        for ($i = 0; $i < count($result); $i++) {
            if ($i < count($result) - 1) {
                $nbrJours = $this->differenceJoursOuvrables(
                    $result[$i + 1]['date'],
                    $result[$i]['date']
                );
                $result[$i]['nbrJours'] = $nbrJours === 0 ? "< 1 jour" : $nbrJours . " jour(s)";
            } else {
                $result[$i]['nbrJours'] = "";
            }
            $result[$i]['date'] = $result[$i]['date']->format('d/m/Y');
        }

        return $result;
    }
}
