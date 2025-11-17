<?php

namespace App\Service\genererPdf\da;

use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;

class GenererPdfDaDirect extends GenererPdfDa
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

        $pdf->AddPage();

        $this->renderHeaderPdfDA($pdf, $userMail, $da);

        $this->renderObjetDetailPdfDA($pdf, $da->getObjetDal(), $da->getDetailDal());

        //===================================================================================================
        /**PRIORITE */
        $this->renderTextWithLine($pdf, 'Priorité');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(20, 6, 'Urgence :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(25, 6, $da->getNiveauUrgence(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(8);

        $this->renderAgenceServicePdfDA($pdf, $da->getAgenceServiceEmetteur(), $da->getAgenceServiceDebiteur());

        $this->renderTableArticlesValidesPdfDA($pdf, $dals);

        //=========================================================================================
        /** OBSERVATIONS */
        $this->renderTextWithLine($pdf, 'Echange entre le service Emetteur et le service Appro');
        $this->renderChatMessages($pdf, $observations);

        // Sauvegarder le PDF
        $this->saveBonAchatValide($pdf, $numDa);
    }
}
