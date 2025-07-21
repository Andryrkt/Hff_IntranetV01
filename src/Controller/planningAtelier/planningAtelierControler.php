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
            PlanningAtelierSearchType::class,  // <-- ici tu utilises le bon FormType
            $this->planningAtelierSearch,
            ['method' => 'GET']
        )->getForm();

        $form->handleRequest($request);
        $dates = [];
        $result = [];
        $criteria = $this->planningAtelierSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            dump($criteria);
            $start = $this->planningAtelierSearch->getDateDebut();
            $end = $this->planningAtelierSearch->getDateFin();

            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start,$interval,(clone $end)->modify('+1 day'));

            foreach ($period as $date) {
               $dates [] = $date; 
            }
            dump($dates);
            $result = $this->planningAtelierModel->recupData($criteria);
            dd($result);
        }

        self::$twig->display('planningAtelier/planningAtelier.html.twig', [
            'form' => $form->createView(),
            'dates'=>$dates,
            'planning' =>$result
        ]);
    }
}
