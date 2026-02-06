<?php

namespace App\Traits;

use DateTime;

/** 
 * Gérer les jours ouvrables et date
 */
trait JoursOuvrablesTrait
{
    /**
     * Décale une date d'un certain nombre de jours ouvrables.
     *
     * @param int $nbJours Positif pour ajouter, négatif pour retirer
     * @param DateTime|null $date Date de référence (par défaut aujourd'hui)
     * @return DateTime
     */
    private function deplacerJoursOuvrables(int $nbJours, ?DateTime $date = null): DateTime
    {
        $date = $date ?? new DateTime();
        $joursDeplaces = 0;
        $direction = $nbJours >= 0 ? '+1 day' : '-1 day';

        while ($joursDeplaces < abs($nbJours)) {
            $date->modify($direction);
            if ($date->format('N') < 6) { // 1=Lundi, 7=Dimanche
                $joursDeplaces++;
            }
        }

        return $date;
    }

    /**
     * Ajoute un nombre de jours ouvrables.
     */
    public function ajouterJoursOuvrables(int $nbJours, ?DateTime $date = null): DateTime
    {
        return $this->deplacerJoursOuvrables($nbJours, $date);
    }

    /**
     * Retire un nombre de jours ouvrables.
     */
    public function retirerJoursOuvrables(int $nbJours, ?DateTime $date = null): DateTime
    {
        return $this->deplacerJoursOuvrables(-$nbJours, $date);
    }

    /**
     * Calcule la différence en jours ouvrables entre deux dates.
     *
     * @param DateTime $dateDebut
     * @param DateTime $dateFin
     * @return int Nombre de jours ouvrables entre les deux dates
     */
    public function differenceJoursOuvrables(DateTime $dateDebut, DateTime $dateFin): int
    {
        $diff = 0;
        // Cloner les dates pour éviter de modifier les originales
        $debut = clone $dateDebut;
        $fin = clone $dateFin;

        // Normaliser les dates à minuit pour éviter les problèmes de comparaison
        $debut->setTime(0, 0, 0);
        $fin->setTime(0, 0, 0);

        $dateActuelle = clone $debut;

        while ($dateActuelle != $fin) {
            $dateActuelle->modify(($debut < $fin ? '+1 day' : '-1 day'));
            if ($dateActuelle->format('N') < 6) {
                $diff++;
            }
        }

        return $diff;
    }
}
