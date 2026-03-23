<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Form\tik\CalendarType;
use App\Entity\tik\TikPlanningSearch;
use App\Form\tik\TikPlanningSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/it")
 */
class CalendarPlanningController extends Controller
{
    /**
     * @Route("/calendar-planning", name="tik_calendar_planning")
     */
    public function calendar(Request $request)
    {
        $tikPlanningSearch = new TikPlanningSearch;

        $form = $this->getFormFactory()->createBuilder(CalendarType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //dd($form->getData());
        }

        $formSearch = $this->getFormFactory()->createBuilder(TikPlanningSearchType::class, $tikPlanningSearch, [
            'method' => 'POST',
        ])->getForm();

        $formSearch->handleRequest($request);

        if ($formSearch->isSubmitted() && $formSearch->isValid()) {
            $tikPlanningSearch = $formSearch->getData();
        }
        $this->getSessionService()->set('tik_planning_search', $tikPlanningSearch->toArray());

        $this->logUserVisit('tik_calendar_planning'); // historisation du page visité par l'utilisateur

        return $this->render('tik/planning/calendar.html.twig', [
            'form' => $form->createView(),
            'formSearch' => $formSearch->createView(),
        ]);
    }
}
