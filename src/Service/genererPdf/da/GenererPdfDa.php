<?php

namespace App\Service\genererPdf\da;

use TCPDF;
use DateTime;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Service\genererPdf\GeneratePdf;

abstract class GenererPdfDa extends GeneratePdf
{
    /** 
     * Fonction pour générer l'entête du PDF de la DA
     */
    protected function renderHeaderPdfDA(TCPDF $pdf, string $numDa, string $userMail, int $daTypeId, DateTime $dateCreation, ?DemandeIntervention $dit = null): void
    {
        $titre = [
            DemandeAppro::TYPE_DA_AVEC_DIT  => "DEMANDE D’APPROVISIONNEMENT",
            DemandeAppro::TYPE_DA_DIRECT    => "DEMANDE D’ACHAT",
            DemandeAppro::TYPE_DA_REAPPRO   => "DEMANDE DE REAPPRO MENSUEL",
        ];

        $pdf->setFont('helvetica', 'B', 14);
        $pdf->setAbsY(11);
        $logoPath =  $_ENV['BASE_PATH_LONG'] . '/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, '', '', 45, 12);
        $pdf->setAbsX(55);
        $pdf->Cell(110, 6, $titre[$daTypeId], 0, 0, 'C', false, '', 0, false, 'T', 'M');

        // entête email
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'BI', 10);
        $pdf->SetY(2);
        $pdf->Cell(0, 6, "email : $userMail", 0, 0, 'R');

        $pdf->setAbsXY(170, 11);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->Cell(35, 6, $numDa, 0, 0, 'L', false, '', 0, false, 'T', 'M');

        $pdf->Ln(6, true);

        if ($dit) {
            $pdf->setFont('helvetica', 'B', 12);
            $pdf->setAbsX(55);
            if ($dit->getTypeDocument() !== null) {
                $descriptionTypeDocument = $dit->getTypeDocument()->getDescription();
            } else {
                $descriptionTypeDocument = ''; // Ou toute autre valeur par défaut appropriée
            }
            $pdf->cell(110, 6, $descriptionTypeDocument, 0, 0, 'C', false, '', 0, false, 'T', 'M');
        }

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->setAbsX(170);
        $pdf->cell(35, 6, 'Le : ' . $dateCreation->format('d/m/Y'), 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7, true);
    }

    /**
     * Affiche une conversation type chat dans un PDF TCPDF.
     *
     * @param TCPDF $pdf
     * @param iterable<DaObservation> $observations Liste d'objets (getUtilisateur(), getObservation(), getDateCreation())
     */
    protected function renderChatMessages(TCPDF $pdf, iterable $observations): void
    {
        $appro = ['marie', 'Vania', 'stg.tahina', 'narindra.veloniaina', 'hobimalala'];

        $w_total = $pdf->GetPageWidth();  // Largeur totale du PDF
        $margins = $pdf->GetMargins();    // Tableau des marges (left, top, right)

        $leftColor  = [220, 220, 220];    // messages autres (gris)
        $rightColor = [255, 209, 69];     // messages APPRO  (orange doux)
        $borderRadius = 3;
        $maxWidth = ($w_total - $margins['left'] - $margins['right']) * 0.75;  // largeur max autorisée

        $previousUser = null;

        foreach ($observations as $obs) {
            $user    = $obs->getUtilisateur();
            $message = str_replace('<br>', "\n", trim($obs->getObservation()));
            $date    = $obs->getDateCreation()->format('d/m/Y H:i');
            $isAppro = in_array($user, $appro);

            $fillColor = $isAppro ? $rightColor : $leftColor;
            $align = $isAppro ? 'R' : 'L';

            // Calcul dynamique de la largeur : prendre en compte chaque ligne
            $pdf->SetFont('helvetica', '', 10);
            $lines       = explode("\n", $message);
            $lineWidths  = array_map(fn($line) => $pdf->GetStringWidth($line), $lines);
            $textWidth   = max($lineWidths) + 11;  // +11 pour padding
            $bubbleWidth = min($textWidth, $maxWidth);

            // position X selon côté
            $x = $isAppro ? $w_total - $margins['right'] - $bubbleWidth : $margins['left'];

            // Si premier message d'un groupe, afficher Nom
            if ($previousUser !== $user) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetXY($x, $pdf->GetY());
                $pdf->Cell($bubbleWidth, 5, $user, 0, 1, $align, false);
            }

            // Afficher la date au-dessus de chaque message
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY($x, $pdf->GetY());
            $pdf->Cell($bubbleWidth, 4, $date, 0, 1, $align, false);

            // Message avec bulle + ombre + coupure automatique
            $this->drawMessageBubble($pdf, $message, $fillColor, $x, $bubbleWidth, $isAppro, $borderRadius, $margins);

            $previousUser = $user;
        }
    }

    /**
     * Dessine une bulle avec texte (gauche ou droite)
     * Gère automatiquement le saut de page si le message déborde.
     */
    protected function drawMessageBubble(TCPDF $pdf, string $message, array $fillColor, float $x, float $bubbleWidth, bool $isAppro, int $borderRadius, array $margins): void
    {
        $availableHeight = $pdf->getPageHeight() - $margins['bottom'] - $pdf->GetY();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        // Mesure la hauteur du texte complet
        $fullHeight = $pdf->getStringHeight($bubbleWidth - 6, $message);

        // Découpage si dépassement
        $textParts = ($fullHeight > $availableHeight)
            ? $this->splitTextToFit($pdf, $message, $bubbleWidth - 6, $availableHeight - 6)
            : [$message];

        foreach ($textParts as $index => $part) {
            $msgHeight = $pdf->getStringHeight($bubbleWidth - 6, $part);
            $yBubble = $pdf->GetY();

            // ---- Ombre douce ----
            $pdf->SetFillColor(...array_map(fn($c) => round($c * 0.8), $fillColor)); // atténuation de la couleur
            $pdf->RoundedRect($x + ($isAppro ? 4.625 : 0.625), $yBubble + 0.625, $bubbleWidth - 4, $msgHeight + 4, $borderRadius, '1111', 'F');

            // ---- Bulle principale ----
            $pdf->SetFillColor(...$fillColor);
            $pdf->RoundedRect($x + ($isAppro ? 4 : 0), $yBubble, $bubbleWidth - 4, $msgHeight + 4, $borderRadius, '1111', 'F');

            // ---- Texte ----
            $pdf->SetXY($x + ($isAppro ? 6 : 3), $yBubble + 2);
            $pdf->MultiCell($bubbleWidth - 6, 0, $part, 0, 'L', false, 1);

            $pdf->Ln(3);

            // Si le texte est scindé, on passe à la page suivante
            if ($index < count($textParts) - 1) {
                $pdf->AddPage();
                $pdf->Ln(2);
            }
        }
    }

    /**
     * Coupe un texte en plusieurs parties de sorte que chacune tienne dans la hauteur max.
     * Retourne un tableau de parties : [part1, part2, part3, ...]
     *
     * Note : on reconstitue le texte avec un espace entre les mots (perd éventuellement
     * les espaces multiples ou tabulations), mais la mise en page résultante respectera
     * le wrapping automatique de TCPDF.
     */
    protected function splitTextToFit(TCPDF $pdf, string $text, float $width, float $maxHeight): array
    {
        $pdf->SetFont('helvetica', '', 10);

        // Normaliser les retours à la ligne en espaces simples pour le découpage en mots,
        // ensuite on recompose avec des espaces simples.
        $normalized = preg_replace("/\s+/u", ' ', trim($text));
        if ($normalized === '') {
            return [''];
        }

        $words = explode(' ', $normalized);
        $parts = [];
        $current = '';

        foreach ($words as $i => $word) {
            $test = $current === '' ? $word : ($current . ' ' . $word);

            // mesurer la hauteur si on ajoute ce mot
            $height = $pdf->getStringHeight($width, $test);

            if ($height <= $maxHeight) {
                // on peut ajouter le mot à la partie courante
                $current = $test;
            } else {
                // la partie courante est complète : on la sauve
                // si elle est vide (un mot trop long), on doit forcer au moins ce mot dans une part
                if ($current === '') {
                    // mot très long (probablement un mot sans espaces) : on essaie de placer tel quel
                    // pour éviter boucle infinie, on met le mot seul dans une part (même si il dépasse)
                    $parts[] = $word;
                } else {
                    $parts[] = $current;
                    // on démarre une nouvelle partie avec le mot actuel
                    $current = $word;
                }
            }
        }

        // pousser la dernière partie si non vide
        if ($current !== '') {
            $parts[] = $current;
        }

        // cas limite : si aucune part (texte vide), renvoyer tableau avec chaîne vide
        return count($parts) ? $parts : [''];
    }

    /**
     * Sauvegarde un PDF dans le répertoire spécifique à un DA.
     * 
     * Cette fonction :
     * 1. Construit le chemin absolu du répertoire pour le DA donné.
     * 2. Vérifie si ce répertoire existe, et le crée si nécessaire.
     * 3. Enregistre le PDF dans ce répertoire avec le nom <numDa>.pdf.
     *
     * @param TCPDF  $pdf   L’objet PDF à sauvegarder.
     * @param string $numDa Numéro de DA utilisé pour le nom du dossier et du fichier.
     *
     * @throws \RuntimeException Si le répertoire ne peut pas être créé.
     */
    protected function saveBonAchatValide(TCPDF $pdf, string $numDa): void
    {
        // Obtention du chemin absolu du répertoire de travail
        $Dossier = $_ENV['BASE_PATH_FICHIER'] . "/da/$numDa";

        // Vérification si le répertoire existe, sinon le créer
        if (!is_dir($Dossier)) {
            if (!mkdir($Dossier, 0777, true)) {
                throw new \RuntimeException("Impossible de créer le répertoire : $Dossier");
            }
        }

        $pdf->Output("$Dossier/$numDa.pdf", "F");
    }
}
