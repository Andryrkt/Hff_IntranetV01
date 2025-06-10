<?php

namespace App\Controller\Traits\da;

use DateTime;


trait DaTrait
{
    private function ajoutNbrJourRestant($dalDernieresVersions)
    {
        foreach ($dalDernieresVersions as $dal) {
            // --- 1. Mettre les deux dates à minuit (00:00:00) ---
            $dateFin     = clone $dal->getDateFinSouhaite(); // on clone pour ne pas modifier l'objet de l'entity
            $dateFin->setTime(0, 0, 0);                      // Y-m-d 00:00:00

            $aujourdhui  = new DateTime('today');            // 'today' crée déjà la date du jour à 00:00:00

            // --- 2. Calculer la différence ---
            $interval = $aujourdhui->diff($dateFin);         // toujours positif dans $interval->days
            $days     = $interval->invert ? -$interval->days // invert = 1 si $dateFin est passée
                :  $interval->days;

            // --- 3. Enregistrer ---
            $dal->setJoursDispo($days);
        }
    }
}
