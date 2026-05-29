<?php

namespace App\Service\genererPdf\ddp;

use App\Controller\Traits\FormatageTrait;
use App\Dto\ddp\DdpRecapDto;

class PdfTableHistoriqueDdpBAP
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
            $this->createTableCell('center', '10%', 'Date'),
            $this->createTableCell('center', '15%', 'Numéro'),
            $this->createTableCell('left', '15%', 'Type'),
            $this->createTableCell('left', '10%', 'N° facture'),
            $this->createTableCell('left', '15%', 'N° facture IPS'),
            $this->createTableCell('center', '5%', '%'),
            $this->createTableCell('right', '15%', 'Montant HT'),
            $this->createTableCell('left', '10%', 'Statut'),
            $this->createTableCell('left', '10%', 'Emetteur'),
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

    private function createRow(DdpRecapDto $historique): string
    {
        // $date = $historique["date_clot"] ? date("d/m/Y", strtotime($historique["date_clot"])) : "-";
        $montant = $this->formaterPrix($historique->montant ?? 0);
        $cells = [
            $this->createTableCell('center', '10%', $historique->dateCreation, "", false),
            $this->createTableCell('center', '15%', $historique->numeroDdp, "", false),
            $this->createTableCell('left', '15%', $historique->typeDemande, "", false),
            $this->createTableCell('left', '10%', $historique->numeroFacture ?? "-", "", false),
            $this->createTableCell('left', '15%', $historique->numeroFactureIps ?? "-", "", false),
            $this->createTableCell('center', '5%', $historique->ratio . "%" ?? "-", "", false),
            $this->createTableCell('right', '15%', $montant ?? "-", "", false),
            $this->createTableCell('left', '10%', $historique->statut ?? "-", "", false),
            $this->createTableCell('left', '10%', $historique->emetteur ?? "-", "", false),
        ];

        return '<tr>' . implode('', $cells) . '</tr>';
    }
}
