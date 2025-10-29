<?php

namespace App\Controller\Traits\da\validation;

use App\Repository\dit\DitRepository;
use App\Entity\dit\DemandeIntervention;
use App\Service\genererPdf\da\GenererPdfDaAvecDit;

trait DaValidationAvecDitTrait
{
    use DaValidationTrait;

    //====================================================================================================
    private DitRepository $ditRepository;
    private GenererPdfDaAvecDit $genererPdfDaAvecDit;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->genererPdfDaAvecDit = new GenererPdfDaAvecDit;
        $this->ditRepository = $em->getRepository(DemandeIntervention::class);
    }
    //====================================================================================================

    /** 
     * Création du fichier Excel et PDF pour une DA avec DIT
     * 
     * @param string $numDa
     * @param int $numeroVersion
     * @return array
     */
    private function exporterDaAvecDitEnExcelEtPdf(string $numDa, int $numeroVersion): array
    {
        return $this->exporterDaEnExcelEtPdf(
            $numDa,
            $numeroVersion,
            function ($numDa) {
                $this->creationPDFAvecDit($numDa); // Création du PDF
            }
        );
    }

    /** 
     * Création du PDF pour une DA avec DIT
     * 
     * @param string $numDa
     * @return void
     */
    private function creationPDFAvecDit(string $numDa): void
    {
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);
        $this->genererPdfDaAvecDit->genererPdfBonAchatValide($dit, $da, $this->getUserMail());
    }
}
