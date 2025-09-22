<?php

namespace App\Controller\Traits\da\validation;

use App\Entity\dit\DemandeIntervention;
use App\Repository\dit\DitRepository;
use App\Service\genererPdf\GenererPdfDaAvecDit;

trait DaValidationAvecDitTrait
{
    use DaValidationTrait;

    //====================================================================================================
    protected DitRepository $ditRepository;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationAvecDitTrait(): void
    {
        $em = $this->getEntityManager();

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
        $genererPdfDaAvecDit = new GenererPdfDaAvecDit;
        $da = $this->getDemandeApproRepository()->findAvecDernieresDALetLRParNumero($numDa);
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);
        $genererPdfDaAvecDit->genererPdf($dit, $da, $this->getUserMail());
    }
}
