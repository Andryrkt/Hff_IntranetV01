<?php

namespace App\Controller\admin\generationPDF;

use App\Controller\Controller;
use App\Controller\Traits\da\validation\DaValidationAvecDitTrait;
use App\Controller\Traits\da\validation\DaValidationDirectTrait;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

/** @Route(path="/admin/generation-PDF") */
class GenerationPDFController extends BaseController
{
    use DaValidationDirectTrait;
    use DaValidationAvecDitTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager($this->getEntityManager());
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
