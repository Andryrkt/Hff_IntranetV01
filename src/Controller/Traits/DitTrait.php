<?php

namespace App\Controller\Traits;


use App\Model\dit\DitModel;
use App\Entity\admin\Application;
use App\Dto\Dit\DemandeInterventionDto;


trait DitTrait
{

    /**

     * @var DemandeInterventionFactory

     * Cette propriété doit être injectée dans le constructeur du contrôleur qui utilise ce trait.

     */

    private $demandeInterventionFactory;



    private function createDemandeInterventionFromDto(DemandeInterventionDto $dto, Application $application): array
    {
        if ($dto->estAtePolTana) {
            $ditAteTana =  $this->demandeInterventionFactory->createFromDto($dto);
            $ditAteTanaPol =  $this->demandeInterventionFactory->createFromDtoPol($dto, $application);
            return [$ditAteTana, $ditAteTanaPol];
        } else {

            return [$this->demandeInterventionFactory->createFromDto($dto)];
        }
    }


    private function historiqueInterventionMateriel(int $idMateriel): array
    {$ditModel = new DitModel();
        $historiqueMateriel = $ditModel->historiqueMateriel($idMateriel);
        
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
