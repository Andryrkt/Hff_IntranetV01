<?php

namespace App\Controller\generationPDF;

use App\Controller\Controller;
use App\Controller\Traits\da\validation\DaValidationAvecDitTrait;
use App\Controller\Traits\da\validation\DaValidationDirectTrait;
use Symfony\Component\Routing\Annotation\Route;

/** @Route(path="/api/generation-PDF") */
class GenerationPDFController extends Controller
{
    use DaValidationDirectTrait;
    use DaValidationAvecDitTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaValidationAvecDitTrait();
        $this->initDaValidationDirectTrait();
    }

    /**
     * @Route(path="/da/{numeroDemandeAppro}", name="generation_pdf_da")
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
