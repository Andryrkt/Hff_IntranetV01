<?php

namespace App\Controller\magasin\planning;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\planning\PlanningMagasinModel;

/**
 * @Route("/magasin/planning-commande-fournisseur")
 */
class PlanningMagasinController extends Controller
{
    private PlanningMagasinModel $planningMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new PlanningMagasinModel();
    }

    /**
     * @Route("", name = "interface_planning_cde_frn_magasin")
     */
    public function headPlanning()
    {
        $data = $this->planningMagasinModel->getPlanningMagasin();

        $uniqueMonths = $this->genererMoisAffiches();
        $preparedData = $this->preparerDonnees($data);

        return $this->render('magasin/planning/planning.html.twig', [
            'uniqueMonths' => $uniqueMonths,
            'preparedData' => $preparedData,
        ]);
    }

    /**
     * Génère la fenêtre de mois affichée dans l'entête du tableau
     * (6 mois précédents, le mois en cours, puis 2 mois suivants).
     */
    private function genererMoisAffiches(): array
    {
        $moisLabels = ['Janv', 'Févr', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        $moisCourant = new \DateTime('first day of this month');

        $uniqueMonths = [];
        for ($offset = -6; $offset <= 2; $offset++) {
            $mois = (clone $moisCourant)->modify($offset . ' month');

            $uniqueMonths[] = [
                'month'   => $moisLabels[(int) $mois->format('n') - 1],
                'year'    => (int) $mois->format('Y'),
                'key'     => $mois->format('Y-m'),
                'current' => $mois->format('Y-m') === $moisCourant->format('Y-m'),
            ];
        }

        return $uniqueMonths;
    }

    /**
     * Regroupe les commandes par fournisseur / agence-service et les répartit par mois.
     */
    private function preparerDonnees(array $data): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $cle = $item['numero_fournisseur'] . '|' . $item['agence_service'];

            if (!isset($grouped[$cle])) {
                $grouped[$cle] = [
                    'fournisseur'   => $item['nom_fournisseur'],
                    'agenceService' => $item['agence_service'],
                    'codeFourn'     => $item['numero_fournisseur'],
                    'commandes'     => [],
                ];
            }

            $timestamp = strtotime($item['date_commande']);
            if ($timestamp === false) {
                continue;
            }

            $moisCle = date('Y-m', $timestamp);

            $grouped[$cle]['commandes'][$moisCle][] = [
                'numero' => $item['numero_commande'],
                'statut' => $item['statut'],
            ];
        }

        return array_values($grouped);
    }
}
