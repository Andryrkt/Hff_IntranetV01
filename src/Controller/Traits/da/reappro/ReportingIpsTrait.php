<?php

namespace App\Controller\Traits\da\reappro;

use DateTime;
use App\Model\da\reappro\ReportingIpsModel;

trait ReportingIpsTrait
{

    private function calculQteEtMontantTotals(array $reportingIps): array
    {
        $result = [
            'qte_totale' => 0,
            'montant_total' => 0
        ];
        foreach ($reportingIps as $item) {
            $result['qte_totale'] += $item['qte_demande'];
            $result['montant_total'] += $item['montant'];
        }
        return $result;
    }

    private function getData(array $criterias): array
    {
        $reportingIpsModel = new ReportingIpsModel();
        $reportingIps = $reportingIpsModel->getReportingData($criterias);

        // 3. Agréger les données
        $processedData = [];
        foreach ($reportingIps as $row) {
            $refProduit = trim($row['reference_produit']);
            if (empty($refProduit)) continue;

            if (!isset($processedData[$refProduit])) {
                $processedData[$refProduit] = [
                    'agence_service_debiteur' => trim($row['agence_debiteur']) . '-' . trim($row['service_debiteur']),
                    'numero_facture' => trim($row['numero_facture']),
                    'client' => trim($row['client']),
                    'constructeur' => trim($row['constructeur']),
                    'reference_produit' => $refProduit,
                    'designation_produit' => trim($row['designation_produit']),
                    'monthly_data' => [],
                ];
            }

            try {
                $date = new DateTime(trim($row['date_commande']));
                $monthKey = $date->format('Y-m');

                if (!isset($processedData[$refProduit]['monthly_data'][$monthKey])) {
                    $processedData[$refProduit]['monthly_data'][$monthKey] = [
                        'total_montant' => 0,
                        'total_qte' => 0,
                        'annee' => $date->format('Y'),
                        'mois' => $date->format('n'),
                    ];
                }

                $processedData[$refProduit]['monthly_data'][$monthKey]['total_montant'] += (float)$row['montant'];
                $processedData[$refProduit]['monthly_data'][$monthKey]['total_qte'] += (int)$row['qte_demande'];
            } catch (\Exception $e) {
                // Ignorer les lignes avec des dates invalides
            }
        }
        // 4. Filtrer par période avec RollingMonthsService
        $monthExtractor = fn(array $productData): array => array_values($productData['monthly_data']);

        $results = $this->rollingMonthsService->filterDataByPeriod(
            array_values($processedData),
            $criterias['periodType'],
            $monthExtractor,
            new DateTime()
        );

        // 5. Calculer les totaux
        $monthKeys = array_column($results['months'], 'key');
        $totals = array_fill_keys($monthKeys, ['qte' => 0, 'montant' => 0]);

        foreach ($results['data'] as $product) {
            foreach ($product['filteredMonths'] as $monthData) {
                $monthKey = $monthData['monthKey'];
                if (isset($totals[$monthKey])) {
                    $totals[$monthKey]['qte'] += $monthData['details']['total_qte'];
                    $totals[$monthKey]['montant'] += $monthData['details']['total_montant'];
                }
            }
        }
        //==================================================================================


        return [
            'results' => $results,
            'totals' => $totals
        ];
    }
}
