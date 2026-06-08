<?php

namespace App\Service\genererPdf\ddp;

use App\Controller\Traits\FormatageTrait;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\da\DemandeAppro;
use App\Service\genererPdf\da\PdfTableHistoriqueLivraisonBAP;
use App\Service\genererPdf\GeneratePdf;
use App\Service\genererPdf\PdfTableGeneratorFlexible;
use TCPDF;

class GeneratePdfDdpDa extends GeneratePdf
{
    use FormatageTrait;

    public function copyToDw(string $nomAvecCheminFichier, string $nomFichier): void
    {
        $cheminDestinationLocal = $nomAvecCheminFichier;
        $cheminFichierDistant = $this->baseCheminDocuware . 'DEMANDE_DE_PAIEMENT/' . $nomFichier;
        $this->copyFile($cheminDestinationLocal, $cheminFichierDistant);
    }

    /**
     * Génération page de garde de la demande de paiement
     * @param DemandePaiementDto $dto 
     * @param string $cheminEtNomDeFichier 
     */
    public function generer(
        array $infoValidationBC,
        array $infoMateriel,
        array $dataRecapOR,
        array $historiqueLivraison,
        DemandeAppro $demandeAppro,
        array $infoFacBl,
        $dtoFacBl,
        DemandePaiementDto $dto,
        string $cheminEtNomDeFichier
    ) {
        $pdf = new TCPDF();
        $isRegul = $dto->typeDemande->getCode() === "DPR";

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
        $titre = $isRegul ? 'REGULARISATION ' : 'PAIEMENT ';

        $pdf->Cell(0, 8, "Service comptabilité – DEMANDE DE $titre", 0, 1, 'C');

        $pdf->setY(28);

        // --- Calcul des largeurs pour la ligne TYPE DE DEMANDE / N° DA ---
        $pdf->SetFont('helvetica', 'B', 12);
        $wLabelType = $pdf->GetStringWidth('TYPE DE DEMANDE : ');
        $wLabelNda  = $pdf->GetStringWidth('N° DA : ');
        $wValeurNda = $pdf->GetStringWidth($dto->numeroDemandeAppro);
        // La valeur du type de demande prend l'espace restant (moins une petite marge)
        $wValeurType = $usable_width - $wLabelType - $wLabelNda - $wValeurNda - 2;

        // Label "TYPE DE DEMANDE : " en gras
        $pdf->Cell($wLabelType, 10, $isRegul ? '' : 'TYPE DE DEMANDE : ', 0, 0);

        // Valeur du type de demande en normal
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell($wValeurType, 10, $isRegul ? '' : $dto->typeDemande->getLibelle(), 0, 0);


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
        $wLabelEmetteur   = $pdf->GetStringWidth('Emetteur :   ');
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
        $pdf->Cell($wLabelEmetteur + 2, 10, 'Emetteur :   ', 0, 0);

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
        $pdf->Cell($usable_width - 50, 10, $this->txt($dto->cif), 1, 1); //  valeur de "CIF" 

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

        $pdf->Cell(70, 10, $dto->modePaiement, 1, 0);

        $pdf->SetFont('helvetica', '', 12);
        $montantAPayer = is_string($dto->montantAPayer) ? $dto->montantAPayer : number_format((float)$dto->montantAPayer, 2, ',', '.');
        $pourcentageApayer = '(' . $dto->pourcentageAPayer . ' %) ';
        $cellWidth = $usable_width - 100;
        $montantWidth = $pdf->GetStringWidth($montantAPayer) + 2;

        // Pourcentage en rouge — bordures gauche + haut + bas - Aligné à droite pour coller au montant
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell($cellWidth - $montantWidth, 10, $pourcentageApayer, 'LTB', 0, 'R');

        // Montant en noir aligné à droite — bordures droite + haut + bas
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell($montantWidth, 10, $montantAPayer, 'RTB', 0, 'R'); // valeur de "Montant à payer" (126.000,12)
        $pdf->Cell(30, 10, $dto->devise, 1, 1); //  valeur de "Devise" (AR)

        $pdf->Ln(5);
        // tableau de recapitulation des historique de demande de paiement
        $pdf->setFont('helvetica', 'B', 10);
        $this->addTitle($pdf, 'Tableau récapitulatif des demandes de paiement effectué sur la commande');
        $header = $this->headerTableau();
        $pdf->setFont('helvetica', '', 10);
        $generatorFlexible = new PdfTableGeneratorFlexible();
        // dd($dto->ddpRecap);
        $html1 = $generatorFlexible->generateTable($header, $dto->ddpRecap, []);
        $pdf->writeHTML($html1, true, false, true, false, '');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 12);
        $soldeAvance = $dtoFacBl ? number_format($dtoFacBl->soldeAvance, 2, ',', '.') : $dto->soldeAvance;
        $pdf->Cell(0, 10, 'Solde avance : ' . $soldeAvance . ' ' . $dto->devise, 0, 1);

        // liste des pièces jointes et des dossiers de douane
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

        $this->pageRecap($pdf, $historiqueLivraison, $demandeAppro, $infoFacBl, $dtoFacBl, $infoValidationBC, $infoMateriel, $dataRecapOR);

        // génération de fichier: à changer plus tard
        $pdf->Output($cheminEtNomDeFichier, 'F');
    }

    private function pageRecap(
        TCPDF $pdf,
        array $historiqueLivraison,
        DemandeAppro $demandeAppro,
        array $infoFacBl,
        $dto,
        array $infoValidationBC,
        array $infoMateriel,
        array $dataRecapOR
    ) {

        $infoBC = $dto->infoBc;
        $pdf = $this->initPDF($pdf);
        $this->renderHeader($pdf, $dto);
        $w100 = $this->getUsableWidth($pdf);

        $this->renderInfoBCAndValidation($pdf, $w100, $infoBC, $infoValidationBC);
        if ($demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT) {
            $this->renderInfoMateriel($pdf, $w100, $infoMateriel);
            $this->renderRecapOR($pdf, $dataRecapOR, $dto);
        }
        $this->renderRecapDA($pdf, $w100, $demandeAppro);
        if (!empty($infoFacBl)) {
            $this->renderInfoFACBL($pdf, $w100, $infoFacBl);
        }
        if (!empty($historiqueLivraison)) {
            $this->renderHistoriqueLivraison($pdf, $historiqueLivraison, $dto->devise);
        }
    }

    private function initPDF(TCPDF $pdf): TCPDF
    {
        $pdf->setMargins(20, 10, 15);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        return $pdf;
    }

    private function renderHeader(TCPDF $pdf, $dto): void
    {
        $pdf->setAbsX(60);
        $pdf->setFont('helvetica', 'B', 22);
        $pdf->Cell(110, 12, 'RECAP', 0, 0, 'C', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $dto->dateDemande->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7);
    }

    private function renderInfoSection(TCPDF $pdf, string $title1, string $title2, callable $callback)
    {
        $pdf->Ln(2);
        $pdf->setFont('helvetica', 'B', 9);

        if ($title2) {
            $w100 = $this->getUsableWidth($pdf);
            $pdf->Cell($w100 * 0.65, 5, $title1);
            $pdf->Cell($w100 * 0.35, 5, $title2, 0, 1);
        } else {
            $pdf->Cell(0, 5, $title1, 0, 1);
        }

        $pdf->Ln(2);

        $pdf->setFont('helvetica', '', 9);
        $callback();
    }

    private function renderInfoBCAndValidation(
        TCPDF $pdf,
        int $w100,
        array $infoBC,
        array $infoValidationBC
    ) {
        $this->renderInfoSection($pdf, 'RESUME DU BC', 'INFORMATION VALIDATION BC', function () use ($pdf, $w100, $infoBC, $infoValidationBC) {
            $this->addInfoLine($pdf, 'Nom fournisseur', $infoBC["nom_fournisseur"] ?? "-", $w100 * 0.65 - 6, 35, 0, 0);
            $this->addInfoLine($pdf, 'Nom Validateur', $infoValidationBC["validateur"] ?? "-", $w100 * 0.35, 25, 0);

            $this->addInfoLine($pdf, 'N° fournisseur', $infoBC["num_fournisseur"] ?? "-", $w100 * 0.65 - 6, 35, 0, 0);
            $dateValidation = $infoValidationBC["dateValidation"] ?? "-";
            $this->addInfoLine($pdf, 'Date Validation', $dateValidation === "-" ? $dateValidation : $dateValidation->format("d/m/Y"), $w100 * 0.35, 25, 0);

            $fields = [
                'N° commande'        => $infoBC["num_cde"] ?? "-",
                'N° demande appro'   => $infoBC["num_cde_ext"] ?? "-",
                'Référence commande' => $infoBC["libelle_cde"] ?? "-",
                'Date commande'      => $infoBC["date_cde"] ? date("d/m/Y", strtotime($infoBC["date_cde"])) : "-",
                'Succursale'         => $infoBC["succ_cde"] ?? "-",
                'Service'            => $infoBC["serv_cde"] ?? "-",
                'Opérateur'          => $infoBC["nom_ope"] ?? "-",
                'Montant HT'         => $this->formaterPrix($infoBC["mtn_cde"] ?? 0) . " " . ($infoBC["devise"] ?? ""),
                'Montant TTC'        => $this->formaterPrix($infoBC["ttc_cde"] ?? 0) . " " . ($infoBC["devise"] ?? ""),
                'Nature de l’achat'  => $infoBC["type_cde"] ?? "-"
            ];

            $pdf->Ln(2);

            foreach ($fields as $label => $value) {
                $this->addInfoLine($pdf, $label, $value, $w100, 35, 0);
                if ($label === 'Date commande') $pdf->Ln(2);
            }
        });
    }

    private function renderInfoMateriel(TCPDF $pdf, int $w100, array $infoMateriel)
    {
        $this->renderInfoSection($pdf, 'LA COMMANDE CONCERNE LE MATÉRIEL SUIVANT :', '', function () use ($pdf, $w100, $infoMateriel) {
            $this->addInfoLine($pdf, '', $infoMateriel["designation"] ?? "-", $w100, '');
            $this->addInfoLine($pdf, 'N° série', $infoMateriel["numserie"] ?? "-", $w100, 28);
            $this->addInfoLine($pdf, 'Identité', $infoMateriel["identite"] ?? "-", $w100, 28);
        });
    }

    private function renderRecapOR(TCPDF $pdf, array $dataRecapOR, $dto)
    {
        $numOR = $dto->numeroOR;
        $numDIT = $dto->numeroDemandeDit;
        $numDIT = $numDIT ? "- $numDIT" : "";
        $this->renderInfoSection($pdf, "RECAPITULATIF DE L’OR $numOR $numDIT", '', function () use ($pdf, $dataRecapOR) {
            $this->addInfoLine($pdf, 'Utilisateur Créateur', $dataRecapOR["createur_or"] ?? "-", 120, 30);
            $pdf->Ln(2);
            $tableGenerator = new PdfTableGeneratorFlexible();
            $tableGenerator->setOptions([
                'table_attributes' => 'border="0" cellpadding="0" cellspacing="0" align="center" style="font-size: 8px;"',
                'header_row_style' => 'background-color: #D3D3D3;',
                'footer_row_style' => 'background-color: #D3D3D3;'
            ]);

            $pdf->writeHTML(
                $tableGenerator->generateTable(
                    $dataRecapOR["header"],
                    $dataRecapOR["body"],
                    $dataRecapOR["footer"]
                )
            );
        });
    }

    private function renderRecapDA(TCPDF $pdf, int $w100, DemandeAppro $demandeAppro)
    {
        $this->renderInfoSection($pdf, 'RECAPITULATIF DE LA DA', '', function () use ($pdf, $w100, $demandeAppro) {
            $this->addInfoLine($pdf, 'N° DA', $demandeAppro->getNumeroDemandeAppro(), $w100, 25);
            $this->addInfoLine($pdf, 'Date de création', $demandeAppro->getDateCreation()->format('d/m/Y'), $w100, 25);
            $this->addInfoLine($pdf, 'Objet', $demandeAppro->getObjetDal(), $w100, 25);
            $this->addInfoLine($pdf, "Utilisateur demandeur", $demandeAppro->getDemandeur(), $w100, 39);
            $this->addInfoLine($pdf, 'Agence – service émetteur', $demandeAppro->getAgenceServiceEmetteur(), $w100, 39);
            $this->addInfoLine($pdf, 'Agence – service débiteur', $demandeAppro->getAgenceServiceDebiteur(), $w100, 39);
        });
    }

    private function renderInfoFacBl(TCPDF $pdf, int $w100, array $infoFacBl)
    {
        $this->renderInfoSection($pdf, 'INFO BL / FAC FOURNISSEUR', '', function () use ($pdf, $w100, $infoFacBl) {
            $this->addInfoLine($pdf, 'Réf', $infoFacBl["refBlFac"] ?? "-", $w100 / 2, 15, 6, 0);
            $this->addInfoLine($pdf, 'N° livraison IPS', $infoFacBl["numLivIPS"] ?? "-", $w100 / 2, 27);
            $this->addInfoLine($pdf, 'Date', $infoFacBl["dateBlFac"] ? $infoFacBl["dateBlFac"]->format('d/m/Y') : "-", $w100 / 2, 15, 6, 0);
            $this->addInfoLine($pdf, 'Date livraison IPS', $infoFacBl["dateLivIPS"] ? date("d/m/Y", strtotime($infoFacBl["dateLivIPS"])) : "-", $w100 / 2, 27);
        });
    }

    private function renderHistoriqueLivraison(TCPDF $pdf, array $historiqueLivraison, string $devise)
    {
        $this->renderInfoSection($pdf, 'RECAPITULATIF DES LIVRAISONS', '', function () use ($pdf, $historiqueLivraison, $devise) {
            if (empty($historiqueLivraison)) {
                $pdf->Cell(0, 5, "Aucune livraison", 0, 1);
            } else {
                $tableGenerator = new PdfTableHistoriqueLivraisonBAP();
                $pdf->writeHTML($tableGenerator->generateTable($historiqueLivraison, $devise));
            }
        });
    }

    private function renderHistoriqueDdp(TCPDF $pdf, array $historiqueDdp, string $devise)
    {
        $this->renderInfoSection($pdf, 'RECAPITULATIF DES DEMANDES DE PAIEMENT', '', function () use ($pdf, $historiqueDdp, $devise) {
            if (empty($historiqueDdp)) {
                $pdf->Cell(0, 5, "Aucune demande de paiement", 0, 1);
            } else {
                $tableGenerator = new PdfTableHistoriqueDdpBAP();
                $pdf->writeHTML($tableGenerator->generateTable($historiqueDdp, $devise));
            }
        });
    }


    private function getUsableWidth(TCPDF $pdf)
    {
        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        return $w_total - $margins['left'] - $margins['right'];
    }

    private function addInfoLine(TCPDF $pdf, string $label, string $value, $wTotal, $labelWidth = 35, $indent = 6, $endLine = 1)
    {
        if ($indent > 0) $pdf->Cell($indent, 5, '', 0, 0);
        $pdf->Cell(6, 5, '-', 0, 0);

        if ($label !== '') {
            $pdf->Cell($labelWidth, 5, $label, 0, 0);
            $pdf->Cell($wTotal - $labelWidth - $indent, 5, ": $value", 0, $endLine);
        } else {
            $pdf->Cell($wTotal - $indent, 5, $value, 0, $endLine);
        }
    }

    private function headerTableau(): array
    {
        // $formatterBooleenIcone = function ($value) {
        //     return $value ? 'OUI' : '';
        // };

        $formatterPourcentage = function ($value) {
            return $value . '%';
        };

        $formatterNull = function ($value) {
            return ($value !== null && $value !== '') ? $value : '—';
        };

        $styleBoldCenter = 'font-weight: bold; text-align: center;';
        $styleBoldLeft = 'font-weight: bold; text-align: left;';
        $styleBoldRight = 'font-weight: bold; text-align: right;';

        return [
            [
                'key' => 'dateCreation',
                'label' => 'Date',
                'width' => 50,
                'style' => $styleBoldCenter,
                'type' => 'date',
            ],
            [
                'key' => 'numeroDdp',
                'label' => 'N°',
                'width' => 55,
                'style' => $styleBoldCenter,
            ],
            [
                'key' => 'typeDemande',
                'label' => 'Type',
                'width' => 100,
                'style' => $styleBoldLeft,
            ],
            [
                'key' => 'numeroFacture',
                'label' => 'N° Facture',
                'width' => 80,
                'style' => $styleBoldCenter,
                'formatter' => $formatterNull
            ],
            [
                'key' => 'numeroFactureIps',
                'label' => 'N° Facture IPS',
                'width' => 50,
                'style' => $styleBoldCenter,
                'formatter' => $formatterNull
            ],
            [
                'key' => 'ratio',
                'label' => '%',
                'width' => 30,
                'style' => $styleBoldCenter,
                'type' => 'number',
                'formatter' => $formatterPourcentage
            ],
            [
                'key' => 'montant',
                'label' => 'Montant',
                'width' => 50,
                'style' => $styleBoldRight,
                'type' => 'number',
            ],
            [
                'key' => 'statut',
                'label' => 'Statut',
                'width' => 70,
                'style' => $styleBoldCenter,
            ],
            [
                'key' => 'emetteur',
                'label' => 'Emetteur',
                'width' => 50,
                'style' => $styleBoldCenter,
            ]
        ];
    }
}
