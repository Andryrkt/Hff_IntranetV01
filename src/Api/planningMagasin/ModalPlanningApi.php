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
            $qteCIS = [];
            for ($i = 0; $i < count($details); $i++) {
                if ($numOr[0] == '5' || $numOr[0] == '3' || $numOr[0] == '4' || $numOr[0] == '2') {
                    $detailes[] = $this->planningMagasinModel->recuperationEtaMag($details[$i]['numerocdecis'], $details[$i]['ref'], $details[$i]['cst']);
                    $recupPariel[] = $this->planningMagasinModel->recupPartiel($details[$i]['numerocdecis'], $details[$i]['ref']);
                    $recupGot['ord'] = $this->planningMagasinModel->recupInfodGcot($details[$i]['numerocdecis']);
                    $qteCIS[] = $this->planningMagasinModel->recupeQteCISlig($details[$i]['numcis'], $details[$i]['intv'], $details[$i]['ref']);
                    $dateLivLig[] = $this->planningMagasinModel->dateLivraisonCIS($details[$i]['numcis'], $details[$i]['ref'], $details[$i]['cst']);
                    $dateAllLig[] = $this->planningMagasinModel->dateAllocationCIS($details[$i]['numcis'], $details[$i]['ref'], $details[$i]['cst']);
                } else {
                    if (empty($details[$i]['numerocmd']) || $details[$i]['numerocmd'] == "0") {
                        $recupGot = [];
                    } else {
                        $detailes[] = $this->planningMagasinModel->recuperationEtaMag($details[$i]['numerocmd'], $details[$i]['ref'], $details[$i]['cst']);
                        $recupPariel[] = $this->planningMagasinModel->recupPartiel($details[$i]['numerocmd'], $details[$i]['ref']);
                        $recupGot['ord'] = $this->planningMagasinModel->recupInfodGcot($details[$i]['numerocmd']);
                    }
                }
                if (!empty($detailes[0])) {
                    $details[$i]['Eta_ivato'] = $detailes[0][0]['Eta_ivato'];
                    $details[$i]['Eta_magasin'] =  $detailes[0][0]['Eta_magasin'];
                    $detailes = [];
                } else {
                    $details[$i]['Eta_ivato'] = "";
                    $details[$i]['Eta_magasin'] = "";
                    $detailes = [];
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
                if (!empty($dateLivLig[0])) {
                    $details[$i]['datelivlig'] = $dateLivLig[$i]['0']['datelivlig'];
                } else {
                    $details[$i]['datelivlig'] = "";
                }

                if (!empty($dateAllLig[0])) {
                    $details[$i]['dateAllLIg'] = $dateAllLig[$i]['0']['datealllig'];
                } else {
                    $details[$i]['dateAllLIg'] = "";
                }
            }
        }

        for ($i = 0; $i < count($details); $i++) {

            if (!empty($qteCIS)) {
                if (!empty($qteCIS[$i])) {

                    $details[$i]['qteORlig'] = $qteCIS[$i]['0']['qteorlig'];
                    $details[$i]['qtealllig'] = $qteCIS[$i]['0']['qtealllig'];
                    $details[$i]['qterlqlig'] = $qteCIS[$i]['0']['qtereliquatlig'];
                    $details[$i]['qtelivlig'] = $qteCIS[$i]['0']['qtelivlig'];
                } elseif (!empty($qteCIS[$i - 1])) {
                    $details[$i]['qteORlig'] = $qteCIS[$i - 1]['0']['qteorlig'];
                    $details[$i]['qtealllig'] = $qteCIS[$i - 1]['0']['qtealllig'];
                    $details[$i]['qterlqlig'] = $qteCIS[$i - 1]['0']['qtereliquatlig'];
                    $details[$i]['qtelivlig'] = $qteCIS[$i - 1]['0']['qtelivlig'];
                } else {
                    $details[$i]['qteORlig'] = "";
                    $details[$i]['qtealllig'] = "";
                    $details[$i]['qterlqlig'] = "";
                    $details[$i]['qtelivlig'] = "";
                }
            }
        }

        $avecOnglet = empty($cdeCIS) || empty($cdeCIS[0]['succ']) ? false : true;
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
    /**
     * @Route("/api/numero-libelle-client", name="api_numero_libelle_client")
     */
    public function client()
    {
        $client = $this->planningMagasinModel->recupClientPlanningMagasin();
        header("Content-type:application/json");
        echo json_encode($client);
    }
}
