<?php

namespace App\Controller\admin\generationPDF;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\da\DaSoumissionValidationService;

/** @Route(path="/admin/generation-PDF") */
class GenerationPDFController extends Controller
{
    private DaSoumissionValidationService $daSoumissionValidationService;

    public function __construct(DaSoumissionValidationService $daSoumissionValidationService)
    {
        parent::__construct();

        $this->daSoumissionValidationService = $daSoumissionValidationService;
    }

    /**
     * @Route(path="/da-avec-dit/{numeroDemandeAppro}", name="generation_pdf_da_avec_dit")
     */
    public function genererPdfDa(string $numeroDemandeAppro)
    {
        $this->daSoumissionValidationService->creationPDFAvecDit($numeroDemandeAppro);
    }

    /**
     * @Route(path="/da-direct/{numeroDemandeAppro}", name="generation_pdf_da_direct")
     */
    public function genererPdfDaDirect(string $numeroDemandeAppro)
    {
        $this->daSoumissionValidationService->creationPDFDirect($numeroDemandeAppro);
    }
}
