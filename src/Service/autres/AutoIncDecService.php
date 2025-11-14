<?php

namespace App\Service\autres;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\Application;

class AutoIncDecService
{

    /**
     * Génère un numéro automatique (incrément ou décrément) pour une application.
     *
     * Format : {CodeApp}{AnnéeMois}{NuméroSéquentiel}
     *
     * @param string $nomDemande Le code de l'application (ex: "CAS")
     * @param string|null $dernierNumApp le dernière numero dans la table Application
     * @param bool $increment Si vrai => incrémente, sinon => décrémente
     * @return string
     */
    public static function autoGenerateNumero(string $nomDemande, ?string $dernierNumApp = null, bool $increment = true): string
    {
        $anneeCourante = date('y');   // Ex: 25
        $moisCourant = date('m');     // Ex: 10
        $anneeMoisCourant = $anneeCourante . $moisCourant; // Ex: 2510

        $maxNum = $dernierNumApp;
        if (!$dernierNumApp) {
            // Si aucun enregistrement précédent
            return $nomDemande . $anneeMoisCourant . '0001';
        }

        // Extraction des parties du numéro
        $numSequential = (int) substr($maxNum, -4);     // ex: 0005
        $dateAnneeMoisNum = substr($maxNum, -8);        // ex: 25100005
        $dateYearsMonthOfMax = substr($dateAnneeMoisNum, 0, 4); // ex: 2510

        if ($dateYearsMonthOfMax == $anneeMoisCourant) {
            $numSequential = $increment ? $numSequential + 1 : $numSequential - 1;
        } else {
            if ($anneeMoisCourant > $dateYearsMonthOfMax) {
                $numSequential = $increment ? 1 : 9999;
            }
        }

        // Formate le numéro avec 4 chiffres (ex: 0005)
        $numSequentialFormate = str_pad($numSequential, 4, '0', STR_PAD_LEFT);

        return $nomDemande . $anneeMoisCourant . $numSequentialFormate;
    }


    /**
     * Met à jour la dernière ID utilisée pour une application donnée.
     *
     * @param string $codeApp Le code de l'application à mettre à jour.
     * @param string $numero  La nouvelle valeur du champ `derniereId`.
     */
    public static function mettreAJourDerniereIdApplication(Application $application, EntityManagerInterface $em, string $numero): void
    {
        $application->setDerniereId($numero);
        $em->persist($application);
    }



    /**
     * Methode qui permet d'incrémenter un nombre de pas 1 lorqu'il est appeler
     *
     * @param integer|null $num
     * @return integer
     */
    public static function autoIncrement(?int $num): int
    {
        if ($num === null) {
            $num = 0;
        }
        return $num + 1;
    }
}
