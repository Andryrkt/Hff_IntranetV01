<?php

namespace App\Controller\Traits\da\reappro;

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

        ['qte_totale' => $qteTotale, 'montant_total' => $montantTotal] = $this->calculQteEtMontantTotals($reportingIps);

        return [
            'reportingIps' => $reportingIps,
            'qteTotale' => $qteTotale,
            'montantTotal' => $montantTotal
        ];
    }
}