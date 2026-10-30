<?php

namespace App\Service\genererPdf\magasin\cdeFrn;

use TCPDF;
use App\Service\genererPdf\GeneratePdf;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionDTO;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionLigneDTO;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionDetailDTO;

class GeneratePdfCdeMagasin extends GeneratePdf
{
    private TCPDF  $pdf;
    private CommandeSoumissionDTO $dto;
    private const FONT             = 'helvetica';

    private const MARGIN_RIGHT     = 7.5;
    private const MARGIN_LEFT      = 7.5;
    private const MARGIN_TOP       = 7.5;
    private const MARGIN_BOTTOM    = 7.5;

    private const TITLE_SIZE         = 16.0;
    private const MAIN_TEXT_SIZE     = 7.2;
    private const SUB_TEXT_SIZE      = 6.7;

    private const MAIN_TEXT_HEIGHT = 5.3;
    private const MAIN_ROW_HEIGHT  = 7;
    private const SUB_ROW_HEIGHT   = 6;
    private const TITLE_HEIGHT     = 9;

    // Palette : bleu ardoise + neutres
    private const ACCENT = [30, 41, 59];
    private const INK    = [15, 23, 42];
    private const MUTED  = [100, 116, 139];
    private const LINE   = [226, 232, 240];
    private const ZEBRA  = [245, 247, 250];
    private const WHITE  = [255, 255, 255];

    /** @var array{img1X:int|float,img1Y:int|float,img1W:int|float,img1H:int|float,img2X:int|float,img2Y:int|float,img2W:int|float,img2H:int|float} $imgConfig */
    private array $imgConfig = [];

    /** @var array{noLigne:int|float,cst:int|float,avBat:int|float,ref:int|float,packQty:int|float,designation:int|float,npr:int|float,fms:int|float,ret:int|float,qteCdee:int|float,qteDispo:int|float,qteDispoMin:int|float,qteDispoMax:int|float,qteVte6M:int|float,nbrVte6M:int|float,coutUnit:int|float,coutTotal:int|float,poids:int|float} $mainRowWidths */
    private array $mainRowWidths = [];

    /** @var array{empty:int|float,refClientLabel:int|float,rmqClient:int|float,numDoc:int|float,ref:int|float,client:int|float,datePlanning:int|float} $subRowWidths */
    private array $subRowWidths = [];

    /** @var array{empty1:int|float,signature:int|float,empty2:int|float,mttTotalLabel:int|float,mttTotal:int|float,docRattacheesLabel:int|float} $footerWidths */
    private array $footerWidths = [];

    private const COL_LABELS = [
        'noLigne'      => "N°\nLine",
        'cst'          => "CST",
        'avBat'        => "Av.\nBat.",
        'ref'          => "Réf.",
        'packQty'      => "Pack.\nQty",
        'designation'  => "Désignation",
        'npr'          => "NPR\n(*)",
        'fms'          => "F\nM\nS",
        'ret'          => "Ret",
        'qteCdee'      => "Qté\nCdée",
        'qteDispo'     => "Qté\nDispo",
        'qteDispoMin'  => "Qté\nDispo\nMin",
        'qteDispoMax'  => "Qté\nDispo\nMax",
        'qteVte6M'     => "Qté Vte\nDernier\n6 Mois",
        'nbrVte6M'     => "Nbr Vte\nDernier\n6 Mois",
        'coutUnit'     => "Coût\nUnit.",
        'coutTotal'    => "Coût\nTotal",
        'poids'        => "Poids\n[kg]",
    ];

    public function __construct(CommandeSoumissionDTO $dto)
    {
        parent::__construct();

        $this->dto = $dto;
        $this->pdf = $this->initPDF();

        $this->defineImgConfig();
        $this->defineMainRowWidths();
        $this->defineSubRowWidths();
        $this->defineFooterWidths();
    }

    public function generate(string $filePath): void
    {
        $this->renderHeader();

        $this->renderTable();

        $this->renderFooter();

        $this->renderPageFooters();

        $this->pdf->Output($filePath, 'F');
    }

    private function initPDF(): TCPDF
    {
        $pdf = new TCPDF("L");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->setMargins(self::MARGIN_LEFT, self::MARGIN_TOP, self::MARGIN_RIGHT, true);
        $pdf->SetAutoPageBreak(true, self::MARGIN_BOTTOM);

        $pdf->AddPage();

        return $pdf;
    }

    private function renderHeader(): void
    {
        $this->pdf->Image("{$this->basePathAssets}/info_HFF_1.png", $this->imgConfig['img1X'], $this->imgConfig['img1Y'], $this->imgConfig['img1W'], $this->imgConfig['img1H'], "PNG");
        $this->pdf->Image("{$this->basePathAssets}/info_HFF_2.png", $this->imgConfig['img2X'], $this->imgConfig['img2Y'], $this->imgConfig['img2W'], $this->imgConfig['img2H'], "PNG");

        $this->pdf->SetTextColor(...self::ACCENT);
        $this->pdf->SetFont(self::FONT, "B", self::TITLE_SIZE);
        $this->pdf->Cell(0, self::TITLE_HEIGHT, "Cde Fournisseur", 0, 0);

        // Responsable sur la même ligne, aligné à droite
        $this->pdf->SetTextColor(...self::MUTED);
        $this->pdf->SetFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->SetX(self::MARGIN_LEFT);
        $this->pdf->Cell(0, self::TITLE_HEIGHT, $this->dto->responsable, 0, 1, 'R');

        $this->pdf->Ln(1);

        $this->info(26, "N° commande :", true);
        $this->info(24, $this->dto->numeroCommande, false, true);
        $this->info(0, $this->dto->getDateCdeFormatted(), false, false, 'L', 1);

        $this->info(26, "Type commande :", true);
        $this->info(24, $this->dto->typeCde);
        $this->info(30, "Délai d'expédition :", true);
        $this->info(0, $this->dto->getDelaiExpedition(), false, false, 'L', 1);

        $this->info(26, "Fournisseur :", true);
        $this->info(24, $this->dto->numFrn, false, true);
        $this->info(70, $this->dto->nomFrn, false, true);
        $this->info(0, $this->dto->getAgenceService(), false, true, 'R', 1);
    }

    /** Cellule de l'entête : libellé gris ou valeur sombre (gras optionnel) */
    private function info(float $w, string $txt, bool $label = false, bool $bold = false, string $align = 'L', int $ln = 0): void
    {
        $this->pdf->SetFont(self::FONT, $label ? "I" : ($bold ? "B" : ""), self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...($label ? self::MUTED : self::INK));
        $this->pdf->Cell($w, self::MAIN_TEXT_HEIGHT, $txt, 0, $ln, $align);
    }

    /**
     * Méthode pour construire le tableau de lignes de commande
     *
     * @return void
     */
    private function renderTable(): void
    {
        $this->pdf->Ln(2);

        $this->renderTableHeader();

        $rowIndex = 0;

        foreach ($this->dto->lignes as $ligneDto) {
            // 1. Calculer la hauteur totale du bloc (ligne + sous-lignes)
            $blockHeight = $this->calculateBlockHeight($ligneDto);

            // 2. Vérifier s'il faut un saut de page AVANT de dessiner le bloc
            if ($this->exceedsPage($blockHeight)) $this->newPage();

            // 3. Couleur de fond alternée
            $fill = ($rowIndex % 2 == 1);
            $this->pdf->SetFillColor(...self::ZEBRA);

            // 4. Dessiner la ligne principale
            $this->renderMainRow($ligneDto, $fill);

            // 5. Dessiner les sous-lignes (même fill que la ligne parente, pour cohérence visuelle)
            foreach ($ligneDto->details as $detailDto) {
                $this->renderSubRow($detailDto, $fill);
            }

            $rowIndex++;
        }

        $this->pdf->SetTextColor(...self::INK);
    }

    private function renderTableHeader(): void
    {
        $this->pdf->SetFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->SetFillColor(...self::ACCENT);
        $this->pdf->SetTextColor(...self::WHITE);
        $this->pdf->SetDrawColor(...self::ACCENT);

        $headerHeight = $this->calculateHeaderHeight();
        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        foreach ($this->mainRowWidths as $key => $width) {
            $label = self::COL_LABELS[$key];
            $align = in_array($key, ['coutUnit', 'coutTotal']) ? 'R' : 'C';

            // MultiCell avec fond, mais sans avancer X automatiquement
            $this->pdf->MultiCell($width, $headerHeight, $label, 0, $align, true, 0, null, null, true, 0, false, true, $headerHeight, 'M');

            $x += $width;
            $this->pdf->SetXY($x, $y);
        }

        $this->pdf->SetXY(self::MARGIN_LEFT, $y + $headerHeight);

        // Reset couleurs pour les lignes de données
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->SetDrawColor(...self::LINE);
        $this->pdf->SetFont(self::FONT, "", self::MAIN_TEXT_SIZE);
    }

    private function renderMainRow(CommandeSoumissionLigneDTO $ligneDto, bool $fill): void
    {
        $this->pdf->SetFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->SetFillColor(...self::ZEBRA);
        $this->pdf->SetTextColor(...self::INK);

        $this->pdf->Cell($this->mainRowWidths['noLigne'],     self::MAIN_ROW_HEIGHT, $ligneDto->numLine,           0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['cst'],         self::MAIN_ROW_HEIGHT, $ligneDto->const,             0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['avBat'],       self::MAIN_ROW_HEIGHT, $ligneDto->avBat,             0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['ref'],         self::MAIN_ROW_HEIGHT, $ligneDto->ref,               0, 0, 'L', $fill);
        $this->pdf->Cell($this->mainRowWidths['packQty'],     self::MAIN_ROW_HEIGHT, $ligneDto->packQty,           0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['designation'], self::MAIN_ROW_HEIGHT, $ligneDto->designation,       0, 0, 'L', $fill);
        $this->pdf->Cell($this->mainRowWidths['npr'],         self::MAIN_ROW_HEIGHT, $ligneDto->npr,               0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['fms'],         self::MAIN_ROW_HEIGHT, $ligneDto->fms,               0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['ret'],         self::MAIN_ROW_HEIGHT, $ligneDto->ret,               0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['qteCdee'],     self::MAIN_ROW_HEIGHT, $ligneDto->qteDem,            0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['qteDispo'],    self::MAIN_ROW_HEIGHT, $ligneDto->qteDispo,          0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['qteDispoMin'], self::MAIN_ROW_HEIGHT, $ligneDto->qteDispoMin,       0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['qteDispoMax'], self::MAIN_ROW_HEIGHT, $ligneDto->qteDispoMax,       0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['qteVte6M'],    self::MAIN_ROW_HEIGHT, $ligneDto->qteVteDer6Mois,    0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['nbrVte6M'],    self::MAIN_ROW_HEIGHT, $ligneDto->nbrVteDer6Mois,    0, 0, 'C', $fill);
        $this->pdf->Cell($this->mainRowWidths['coutUnit'],    self::MAIN_ROW_HEIGHT, $ligneDto->getPrixUnitaire(), 0, 0, 'R', $fill);
        $this->pdf->Cell($this->mainRowWidths['coutTotal'],   self::MAIN_ROW_HEIGHT, $ligneDto->getPrixTotal(),    0, 0, 'R', $fill);
        $this->pdf->Cell($this->mainRowWidths['poids'],       self::MAIN_ROW_HEIGHT, $ligneDto->getPoids(),        0, 1, 'C', $fill);
    }

    private function renderSubRow(CommandeSoumissionDetailDTO $detailDto, bool $fill): void
    {
        $this->pdf->SetFont(self::FONT, "", self::SUB_TEXT_SIZE);
        $this->pdf->SetFillColor(...self::ZEBRA);
        $this->pdf->SetTextColor(...self::MUTED);

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        // Filet fin séparant ce détail du suivant (sous la cellule texte)
        $this->drawHairline(
            $x + $this->subRowWidths['empty'] + $this->subRowWidths['refClientLabel'],
            $y,
            $this->pdf->getPageWidth() - self::MARGIN_LEFT
        );

        // Cellule vide
        $this->pdf->Cell($this->subRowWidths['empty'], self::SUB_ROW_HEIGHT, '', 0, 0, 'L', $fill);

        $this->pdf->SetFont(self::FONT, "I", self::SUB_TEXT_SIZE);
        $this->pdf->Cell($this->subRowWidths['refClientLabel'], self::SUB_ROW_HEIGHT, "Référence client:", 0, 0, 'L', $fill);

        $this->pdf->Cell($this->subRowWidths['rmqClient'],    self::SUB_ROW_HEIGHT, $detailDto->rmqClient,                  0, 0, 'R', $fill);
        $this->pdf->Cell($this->subRowWidths['numDoc'],       self::SUB_ROW_HEIGHT, "- {$detailDto->numDoc}",               0, 0, 'L', $fill);
        $this->pdf->Cell($this->subRowWidths['ref'],          self::SUB_ROW_HEIGHT, $detailDto->getRefSplitted(),           0, 0, 'C', $fill);
        $this->pdf->Cell($this->subRowWidths['client'],       self::SUB_ROW_HEIGHT, $detailDto->getClient(),                0, 0, 'L', $fill);
        $this->pdf->Cell($this->subRowWidths['datePlanning'], self::SUB_ROW_HEIGHT, $detailDto->getDatePlanningFormatted(), 0, 1, 'L', $fill);
    }

    private function renderFooter(): void
    {
        // Le pied de page doit tenir sur une seule page
        if ($this->exceedsPage($this->calculateFooterHeight())) $this->newPage();

        $this->pdf->Ln(3);

        $this->pdf->SetFont(self::FONT, '', 6.2);
        $this->pdf->SetTextColor(...self::MUTED);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, 'TOTAL POIDS [KG]', 0, 1, 'R');
        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE + 1);
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, $this->dto->getPoidsTotal(), 0, 1, 'R');

        $x0 = $this->pdf->GetX();
        $y0 = $this->pdf->GetY() + 2;
        $hh = self::MAIN_TEXT_HEIGHT;

        // Signature : "X" suivi d'un trait, libellé dessous
        $sigX = $x0 + $this->footerWidths["empty1"];
        $this->pdf->SetXY($sigX, $y0 + 1);
        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->Cell(6, 7, "X");
        $this->pdf->SetLineStyle(['width' => 0.2, 'dash' => 0, 'color' => self::MUTED]);
        $this->pdf->Line($sigX + 6, $y0 + 7, $sigX + $this->footerWidths["signature"], $y0 + 7);
        $this->pdf->SetXY($sigX, $y0 + 8.5);
        $this->pdf->SetFont(self::FONT, 'I', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::MUTED);
        $this->pdf->Cell($this->footerWidths["signature"], $hh, "Signature");

        // Total : encadré par deux filets
        $totW = $this->footerWidths["mttTotalLabel"] + $this->footerWidths["mttTotal"];
        $totX = $sigX + $this->footerWidths["signature"] + $this->footerWidths["empty2"];
        $this->pdf->SetLineStyle(['width' => 0.4, 'dash' => 0, 'color' => self::ACCENT]);
        $this->pdf->Line($totX, $y0, $totX + $totW, $y0);
        $this->pdf->Line($totX, $y0 + 8, $totX + $totW, $y0 + 8);

        $this->pdf->SetXY($totX, $y0 + 1);
        $this->pdf->SetFont(self::FONT, 'I', self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->footerWidths["mttTotalLabel"], 6, "Total commande HT :");
        $this->pdf->SetFont(self::FONT, 'B', 10);
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->Cell($this->footerWidths["mttTotal"], 6, $this->dto->getMontantTotal(), 0, 0, 'R');

        $this->pdf->SetXY($totX, $y0 + 8.5);
        $this->pdf->SetFont(self::FONT, 'I', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::MUTED);
        $this->pdf->Cell($totW, $hh, "Montant en {$this->dto->devise}", 0, 0, 'C');

        $this->pdf->SetXY(self::MARGIN_LEFT, $y0 + 8.5 + $hh);
        $this->drawFooterSeparator($this->pdf->GetX(), $this->pdf->GetY() + 1.5, $this->pdf->GetPageWidth() - self::MARGIN_LEFT);

        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::ACCENT);
        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::ACCENT);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, "Documents OR rattachés (OR validé) :", 0, 1);

        $y1 = $this->pdf->GetY();
        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->MultiCell(0, 0, $this->dto->getAllValidatedOR(), 0, "L");

        $this->pdf->setY($y1 + $hh * 2.5);

        $y2 = $this->pdf->GetY();
        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::ACCENT);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, "Documents PO rattachés (PO validé) :", 0, 1);

        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->MultiCell(0, 0, $this->dto->getAllValidatedPO(), 0, "L");

        $this->pdf->setY($y2 + $hh * 2.5);
        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::ACCENT);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, "Commentaire :", 0, 1);

        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $this->pdf->SetTextColor(...self::INK);
        $this->pdf->MultiCell(0, 4, $this->dto->commentaire, 0, "L");
    }

    /** Footer de chaque page : date de génération à gauche, "Page X / Y" au centre */
    private function renderPageFooters(): void
    {
        $date  = (new \DateTime("now", new \DateTimeZone("Indian/Antananarivo")))->format('d/m/Y H:i:s');
        $total = $this->pdf->getNumPages();
        $y     = $this->pdf->getPageHeight() - self::MARGIN_BOTTOM;

        for ($i = 1; $i <= $total; $i++) {
            $this->pdf->setPage($i);
            $this->pdf->SetAutoPageBreak(false);
            $this->pdf->SetFont(self::FONT, "", self::SUB_TEXT_SIZE);
            $this->pdf->SetTextColor(...self::MUTED);
            $this->pdf->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => self::LINE]);

            $this->pdf->Line(self::MARGIN_LEFT / 2, $y, $this->pdf->getPageWidth() - self::MARGIN_RIGHT / 2, $y);

            $this->pdf->SetXY(self::MARGIN_LEFT, $y);
            $this->pdf->Cell(0, self::SUB_ROW_HEIGHT, $date, 0, 0, 'L');

            $this->pdf->SetXY(self::MARGIN_LEFT, $y);
            $this->pdf->Cell(0, self::SUB_ROW_HEIGHT, "Page $i / $total", 0, 0, 'C');
        }
    }

    private function getUsableWidth(): float
    {
        $w_total = $this->pdf->getPageWidth();
        return $w_total - (self::MARGIN_TOP + self::MARGIN_BOTTOM);
    }

    private function calculateBlockHeight(CommandeSoumissionLigneDTO $ligneDto): float
    {
        $height = self::MAIN_ROW_HEIGHT; // hauteur ligne principale
        $height += count($ligneDto->details) * self::SUB_ROW_HEIGHT;
        return $height;
    }

    private function calculateFooterHeight(): float
    {
        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $w100 = $this->getUsableWidth();

        // Ln(3) x2 + 2 lignes poids + signature/total (8.5 + libellés) + séparateur (4.5) + 2 titres OR/PO
        return 3 + 3
            + 2 * self::MAIN_TEXT_HEIGHT
            + 2 + 8.5 + self::MAIN_TEXT_HEIGHT + 4.5
            + 2 * self::MAIN_TEXT_HEIGHT
            + $this->pdf->getStringHeight($w100, $this->dto->getAllValidatedOR())
            + $this->pdf->getStringHeight($w100, $this->dto->getAllValidatedPO());
    }

    private function calculateHeaderHeight(): float
    {
        $maxLines = 1;

        foreach (self::COL_LABELS as $key => $label) {
            $width = $this->mainRowWidths[$key];
            $nbLines = $this->pdf->getNumLines($label, $width);
            $maxLines = max($maxLines, $nbLines);
        }

        return $maxLines * (self::MAIN_ROW_HEIGHT - 1); // maxLines = 3
    }

    private function exceedsPage(float $neededHeight): bool
    {
        $pageBreakTrigger = $this->pdf->getPageHeight() - $this->pdf->getBreakMargin();
        return $this->pdf->GetY() + $neededHeight > $pageBreakTrigger;
    }

    /** Nouvelle page avec l'entête de la commande et l'entête des colonnes du tableau */
    private function newPage(): void
    {
        $this->pdf->AddPage();
        $this->renderHeader();
        $this->pdf->Ln(3);
        $this->renderTableHeader();
    }

    /** Positions et tailles des logos de l'entête */
    private function defineImgConfig(): void
    {
        $w100 = $this->getUsableWidth();

        $this->imgConfig['img1W'] = 44.852;
        $this->imgConfig['img1H'] = $this->imgConfig['img1W'] * 434 / 1039;
        $this->imgConfig['img1X'] = $w100 / 2 + 10;

        $this->imgConfig['img2W'] = 25.588;
        $this->imgConfig['img2H'] = $this->imgConfig['img2W'] * 223 / 460;
        $this->imgConfig['img2X'] = self::MARGIN_LEFT + $w100 - $this->imgConfig['img2W'];

        $this->imgConfig['img1Y'] = $this->imgConfig['img2Y'] = self::MARGIN_TOP + 7;
    }

    /** 
     * Définir les largeurs des colonnes de lignes principales
     * 
     * @return void
     */
    private function defineMainRowWidths(): void
    {
        $w100 = $this->getUsableWidth();
        $this->mainRowWidths = array_fill_keys(array_keys(self::COL_LABELS), 0);

        $this->mainRowWidths['noLigne']   =
            $this->mainRowWidths['cst']   =
            $this->mainRowWidths['avBat'] =
            $this->mainRowWidths['npr']   =
            $this->mainRowWidths['ret']   =
            $this->mainRowWidths['fms']   = 10;

        $this->mainRowWidths['coutUnit']      =
            $this->mainRowWidths['coutTotal'] =
            $this->mainRowWidths['ref']       = 20;

        $this->mainRowWidths['designation'] = 50;

        $wUsed = array_sum(array_values($this->mainRowWidths));
        $wRemaining = $w100 - $wUsed;

        $this->mainRowWidths['packQty'] = $this->mainRowWidths['qteCdee'] = $this->mainRowWidths['qteDispo'] = $this->mainRowWidths['qteDispoMin'] = $this->mainRowWidths['qteDispoMax'] = $this->mainRowWidths['poids'] = $this->mainRowWidths['qteVte6M'] = $this->mainRowWidths['nbrVte6M'] = $wRemaining / 8;
    }

    /** 
     * Définir les largeurs des colonnes de lignes secondaires
     * 
     * @return void
     */
    private function defineSubRowWidths(): void
    {
        $w100 = $this->getUsableWidth();

        // Largeur vide = somme des colonnes de "N° Ligne" jusqu'à "Réf" / 2 incluse
        $emptyWidth = $this->mainRowWidths['noLigne']
            + $this->mainRowWidths['cst']
            + $this->mainRowWidths['avBat']
            + ($this->mainRowWidths['ref'] / 2);

        $this->subRowWidths = [
            "empty"          => $emptyWidth,
            "refClientLabel" => 20,
            "rmqClient"      => 15,
            "numDoc"         => 15,
            "ref"            => 75,
            "client"         => 90,
            "datePlanning"   => 0,
        ];

        $wUsed = array_sum(array_values($this->subRowWidths));

        $this->subRowWidths['datePlanning'] = $w100 - $wUsed;
    }

    /** 
     * Définir les largeurs des colonnes de pied de page
     *
     * @return void
     */
    private function defineFooterWidths(): void
    {
        $w100 = $this->getUsableWidth();

        $this->footerWidths["empty1"] = $this->footerWidths["signature"] = 40;
        $this->footerWidths["empty2"] = $w100 * 0.71 - ($this->footerWidths["empty1"] + $this->footerWidths["signature"]);
        $this->footerWidths["mttTotalLabel"] = $this->footerWidths["mttTotal"] = 25;
        $this->footerWidths["docRattacheesLabel"] = $w100 * 0.157;
    }

    /**
     * Trace un filet fin entre deux sous-lignes de détail.
     */
    private function drawHairline(float $xStart, float $y, float $xEnd): void
    {
        $this->pdf->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => self::LINE]);
        $this->pdf->Line($xStart, $y, $xEnd, $y);
    }

    /**
     * Trace une ligne de séparation dans le footer
     */
    private function drawFooterSeparator(float $xStart, float $y, float $xEnd): void
    {
        $this->pdf->SetLineStyle(['width' => 0.4, 'dash' => 0, 'color' => self::ACCENT]);
        $this->pdf->Line($xStart, $y, $xEnd, $y);
        $this->pdf->Ln(3);
        $this->pdf->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => self::LINE]);
    }
}
