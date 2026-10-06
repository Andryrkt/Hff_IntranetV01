<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinOrATraiterTrait
{
    private function recupData(array $criteria)
    {
        $magasinListeOrATraiterModel = new MagasinListeOrATraiterModel();
        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, $magasinListeOrATraiterModel);

        $data = $magasinListeOrATraiterModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('magasin_liste_or_traiter_search_criteria', $criteria);

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

    private function recupNumOrTraiterSelonCondition(array $criteria, MagasinListeOrATraiterModel $magasinListeOrATraiterModel): array
    {
        /** @var array $numOrItv @var array $numORTouCourt @ */
        [$numOrItv, $numORTouCourt] = $magasinListeOrATraiterModel->recupNumOr($criteria);

        $numOrValideString = TableauEnStringService::orEnString($numOrItv);

        return  [
            "numOrValideString" => $numOrValideString
        ];
    }
}
