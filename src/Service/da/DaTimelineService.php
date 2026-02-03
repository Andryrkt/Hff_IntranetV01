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

    public function __construct(EntityManagerInterface $em)
    {
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
    }

    public function getTimelineData(string $numeroDa): array
    {
        $allDatas = $this->daAfficherRepository->getTimelineData($numeroDa);
        $result = [];

        foreach ($allDatas as $key => $data) {
            $statutDal = $data['statutDal'];

            // Ajouter le statut initial si nécessaire
            if ($key === 0 && $statutDal !== DemandeAppro::STATUT_SOUMIS_APPRO) {
                $result[] = [
                    'statut'   => DemandeAppro::STATUT_SOUMIS_APPRO,
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
