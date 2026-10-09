<?php

namespace App\Controller\admin\generationPDF;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\da\DemandeApproRepository;
use App\Entity\da\DemandeAppro;
use App\Service\da\DaConsumptionHistory;
use App\Service\da\DaService;
use App\Service\da\DaSoumissionValidationService;

/** @Route(path="/admin/generation-PDF") */
class GenerationPDFReapproController extends Controller
{
    private DemandeApproRepository $demandeApproRepository;

    private DaService $daService;
    private DaSoumissionValidationService $daSoumissionValidationService;

    public function __construct(DaService $daService, DaSoumissionValidationService $daSoumissionValidationService)
    {
        $em = $this->getEntityManager();
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->daService = $daService;
        $this->daSoumissionValidationService = $daSoumissionValidationService;
    }

    /**
     * @Route(path="/valider/da-reappro/{numeroDemandeAppro}", name="valider_da_reappro")
     */
    public function validerDaReappro(string $numeroDemandeAppro)
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('security_signin');
        }
        $demandeAppro = $this->demandeApproRepository->findAvecDernieresDALetLRParNumero($numeroDemandeAppro);
        // création de PDF
        $daConsumptionHistory = new DaConsumptionHistory();
        $dateRange = $daConsumptionHistory->getLast13MonthsDateRange();
        $monthsList = $daConsumptionHistory->getMonthsList($dateRange['start'], $dateRange['end']);
        $dataHistoriqueConsommation = $daConsumptionHistory->getHistoriqueConsommation($demandeAppro, $dateRange, $monthsList);
        $observations = $this->daService->getObservations($numeroDemandeAppro);

        $this->daSoumissionValidationService->creationPDFReappro($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);

        // Dépôt du document dans DocuWare
        $this->daSoumissionValidationService->copyPDFToDWReapproPonctuel($numeroDemandeAppro);

        // Enregistrement dans la table de Soumission
        $this->daSoumissionValidationService->ajouterDansDaSoumisAValidation($demandeAppro);
    }
}
