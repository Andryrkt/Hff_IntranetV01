<?php

namespace App\Controller\admin\generationPDF;

use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Service\da\DaConsumptionHistory;
use App\Service\da\DaObservationService;
use App\Repository\da\DemandeApproRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\da\DaSoumissionValidationService;
use Doctrine\ORM\EntityManagerInterface;

/** @Route(path="/admin/generation-PDF") */
class GenerationPDFReapproController extends Controller
{
    private DaObservationService $daObservationService;
    private DemandeApproRepository $demandeApproRepository;
    private DaSoumissionValidationService $daSoumissionValidationService;

    public function __construct(
        EntityManagerInterface $em,
        DaObservationService $daObservationService,
        DaSoumissionValidationService $daSoumissionValidationService
    ) {
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
        $this->daObservationService = $daObservationService;
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
        $observations = $this->daObservationService->getObservations($numeroDemandeAppro);

        $this->daSoumissionValidationService->creationPDFReappro($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);

        // Dépôt du document dans DocuWare
        $this->daSoumissionValidationService->copyPDFToDWReapproPonctuel($numeroDemandeAppro);

        // Enregistrement dans la table de Soumission
        $this->daSoumissionValidationService->ajouterDansDaSoumisAValidation($demandeAppro);
    }
}
