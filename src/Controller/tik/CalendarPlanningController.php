<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Form\tik\CalendarType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CalendarPlanningController extends Controller
{
    /**
     * @Route("/tik-calendar-planning", name="tik_calendar_planning")
     */
    public function calendar(Request $request)
    {
        $form = self::$validator->createBuilder(CalendarType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        $this->logUserVisit('tik_calendar_planning'); // historisation du page visité par l'utilisateur

        self::$twig->display('tik/planning/calendar.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
