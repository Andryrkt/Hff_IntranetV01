<?php

namespace App\Service\genererPdf;

use App\Controller\Traits\FormatageTrait;
use App\Dto\ddp\DdpDto;
use App\Entity\ddp\DemandePaiement;
use App\Traits\ChaineCaractereTrait;
use TCPDF;

class GeneratePdfDdp extends GeneratePdf
{
    use ChaineCaractereTrait;
    use FormatageTrait;

    /**
     * Genere le PDF DEMANDE DE PAIEMENT (DDP)
     */
    public function genererPDF(DemandePaiement $data, string $cheminDeFichier)
    {
        $pdf = new TCPDF();

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $w_total - $margins['left'] - $margins['right']; // largeur totale utilisable
        $w50 = $usable_width / 2; // demi de la largeur totale utilisable

        $pdf->setPrintHeader(false); // Supprime l'en-tête
        $pdf->AddPage();

        // tête de page 
        $pdf->setY(5);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Emetteur : ' . $data->getAdresseMailDemandeur(), 0, 1, 'R'); // TO DO: valeur de "Emetteur" (changer 'emetteur@hff.mg')

        $pdf->Image("{$this->basePathAssets}/henriFraise.jpg", 5, 1, 40, 0, 'jpg');

        // Grand titre du pdf
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->setY(19);
        $pdf->Rect($pdf->GetX() + 20, $pdf->GetY(), $w50 * 2 - 40, 8);
        $pdf->Cell(0, 8, 'Service comptabilité – DEMANDE DE PAIEMENT ', 0, 1, 'C');

        $pdf->setY(28);
        $pdf->Cell($pdf->GetStringWidth('TYPE DE DEMANDE : '), 10, 'TYPE DE DEMANDE : ', 0, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, $data->getTypeDemandeId()->getLibelle(), 0, 0);  // TO DO: valeur de "TYPE DE DEMANDE" (changer 'DEMANDE DE PAIEMENT A L’AVANCE')

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('TYPE DE DEMANDE') + 1, $pdf->GetY() - 2.5);


        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(
            0,
            10,
            $data->getNumeroDdp() . '_' . $data->getNumeroVersion(),
            0,
            1,
            'R'
        );  // TO DO: valeur de "NUMERO DOCUMENT" (changer 'DDP25019999'  + le version )

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($pdf->GetStringWidth('DATE : '), 10, 'DATE : ', 0, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, $data->getDateCreation()->format('d/m/Y'), 0, 1); // TO DO: valeur de "DATE" (changer '12/02/2024')

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('DATE') + 1, $pdf->GetY() - 2.5);

        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'N° commande ', 1, 0);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell($usable_width - 50, 10, implode(';', $data->getNumeroCommande()), 1, 1); // TO DO: valeur de "N° commande" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'N° facture ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell($usable_width - 50, 10, implode(';', $data->getNumeroFacture()), 1, 1); // TO DO: valeur de "N° facture" (remplacer '' par sa valeur)

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Bénéficiaire', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $data->getBeneficiaire(), 1, 1); // TO DO: valeur de "Bénéficiaire" (remplacer '' par sa valeur)
        // if (isFrnNonImmatricule) {
        //     $tagText = '(Non Immatriculé)';

        //     // 1. Nom béneficiaire sans border gauche LTB
        //     $pdf->SetFont('helvetica', '', 12);
        //     $nameW = $pdf->GetStringWidth($tagText);
        //     $pdf->SetTextColor(0, 0, 0);
        //     $pdf->Cell($usable_width - 50 - $nameW, 10, $data->getBeneficiaire(), 'LTB', 0, 'L');

        //     // 2. Texte si immatriculé sans border droite RTB
        //     $pdf->SetFont('helvetica', 'B', 10);
        //     $pdf->SetTextColor(255, 0, 0);
        //     $pdf->Cell($nameW, 10, $tagText, 'RTB', 1, 'L');
        //     $pdf->SetTextColor(0, 0, 0);
        // } else {
        //     $pdf->SetFont('helvetica', '', 12);
        //     $pdf->Cell($usable_width - 50, 10, $data->getBeneficiaire(), 1, 1, 'L');
        // }
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Motif ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell($usable_width - 50, 10, $data->getMotif(), 1, 1); // TO DO: valeur de "Motif" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Agence à débiter ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $data->getAgenceDebiter(), 1, 1); // TO DO: valeur de "Agence à débiter" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Service à débiter ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $data->getServiceDebiter(), 1, 1); // TO DO: valeur de "Service à débiter" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'RIB ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $data->getRibFournisseur(), 1, 1); // TO DO: valeur de "RIB" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Contact ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $data->getContact(), 1, 1); // TO DO: valeur de "Contact" (remplacer '' par sa valeur)

        $pdf->Cell(0, 10, '*Attention : RIB mis à jour', 0, 1);

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('*Attention') + 1, $pdf->GetY() - 2.5);

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(70, 10, 'Mode de paiement', 1, 0, 'C');
        $pdf->Cell($usable_width - 100, 10, 'Montant à payer', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Devise', 1, 1, 'C');

        $pdf->Line($pdf->GetX() + 16.5, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Mode de paiement') + 16.5, $pdf->GetY() - 2.5);
        $pdf->Line($pdf->GetX() + 98.5, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Montant à payer') + 98.5, $pdf->GetY() - 2.5);
        $pdf->Line($pdf->GetX() + 168.2, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Devise') + 168.2, $pdf->GetY() - 2.5);

        $pdf->Cell(70, 10, $data->getModePaiement(), 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 100, 10, $data->getMontantAPayer(), 1, 0); // TO DO: valeur de "Montant à payer" (remplacer '' par sa valeur)
        $pdf->Cell(30, 10, $data->getDevise(), 1, 1); // TO DO: valeur de "Devise" (remplacer '' par sa valeur)

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des pièces jointes :', 0, 1);
        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Liste des pièces jointes') + 1, $pdf->GetY() - 2.5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, implode(";", $this->removePdfExtension($data->getLesFichiers())), 0, 'L', 0, 1); // TO DO: valeur de "Liste des pièces jointes" (remplacer 'PJ1, PJ2, ...' par sa valeur)

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des dossiers de douane  :', 0, 1);
        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Liste des dossiers de douane ') + 1, $pdf->GetY() - 2.5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, implode(";", $data->getNumeroDossierDouane()), 0, 'L', 0, 1);

        // génération de fichier: à changer plus tard
        $directory = dirname($cheminDeFichier);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $pdf->Output($cheminDeFichier, 'F');
    }

    /**
     * Genere le PDF DEMANDE DE PAIEMENT (DDP)
     */
    public function genererPdfDto(DdpDto $dto, string $cheminDeFichier)
    {
        $pdf = new TCPDF();

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $w_total - $margins['left'] - $margins['right']; // largeur totale utilisable
        $w50 = $usable_width / 2; // demi de la largeur totale utilisable

        $pdf->setPrintHeader(false); // Supprime l'en-tête
        $pdf->AddPage();

        // tête de page 
        $pdf->setY(5);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, 'Emetteur : ' . $dto->adresseMailDemandeur, 0, 1, 'R'); // TO DO: valeur de "Emetteur" (changer 'emetteur@hff.mg')

        $pdf->Image("{$this->basePathAssets}/henriFraise.jpg", 5, 1, 40, 0, 'jpg');

        // Grand titre du pdf
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->setY(19);
        $pdf->Rect($pdf->GetX() + 20, $pdf->GetY(), $w50 * 2 - 40, 8);
        $pdf->Cell(0, 8, 'Service comptabilité – DEMANDE DE PAIEMENT ', 0, 1, 'C');

        $pdf->setY(28);
        $pdf->Cell($pdf->GetStringWidth('TYPE DE DEMANDE : '), 10, 'TYPE DE DEMANDE : ', 0, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, $dto->typeDdp->getLibelle(), 0, 0);  // TO DO: valeur de "TYPE DE DEMANDE" (changer 'DEMANDE DE PAIEMENT A L’AVANCE')

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('TYPE DE DEMANDE') + 1, $pdf->GetY() - 2.5);


        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(
            0,
            10,
            $dto->numeroDdp,
            0,
            1,
            'R'
        );  // TO DO: valeur de "NUMERO DOCUMENT" (changer 'DDP25019999'  + le version )

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($pdf->GetStringWidth('DATE : '), 10, 'DATE : ', 0, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, $dto->dateDemandeFormater(), 0, 1); // TO DO: valeur de "DATE" (changer '12/02/2024')

        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('DATE') + 1, $pdf->GetY() - 2.5);

        $pdf->Ln(2);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'N° commande ', 1, 0);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell($usable_width - 50, 10, implode(';', $dto->numeroCommande), 1, 1); // TO DO: valeur de "N° commande" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'N° facture ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell($usable_width - 50, 10, implode(';', $dto->numeroFacture), 1, 1); // TO DO: valeur de "N° facture" (remplacer '' par sa valeur)

        $pdf->Ln(5);

        if ($dto->isFrnNonImmatricule) {
            $tagText = '(Non Immatriculé)';
            $w       = 50;   // largeur cellule
            $h       = 10;   // hauteur totale cellule
            $padY    = 1;    // padding vertical (bas)

            // Position de départ
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // 1) Bordure extérieure de la cellule
            $pdf->Rect($x, $y, $w, $h);

            // 2) "Bénéficiaire" en HAUT (avec padding gauche aussi)
            $pdf->SetXY($x, $y + $padY);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell($w, 5, 'Bénéficiaire', 0, 'L');

            // 3) Tag rouge en BAS (avec padding gauche + bas)
            $pdf->SetXY($x, $y + $h - 4 - $padY);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->MultiCell($w, 4, $tagText, 0, 'L');
            $pdf->SetTextColor(0, 0, 0);

            // 4) Replacer le curseur APRÈS la cellule
            $pdf->SetXY($x + $w, $y);
        } else {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(50, 10, 'Bénéficiaire', 1, 0);
        }

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->beneficiaire, 1, 1); // TO DO: valeur de "Bénéficiaire" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Motif ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell($usable_width - 50, 10, $dto->motif, 1, 1); // TO DO: valeur de "Motif" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Agence à débiter ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, '01', 1, 1); // TO DO: valeur de "Agence à débiter" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Service à débiter ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, 'NEG', 1, 1); // TO DO: valeur de "Service à débiter" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'RIB ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->ribFournisseur, 1, 1); // TO DO: valeur de "RIB" (remplacer '' par sa valeur)

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 10, 'Contact ', 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($usable_width - 50, 10, $dto->contact, 1, 1); // TO DO: valeur de "Contact" (remplacer '' par sa valeur)

        if ($dto->ribFournisseurChanger()) {
            $pdf->SetTextColor(255, 0, 0); // Rouge
            $pdf->Cell(0, 10, '*Attention : RIB mis à jour', 0, 1);
            $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('*Attention') + 1, $pdf->GetY() - 2.5);
            $pdf->SetTextColor(0, 0, 0); // Retour au noir
        }

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
        $this->renderMontantAPayer($pdf, $dto, $usable_width - 100);
        $pdf->Cell(30, 10, $dto->devise, 1, 1); // TO DO: valeur de "Devise" (remplacer '' par sa valeur)

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des pièces jointes :', 0, 1);
        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Liste des pièces jointes') + 1, $pdf->GetY() - 2.5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, implode(";", $this->nomsFichiersSansExtension($dto->lesFichiers)), 0, 'L', 0, 1); // valeur de "Liste des pièces jointes"

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Liste des dossiers de douane  :', 0, 1);
        $pdf->Line($pdf->GetX() + 1, $pdf->GetY() - 2.5, $pdf->GetX() + $pdf->GetStringWidth('Liste des dossiers de douane ') + 1, $pdf->GetY() - 2.5);

        $pdf->SetFont('helvetica', '', 12);
        $pdf->MultiCell(0, 10, implode(";", $dto->numeroDossierDouane), 0, 'L', 0, 1);

        // Après arrivage : résumé des BC, livraisons et demandes de paiement déjà faites
        if (!empty($dto->donneesPageDeGarde)) {
            $commandes = $dto->donneesPageDeGarde['commandes'] ?? [];
            $devise = $dto->devise ?? '';
            $this->renderResumeBc($pdf, $dto, $commandes, $dto->donneesPageDeGarde['factures'] ?? []);
            $this->renderHistoriqueLivraison($pdf, $commandes, $devise);
            $this->renderHistoriqueDdp($pdf, $commandes, $devise);
        }

        // génération de fichier: à changer plus tard
        $directory = dirname($cheminDeFichier);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $pdf->Output($cheminDeFichier, 'F');
    }

    /**
     * Après arrivage : une ligne par BC, comme le tableau des montants par commande du formulaire
     */
    private function renderResumeBc(TCPDF $pdf, DdpDto $dto, array $commandes, array $factures): void
    {
        $devise = $dto->devise ?? '';
        $lignes = [];
        foreach (array_keys($commandes) as $numCde) {
            $facturesCde = array_filter($factures, fn($f) => trim((string) ($f['Numero_PO'] ?? '')) === (string) $numCde);
            // valeurs distinctes d'une colonne des factures de la commande
            $valeurs = fn(string $champ) => implode(', ', array_unique(array_filter(array_map(fn($f) => trim((string) ($f[$champ] ?? '')), $facturesCde)))) ?: '-';
            $lignes[] = [
                ['left', '11%', $valeurs('Numero_Facture')],
                ['center', '9%', $numCde],
                ['left', '9%', $valeurs('Numero_Dossier_Douane')],
                ['left', '11%', $valeurs('Numero_LTA')],
                ['left', '10%', $valeurs('Numero_HAWB')],
                ['right', '11%', $this->formaterPrix($dto->soldesAvance[$numCde] ?? 0)],
                ['right', '14%', $this->formaterPrix($dto->montantCommande((string) $numCde) ?? 0), $this->pourcentageCommande($dto, (string) $numCde, $dto->montantCommande((string) $numCde))],
                ['right', '14%', $this->formaterPrix($dto->montantCommandeAPayer((string) $numCde) ?? 0), $this->pourcentageCommande($dto, (string) $numCde, $dto->montantCommandeAPayer((string) $numCde))],
                ['right', '11%', $this->formaterPrix($dto->soldeAvanceRestant((string) $numCde) ?? 0)],
            ];
        }

        $this->renderTable($pdf, 'RESUME DES BC', 'Aucune commande', ['Facture fournisseur', 'N° commande', 'N° dossier douane', 'N° LTA', 'N° HAWB', "Solde avance ($devise)", "Montant commande réceptionnée ($devise)", "Montant commande à payer ($devise)","Solde avance restant ($devise)"], $lignes);
    }

    /**
     * Case "Montant à payer" : montant aligné à droite, précédé en rouge de sa part du montant total
     * des commandes (après arrivage) ou du commande (à l'avance). Deux cellules superposées : une Cell n'a qu'une seule couleur.
     */
    private function renderMontantAPayer(TCPDF $pdf, DdpDto $dto, float $largeur): void
    {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $montant = $this->formaterPrix($dto->montantAPayer());
        $pdf->Cell($largeur, 10, $montant, 1, 0, 'R');

        // montantsTotalCommande n'est rempli qu'après arrivage ; à l'avance le total est dans montantTotalCommande
        $montantTotal = $dto->montantsTotalCommande ? array_sum($dto->montantsTotalCommande) : $dto->montantTotalCommande();
        $pourcentage = $this->pourcentage($dto->montantAPayer(), (float) $montantTotal);
        if ($pourcentage !== '') {
            $pdf->SetXY($x, $y);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell($largeur - $pdf->GetStringWidth($montant) - 1, 10, $pourcentage, 0, 0, 'R');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($x + $largeur, $y);
        }
    }

    /**
     * Part d'une commande dans son montant total IPS.
     */
    private function pourcentageCommande(DdpDto $dto, string $numCde, ?float $montant): string
    {
        return $this->pourcentage($montant, (float) ($dto->montantsTotalCommande[$numCde] ?? 0));
    }

    /**
     * "(0,18 %)", vide si le montant total est inconnu.
     * Le formulaire affiche le même : DemandePaiementManager.js::pourcentage
     */
    private function pourcentage(?float $montant, float $montantTotal): string
    {
        return $montantTotal > 0 ? '(' . $this->formaterPrix(($montant ?? 0) / $montantTotal * 100) . ' %)' : '';
    }

    private function renderHistoriqueLivraison(TCPDF $pdf, array $commandes, string $devise): void
    {
        // une livraison peut couvrir plusieurs commandes et plusieurs factures : une ligne par livraison
        $livraisons = [];
        foreach ($commandes as $numCde => $commande) {
            foreach ($commande['livraisons'] ?? [] as $livraison) {
                $numLiv = (string) ($livraison['num_liv'] ?? '');
                $livraisons[$numLiv] = $livraisons[$numLiv] ?? ['commandes' => [], 'factures' => [], 'reference' => $livraison['ref_fac_bl'] ?? '', 'date' => $livraison['date_clot'] ?? null, 'montant' => 0.0];
                $livraisons[$numLiv]['commandes'][(string) $numCde] = true;
                $livraisons[$numLiv]['factures'][(string) ($livraison['numero_facture_ips'] ?? '')] = true;
                $livraisons[$numLiv]['montant'] += (float) ($livraison['montant_fac_bl'] ?? 0);
            }
        }
        ksort($livraisons);

        $lignes = [];
        foreach ($livraisons as $numLiv => $livraison) {
            $lignes[] = [
                ['left', '17%', $livraison['reference']],
                ['center', '12%', $numLiv],
                ['center', '24%', implode(', ', array_keys($livraison['commandes']))],
                ['center', '15%', implode(', ', array_keys($livraison['factures']))],
                ['center', '13%', !empty($livraison['date']) ? date('d/m/Y', strtotime($livraison['date'])) : '-'],
                ['right', '19%', $this->formaterPrix($livraison['montant'])],
            ];
        }

        $this->renderTable($pdf, 'RECAPITULATIF DES LIVRAISONS', 'Aucune livraison', ['Facture fournisseur', 'N° Livraison IPS', 'N° commande', 'N° Facture IPS', 'Date livraison IPS', "Montant ($devise)"], $lignes);
    }

    private function renderHistoriqueDdp(TCPDF $pdf, array $commandes, string $devise): void
    {
        $lignes = [];
        foreach ($commandes as $numCde => $commande) {
            foreach ($commande['ddps'] ?? [] as $ddp) {
                $lignes[] = [
                    ['center', '11%', $numCde],
                    ['center', '10%', $ddp['date']],
                    ['center', '14%', $ddp['numero']],
                    ['left', '28%', $ddp['type']],
                    ['right', '14%', $this->formaterPrix($ddp['montant'])],
                    ['left', '23%', $ddp['statut']],
                ];
            }
        }

        $this->renderTable($pdf, 'RECAPITULATIF DES DEMANDES DE PAIEMENT DEJA ENREGISTREES', 'Aucune demande de paiement', ['N° commande', 'Date', 'Numéro DDP', 'Type', "Montant ($devise)", 'Statut'], $lignes);
    }

    private function renderTable(TCPDF $pdf, string $titre, string $messageVide, array $entetes, array $lignes): void
    {
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 6, $titre, 0, 1);
        $pdf->SetFont('helvetica', '', 8);
        if (empty($lignes)) {
            $pdf->Cell(0, 6, $messageVide, 0, 1);
            return;
        }
        $pdf->writeHTML($this->tableHtml($entetes, $lignes));
    }

    /**
     * @param string[] $entetes
     * @param array<array<array{0: string, 1: string, 2: string, 3?: string}>> $lignes cellules [alignement, largeur, valeur, texte en rouge devant la valeur]
     */
    private function tableHtml(array $entetes, array $lignes): string
    {
        $html = '<table border="0" cellpadding="2" cellspacing="0" style="font-size: 7px;"><thead><tr style="background-color: #D3D3D3; font-weight: bold;">';
        foreach ($entetes as $i => $entete) {
            [$align, $largeur] = $lignes[0][$i];
            $html .= sprintf('<th align="%s" style="width:%s;">%s</th>', $align, $largeur, htmlspecialchars($entete));
        }
        $html .= '</tr></thead><tbody>';
        foreach ($lignes as $ligne) {
            $html .= '<tr>';
            foreach ($ligne as $cellule) {
                [$align, $largeur, $valeur] = $cellule;
                $rouge = empty($cellule[3]) ? '' : '<span style="color:#FF0000;">' . htmlspecialchars($cellule[3]) . '</span> ';
                $html .= sprintf('<td align="%s" style="width:%s;">%s%s</td>', $align, $largeur, $rouge, htmlspecialchars((string) $valeur));
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    /**
     * Noms des pièces jointes à afficher sur la page de garde : uniquement le nom
     * du fichier (sans dossier, ex: "PDV\xxx.pdf" → "xxx") et sans extension,
     * quelle qu'elle soit (.pdf, .PDF, .jpg...). Doublons et vides retirés.
     */
    private function nomsFichiersSansExtension(array $fichiers): array
    {
        $noms = [];
        foreach ($fichiers as $fichier) {
            $nom = basename(str_replace('\\', '/', (string) $fichier));
            $nom = pathinfo($nom, PATHINFO_FILENAME);
            if ($nom !== '') {
                $noms[] = $nom;
            }
        }

        return array_values(array_unique($noms));
    }
}
