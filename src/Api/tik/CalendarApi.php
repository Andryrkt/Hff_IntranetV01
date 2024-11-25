<?php

namespace App\Api\tik;

use App\Controller\Controller;
use App\Entity\tik\TkiPlanning;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CalendarApi extends Controller
{
    /**
     * @Route("/api/tik/calendar-fetch", name="calendar-fetch", methods={"GET", "POST"})
     */
    public function calendar(Request $request): JsonResponse
    {
        // Vérifier si c'est une méthode GET
        if ($request->isMethod('GET')) {
            // Récupération des événements depuis la base de données
            $events = self::$em->getRepository(TkiPlanning::class)->findAll();

            // Transformation des données en tableau JSON
            $eventData = [];
            foreach ($events as $event) {
                $eventData[] = [
                    'id' => $event->getId(),
                    'title' => $event->getObjetDemande(),
                    'description' => $event->getDetailDemande(),
                    'start' => $event->getDateDebutPlanning()->format('Y-m-d H:i:s'),
                    'end' => $event->getDateFinPlanning()->format('Y-m-d H:i:s'),
                ];
            }

            // Retourner les données en JSON
            return new JsonResponse($eventData);
        }

        // Vérifier si c'est une méthode POST
        if ($request->isMethod('POST')) {
            // Récupérer les données JSON envoyées
            $data = json_decode($request->getContent(), true);

            // Validation des données
            if (isset($data['title'], $data['description'], $data['start'], $data['end'])) {
                // Création de l'événement
                $event = new TkiPlanning();
                $event->setObjetDemande($data['title']);
                $event->setDetailDemande($data['description']);
                $event->setDateDebutPlanning(new \DateTime($data['start']));
                $event->setDateFinPlanning(new \DateTime($data['end']));

                // Sauvegarde dans la base de données
                $entityManager = self::$em;
                $entityManager->persist($event);
                $entityManager->flush();

                return new JsonResponse(['status' => 'success'], JsonResponse::HTTP_CREATED);
            }

            // Retourner une erreur si les données sont invalides
            return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Retourner une erreur si la méthode n'est pas autorisée
        return new JsonResponse(['error' => 'Method not allowed'], JsonResponse::HTTP_METHOD_NOT_ALLOWED);
    }
}
