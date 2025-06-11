<?php

namespace App\Controller\Traits\da;

use App\Entity\da\DemandeApproL;
use DateTime;

trait DemandeApproTrait
{
    /**
     * Permet de calculer le nombre de jours disponibles avant la date de fin souhaitée
     *
     * @param DemandeApproL $dal
     * @return int Nombre de jours disponibles (positif si la date n'est pas encore passée, négatif si elle l'est)
     */
    public function getJoursRestants(DemandeApproL $dal): int
    {
        // --- 1. Mettre les deux dates à minuit (00:00:00) ---
        $dateFin     = clone $dal->getDateFinSouhaite(); // on clone pour ne pas modifier l'objet de l'entity
        $dateFin->setTime(0, 0, 0);                      // Y-m-d 00:00:00

        $aujourdhui  = new DateTime('today');            // 'today' crée déjà la date du jour à 00:00:00

        // --- 2. Calculer la différence ---
        $interval = $aujourdhui->diff($dateFin);         // toujours positif dans $interval->days
        $days     = $interval->invert ? -$interval->days // invert = 1 si $dateFin est passée
            :  $interval->days;

        // --- 3. Retourner la valeur ---
        return $days;
    }
}
