<?php

namespace App\Service\genererPdf\da;

use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use TCPDF;

class GenererPdfDaAvecDit extends GenererPdfDa
{
    /** 
     * Fonction pour générer le PDF d'un bon d'achat validé d'une DA avec DIT
     * 
     * @param DemandeIntervention $dit la DIT correspondante
     * @param DemandeAppro $da la DA correspondante
     * @param string $userMail l'email de l'utilisateur (optionnel)
     * 
     * @return void
     */
    public function genererPdfBonAchatValide(DemandeIntervention $dit, DemandeAppro $da, string $userMail = ''): void
    {
        $pdf = new TCPDF();
        $dals = $da->getDAL();
        $numDa = $da->getNumeroDemandeAppro();

        $pdf->AddPage();

        $this->renderHeaderPdfDA($pdf, $userMail, $da, $dit);

        $this->renderObjetDetailPdfDA($pdf, $da->getObjetDal(), $da->getDetailDal());

        //===================================================================================================
        /**INTERVENTION */
        $this->renderTextWithLine($pdf, 'Intervention');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, 'Date prévue :', 0, 0, '', false, '', 0, false, 'T', 'M');
        if ($dit->getDatePrevueTravaux() !== null && !empty($dit->getDatePrevueTravaux())) {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux()->format('d/m/Y'), 1, 0, '', false, '', 0, false, 'T', 'M');
        } else {
            $pdf->cell(50, 6, $dit->getDatePrevueTravaux(), 1, 0, '', false, '', 0, false, 'T', 'M');
        }
        $pdf->setAbsX(130);
        $pdf->cell(20, 6, 'Urgence :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getIdNiveauUrgence()->getDescription(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6);

        $this->renderAgenceServicePdfDA($pdf, $dit->getAgenceServiceEmetteur(), $dit->getAgenceServiceDebiteur());

        /**REPARATION */
        $this->renderTextWithLine($pdf, 'Réparation');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);
        $pdf->cell(25, 6, 'Type :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $dit->getInternetExterne(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(23, 6, 'Réparation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(35, 6, $dit->getTypeReparation(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(25, 6, 'Réalisé par :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getReparationRealise(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6);

        //========================================================================================================
        /** CARACTERISTIQUE MATERIEL */
        $this->renderTextWithLine($pdf, 'Caractéristiques du matériel');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);


        $pdf->cell(25, 6, 'Désignation :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(70, 6, $dit->getDesignation(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(140);
        $pdf->cell(20, 6, 'N° Série :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getNumSerie(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7);


        $pdf->cell(25, 6, 'N° Parc :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(30, 6, $dit->getNumParc(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(70);
        $pdf->cell(21, 6, 'Modèle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(37, 6, $dit->getModele(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(30, 6, 'Constructeur :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getConstructeur(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(7);

        $pdf->cell(25, 6, 'Casier :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $casier = $dit->getCasier();
        if (mb_strlen($casier) > 17) {
            $casier = mb_substr($casier, 0, 15) . '...';
        }
        $pdf->cell(40, 6, $casier, 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(80);
        $pdf->cell(23, 6, 'Id Matériel :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(20, 6, $dit->getIdMateriel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(130);
        $pdf->cell(33, 6, 'livraison partielle :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getLivraisonPartiel(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6);

        //===================================================================================================
        /** ETAT MACHINE */
        $this->renderTextWithLine($pdf, 'Etat machine');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('helvetica', 'B', 10);

        $pdf->cell(25, 6, "Heures :", 0, 'L', false, 0);
        $pdf->cell(30, 6, $dit->getHeure(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->setAbsX(135);
        $pdf->cell(25, 6, 'Kilométrage :', 0, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->cell(0, 6, $dit->getKm(), 1, 0, '', false, '', 0, false, 'T', 'M');
        $pdf->Ln(6);

        $this->renderTableArticlesValidesPdfDA($pdf, $dals);

        // Sauvegarder le PDF
        $this->saveBonAchatValide($pdf, $numDa);
    }
}
