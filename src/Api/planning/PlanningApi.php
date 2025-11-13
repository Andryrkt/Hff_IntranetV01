<?php

namespace App\Api\planning;

use App\Controller\Controller;
use App\Model\planning\PlanningModel;
use App\Entity\dit\DemandeIntervention;
use App\Model\planning\ModalPlanningModel;
use Symfony\Component\Routing\Annotation\Route;

class PlanningApi extends Controller
{
    private PlanningModel $planningModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningModel = new PlanningModel();
    }

    /**
     * @Route("/serviceDebiteurPlanning-fetch/{agenceId}")
     */
    public function serviceDebiteur($agenceId)
    {
        if ($agenceId == 100) {
            $serviceDebiteur = [];
        } else {
            $serviceDebiteur = $this->planningModel->recuperationServiceDebite($agenceId);
        }

        header("Content-type:application/json");

        echo json_encode($serviceDebiteur);
    }

    /**
     * @Route("/detail-modal/{numOr}", name="liste_detailModal")
     *
     * @return void
     */
    public function detailModal($numOr)
    {
        $criteria = $this->getSessionService()->get('planning_search_criteria', []);
        //RECUPERATION DE LISTE DETAIL 
        $orCIS = [];
        if ($numOr === '') {
            $details = [];
        } else {
            $details = $this->planningModel->recuperationDetailPieceInformix($numOr, $criteria);

            $orCIS = $this->planningModel->recupOrcis($numOr);

            $ditRepositoryConditionner = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $numOr)[0]]);
            $numDit = $ditRepositoryConditionner->getNumeroDemandeIntervention();
            $migration = $ditRepositoryConditionner->getMigration();

            $detailes = [];
            //    dd($details);
            $qteCIS = [];
            $recupPariel = [];
            $recupGot = [];
            $dateLivLig = [];
            $dateAllLig = [];
            for ($i = 0; $i < count($details); $i++) {

                if ($numOr[0] == '5' || $numOr[0] == '3' || $numOr[0] == '4' || $numOr[0] == '2') {

                    // if ($details[$i]['numcis'] !== "0"  || $details[$i]['numerocdecis'] == "0") {

                    //     $recupGot = [];
                    //     $qteCIS[] = $this->planningModel->recupeQteCISlig($details[$i]['numor'], $details[$i]['intv'], $details[$i]['ref']);
                    //     $dateLivLig[] = $this->planningModel->dateLivraisonCIS($details[$i]['numcis'], $details[$i]['ref'], $details[$i]['cst']);
                    //     $dateAllLig[] = $this->planningModel->dateAllocationCIS($details[$i]['numcis'], $details[$i]['ref'], $details[$i]['cst']);
                    //     $recupGot['ord'] = $this->planningModel->recuperationinfodGcot($details[$i]['numerocdecis']);
                    // } else {
                    $detailes[] = $this->planningModel->recuperationEtaMag($details[$i]['numerocdecis'], $details[$i]['ref'], $details[$i]['cst']);
                    $recupPariel[] = $this->planningModel->recuperationPartiel($details[$i]['numerocdecis'], $details[$i]['ref']);
                    $recupGot['ord'] = $this->planningModel->recuperationinfodGcot($details[$i]['numerocdecis']);
                    $qteCIS[] = $this->planningModel->recupeQteCISlig($details[$i]['numor'], $details[$i]['intv'], $details[$i]['ref']);
                    $dateLivLig[] = $this->planningModel->dateLivraisonCIS($details[$i]['numcis'], $details[$i]['ref'], $details[$i]['cst']);
                    $dateAllLig[] = $this->planningModel->dateAllocationCIS($details[$i]['numcis'], $details[$i]['ref'], $details[$i]['cst']);
                    // }
                } else {
                    if (empty($details[$i]['numerocmd']) || $details[$i]['numerocmd'] == "0") {
                        $recupGot = [];
                    } else {
                        $detailes[] = $this->planningModel->recuperationEtaMag($details[$i]['numerocmd'], $details[$i]['ref'], $details[$i]['cst']);
                        $recupPariel[] = $this->planningModel->recuperationPartiel($details[$i]['numerocmd'], $details[$i]['ref']);
                        // $qteCIS[] = $this->planningModel->recupeQteCISlig($details[$i]['numor'],$details[$i]['intv'],$details[$i]['ref']);
                        $recupGot['ord'] = $this->planningModel->recuperationinfodGcot($details[$i]['numerocmd']);
                    }
                }

                if (!empty($detailes[0])) {
                    $details[$i]['Eta_ivato'] = $detailes[0][0]['Eta_ivato'];
                    $details[$i]['Eta_magasin'] =  $detailes[0][0]['Eta_magasin'];
                    $details[$i]['Est_ship_date'] =  $detailes[0][0]['Est_ship_date'];
                    $detailes = [];
                } else {
                    $details[$i]['Eta_ivato'] = "";
                    $details[$i]['Eta_magasin'] = "";
                    $details[$i]['Est_ship_date'] = "";
                    $detailes = [];
                }

                if (!empty($recupPariel[$i])) {
                    $details[$i]['qteSlode'] = $recupPariel[$i]['0']['solde'];
                    $details[$i]['qte'] = $recupPariel[$i]['0']['qte'];
                } else {
                    $details[$i]['qteSlode'] = "";
                    $details[$i]['qte'] = "";
                }


                if (!empty($recupGot)) {
                    $details[$i]['Ord'] = $recupGot['ord'] === false ? '' : $recupGot['ord']['Ord'];
                } else {
                    $details[$i]['Ord'] = "";
                }

                if (!empty($dateLivLig[0])) {
                    $details[$i]['dateLivLIg'] = $dateLivLig[$i]['0']['datelivlig'];
                } else {
                    $details[$i]['dateLivLIg'] = "";
                }

                if (!empty($dateAllLig[0])) {
                    $details[$i]['dateAllLIg'] = $dateAllLig[$i]['0']['datealllig'];
                } else {
                    $details[$i]['dateAllLIg'] = "";
                }




                $details[$i]['numDit'] = $numDit;
                $details[$i]['migration'] = $migration;
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


        $avecOnglet = empty($orCIS) || empty($orCIS[0]['succ']) ? false : true;

        header("Content-type:application/json");

        echo json_encode([
            'avecOnglet' => $avecOnglet,
            'data' => $details,
        ]);
    }

    /**
     * @Route("/api/technicien-intervenant/{numOr}/{numItv}", name="")
     */
    public function TechnicienIntervenant($numOr, $numItv)
    {
        $matriculeNom = $this->planningModel->recupTechnicientIntervenant($numOr, $numItv);

        if (empty($matriculeNom)) {
            $matriculeNom = $this->planningModel->recupTechnicien2($numOr, $numItv);
        }

        header("Content-type:application/json");

        echo json_encode($matriculeNom);
    }
}
