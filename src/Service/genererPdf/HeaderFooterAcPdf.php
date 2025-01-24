<?php

namespace App\Service\genererPdf;

use TCPDF;

class HeaderFooterAcPdf extends TCPDF {
    // En-tête de page
    public function Header() {
       // Définir la police pour l'email
        $this->SetFont('helvetica', '', 10);

        // Définir la couleur du texte
        $this->SetTextColor(0, 0, 0);

        // Positionner le texte de l'email
        $this->SetXY(11, 2); // Coordonnées pour l'email
        $this->Cell(35, 6, 'AR-HFF', 0, 0, 'L');
        
    }

    // Pied de page
    public function Footer() {
        // $this->SetY(-15);
        // $this->SetFont('helvetica', 'I', 8);
        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}