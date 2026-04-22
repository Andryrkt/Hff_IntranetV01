<?php

namespace App\Service\genererPdf\da;

use App\Controller\Traits\FormatageTrait;

class PdfTableHistoriqueLivraisonBAP
{
    use FormatageTrait;

    public function generateTable(array $data)
    {
        $html = '<table border="0" cellpadding="2" cellspacing="0" style="font-size: 9px;">';
        $html .= $this->generateHeader();
        $html .= $this->generateBody($data);
        $html .= '</table>';
        return $html;
    }

    private function generateHeader(): string
    {
        $columns = [
            $this->createTableCell('center', '20%', 'N° Livraison IPS'),
            $this->createTableCell('left', '25%', 'Référence'),
            $this->createTableCell('center', '20%', 'Date livraison IPS'),
            $this->createTableCell('right', '20%', 'Montant'),
        ];

        return sprintf(
            '<thead><tr style="background-color: #D3D3D3; font-weight: bold;">%s</tr></thead>',
            implode('', $columns)
        );
    }

    private function createTableCell(string $align, string $width, string $label, string $style = "", bool $header = true): string
    {
        $tag = $header ? 'th' : 'td';
        return sprintf('<%s align="%s" style="width:%s; %s">%s</%s>', $tag, $align, $width, $style, $label, $tag);
    }

    private function generateBody(array $data): string
    {
        $rows = [];

        foreach ($data as $historique) {
            $rows[] = $this->createRow($historique);
        }

        return '<tbody>' . implode('', $rows) . '</tbody>';
    }

    private function createRow(array $historique): string
    {
        $date = $historique["date_clot"] ? date("d/m/Y", strtotime($historique["date_clot"])) : "-";
        $montant = $this->formaterPrix($historique["montant_fac_bl"] ?? 0);
        $cells = [
            $this->createTableCell('center', '20%', $historique['num_liv'], "", false),
            $this->createTableCell('left', '25%', $historique['ref_fac_bl'], "", false),
            $this->createTableCell('center', '20%', $date, "", false),
            $this->createTableCell('right', '20%', $montant, "", false),
        ];

        return '<tr>' . implode('', $cells) . '</tr>';
    }
}
