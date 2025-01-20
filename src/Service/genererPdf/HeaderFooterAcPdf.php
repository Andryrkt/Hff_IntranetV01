<?php

namespace App\Service\genererPdf;

use TCPDF;

class HeaderFooterAcPdf extends TCPDF {
    // En-tête de page
    public function Header() {
        // $this->SetFont('helvetica', '', 10);
        // $logoPath = $logoPath = 'C:' . DIRECTORY_SEPARATOR . 'wamp64' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'Hffintranet' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'logoHFF.jpg';
        // $this->Image($logoPath, 15, 20, 50, '', 'jpg', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
    }

    // Pied de page
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}