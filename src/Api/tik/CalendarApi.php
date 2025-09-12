<?php

namespace App\Api\tik;

use DateTime;
use Exception;
use App\Controller\Controller;
use App\Entity\tik\TkiPlanning;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\tik\TkiReplannification;
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
            $tab = $this->getSessionService()->get('tik_planning_search', []); // Pour le tri ou formulaire de recherche
            $userId = $this->getSessionService()->get('user_id');

            // Récupération des événements depuis la base de données
            $events = $this->getEntityManager()->getRepository(TkiPlanning::class)->findByFilter($tab);

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
                $intervenantId      = $demandeSupportInfo->getIntervenant()->getId(); // id de l'intervenant affilié au planning
                $ticket             = $numeroTicket ? true : false;

                $eventData[] = [
                    'id'              => $planningId,
                    'title'           => ($ticket ? $numeroTicket . ' - ' : '') . $objetDemande,
                    'start'           => $dateDebutPlanning->format('Y-m-d H:i:s'),
                    'end'             => $dateFinPlanning->format('Y-m-d H:i:s'),
                    'backgroundColor' => $ticket ? '#fbbb01' : '#3788d8',
                    'classNames'      => $ticket ? 'planning-ticket' : '',
                    'editable'        => ($ticket && $userId === $intervenantId) ? true : false, // si planning d'un ticket et l'id de l'intervenant === id de l'utilisateur connecté
                    'extendedProps'   => $ticket ? [
                        'numeroTicket'    => $numeroTicket,
                        'objetDemande'    => $objetDemande,
                        'detailDemande'   => $detailDemande,
                        'id'              => $demandeSupportInfo->getId(),
                        'demandeur'       => $demandeSupportInfo->getUtilisateurDemandeur(),
                        'intervenant'     => $demandeSupportInfo->getNomIntervenant(),
                        'dateCreation'    => $demandeSupportInfo->getDateCreation()->format('d/m/Y'),
                        'dateFinSouhaite' => $demandeSupportInfo->getDateFinSouhaitee()->format('d/m/Y'),
                        'debutPlanning'   => $dateDebutPlanning->format('H:i'),
                        'finPlanning'     => $dateFinPlanning->format('H:i'),
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

                $userId = $this->getSessionService()->get('user_id');
                $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
                // Création de l'événement
                $event = new TkiPlanning();
                $event->setObjetDemande($data['title']);
                $event->setDetailDemande($data['description']);
                $event->setDateDebutPlanning(new \DateTime($data['start']));
                $event->setDateFinPlanning(new \DateTime($data['end']));
                $event->setUser($user);

                // Sauvegarde dans la base de données
                $entityManager = $this->getEntityManager();
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

    /**  
     * @Route("/api/tik/data/calendar/{id<\d+>}", name="planning_data")
     */
    public function replanifier($id, Request $request)
    {
        header("Content-type: application/json");
        // Récupérer les données JSON envoyées
        $data = json_decode($request->getContent(), true);

        $dateDebut = new DateTime($data['dateDebut']);
        $dateFin = new DateTime($data['dateFin']);

        /** 
         * @var TkiPlanning $planning l'entité de TkiPlanning correspondant à l'id $id
         */
        $planning = $this->getEntityManager()->getRepository(TkiPlanning::class)->find($id);

        $demandeSupportInfo = $planning->getDemandeSupportInfo();

        try {
            $result = $this->saveSupportInfo($demandeSupportInfo, $dateDebut);

            // Vérifier si saveSupportInfo a bien fonctionné
            if ($result === false) {
                throw new Exception("Erreur lors de la mise à jour des informations par 'saveSupportInfo()'.");
            }

            $this->saveReplannification($demandeSupportInfo, $planning, $dateDebut, $dateFin);
            $this->savePlanning($planning, $dateDebut, $dateFin);

            $this->getEntityManager()->flush();

            echo json_encode([
                'status' => 'success',
                'saveSupportInfo' => $result,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function saveSupportInfo(DemandeSupportInformatique $supportInfo, $date)
    {
        try {
            $oldDateDebut = $supportInfo->getDateDebutPlanning();
            $oldDateFin = $supportInfo->getDateFinPlanning();
            $updated = false;
            $i = 0;

            if ($oldDateDebut > $date) {
                $supportInfo->setDateDebutPlanning($date);
                $updated = true;
                $i++;
            }
            if ($date > $oldDateFin) {
                $supportInfo->setDateFinPlanning($date);
                $updated = true;
                $i++;
            }
            if ($updated) {
                $this->getEntityManager()->persist($supportInfo);
            }
            return $i; // Retourne le nombre de modifications effectuées
        } catch (Exception $e) {
            return false; // En cas d'erreur, retourne false
        }
    }

    private function savePlanning(TkiPlanning $planning, $dateDebut, $dateFin)
    {
        try {
            if (!$planning) {
                throw new Exception("Objet planning invalide.");
            }

            $planning
                ->setDateDebutPlanning($dateDebut)
                ->setDateFinPlanning($dateFin);

            $this->getEntityManager()->persist($planning);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la sauvegarde du planning: " . $e->getMessage());
        }
    }

    private function saveReplannification(DemandeSupportInformatique $supportInfo, TkiPlanning $planning, $dateDebut, $dateFin)
    {
        try {
            if (!$supportInfo || !$planning) {
                throw new Exception("Informations de support ou planning invalides.");
            }

            $replanification = new TkiReplannification();
            $replanification
                ->setNumeroTicket($supportInfo->getNumeroTicket())
                ->setOldDateDebutPlanning($planning->getDateDebutPlanning())
                ->setOldDateFinPlanning($planning->getDateFinPlanning())
                ->setNewDateDebutPlanning($dateDebut)
                ->setNewDateFinPlanning($dateFin)
                ->setDemandeSupportInfo($supportInfo)
                ->setUser($planning->getUser())
                ->setPlanning($planning);

            $this->getEntityManager()->persist($replanification);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la replanification: " . $e->getMessage());
        }
    }
}
