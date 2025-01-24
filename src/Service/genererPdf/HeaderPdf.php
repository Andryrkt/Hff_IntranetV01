<?php

namespace App\Service\genererPdf;

use TCPDF;

class HeaderPdf extends TCPDF
{
    private $email;

    // Constructeur pour passer l'email
    public function __construct($email) {
        parent::__construct();
        $this->email = $email;
    }

    // Personnalisation de l'en-tête
    public function Header() {
        // Ajouter un logo ou d'autres éléments si nécessaire
        // $this->Image('path/to/logo.png', 10, 10, 20);

        // Définir la police pour l'email
        $this->SetFont('helvetica', '', 10);

        // Définir la couleur du texte
        $this->SetTextColor(0, 0, 0);

        // Positionner le texte de l'email
        $this->SetXY(118, 2); // Coordonnées pour l'email
        $this->Cell(35, 6, $this->email, 0, 0, 'L');

        // Ajouter une ligne après l'en-tête
        $this->SetLineWidth(0.2); // Épaisseur de la ligne
        $this->SetDrawColor(0, 0, 0); // Couleur de la ligne (noir)
        $this->Line(10, 8, 200, 8); // Coordonnées X1, Y1, X2, Y2
    }
}