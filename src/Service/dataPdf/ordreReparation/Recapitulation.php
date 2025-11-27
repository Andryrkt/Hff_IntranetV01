<?php

namespace App\Service\dataPdf\ordreReparation;

use App\Model\dit\DitModel;

class Recapitulation
{
    private DitModel $ditModel;

    public function __construct()
    {
        $this->ditModel = new DitModel;
    }

    public function getData(string $numOr): array
    {
        $data = $this->getAllData($numOr);
        return [
            'header' => $this->getHeaderConfig(),
            'body'   => $data["body"],
            'footer' => $data["footer"],
        ];
    }

    private function getHeaderConfig(): array
    {
        return [
            [
                'key'          => 'itv',
                'label'        => 'ITV',
                'width'        => 40,
                'style'        => 'font-weight: 900;',
                'header_style' => 'font-weight: 900;',
                'cell_style'   => '',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'libelleItv',
                'label'        => 'Libellé ITV',
                'width'        => 150,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => 'text-align: left;',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'mttTotal',
                'label'        => 'Mtt Total',
                'width'        => 70,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'mttPieces',
                'label'        => 'Mtt Pièces',
                'width'        => 60,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'mttMo',
                'label'        => 'Mtt MO',
                'width'        => 60,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'mttSt',
                'label'        => 'Mtt ST',
                'width'        => 80,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'mttLub',
                'label'        => 'Mtt LUB',
                'width'        => 80,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'mttAutres',
                'label'        => 'Mtt Autres',
                'width'        => 80,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ]
        ];
    }

    private function getAllData(string $numOr): array
    {
        $data = ["body" => [], "footer" => ['itv' => 'TOTAL', 'mttTotal' => 0, 'mttPieces' => 0, 'mttMo' => 0, 'mttSt' => 0, 'mttLub' => 0, 'mttAutres' => 0,]];
        $orSoumisAValidation = $this->ditModel->recupOrSoumisValidation($numOr);

        foreach ($orSoumisAValidation as $orSoumis) {
            $data["body"][] = [
                'itv'        => $orSoumis["numero_itv"],
                'libelleItv' => $orSoumis["libelle_itv"],
                'mttTotal'   => $orSoumis["montant_itv"],
                'mttPieces'  => $orSoumis["montant_piece"],
                'mttMo'      => $orSoumis["montant_mo"],
                'mttSt'      => $orSoumis["montant_achats_locaux"],
                'mttLub'     => $orSoumis["montant_lubrifiants"],
                'mttAutres'  => $orSoumis["montant_divers"]
            ];
            $data["footer"]["mttTotal"]  += $orSoumis["montant_itv"];
            $data["footer"]["mttPieces"] += $orSoumis["montant_piece"];
            $data["footer"]["mttMo"]     += $orSoumis["montant_mo"];
            $data["footer"]["mttSt"]     += $orSoumis["montant_achats_locaux"];
            $data["footer"]["mttLub"]    += $orSoumis["montant_lubrifiants"];
            $data["footer"]["mttAutres"] += $orSoumis["montant_divers"];
        }

        return $data;
    }
}
