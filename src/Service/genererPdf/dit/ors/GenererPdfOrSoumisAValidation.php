<?php

namespace App\Service\genererPdf\dit\ors;

use App\Service\genererPdf\HeaderPdf;
use App\Service\genererPdf\GeneratePdf;
use App\Controller\Traits\FormatageTrait;
use App\Service\genererPdf\PdfTableGeneratorFlexible;

class GenererPdfOrSoumisAValidation extends GeneratePdf
{
    use FormatageTrait;

    // ORDRE DE REPARATION (OR)
    public function copyToDw($filename, string $numDit)
    {
        $cheminFichierDistant = $this->baseCheminDocuware . 'ORDRE_DE_MISSION/' . $filename;
        $cheminDestinationLocal = $this->baseCheminDuFichier . 'dit/' . $numDit . '/' . $filename;
        copy($cheminDestinationLocal, $cheminFichierDistant);
    }

    /**
     * generer pdf pour la soumission OR
     */
    function GenererPdf($ditInsertionOr, $montantPdf, $quelqueaffichage, $email, string $suffix, array $pieceFaibleAchat = [], string $nomAvecCheminFichier)
    {
        $pdf = new HeaderPdf($email);
        $tableGenerator = new PdfTableGeneratorFlexible();

        $tableGenerator->setOptions([
            'table_attributes' => 'border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;"',
            'header_row_style' => 'background-color: #D3D3D3;',
            'footer_row_style' => 'background-color: #D3D3D3;'
        ]);


        $pdf->AddPage();


        $pdf->setFont('helvetica', 'B', 17);
        $pdf->Cell(0, 6, 'Validation OR', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        // Début du bloc
        $pdf->setFont('helvetica', '', 10);
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->setFont('helvetica', 'B', 10);
        // Date de soumission
        $pdf->Cell(45, 6, 'Date soumission : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditInsertionOr->getDateSoumission()->format('d/m/Y'), 0, 1, '', false, '', 0, false, 'T', 'M');

        // numero devis
        $pdf->setAbsX(130);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(20, 6, 'N° Devis :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(0, 6, $quelqueaffichage['numDevis'][0]['seor_numdev'] === '' ? 0 : $quelqueaffichage['numDevis'][0]['seor_numdev'], 0, 0, '', false, '', 0, false, 'T', 'M');

        // Numéro OR
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Numéro OR : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditInsertionOr->getNumeroOR(), 0, 1, '', false, '', 0, false, 'T', 'M');

        //sortie pol
        $pdf->setAbsX(130);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(20, 6, 'Sortie POL : ', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(0, 6, ' ' . $quelqueaffichage['pol'], 0, 0, '', false, '', 0, false, 'T', 'M');

        // Version à valider
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Version à valider : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $ditInsertionOr->getNumeroVersion(), 0, 1, '', false, '', 0, false, 'T', 'M');

        // sortie magasin
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Sortie magasin : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $quelqueaffichage['sortieMagasin'], 0, 1, '', false, '', 0, false, 'T', 'M');

        // Achat locaux
        $pdf->SetXY($startX, $pdf->GetY() + 2);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(45, 6, 'Achat locaux : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(50, 6, $quelqueaffichage['achatLocaux'], 0, 1, '', false, '', 0, false, 'T', 'M');

        // Fin du bloc
        $pdf->Ln(10, true);

        // ================================================================================================
        // Tableau pour la situation de l'OR

        $html = $tableGenerator->generateTable(
            $this->headerSituationOr(),
            $montantPdf['avantApres'],
            $this->footerSituationOr($montantPdf)
        );

        $pdf->writeHTML($html, true, false, true, false, '');

        //$pdf->Ln(10, true);
        //===========================================================================================
        //Titre: Controle à faire
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'Contrôle à faire (par rapport dernière version) : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->Ln(10, true);

        $pdf->setFont('helvetica', '', 10);
        //Nouvelle intervention
        $pdf->Cell(45, 6, ' - Nouvelle intervention : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $montantPdf['nombreStatutNouvEtSupp']['nbrNouv'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //intervention supprimer

        $pdf->Cell(45, 6, ' - Intervention supprimée : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $montantPdf['nombreStatutNouvEtSupp']['nbrSupp'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //nombre ligne modifiée
        $pdf->Cell(45, 6, ' - Nombre ligne modifiée :', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $montantPdf['nombreStatutNouvEtSupp']['nbrModif'], 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(5, true);

        //montant total modifié
        $pdf->Cell(45, 6, ' - Montant total modifié :', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 5, $this->formatNumber($montantPdf['nombreStatutNouvEtSupp']['mttModif']), 0, 0, '', false, '', 0, false, 'T', 'M');

        $pdf->Ln(10, true);

        //==========================================================================================================
        //Titre: Récapitulation de l'OR
        $this->addTitle($pdf, "Récapitulation de l'OR", 'helvetica',  'B', 10, 'L',  5);


        $pdf->setFont('helvetica', '', 12);
        $html = $tableGenerator->generateTable(
            $this->headerRecapitulationOR(),
            $montantPdf['recapOr'],
            $this->footerRecapitulationOR($montantPdf)
        );

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(10, true);
        //==========================================================================================================
        //Titre: Pièce(s) à faible activité d'achat
        $pdf->SetTextColor(255, 0, 0);
        $this->addTitle($pdf, empty($pieceFaibleAchat) ? '' : "Attention : les prix des pièces ci-dessous sont susceptibles d’augmenter. Merci de les confirmer auprès du service Magasin.", 'helvetica', 'B', 10, 'L', 1);

        $pdf->SetTextColor(0, 0, 0);
        if (!empty($pieceFaibleAchat)) {
            $pdf->setFont('helvetica', '', 12);
            $html = $tableGenerator->generateTable(
                $this->headerPieceFaibleActivite(),
                $pieceFaibleAchat,
                []
            );

            $pdf->writeHTML($html, true, false, true, false, '');
        }
        //==========================================================================================================
        //Titre: Observation
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->Cell(30, 6, 'Observation : ', 0, 0, 'L', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->MultiCell(164, 60, $ditInsertionOr->getObservation(), 0, '', 0, 0, '', '', true);

        //==========================================================================================================


        $pdf->Output($nomAvecCheminFichier, 'F');
    }

    /**===============================================================
     * -------- Pour le tableau Situation de l'OR ------------------
     *================================================================*/

    private function headerSituationOr(): array
    {
        return  [
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
                'key'          => 'datePlanning',
                'label'        => 'Date pla',
                'width'        => 50,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => 'text-align: left;',
                'footer_style' => 'font-weight: bold;',
                'type'         => 'date'
            ],
            [
                'key'          => 'nbLigAv',
                'label'        => 'Nb Lig av',
                'width'        => 50,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => '',
                'footer_style' => 'font-weight: bold;'
            ],
            [
                'key'          => 'nbLigAp',
                'label'        => 'Nb Lig ap',
                'width'        => 50,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => '',
                'footer_style' => 'font-weight: bold;'
            ],
            [
                'key'          => 'mttTotalAv',
                'label'        => 'Mtt Total av',
                'width'        => 80,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'mttTotalAp',
                'label'        => 'Mtt total ap',
                'width'        => 80,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: bold; text-align: right;',
                'type'         => 'number'
            ],
            [
                'key'          => 'statut',
                'label'        => 'Statut',
                'width'        => 40,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold; text-align: center;',
                'cell_style'   => 'text-align: left;',
                'footer_style' => 'font-weight: bold; text-align: center;',
                'styler'       => function ($value, $row) {
                    switch ($value) {
                        case 'Supp':
                            return 'background-color: #FF0000;';
                        case 'Modif':
                            return 'background-color: #FFFF00;';
                        case 'Nouv':
                            return 'background-color: #00FF00;';
                        default:
                            return '';
                    }
                }
            ]
        ];
    }

    private function footerSituationOr(array $montantPdf): array
    {
        return [
            'itv'              => '',
            'libelleItv'       => '',
            'datePlanning'     => 'TOTAL',
            'nbLigAv'          => $montantPdf['totalAvantApres']['totalNbLigAv'] ?? '',
            'nbLigAp'          => $montantPdf['totalAvantApres']['totalNbLigAp'] ?? '',
            'mttTotalAv'       => $montantPdf['totalAvantApres']['totalMttTotalAv'] ?? '',
            'mttTotalAp'       => $montantPdf['totalAvantApres']['totalMttTotalAp'] ?? '',
            'statut'           => ''
        ];
    }

    private function getDynamicStyle($key, $value)
    {
        $styles = '';
        if ($key === 'statut') {
            switch ($value) {
                case 'Supp':
                    $styles .= 'background-color: #FF0000;';
                    break;
                case 'Modif':
                    $styles .= 'background-color: #FFFF00;';
                    break;
                case 'Nouv':
                    $styles .= 'background-color: #00FF00;';
                    break;
            }
        }
        return $styles;
    }

    /**===============================================================
     * -------- Pour le tableau Recapitulation de l'OR ------------------
     *================================================================*/

    private function headerRecapitulationOR(): array
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

    private function footerRecapitulationOR(array $montantPdf): array
    {
        return [
            'itv'              => 'TOTAL',
            'mttTotal'         => $montantPdf['totalRecapOr']['montant_itv'] ?? '',
            'mttPieces'        => $montantPdf['totalRecapOr']['montant_piece'] ?? '',
            'mttMo'            => $montantPdf['totalRecapOr']['montant_mo'] ?? '',
            'mttSt'            => $montantPdf['totalRecapOr']['montant_achats_locaux'] ?? '',
            'mttLub'           => $montantPdf['totalRecapOr']['montant_lubrifiants'] ?? '',
            'mttAutres'        => $montantPdf['totalRecapOr']['montant_frais_divers'] ?? ''
        ];
    }

    /**============================================================================
     * -------- Pour le tableau pièces à faible activité d'achat ------------------
     *=============================================================================*/
    private function headerPieceFaibleActivite()
    {
        return [
            [
                'key'          => 'numero_itv',
                'label'        => 'ITV',
                'width'        => 40,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => '',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'libelle_itv',
                'label'        => 'Libellé ITV',
                'width'        => 150,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => 'text-align: left;',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'constructeur',
                'label'        => 'Const',
                'width'        => 40,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => '',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'reference',
                'label'        => 'Réfp.',
                'width'        => 40,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => '',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'designation',
                'label'        => 'Designation',
                'width'        => 150,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => 'text-align: left;',
                'footer_style' => 'font-weight: 900;'
            ],
            [
                'key'          => 'pmp',
                'label'        => 'Pmp',
                'width'        => 80,
                'style'        => 'font-weight: bold;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => 'text-align: right;',
                'footer_style' => 'font-weight: 900;',
                'type'         => 'number'
            ],
            [
                'key'          => 'date_derniere_cde',
                'label'        => 'Date dern cmd',
                'width'        => 50,
                'style'        => 'font-weight: bold; text-align: center;',
                'header_style' => 'font-weight: bold;',
                'cell_style'   => 'text-align: center;',
                'footer_style' => 'font-weight: bold;',
                'default_value' => 'jamais commandé',
                'type'         => 'date'
            ],
        ];
    }
}
