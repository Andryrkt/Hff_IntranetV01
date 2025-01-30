<?php

namespace App\Controller\planning;

use App\Controller\Controller;
use App\Model\planning\PlanningModel;
use App\Controller\Traits\Transformation;
use App\Entity\planning\ListePlanningSearch;
use App\Form\planning\ListePlanningSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListeController extends Controller
{
    use Transformation;
    private ListePlanningSearch $listePlanningSearch;
    private PlanningModel $planningModel;
    public function __construct()
    {
        parent::__construct();
        $this->listePlanningSearch = new ListePlanningSearch();
        $this->planningModel = new PlanningModel();
    }
    /**
     * @Route("/Liste",name = "liste_planning")
     * 
     *@return void
     */
    public function listecomplet(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        //initialisation
        $this->listePlanningSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
        ;

        $form = self::$validator->createBuilder(
            ListePlanningSearchType::class,
            $this->listePlanningSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->listePlanningSearch;
        self::$twig->display('planning/listePlanning.html.twig', [
            'form' => $form->createView(),

        ]);
    }
}
