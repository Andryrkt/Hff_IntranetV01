<?php

namespace App\Controller\admin\generationPDF;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationReapproTrait;
use App\Service\da\DaConsumptionHistory;
use App\Service\genererPdf\da\GenererPdfDaReappro;

/** @Route(path="/admin/generation-PDF") */
class GenerationPDFReapproController extends Controller
{
    use DaValidationReapproTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaValidationReapproTrait();
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
        $observations = $this->daObservationRepository->findBy(
            ['numDa' => $numeroDemandeAppro],
            ['dateCreation' => 'ASC']
        );

        $genererPdfReappro = new GenererPdfDaReappro();
        $genererPdfReappro->genererPdfBonAchatValide($demandeAppro, $observations, $monthsList, $dataHistoriqueConsommation);

        // Dépôt du document dans DocuWare
        $genererPdfReappro->copyToDWDaAValiderReapproPonctuel($numeroDemandeAppro, "");

        // Enregistrement dans la table de Soumission
        $this->ajouterDansDaSoumisAValidation($demandeAppro);
    }
}
