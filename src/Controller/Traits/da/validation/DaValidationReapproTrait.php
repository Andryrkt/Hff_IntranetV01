<?php

namespace App\Controller\Traits\da\validation;

use DateTime;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeAppro;
use App\Model\da\DaReapproModel;
use App\Repository\da\DaObservationRepository;

trait DaValidationReapproTrait
{
    use DaValidationTrait;
    private DaObservationRepository $daObservationRepository;
    private DaReapproModel $daReapproModel;
    private string $cheminDeBase;

    //==================================================================================================
    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationReapproTrait(): void
    {
        $this->initDaTrait();
        $em = $this->getEntityManager();
        $this->daObservationRepository = $em->getRepository(DaObservation::class);
        $this->daReapproModel = new DaReapproModel;
        $this->cheminDeBase = $_ENV['BASE_PATH_FICHIER'] . '/da/';
    }
    //==================================================================================================

    /**
     * Cette fonction calcule dynamiquement la période de 12 mois glissants pour un SQL BETWEEN.
     * Elle retourne :
     *   - le premier jour du mois il y a 12 mois
     *   - le dernier jour du mois précédent
     *
     * Exemple : si aujourd'hui = 28/10/2025
     *   start = 2024-10-01
     *   end   = 2025-09-30
     *
     * @return array ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD']
     */
    private function getLast12MonthsRange(): array
    {
        $startDate = new DateTime('first day of -12 months');
        $endDate = new DateTime('last day of last month');
        return [
            'start' => $startDate->format('Y-m-d'),
            'end'   => $endDate->format('Y-m-d')
        ];
    }

    /**
     * Génère une liste de tous les mois entre deux dates.
     * Chaque mois est formaté en 'MM-YYYY'.
     *
     * @param string $startDate Date de début au format 'Y-m-d' (ex: 2024-10-01)
     * @param string $endDate   Date de fin au format 'Y-m-d' (ex: 2025-09-30)
     * @return array            Tableau de mois ['10-2024','11-2024', ...]
     */
    private function getMonthsList(string $startDate, string $endDate): array
    {
        $months = [];
        $monthsLabel = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        // Convertir les chaînes en objets DateTime
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        // S'assurer que l'on prend le premier jour du mois de fin
        $end->modify('first day of this month');

        // Boucle sur chaque mois
        while ($start <= $end) {
            $month = $start->format('m-Y'); // ex: 10-2024
            [$mois, $annee] = explode('-', $month);
            $months[] = $monthsLabel[$mois - 1]  . '-' . $annee;
            $start->modify('+1 month');
        }

        return $months;
    }

    public function getHistoriqueConsommation(DemandeAppro $demandeAppro, array $dateRange, array $monthsList)
    {
        $result = [];
        $codeAgence = $demandeAppro->getAgenceEmetteur()->getCodeAgence();
        $codeService = $demandeAppro->getServiceEmetteur()->getCodeService();
        $datas = $this->daReapproModel->getHistoriqueConsommation($dateRange, $codeAgence, $codeService);

        foreach ($datas as $row) {
            // Clé unique par produit
            $key = md5($row['cst'] . '|' . $row['refp'] . '|' . $row['desi']);

            // Initialiser si pas déjà existant
            if (!isset($result[$key])) {
                $result[$key] = [
                    'cst'      => $row['cst'],
                    'refp'     => $row['refp'],
                    'desi'     => $row['desi'],
                    'qteTotal' => 0,
                    'qte'      => [] // sous-tableau par mois
                ];
                // Initialiser tous les mois à 0
                foreach ($monthsList as $mois) {
                    $result[$key]['qte'][$mois] = 0;
                }
            }

            // Ajouter la quantité pour le mois correspondant
            $mois = $row['mois_annee'];
            $qte = floatval($row['qte_fac']);
            $result[$key]['qteTotal'] += $qte;
            $result[$key]['qte'][$mois] += $qte;
        }

        return $result;
    }
}
