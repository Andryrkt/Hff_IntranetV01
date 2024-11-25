<?php

namespace App\Api\tik;

use App\Controller\Controller;
use App\Entity\tik\TkiPlanning;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CalendarApi extends Controller
{
    /**
     * @Route("/api/tik/calendar-fetch", name="calendar-fetch", methods={"GET", "POST"})
     */
    public function calendar(Request $request)
    {
        header("Content-type: application/json");
        // Vérifier si c'est une méthode GET
        if ($request->isMethod('GET')) {

            $userId = $this->sessionService->get('user_id');
            // $user = self::$em->getRepository(User::class)->find($userId);

            // Récupération des événements depuis la base de données
            $events = self::$em->getRepository(TkiPlanning::class)->findBy(['userId' => $userId]);

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

            echo json_encode($eventData);
            exit;
        }

        // Vérifier si c'est une méthode POST
        if ($request->isMethod('POST')) {
            // Récupérer les données JSON envoyées
            $data = json_decode($request->getContent(), true);

            // Validation des données
            if (isset($data['title'], $data['description'], $data['start'], $data['end'])) {
                
                $userId = $this->sessionService->get('user_id');
                $user = self::$em->getRepository(User::class)->find($userId);
                // Création de l'événement
                $event = new TkiPlanning();
                $event->setObjetDemande($data['title']);
                $event->setDetailDemande($data['description']);
                $event->setDateDebutPlanning(new \DateTime($data['start']));
                $event->setDateFinPlanning(new \DateTime($data['end']));
                $event->setUserId($user);

                // Sauvegarde dans la base de données
                $entityManager = self::$em;
                $entityManager->persist($event);
                $entityManager->flush();

                
                echo json_encode(['success' => true]);
                exit;
            }

            echo json_encode(['error' => 'Données invalides']);
            exit;
        }

        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(['error' => 'Méthode non autorisée']);
    }
}
