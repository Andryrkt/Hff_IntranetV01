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
     * Génère la fenêtre de mois affichée dans l'entête du tableau : toujours 12 mois
     * (6 mois précédents, le mois en cours, puis 5 mois suivants).
     */
    private function genererMoisAffiches(): array
    {
        $moisLabels = ['Janv', 'Févr', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        $moisCourant = new \DateTime('first day of this month');

        $uniqueMonths = [];
        for ($offset = -6; $offset <= 5; $offset++) {
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
                    'fournisseur'   => trim($item['nom_fournisseur']),
                    'agenceService' => trim($item['agence_service']),
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
                'statut' => $this->normaliserStatut($item['statut']),
            ];
        }

        return array_values($grouped);
    }

    /**
     * Corrige le mojibake produit par DatabaseInformix::convertToUtf8() : cette méthode
     * teste 'ISO-8859-1' avant 'UTF-8' pour deviner l'encodage, et comme le Latin-1 accepte
     * n'importe quel octet, une chaîne déjà en UTF-8 (ex: "facturé") est ré-encodée comme si
     * elle était en Latin-1, produisant "facturÃ©". On annule ce ré-encodage ici.
     */
    private function normaliserStatut(string $statut): string
    {
        $statut = trim($statut);

        if (strpos($statut, 'Ã') === false && strpos($statut, 'Â') === false) {
            return $statut;
        }

        $repare = @mb_convert_encoding($statut, 'ISO-8859-1', 'UTF-8');
        if ($repare !== false && mb_check_encoding($repare, 'UTF-8') && $repare !== $statut) {
            return $repare;
        }

        return $statut;
    }
}
