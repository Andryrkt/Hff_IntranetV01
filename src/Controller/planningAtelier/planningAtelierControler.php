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

        $output = [];
        $filteredDates = [];
        $dates = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $start = $criteria->getDateDebut();
            $end = $criteria->getDateFin();

            $result = $this->planningAtelierModel->recupData($criteria);

            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

            foreach ($period as $date) {
                $dates[] = $date;
                $filteredDates[] = $date->format('Y-m-d');
            }
            foreach ($result as $item) {
                $key = $item['section'] . '|' . $item['intitule'] . '|' . $item['numor'] . '|' . $item['itv'] . '|' . $item['ressource'] . '|' . $item['nbjour'];

                if (!isset($output[$key])) {
                    $output[$key] = [
                        "section" => $item["section"],
                        "intitule" => $item["intitule"],
                        "numor" => $item["numor"],
                        "itv" => $item["itv"],
                        "ressource" => $item["ressource"],
                        "nbjour" => $item["nbjour"],
                        "presence" => [] // clef: 'Y-m-d', valeur: ['matin' => bool, 'apm' => bool]
                    ];
                }

                $debut = new \DateTime($item["datedebut"]);
                $fin = new \DateTime($item["datefin"]);

                foreach ($dates as $date) {
                    $dateStr = $date->format('Y-m-d');

                    $matin_debut = new \DateTime("$dateStr 08:00:00");
                    $matin_fin   = new \DateTime("$dateStr 12:00:00");
                    $aprem_debut = new \DateTime("$dateStr 13:30:00");
                    $aprem_fin   = new \DateTime("$dateStr 17:30:00");

                    if (!isset($output[$key]['presence'][$dateStr])) {
                        $output[$key]['presence'][$dateStr] = ['matin' => false, 'apm' => false];
                    }

                    if ($fin >= $matin_debut && $debut < $matin_fin) {
                        $output[$key]['presence'][$dateStr]['matin'] = true;
                    }
                    if ($fin >= $aprem_debut && $debut < $aprem_fin) {
                        $output[$key]['presence'][$dateStr]['apm'] = true;
                    }
                }
            }
        }
        self::$twig->display('planningAtelier/planningAtelier.html.twig', [
            'form' => $form->createView(),
            'dates' => $dates,
            'filteredDates' => $filteredDates,
            'planning' => $output
        ]);
    }
}
