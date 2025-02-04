<?php

namespace App\Api\tik;

use App\Controller\Controller;
use App\Entity\tik\TkiPlanning;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
            $events = self::$em->getRepository(TkiPlanning::class)->findAll();

            // Transformation des données en tableau JSON
            $eventData = [];
            foreach ($events as $event) {
                /**
                 * @var DemandeSupportInformatique $demandeSupportInfo ticket correspondant au planning
                 */
                $demandeSupportInfo = $event->getDemandeSupportInfo();
                /** 
                 * @var TkiPlanning $event planning de l'évènement
                 */
                $planningId         = $event->getId();
                $numeroTicket       = $event->getNumeroTicket();
                $objetDemande       = $event->getObjetDemande();
                $detailDemande      = $event->getDetailDemande();
                $dateDebutPlanning  = $event->getDateDebutPlanning();
                $dateFinPlanning    = $event->getDateFinPlanning();
                $partOfDay          = $demandeSupportInfo->getPartOfDay();
                $ticket             = $numeroTicket ? true : false;

                $eventData[] = [
                    'id'              => $planningId,
                    'title'           => ($ticket ? $numeroTicket . ' - ' : '') . $objetDemande,
                    'start'           => $dateDebutPlanning->format('Y-m-d H:i:s'),
                    'end'             => $dateFinPlanning->format('Y-m-d H:i:s'),
                    'backgroundColor' => $ticket ? '#fbbb01' : '#3788d8',
                    'classNames'      => $ticket ? 'planning-ticket' : '',
                    'extendedProps'   => $ticket ? [
                        'numeroTicket'    => $numeroTicket,
                        'objetDemande'    => $objetDemande,
                        'detailDemande'   => $detailDemande,
                        'id'              => $demandeSupportInfo->getId(),
                        'demandeur'       => $demandeSupportInfo->getUtilisateurDemandeur(),
                        'intervenant'     => $demandeSupportInfo->getNomIntervenant(),
                        'dateCreation'    => $demandeSupportInfo->getDateCreation()->format('d/m/Y'),
                        'dateFinSouhaite' => $demandeSupportInfo->getDateFinSouhaitee()->format('d/m/Y'),
                        'debutPlanning'   => $partOfDay === 'AM' ? '08:00' : '13:30',
                        'finPlanning'     => $partOfDay === 'AM' ? '12:00' : '17:30',
                        'categorie'       => $demandeSupportInfo->getCategorie()->getDescription(),
                    ] : [],
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
                $event->setUser($user);

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
