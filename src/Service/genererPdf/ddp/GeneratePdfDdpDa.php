<?php

namespace App\Service\genererPdf\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Service\genererPdf\GeneratePdf;
use TCPDF;

class GeneratePdfDdpDa extends GeneratePdf
{
    public function copyToDw(string $nomAvecCheminFichier, string $nomFichier): void
    {
        $cheminDestinationLocal = $nomAvecCheminFichier;
        $cheminFichierDistant = $this->baseCheminDocuware . 'DEMANDE_DE_PAIEMENT/' . $nomFichier;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    /**
     * Génération page de garde de la demande de paiement
     * @param DemandePaiementDto|DdpDto $dto 
     * @param string $cheminEtNomDeFichier 
     */
    public function generer($dto, string $cheminEtNomDeFichier)
    {
        $pdf = new TCPDF();

        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/henriFraise.jpg'; // chemin du logo

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $w_total - $margins['left'] - $margins['right']; // largeur totale utilisable
        $w50 = $usable_width / 2; // demi de la largeur totale utilisable

        $pdf->setPrintHeader(false); // Supprime l'en-tête
        $pdf->AddPage();

        // tête de page : Logo | N° DDP | Emetteur sur la même ligne
        $pdf->Image($logoPath, 5, 1, 40, 0, 'jpg'); // logo absolu X=5, Y=1, W=40

        // Positionner le curseur texte juste après le logo, à la même hauteur
        $pdf->SetXY(45, 5);
        $pdf->SetFont('helvetica', '', 12);

        // Largeur utilisable après le logo (jusqu'à la marge droite)
        $wApresLogo = $pdf->GetPageWidth() - 45 - $margins['right'];
        $wNumeroDdp = $pdf->GetStringWidth($dto->numeroDdp);

        // N° DDP à gauche (juste après le logo)
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($wNumeroDdp + 2, 8, $dto->numeroDdp, 0, 0);

        // Emetteur aligné à droite (largeur restante)
        $pdf->Cell($wApresLogo - $wNumeroDdp - 2, 8, 'Emetteur : ' . $dto->adresseMailDemandeur, 0, 1, 'R');

        // Grand titre du pdf
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->setY(19);
        $pdf->Rect($pdf->GetX() + 20, $pdf->GetY(), $w50 * 2 - 40, 8);
        $pdf->Cell(0, 8, 'Service comptabilité – DEMANDE DE PAIEMENT ', 0, 1, 'C');

        $pdf->setY(28);

        // --- Calcul des largeurs pour la ligne TYPE DE DEMANDE / N° DA ---
        $pdf->SetFont('helvetica', 'B', 12);
        $wLabelType = $pdf->GetStringWidth('TYPE DE DEMANDE : ');
        $wLabelNda  = $pdf->GetStringWidth('N° DA : ');
        $wValeurNda = $pdf->GetStringWidth($dto->numeroDemandeAppro);
        // La valeur du type de demande prend l'espace restant (moins une petite marge)
        $wValeurType = $usable_width - $wLabelType - $wLabelNda - $wValeurNda - 2;

        // Label "TYPE DE DEMANDE : " en gras
        $pdf->Cell($wLabelType, 10, 'TYPE DE DEMANDE : ', 0, 0);

        // Valeur du type de demande en normal
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($wValeurType, 10, $dto->typeDemande->getLibelle(), 0, 0);


        // Label "N° DA : " en gras
        $pdf->Cell($wLabelNda, 10, 'N° DA : ', 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);

        // Valeur N° DA en gras, alignée à droite
        $pdf->Cell($wValeurNda + 2, 10, $dto->numeroDemandeAppro, 0, 1, 'R'); // valeur de "NUMERO DOCUMENT" (changer 'DDP25019999'  + le version )

        // --- Calcul des largeurs pour la ligne DATE / Emetteur ---
        $pdf->SetFont('helvetica', 'B', 12);
        $wLabelDate       = $pdf->GetStringWidth('DATE : ');
        $pdf->SetFont('helvetica', '', 12);
        $wValeurDate      = $pdf->GetStringWidth($dto->dateDemande->format('d/m/Y'));
        $wLabelEmetteur   = $pdf->GetStringWidth('Emetteur : ');
        $wValeurDemandeur = $pdf->GetStringWidth($dto->demandeur);
        // La valeur de la date prend l'espace restant (moins une petite marge)
        $wValeurDateCellule = $usable_width - $wLabelDate - $wValeurDate - $wLabelEmetteur - $wValeurDemandeur - 2;

        // Label "DATE : " en gras
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($wLabelDate, 10, 'DATE : ', 0, 0);

        // Valeur de la date en normal
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($wValeurDate + $wValeurDateCellule, 10, $dto->dateDemande->format('d/m/Y'), 0, 0);

        // Label "Emetteur : " en normal
        $pdf->Cell($wLabelEmetteur, 10, 'Emetteur : ', 0, 0);

        // Valeur demandeur en gras, alignée à droite
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($wValeurDemandeur + 2, 10, $dto->demandeur, 0, 1, 'R');

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('DATE') + 1, $pdf->GetY() - 2.5);

        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'N° commande ', 1, 0);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell($usable_width - 50, 10, $dto->numCdeString(), 1, 1); // valeur de "N° commande" (en chaine de caractère séparer par ";")

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'N° facture ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell($usable_width - 50, 10, $dto->numFacString(), 1, 1); // valeur de "N° facture" (en chaine de caractère séparer par ";")

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Bénéficiaire', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->beneficiaire, 1, 1); // valeur de "Bénéficiaire" (nom du fournisseur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Motif ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell($usable_width - 50, 10, $dto->motif, 1, 1); // valeur de "Motif" 

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Agence à débiter ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->debiteur['agence']->getCodeAgence(), 1, 1); // valeur de "code Agence à débiter" ( 01)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Service à débiter ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->debiteur['service']->getCodeService(), 1, 1); //  valeur de "code Service à débiter" (NEG)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'RIB ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->ribFournisseur, 1, 1); //  valeur de "RIB" 

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'CIF ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->cif, 1, 1); //  valeur de "CIF" 

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Contact ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->contact, 1, 1); //  valeur de "Contact" (03xxxxxxxx)

        if ($dto->ribFournisseurChanger()) {
            $pdf->SetTextColor(255, 0, 0); // Rouge
            $pdf->Cell(0, 10, '*Attention : RIB mis à jour', 0, 1);
            $pdf->SetTextColor(0, 0, 0); // Retour au noir
        }

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('*Attention') + 1, $pdf->GetY() - 2.5);

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(70, 10, 'Mode de paiement', 1, 0, 'C');
        $pdf->Cell($usable_width - 100, 10, 'Montant à payer', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Devise', 1, 1, 'C');

        $pdf->Line($pdf->GetX() + 16.5, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Mode de paiement') + 16.5, $pdf->GetY() - 2.5);
        $pdf->Line($pdf->GetX() + 98.5, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Montant à payer') + 98.5, $pdf->GetY() - 2.5);
        $pdf->Line($pdf->GetX() + 168.2, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Devise') + 168.2, $pdf->GetY() - 2.5);

        $pdf->Cell(70, 10, $dto->modePaiement, 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 100, 10, is_string($dto->montantAPayer) ? $dto->montantAPayer : number_format((float)$dto->montantAPayer, 2, ',', '.'), 1, 0); // valeur de "Montant à payer" (126.000,12)
        $pdf->Cell(30, 10, $dto->devise, 1, 1); //  valeur de "Devise" (AR)

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des pièces jointes :', 0, 1);
        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Liste des pièces jointes') + 1, $pdf->GetY() - 2.5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, $dto->lesFichiersStringSansExtension(), 0, 'L', 0, 1); // TO DO: valeur de "Liste des pièces jointes" (remplacer 'PJ1, PJ2, ...' par sa valeur)

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des dossiers de douane  :', 0, 1);
        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Liste des dossiers de douane ') + 1, $pdf->GetY() - 2.5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, $dto->numeroDossierDouaneString(), 0, 'L', 0, 1);

        //  génération de fichier: à changer plus tard
        $pdf->Output($cheminEtNomDeFichier, 'F');
    }
}
