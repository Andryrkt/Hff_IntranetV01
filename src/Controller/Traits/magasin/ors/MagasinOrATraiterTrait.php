<?php

namespace App\Controller\Traits\magasin\ors;


use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\MagasinListeOrATraiterModel;

trait MagasinOrATraiterTrait
{
    private function recupData(array $criteria)
    {
        $magasinListeOrATraiterModel = new MagasinListeOrATraiterModel();
        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, $magasinListeOrATraiterModel, $this->getEntityManager());

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

    private function recupNumOrTraiterSelonCondition(array $criteria, $magasinListeOrATraiterModel, $em): array
    {
        /** @var array $numOrItv @var array $numORTouCourt @ */
        [$numOrItv, $numORTouCourt] = $magasinListeOrATraiterModel->recupNumOr($criteria);

        $numOrValideString = TableauEnStringService::orEnString($numOrItv);

        return  [
            "numOrValideString" => $numOrValideString
        ];
    }
    /*
    private function numeroOrValide($numeroOrs, $magasinModel, $em)
    {
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if(!empty($numItv)){
                $numItvs = $magasinModel->recupNumeroItv($numeroOr['numero_or'],$this->orEnString($numItv));
                if($numItvs[0]['nbitv'] === "0"){
                    $numOrValide[] = $numeroOr;
                }
            }
        }

        return $numOrValide;
    }
        */

    private function recupNumORItvValide($numeroOrs, $em)
    {
        $numOrValideItv = [];
        $numOrValide = [];
        foreach ($numeroOrs as $numeroOr) {
            $numItv = $em->getRepository(DitOrsSoumisAValidation::class)->findNumItvValide($numeroOr['numero_or']);
            if (!empty($numItv)) {
                foreach ($numItv as  $value) {
                    $numOrValideItv[] = $numeroOr['numero_or'] . '-' . $value;
                    $numOrValide[] = $numeroOr['numero_or'];
                }
            }
        }
        return [
            'numeroOr_itv' => $numOrValideItv,
            'numeroOr' => $numOrValide
        ];
    }
}
