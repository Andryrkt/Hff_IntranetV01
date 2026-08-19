<?php

namespace App\Controller\Traits;


use App\Model\dit\DitModel;
use App\Dto\Dit\DemandeInterventionDto;
use App\Factory\Dit\DemandeInterventionFactory;

trait DitTrait
{
    private function createDemandeInterventionFromDto(DemandeInterventionDto $dto, DemandeInterventionFactory $demandeInterventionFactory): array
    {
        if ($dto->estAtePolTana) {
            $ditAteTana =  $demandeInterventionFactory->createFromDto($dto);
            $ditAteTanaPol =  $demandeInterventionFactory->createFromDtoPol($dto);
            return [$ditAteTana, $ditAteTanaPol];
        } else {
            return [$demandeInterventionFactory->createFromDto($dto)];
        }
    }

    private function historiqueInterventionMateriel(int $idMateriel, string $reparationRealise): array
    {
        $ditModel = new DitModel();
        $historiqueMateriel = $ditModel->historiqueMateriel($idMateriel, $reparationRealise);

        foreach ($historiqueMateriel as $keys => $values) {
            foreach ($values as $key => $value) {
                if ($key == "datedebut") {
                    $historiqueMateriel[$keys]['datedebut'] = implode('/', array_reverse(explode("-", $value)));
                } elseif ($key === 'somme') {
                    $historiqueMateriel[$keys][$key] = explode(',', $this->formatNumber($value))[0];
                }
            }
        }
        return $historiqueMateriel;
    }
}
