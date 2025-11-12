<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinOrALIvrerTrait
{
    private function recupData($criteria)
    {
        $magasinListOrLivrerModel = new MagasinListeOrLivrerModel();

        /** @var string $numeroOrsItv @var string $numeroOr */
        [$numeroOrsItv, $numeroOr] = $this->recupNumOrSelonCondition($criteria);

        $data = $magasinListOrLivrerModel->recupereListeMaterielValider($criteria, $numeroOrsItv, $numeroOr);

        //ajouter le numero dit dans data
        for ($i = 0; $i < count($data); $i++) {
            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $data[$i]['referencedit']]);
            if (!empty($ditRepository)) {
                $data[$i]['niveauUrgence'] = $ditRepository->getIdNiveauUrgence()->getDescription();
            } else {
                break;
            }
        }
        return $data;
    }

    private function recupNumOrSelonCondition(array $criteria): array
    {
        $magasinModel = new MagasinListeOrATraiterModel();
        /** @var array $numeroOrsItv @var array $numeroOr */
        [$numeroOrsItv, $numeroOr] = $magasinModel->recupNumOr($criteria);

        return [
            TableauEnStringService::orEnString($numeroOrsItv),
            TableauEnStringService::orEnString($numeroOr)
        ];
    }
}
