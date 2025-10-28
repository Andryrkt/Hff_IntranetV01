<?php

namespace App\Api\planningMagasin;

use App\Controller\Controller;
use App\Model\planningMagasin\ModalPlanningMagasinModel;
use Symfony\Component\Routing\Annotation\Route;

class ModalPlanningApi extends Controller
{
    private ModalPlanningMagasinModel $planningMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new ModalPlanningMagasinModel();
    }


    /**
     * @Route("/detail_plannigMagasin-modal/{numOr}", name="liste_detailPlanningMagasin")
     *
     * @return void
     */
    public function detailModal($numOr)
    {
        // Récupération de la liste de détails
        $cdeCIS = [];
        if ($numOr === '') {
            $details = [];
        } else {
            $details = $this->planningMagasinModel->recupDetailPlanningMagasinInformix($numOr);
            $cdeCIS = $this->planningMagasinModel->recupOrcis($numOr);
            $recupPariel = [];
            $recupGot = [];
            for ($i = 0; $i < count($details); $i++) {
                if ($numOr[0] == '5' || $numOr[0] == '3' || $numOr[0] == '4' || $numOr[0] == '2') {
                    $recupPariel[] = $this->planningMagasinModel->recupPartiel($details[$i]['numerocdecis'], $details[$i]['ref']);
                    $recupGot['ord'] = $this->planningMagasinModel->recupInfodGcot($details[$i]['numerocdecis']);
                } else {
                    if (empty($details[$i]['numerocmd']) || $details[$i]['numerocmd'] == "0") {
                        $recupGot = [];
                    } else {
                        $recupPariel[] = $this->planningMagasinModel->recupPartiel($details[$i]['numerocmd'], $details[$i]['ref']);
                        $recupGot['ord'] = $this->planningMagasinModel->recupInfodGcot($details[$i]['numerocmd']);
                    }
                }

                if (!empty($recupGot)) {
                    $details[$i]['Ord'] = $recupGot['ord'] === false ? '' : $recupGot['ord']['Ord'];
                } else {
                    $details[$i]['Ord'] = "";
                }

                if (!empty($recupPariel[$i])) {
                    $details[$i]['qteSlode'] = $recupPariel[$i]['0']['solde'];
                    $details[$i]['qte'] = $recupPariel[$i]['0']['qte'];
                } else {
                    $details[$i]['qteSlode'] = "";
                    $details[$i]['qte'] = "";
                }
            }
            $avecOnglet = empty($cdeCIS) || empty($cdeCIS[0]['succ']) ? false : true;
        }
        header("Content-type:application/json");
        echo json_encode([
            'avecOnglet' => $avecOnglet,
            'data' => $details,
        ]);
    }




    private function regroupeParIntervention(array $details): array
    {
        $groupedDetails = [];

        foreach ($details as $detail) {
            $intvKey = $detail['intv']; // La valeur de 'intv' utilisée comme clé
            if (!isset($groupedDetails[$intvKey])) {
                $groupedDetails[$intvKey] = [];
            }
            $groupedDetails[$intvKey][] = $detail; // Ajouter l'élément au groupe correspondant
        }
        return $groupedDetails;
    }
}
