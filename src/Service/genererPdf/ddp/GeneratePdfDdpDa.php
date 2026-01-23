<?php

namespace App\Service\genererPdf\ddp;

use App\Dto\ddp\DemandePaiementDto;

use TCPDF;

class GeneratePdfDdpDa
{

    public function generer(DemandePaiementDto $dto, string $cheminEtNomDeFichier)
    {
        $pdf = new TCPDF();

        $logoPath = $_ENV['BASE_PATH_LONG'] . '/Views/assets/henriFraise.jpg'; // chemin du logo

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $w_total - $margins['left'] - $margins['right']; // largeur totale utilisable
        $w50 = $usable_width / 2; // demi de la largeur totale utilisable

        $pdf->setPrintHeader(false); // Supprime l'en-tête
        $pdf->AddPage();

        // tête de page 
        $pdf->setY(5);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Emetteur : ' . $dto->adresseMailDemandeur, 0, 1, 'R');

        $pdf->Image($logoPath, 5, 1, 40, 0, 'jpg');

        // Grand titre du pdf
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->setY(19);
        $pdf->Rect($pdf->GetX() + 20, $pdf->GetY(), $w50 * 2 - 40, 8);
        $pdf->Cell(0, 8, 'Service comptabilité – DEMANDE DE PAIEMENT ', 0, 1, 'C');

        $pdf->setY(28);
        $pdf->Cell($pdf->GetStringWidth('TYPE DE DEMANDE : '), 10, 'TYPE DE DEMANDE : ', 0, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, $dto->typeDemande->getLibelle(), 0, 0); // valeur de "TYPE DE DEMANDE" (changer 'DEMANDE DE PAIEMENT A L’AVANCE')

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('TYPE DE DEMANDE') + 1, $pdf->GetY() - 2.5);


        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $dto->numeroDdp . '_' . $dto->numeroVersion, 0, 1, 'R');  // valeur de "NUMERO DOCUMENT" (changer 'DDP25019999'  + le version )

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($pdf->GetStringWidth('DATE : '), 10, 'DATE : ', 0, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, $dto->dateDemande->format('d/m/Y'), 0, 1); // valeur de "DATE" (changer '12/02/2024')

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
        $pdf->Cell(50, 10, 'Contact ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->contact, 1, 1); //  valeur de "Contact" (03xxxxxxxx)

        $pdf->Cell(0, 10, '*Attention : RIB mis à jour', 0, 1); // TODO: il faut mettre une codition pour l'afficher ou pas (si l'utilisateur modifie le rib initial)

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
        $pdf->Cell($usable_width - 100, 10, $dto->montantAPayer, 1, 0); // valeur de "Montant à payer" (126.000,12)
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

        // génération de fichier: à changer plus tard
        $pdf->Output($cheminEtNomDeFichier, 'F');
    }
}
