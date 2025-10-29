<?php

namespace App\Service\genererPdf\da;

use TCPDF;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;

class GenererPdfDaReappro extends GenererPdfDa
{
    /** 
     * Fonction pour générer le PDF d'un bon d'achat validé d'une DA réappro
     * 
     * @param DemandeAppro            $da                         la DA correspondante
     * @param string                  $userMail                   l'email de l'utilisateur ()
     * @param iterable<DaObservation> $observations               les observations liées à la DA
     * @param array                   $monthsList                 liste de mois dans le tableau d'historique de consommation
     * @param array                   $dataHistoriqueConsommation données de la liste d'historique de consommation
     * 
     * @return void
     */
    public function genererPdfBonAchatValide(DemandeAppro $da, string $userMail, iterable $observations, array $monthsList, array $dataHistoriqueConsommation): void
    {
        $pdf = new TCPDF();
        $dals = $da->getDAL();
        $numDa = $da->getNumeroDemandeAppro();

        $pdf->AddPage();

        $this->renderHeaderPdfDA($pdf, $numDa, $userMail, $da->getDaTypeId(), $da->getDateCreation());

        $this->renderObjetDetailPdfDA($pdf, $da->getObjetDal(), $da->getDetailDal());

        $this->renderAgenceServicePdfDA($pdf, $da->getAgenceServiceEmetteur(), $da->getAgenceServiceDebiteur());

        $this->renderTableArticleDemandeReappro($pdf, $da->getDAL());

        $this->renderTableHistoriqueConsomReappro($pdf, $monthsList, $dataHistoriqueConsommation);

        //=========================================================================================
        /** OBSERVATIONS */
        $this->renderTextWithLine($pdf, 'Echange entre le service Emetteur et le service Appro');
        $this->renderChatMessages($pdf, $observations);

        // Sauvegarder le PDF
        $this->saveBonAchatValide($pdf, $numDa);
    }
}
