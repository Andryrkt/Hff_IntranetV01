<?php

namespace App\Controller\Traits\da\validation;

use App\Entity\da\DemandeAppro;
use App\Entity\dit\DemandeIntervention;
use App\Repository\dit\DitRepository;
use App\Service\da\EmailDaService;
use App\Service\genererPdf\GenererPdfDaAvecDit;

trait DaValidationAvecDitTrait
{
    use DaValidationTrait;

    //====================================================================================================
    private DitRepository $ditRepository;
    private EmailDaService $emailDaService;

    /**
     * Initialise les valeurs par défaut du trait
     */
    public function initDaValidationAvecDitTrait(): void
    {
        $em = $this->getEntityManager();
        $this->initDaTrait();
        $this->emailDaService = new EmailDaService;
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
        $da = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numDa);
        $dit = $this->ditRepository->findOneBy(['numeroDemandeIntervention' => $da->getNumeroDemandeDit()]);
        $genererPdfDaAvecDit->genererPdf($dit, $da, $this->getUserMail());
    }

    /** 
     * Méthode pour envoyer une email de validation à l'Atelier et l'Appro
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    private function envoyerMailValidationDaAvecDit(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->emailDaService->envoyerMailValidationDaAvecDitAuxAtelier($demandeAppro, $resultatExport, $tab); // envoi de mail à l'atelier
        $this->emailDaService->envoyerMailValidationDaAvecDitAuxAppro($demandeAppro, $resultatExport, $tab); // envoi de mail à l'appro
    }
}
