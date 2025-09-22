<?php

namespace App\Service\genererPdf;

use TCPDF;
use App\Controller\Traits\FormatageTrait;

class GenererPdfBadm extends GeneratePdf
{
    use FormatageTrait;

    private $pdf;

    public function __construct(
        TCPDF $pdf,
        string $baseCheminDuFichier = null
    ) {
        parent::__construct($baseCheminDuFichier);
        $this->pdf = $pdf;
    }

    /**
     * Generer pdf badm 
     */
    function genererPdfBadm(array $tab, array $orDb = [], array $or2 = [])
    {
        $pdf = $this->pdf;
        // ... (le reste de la méthode genererPdfBadm reste identique)

        $Dossier = ($_ENV['BASE_PATH_FICHIER'] ?? '') . '/bdm/';
        $pdf->Output($Dossier . $tab['Num_BDM'] . '_' . $tab['Agence_Service_Emetteur_Non_separer'] . '.pdf', 'F');
    }

    /**
     * Ajout d'image dans le pdf
     *
     * @param [type] $pdf
     * @param [type] $tab
     * @return void
     */
    public function AjoutImage($pdf, $tab)
    {
        $pdf->AddPage();
        $imagePath = $tab['image'];
        if ($tab['extension'] === 'JPG') {
            $pdf->Image($imagePath, 15, 25, 180, 150, 'JPG', '', '', true, 75, '', false, false, 0, false, false, false);
        } elseif ($tab['extension'] === 'JEPG') {
            $pdf->Image($imagePath, 15, 25, 180, 150, 'JEPG', '', '', true, 75, '', false, false, 0, false, false, false);
        } elseif ($tab['extension'] === 'PNG') {
            $pdf->Image($imagePath, 15, 25, 180, 150, 'PNG', '', '', true, 75, '', false, false, 0, false, false, false);
        }
    }


    /**
     * Recuperation et affichage des or dans une tableau
     *
     * @param [type] $pdf
     * @param [type] $orDb
     * @return void
     */
    public function affichageListOr($pdf, $orDb)
    {
        $pdf->AddPage('L');

        $header1 = ['Agence', 'Service', 'numor', 'Date', 'ref', 'interv', 'intitulé travaux', 'Ag/Serv débiteur', 'montant total', 'montant pièces', 'montant piece livrées'];

        // Commencer le tableau HTML
        $html = '<h2 style="text-align:center">Liste OR encours</h2>';

        $html .= '<table border="1" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px; ">';

        $html .= '</colgroup>';
        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($header1 as $key => $value) {
            if ($key === 0) {
                $html .= '<th style="width: 75px" >' . $value . '</th>';
            } elseif ($key === 2) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 3) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 4) {
                $html .= '<th style="width: 90px" >' . $value . '</th>';
            } elseif ($key === 5) {
                $html .= '<th style="width: 30px" >' . $value . '</th>';
            } elseif ($key === 6) {
                $html .= '<th style="width: 230px;" >' . $value . '</th>';
            } elseif ($key === 7) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 8) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 9) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } elseif ($key === 10) {
                $html .= '<th style="width: 50px" >' . $value . '</th>';
            } else {
                $html .= '<th >' . $value . '</th>';
            }
        }
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        // Ajouter les lignes du tableau
        foreach ($orDb as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $cell) {

                if ($key === 'agence') {
                    $html .= '<td style="width: 75px"  >' . $cell . '</td>';
                } elseif ($key === 'slor_numor') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'date') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'seor_refdem_lib') {
                    $html .= '<td style="width: 90px"  >' . $cell . '</td>';
                } elseif ($key === 'sitv_interv') {
                    $html .= '<td style="width: 30px"  >' . $cell . '</td>';
                } elseif ($key === 'stiv_comment') {
                    $html .= '<td style="width: 230px; text-align: left;"  >' . $cell . '</td>';
                } elseif ($key === 'agence_service') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'montant_total') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'montant_pieces') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } elseif ($key === 'montant_pieces_livrees') {
                    $html .= '<td style="width: 50px"  >' . $cell . '</td>';
                } else {
                    $html .= '<td  >' . $cell . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';


        $pdf->writeHTML($html, true, false, true, false, '');
    }
}
