<?php

namespace App\Controller\planningAtelier;

use App\Controller\Controller;
use App\Entity\planningAtelier\planningAtelierSearch;
use App\Form\planningAtelier\planningAtelierSearchType;
use App\Model\planningAtelier\planningAtelierModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class planningAtelierControler extends Controller
{
    private planningAtelierSearch $planningAtelierSearch;
    private planningAtelierModel $planningAtelierModel;
    public function __construct()
    {
        parent::__construct();
        $this->planningAtelierSearch = new planningAtelierSearch();
        $this->planningAtelierModel = new planningAtelierModel();
    }
    /**
     * @route("/planningAtelier", name = "planningAtelier_vue")
     * 
     * @return void
     */
    public function planningAtelierEncours(Request $request)
    {
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(
            PlanningAtelierSearchType::class, 
            $this->planningAtelierSearch,
            ['method' => 'GET']
        )->getForm();

        $form->handleRequest($request);
        $filteredDates = [];
        $output = [];
        $dates = [];
        $result = [];
        $criteria = $this->planningAtelierSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $start = $this->planningAtelierSearch->getDateDebut();
            $end = $this->planningAtelierSearch->getDateFin();
            $result = $this->planningAtelierModel->recupData($criteria);
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start,$interval,(clone $end)->modify('+1 day'));

            foreach ($period as $date) {
               $dates [] = $date; 
            }
            $filteredDates = array_map(fn($date) => $date->format('Y-m-d'), $dates);

            
            $output = [];

            foreach ($result as $item) {
                // Clé de regroupement unique basée sur les champs communs
                $key = $item['section'] . '|' . $item['intitule'] . '|' . $item['numor'] . '|' . $item['itv'] . '|' . $item['ressource'] . '|' . $item['nbjour'];
                
                if (!isset($output[$key])) {
                    $output[$key] = [
                        "section" => $item["section"],
                        "intitule" => $item["intitule"],
                        "numor" => $item["numor"],
                        "itv" => $item["itv"],
                        "ressource" => $item["ressource"],
                        "nbjour" => $item["nbjour"],
                        "dateData" => []
                    ];
                }

                $output[$key]["dateData"][] = [
                    "datedebut" => $item["datedebut"],
                    "datefin" => $item["datefin"]
                ];
            }
        }

        self::$twig->display('planningAtelier/planningAtelier.html.twig', [
            'form' => $form->createView(),
            'dates'=>$dates,
            'filteredDates'=>$filteredDates,
            'planning' =>$output
        ]);
    }
}
