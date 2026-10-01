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

    private const TITLE_SIZE       = 10.0;
    private const MAIN_TEXT_SIZE   = 7.2;
    private const SUB_TEXT_SIZE    = 6.7;

    private const MAIN_TEXT_HEIGHT = 5.3;
    private const MAIN_ROW_HEIGHT  = 6.5;
    private const SUB_ROW_HEIGHT   = 5.75;
    private const TITLE_HEIGHT     = 7.5;

    private const TEXT_COLOR        = [50, 50, 50];
    private const TEXT_HEADER_COLOR = [240, 240, 240];
    private const HEADER_COLOR      = [50, 50, 50];
    private const ROW_COLOR         = [200, 200, 200];
    private const DOTTED_LINE_COLOR = [50, 50, 50];

    /** @var array{numCdeLbl:int|float,numCde:int|float,typeCdeLbl:int|float,typeCde:int|float,delaiExpLbl:int|float,frnLbl:int|float,numFrn:int|float,nomFrn:int|float,responsableLbl:int|float} $headerWidth */
    private array $headerWidth = [];

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

        $this->defineHeaderConfig();
        $this->defineMainRowWidths();
        $this->defineSubRowWidths();
        $this->defineFooterWidths();
    }

    public function generate(string $filePath): void
    {
        $this->renderHeader();

        $this->renderTable();

        $this->renderFooter();

        $this->pdf->Output($filePath, 'I');
        die;
    }

    private function initPDF(): TCPDF
    {
        $pdf = new TCPDF("L");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->setMargins(self::MARGIN_LEFT, self::MARGIN_TOP, self::MARGIN_RIGHT, true);

        $pdf->AddPage();

        return $pdf;
    }

    private function renderHeader(): void
    {
        $this->pdf->Image("{$this->basePathAssets}/info_HFF_1.png", $this->imgConfig['img1X'], $this->imgConfig['img1Y'], $this->imgConfig['img1W'], $this->imgConfig['img1H'], "PNG");
        $this->pdf->Image("{$this->basePathAssets}/info_HFF_2.png", $this->imgConfig['img2X'], $this->imgConfig['img2Y'], $this->imgConfig['img2W'], $this->imgConfig['img2H'], "PNG");

        $this->pdf->SetFont(self::FONT, "B", self::TITLE_SIZE);
        $this->pdf->Cell(0, self::TITLE_HEIGHT, "Cde Fournisseur", 0, 1);

        $this->pdf->setFont(self::FONT, "I", self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->headerWidth['numCdeLbl'], self::MAIN_TEXT_HEIGHT, "No Commande:", 0, 0);

        $this->pdf->setFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->headerWidth['numCde'], self::MAIN_TEXT_HEIGHT, $this->dto->numeroCommande, 0, 0);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, $this->dto->getDateCdeFormatted(), 0, 1);

        $this->pdf->setFont(self::FONT, "I", self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->headerWidth['typeCdeLbl'], self::MAIN_TEXT_HEIGHT, "Type Commande:", 0, 0);

        $this->pdf->setFont(self::FONT, "", self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->headerWidth['typeCde'], self::MAIN_TEXT_HEIGHT, $this->dto->typeCde, 0, 0);

        $this->pdf->setFont(self::FONT, "I", self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->headerWidth['delaiExpLbl'], self::MAIN_TEXT_HEIGHT, "Délai d'expédition:", 0, 0);

        $this->pdf->setFont(self::FONT, "", self::MAIN_TEXT_SIZE);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, $this->dto->getDelaiExpedition(), 0, 1);

        $this->pdf->setFont(self::FONT, "I", self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->headerWidth['frnLbl'], self::MAIN_TEXT_HEIGHT, "Fournisseur:", 0, 0);

        $this->pdf->setFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->headerWidth['numFrn'], self::MAIN_TEXT_HEIGHT, $this->dto->numFrn, 0, 0);
        $this->pdf->MultiCell($this->headerWidth['nomFrn'], self::MAIN_TEXT_HEIGHT, $this->dto->nomFrn, 0, 'L');

        $this->pdf->setFont(self::FONT, "I", self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->headerWidth['responsableLbl'], self::MAIN_TEXT_HEIGHT, "Responsable:", 0, 0);

        $this->pdf->setFont(self::FONT, "", self::MAIN_TEXT_SIZE);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, $this->dto->responsable, 0, 0);

        $this->pdf->setFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, $this->dto->getAgenceService(), 0, 1, 'R');
    }

    /** 
     * Méthode pour construire le tableau de lignes de commande
     * 
     * @return void
     */
    private function renderTable(): void
    {
        $this->pdf->Ln(3);

        $this->renderTableHeader();

        $rowIndex = 0;

        foreach ($this->dto->lignes as $ligneDto) {
            // 1. Calculer la hauteur totale du bloc (ligne + sous-lignes)
            $blockHeight = $this->calculateBlockHeight($ligneDto);

            // 2. Vérifier s'il faut un saut de page AVANT de dessiner le bloc
            if ($this->exceedsPage($blockHeight)) $this->newPage();

            // 3. Couleur de fond alternée
            $fill = ($rowIndex % 2 == 1);
            $this->pdf->SetFillColor(...self::ROW_COLOR);

            // 4. Dessiner la ligne principale
            $this->renderMainRow($ligneDto, $fill);

            // 5. Dessiner les sous-lignes (même fill que la ligne parente, pour cohérence visuelle)
            foreach ($ligneDto->details as $detailDto) {
                $this->renderSubRow($detailDto, $fill);
            }

            // 6. Dessiner la ligne de séparation entre les lignes principales
            $x = $this->pdf->GetX();
            $y = $this->pdf->GetY();

            $this->pdf->Line($x, $y, $this->pdf->getPageWidth() - self::MARGIN_LEFT, $y);

            $rowIndex++;
        }
    }

    private function renderTableHeader(): void
    {
        $this->pdf->SetFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->SetFillColor(...self::HEADER_COLOR);
        $this->pdf->SetTextColor(...self::TEXT_HEADER_COLOR);
        $this->pdf->SetDrawColor(...self::HEADER_COLOR);

        $headerHeight = $this->calculateHeaderHeight();
        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        foreach ($this->mainRowWidths as $key => $width) {
            $label = self::COL_LABELS[$key];
            $align = in_array($key, ['coutUnit', 'coutTotal']) ? 'R' : 'C';

            // MultiCell avec fond, mais sans avancer X automatiquement
            $this->pdf->MultiCell($width, $headerHeight, $label, 1, $align, true, 0, null, null, true, 0, false, true, $headerHeight, 'M');

            $x += $width;
            $this->pdf->SetXY($x, $y);
        }

        $this->pdf->SetXY(self::MARGIN_LEFT, $y + $headerHeight);

        // Reset couleurs pour les lignes de données
        $this->pdf->SetTextColor(...self::TEXT_COLOR);
        $this->pdf->SetDrawColor(...self::TEXT_COLOR);
        $this->pdf->SetFont(self::FONT, "", self::MAIN_ROW_HEIGHT);
    }

    private function renderMainRow(CommandeSoumissionLigneDTO $ligneDto, bool $fill): void
    {
        $this->pdf->SetFont(self::FONT, "B", self::MAIN_TEXT_SIZE);
        $this->pdf->SetFillColor(...self::ROW_COLOR);

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
        $this->pdf->SetFillColor(...self::ROW_COLOR);

        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();

        // Ligne pointillée séparant ce détail du suivant (sous la cellule texte)
        $this->drawDottedSeparator(
            $x + $this->subRowWidths['empty'] + $this->subRowWidths['refClientLabel'],
            $y,
            $this->pdf->getPageWidth() - self::MARGIN_LEFT,
            $fill
        );

        // Cellule vide (avec bordure normale, comme le reste du tableau)
        $this->pdf->Cell($this->subRowWidths['empty'], self::SUB_ROW_HEIGHT, '', 0, 0, 'L', $fill);

        $this->pdf->SetFont(self::FONT, "I", self::SUB_TEXT_SIZE);
        $this->cellUnderline($this->subRowWidths['refClientLabel'], self::SUB_ROW_HEIGHT, "Référence client:", 0, 0, 'L', $fill);

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

        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, 'Total Poids [Kg]', 0, 1, 'R');
        $this->pdf->Cell(0, self::MAIN_TEXT_HEIGHT, $this->dto->getPoidsTotal(), 0, 1, 'R');

        $this->pdf->Cell($this->footerWidths["empty1"]);
        $this->cellUnderline($this->footerWidths["signature"], self::MAIN_TEXT_HEIGHT, "X", 0, 0, '', false, true);

        $this->pdf->Cell($this->footerWidths["empty2"]);

        $this->pdf->SetFont(self::FONT, 'I', self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->footerWidths["mttTotalLabel"], self::MAIN_TEXT_HEIGHT, "Total commande HT : ", 0, 0);
        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->footerWidths["mttTotal"], self::MAIN_TEXT_HEIGHT, $this->dto->getMontantTotal(), 0, 1, 'R');

        $this->pdf->Cell($this->footerWidths["empty1"]);
        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $this->pdf->Cell($this->footerWidths["signature"], self::MAIN_TEXT_HEIGHT, "Signature");

        $this->pdf->Cell($this->footerWidths["empty2"]);
        $this->pdf->Cell($this->footerWidths["mttTotalLabel"] + $this->footerWidths["mttTotal"], self::MAIN_TEXT_HEIGHT, "Montant en {$this->dto->devise}", 0, 1, 'C');

        $this->drawFooterSeparator($this->pdf->GetX(), $this->pdf->GetY() + 1.5, $this->pdf->GetPageWidth() - self::MARGIN_LEFT);

        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->footerWidths["docRattacheesLabel"], self::MAIN_TEXT_HEIGHT, "Documents OR rattachés (OR validé) :", 0, 1, '', false, true);

        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $this->pdf->MultiCell(0, 0, $this->dto->getAllValidatedOR(), 0, "L");

        $this->pdf->Ln(3);

        $this->pdf->SetFont(self::FONT, 'B', self::MAIN_TEXT_SIZE);
        $this->cellUnderline($this->footerWidths["docRattacheesLabel"], self::MAIN_TEXT_HEIGHT, "Documents PO rattachés (PO validé) :", 0, 1, '', false, true);

        $this->pdf->SetFont(self::FONT, '', self::MAIN_TEXT_SIZE);
        $this->pdf->MultiCell(0, 0, $this->dto->getAllValidatedPO(), 0, "L");
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

        // Ln(3) x3 + 2 lignes poids + 2 lignes signature + séparateur (Ln 3 inclus) + 2 titres OR/PO
        return 3 + 3 + 3
            + 4 * self::MAIN_TEXT_HEIGHT
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
        $this->renderHeader($this->dto);
        $this->pdf->Ln(3);
        $this->renderTableHeader();
    }

    /** 
     * Définir les largeurs des colonnes sur l'entête de la page
     *
     * @return void
     */
    private function defineHeaderConfig(): void
    {
        $w100 = $this->getUsableWidth();

        $this->headerWidth['numCdeLbl']          =
            $this->headerWidth['typeCdeLbl']     =
            $this->headerWidth['typeCde']        =
            $this->headerWidth['delaiExpLbl']    =
            $this->headerWidth['frnLbl']         =
            $this->headerWidth['responsableLbl'] = 24;

        $this->headerWidth['numCde'] = $this->headerWidth['numFrn'] = 15;
        $this->headerWidth['nomFrn'] = 45;

        $this->imgConfig['img1W'] = 44.852;
        $this->imgConfig['img1H'] = $this->imgConfig['img1W'] * 434 / 1039;
        $this->imgConfig['img1X'] = $w100 / 2;

        $this->imgConfig['img2W'] = 25.588;
        $this->imgConfig['img2H'] = $this->imgConfig['img2W'] * 223 / 460;
        $this->imgConfig['img2X'] = $w100 - $this->imgConfig['img2W'] + 5;

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
     * Trace une ligne horizontale en pointillés entre deux sous-lignes de détail.
     */
    private function drawDottedSeparator(float $xStart, float $y, float $xEnd, bool $fill): void
    {
        $this->pdf->SetLineStyle([
            'width' => $fill ? 0.5 : 0.1,
            'dash'  => 2.25,
            'color' => self::DOTTED_LINE_COLOR,
        ]);

        $this->pdf->Line($xStart, $y, $xEnd, $y);

        // Reset au style de ligne normal (plein) pour la suite du tableau
        $this->pdf->SetLineStyle([
            'width' => 0.1,
            'dash'  => 0,
            'color' => self::TEXT_COLOR,
        ]);
    }

    /** 
     * Trace une ligne de séparation dans le footer
     */
    private function drawFooterSeparator(float $xStart, float $y, float $xEnd): void
    {
        $this->pdf->SetLineStyle([
            'width' => 0.7,
            'dash'  => 0,
            'color' => self::TEXT_COLOR,
        ]);

        $this->pdf->Line($xStart, $y, $xEnd, $y);
        $this->pdf->Ln(3);

        // Reset au style de ligne normal (plein) pour la suite du tableau
        $this->pdf->SetLineStyle([
            'width' => 0.1,
            'dash'  => 0,
            'color' => self::TEXT_COLOR,
        ]);
    }

    /**
     * Affiche une Cell classique et dessine un trait fin en dessous du texte,
     * pour simuler un soulignement (non supporté nativement par TCPDF sur Cell()).
     */
    private function cellUnderline(float $w, float $h, string $txt, $border = 0, int $ln = 0, string $align = '', bool $fill = false, bool $hasLongUnderline = false): void
    {
        $x = $this->pdf->GetX() + 1; // + décalage
        $y = $this->pdf->GetY();

        $this->pdf->Cell($w, $h, $txt, $border, 0, $align, $fill);

        // Largeur réelle du texte pour ne souligner que le texte, pas toute la cellule
        $textWidth = $hasLongUnderline ? $w : $this->pdf->GetStringWidth($txt);

        $lineY = $y + $h - ($hasLongUnderline ? 1 : 1.35); // légèrement au-dessus du bas de la cellule
        $lineXStart = $x;
        $lineXEnd = $x + $textWidth;

        $this->pdf->Line($lineXStart, $lineY, $lineXEnd, $lineY);

        // Repositionner le curseur comme le ferait Cell() avec $ln=0
        if ($ln == 0)      $this->pdf->SetXY($x + $w - 1, $y);
        elseif ($ln == 1)  $this->pdf->SetXY(self::MARGIN_LEFT, $y + $h);
        elseif ($ln == 2)  $this->pdf->SetXY($x - 1, $y + $h);
    }
}
