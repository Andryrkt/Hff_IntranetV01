<?php

namespace App\Service\genererPdf\da;

class PdfTableReappro
{
    public function generateTable(array $rows)
    {
        $html = '<table class="table text-center mt-3 align-middle">';
        $html .= $this->generateHeader();
        $html .= $this->generateBody($rows);
        $html .= '</table>';
        return $html;
    }

    private function generateHeader()
    {
        return '
        <thead class="table-dark align-middle">
            <tr>
                <th width="7%">Constructeur</th>
                <th width="13%">Référence</th>
                <th>Désignation</th>
                <th width="10%" class="text-end pe-3">PU</th>
                <th width="10%">Qté demandé</th>
                <th width="10%" class="text-end pe-3">Montant</th>
            </tr>
        </thead>
    ';
    }

    private function generateBody(array $rows)
    {
        $html = '<tbody>';

        // Si aucune ligne n’existe
        if (empty($rows)) {
            $html .= '<tr><td colspan="6" class="text-center fw-bold">N/A</td></tr>';
            $html .= '</tbody>';
            return $html;
        }

        foreach ($rows as $dal) {
            // Vérifie si tous les montants sont nuls
            $montantsZero = isset($dal['montantFormatted']) && (float)$dal['montantFormatted'] == 0;

            if ($montantsZero) {
                $html .= '<tr><td colspan="6" class="text-center fw-bold">N/A</td></tr>';
                continue;
            }

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($dal['artConstp'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($dal['artRefp'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($dal['artDesi'] ?? '') . '</td>';
            $html .= '<td class="text-end pe-3">' . htmlspecialchars($dal['PUFormatted'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($dal['qteDem'] ?? '') . '</td>';
            $html .= '<td class="text-end pe-3">' . htmlspecialchars($dal['montantFormatted'] ?? '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        return $html;
    }
}
