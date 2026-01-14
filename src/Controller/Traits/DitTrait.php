<?php

namespace App\Controller\Traits;


use App\Model\dit\DitModel;
use App\Dto\Dit\DemandeInterventionDto;
use App\Factory\Dit\DemandeInterventionFactory;

trait DitTrait
{

    /**
     * @var DemandeInterventionFactory
     * Cette propriété doit être injectée dans le constructeur du contrôleur qui utilise ce trait.
     */
    private $demandeInterventionFactory;

    private function createDemandeInterventionFromDto(DemandeInterventionDto $dto): array
    {
        if ($dto->estAtePolTana) {
            $ditAteTana =  $this->demandeInterventionFactory->createFromDto($dto);
            $ditAteTanaPol =  $this->demandeInterventionFactory->createFromDtoPol($dto);
            return [$ditAteTana, $ditAteTanaPol];
        } else {
            return [$this->demandeInterventionFactory->createFromDto($dto)];
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
