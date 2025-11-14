<?php

namespace App\Controller\admin\generationPDF;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\validation\DaValidationDirectTrait;
use App\Controller\Traits\da\validation\DaValidationAvecDitTrait;

/** @Route(path="/admin/generation-PDF") */
class GenerationPDFController extends Controller
{
    use DaValidationDirectTrait;
    use DaValidationAvecDitTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaValidationAvecDitTrait();
        $this->initDaValidationDirectTrait();
    }

    /**
     * @Route(path="/da-avec-dit/{numeroDemandeAppro}", name="generation_pdf_da_avec_dit")
     */
    public function genererPdfDa(string $numeroDemandeAppro)
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('security_signin');
        }
        $this->creationPDFAvecDit($numeroDemandeAppro);
    }

    /**
     * @Route(path="/da-direct/{numeroDemandeAppro}", name="generation_pdf_da_direct")
     */
    public function genererPdfDaDirect(string $numeroDemandeAppro)
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('security_signin');
        }
        $this->creationPDFDirect($numeroDemandeAppro);
    }
}
