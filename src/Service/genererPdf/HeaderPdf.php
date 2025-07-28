<?php

namespace App\Service\genererPdf;

use TCPDF;

class HeaderPdf extends TCPDF
{
    private $email;

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function Header()
    {
        if (! $this->email) {
            return; // Empêcher l'affichage si aucun email n'est défini
        }

        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(118, 2);
        $this->Cell(35, 6, $this->email, 0, 0, 'L');

        $this->SetLineWidth(0.2);
        $this->SetDrawColor(0, 0, 0);
        $this->Line(10, 8, 200, 8);
    }
}
