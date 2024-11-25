<?php

namespace App\Api\tik;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class CategorieApi extends Controller
{
    /**
     * @Route("/api/tik/calendar-fetch", name="calendar-fetch")
     *
     * @return void
     */
    public function calendar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Récupération des événements
            $stmt = $pdo->query('SELECT * FROM events');
            $events = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $events[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'start' => $row['start'],
                    'end' => $row['end']
                ];
            }
        
            $stmt = $pdo->query('SELECT * FROM ticketing');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $events[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'start' => $row['start'],
                    'end' => $row['end']
                ];
            }
        
            echo json_encode($events);
        
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Création d'un nouvel événement
            $data = json_decode(file_get_contents('php://input'), true);
        
            if (isset($data['title'], $data['start'], $data['end'])) {
                $stmt = $pdo->prepare('INSERT INTO events (title, start, end) VALUES (:title, :start, :end)');
                $stmt->execute([
                    ':title' => $data['title'],
                    ':start' => $data['start'],
                    ':end' => $data['end']
                ]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
            }
        }
    }
}