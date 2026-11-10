<?php

namespace App\Twig;

use App\Entity\admin\historisation\documentOperation\SuiviOperationTache;
use Doctrine\Persistence\ManagerRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * taches_a_planifier('nom') : tâches du suivi des opérations assignées à l'utilisateur
 * et pas encore planifiées, pour la cloche de la barre de navigation (partials/_navigation.html.twig)
 */
class NotificationExtension extends AbstractExtension
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('taches_a_planifier', [$this, 'tachesAPlanifier']),
            new TwigFunction('taches_a_traiter', [$this, 'tachesATraiter']),
        ];
    }

    /**
     * @return SuiviOperationTache[] à faire aujourd'hui ou en retard
     */
    public function tachesATraiter(?string $nomUtilisateur): array
    {
        if (!$nomUtilisateur) {
            return [];
        }

        return $this->doctrine->getManager()
            ->getRepository(SuiviOperationTache::class)
            ->aTraiter($nomUtilisateur);
    }

    /**
     * @return SuiviOperationTache[]
     */
    public function tachesAPlanifier(?string $nomUtilisateur): array
    {
        if (!$nomUtilisateur) {
            return [];
        }

        return $this->doctrine->getManager()
            ->getRepository(SuiviOperationTache::class)
            ->aPlanifier($nomUtilisateur);
    }
}
