<?php

namespace App\Service\genererPdf;

use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;

class GenererPdfDaDirect extends GeneratePdf
{
    /** 
     * Fonction pour générer le PDF d'un bon d'achat validé d'une DA sans DIT
     * 
     * @param DemandeAppro $da la DA correspondante
     * @param iterable<DaObservation> $observations les observations liées à la DA
     * @param string $userMail l'email de l'utilisateur (optionnel)
     * 
     * @return void
     */
    public function genererPdfBonAchatValide(DemandeAppro $da, iterable $observations, string $userMail = ''): void
    {
        $pdf = new TCPDF();
        $dals = $da->getDAL();
        $numDa = $da->getNumeroDemandeAppro();
        $generator = new PdfTableMatriceGenerator();

        $pdf->AddPage();

        //=========================================================================================
        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetY(2);
        $pdf->writeHTMLCell(0, 6, '', '', "email : $userMail", 0, 1, false, true, 'R');

        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath =  $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        $pdf->Cell(110, 6, 'DEMANDE D\'ACHAT', 0, 0, 'C', false, '', 0, false, 'T', 'M');

        $pdf->setAbsX(170);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $numDa, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $da->getDateCreation()->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        //========================================================================================
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Objet :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->cell(0, 6, $da->getObjetDal(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);

        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Détails :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setFont('helvetica', '', 9);
        $pdf->MultiCell(164, 50, $da->getDetailDal(), 1, '', 0, 0, '', '', true);
        $pdf->Ln(3, true);
        $pdf->setAbsY(83);

        //===================================================================================================
        /**PRIORITE */
        $this->renderTextWithLine($pdf, 'Priorité');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(20, 6, 'Urgence :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(25, 6, $da->getNiveauUrgence(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /**AGENCE-SERVICE */
        $this->renderTextWithLine($pdf, 'Agence - Service');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Emetteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(50, 6, $da->getAgenceServiceEmetteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Débiteur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $da->getAgenceServiceDebiteur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6, true);

        //===================================================================================================
        /** ARTICLE VALIDES */
        $this->renderTextWithLine($pdf, 'Articles validés');

        $pdf->Ln(3);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', '', 10);
        $html1 = $generator->generer($dals);
        $pdf->writeHTML($html1, true, false, true, false, '');

        //=========================================================================================
        /** OBSERVATIONS */
        $this->renderTextWithLine($pdf, 'Echange entre le service Emetteur et le service Appro');
        $this->renderChatMessages($pdf, $observations);

        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa";

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $pdf->Output("$Dossier/$numDa.pdf", 'I');
    }

    /**
     * Affiche une liste d'observations sous forme de chat dans un PDF TCPDF.
     *
     * @param TCPDF $pdf L'instance TCPDF
     * @param iterable<DaObservation> $observations Liste d'objets Daobservation (ayant utilisateur, observation, date_creation)
     */
    function renderChatMessages(TCPDF $pdf, iterable $observations): void
    {
        $appro = ['marie', 'Vania', 'stg.tahina', 'narindra.veloniaina', 'hobimalala'];

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)
        $usable_width = $w_total - $margins['left'] - $margins['right'];

        $leftColor = [240, 240, 240]; // gris clair
        $rightColor = [210, 230, 255]; // bleu clair
        $borderRadius = 3;

        $maxWidth = 120;

        foreach ($observations as $obs) {
            $user    = $obs->getUtilisateur();
            $message = $obs->getObservation();
            $date    = $obs->getDateCreation()->format('d/m/Y H:i');
            $isAppro = in_array($user, $appro);

            $align = $isAppro ? 'R' : 'L';
            $fillColor = $isAppro ? $rightColor : $leftColor;

            // Remplace <br> par \n pour MultiCell
            $message = str_replace('<br>', "\n", $message);

            // Calcule la largeur et position X
            $x = $isAppro ? $usable_width - $maxWidth + $margins['left'] : $margins['left'];

            // Position Y actuelle
            $y = $pdf->GetY() + 5;

            // En-tête : nom + date
            $header = sprintf('%s — %s', $user, $date);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->MultiCell($maxWidth, 5, $header, 0, $align, false, 1, $x, $y, true);
            $y = $pdf->GetY();

            // Mesure la hauteur du message
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetXY($x, $y);
            $messageHeight = $pdf->getStringHeight($maxWidth - 4, $message);

            // Dessine une bulle arrondie
            $pdf->SetFillColor(...$fillColor);
            $pdf->RoundedRect($x, $y, $maxWidth, $messageHeight + 4, $borderRadius, '1111', 'DF');

            // Texte du message
            $pdf->SetXY($x + 2, $y + 2);
            $pdf->MultiCell($maxWidth - 4, 0, $message, 0, 'L', false, 1, '', '', true);

            // Saut après le message
            $pdf->Ln(3);
        }
    }
}
