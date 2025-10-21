<?php

namespace App\Controller\Traits;

use Exception;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Dto\Dit\DemandeInterventionDto;
use App\Entity\dit\DemandeIntervention;
use App\Entity\admin\dit\WorNiveauUrgence;



trait DitTrait
{

    /**

     * @var DemandeInterventionFactory

     * Cette propriété doit être injectée dans le constructeur du contrôleur qui utilise ce trait.

     */

    private $demandeInterventionFactory;



    private function createDemandeInterventionFromDto(DemandeInterventionDto $dto): DemandeIntervention
    {
        return $this->demandeInterventionFactory->createFromDto($dto);
    }


    private function historiqueInterventionMateriel($dits): array
    {
        $historiqueMateriel = $this->getDitModel()->historiqueMateriel($dits->getIdMateriel());
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
